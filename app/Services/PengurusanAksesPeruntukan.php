<?php

namespace App\Services;

use App\Audit\PeristiwaAudit;
use App\Enums\Peranan;
use App\Models\AktivitiKewangan;
use App\Models\Ptj;
use App\Models\RujukanOs;
use App\Models\TetapanAksesPtj;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PengurusanAksesPeruntukan
{
    public const KATEGORI = ['jpnt' => 'JPNT', 'ppd' => 'PPD', 'smk-ptj' => 'SMK PTJ'];

    public function __construct(private JejakAudit $audit, private PengurusanPengguna $users) {}

    public function sasaran(string $kind, string $key): array
    {
        if ($kind === 'kategori') {
            abort_unless(isset(self::KATEGORI[$key]), 404);
            return ['kind' => $kind, 'key' => $key, 'kategori' => self::KATEGORI[$key],
                'ptj_id' => null, 'label' => 'Lalai Kategori — '.self::KATEGORI[$key]];
        }
        abort_unless($kind === 'ptj' && ctype_digit($key), 404);
        $ptj = Ptj::findOrFail($key);
        return ['kind' => $kind, 'key' => (string) $ptj->id, 'kategori' => $ptj->kategori,
            'ptj_id' => $ptj->id, 'label' => $ptj->nama];
    }

    public function keadaan(array $target, AktivitiKewangan $activity): array
    {
        $catalogue = RujukanOs::untukPelaksanaan()
            ->whereHas('pemetaan', fn ($q) => $q->where('aktiviti_kewangan_id', $activity->id))
            ->orderBy('kod_objek')->orderBy('butiran_perbelanjaan')->orderBy('id')->get();
        $settings = TetapanAksesPtj::where('aktiviti_kewangan_id', $activity->id)
            ->where(function ($q) use ($target) {
                $q->where('kategori_ptj', $target['kategori']);
                if ($target['ptj_id'] !== null) { $q->orWhere('ptj_id', $target['ptj_id']); }
            })->orderBy('id')->get();
        $defaults = $settings->where('kategori_ptj', $target['kategori'])->keyBy(fn ($row) => $row->rujukan_os_id ?? 'activity');
        $own = $target['kind'] === 'kategori' ? $defaults :
            $settings->where('ptj_id', $target['ptj_id'])->keyBy(fn ($row) => $row->rujukan_os_id ?? 'activity');
        $resolve = function ($key) use ($defaults, $own, $target) {
            $record = $own->get($key);
            $inherited = $target['kind'] === 'ptj' ? $defaults->get($key)?->dibenarkan : null;
            return ['configured' => $record !== null, 'allowed' => $record?->dibenarkan ?? $inherited,
                'inherited' => $inherited, 'effective' => $record?->dibenarkan ?? $inherited];
        };
        $parent = $resolve('activity');
        $rows = $catalogue->map(function ($os) use ($resolve) {
            return ['os' => $os] + $resolve($os->id);
        });
        // Include category dependencies and stored overrides, not merely rendered checkboxes.
        $snapshot = $settings->map(fn ($row) => [
            'id' => $row->id, 'kategori_ptj' => $row->kategori_ptj, 'ptj_id' => $row->ptj_id,
            'rujukan_os_id' => $row->rujukan_os_id, 'dibenarkan' => $row->dibenarkan,
            'updated_at' => $row->getRawOriginal('updated_at'),
        ])->all();
        $revision = hash('sha256', json_encode([
            'target' => $target, 'activity_id' => $activity->id, 'settings' => $snapshot,
            'catalogue' => $catalogue->map(fn ($os) => [$os->id, $os->kod_objek, $os->butiran_perbelanjaan])->all(),
        ], JSON_THROW_ON_ERROR));
        return ['target' => $target, 'activity' => $activity, 'parent' => $parent, 'rows' => $rows,
            'revision' => $revision, 'snapshot' => $snapshot,
            'count' => $parent['effective'] === true ? $rows->where('effective', true)->count() : 0];
    }

    public function simpan(string $kind, string $key, int $activityId, array $input): void
    {
        DB::transaction(function () use ($kind, $key, $activityId, $input) {
            $actor = User::whereKey(Auth::id())->lockForUpdate()->first();
            abort_unless($actor && $actor->google_subject !== null && $this->users->bolehUrus($actor) &&
                session('abm_access_revision') === $actor->access_revision, 403);
            // Category edits and inheriting PTJ edits must not interleave their dependency check/write.
            DB::select('select pg_advisory_xact_lock(41620101, 8010)');
            $target = $this->sasaran($kind, $key);
            $activity = AktivitiKewangan::findOrFail($activityId);
            $state = $this->keadaan($target, $activity);
            $data = Validator::make($input, [
                'revision' => ['required', 'string', 'regex:/\A[a-f0-9]{64}\z/'],
                'reason' => ['required', 'string', 'max:1000'],
                'activity' => ['required', 'array:configured,allowed'],
                'activity.configured' => ['required', 'boolean'],
                'activity.allowed' => ['required', 'boolean'],
                'settings' => ['present', 'array'],
                'settings.*' => ['required', 'array:configured,allowed'],
                'settings.*.configured' => ['required', 'boolean'],
                'settings.*.allowed' => ['required', 'boolean'],
            ])->validate();
            abort_unless(hash_equals($state['revision'], $data['revision']), 409,
                'Tetapan atau rujukan telah berubah. Muat semula sebelum menyimpan.');
            $expected = $state['rows']->map(fn ($row) => (string) $row['os']->id)->all();
            $received = array_map('strval', array_keys($data['settings']));
            sort($expected); sort($received);
            if ($expected !== $received) {
                throw ValidationException::withMessages(['settings' => 'Senarai OS tidak lengkap atau mengandungi OS yang tidak dibenarkan. Muat semula.']);
            }
            // Category activity defaults are explicit on/off toggles; only PTJ overrides inherit.
            if ($kind === 'kategori' && ! $data['activity']['configured']) {
                throw ValidationException::withMessages(['activity' => 'Tetapkan Benarkan atau Sekat bagi Aktiviti Kewangan kategori.']);
            }
            $reason = trim($data['reason']);
            if ($reason === '') { throw ValidationException::withMessages(['reason' => 'Alasan perubahan diperlukan.']); }
            $before = $state['snapshot'];
            $this->audit->jalankan(Peranan::Superadmin, function () use ($target, $activity, $data, $reason, $before) {
                $this->write($target, $activity->id, null, $data['activity']);
                foreach ($data['settings'] as $osId => $setting) {
                    $this->write($target, $activity->id, (int) $osId, $setting);
                }
                $after = $this->keadaan($target, $activity);
                return new PeristiwaAudit(
                    $target['kind'] === 'kategori' ? 'access.category_permissions_updated' : 'access.ptj_permissions_updated',
                    TetapanAksesPtj::class, $target['kind'].':'.$target['key'].'/activity:'.$activity->id,
                    ['settings' => $before], ['settings' => $after['snapshot']], $reason, true,
                    ptjId: $target['ptj_id'], aktivitiId: $activity->id,
                );
            });
        });
    }

    private function write(array $target, int $activityId, ?int $osId, array $setting): void
    {
        $identity = ['kategori_ptj' => $target['kind'] === 'kategori' ? $target['kategori'] : null,
            'ptj_id' => $target['ptj_id'], 'aktiviti_kewangan_id' => $activityId, 'rujukan_os_id' => $osId];
        $record = TetapanAksesPtj::where($identity)->first();
        if (! $setting['configured']) {
            $record?->delete(); // A PTJ reset restores per-setting inheritance; an unset OS category stays unconfigured.
            return;
        }
        $allowed = (bool) $setting['allowed'];
        if ($record === null) { TetapanAksesPtj::create($identity + ['dibenarkan' => $allowed]); }
        elseif ($record->dibenarkan !== $allowed) { $record->update(['dibenarkan' => $allowed]); }
    }
}
