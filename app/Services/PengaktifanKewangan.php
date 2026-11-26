<?php

namespace App\Services;

use App\Audit\PeristiwaAudit;
use App\Enums\Peranan;
use App\Enums\StatusBidaan;
use App\Models\AktivitiKewangan;
use App\Models\Anggaran;
use App\Models\Bidaan;
use App\Models\Ptj;
use Illuminate\Support\Facades\DB;

/** Initial preparation only. No source writes, completeness, calculations or submission transitions. */
class PengaktifanKewangan
{
    public const PAYROLL_OS = ['11000', '12000', '13000'];

    public function __construct(
        private JejakAudit $audit,
        private KawalanPenyediaanKewangan $guard,
    ) {}

    private function state(Bidaan $bid, ?Anggaran $estimate = null): array
    {
        $state = ['bidaan_id' => $bid->id, 'ptj_id' => $bid->ptj_id,
            'aktiviti_kewangan_id' => $bid->aktiviti_kewangan_id, 'kitaran_id' => $bid->kitaran_id,
            'diaktifkan' => $bid->diaktifkan, 'status' => $bid->status->value, 'semakan' => $bid->semakan];
        if ($estimate !== null) {
            $state['anggaran'] = ['id' => $estimate->id, 'rujukan_os_id' => $estimate->rujukan_os_id,
                'diaktifkan' => $estimate->diaktifkan, 'semakan' => $estimate->semakan];
        }
        return $state;
    }

    public function aktifkanBidaan(int $cycleId, int $ptjId, int $activityId, int $expectedRevision, ?string $reason = null): Bidaan
    {
        return DB::transaction(function () use ($cycleId, $ptjId, $activityId, $expectedRevision, $reason) {
            $actor = $this->guard->actor();
            $this->guard->prepareLocks($cycleId);
            Ptj::findOrFail($ptjId);
            AktivitiKewangan::findOrFail($activityId);
            $authority = $this->guard->authorise($actor, Peranan::PenyediaBidaan, $cycleId, $ptjId, $activityId, null, $reason);
            $bid = Bidaan::where('kitaran_id', $cycleId)->where('ptj_id', $ptjId)
                ->where('aktiviti_kewangan_id', $activityId)->lockForUpdate()->first();
            if ($bid) {
                $this->guard->current($bid, $expectedRevision);
                if ($bid->diaktifkan) {
                    return $bid;
                }
                abort_if($bid->anggaran()->exists(), 409, 'Bidaan belum aktif mempunyai rekod OS; semakan pentadbiran diperlukan.');
            } else {
                abort_unless($expectedRevision === 0, 409, 'Konteks Bidaan telah berubah.');
            }
            $before = $bid ? $this->state($bid) : ['exists' => false];
            $this->audit->jalankan(Peranan::PenyediaBidaan, function () use (&$bid, $cycleId, $ptjId, $activityId, $authority, $before) {
                if ($bid === null) {
                    $bid = new Bidaan;
                    $bid->forceFill(['kitaran_id' => $cycleId, 'ptj_id' => $ptjId,
                        'aktiviti_kewangan_id' => $activityId, 'semakan' => 0, 'status' => StatusBidaan::DalamPenyediaan]);
                }
                $bid->diaktifkan = true;
                $bid->semakan++;
                $bid->save();
                return new PeristiwaAudit('bid.activated', Bidaan::class, (string) $bid->id,
                    $before, $this->state($bid) + ['pintasan' => $authority['override']],
                    $authority['reason'] ?: 'Aktifkan Bidaan.', ptjId: $ptjId, aktivitiId: $activityId, kitaranId: $cycleId);
            });
            return $bid->fresh();
        });
    }

    public function aktifkanOs(int $bidId,int $osId,int $expectedBidRevision,?string $reason=null): Anggaran
    {
        return DB::transaction(function()use($bidId,$osId,$expectedBidRevision,$reason){
            $links=app(PautanFotostat::class);$os=\App\Models\RujukanOs::findOrFail($osId);
            abort_if($links->meter($os),409,'Aktifkan Bacaan Meter melalui OS24000 Sewaan Mesin Fotostat dan Riso.');
            if(!$links->rental($os))return $this->aktifkanSatuOs($bidId,$osId,$expectedBidRevision,$reason);
            $this->guard->actor();$context=Bidaan::findOrFail($bidId);$this->guard->prepareLocks($context->kitaran_id);
            $bid=Bidaan::whereKey($bidId)->lockForUpdate()->firstOrFail();$this->guard->current($bid,$expectedBidRevision,[StatusBidaan::DalamPenyediaan,StatusBidaan::Dikembalikan]);
            [$r,$m]=$links->pair($bid);$links->authority($bid,$reason);
            foreach([$r,$m] as $ref)abort_if($bid->anggaran()->where('rujukan_os_id',$ref->id)->whereHas('butiran',fn($q)=>$q->whereHas('hasilPengiraan'))->exists(),409,'Pasangan berpengiraan memerlukan semakan pengiraan.');
            $rental=$this->aktifkanSatuOs($bidId,$r->id,$bid->semakan,$reason,true);
            $this->aktifkanSatuOs($bidId,$m->id,$bid->fresh()->semakan,$reason,true);
            return $rental->fresh();
        });
    }

