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

class PengangkutanSpreadsheetTest extends TestCase
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
        $this->os = RujukanOs::create(['kod_objek' => '22000', 'butiran_perbelanjaan' => 'Tuntutan Perbelanjaan Pertukaran/ Penempatan Baharu/ Penempatan Semula']);
        $this->unconfigured = RujukanOs::create(['kod_objek' => '29000', 'butiran_perbelanjaan' => 'Pakej (Mesyuarat/ Bengkel/ Kursus)']);
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
    private function row($place,array $extra=[]): array {
        $jobs=app(\App\Services\RujukanJawatan::class)->grades();$job=array_key_first(array_filter($jobs,fn($grades)=>in_array('DG9',$grades,true)));
        return array_replace(['tempat_bertugas_id'=>$place->id,'kategori1'=>'Pertukaran/ Penempatan','kategori2'=>'Bujang','kategori3'=>'Pemberian Pindahan','lokasi'=>'Semenanjung','nama'=>'AHMAD UJIAN','jawatan'=>$job,'gred'=>'DG9','tujuan'=>null,'kadar'=>null],$extra);
    }
    private function goods($place,array $extra=[]): array {return $this->row($place,array_replace(['kategori1'=>'Pengangkutan Barang','kategori2'=>null,'kategori3'=>null,'lokasi'=>null,'nama'=>null,'jawatan'=>null,'gred'=>null,'tujuan'=>'Pengangkutan Peralatan Pejabat','kadar'=>'4320.12345'],$extra));}
    private function migration() {return require database_path('migrations/2026_11_17_010000_unify_os22000_catalogue.php');}
    private function gridCapture(string $name,$response): void
    {
        $this->workspaceCapture($name,$response);
        if(getenv('ABM_UI_CAPTURE')==='1')foreach(['abm-money.js','anggaran-grid.css','anggaran-grid.js'] as $asset)copy(public_path($asset),storage_path('app/ui-qa/'.$asset));
    }
    public function test_reference_inventory_and_missing_grade_location_matches_are_not_fabricated(): void
    {
        $d=new \App\Services\DefinisiPengangkutan;$rows=$d->rateRows();$this->assertCount(126,$rows);$this->assertCount(6,array_filter($rows,fn($r)=>$r[4]===''));$this->assertCount(8,array_filter($rows,fn($r)=>$r[2]==='C'));$this->assertCount(0,array_filter($rows,fn($r)=>$r[2]==='11'));
        [$bid,$estimate,$place]=$this->gridContext();$r=$this->row($place);$this->assertSame('350',$d->preview($d->source($d->validate([$r])[0])));
        $r['kategori2']='Keluarga';$r['lokasi']='Sabah/ Sarawak/ WP Labuan';$this->assertSame('1100',$d->preview($d->source($d->validate([$r])[0])));
        $r['kategori3']='Elaun Berpindah';$this->assertSame('1444',$d->preview($d->source($d->validate([$r])[0])));
        foreach(['VU7'] as $grade){$jobs=app(\App\Services\RujukanJawatan::class)->grades();$r['jawatan']=array_key_first(array_filter($jobs,fn($grades)=>in_array($grade,$grades,true)));$r['gred']=$grade;
            $this->assertFalse($d->rowReference($r)['matched']);$this->assertNull($d->preview($d->source($d->validate([$r])[0])));
            $this->postJson($this->gridUrl($bid,$estimate),$this->payload($bid,$estimate,[array_replace($r,['kadar'=>'999'])]))->assertUnprocessable()->assertJsonValidationErrors('rekod.0.kadar');
        }
        $this->postJson($this->gridUrl($bid,$estimate),$this->payload($bid,$estimate,[$this->row($place,['gred'=>'DG11'])]))->assertUnprocessable()->assertJsonValidationErrors('rekod.0.gred');
        $r=$this->row($place,['kategori3'=>'Tambang Pengangkutan Barang','lokasi'=>'Sabah/ Sarawak/ WP Labuan','kadar'=>'100']);$this->postJson($this->gridUrl($bid,$estimate),$this->payload($bid,$estimate,[$r]))->assertUnprocessable()->assertJsonValidationErrors('rekod.0.lokasi');
    }

    public function test_both_paths_all_claims_and_exact_kadar_times_one(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$d=new \App\Services\DefinisiPengangkutan;
        foreach(['Bujang','Keluarga'] as $family)foreach(['Semenanjung','Sabah/ Sarawak/ WP Labuan'] as $location){
            foreach(['Pemberian Pindahan','Elaun Berpindah','Belanja Pelbagai'] as $claim){$r=$this->row($place,['kategori2'=>$family,'kategori3'=>$claim,'lokasi'=>$location,'kadar'=>$claim==='Belanja Pelbagai'?'1234.567':null]);$source=$d->source($d->validate([$r])[0]);$this->assertNotNull($d->preview($source));if($claim==='Belanja Pelbagai')$this->assertSame('1234.567',$d->preview($source));}
            if($location==='Semenanjung'){$r=$this->row($place,['kategori2'=>$family,'kategori3'=>'Tambang Pengangkutan Barang','kadar'=>'987.654']);$this->assertSame('987.654',$d->preview($d->source($d->validate([$r])[0])));}
        }
        $this->assertSame('4320.12345',$d->preview($d->source($d->validate([$this->goods($place)])[0])));
        $this->assertNull($d->preview($d->source($this->goods($place,['tujuan'=>null]))));$this->assertNull($d->preview($d->source($this->goods($place,['kadar'=>null]))));
    }

    public function test_http_save_reopen_edit_noop_partial_and_accounting_fixtures(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$this->gridCapture('pengangkutan-empty',$this->get($url)->assertOk());
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place),$this->goods($place),$this->row($place,['kategori3'=>'Belanja Pelbagai','kadar'=>'125.125'])]))->assertOk();
        $response=$this->get($url)->assertOk();$this->assertSame(['350','4320.12345','125.125'],$response->viewData('records')->pluck('preview')->all());$this->gridCapture('pengangkutan-populated',$response);
        $source=$estimate->butiran()->orderBy('id')->first();$r=(new \App\Services\DefinisiPengangkutan)->project($source->data_sumber)+['tempat_bertugas_id'=>$place->id,'butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan];$audit=RekodAudit::count();
        $this->postJson($url,$this->payload($bid,$estimate,[$r]))->assertOk();$this->assertSame($audit,RekodAudit::count());
        $r['kategori2']='Keluarga';$this->postJson($url,$this->payload($bid,$estimate,[$r]))->assertOk();$this->assertSame('Keluarga',$source->fresh()->data_sumber->kategori2);
        $this->postJson($url,$this->payload($bid,$estimate,[['tempat_bertugas_id'=>$place->id,'kategori1'=>'Pengangkutan Barang','tujuan'=>'Belum Lengkap']]))->assertOk();$response=$this->get($url)->assertOk();$this->assertNull($response->viewData('records')->last()['preview']);$this->gridCapture('pengangkutan-partial',$response);$this->assertDatabaseCount('hasil_pengiraan',0);
    }

    public function test_inactive_derived_malformed_and_rate_override_injections_are_atomic(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        foreach([['tujuan'=>'Wrong'],['kategori1'=>'Other'],['kategori2'=>'Other'],['kategori3'=>'Other'],['lokasi'=>'Other'],['kadar'=>'999'],['kadar'=>[]],['gred'=>'C'],['jarak'=>'10'],['bil_unit'=>'1'],['bil_bulan'=>'12'],['tarikh_mula'=>'2028-01-01'],['preview'=>'100'],['kategori1'=>[]],['kategori2'=>[]],['lokasi'=>[]]] as $extra)$this->postJson($url,$this->payload($bid,$estimate,[$this->goods($place),$this->row($place,$extra)]))->assertUnprocessable();
        foreach(['nama'=>'Officer','jawatan'=>array_key_first(app(\App\Services\RujukanJawatan::class)->grades()),'gred'=>'DG9','kategori2'=>'Bujang','kategori3'=>'Belanja Pelbagai','lokasi'=>'Semenanjung'] as $key=>$value)$this->postJson($url,$this->payload($bid,$estimate,[$this->goods($place,[$key=>$value])]))->assertUnprocessable();
        $this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('rekod_audit',0);
    }

    public function test_stale_live_phase_scope_role_and_readonly_guards(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$data=$this->payload($bid,$estimate,[$this->row($place)]);$this->postJson($url,$data)->assertOk();$this->postJson($url,$data)->assertConflict();
        $outside=\App\Models\TempatBertugas::create(['ptj_id'=>$this->other->id,'nama'=>'Sektor Perancangan','jenis'=>'sektor']);$this->postJson($url,$this->payload($bid,$estimate,[$this->goods($outside)]))->assertForbidden();
        $this->phase->forceFill(['dibuka'=>false])->save();$this->postJson($url,$this->payload($bid,$estimate,[$this->goods($place)]))->assertForbidden();
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PemantauBidaan);$response=$this->get($url)->assertOk();$this->assertFalse($response->viewData('editable'));$this->gridCapture('pengangkutan-readonly',$response);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->goods($place)]))->assertForbidden();
    }

    public function test_seven_existing_codes_allow_both_paths_without_os21000_aliasing(): void
    {
        $catalogue=app(\App\Services\KatalogDefinisiAnggaran::class);
        foreach(['Pengurusan Pentadbiran Negeri'=>['010801'],'Pendidikan Rendah'=>['030220'],'Pendidikan Awal'=>['030102'],'Pendidikan Menengah'=>['030301','030302'],'Pengurusan Asrama'=>['030313','030314']] as $name=>$codes){
            $a=AktivitiKewangan::firstOrCreate(['nama'=>$name],['singkatan'=>$name]);$bid=Bidaan::forceCreate(['kitaran_id'=>$this->cycle->id,'ptj_id'=>$this->owner->id,'aktiviti_kewangan_id'=>$a->id,'diaktifkan'=>true,'semakan'=>1]);
            foreach($codes as $code){$k=\App\Models\KodAktiviti::firstOrCreate(['kod_aktiviti'=>$code],['aktiviti_kewangan_id'=>$a->id]);PemetaanOs::where('aktiviti_kewangan_id',$a->id)->where('rujukan_os_id',$this->os->id)->delete();PemetaanOs::create(['aktiviti_kewangan_id'=>$a->id,'rujukan_os_id'=>$this->os->id,'kod_aktiviti_id'=>$k->id,'singkatan_sumber'=>$name,'butiran_asal'=>\App\Services\DefinisiPengangkutan::LABEL,'status_sumber'=>'Aktif']);
                $this->assertInstanceOf(\App\Services\DefinisiPengangkutan::class,$catalogue->untuk($this->os,$bid));
            }
        }
        $this->assertFalse((new \App\Services\DefinisiPengangkutan)->supports(RujukanOs::create(['kod_objek'=>'21000','butiran_perbelanjaan'=>'Tuntutan Perbelanjaan Pertukaran'])));
    }

    public function test_targeted_catalogue_cleanup_retains_current_sources_mapping_ids_and_permissions(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$this->postJson($this->gridUrl($bid,$estimate),$this->payload($bid,$estimate,[$this->row($place)]))->assertOk();$source=$estimate->butiran()->sole();
        $old=RujukanOs::create(['kod_objek'=>'22000','butiran_perbelanjaan'=>'Pengangkutan Barang-barang']);$code=\App\Models\KodAktiviti::create(['aktiviti_kewangan_id'=>$this->activity->id,'kod_aktiviti'=>'010899']);
        $mapping=PemetaanOs::create(['aktiviti_kewangan_id'=>$this->activity->id,'kod_aktiviti_id'=>$code->id,'rujukan_os_id'=>$old->id,'singkatan_sumber'=>'PPN','butiran_asal'=>'Pengangkutan Barang-barang','status_sumber'=>'Aktif']);
        $oldEstimate=Anggaran::forceCreate(['bidaan_id'=>$bid->id,'ptj_id'=>$this->owner->id,'rujukan_os_id'=>$old->id,'diaktifkan'=>true,'semakan'=>1]);$obsolete=\App\Models\ButiranAnggaran::forceCreate(['anggaran_id'=>$oldEstimate->id,'ptj_id'=>$this->owner->id,'tempat_bertugas_id'=>$place->id,'data_sumber'=>(object)['fixture'=>'obsolete development draft']]);
        TetapanAksesPtj::create(['kategori_ptj'=>'JPNT','aktiviti_kewangan_id'=>$this->activity->id,'rujukan_os_id'=>$old->id,'dibenarkan'=>true]);$revision=$bid->fresh()->semakan;
        $this->migration()->up();$this->migration()->up();
        $this->assertNull($old->fresh());$this->assertNull($oldEstimate->fresh());$this->assertNull($obsolete->fresh());$this->assertNotNull($source->fresh());$this->assertSame($this->os->id,$mapping->fresh()->rujukan_os_id);$this->assertSame('Pengangkutan Barang-barang',$mapping->fresh()->butiran_asal);$this->assertSame($revision+1,$bid->fresh()->semakan);
        $this->assertSame(1,TetapanAksesPtj::where('rujukan_os_id',$this->os->id)->count());$this->assertSame(1,Anggaran::where('bidaan_id',$bid->id)->count());$this->assertNotNull($this->unconfigured->fresh());
    }

    public function test_catalogue_cleanup_aborts_atomically_for_non_draft_bids(): void
    {
        [$bid,$estimate]=$this->gridContext();$old=RujukanOs::create(['kod_objek'=>'22000','butiran_perbelanjaan'=>'Pengangkutan Barang-barang']);$oldEstimate=Anggaran::forceCreate(['bidaan_id'=>$bid->id,'ptj_id'=>$this->owner->id,'rujukan_os_id'=>$old->id,'diaktifkan'=>true]);$bid->forceFill(['status'=>StatusBidaan::Muktamad])->save();
        try{$this->migration()->up();$this->fail('Non-draft reconciliation must stop.');}catch(\RuntimeException $e){$this->assertStringContainsString('bukan draf',$e->getMessage());}
        $this->assertNotNull($old->fresh());$this->assertNotNull($oldEstimate->fresh());$this->assertNotNull($estimate->fresh());
    }
}
