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

class TravelSpreadsheetTest extends TestCase
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
        $this->os = RujukanOs::create(['kod_objek' => '21000', 'butiran_perbelanjaan' => 'Perjalanan Sara Hidup (Umum)']);
        $this->unconfigured = RujukanOs::create(['kod_objek' => '15000', 'butiran_perbelanjaan' => 'Faedah-faedah Kewangan Yang Lain']);
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
    private function row($place,array $extra=[]): array
    {
        return array_replace(['tempat_bertugas_id'=>$place->id,'tujuan'=>'Urusan rasmi','nama'=>'AHMAD UJIAN','jawatan'=>'JURUTERA','gred'=>'J9','tarikh_mula'=>'2028-02-01','tarikh_tamat'=>'2028-02-29','kategori1'=>\App\Services\DefinisiPerjalanan::MEALS,'kategori2'=>null,'lokasi'=>'Semenanjung','kadar'=>null,'jarak'=>null,'bil_hari'=>'3','kekerapan'=>'2','bil_bulan'=>'12'],$extra);
    }
    private function component($place,string $category,?string $component=null,array $extra=[]): array
    {
        $r=['kategori1'=>$category,'kategori2'=>$component,'lokasi'=>'Semenanjung/ Sabah/ Sarawak/ WP Labuan','bil_hari'=>null,'kadar'=>'180.12345','jarak'=>null];
        if($category===\App\Services\DefinisiPerjalanan::HOTEL)$r=array_replace($r,['lokasi'=>'Semenanjung','bil_hari'=>'2','kadar'=>null]);
        if($component===\App\Services\DefinisiPerjalanan::VEHICLE)$r=array_replace($r,['jarak'=>'150','kadar'=>null]);
        return $this->row($place,array_replace($r,$extra));
    }
    private function gridCapture(string $name,$response): void
    {
        $this->workspaceCapture($name,$response);
        if(getenv('ABM_UI_CAPTURE')==='1')foreach(['abm-money.js','anggaran-grid.css','anggaran-grid.js'] as $asset)copy(public_path($asset),storage_path('app/ui-qa/'.$asset));
    }
    public function test_original_83_rows_shared_rate_matching_and_no_special_c_guess(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$d=new \App\Services\DefinisiPerjalanan(2028);$rows=$d->rateRows();$this->assertCount(83,$rows);$this->assertCount(6,$d->references());$this->assertCount(5,array_filter($rows,fn($r)=>$r[0]==='C'));$this->assertCount(3,array_filter($rows,fn($r)=>$r[3]===''));
        $jobs=app(\App\Services\RujukanJawatan::class)->grades();$mapped=0;
        foreach($rows as $r){if($r[0]==='C')continue;$chosen=null;foreach($jobs as $job=>$grades)foreach($grades as $grade){if($r[0]===''||($grade!=='VU7'&&preg_match('/^[A-Z]+([1-9]|1[0-5])$/D',$grade,$m)&&$m[1]===$r[0])){$chosen=[$job,$grade];break 2;}}
            if(!$chosen){$this->assertContains($r[0],['11','15']);continue;}
            $x=$this->component($place,$r[1],$r[1]===$d::COMBINED?$d::VEHICLE:null,['jawatan'=>$chosen[0],'gred'=>$chosen[1],'lokasi'=>$r[2],'kadar'=>$r[3]===''?'10':null,'bil_hari'=>in_array($r[1],[$d::MEALS,$d::HOTEL],true)?'2':null]);$ref=$d->rowReference($x);$this->assertTrue($ref['matched']);$this->assertSame($r[3]===''?null:$r[3],$ref['kadar']);$this->assertTrue($d->readable($d->source($d->validate([$x])[0])));$mapped++;
        }$this->assertGreaterThan(60,$mapped);
        $s=$d->source($this->row($place,['jawatan'=>'PENGARAH PENDIDIKAN NEGERI','gred'=>'VU7']));$this->assertNull($d->preview($s));$this->assertFalse($d->rowReference(get_object_vars($s))['matched']);
        foreach($jobs as $job=>$grades)foreach($grades as $grade){$x=$this->row($place,['jawatan'=>$job,'gred'=>$grade]);$ref=$d->rowReference($x);$this->assertSame($grade!=='VU7',$ref['matched']);}
    }
    public function test_seven_component_formulas_and_exact_manual_fare_precision(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$d=new \App\Services\DefinisiPerjalanan(2028);
        $cases=[[$this->row($place),'3240'],[$this->component($place,$d::HOTEL),'10560'],[$this->component($place,$d::COMBINED,$d::VEHICLE),'3600']];
        foreach([$d::TAXI,'Bayaran Tambang Kereta Api','Bayaran Tambang Kapal/ Bot','Bayaran Kapal Terbang'] as $c)$cases[]=[$this->component($place,$c===$d::TAXI?$d::COMBINED:$c,$c===$d::TAXI?$c:null),'4322.9628'];
        foreach($cases as [$x,$amount]){$s=$d->source($d->validate([$x])[0]);$this->assertSame($amount,$d->preview($s));$this->assertTrue($d->readable($s));}
        $this->assertSame('4680',$d->preview($d->source($this->row($place,['lokasi'=>'Sabah/ Sarawak/ WP Labuan']))));
        $this->assertSame('12000',$d->preview($d->source($this->component($place,$d::HOTEL,null,['lokasi'=>'Sabah/ Sarawak/ WP Labuan']))));
        $x=$this->component($place,'Bayaran Kapal Terbang',null,['kadar'=>'4,320.12345']);$s=$d->source($d->validate([$x])[0]);$this->assertSame('4320.12345',$s->kadar);$this->assertSame('103682.9628',$d->preview($s));
        foreach(['nama','jawatan','gred','kategori1','lokasi','bil_hari','kekerapan','bil_bulan'] as $k)$this->assertNull($d->preview($d->source($this->row($place,[$k=>null]))));
        $this->assertNull($d->preview($d->source($this->row($place,['nama'=>'   ']))));$this->assertSame('0',$d->preview($d->source($this->row($place,['bil_hari'=>'0']))));
    }
    public function test_inclusive_duration_independent_days_nights_and_months(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$d=new \App\Services\DefinisiPerjalanan(2028);
        foreach([['2028-02-01','2028-02-29','29'],['2028-02-29','2028-02-29','1'],['2028-01-01','2028-12-31','366']] as [$a,$b,$days]){
            $s=$d->source($d->validate([$this->row($place,['tarikh_mula'=>$a,'tarikh_tamat'=>$b])])[0]);$this->assertSame($days,$d->project($s)['tempoh']);$this->assertSame('3240',$d->preview($s));$this->assertSame('3',$s->bil_hari);$this->assertSame('12',$s->bil_bulan);
        }
    }
    public function test_save_reopen_edit_noop_partial_readonly_and_separate_components(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$d=new \App\Services\DefinisiPerjalanan(2028);$this->gridCapture('travel-empty',$this->get($url)->assertOk());
        $rows=[$this->row($place),$this->component($place,$d::HOTEL),$this->component($place,$d::COMBINED,$d::VEHICLE),$this->component($place,$d::COMBINED,$d::TAXI)];
        foreach(['Bayaran Tambang Kereta Api','Bayaran Tambang Kapal/ Bot','Bayaran Kapal Terbang'] as $c)$rows[]=$this->component($place,$c);
        $this->postJson($url,$this->payload($bid,$estimate,$rows))->assertOk();$this->assertSame(7,$estimate->butiran()->count());$response=$this->get($url)->assertOk();$this->assertSame(['3240','10560','3600','4322.9628','4322.9628','4322.9628','4322.9628'],$response->viewData('records')->pluck('preview')->all());$this->gridCapture('travel-populated',$response);
        $source=$estimate->butiran()->orderBy('id')->first();$saved=$this->row($place,['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan]);$audit=RekodAudit::count();$this->postJson($url,$this->payload($bid,$estimate,[$saved]))->assertOk();$this->assertSame($audit,RekodAudit::count());$this->assertNull($source->fresh()->data_sumber->kadar);
        $saved['bil_hari']='2.5';$this->postJson($url,$this->payload($bid,$estimate,[$saved]))->assertOk();$this->assertSame('2.5',$source->fresh()->data_sumber->bil_hari);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['bil_bulan'=>null])]))->assertOk();$response=$this->get($url)->assertOk();$this->assertNull($response->viewData('records')->last()['preview']);$this->gridCapture('travel-partial',$response);$this->assertDatabaseCount('hasil_pengiraan',0);
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PemantauBidaan);$response=$this->get($url)->assertOk();$this->assertFalse($response->viewData('editable'));$this->gridCapture('travel-readonly',$response);
    }
    public function test_invalid_inactive_derived_grade_category_location_and_date_injections_are_atomic(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$d=new \App\Services\DefinisiPerjalanan(2028);
        foreach([['kategori1'=>'X'],['kategori1'=>[]],['kategori2'=>[]],['kategori2'=>$d::TAXI],['lokasi'=>'Sabah'],['lokasi'=>[]],['gred'=>'DG9'],['gred'=>[]],['jawatan'=>[]],['bil_hari'=>[]],['bil_hari'=>'1,000'],['kekerapan'=>'1e2'],['bil_bulan'=>[]],['tarikh_mula'=>'2027-12-31'],['tarikh_tamat'=>'2029-01-01'],['tarikh_mula'=>'2028-02-30'],['tarikh_mula'=>[]],['tarikh_tamat'=>'2028-01-01'],['kadar'=>'99'],['jarak'=>'1'],['kategori3'=>null],['unit'=>'X'],['bil_unit'=>'1'],['jam'=>'1'],['tempoh'=>'1'],['preview'=>'100']] as $extra)$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place),$this->row($place,$extra)]))->assertUnprocessable();
        $this->postJson($url,$this->payload($bid,$estimate,[$this->component($place,$d::COMBINED,$d::TAXI,['jarak'=>'12'])]))->assertUnprocessable();
        $this->postJson($url,$this->payload($bid,$estimate,[$this->component($place,$d::COMBINED,$d::VEHICLE,['bil_hari'=>'2'])]))->assertUnprocessable();
        $this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('rekod_audit',0);
    }
    public function test_live_permission_phase_scope_source_and_bid_revision_guards(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$data=$this->payload($bid,$estimate,[$this->row($place)]);$this->postJson($url,$data)->assertOk();$this->postJson($url,$data)->assertConflict();
        $source=$estimate->butiran()->sole();$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan+1])]))->assertConflict();
        $outside=\App\Models\TempatBertugas::create(['ptj_id'=>$this->other->id,'nama'=>'Sektor Perancangan','jenis'=>'sektor']);$this->postJson($url,$this->payload($bid,$estimate,[$this->row($outside)]))->assertForbidden();
        TetapanAksesPtj::where('rujukan_os_id',$this->os->id)->update(['dibenarkan'=>false]);$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertNotFound();
        TetapanAksesPtj::where('rujukan_os_id',$this->os->id)->update(['dibenarkan'=>true]);$this->phase->forceFill(['dibuka'=>false])->save();$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertForbidden();
    }
    public function test_three_variants_exact_mappings_and_pertukaran_separation(): void
    {
        [$bid,$estimate]=$this->gridContext();$c=app(\App\Services\KatalogDefinisiAnggaran::class);$this->assertInstanceOf(\App\Services\DefinisiPerjalanan::class,$c->untuk($this->os,$bid));
        foreach(['Sukan','Kokurikulum'] as $suffix){$os=RujukanOs::create(['kod_objek'=>'21000','butiran_perbelanjaan'=>'Perjalanan Sara Hidup ('.$suffix.')']);$code=\App\Models\KodAktiviti::firstOrCreate(['aktiviti_kewangan_id'=>$this->activity->id,'kod_aktiviti'=>'010803']);$m=PemetaanOs::create(['aktiviti_kewangan_id'=>$this->activity->id,'rujukan_os_id'=>$os->id,'kod_aktiviti_id'=>$code->id,'singkatan_sumber'=>'PPN','butiran_asal'=>$os->butiran_perbelanjaan,'status_sumber'=>'Aktif']);$this->assertInstanceOf(\App\Services\DefinisiPerjalanan::class,$c->untuk($os,$bid));$code->forceFill(['kod_aktiviti'=>'010899'])->save();$this->assertNull($c->untuk($os,$bid));$m->delete();$code->delete();}
        $os=RujukanOs::create(['kod_objek'=>'21000','butiran_perbelanjaan'=>'Tuntutan Perbelanjaan Pertukaran']);$this->assertFalse((new \App\Services\DefinisiPerjalanan(2028))->supports($os));
        $this->os->pemetaan()->delete();$this->assertNull($c->untuk($this->os,$bid));
    }
    public function test_copy_switch_component_and_delete_preserve_independent_source(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertOk();$source=$estimate->butiran()->sole();
        $copy=app(\App\Services\DrafButiranAnggaran::class)->salin($estimate->id,$source->id,['semakan_bidaan'=>$bid->fresh()->semakan,'semakan_anggaran'=>$estimate->fresh()->semakan,'semakan_butiran'=>$source->semakan]);$this->assertNotSame($source->id,$copy->id);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->component($place,\App\Services\DefinisiPerjalanan::COMBINED,\App\Services\DefinisiPerjalanan::TAXI,['butiran_id'=>$copy->id,'semakan_butiran'=>$copy->semakan])]))->assertOk();$this->assertSame('3',$source->fresh()->data_sumber->bil_hari);$this->assertNull($copy->fresh()->data_sumber->bil_hari);
        $this->postJson($url,$this->payload($bid,$estimate,[],[['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan]]))->assertOk();$this->assertNull($source->fresh());$this->assertNotNull($copy->fresh());
    }
}
