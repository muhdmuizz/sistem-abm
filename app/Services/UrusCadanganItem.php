<?php

namespace App\Services;

use App\Audit\PeristiwaAudit;
use App\Enums\Peranan;
use App\Enums\StatusBidaan;
use App\Models\Anggaran;
use App\Models\Bidaan;
use App\Models\CadanganItemAnggaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UrusCadanganItem
{
    public const RATE_RULE = '/^\d{1,16}(\.\d{1,2})?$/D';

    public function __construct(
        private KawalanPenyediaanKewangan $guard,
        private KatalogDefinisiAnggaran $catalogue,
        private JejakAudit $audit,
    ) {}

    public function categories(DefinisiAnggaran $definition): array
    {
        return match (true) {
            $definition instanceof DefinisiBahanMentah => $definition->references()['Bukan Pukal'] ?? [],
            $definition instanceof DefinisiAlatPejabat => $definition->references(),
            default => [],
        };
    }

    public function create(int $bidId, int $estimateId, array $input): void
    {
        DB::transaction(function () use ($bidId, $estimateId, $input) {
            $actor = $this->guard->actor();
            $context = Bidaan::findOrFail($bidId);
            $this->guard->prepareLocks($context->kitaran_id);
            $bid = Bidaan::whereKey($bidId)->lockForUpdate()->firstOrFail();
            $estimate = $bid->anggaran()->whereKey($estimateId)->lockForUpdate()->firstOrFail();
            abort_unless($bid->diaktifkan && $estimate->diaktifkan &&
                in_array($bid->status, [StatusBidaan::DalamPenyediaan, StatusBidaan::Dikembalikan], true), 409);

            $os = $this->guard->catalogue($bid, $estimate->rujukan_os_id);
            $definition = $this->catalogue->untuk($os, $bid);
            abort_unless($definition, 404);
            $categories = $this->categories($definition);
            abort_if($categories === [], 404);
            $data = validator($input, [
                'kategori' => ['required', 'string', Rule::in(array_keys($categories))],
                'nama' => 'required|string|max:255',
                'unit' => $definition instanceof DefinisiBahanMentah ? 'required|string|max:40' : 'prohibited',
                'kadar' => ['required', 'string', 'regex:'.self::RATE_RULE],
                'justifikasi' => 'required|string|max:2000',
                'reason' => 'nullable|string|max:1000',
            ])->validate();
            $authority = $this->guard->authorise($actor, Peranan::PenyediaAnggaran, $bid->kitaran_id,
                $bid->ptj_id, $bid->aktiviti_kewangan_id, $os->id, $data['reason'] ?? null);

            $normal = RujukanCadanganItem::normalise($data['nama']);
            $exists = collect(array_keys($categories[$data['kategori']]))->contains(
                fn ($name) => RujukanCadanganItem::normalise((string) $name) === $normal
            ) || CadanganItemAnggaran::where('kod_os', $os->kod_objek)
                ->where('kategori', $data['kategori'])->where('nama_normal', $normal)
                ->whereIn('status', ['menunggu', 'diluluskan'])->exists();
            if ($normal === '' || $exists) {
                throw ValidationException::withMessages(['nama' => $exists
                    ? 'Item ini sudah wujud atau sedang menunggu semakan.' : 'Masukkan nama item.']);
            }

            $attributes = [
                'anggaran_id' => $estimate->id, 'pemohon_id' => $actor->id, 'ptj_id' => $bid->ptj_id,
                'kod_os' => $os->kod_objek, 'kategori' => $data['kategori'], 'nama' => trim($data['nama']),
                'nama_normal' => $normal, 'unit' => $data['unit'] ?? 'Unit', 'kadar_cadangan' => $data['kadar'],
                'justifikasi' => $data['justifikasi'], 'status' => 'menunggu',
            ];

            $this->audit->jalankan(Peranan::PenyediaAnggaran, function () use ($attributes, $bid, $authority) {
                $item = new CadanganItemAnggaran;
                $item->forceFill($attributes)->save();
                return new PeristiwaAudit('item.proposed', CadanganItemAnggaran::class, (string) $item->id,
                    selepas: $attributes + ['pintasan' => $authority['override']], alasan: $authority['reason'] ?: $item->justifikasi,
                    ptjId: $bid->ptj_id, aktivitiId: $bid->aktiviti_kewangan_id, kitaranId: $bid->kitaran_id);
            });
        });
    }

    public function decide(int $id, array $input): void
    {
        DB::transaction(function () use ($id, $input) {
            $actor = $this->guard->actor();
            abort_unless($actor->penugasanPeranan->contains('peranan', Peranan::Superadmin), 403);
            $data = validator($input, [
                'status' => ['required', Rule::in(['diluluskan', 'ditolak'])],
                'kadar' => ['nullable', 'string', 'regex:'.self::RATE_RULE],
                'catatan' => 'required|string|max:2000',
            ])->validate();

            // Serialise publication with draft validation and the category duplicate check.
            DB::select('select pg_advisory_xact_lock(41620101, 8010)');
            $item = CadanganItemAnggaran::whereKey($id)->lockForUpdate()->firstOrFail();
            abort_unless($item->status === 'menunggu', 409, 'Cadangan ini telah disemak.');
            $changes = [
                'status' => $data['status'],
                'kadar_diluluskan' => $data['status'] === 'diluluskan' ? ($data['kadar'] ?? $item->kadar_cadangan) : null,
                'penyemak_id' => $actor->id,
                'catatan_keputusan' => $data['catatan'],
            ];

            $this->audit->jalankan(Peranan::Superadmin, function () use ($item, $changes) {
                $item->forceFill($changes + ['diputuskan_pada' => now()])->save();
                return new PeristiwaAudit('item.reviewed', CadanganItemAnggaran::class, (string) $item->id,
                    sebelum: ['status' => 'menunggu'], selepas: $changes, alasan: $changes['catatan_keputusan'],
                    ptjId: $item->ptj_id);
            });
        });
    }
}
