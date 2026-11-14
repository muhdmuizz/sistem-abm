<?php

namespace App\Services;

use App\Audit\PeristiwaAudit;
use App\Enums\Peranan;
use App\Enums\StatusBidaan;
use App\Models\Anggaran;
use App\Models\Bidaan;
use App\Models\ButiranAnggaran;
use App\Models\TempatBertugas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use JsonException;
use stdClass;
use SplObjectStorage;

/** Backend draft envelopes only. OS adapters must later validate approved field contracts. */
class DrafButiranAnggaran
{
    public function __construct(private JejakAudit $audit, private KawalanPenyediaanKewangan $guard) {}

    /**
     * Null source ID means a distinct new source; an edit must supply its existing ID.
     * Revisions are technical stale-write checks, never formal submission versions.
     */
    public function simpan(int $estimateId, ?int $sourceId, array $input): ButiranAnggaran
    {
        return DB::transaction(function () use ($estimateId, $sourceId, $input) {
            [$actor, $bid, $estimate, $source] = $this->lockedContext($estimateId, $sourceId);
            $cycleId = $bid->kitaran_id;

            $data = Validator::make(['rekod' => $input], [
                'rekod' => 'required|array:tempat_bertugas_id,data_sumber,semakan_bidaan,semakan_anggaran,semakan_butiran,reason',
                'rekod.tempat_bertugas_id' => 'required|integer|min:1',
                'rekod.data_sumber' => ['present', function ($attribute, $value, $fail) {
                    if (! $value instanceof stdClass) {
                        $fail('Data sumber mesti objek JSON draf.');
                    }
                }],
                'rekod.semakan_bidaan' => 'required|integer|min:0',
                'rekod.semakan_anggaran' => 'required|integer|min:0',
                'rekod.semakan_butiran' => $source ? 'required|integer|min:0' : 'prohibited',
                'rekod.reason' => 'nullable|string|max:1000',
            ])->validate()['rekod'];

            $workplaceId = (int) $data['tempat_bertugas_id'];
            $authority = $this->guard->authorise($actor, Peranan::PenyediaAnggaran, $cycleId,
                $bid->ptj_id, $bid->aktiviti_kewangan_id, $estimate->rujukan_os_id, $data['reason'] ?? null, $workplaceId);
            // Do not allow a workplace-limited user to take another workplace's source.
            if ($source !== null && $source->tempat_bertugas_id !== $workplaceId) {
                $this->guard->authorise($actor, Peranan::PenyediaAnggaran, $cycleId,
                    $bid->ptj_id, $bid->aktiviti_kewangan_id, $estimate->rujukan_os_id, $data['reason'] ?? null,
                    $source->tempat_bertugas_id);
            }
            $this->currentSource($bid, $estimate, $source, $data);
            // Explicit parent and concrete workplace; IDs in opaque data cannot affect ownership.
            TempatBertugas::whereKey($workplaceId)->where('ptj_id', $bid->ptj_id)->sharedLock()->firstOrFail();
            $encoded = $this->sourceJson($data['data_sumber']);
            $unchanged = $source !== null && $source->tempat_bertugas_id === $workplaceId &&
                $this->sourceJson($source->data_sumber) === $encoded;
            if ($unchanged) {
                return $source;
            }
            abort_if($source && $source->hasilPengiraan()->exists(), 409,
                'Butiran berpengiraan memerlukan pengiraan semula yang diluluskan; rekod asal dikekalkan.');
            $before = $source ? $this->snapshot($source, $bid, $estimate) : ['exists' => false,
                'semakan_bidaan' => $bid->semakan, 'semakan_anggaran' => $estimate->semakan];
            $this->audit->jalankan(Peranan::PenyediaAnggaran, function () use (&$source, $bid, $estimate, $workplaceId, $encoded, $before, $authority) {
                $created = $source === null;
                if ($created) {
                    $source = new ButiranAnggaran;
                    $source->forceFill(['anggaran_id' => $estimate->id, 'ptj_id' => $bid->ptj_id, 'semakan' => 0]);
                }
                $source->forceFill(['tempat_bertugas_id' => $workplaceId,
                    'data_sumber' => json_decode($encoded, false, 512, JSON_THROW_ON_ERROR)]);
                $source->semakan++;
                $source->save();
                $estimate->semakan++;
                $estimate->save();
                $bid->semakan++;
                $bid->save();
                return new PeristiwaAudit($created ? 'source.draft_created' : 'source.draft_updated',
                    ButiranAnggaran::class, (string) $source->id, $before,
                    $this->snapshot($source, $bid, $estimate) + ['pintasan' => $authority['override']],
                    $authority['reason'] ?: ($created ? 'Simpan draf Butiran Anggaran.' : 'Pinda draf Butiran Anggaran.'),
                    ptjId: $bid->ptj_id, aktivitiId: $bid->aktiviti_kewangan_id,
                    tempatBertugasId: $workplaceId, kitaranId: $bid->kitaran_id);
            });
            return $source->fresh();
        });
    }


