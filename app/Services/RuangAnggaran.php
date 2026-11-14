<?php

namespace App\Services;

use App\Enums\Peranan;
use App\Models\Anggaran;
use App\Models\Bidaan;
use App\Models\ButiranAnggaran;
use App\Models\RekodAudit;
use App\Models\TempatBertugas;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/** Read-only metadata queries. Source fields require approved OS adapters. */
class RuangAnggaran
{
    public function __construct(private RuangBidaPeruntukan $workspace, private SkopPeranan $roles) {}

    private function workplaces(User $actor, Bidaan $bid, Anggaran $estimate): Builder
    {
        abort_unless($estimate->bidaan_id === $bid->id &&
            $this->workspace->canView($actor, $bid->ptj_id, $bid->aktiviti_kewangan_id), 403);
        abort_unless($this->workspace->catalogue($actor, $bid)->contains('id', $estimate->rujukan_os_id), 404);

        $wide = false;
        foreach ([Peranan::PenyediaBidaan, Peranan::PemantauBidaan, Peranan::PenyediaAnggaran] as $role) {
            if ($this->roles->meliputi($actor->id, $role, $bid->ptj_id, $bid->aktiviti_kewangan_id)) {
                $wide = true;
                break;
            }
        }
        $workplaces = TempatBertugas::where('ptj_id', $bid->ptj_id);
        if (! $wide) {
            $own = $actor->tempat_bertugas_id;
            abort_unless($own && $this->roles->meliputi($actor->id, Peranan::PenyediaAnggaran,
                $bid->ptj_id, $bid->aktiviti_kewangan_id, $own), 403);
            $workplaces->whereKey($own);
        }

        return $workplaces;
    }

    public function sources(User $actor, Bidaan $bid, Anggaran $estimate): Builder
    {
        $workplaces = $this->workplaces($actor, $bid, $estimate);
        // Apply the scope before pagination/counting; never fetch opaque source JSON or outputs.
        return ButiranAnggaran::query()->where('anggaran_id', $estimate->id)->where('ptj_id', $bid->ptj_id)
            ->whereIn('tempat_bertugas_id', $workplaces->select('id'))
            ->select(['id', 'anggaran_id', 'tempat_bertugas_id', 'updated_at'])
            ->with('tempatBertugas:id,nama');
    }
    public const ACTIONS = [
        'estimate.activated' => 'OS diaktifkan',
        'source.draft_created' => 'Draf Butiran ditambah',
        'source.draft_updated' => 'Draf Butiran dipinda',
        'source.draft_duplicated' => 'Draf Butiran disalin',
        'source.draft_deleted' => 'Draf Butiran dipadam',
    ];

    public const ROLE_LABELS = [
        'superadmin' => 'Superadmin', 'ketua_bidaan' => 'Ketua Bidaan',
        'penyelia_bidaan' => 'Penyelia Bidaan', 'penyedia_bidaan' => 'Penyedia Bidaan',
        'penyedia_anggaran' => 'Penyedia Anggaran', 'pemantau_bidaan' => 'Pemantau Bidaan',
    ];

    public function history(User $actor, Bidaan $bid, Anggaran $estimate): Builder
    {
        $workplaces = $this->workplaces($actor, $bid, $estimate);
        // Entity lookup uses the existing type/identity index. Current row scope
        // prevents historical events from revealing rows no longer visible.
        $sourceIds = $this->sources($actor, $bid, $estimate)->without('tempatBertugas')
            ->select([])->selectRaw('CAST(butiran_anggaran.id AS text)');

        return RekodAudit::query()->where('ptj_id', $bid->ptj_id)
            ->where('aktiviti_kewangan_id', $bid->aktiviti_kewangan_id)->where('kitaran_id', $bid->kitaran_id)
            ->where(function ($events) use ($bid, $estimate, $sourceIds, $workplaces) {
                $events->where(function ($activation) use ($estimate) {
                    $activation->where('jenis_entiti', Anggaran::class)->where('id_entiti', (string) $estimate->id)
                        ->where('tindakan', 'estimate.activated');
                })->orWhere(function ($sources) use ($bid, $estimate, $sourceIds, $workplaces) {
                    $sources->where('jenis_entiti', ButiranAnggaran::class)
                        ->whereIn('id_entiti', $sourceIds)
                        ->whereRaw("rekod_audit.selepas @> jsonb_build_object('anggaran_id', CAST(? AS bigint), 'bidaan_id', CAST(? AS bigint))", [$estimate->id, $bid->id])
                        ->whereIn('tindakan', ['source.draft_created', 'source.draft_updated', 'source.draft_duplicated'])
                        ->whereIn('tempat_bertugas_id', (clone $workplaces)->select('id'))
                        ->whereExists(function ($after) use ($workplaces) {
                            $after->selectRaw('1')->fromSub((clone $workplaces)->select('id'), 'visible_after')
                                ->whereRaw("rekod_audit.selepas @> jsonb_build_object('tempat_bertugas_id', visible_after.id)");
                        })
                        ->where(function ($before) use ($workplaces) {
                            $before->where('tindakan', 'source.draft_created')
                                ->orWhereExists(function ($previous) use ($workplaces) {
                                    $previous->selectRaw('1')->fromSub((clone $workplaces)->select('id'), 'visible_before')
                                        ->whereRaw("rekod_audit.sebelum @> jsonb_build_object('tempat_bertugas_id', visible_before.id)");
                                });
                        });
                })->orWhere(function ($deleted) use ($bid,$estimate,$workplaces) {
                    $deleted->where('jenis_entiti',ButiranAnggaran::class)->where('tindakan','source.draft_deleted')
                        ->whereRaw("rekod_audit.selepas @> jsonb_build_object('exists', false, 'anggaran_id', CAST(? AS bigint), 'bidaan_id', CAST(? AS bigint))",[$estimate->id,$bid->id])
                        ->whereIn('tempat_bertugas_id',(clone $workplaces)->select('id'))
                        ->whereExists(function($old) use($workplaces) {
                            $old->selectRaw('1')->fromSub((clone $workplaces)->select('id'),'deleted_before')
                                ->whereRaw("rekod_audit.sebelum @> jsonb_build_object('tempat_bertugas_id', deleted_before.id)");
                        })->whereExists(function($after) use($workplaces) {
                            $after->selectRaw('1')->fromSub((clone $workplaces)->select('id'),'deleted_after')
                                ->whereRaw("rekod_audit.selepas @> jsonb_build_object('tempat_bertugas_id', deleted_after.id)");
                        });
                });
            })
            ->select(['id', 'pelaku_id', 'peranan', 'tindakan', 'jenis_entiti', 'id_entiti', 'berlaku_pada'])
            ->with('pelaku:id,name');
    }

}
