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

class PertukaranSpreadsheetTest extends TestCase
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
        $this->os = RujukanOs::create(['kod_objek' => '21000', 'butiran_perbelanjaan' => 'Tuntutan Perbelanjaan Pertukaran']);
        $this->unconfigured = RujukanOs::create(['kod_objek' => '29000', 'butiran_perbelanjaan' => 'Pakej (Mesyuarat/ Bengkel/ Kursus)']);
        $code=\App\Models\KodAktiviti::create(['aktiviti_kewangan_id'=>$this->activity->id,'kod_aktiviti'=>'010805']);
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
    private function row($place,array $extra=[]): array {
        $jobs=app(\App\Services\RujukanJawatan::class)->grades();
        $job=array_key_first(array_filter($jobs,fn($grades)=>in_array('DG9',$grades,true)));
        return array_replace(['tempat_bertugas_id'=>$place->id,'nama'=>'AHMAD UJIAN','jawatan'=>$job,'gred'=>'DG9','kategori1'=>'Tidak Berpindah Rumah','kategori2'=>'Bujang','kategori3'=>'Elaun Perjalanan Kenderaan/ Tambang Pengangkutan Awam','komponen'=>'Elaun Perjalanan Kenderaan','padanan'=>'Semenanjung/ Sabah/ Sarawak/ WP Labuan','jarak'=>'4320','kadar'=>null],$extra);
    }
    private function manual($place,array $extra=[]): array {return $this->row($place,array_replace(['kategori1'=>'Berpindah Rumah','kategori3'=>'Tambang Perjalanan','komponen'=>'Bayaran Kapal Terbang','jarak'=>null,'kadar'=>'1234.567'],$extra));}
    private function meal($place,array $extra=[]): array {return $this->row($place,array_replace(['kategori1'=>'Berpindah Rumah','kategori3'=>'Elaun Makan','komponen'=>'Elaun Makan','padanan'=>'Semenanjung','jarak'=>null],$extra));}
    private function gridCapture(string $name,$response): void
    {
        $this->workspaceCapture($name,$response);
        if(getenv('ABM_UI_CAPTURE')==='1')foreach(['abm-money.js','anggaran-grid.css','anggaran-grid.js'] as $asset)copy(public_path($asset),storage_path('app/ui-qa/'.$asset));
    }
    public function test_eight_paths_preserve_83_rates_numeric_grades_and_no_special_c_mapping(): void
    {
        $d=new \App\Services\DefinisiPertukaran;$refs=$d->references();$paths=0;
        foreach($refs as $families)foreach($families as $claims)$paths+=count($claims);
        $this->assertSame(8,$paths);$this->assertCount(83,$d->rateRows());
        $this->assertSame(5,count(array_filter($d->rateRows(),fn($r)=>$r[0]==='C')));
        $this->assertSame(3,count(array_filter($d->rateRows(),fn($r)=>$r[3]==='')));
        [$bid,$estimate,$place]=$this->gridContext();
        $row=$this->meal($place);$this->assertSame('45.00',$d->rowReference($row)['kadar']);
        $row['padanan']='Sabah/ Sarawak/ WP Labuan';$this->assertSame('65.00',$d->rowReference($row)['kadar']);
        $jobs=app(\App\Services\RujukanJawatan::class)->grades();$row['jawatan']=array_key_first(array_filter($jobs,fn($grades)=>in_array('VU7',$grades,true)));$row['gred']='VU7';
        $this->assertFalse($d->rowReference($row)['matched']);$this->assertNull($d->preview($d->source($row)));
        $row['kadar']='115';$this->postJson($this->gridUrl($bid,$estimate),$this->payload($bid,$estimate,[$row]))->assertUnprocessable()->assertJsonValidationErrors('rekod.0.kadar');
        $this->assertDatabaseCount('butiran_anggaran',0);
    }

    public function test_all_eight_paths_and_components_use_only_approved_multipliers(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$d=new \App\Services\DefinisiPertukaran;
        foreach(['Bujang','Keluarga'] as $family){
            $r=$this->row($place,['kategori2'=>$family]);$this->assertSame('4320',$d->preview($d->source($d->validate([$r])[0])));
            $r['komponen']='Tambang Pengangkutan Awam';$r['jarak']=null;$this->assertSame('1',$d->preview($d->source($d->validate([$r])[0])));
            $this->assertSame('45',$d->preview($d->source($d->validate([$this->meal($place,['kategori2'=>$family])])[0])));
            $r=$this->meal($place,['kategori2'=>$family,'kategori3'=>'Bayaran Sewa Hotel/ Elaun Lojing','komponen'=>'Bayaran Sewa Hotel/ Elaun Lojing']);
            $this->assertSame('220',$d->preview($d->source($d->validate([$r])[0])));
            foreach(['Bayaran Tambang Kereta Api','Bayaran Tambang Kapal/ Bot','Bayaran Kapal Terbang'] as $mode){
                $r=$this->manual($place,['kategori2'=>$family,'komponen'=>$mode]);$this->assertSame('1234.567',$d->preview($d->source($d->validate([$r])[0])));
                $r['kadar']=null;$this->assertNull($d->preview($d->source($d->validate([$r])[0])));
            }
        }
    }

    public function test_http_save_reopen_edit_noop_and_partial_sources_keep_exact_values_without_outputs(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$this->gridCapture('pertukaran-empty',$this->get($url)->assertOk());
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place),$this->manual($place),$this->meal($place)]))->assertOk();
        $response=$this->get($url)->assertOk();$records=$response->viewData('records');
        $this->assertSame(['4320','1234.567','45'],$records->pluck('preview')->all());$this->gridCapture('pertukaran-populated',$response);
        $sources=$estimate->butiran()->orderBy('id')->get();$this->assertSame('1234.567',$sources[1]->data_sumber->kadar);$this->assertNull($sources[0]->data_sumber->kadar);
        $d=new \App\Services\DefinisiPertukaran;$r=$d->project($sources[0]->data_sumber)+['butiran_id'=>$sources[0]->id,'semakan_butiran'=>$sources[0]->semakan,'tempat_bertugas_id'=>$place->id];
        $count=RekodAudit::count();$this->postJson($url,$this->payload($bid,$estimate,[$r]))->assertOk();$this->assertSame($count,RekodAudit::count());
        $r['jarak']='1234.56789';$this->postJson($url,$this->payload($bid,$estimate,[$r]))->assertOk();$this->assertSame('1234.56789',$sources[0]->fresh()->data_sumber->jarak);
        $this->postJson($url,$this->payload($bid,$estimate,[['tempat_bertugas_id'=>$place->id,'nama'=>'Belum Lengkap']]))->assertOk();
        $partial=$this->get($url)->assertOk();$this->assertNull($partial->viewData('records')->last()['preview']);$this->gridCapture('pertukaran-partial',$partial);
        $this->assertDatabaseCount('butiran_anggaran',4);$this->assertDatabaseCount('hasil_pengiraan',0);
    }

    public function test_invalid_reference_derived_inactive_and_fixed_rate_injection_roll_back_whole_batch(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$audit=RekodAudit::count();
        $bad=[['kategori1'=>'Palsu'],['kategori2'=>'Palsu'],['kategori3'=>'Elaun Makan'],['komponen'=>'Bayaran Kapal Terbang'],['padanan'=>'Semenanjung'],['kadar'=>'999'],['kadar'=>['999']],['bil_unit'=>'2'],['bil_bulan'=>'12'],['tujuan'=>'Palsu'],['tarikh_mula'=>'2028-01-01'],['preview'=>'999'],['gred'=>'C'],['kategori1'=>['Berpindah Rumah']]];
        foreach($bad as $extra)$this->postJson($url,$this->payload($bid,$estimate,[$this->manual($place),$this->row($place,$extra)]))->assertUnprocessable();
        $this->postJson($url,$this->payload($bid,$estimate,[$this->meal($place,['jarak'=>'10'])]))->assertUnprocessable()->assertJsonValidationErrors('rekod.0.jarak');
        $this->assertDatabaseCount('butiran_anggaran',0);$this->assertSame($audit,RekodAudit::count());
    }

    public function test_stale_phase_role_and_workplace_guards_and_readonly_presentation(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$payload=$this->payload($bid,$estimate,[$this->row($place)]);
        $this->postJson($url,$payload)->assertOk();$this->postJson($url,$payload)->assertConflict();$this->assertDatabaseCount('butiran_anggaran',1);
        $outside=\App\Models\TempatBertugas::create(['ptj_id'=>$this->other->id,'nama'=>'Sektor Perancangan','jenis'=>'sektor']);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($outside)]))->assertForbidden();
        $this->phase->forceFill(['dibuka'=>false])->save();$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertForbidden();
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PemantauBidaan);
        $response=$this->get($url)->assertOk();$this->assertFalse($response->viewData('editable'));$this->gridCapture('pertukaran-readonly',$response);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertForbidden();
    }

    public function test_source_revision_deletion_and_copy_independence(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$this->postJson($url,$this->payload($bid,$estimate,[$this->manual($place)]))->assertOk();$r=$estimate->butiran()->sole();
        $copy=app(\App\Services\DrafButiranAnggaran::class)->salin($estimate->id,$r->id,['semakan_bidaan'=>$bid->fresh()->semakan,'semakan_anggaran'=>$estimate->fresh()->semakan,'semakan_butiran'=>$r->semakan]);
        $this->assertNotSame($r->id,$copy->id);$this->assertEquals($r->data_sumber,$copy->data_sumber);
        $bad=[['butiran_id'=>$r->id,'semakan_butiran'=>$r->semakan+1]];$this->postJson($url,$this->payload($bid,$estimate,[],$bad))->assertConflict();$this->assertDatabaseCount('butiran_anggaran',2);
        $this->postJson($url,$this->payload($bid,$estimate,[],[['butiran_id'=>$r->id,'semakan_butiran'=>$r->semakan]]))->assertOk();$this->assertNull($r->fresh());$this->assertNotNull($copy->fresh());
    }

    public function test_five_existing_mappings_and_os22000_separation(): void
    {
        $catalogue=app(\App\Services\KatalogDefinisiAnggaran::class);
        foreach(['Pengurusan Pentadbiran Negeri'=>'010805','Pendidikan Rendah'=>'030220','Pendidikan Awal'=>'030102','Pendidikan Menengah'=>'030302','Pengurusan Asrama'=>'030314'] as $name=>$code){
            $activity=AktivitiKewangan::firstOrCreate(['nama'=>$name],['singkatan'=>$name]);$bid=Bidaan::forceCreate(['kitaran_id'=>$this->cycle->id,'ptj_id'=>$this->owner->id,'aktiviti_kewangan_id'=>$activity->id,'diaktifkan'=>true,'semakan'=>1]);
            $k=\App\Models\KodAktiviti::firstOrCreate(['kod_aktiviti'=>$code],['aktiviti_kewangan_id'=>$activity->id]);PemetaanOs::firstOrCreate(['aktiviti_kewangan_id'=>$activity->id,'rujukan_os_id'=>$this->os->id],['kod_aktiviti_id'=>$k->id,'singkatan_sumber'=>$name,'butiran_asal'=>$this->os->butiran_perbelanjaan,'status_sumber'=>'Aktif']);
            $this->assertInstanceOf(\App\Services\DefinisiPertukaran::class,$catalogue->untuk($this->os,$bid));$this->assertNull($catalogue->untuk(RujukanOs::create(['kod_objek'=>'22000','butiran_perbelanjaan'=>'Varian '.$code]),$bid));
            PemetaanOs::where('aktiviti_kewangan_id',$activity->id)->where('rujukan_os_id',$this->os->id)->delete();$this->assertNull($catalogue->untuk($this->os,$bid));
        }
    }
}