    /**
     * One editor save, one Anggaran, all rows or none. Each entry contains the full
     * replacement source object. Omit butiran_id for a new independent source.
     * No public endpoint: approved OS adapters must validate their field schema first.
     *
     * @return array<int, ButiranAnggaran> Persisted sources in the supplied row order.
     */
    public function simpanBanyak(int $estimateId, array $input): array
    {
        return DB::transaction(function () use ($estimateId, $input) {
            $this->guard->actor();
            $data = Validator::make(['kelompok' => $input], [
                'kelompok' => 'required|array:semakan_bidaan,semakan_anggaran,reason,rekod',
                'kelompok.semakan_bidaan' => 'required|integer|min:0',
                'kelompok.semakan_anggaran' => 'required|integer|min:0',
                'kelompok.reason' => 'nullable|string|max:1000',
                'kelompok.rekod' => ['required', 'array', 'min:1', function ($attribute, $value, $fail) {
                    if (! is_array($value) || ! array_is_list($value)) {
                        $fail('Rekod mesti senarai baris yang tersusun.');
                    }
                }],
                'kelompok.rekod.*' => 'required|array:butiran_id,tempat_bertugas_id,data_sumber,semakan_butiran',
                'kelompok.rekod.*.butiran_id' => 'sometimes|required|integer|min:1|distinct',
            ])->validate()['kelompok'];

            $bidRevision = (int) $data['semakan_bidaan'];
            $estimateRevision = (int) $data['semakan_anggaran'];
            $saved = [];
            foreach ($data['rekod'] as $index => $row) {
                $sourceId = isset($row['butiran_id']) ? (int) $row['butiran_id'] : null;
                unset($row['butiran_id']);
                try {
                    $source = $this->simpan($estimateId, $sourceId, $row + [
                        'semakan_bidaan' => $bidRevision, 'semakan_anggaran' => $estimateRevision,
                        'reason' => $data['reason'] ?? null,
                    ]);
                } catch (ValidationException $exception) {
                    $errors = [];
                    foreach ($exception->errors() as $key => $messages) {
                        $field = str_starts_with($key, 'rekod.') ? substr($key, 6) : $key;
                        $errors['kelompok.rekod.'.$index.'.'.$field] = $messages;
                    }
                    throw ValidationException::withMessages($errors);
                }
                $saved[] = $source;
                // Parent locks acquired by the first save remain held until this
                // outer transaction commits. No-op rows do not advance revisions.
                $estimate = Anggaran::whereKey($estimateId)->firstOrFail();
                $estimateRevision = $estimate->semakan;
                $bidRevision = Bidaan::whereKey($estimate->bidaan_id)->firstOrFail()->semakan;
            }
            return $saved;
        });
    }


