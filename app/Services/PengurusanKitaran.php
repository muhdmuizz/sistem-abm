<?php

namespace App\Services;

use App\Audit\PeristiwaAudit;
use App\Enums\Peranan;
use App\Models\Fasa;
use App\Models\Kitaran;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PengurusanKitaran
{
    public const SKOP = ['global' => 'Global', 'ptj' => 'PTJ Terpilih',
        'aktiviti' => 'Aktiviti Kewangan Terpilih', 'gabungan' => 'Kombinasi PTJ & Aktiviti Kewangan'];

    public function __construct(private JejakAudit $audit, private PengurusanPengguna $users) {}

    private function actor(): void
    {
        $actor = User::whereKey(Auth::id())->lockForUpdate()->first();
        abort_unless($actor && $actor->google_subject !== null && $this->users->bolehUrus($actor) &&
            session('abm_access_revision') === $actor->access_revision, 403);
    }

    public function ciptaKitaran(array $input): Kitaran
    {
        return DB::transaction(function () use ($input) {
            $this->actor();
            DB::select('select pg_advisory_xact_lock(41620101, 8011)');
            $data = Validator::make($input, ['tahun' => 'required|integer|between:1,9999|unique:kitarans,tahun'])->validate();
            $cycle = null;
            $this->audit->jalankan(Peranan::Superadmin, function () use ($data, &$cycle) {
                $cycle = Kitaran::create(['tahun' => $data['tahun']]);
                return new PeristiwaAudit('cycle.created', Kitaran::class, (string) $cycle->id,
                    ['exists' => false], ['tahun' => $cycle->tahun], 'Tambah Kitaran ABM.', kitaranId: $cycle->id);
            });
            return $cycle;
        });
    }

    private function validatePhase(Kitaran $cycle, array $input, ?Fasa $phase): array
    {
        $data = Validator::make($input, [
            'jenis' => ['required', Rule::in($phase ? [$phase->jenis] : ['bidaan_awal', 'pindaan'])],
            'tarikh_mula' => ['required', 'date_format:Y-m-d'],
            'tarikh_tamat' => ['required', 'date_format:Y-m-d', 'after_or_equal:tarikh_mula'],
            'jenis_skop' => ['required', Rule::in(array_keys(self::SKOP))],
            'ptj_ids' => ['sometimes', 'array'],
            'ptj_ids.*' => ['required', 'integer', 'distinct', 'exists:ptjs,id'],
            'aktiviti_ids' => ['sometimes', 'array'],
            'aktiviti_ids.*' => ['required', 'integer', 'distinct', 'exists:aktiviti_kewangans,id'],
            'pasangan' => ['sometimes', 'array'],
            'pasangan.*' => ['required', 'array:ptj_id,aktiviti_kewangan_id'],
            'pasangan.*.ptj_id' => ['required', 'integer', 'exists:ptjs,id'],
            'pasangan.*.aktiviti_kewangan_id' => ['required', 'integer', 'exists:aktiviti_kewangans,id'],
            'semakan' => [$phase ? 'required' : 'nullable', 'integer', 'min:0'],
            'reason' => [$phase ? 'required' : 'nullable', 'string', 'max:1000'],
        ])->validate();
        $p = array_map('intval', $data['ptj_ids'] ?? []);
        $a = array_map('intval', $data['aktiviti_ids'] ?? []);
        $pairs = array_map(fn ($row) => ['ptj_id' => (int) $row['ptj_id'], 'aktiviti_kewangan_id' => (int) $row['aktiviti_kewangan_id']], $data['pasangan'] ?? []);
        $shape = match ($data['jenis_skop']) {
            'global' => ! $p && ! $a && ! $pairs,
            'ptj' => count($p) > 0 && ! $a && ! $pairs,
            'aktiviti' => count($a) > 0 && ! $p && ! $pairs,
            'gabungan' => count($pairs) > 0 && ! $p && ! $a,
        };
        if (! $shape || count(array_unique(array_map(fn ($row) => json_encode($row), $pairs))) !== count($pairs)) {
            throw ValidationException::withMessages(['jenis_skop' => 'Pilih skop yang lengkap tanpa kombinasi berulang atau pilihan daripada jenis skop lain.']);
        }
        $data['scopes'] = match ($data['jenis_skop']) {
            'global' => [['ptj_id' => null, 'aktiviti_kewangan_id' => null]],
            'ptj' => array_map(fn ($id) => ['ptj_id' => $id, 'aktiviti_kewangan_id' => null], $p),
            'aktiviti' => array_map(fn ($id) => ['ptj_id' => null, 'aktiviti_kewangan_id' => $id], $a),
            'gabungan' => $pairs,
        };
        if ($phase && trim($data['reason']) === '') {
            throw ValidationException::withMessages(['reason' => 'Alasan perubahan diperlukan.']);
        }
        return $data;
    }

    private function snapshot(Fasa $phase): array
    {
        return ['kitaran_id' => $phase->kitaran_id, 'tahun' => $phase->tahun_kitaran,
            'jenis' => $phase->jenis, 'nombor_pindaan' => $phase->nombor_pindaan,
            'tarikh_mula' => $phase->tarikh_mula->toDateString(), 'tarikh_tamat' => $phase->tarikh_tamat->toDateString(),
            'dibuka' => $phase->dibuka, 'pernah_dibuka' => $phase->pernah_dibuka, 'semakan' => $phase->semakan,
            'jenis_skop' => $phase->jenis_skop, 'skop' => $phase->skop()->orderBy('id')->get()->map(fn ($row) => [
                'id' => $row->id, 'ptj_id' => $row->ptj_id, 'aktiviti_kewangan_id' => $row->aktiviti_kewangan_id,
            ])->all()];
    }

    private function saveScopes(Fasa $phase, array $rows): void
    {
        $wanted = collect($rows)->keyBy(fn ($row) => json_encode($row));
        foreach ($phase->skop()->get() as $existing) {
            $tuple = ['ptj_id' => $existing->ptj_id, 'aktiviti_kewangan_id' => $existing->aktiviti_kewangan_id];
            $key = json_encode($tuple);
            if ($existing->jenis_skop === $phase->jenis_skop && $wanted->has($key)) { $wanted->forget($key); }
            else { $existing->delete(); }
        }
        foreach ($wanted as $tuple) { $phase->skop()->create(['jenis_skop' => $phase->jenis_skop] + $tuple); }
    }

    public function simpanFasa(int $cycleId, ?int $phaseId, array $input): Fasa
    {
        return DB::transaction(function () use ($cycleId, $phaseId, $input) {
            $this->actor();
            $cycle = Kitaran::whereKey($cycleId)->lockForUpdate()->firstOrFail();
            $phase = $phaseId === null ? null : $cycle->fasa()->whereKey($phaseId)->lockForUpdate()->firstOrFail();
            $data = $this->validatePhase($cycle, $input, $phase);
            abort_if($phase && $phase->semakan !== (int) $data['semakan'], 409, 'Fasa telah berubah. Muat semula sebelum menyimpan.');
            $before = $phase ? $this->snapshot($phase) : ['exists' => false];
            $this->audit->jalankan(Peranan::Superadmin, function () use ($cycle, $data, $before, &$phase) {
                $created = $phase === null;
                if ($created) {
                    $number = $data['jenis'] === 'pindaan' ? (int) $cycle->fasa()->max('nombor_pindaan') + 1 : null;
                    $phase = new Fasa;
                    $phase->forceFill(['kitaran_id' => $cycle->id, 'tahun_kitaran' => $cycle->tahun,
                        'jenis' => $data['jenis'], 'nombor_pindaan' => $number,
                        'dibuka' => false, 'pernah_dibuka' => false, 'semakan' => 0]);
                } elseif ($phase->jenis_skop !== $data['jenis_skop']) {
                    // Composite FK protects scope shape; replace the old shape in this transaction.
                    $phase->skop()->delete();
                }
                $phase->forceFill(['tarikh_mula' => $data['tarikh_mula'], 'tarikh_tamat' => $data['tarikh_tamat'],
                    'jenis_skop' => $data['jenis_skop']]);
                if (! $created) { $phase->semakan++; }
                $phase->save();
                $this->saveScopes($phase, $data['scopes']);
                $after = $this->snapshot($phase->fresh());
                return new PeristiwaAudit($created ? 'phase.created' : 'phase.updated', Fasa::class, (string) $phase->id,
                    $before, $after, $created ? 'Tambah Fasa '.$phase->label().'.' : trim($data['reason']),
                    ! $created, kitaranId: $cycle->id, fasaRujukan: $phase->label());
            });
            return $phase->fresh();
        });
    }

    public function tindakan(int $cycleId, int $phaseId, string $action, array $input): Fasa
    {
        abort_unless(in_array($action, ['buka', 'tutup', 'buka-semula', 'lanjutkan'], true), 404);
        return DB::transaction(function () use ($cycleId, $phaseId, $action, $input) {
            $this->actor();
            $cycle = Kitaran::whereKey($cycleId)->lockForUpdate()->firstOrFail();
            $phase = $cycle->fasa()->whereKey($phaseId)->lockForUpdate()->firstOrFail();
            $data = Validator::make($input, ['semakan' => 'required|integer|min:0',
                'reason' => 'required|string|max:1000',
                'tarikh_tamat' => $action === 'lanjutkan' ? 'required|date_format:Y-m-d' : 'prohibited'])->validate();
            abort_if($phase->semakan !== (int) $data['semakan'], 409, 'Fasa telah berubah. Muat semula.');
            $reason = trim($data['reason']);
            if ($reason === '') { throw ValidationException::withMessages(['reason' => 'Alasan tindakan diperlukan.']); }
            $valid = match ($action) {
                'buka' => ! $phase->dibuka && ! $phase->pernah_dibuka,
                'buka-semula' => ! $phase->dibuka && $phase->pernah_dibuka,
                'tutup' => $phase->dibuka,
                'lanjutkan' => true,
            };
            abort_unless($valid, 409, 'Tindakan tidak lagi sesuai untuk status Fasa semasa.');
            if ($action === 'lanjutkan' && $data['tarikh_tamat'] <= $phase->tarikh_tamat->toDateString()) {
                throw ValidationException::withMessages(['tarikh_tamat' => 'Tarikh baharu mesti selepas tarikh tamat semasa.']);
            }
            $before = $this->snapshot($phase);
            $this->audit->jalankan(Peranan::Superadmin, function () use ($cycle, $phase, $action, $data, $reason, $before) {
                if ($action === 'lanjutkan') { $phase->tarikh_tamat = $data['tarikh_tamat']; }
                elseif ($action === 'tutup') { $phase->dibuka = false; }
                else { $phase->dibuka = true; $phase->pernah_dibuka = true; }
                $phase->semakan++; $phase->save();
                return new PeristiwaAudit('phase.'.$action, Fasa::class, (string) $phase->id,
                    $before, $this->snapshot($phase->fresh()), $reason, true,
                    kitaranId: $cycle->id, fasaRujukan: $phase->label());
            });
            return $phase->fresh();
        });
    }
}
