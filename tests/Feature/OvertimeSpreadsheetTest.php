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

class OvertimeSpreadsheetTest extends TestCase
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
        $this->os = RujukanOs::create(['kod_objek' => '14000', 'butiran_perbelanjaan' => 'Bayaran Lebih Masa']);
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
        return array_replace(['tempat_bertugas_id'=>$place->id,'tujuan'=>\App\Services\DefinisiLebihMasa::PURPOSES[0],'nama'=>'AHMAD UJIAN','jawatan'=>'JURUTERA','gred'=>'J9','tarikh_mula'=>'2028-02-01','tarikh_tamat'=>'2028-02-29','jam'=>'3','bil_hari'=>'2','kekerapan'=>'1','bil_bulan'=>'12'],$extra);
    }
    private function gridCapture(string $name,$response): void
    {
        $this->workspaceCapture($name,$response);
        if(getenv('ABM_UI_CAPTURE')==='1')foreach(['abm-money.js','anggaran-grid.css','anggaran-grid.js'] as $asset)copy(public_path($asset),storage_path('app/ui-qa/'.$asset));
    }
    public function test_all_131_salary_pairs_exact_formula_and_final_only_rounding(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$d=new \App\Services\DefinisiLebihMasa(2028);$count=0;
        foreach($d->salaries() as $job=>$grades)foreach($grades as $grade=>$rate){
            $r=$this->row($place,['jawatan'=>$job,'gred'=>$grade]);$s=$d->source($d->validate([$r])[0]);
            $expected=(string)\Brick\Math\BigDecimal::of($rate['median'])->multipliedBy('864')->dividedBy(2504,2,\Brick\Math\RoundingMode::HalfUp);
            $this->assertSame($expected,$d->preview($s));$this->assertTrue($d->readable($s));$count++;
        }$this->assertSame(131,$count);
        // 6680 * 12 / 2504 = repeating rate; rounding that first would give 2304.72 instead.
        $this->assertSame('2304.92',$d->preview($d->source($this->row($place))));
        // Exact half-cent ties, both signs, zero, arbitrary scale and large exact input.
        foreach([['0.1565','2.51'],['-0.1565','-2.51'],['0','0.00']] as [$hours,$amount]){
            $s=$d->source($this->row($place,['jam'=>$hours,'bil_hari'=>'0.5','kekerapan'=>'1','bil_bulan'=>'1']));$this->assertSame($amount,$d->preview($s));
        }
        $s=$d->source($this->row($place,['jam'=>'9007199254740993.12345']));$this->assertSame((string)\Brick\Math\BigDecimal::of('6680')->multipliedBy(12)->multipliedBy('9007199254740993.12345')->multipliedBy(24)->dividedBy(2504,2,\Brick\Math\RoundingMode::HalfUp),$d->preview($s));
        foreach(['nama','jawatan','gred','tujuan','jam','bil_hari','kekerapan','bil_bulan'] as $key)$this->assertNull($d->preview($d->source($this->row($place,[$key=>null]))));
        $this->assertNull($d->preview($d->source($this->row($place,['nama'=>'   ']))));
    }
    public function test_dates_do_not_set_chargeable_days_or_months(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$d=new \App\Services\DefinisiLebihMasa(2028);
        foreach([['2028-02-01','2028-02-29','29'],['2028-02-29','2028-02-29','1'],['2028-01-01','2028-12-31','366']] as [$a,$b,$days]){
            $s=$d->source($d->validate([$this->row($place,['tarikh_mula'=>$a,'tarikh_tamat'=>$b])])[0]);$this->assertSame($days,$d->project($s)['tempoh']);$this->assertSame('2304.92',$d->preview($s));$this->assertSame('2',$s->bil_hari);$this->assertSame('12',$s->bil_bulan);
        }
    }
    public function test_save_reopen_noop_partial_readonly_and_one_source_per_entry(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$this->gridCapture('overtime-empty',$this->get($url)->assertOk());
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place),$this->row($place)]))->assertOk();
        $this->assertSame(2,$estimate->butiran()->count());$response=$this->get($url)->assertOk();$this->assertSame(['2304.92','2304.92'],$response->viewData('records')->pluck('preview')->all());$this->gridCapture('overtime-populated',$response);
        $source=$estimate->butiran()->orderBy('id')->first();$saved=$this->row($place,['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan]);$audit=RekodAudit::count();$this->postJson($url,$this->payload($bid,$estimate,[$saved]))->assertOk();$this->assertSame($audit,RekodAudit::count());$this->assertObjectNotHasProperty('kadar',$source->fresh()->data_sumber);
        $saved['jam']='4.12345';$this->postJson($url,$this->payload($bid,$estimate,[$saved]))->assertOk();$this->assertSame('4.12345',$source->fresh()->data_sumber->jam);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['bil_bulan'=>null])]))->assertOk();$response=$this->get($url)->assertOk();$this->assertNull($response->viewData('records')->last()['preview']);$this->gridCapture('overtime-partial',$response);$this->assertDatabaseCount('hasil_pengiraan',0);
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PemantauBidaan);$response=$this->get($url)->assertOk();$this->assertFalse($response->viewData('editable'));$this->gridCapture('overtime-readonly',$response);
    }
    public function test_invalid_derived_grade_dates_and_decimal_injections_are_atomic(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        foreach([['tujuan'=>'Aktiviti/ Program/ Mesyuarat rasmi'],['tujuan'=>[]],['gred'=>'DG9'],['gred'=>[]],['jawatan'=>[]],['jam'=>[]],['jam'=>'1,000'],['jam'=>'1e2'],['kekerapan'=>[]],['bil_bulan'=>[]],['tarikh_mula'=>'2027-12-31'],['tarikh_tamat'=>'2029-01-01'],['tarikh_mula'=>'2028-02-30'],['tarikh_mula'=>[]],['tarikh_tamat'=>'2028-01-01'],['kadar'=>'99'],['median'=>'99'],['kategori1'=>'X'],['unit'=>'X'],['bil_unit'=>'1'],['tempoh'=>'1'],['preview'=>'100']] as $extra)$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place),$this->row($place,$extra)]))->assertUnprocessable();
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
    public function test_four_exact_mappings_and_no_awal_or_wrong_parent_mapping(): void
    {
        [$bid,$estimate]=$this->gridContext();$c=app(\App\Services\KatalogDefinisiAnggaran::class);$this->assertInstanceOf(\App\Services\DefinisiLebihMasa::class,$c->untuk($this->os,$bid));
        foreach(['Pendidikan Rendah'=>'030219','Pendidikan Menengah'=>'030301','Pengurusan Asrama'=>'030313','Pendidikan Awal'=>'030101'] as $name=>$code){
            $a=AktivitiKewangan::create(['nama'=>$name]);TetapanAksesPtj::create(['kategori_ptj'=>'JPNT','aktiviti_kewangan_id'=>$a->id,'rujukan_os_id'=>null,'dibenarkan'=>true]);$k=\App\Models\KodAktiviti::create(['aktiviti_kewangan_id'=>$a->id,'kod_aktiviti'=>$code]);PemetaanOs::create(['aktiviti_kewangan_id'=>$a->id,'rujukan_os_id'=>$this->os->id,'kod_aktiviti_id'=>$k->id,'singkatan_sumber'=>'X','butiran_asal'=>'Bayaran Lebih Masa','status_sumber'=>'Aktif']);
            $b=Bidaan::forceCreate(['kitaran_id'=>$this->cycle->id,'ptj_id'=>$this->owner->id,'aktiviti_kewangan_id'=>$a->id]);
            if($name==='Pendidikan Awal')$this->assertNull($c->untuk($this->os,$b));else{$this->operator->penugasanPeranan()->create(['peranan'=>Peranan::PenyediaAnggaran,'ptj_id'=>$this->owner->id,'aktiviti_kewangan_id'=>$a->id]);$this->assertInstanceOf(\App\Services\DefinisiLebihMasa::class,$c->untuk($this->os,$b));}
        }
        $this->os->pemetaan()->delete();$this->assertNull($c->untuk($this->os,$bid));
    }
    public function test_registered_programme_choice_is_scoped_and_workplace_checked(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$otherPlace=\App\Models\TempatBertugas::create(['ptj_id'=>$this->owner->id,'nama'=>'Sektor Lain','jenis'=>'sektor']);
        $teras=\App\Models\TerasStrategik::forceCreate(['nombor'=>1,'tajuk'=>'Teras']);$strategi=\App\Models\StrategiRpm::forceCreate(['teras_strategik_id'=>$teras->id,'nombor'=>1,'tajuk'=>'Strategi']);$prakarsa=\App\Models\PrakarsaRpm::forceCreate(['strategi_id'=>$strategi->id,'nombor'=>1,'tajuk'=>'Prakarsa']);
        $p=\App\Models\ProgramAktiviti::forceCreate(['bidaan_id'=>$bid->id,'ptj_id'=>$this->owner->id,'tempat_bertugas_id'=>$place->id,'kitaran_id'=>$this->cycle->id,'tahun_kitaran'=>2028,'teras_strategik_id'=>$teras->id,'strategi_id'=>$strategi->id,'prakarsa_id'=>$prakarsa->id,'created_by'=>$this->operator->id,'nama'=>'Mesyuarat Pengurusan','semakan'=>0]);
        $d=app(\App\Services\KatalogDefinisiAnggaran::class)->untuk($this->os,$bid);$key='Program #'.$p->id.' — '.$p->nama;$this->assertArrayHasKey($key,$d->references());$url=$this->gridUrl($bid,$estimate);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($otherPlace,['tujuan'=>$key])]))->assertUnprocessable();
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['tujuan'=>$key])]))->assertOk();
        $p->delete();$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['tujuan'=>$key])]))->assertUnprocessable();
        $otherCycle=Kitaran::create(['tahun'=>2029]);$otherBid=Bidaan::forceCreate(['kitaran_id'=>$otherCycle->id,'ptj_id'=>$this->owner->id,'aktiviti_kewangan_id'=>$this->activity->id]);$d=app(\App\Services\KatalogDefinisiAnggaran::class)->untuk($this->os,$otherBid);$this->assertArrayNotHasKey($key,$d->references());
    }
    public function test_copy_and_audited_delete_preserve_independent_sources(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertOk();$source=$estimate->butiran()->sole();
        $copy=app(\App\Services\DrafButiranAnggaran::class)->salin($estimate->id,$source->id,['semakan_bidaan'=>$bid->fresh()->semakan,'semakan_anggaran'=>$estimate->fresh()->semakan,'semakan_butiran'=>$source->semakan]);$this->assertNotSame($source->id,$copy->id);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['butiran_id'=>$copy->id,'semakan_butiran'=>$copy->semakan,'jam'=>'6'])]))->assertOk();$this->assertSame('3',$source->fresh()->data_sumber->jam);$this->assertSame('6',$copy->fresh()->data_sumber->jam);
        $this->postJson($url,$this->payload($bid,$estimate,[],[['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan]]))->assertOk();$this->assertNull($source->fresh());$this->assertNotNull($copy->fresh());
    }
}