    /** Internal copy of one persisted draft; source inputs and target ownership are not client-supplied. */
    public function salin(int $estimateId, int $sourceId, array $input): ButiranAnggaran
    {
        return DB::transaction(function () use ($estimateId, $sourceId, $input) {
            [$actor, $bid, $estimate, $source] = $this->lockedContext($estimateId, $sourceId);
            $data = Validator::make(['rekod' => $input], [
                'rekod' => 'required|array:semakan_bidaan,semakan_anggaran,semakan_butiran,reason',
                'rekod.semakan_bidaan' => 'required|integer|min:0',
                'rekod.semakan_anggaran' => 'required|integer|min:0',
                'rekod.semakan_butiran' => 'required|integer|min:0',
                'rekod.reason' => 'nullable|string|max:1000',
            ])->validate()['rekod'];
            $workplaceId = $source->tempat_bertugas_id;
            $authority = $this->guard->authorise($actor, Peranan::PenyediaAnggaran, $bid->kitaran_id,
                $bid->ptj_id, $bid->aktiviti_kewangan_id, $estimate->rujukan_os_id, $data['reason'] ?? null, $workplaceId);
            $this->currentSource($bid, $estimate, $source, $data);
            TempatBertugas::whereKey($workplaceId)->where('ptj_id', $bid->ptj_id)->sharedLock()->firstOrFail();
            abort_if($source->hasilPengiraan()->exists(), 409,
                'Butiran berpengiraan memerlukan pengiraan semula yang diluluskan sebelum disalin.');
            $payload = $source->data_sumber;
            if (! $payload instanceof stdClass) {
                throw ValidationException::withMessages(['rekod.data_sumber' => 'Data sumber draf tidak sah untuk disalin.']);
            }
            $encoded = $this->sourceJson($payload);
            $before = ['exists' => false, 'disalin_daripada' => $source->id, 'semakan_sumber_asal' => $source->semakan,
                'tempat_bertugas_id' => $workplaceId, 'semakan_bidaan' => $bid->semakan, 'semakan_anggaran' => $estimate->semakan];
            $copy = null;
            $this->audit->jalankan(Peranan::PenyediaAnggaran, function () use (&$copy, $source, $bid, $estimate, $workplaceId, $encoded, $before, $authority) {
                $copy = new ButiranAnggaran;
                $copy->forceFill(['anggaran_id' => $estimate->id, 'ptj_id' => $bid->ptj_id,
                    'tempat_bertugas_id' => $workplaceId, 'semakan' => 1,
                    'data_sumber' => json_decode($encoded, false, 512, JSON_THROW_ON_ERROR)]);
                $copy->save();
                $estimate->semakan++;
                $estimate->save();
                $bid->semakan++;
                $bid->save();
                return new PeristiwaAudit('source.draft_duplicated', ButiranAnggaran::class, (string) $copy->id,
                    $before, $this->snapshot($copy, $bid, $estimate) + [
                        'disalin_daripada' => $source->id, 'semakan_sumber_asal' => $source->semakan,
                        'pintasan' => $authority['override']],
                    $authority['reason'] ?: 'Salin draf Butiran Anggaran.',
                    ptjId: $bid->ptj_id, aktivitiId: $bid->aktiviti_kewangan_id,
                    tempatBertugasId: $workplaceId, kitaranId: $bid->kitaran_id);
            });
            return $copy->fresh();
        });
    }


    /** Authorised removal of a source-only draft. Calculated records require the later deletion/recalculation workflow. */
    public function padam(int $estimateId, int $sourceId, array $input): void
    {
        DB::transaction(function () use ($estimateId,$sourceId,$input) {
            [$actor,$bid,$estimate,$source]=$this->lockedContext($estimateId,$sourceId);
            $data=Validator::make(['rekod'=>$input],[
                'rekod'=>'required|array:semakan_bidaan,semakan_anggaran,semakan_butiran,reason',
                'rekod.semakan_bidaan'=>'required|integer|min:0','rekod.semakan_anggaran'=>'required|integer|min:0',
                'rekod.semakan_butiran'=>'required|integer|min:0','rekod.reason'=>'nullable|string|max:1000',
            ])->validate()['rekod'];
            $authority=$this->guard->authorise($actor,Peranan::PenyediaAnggaran,$bid->kitaran_id,$bid->ptj_id,
                $bid->aktiviti_kewangan_id,$estimate->rujukan_os_id,$data['reason'] ?? null,$source->tempat_bertugas_id);
            $this->currentSource($bid,$estimate,$source,$data);
            abort_if($source->hasilPengiraan()->exists(),409,'Butiran berpengiraan tidak boleh dipadam melalui draf ini.');
            $before=$this->snapshot($source,$bid,$estimate);
            $this->audit->jalankan(Peranan::PenyediaAnggaran,function() use($source,$bid,$estimate,$before,$authority) {
                $source->delete(); $bid->semakan++; $bid->save(); $estimate->semakan++; $estimate->save();
                return new PeristiwaAudit('source.draft_deleted',ButiranAnggaran::class,(string)$source->id,$before,
                    ['exists'=>false,'bidaan_id'=>$bid->id,'anggaran_id'=>$estimate->id,
                        'tempat_bertugas_id'=>$source->tempat_bertugas_id,'semakan_bidaan'=>$bid->semakan,
                        'semakan_anggaran'=>$estimate->semakan,'pintasan'=>$authority['override']],
                    $authority['reason'] ?: 'Padam draf Butiran Anggaran.',ptjId:$bid->ptj_id,
                    aktivitiId:$bid->aktiviti_kewangan_id,tempatBertugasId:$source->tempat_bertugas_id,kitaranId:$bid->kitaran_id);
            });
        });
    }

