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

class MedicalSpreadsheetTest extends TestCase
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
        $this->os = RujukanOs::create(['kod_objek' => '15000', 'butiran_perbelanjaan' => 'Bayaran Perubatan']);
        $this->unconfigured = RujukanOs::create(['kod_objek' => '15000', 'butiran_perbelanjaan' => 'Faedah-faedah Kewangan Yang Lain']);
        $code=\App\Models\KodAktiviti::create(['aktiviti_kewangan_id'=>$this->activity->id,'kod_aktiviti'=>'010804']);
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
        $jobs=app(\App\Services\RujukanJawatan::class)->grades();$job=array_key_first(array_filter($jobs,fn($g)=>in_array('DG9',$g,true)));
        return array_replace(['tempat_bertugas_id'=>$place->id,'kategori1'=>\App\Services\DefinisiPerubatan::KIDNEY,'nama'=>'AHMAD UJIAN','jawatan'=>$job,'gred'=>'DG9','tarikh_mula'=>'2028-02-01','tarikh_tamat'=>'2028-04-30','bil_unit'=>'1','kadar'=>'120.00','kekerapan'=>'3','bil_bulan'=>'12'],$extra);
    }
    private function ordinary($place,array $extra=[]): array {return $this->row($place,array_replace(['kategori1'=>'Pembekalan Ubat','tarikh_mula'=>null,'tarikh_tamat'=>null],$extra));}
    private function gridCapture(string $name,$response): void
    {
        $this->workspaceCapture($name,$response);
        if(getenv('ABM_UI_CAPTURE')==='1')foreach(['abm-money.js','anggaran-grid.css','anggaran-grid.js'] as $asset)copy(public_path($asset),storage_path('app/ui-qa/'.$asset));
    }
    public function test_exact_nine_categories_manual_rates_and_four_factor_formula_for_every_category(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$d=new \App\Services\DefinisiPerubatan(2028);
        $this->assertSame(['Pembekalan Ubat','Pembekalan Alat','Perkhidmatan Perubatan','Rawatan Penyakit Buah Pinggang','Rawatan Kemandulan','Rawatan Kepakaran','Rawatan di Institut Jantung Negara (IJN) Sdn. Bhd.','Rawatan Kecemasan','Rawatan di Luar Negeri'],array_keys($d->references()));
        foreach($d->references() as $category=>$ref){$this->assertSame(['mode'=>'USER_INPUT_KADAR'],$ref);$r=$this->ordinary($place,['kategori1'=>$category]);$s=$d->source($d->validate([$r])[0]);$this->assertSame('4320',$d->preview($s));$this->assertTrue($d->readable($s));}
        $this->assertSame('4324.4442',$d->preview($d->source($this->row($place,['kadar'=>'120.12345']))));
        foreach(['bil_unit','kadar','kekerapan','bil_bulan'] as $key)$this->assertNull($d->preview($d->source($this->row($place,[$key=>null]))));
        $this->assertSame('0',$d->preview($d->source($this->row($place,['bil_unit'=>'0']))));
    }

    public function test_contract_calendar_duration_is_inclusive_and_never_sets_chargeable_months(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$d=new \App\Services\DefinisiPerubatan(2028);
        foreach([['2028-02-01','2028-04-30','0 tahun, 3 bulan, 0 hari'],['2028-02-29','2028-02-29','0 tahun, 0 bulan, 1 hari'],['2028-01-31','2028-02-29','0 tahun, 1 bulan, 1 hari'],['2028-01-01','2028-12-31','1 tahun, 0 bulan, 0 hari']] as [$start,$end,$duration]){
            $s=$d->source($d->validate([$this->row($place,['tarikh_mula'=>$start,'tarikh_tamat'=>$end])])[0]);$this->assertSame($duration,$d->project($s)['tempoh']);$this->assertSame('12',$s->bil_bulan);$this->assertSame('4320',$d->preview($s));
        }
        $this->assertNull($d->project($d->source($this->row($place,['tarikh_tamat'=>null])))['tempoh']);$this->assertNull($d->project($d->source($this->ordinary($place)))['tempoh']);
    }

    public function test_http_save_reopen_edit_noop_partial_and_readonly_fixtures(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$this->gridCapture('medical-empty',$this->get($url)->assertOk());
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place),$this->ordinary($place)]))->assertOk();$response=$this->get($url)->assertOk();$this->assertSame(['4320','4320'],$response->viewData('records')->pluck('preview')->all());$this->assertSame('0 tahun, 3 bulan, 0 hari',$response->viewData('records')[0]['tempoh']);$this->gridCapture('medical-populated',$response);
        $source=$estimate->butiran()->orderBy('id')->first();$saved=(new \App\Services\DefinisiPerubatan(2028))->project($source->data_sumber);unset($saved['tempoh']);$saved+=['tempat_bertugas_id'=>$place->id,'butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan];
        $audit=RekodAudit::count();$this->postJson($url,$this->payload($bid,$estimate,[$saved]))->assertOk();$this->assertSame($audit,RekodAudit::count());
        $saved['kadar']='120.12345';$this->postJson($url,$this->payload($bid,$estimate,[$saved]))->assertOk();$this->assertSame('120.12345',$source->fresh()->data_sumber->kadar);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['bil_bulan'=>null,'tarikh_tamat'=>null])]))->assertOk();$response=$this->get($url)->assertOk();$this->assertNull($response->viewData('records')->last()['preview']);$this->gridCapture('medical-partial',$response);$this->assertDatabaseCount('hasil_pengiraan',0);
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PemantauBidaan);$response=$this->get($url)->assertOk();$this->assertFalse($response->viewData('editable'));$this->gridCapture('medical-readonly',$response);
    }

    public function test_invalid_inactive_derived_grade_dates_and_decimal_injections_are_atomic(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        foreach([['kategori1'=>'Other'],['kategori1'=>[]],['gred'=>'DG99'],['gred'=>[]],['jawatan'=>[]],['jawatan'=>'Other'],['bil_unit'=>[]],['bil_unit'=>'1,000'],['kadar'=>'1e2'],['kadar'=>[]],['kekerapan'=>[]],['bil_bulan'=>[]],['tarikh_mula'=>'2027-12-31'],['tarikh_tamat'=>'2029-01-01'],['tarikh_mula'=>'2028-02-30'],['tarikh_mula'=>[]],['tarikh_tamat'=>'2028-01-01'],['tujuan'=>'Other'],['kategori2'=>'Other'],['kategori3'=>'Other'],['unit'=>'Session'],['tempoh'=>'1 tahun'],['preview'=>'100'],['tarikh_mula_kontrak'=>'2028-01-01']] as $extra)$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place),$this->row($place,$extra)]))->assertUnprocessable();
        foreach(['tarikh_mula','tarikh_tamat'] as $key)$this->postJson($url,$this->payload($bid,$estimate,[$this->ordinary($place,[$key=>'2028-02-01'])]))->assertUnprocessable()->assertJsonValidationErrors('rekod.0.'.$key);
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

    public function test_only_ppn_010804_mapping_can_select_medical_definition(): void
    {
        [$bid,$estimate]=$this->gridContext();$c=app(\App\Services\KatalogDefinisiAnggaran::class);$this->assertInstanceOf(\App\Services\DefinisiPerubatan::class,$c->untuk($this->os,$bid));$this->assertNull($c->untuk($this->unconfigured,$bid));
        $map=$this->os->pemetaan()->sole();$code=\App\Models\KodAktiviti::create(['aktiviti_kewangan_id'=>$this->activity->id,'kod_aktiviti'=>'010801']);$map->update(['kod_aktiviti_id'=>$code->id]);$this->assertNull($c->untuk($this->os,$bid));
        $other=AktivitiKewangan::create(['nama'=>'Pendidikan Menengah']);$code=\App\Models\KodAktiviti::create(['aktiviti_kewangan_id'=>$other->id,'kod_aktiviti'=>'030301']);$map->delete();PemetaanOs::create(['aktiviti_kewangan_id'=>$other->id,'rujukan_os_id'=>$this->os->id,'kod_aktiviti_id'=>$code->id,'singkatan_sumber'=>'Menengah','butiran_asal'=>'Bayaran Perubatan','status_sumber'=>'Aktif']);
        $otherBid=Bidaan::forceCreate(['kitaran_id'=>$this->cycle->id,'ptj_id'=>$this->owner->id,'aktiviti_kewangan_id'=>$other->id]);$this->assertNull($c->untuk($this->os,$otherBid));
    }

    public function test_copy_switch_category_and_audited_delete_preserve_source_independence(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertOk();$source=$estimate->butiran()->sole();
        $copy=app(\App\Services\DrafButiranAnggaran::class)->salin($estimate->id,$source->id,['semakan_bidaan'=>$bid->fresh()->semakan,'semakan_anggaran'=>$estimate->fresh()->semakan,'semakan_butiran'=>$source->semakan]);$this->assertNotSame($source->id,$copy->id);
        $r=$this->ordinary($place,['butiran_id'=>$copy->id,'semakan_butiran'=>$copy->semakan]);$this->postJson($url,$this->payload($bid,$estimate,[$r]))->assertOk();$this->assertNull($copy->fresh()->data_sumber->tarikh_mula);$this->assertSame('2028-02-01',$source->fresh()->data_sumber->tarikh_mula);
        $this->postJson($url,$this->payload($bid,$estimate,[],[['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan]]))->assertOk();$this->assertNull($source->fresh());$this->assertNotNull($copy->fresh());$this->assertSame('source.draft_deleted',RekodAudit::orderByDesc('id')->first()->tindakan);
    }
}