    public function aktifkanGaji(int $bidId, int $expectedRevision, ?string $reason = null): void
    {
        DB::transaction(function () use ($bidId, $expectedRevision, $reason) {
            $this->guard->actor();
            $context = Bidaan::findOrFail($bidId);
            $this->guard->prepareLocks($context->kitaran_id);
            $bid = Bidaan::whereKey($bidId)->lockForUpdate()->firstOrFail();
            $this->guard->current($bid, $expectedRevision);
            $references = \App\Models\RujukanOs::untukPelaksanaan()
                ->whereIn('kod_objek', self::PAYROLL_OS)
                ->whereHas('pemetaan', fn ($query) => $query->where('aktiviti_kewangan_id', $bid->aktiviti_kewangan_id))
                ->orderBy('kod_objek')->get();
            abort_unless($references->pluck('kod_objek')->all() === self::PAYROLL_OS, 409,
                'Pemetaan OS Gaji & Emolumen belum lengkap.');

            foreach ($references as $reference) {
                $this->aktifkanSatuOs($bidId, $reference->id, $bid->refresh()->semakan, $reason);
            }
        });
    }

    public function nyahaktifkanFotostat(int $bidId,int $revision,?string $reason=null): void
    {
        DB::transaction(function()use($bidId,$revision,$reason){
            $this->guard->actor();$context=Bidaan::findOrFail($bidId);$this->guard->prepareLocks($context->kitaran_id);
            $bid=Bidaan::whereKey($bidId)->lockForUpdate()->firstOrFail();
            $this->guard->current($bid,$revision,[StatusBidaan::DalamPenyediaan,StatusBidaan::Dikembalikan]);
            $links=app(PautanFotostat::class);$links->authority($bid,$reason);
            [$r,$m]=$links->pair($bid);
            foreach([$r,$m] as $os){
                $estimate=$bid->anggaran()->where('rujukan_os_id',$os->id)->lockForUpdate()->first();
                if(!$estimate?->diaktifkan)continue;
                $before=$this->state($bid,$estimate);
                $this->audit->jalankan(Peranan::PenyediaAnggaran,function()use($bid,$estimate,$before,$reason){
                    $estimate->diaktifkan=false;$estimate->semakan++;$estimate->save();$bid->semakan++;$bid->save();
                    return new PeristiwaAudit('estimate.deactivated',Anggaran::class,(string)$estimate->id,$before,$this->state($bid,$estimate),$reason?:'Nyahaktifkan pasangan Fotostat; draf dikekalkan.',ptjId:$bid->ptj_id,aktivitiId:$bid->aktiviti_kewangan_id,kitaranId:$bid->kitaran_id);
                });
            }
        });
    }

    private function aktifkanSatuOs(int $bidId, int $osId, int $expectedBidRevision, ?string $reason = null,bool $retained=false): Anggaran
    {
        return DB::transaction(function () use ($bidId, $osId, $expectedBidRevision, $reason,$retained) {
            $actor = $this->guard->actor();
            $cycleId = Bidaan::whereKey($bidId)->value('kitaran_id');
            abort_if($cycleId === null, 404);
            $this->guard->prepareLocks($cycleId);
            $bid = Bidaan::whereKey($bidId)->where('kitaran_id', $cycleId)->lockForUpdate()->firstOrFail();
            $authority = $this->guard->authorise($actor, Peranan::PenyediaAnggaran, $cycleId, $bid->ptj_id, $bid->aktiviti_kewangan_id, $osId, $reason);
            $this->guard->current($bid, $expectedBidRevision,$retained?[StatusBidaan::DalamPenyediaan,StatusBidaan::Dikembalikan]:[StatusBidaan::DalamPenyediaan]);
            abort_unless($bid->diaktifkan, 409, 'Aktifkan Bidaan terlebih dahulu.');
            // Superadmin overrides permission/time scope, never catalogue exclusion or activity parentage.
            $this->guard->catalogue($bid, $osId);
            $estimate = $bid->anggaran()->where('rujukan_os_id', $osId)->lockForUpdate()->first();
            if ($estimate?->diaktifkan) {
                return $estimate;
            }
            abort_if(!$retained && $estimate && $estimate->butiran()->exists(), 409,
                'Aktifkan semula OS berdata memerlukan pengesahan dan pengiraan terkini.');
            $before = $this->state($bid, $estimate);
            $this->audit->jalankan(Peranan::PenyediaAnggaran, function () use ($bid, $osId, &$estimate, $before, $authority) {
                if ($estimate === null) {
                    $estimate = new Anggaran;
                    $estimate->forceFill(['bidaan_id' => $bid->id, 'ptj_id' => $bid->ptj_id,
                        'rujukan_os_id' => $osId, 'semakan' => 0]);
                }
                $estimate->diaktifkan = true;
                $estimate->semakan++;
                $estimate->save();
                $bid->semakan++;
                $bid->save();
                return new PeristiwaAudit('estimate.activated', Anggaran::class, (string) $estimate->id,
                    $before, $this->state($bid, $estimate) + ['pintasan' => $authority['override']],
                    $authority['reason'] ?: 'Aktifkan OS.', ptjId: $bid->ptj_id,
                    aktivitiId: $bid->aktiviti_kewangan_id, kitaranId: $bid->kitaran_id);
            });
            return $estimate->fresh();
        });
    }
}
