<?php

namespace App\Services;

use App\Enums\Peranan;
use App\Enums\StatusBidaan;
use App\Models\Anggaran;
use App\Models\Bidaan;
use App\Models\TempatBertugas;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EditorAnggaran
{
    public function __construct(private KatalogDefinisiAnggaran $definitions, private DrafButiranAnggaran $drafts,
        private KawalanPenyediaanKewangan $guard, private SkopPeranan $roles, private RuangBidaPeruntukan $workspace) {}

    /** Presentation eligibility only. Live transactional guards independently authorise saves. */
    public function access(User $actor, Bidaan $bid, Anggaran $estimate): array
    {
        $places = TempatBertugas::where('ptj_id',$bid->ptj_id);
        if (!$this->roles->meliputi($actor->id,Peranan::PenyediaAnggaran,$bid->ptj_id,$bid->aktiviti_kewangan_id)) {
            $own=$actor->tempat_bertugas_id;
            if ($own && $this->roles->meliputi($actor->id,Peranan::PenyediaAnggaran,$bid->ptj_id,$bid->aktiviti_kewangan_id,$own)) {
                $places->whereKey($own);
            } else { $places->whereRaw('1=0'); }
        }
        $places=$places->orderBy('nama')->get(['id','nama']);
        $open=$this->workspace->open($bid->kitaran_id,$bid->ptj_id,$bid->aktiviti_kewangan_id);
        $central=app(AksesPeruntukan::class)->bolehAksesOs($bid->ptj_id,$bid->aktiviti_kewangan_id,$estimate->rujukan_os_id);
        $admin=$this->workspace->admin($actor);
        return ['places'=>$places,'editable'=>$places->isNotEmpty() && $bid->diaktifkan && $estimate->diaktifkan &&
            in_array($bid->status,[StatusBidaan::DalamPenyediaan,StatusBidaan::Dikembalikan],true) && ($admin || ($open && $central)),
            'needsReason'=>$admin && (!$open || !$central)];
    }

    public function save(int $estimateId, array $input, ?int $programId = null): void
    {
        DB::transaction(function () use ($estimateId,$input,$programId) {
            $actor=$this->guard->actor();
            $estimateContext=Anggaran::findOrFail($estimateId);
            $bidContext=Bidaan::findOrFail($estimateContext->bidaan_id);
            $this->guard->prepareLocks($bidContext->kitaran_id);
            $bid=Bidaan::whereKey($bidContext->id)->lockForUpdate()->firstOrFail();
            $estimate=$bid->anggaran()->whereKey($estimateId)->lockForUpdate()->firstOrFail();
            $os=$this->guard->catalogue($bid,$estimate->rujukan_os_id);
            $definition=$this->definitions->untuk($os,$bid);
            abort_unless($definition,404);
            $list=function($attribute,$value,$fail){if(!is_array($value)||!array_is_list($value)){$fail('Gunakan senarai baris tersusun.');}};
            $data=Validator::make(['editor'=>$input],[
                'editor'=>'required|array:semakan_bidaan,semakan_anggaran,reason,rekod,padam',
                'editor.semakan_bidaan'=>'required|integer|min:0','editor.semakan_anggaran'=>'required|integer|min:0',
                'editor.reason'=>'nullable|string|max:1000','editor.rekod'=>['present','array',$list],
                'editor.padam'=>['present','array',$list],'editor.padam.*'=>'required|array:butiran_id,semakan_butiran,sahkan_meter,semakan_meter',
                'editor.padam.*.sahkan_meter'=>'sometimes|boolean','editor.padam.*.semakan_meter'=>'sometimes|integer|min:0',
                'editor.padam.*.butiran_id'=>'required|integer|min:1|distinct','editor.padam.*.semakan_butiran'=>'required|integer|min:0',
            ])->validate()['editor'];
            $this->guard->current($bid,(int)$data['semakan_bidaan'],[StatusBidaan::DalamPenyediaan,StatusBidaan::Dikembalikan]);
            abort_unless($estimate->semakan===(int)$data['semakan_anggaran'] && $bid->diaktifkan && $estimate->diaktifkan,409);
            $program = $programId === null ? null : $bid->programAktiviti()->whereKey($programId)->lockForUpdate()->firstOrFail();
            if ($program) {
                abort_unless(PilihanOsProgram::supports($os) && in_array($estimate->rujukan_os_id,$program->os_terpilih ?? [],true),404);
                $this->guard->authorise($actor,Peranan::PenyediaAnggaran,$bid->kitaran_id,$bid->ptj_id,$bid->aktiviti_kewangan_id,$estimate->rujukan_os_id,$data['reason'] ?? null,$program->tempat_bertugas_id);
            }
            $rows=$definition->validate($data['rekod']);
            $ids=array_map('intval',array_column($rows,'butiran_id'));
            abort_if(array_intersect($ids,array_map('intval',array_column($data['padam'],'butiran_id'))),422);
            // Exact role/OS/Fasa checks also apply to an empty or no-op request.
            $this->guard->authorise($actor,Peranan::PenyediaAnggaran,$bid->kitaran_id,$bid->ptj_id,
                $bid->aktiviti_kewangan_id,$estimate->rujukan_os_id,$data['reason'] ?? null);
            foreach (array_merge($rows,$data['padam']) as $row) {
                if (!isset($row['butiran_id'])) { continue; }
                $existing=$estimate->butiran()->whereKey($row['butiran_id'])->lockForUpdate()->firstOrFail();
                abort_if($program && $existing->program_aktiviti_id !== $program->id,404);
                $this->guard->authorise($actor,Peranan::PenyediaAnggaran,$bid->kitaran_id,$bid->ptj_id,
                    $bid->aktiviti_kewangan_id,$estimate->rujukan_os_id,$data['reason'] ?? null,$existing->tempat_bertugas_id);
                abort_unless($definition->readable($existing->data_sumber),409,'Rekod asal memerlukan penyesuaian; tiada perubahan disimpan.');
            }
            $save=[];
            foreach($rows as $row) {
                $entry=['tempat_bertugas_id'=>$row['tempat_bertugas_id'],'data_sumber'=>$definition->source($row)];
                if(isset($row['butiran_id'])) {
                    $entry['butiran_id']=$row['butiran_id'];
                    $entry['semakan_butiran']=$row['semakan_butiran'] ?? -1;
                } else {
                    abort_if(isset($row['semakan_butiran']),422);
                    if(!$definition->hasInput($row)) { continue; }
                }
                if ($program) {
                    abort_unless((int)$entry['tempat_bertugas_id'] === $program->tempat_bertugas_id,422);
                    $entry['program_aktiviti_id'] = $program->id;
                }
                $save[]=$entry;
            }
            if($save) { $this->drafts->simpanBanyak($estimateId,['semakan_bidaan'=>$bid->semakan,
                'semakan_anggaran'=>$estimate->semakan,'reason'=>$data['reason'] ?? null,'rekod'=>$save]); }
            foreach($data['padam'] as $row) {
                $this->drafts->padam($estimateId,(int)$row['butiran_id'],['semakan_bidaan'=>$bid->fresh()->semakan,
                    'semakan_anggaran'=>$estimate->fresh()->semakan,'semakan_butiran'=>$row['semakan_butiran'],'reason'=>$data['reason'] ?? null,...array_intersect_key($row,array_flip(['sahkan_meter','semakan_meter']))]);
            }
        });
    }
}
