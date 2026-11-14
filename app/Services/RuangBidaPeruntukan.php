<?php

namespace App\Services;

use App\Enums\Peranan;
use App\Enums\StatusBidaan;
use App\Models\AktivitiKewangan;
use App\Models\Anggaran;
use App\Models\Bidaan;
use App\Models\Ptj;
use App\Models\RujukanOs;
use App\Models\User;
use Illuminate\Support\Collection;

/** Read/presentation eligibility only; mutations must use PengaktifanKewangan. */
class RuangBidaPeruntukan
{
    public const ROLES = [Peranan::Superadmin, Peranan::PenyediaBidaan, Peranan::PenyediaAnggaran, Peranan::PemantauBidaan];
    public const STATUS = ['dalam_penyediaan' => 'Dalam Penyediaan', 'dalam_semakan' => 'Dalam Semakan',
        'dikembalikan' => 'Dikembalikan', 'muktamad' => 'Muktamad'];

    // Transient presentation memoisation only; never used by the mutation service.
    private array $admins = [];
    private array $eligibility = [];
    private array $windows = [];
    private array $osPermissions = [];

    public function __construct(private SkopPeranan $roles, private AksesPeruntukan $permissions, private PembukaanFasa $phases) {}

    public function canUse(User $actor): bool
    {
        return $actor->is_active && $actor->ptj_id !== null &&
            $actor->penugasanPeranan()->whereIn('peranan', array_map(fn ($r) => $r->value, self::ROLES))->exists();
    }

    public function admin(User $actor): bool
    {
        return $this->admins[$actor->id] ??= $actor->penugasanPeranan()->where('peranan', Peranan::Superadmin)->exists();
    }

    public function ptjs(User $actor): Collection
    {
        abort_unless($this->canUse($actor), 403);
        $assignments = $actor->penugasanPeranan()->get();
        if ($assignments->contains('peranan', Peranan::Superadmin) ||
            $assignments->contains(fn ($a) => $a->peranan === Peranan::PemantauBidaan && $a->ptj_id === null)) {
            return Ptj::orderBy('nama')->get();
        }
        $ids = $assignments->filter(fn ($a) =>
            in_array($a->peranan, [Peranan::PenyediaBidaan, Peranan::PenyediaAnggaran, Peranan::PemantauBidaan], true) &&
            ($a->tempat_bertugas_id === null || $a->tempat_bertugas_id === $actor->tempat_bertugas_id))
            ->pluck('ptj_id')->filter()->unique();
        return Ptj::whereIn('id', $ids)->orderBy('nama')->get();
    }

    private function assigned(User $actor, Peranan $role, int $ptj, int $activity): bool
    {
        $key = implode('/', [$actor->id, $role->value, $ptj, $activity, $actor->tempat_bertugas_id]);
        return $this->eligibility[$key] ??= $this->roles->meliputi($actor->id, $role, $ptj, $activity, $actor->tempat_bertugas_id);
    }

    public function open(int $cycle, int $ptj, int $activity): bool
    {
        $key = $cycle.'/'.$ptj.'/'.$activity;
        return $this->windows[$key] ??= $this->phases->dibenarkan($cycle, $ptj, $activity);
    }

    private function osAllowed(int $ptj, int $activity, int $os): bool
    {
        $key = $ptj.'/'.$activity.'/'.$os;
        return $this->osPermissions[$key] ??= $this->permissions->bolehAksesOs($ptj, $activity, $os);
    }

    public function canView(User $actor, int $ptj, int $activity): bool
    {
        if (! $this->canUse($actor)) {
            return false;
        }
        if ($this->admin($actor)) {
            return true;
        }
        if (! $this->permissions->bolehAksesAktiviti($ptj, $activity)) {
            return false;
        }
        foreach ([Peranan::PenyediaBidaan, Peranan::PenyediaAnggaran, Peranan::PemantauBidaan] as $role) {
            if ($this->assigned($actor, $role, $ptj, $activity)) {
                return true;
            }
        }
        return false;
    }

    public function activities(User $actor, Ptj $ptj): Collection
    {
        return AktivitiKewangan::orderBy('nama')->get()->filter(fn ($a) => $this->canView($actor, $ptj->id, $a->id))->values();
    }

    public function catalogue(User $actor, Bidaan $bid): Collection
    {
        abort_unless($this->canView($actor, $bid->ptj_id, $bid->aktiviti_kewangan_id), 403);
        $admin = $this->admin($actor);
        return RujukanOs::untukPelaksanaan()
            ->whereHas('pemetaan', fn ($q) => $q->where('aktiviti_kewangan_id', $bid->aktiviti_kewangan_id))
            ->orderBy('kod_objek')->orderBy('butiran_perbelanjaan')->get()
            ->filter(fn ($os) => $admin || $this->osAllowed($bid->ptj_id, $bid->aktiviti_kewangan_id, $os->id))->values();
    }

    public function activation(User $actor, int $cycle, int $ptj, int $activity, ?Bidaan $bid, ?RujukanOs $os = null, ?Anggaran $estimate = null): array
    {
        $admin = $this->admin($actor);
        $role = $os ? Peranan::PenyediaAnggaran : Peranan::PenyediaBidaan;
        $assigned = $this->assigned($actor, $role, $ptj, $activity);
        $central = $os ? $this->osAllowed($ptj, $activity, $os->id) : $this->permissions->bolehAksesAktiviti($ptj, $activity);
        $open = $this->open($cycle, $ptj, $activity);
        $explanation = null;
        if ($bid && $bid->status !== StatusBidaan::DalamPenyediaan) {
            $explanation = 'Pengaktifan awal tidak tersedia untuk status Bidaan semasa.';
        } elseif ($os && ! $bid?->diaktifkan) {
            $explanation = 'Aktifkan Bidaan terlebih dahulu.';
        } elseif (($os && $estimate?->diaktifkan) || (! $os && $bid?->diaktifkan)) {
            $explanation = 'Rekod telah diaktifkan.';
        } elseif (($os && $estimate && $estimate->butiran()->exists()) || (! $os && $bid && $bid->anggaran()->exists())) {
            $explanation = 'Rekod sedia ada memerlukan semakan sebelum pengaktifan.';
        } elseif (! $assigned) {
            $explanation = $os ? 'Pengaktifan oleh Penyedia Anggaran.' : 'Pengaktifan oleh Penyedia Bidaan.';
        } elseif (! $admin && ! $central) {
            $explanation = 'Kebenaran pusat tidak membenarkan pengaktifan.';
        } elseif (! $admin && ! $open) {
            $explanation = 'Fasa belum dibuka atau di luar tempoh pembukaan.';
        }
        return ['allowed' => $explanation === null, 'explanation' => $explanation,
            'open' => $open, 'needs_reason' => $admin && (! $central || ! $open),
            'central' => $central, 'admin' => $admin];
    }
}