    /** Caller owns the outer transaction; preserve the existing financial lock order. */
    private function lockedContext(int $estimateId, ?int $sourceId): array
    {
        $actor = $this->guard->actor();
        $context = Anggaran::whereKey($estimateId)->firstOrFail(['id', 'bidaan_id']);
        $cycleId = Bidaan::whereKey($context->bidaan_id)->value('kitaran_id');
        abort_if($cycleId === null, 404);
        $this->guard->prepareLocks($cycleId);
        $bid = Bidaan::whereKey($context->bidaan_id)->where('kitaran_id', $cycleId)->lockForUpdate()->firstOrFail();
        $estimate = $bid->anggaran()->whereKey($estimateId)->lockForUpdate()->firstOrFail();
        $source = $sourceId === null ? null : $estimate->butiran()->whereKey($sourceId)->lockForUpdate()->firstOrFail();
        return [$actor, $bid, $estimate, $source];
    }

    private function currentSource(Bidaan $bid, Anggaran $estimate, ?ButiranAnggaran $source, array $data): void
    {
        $this->guard->current($bid, (int) $data['semakan_bidaan'],
            [StatusBidaan::DalamPenyediaan, StatusBidaan::Dikembalikan]);
        abort_unless($bid->diaktifkan && $estimate->diaktifkan, 409, 'Bidaan dan OS mesti aktif.');
        abort_unless($estimate->semakan === (int) $data['semakan_anggaran'], 409, 'OS telah berubah. Muat semula.');
        abort_if($source && $source->semakan !== (int) $data['semakan_butiran'], 409, 'Butiran telah berubah. Muat semula.');
        $this->guard->catalogue($bid, $estimate->rujukan_os_id);
    }

    private function snapshot(ButiranAnggaran $source, Bidaan $bid, Anggaran $estimate): array
    {
        return ['butiran_id' => $source->id, 'anggaran_id' => $estimate->id, 'bidaan_id' => $bid->id,
            'tempat_bertugas_id' => $source->tempat_bertugas_id,
            // Explicit validated source representation preserves JSON object/list and decimal text types.
            'data_sumber_json' => $this->sourceJson($source->data_sumber),
            'semakan_butiran' => $source->semakan, 'semakan_anggaran' => $estimate->semakan, 'semakan_bidaan' => $bid->semakan];
    }

    private function sourceJson(stdClass $source): string
    {
        try {
            // Inspect inert JSON values without invoking serializers on arbitrary PHP objects.
            $canonical = $this->canonical($source, 0, new SplObjectStorage);
            return json_encode($canonical, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $exception) {
            throw ValidationException::withMessages(['rekod.data_sumber' => 'Data sumber JSON tidak sah.']);
        }
    }

    private function canonical(mixed $value, int $depth, SplObjectStorage $ancestors): mixed
    {
        if ($depth > 512) {
            throw ValidationException::withMessages(['rekod.data_sumber' => 'Struktur JSON terlalu dalam atau berulang.']);
        }
        if ($value instanceof stdClass) {
            if ($ancestors->contains($value)) {
                throw ValidationException::withMessages(['rekod.data_sumber' => 'Struktur JSON berulang tidak dibenarkan.']);
            }
            $ancestors->attach($value);
            $properties = get_object_vars($value);
            ksort($properties, SORT_STRING);
            $result = new stdClass;
            foreach ($properties as $key => $entry) {
                $name = strtolower((string) preg_replace('/([a-z])([A-Z])/', '$1_$2', (string) $key));
                $name = preg_replace('/[^a-z0-9]+/', '_', $name);
                if (preg_match('/password|passwd|secret|token|authorization|cookie|api_key|app_key|credential/', $name)) {
                    throw ValidationException::withMessages(['rekod.data_sumber' => 'Medan kelayakan sensitif tidak dibenarkan.']);
                }
                $result->{$key} = $this->canonical($entry, $depth + 1, $ancestors);
            }
            $ancestors->detach($value);
            return $result;
        }
        if (is_array($value) && array_is_list($value)) {
            return array_map(fn ($entry) => $this->canonical($entry, $depth + 1, $ancestors), $value);
        }
        if ($value === null || is_string($value) || is_int($value) || is_bool($value)) {
            return $value;
        }
        throw ValidationException::withMessages(['rekod.data_sumber' => 'Gunakan nilai JSON yang sah; nombor perpuluhan tepat mesti teks.']);
    }
}
