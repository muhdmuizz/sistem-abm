<?php

namespace Tests\Feature;

use App\Enums\Peranan;
use App\Enums\StatusBidaan;
use App\Models\AktivitiKewangan;
use App\Models\Anggaran;
use App\Models\Bidaan;
use App\Models\Fasa;
use App\Models\Kitaran;
use App\Models\PemetaanOs;
use App\Models\Ptj;
use App\Models\RekodAudit;
use App\Models\RujukanOs;
use App\Models\TetapanAksesPtj;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RawMaterialsSpreadsheetTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;
    private Ptj $owner;
    private Ptj $other;
    private AktivitiKewangan $activity;
    private Kitaran $cycle;
    private Fasa $phase;
    private RujukanOs $os;
    private RujukanOs $unconfigured;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2028-05-15 12:00:00', 'Asia/Kuala_Lumpur'));
        $this->owner = Ptj::create(['kod_ptj' => 'WORK-001', 'nama' => 'JPNT', 'kategori' => 'JPNT']);
        $this->other = Ptj::create(['kod_ptj' => 'WORK-002', 'nama' => 'PPD Kuala Nerus', 'kategori' => 'PPD']);
        $this->activity = AktivitiKewangan::create(['nama' => 'Pengurusan Pentadbiran Negeri', 'singkatan' => 'PPN']);
        $this->cycle = Kitaran::create(['tahun' => 2028]);
        $this->phase = Fasa::forceCreate(['kitaran_id' => $this->cycle->id, 'tahun_kitaran' => 2028,
            'jenis' => 'bidaan_awal', 'tarikh_mula' => '2028-05-01', 'tarikh_tamat' => '2028-05-31',
            'jenis_skop' => 'global', 'dibuka' => true, 'pernah_dibuka' => true]);
        $this->phase->skop()->create(['jenis_skop' => 'global']);
        $this->os = RujukanOs::create(['kod_objek' => '26000', 'butiran_perbelanjaan' => 'Bekalan Bahan Mentah dan Bahan-bahan Untuk Penyelenggaraan dan Pembaikan']);
        $this->unconfigured = RujukanOs::create(['kod_objek' => '26000', 'butiran_perbelanjaan' => 'Bahan Mentah Pukal']);
        $code=\App\Models\KodAktiviti::create(['aktiviti_kewangan_id'=>$this->activity->id,'kod_aktiviti'=>'010801']);
        foreach ([$this->os, $this->unconfigured] as $os) {
            PemetaanOs::create(['aktiviti_kewangan_id' => $this->activity->id, 'rujukan_os_id' => $os->id,
                'kod_aktiviti_id'=>$code->id,'singkatan_sumber' => 'PPN', 'butiran_asal' => $os->butiran_perbelanjaan, 'status_sumber' => 'Aktif']);
        }
        foreach ([null, $this->os->id] as $osId) {
            TetapanAksesPtj::create(['kategori_ptj' => 'JPNT', 'aktiviti_kewangan_id' => $this->activity->id,
                'rujukan_os_id' => $osId, 'dibenarkan' => true]);
        }
        $this->operator = User::factory()->create(['name' => 'Penyedia Anggaran']);
        $this->operator->forceFill(['is_active' => true, 'ptj_id' => $this->owner->id, 'google_subject' => 'workspace-fixture'])->save();
        $this->workspaceRole(Peranan::PenyediaBidaan);
        $this->workspaceRole(Peranan::PenyediaAnggaran);
        $this->actingAs($this->operator)->withSession(['abm_access_revision' => $this->operator->fresh()->access_revision,
            'abm_authenticated_at' => now()->timestamp, 'abm_last_activity' => now()->timestamp]);
    }

    private function workspaceRole(Peranan $role): void
    {
        $scope = ['peranan' => $role];
        if (in_array($role, [Peranan::PenyediaBidaan, Peranan::PenyediaAnggaran], true)) {
            $scope += ['ptj_id' => $this->owner->id, 'aktiviti_kewangan_id' => $this->activity->id];
        } elseif (in_array($role, [Peranan::KetuaBidaan, Peranan::PenyeliaBidaan], true)) {
            $scope['aktiviti_kewangan_id'] = $this->activity->id;
        } elseif ($role === Peranan::PemantauBidaan) {
            $scope['ptj_id'] = $this->owner->id;
        }
        $this->operator->penugasanPeranan()->create($scope);
    }

    private function workspaceIndex(): string
    {
        return route('bids.index', ['kitaran_id' => $this->cycle->id, 'ptj_id' => $this->owner->id]);
    }

    private function workspaceActivateUrl(): string
    {
        return route('bids.activate', [$this->cycle->id, $this->owner->id, $this->activity->id]);
    }

    private function workspaceBid(): Bidaan
    {
        return Bidaan::forceCreate(['kitaran_id' => $this->cycle->id, 'ptj_id' => $this->owner->id,
            'aktiviti_kewangan_id' => $this->activity->id, 'diaktifkan' => true, 'semakan' => 1])->fresh();
    }

    private function workspaceCapture(string $name, $response): void
    {
        if (getenv('ABM_UI_CAPTURE') !== '1') { return; }
        $directory = storage_path('app/ui-qa');
        if (! is_dir($directory)) { mkdir($directory, 0775, true); }
        // Static browser fixtures run on the real clock, not this test's future Kitaran clock.
        $html = preg_replace('/data-deadline="\d+"/', 'data-deadline="'.(time() + 3600).'"', $response->getContent());
        file_put_contents($directory.'/'.$name.'.html',
            str_replace('http://localhost', 'http://127.0.0.1:4173', $html));
        foreach (['abm.css', 'abm.js'] as $asset) { copy(public_path($asset), $directory.'/'.$asset); }
    }

    private function gridContext(): array
    {
        $bid=$this->workspaceBid();
        $estimate=Anggaran::forceCreate(['bidaan_id'=>$bid->id,'ptj_id'=>$bid->ptj_id,'rujukan_os_id'=>$this->os->id,'diaktifkan'=>true,'semakan'=>1]);
        $place=\App\Models\TempatBertugas::create(['ptj_id'=>$bid->ptj_id,'nama'=>'Sektor Pembangunan Murid','jenis'=>'sektor']);
        $this->operator->forceFill(['tempat_bertugas_id'=>$place->id])->save();
        $this->withCookie(session()->getName(),session()->getId());
        return [$bid,$estimate,$place];
    }
    private function gridUrl(Bidaan $bid,Anggaran $estimate): string { return route('estimates.editor',[$bid->id,$estimate->id]); }
    private function payload(Bidaan $bid,Anggaran $estimate,array $rows,array $deleted=[]): array
    {
        return ['semakan_bidaan'=>$bid->fresh()->semakan,'semakan_anggaran'=>$estimate->fresh()->semakan,'rekod'=>$rows,'padam'=>$deleted];
    }
    private function row($place,array $extra=[]): array {return array_replace(['tempat_bertugas_id'=>$place->id,'tujuan'=>'Bekalan Bengkel','kategori1'=>'Bukan Pukal','kategori2'=>'Alat Ganti Elektrik','kategori3'=>'Lampu Kalimantang (LED)','bil_unit'=>'2','kekerapan'=>null,'bil_bulan'=>null],$extra);}
    private function bulkRow($place,array $extra=[]): array {return $this->row($place,array_replace(['tujuan'=>'Van Hiace TTB 1234','kategori1'=>'Pukal','kategori2'=>'Petrol (RON95)','kategori3'=>'Kereta','bil_unit'=>'30','kekerapan'=>'3','bil_bulan'=>'12'],$extra));}
    private function bulkEstimate(Bidaan $bid): Anggaran {
        $mapping=$this->unconfigured->pemetaan()->sole();$code=\App\Models\KodAktiviti::create(['aktiviti_kewangan_id'=>$this->activity->id,'kod_aktiviti'=>'010809']);$mapping->update(['kod_aktiviti_id'=>$code->id]);
        TetapanAksesPtj::create(['kategori_ptj'=>'JPNT','aktiviti_kewangan_id'=>$this->activity->id,'rujukan_os_id'=>$this->unconfigured->id,'dibenarkan'=>true]);
        return Anggaran::forceCreate(['bidaan_id'=>$bid->id,'ptj_id'=>$bid->ptj_id,'rujukan_os_id'=>$this->unconfigured->id,'diaktifkan'=>true,'semakan'=>1]);
    }
    private function gridCapture(string $name,$response): void
    {
        $this->workspaceCapture($name,$response);
        if(getenv('ABM_UI_CAPTURE')==='1')foreach(['abm-money.js','anggaran-grid.css','anggaran-grid.js'] as $asset)copy(public_path($asset),storage_path('app/ui-qa/'.$asset));
    }
    public function test_all_42_reference_paths_units_and_original_non_fuel_rates_are_preserved(): void
    {
        $all=[];foreach([false,true] as $bulk){$d=new \App\Services\DefinisiBahanMentah(2028,$bulk);$count=0;foreach($d->references() as $a=>$sub)foreach($sub as $b=>$items)foreach($items as $c=>$ref){$all[implode('|',[$a,$b,$c])]=$ref;$count++;$this->assertSame('Bil. '.$ref['unit'],$ref['quantity_label']);$this->assertNotNull($ref['kadar']);}$this->assertSame($bulk?3:39,$count);}
        $this->assertCount(42,$all);$this->assertCount(5,array_filter($all,fn($r)=>$r['fuel']));
        $text=file_get_contents(base_path('docs/references/OS26000-bahan-mentah-reference.md'));$section=explode('**UNIT** is automatically',explode('### Approved UNIT reference mapping',$text)[1])[0];$unitCount=0;
        foreach(explode("\n",$section) as $line){$cells=array_map('trim',explode('|',trim($line,'| ')));if(count($cells)===4&&in_array($cells[0],['Pukal','Bukan Pukal'],true)){$this->assertSame($cells[3],$all[implode('|',array_slice($cells,0,3))]['unit']);$unitCount++;}}
        $this->assertSame(42,$unitCount);
        $h=fopen(base_path('docs/references/OS26000-bahan-mentah-kadar-source.csv'),'rb');fgetcsv($h,0,',','"','');$unchanged=0;
        while(($r=fgetcsv($h,0,',','"',''))!==false){$ref=$all[implode('|',array_slice($r,0,3))];if(!$ref['fuel']){$this->assertSame(0,\Brick\Math\BigDecimal::of($r[3])->compareTo($ref['kadar']));$unchanged++;}}fclose($h);$this->assertSame(37,$unchanged);
    }

    public function test_exact_formulas_and_explicit_fuel_planning_year_boundaries(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();
        foreach([2027,2028] as $year){$d=new \App\Services\DefinisiBahanMentah($year,true);$this->assertSame('4320',$d->preview($d->source($d->validate([$this->bulkRow($place)])[0])));$this->assertSame('5400',$d->preview($d->source($this->bulkRow($place,['kategori2'=>'Diesel','kategori3'=>'Van']))));}
        $d=new \App\Services\DefinisiBahanMentah(2028,false);$this->assertSame('30',$d->preview($d->source($this->row($place))));
        $r=$this->row($place,['kategori2'=>'Alat Ganti Kenderaan','kategori3'=>'Tyre Wax','bil_unit'=>'288.00823']);$this->assertSame('4320.12345',$d->preview($d->source($r)));
        $d=new \App\Services\DefinisiBahanMentah(2029,true);$this->assertNull($d->references()['Pukal']['Petrol (RON95)']['Kereta']['kadar']);$this->assertNull($d->preview($d->source($this->bulkRow($place))));
        $d=new \App\Services\DefinisiBahanMentah(2029,false);$this->assertSame('30',$d->preview($d->source($this->row($place))));$this->assertNull($d->references()['Bukan Pukal']['Alat Ganti Kenderaan']['Diesel (Bukan Pukal)']['kadar']);
    }

    public function test_http_both_editors_save_reopen_noop_edit_and_partial_fixtures(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$bulk=$this->bulkEstimate($bid);
        foreach([[$estimate,false,'raw'],[$bulk,true,'raw-bulk']] as [$e,$isBulk,$name]){
            $url=$this->gridUrl($bid,$e);$this->gridCapture($name.'-empty',$this->get($url)->assertOk());$r=$isBulk?$this->bulkRow($place):$this->row($place);
            $this->postJson($url,$this->payload($bid,$e,[$r]))->assertOk();$source=$e->butiran()->sole();$response=$this->get($url)->assertOk();$this->assertSame($isBulk?'4320':'30',$response->viewData('records')[0]['preview']);$this->gridCapture($name.'-populated',$response);
            $saved=(new \App\Services\DefinisiBahanMentah(2028,$isBulk))->project($source->data_sumber)+['tempat_bertugas_id'=>$place->id,'butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan];$audit=RekodAudit::count();$this->postJson($url,$this->payload($bid,$e,[$saved]))->assertOk();$this->assertSame($audit,RekodAudit::count());
            $saved['bil_unit']='2.12345';$this->postJson($url,$this->payload($bid,$e,[$saved]))->assertOk();$this->assertSame('2.12345',$source->fresh()->data_sumber->bil_unit);
            $partial=$isBulk?$this->bulkRow($place,['bil_bulan'=>null]):$this->row($place,['kategori3'=>null,'bil_unit'=>null]);$this->postJson($url,$this->payload($bid,$e,[$partial]))->assertOk();$response=$this->get($url)->assertOk();$this->assertNull($response->viewData('records')->last()['preview']);$this->gridCapture($name.'-partial',$response);
        }
        $this->assertDatabaseCount('butiran_anggaran',4);$this->assertDatabaseCount('hasil_pengiraan',0);
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PemantauBidaan);
        foreach([[$estimate,'raw'],[$bulk,'raw-bulk']] as [$e,$name]){$response=$this->get($this->gridUrl($bid,$e))->assertOk();$this->assertFalse($response->viewData('editable'));$this->gridCapture($name.'-readonly',$response);}
    }

    public function test_invalid_cross_branch_derived_inactive_and_money_injections_are_atomic(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$bulk=$this->bulkEstimate($bid);$url=$this->gridUrl($bid,$estimate);
        foreach([['kategori1'=>'Pukal'],['kategori2'=>'Diesel','kategori3'=>'Van'],['kategori3'=>'Unknown'],['kekerapan'=>'3'],['bil_bulan'=>'12'],['kadar'=>'999'],['unit'=>'Liter'],['basis'=>'Other'],['preview'=>'100'],['tarikh_mula'=>'2028-01-01'],['nama'=>'Officer'],['kategori1'=>[]],['kategori2'=>[]],['kategori3'=>[]],['bil_unit'=>'1,000'],['bil_unit'=>'1e2'],['bil_unit'=>[]]] as $extra)$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place),$this->row($place,$extra)]))->assertUnprocessable();
        $this->postJson($this->gridUrl($bid,$bulk),$this->payload($bid,$bulk,[$this->row($place)]))->assertUnprocessable();$this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('rekod_audit',0);
    }

    public function test_permissions_stale_phase_workplace_and_source_revision_guards(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$bulk=$this->bulkEstimate($bid);$url=$this->gridUrl($bid,$estimate);$data=$this->payload($bid,$estimate,[$this->row($place)]);$this->postJson($url,$data)->assertOk();$this->postJson($url,$data)->assertConflict();
        TetapanAksesPtj::where('rujukan_os_id',$this->unconfigured->id)->update(['dibenarkan'=>false]);$this->postJson($this->gridUrl($bid,$bulk),$this->payload($bid,$bulk,[$this->bulkRow($place)]))->assertNotFound();
        try{app(\App\Services\EditorAnggaran::class)->save($bulk->id,$this->payload($bid,$bulk,[$this->bulkRow($place)]));$this->fail('Revoked Pukal permission must fail.');}catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(403,$e->getStatusCode());}
        $this->assertSame(0,$bulk->butiran()->count());
        $outside=\App\Models\TempatBertugas::create(['ptj_id'=>$this->other->id,'nama'=>'Sektor Perancangan','jenis'=>'sektor']);$this->postJson($url,$this->payload($bid,$estimate,[$this->row($outside)]))->assertForbidden();
        $source=$estimate->butiran()->sole();$r=$this->row($place,['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan+1]);$this->postJson($url,$this->payload($bid,$estimate,[$r]))->assertConflict();
        $this->phase->forceFill(['dibuka'=>false])->save();$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertForbidden();
    }

    public function test_all_nine_existing_activity_mappings_keep_identities_and_parentage_separate(): void
    {
        $catalogue=app(\App\Services\KatalogDefinisiAnggaran::class);$maps=[false=>['Pengurusan Pentadbiran Negeri'=>'010801','Pendidikan Rendah'=>'030219','Pendidikan Awal'=>'030101','Pendidikan Menengah'=>'030301','Pengurusan Asrama'=>'030313'],true=>['Pengurusan Pentadbiran Negeri'=>'010809','Pendidikan Rendah'=>'030222','Pendidikan Menengah'=>'030304','Pengurusan Asrama'=>'030316']];
        foreach($maps as $bulk=>$activities)foreach($activities as $name=>$code){$a=AktivitiKewangan::firstOrCreate(['nama'=>$name],['singkatan'=>$name]);$bid=Bidaan::where('kitaran_id',$this->cycle->id)->where('ptj_id',$this->owner->id)->where('aktiviti_kewangan_id',$a->id)->first()??Bidaan::forceCreate(['kitaran_id'=>$this->cycle->id,'ptj_id'=>$this->owner->id,'aktiviti_kewangan_id'=>$a->id]);$k=\App\Models\KodAktiviti::firstOrCreate(['kod_aktiviti'=>$code],['aktiviti_kewangan_id'=>$a->id]);$os=$bulk?$this->unconfigured:$this->os;
            PemetaanOs::where('aktiviti_kewangan_id',$a->id)->where('rujukan_os_id',$os->id)->delete();PemetaanOs::create(['aktiviti_kewangan_id'=>$a->id,'rujukan_os_id'=>$os->id,'kod_aktiviti_id'=>$k->id,'singkatan_sumber'=>$name,'butiran_asal'=>$os->butiran_perbelanjaan,'status_sumber'=>'Aktif']);$d=$catalogue->untuk($os,$bid);$this->assertInstanceOf(\App\Services\DefinisiBahanMentah::class,$d);$this->assertSame($bulk?'Pukal':'Bukan Pukal',$d->branch());$this->assertSame([$d->branch()],array_keys($d->references()));
        }
        $awal=AktivitiKewangan::where('nama','Pendidikan Awal')->sole();$bid=Bidaan::where('aktiviti_kewangan_id',$awal->id)->sole();$this->assertNull($catalogue->untuk($this->unconfigured,$bid));
    }

    public function test_copy_and_audited_delete_keep_source_values_independent(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertOk();$source=$estimate->butiran()->sole();$copy=app(\App\Services\DrafButiranAnggaran::class)->salin($estimate->id,$source->id,['semakan_bidaan'=>$bid->fresh()->semakan,'semakan_anggaran'=>$estimate->fresh()->semakan,'semakan_butiran'=>$source->semakan]);$this->assertNotSame($source->id,$copy->id);$this->assertEquals($source->data_sumber,$copy->data_sumber);
        $this->postJson($url,$this->payload($bid,$estimate,[],[['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan]]))->assertOk();$this->assertNull($source->fresh());$this->assertNotNull($copy->fresh());$this->assertSame('source.draft_deleted',RekodAudit::orderByDesc('id')->first()->tindakan);
    }
}
