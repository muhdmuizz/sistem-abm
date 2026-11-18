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

class BenefitsSpreadsheetTest extends TestCase
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
        $this->os = RujukanOs::create(['kod_objek' => '15000', 'butiran_perbelanjaan' => 'Faedah-faedah Kewangan Yang Lain']);
        $this->unconfigured = RujukanOs::create(['kod_objek' => '15000', 'butiran_perbelanjaan' => 'Bayaran Perubatan']);
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
        $jobs=app(\App\Services\RujukanJawatan::class)->grades();$job=array_key_first(array_filter($jobs,fn($g)=>in_array('DG9',$g,true)));
        return array_replace(['tempat_bertugas_id'=>$place->id,'kategori1'=>'Bantuan Baju Kot','kategori2'=>null,'kategori3'=>null,'nama'=>'AHMAD UJIAN','jawatan'=>$job,'gred'=>'DG9','tujuan'=>null,'kadar'=>null],$extra);
    }
    private function fees($place,array $extra=[]): array {return $this->row($place,array_replace(['kategori1'=>\App\Services\DefinisiFaedah::FEES,'tujuan'=>'Yuran profesional','kadar'=>'4320.12345'],$extra));}
    private function travel($place,array $extra=[]): array {return $this->row($place,array_replace(['kategori1'=>\App\Services\DefinisiFaedah::TRAVEL,'kategori2'=>'Wilayah Asal','kategori3'=>'Sabah','kadar'=>'4320.12345'],$extra));}
    private function gridCapture(string $name,$response): void
    {
        $this->workspaceCapture($name,$response);
        if(getenv('ABM_UI_CAPTURE')==='1')foreach(['abm-money.js','anggaran-grid.css','anggaran-grid.js'] as $asset)copy(public_path($asset),storage_path('app/ui-qa/'.$asset));
    }
    public function test_original_125_grade_rows_and_17_paths_are_unchanged_with_no_legacy_c_mapping(): void
    {
        $d=new \App\Services\DefinisiFaedah;$rates=$d->gradeRates();$count=0;$c=0;foreach($rates as $category=>$grades)foreach($grades as $grade=>$rate){$count++;$c+=(int)($grade==='C');}
        $this->assertSame(125,$count);$this->assertSame(8,$c);$this->assertSame('',$rates[\App\Services\DefinisiFaedah::FEES]['']);
        $refs=$d->references();$this->assertCount(13,$refs);$leaves=0;foreach($refs as $subs)foreach($subs as $items)$leaves+=count($items);$this->assertSame(17,$leaves);
        [$bid,$estimate,$place]=$this->gridContext();$r=$this->row($place,['jawatan'=>'PENGARAH PENDIDIKAN NEGERI','gred'=>'VU7']);$this->assertFalse($d->rowReference($r)['matched']);$this->assertNull($d->preview($d->source($r)));
    }

    public function test_all_thirteen_benefits_use_the_applicable_rate_times_one_with_no_quantity_factors(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$d=new \App\Services\DefinisiFaedah;
        foreach(['Bantuan Baju Kot'=>'500','Bantuan Pakaian Panas Tempat Kerja'=>'200','Bayaran Pakaian Istiadat'=>'3500','Bantuan Bayaran Pakaian Black Tie'=>'1000','Bayaran Pakaian Menghadiri Upacara Rasmi (Pegawai)'=>'650','Bayaran Pakaian Menghadiri Upacara Rasmi (Pasangan)'=>'450','Kemudahan Pembelian Alat Komunikasi Mudah Alih'=>'750','Elaun Pakaian Panas'=>'1500'] as $category=>$amount){$r=$this->row($place,['kategori1'=>$category]);$this->assertSame($amount,$d->preview($d->source($d->validate([$r])[0])));}
        $jobs=app(\App\Services\RujukanJawatan::class)->grades();$job=array_key_first(array_filter($jobs,fn($g)=>in_array('W1',$g,true)));
        $this->assertSame('30',$d->preview($d->source($d->validate([$this->row($place,['kategori1'=>'Bayaran Pembaharuan Lesen Memandu','jawatan'=>$job,'gred'=>'W1'])])[0])));
        $this->assertFalse($d->rowReference($this->row($place,['kategori1'=>'Bayaran Pembaharuan Lesen Memandu']))['matched']);
        $this->assertSame('4320.12345',$d->preview($d->source($d->validate([$this->fees($place)])[0])));$this->assertSame('4320.12345',$d->preview($d->source($d->validate([$this->travel($place)])[0])));
        foreach(['Ijazah Kedoktoran (PhD)'=>'1250','Ijazah Sarjana (Master)'=>'1000','Ijazah Sarjana Muda'=>'750','Diploma/ Setaraf'=>'500'] as $qualification=>$amount)$this->assertSame($amount,$d->preview($d->source($d->validate([$this->row($place,['kategori1'=>\App\Services\DefinisiFaedah::HPA,'kategori2'=>$qualification])])[0])));
        $this->assertSame('225',$d->preview($d->source($this->row($place,['kategori1'=>\App\Services\DefinisiFaedah::BIPS]))));
        foreach(['nama'=>'   ','jawatan'=>null,'gred'=>null] as $key=>$value)$this->assertNull($d->preview($d->source($this->fees($place,[$key=>$value]))));
        $this->assertNull($d->preview($d->source($this->fees($place,['tujuan'=>null]))));$this->assertNull($d->preview($d->source($this->fees($place,['kadar'=>null]))));$this->assertNull($d->preview($d->source($this->travel($place,['kategori3'=>null]))));
        foreach(['bil_unit','kekerapan','bil_bulan','unit','tarikh_mula','tarikh_tamat'] as $key)$this->assertNotContains($key,array_column($d->columns(),'key'));
    }

    public function test_bips_uses_every_exact_job_grade_kgt_and_withholds_the_intentional_vu7_blank(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$d=new \App\Services\DefinisiFaedah;$rates=$d->kgtRates();$fixed=0;$pending=0;
        foreach($rates as $job=>$grades)foreach($grades as $grade=>$rate){$r=$this->row($place,['kategori1'=>\App\Services\DefinisiFaedah::BIPS,'jawatan'=>$job,'gred'=>$grade]);$ref=$d->rowReference($r);
            if($rate===''){$pending++;$this->assertSame('VU7',$grade);$this->assertFalse($ref['matched']);$this->assertSame('UNRESOLVED_PERCENT',$ref['mode']);$this->assertNull($d->preview($d->source($r)));continue;}
            $fixed++;$this->assertSame(0,\Brick\Math\BigDecimal::of($rate)->compareTo($ref['kadar']));$this->assertSame(0,\Brick\Math\BigDecimal::of($rate)->compareTo($d->preview($d->source($r))));
        }
        $this->assertSame(130,$fixed);$this->assertSame(1,$pending);
        $this->postJson($this->gridUrl($bid,$estimate),$this->payload($bid,$estimate,[$this->row($place,['kategori1'=>\App\Services\DefinisiFaedah::BIPS,'jawatan'=>'PENGARAH PENDIDIKAN NEGERI','gred'=>'VU7','kadar'=>'1351.35'])]))->assertUnprocessable()->assertJsonValidationErrors('rekod.0.kadar');
    }

    public function test_http_mixed_modes_save_reopen_noop_edit_partial_and_readonly_fixtures(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$this->gridCapture('benefits-empty',$this->get($url)->assertOk());
        $rows=[$this->row($place),$this->fees($place),$this->travel($place),$this->row($place,['kategori1'=>\App\Services\DefinisiFaedah::HPA,'kategori2'=>'Ijazah Kedoktoran (PhD)']),$this->row($place,['kategori1'=>\App\Services\DefinisiFaedah::BIPS])];
        $this->postJson($url,$this->payload($bid,$estimate,$rows))->assertOk();$response=$this->get($url)->assertOk();$this->assertSame(['500','4320.12345','4320.12345','1250','225'],$response->viewData('records')->pluck('preview')->all());$this->gridCapture('benefits-populated',$response);
        $sources=$estimate->butiran()->orderBy('id')->get();$fixed=$sources[0];$this->assertNull($fixed->data_sumber->kadar);$this->assertSame('4320.12345',$sources[1]->data_sumber->kadar);
        $saved=(new \App\Services\DefinisiFaedah)->project($sources[1]->data_sumber)+['tempat_bertugas_id'=>$place->id,'butiran_id'=>$sources[1]->id,'semakan_butiran'=>$sources[1]->semakan];$audit=RekodAudit::count();$this->postJson($url,$this->payload($bid,$estimate,[$saved]))->assertOk();$this->assertSame($audit,RekodAudit::count());
        $saved['kadar']='9876.54321';$this->postJson($url,$this->payload($bid,$estimate,[$saved]))->assertOk();$this->assertSame('9876.54321',$sources[1]->fresh()->data_sumber->kadar);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->fees($place,['tujuan'=>null,'kadar'=>null]),$this->row($place,['kategori1'=>\App\Services\DefinisiFaedah::BIPS,'jawatan'=>'PENGARAH PENDIDIKAN NEGERI','gred'=>'VU7'])]))->assertOk();$response=$this->get($url)->assertOk();$this->assertNull($response->viewData('records')->last()['preview']);$this->gridCapture('benefits-partial',$response);$this->assertDatabaseCount('hasil_pengiraan',0);
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PemantauBidaan);$response=$this->get($url)->assertOk();$this->assertFalse($response->viewData('editable'));$this->gridCapture('benefits-readonly',$response);
    }

    public function test_derived_inactive_cross_category_grade_and_malformed_injections_are_atomic(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        foreach([['kategori1'=>'Other'],['kategori1'=>[]],['kategori2'=>'Wilayah Asal'],['kategori3'=>'Sabah'],['kategori2'=>[]],['kategori3'=>[]],['tujuan'=>'Not applicable'],['kadar'=>'1'],['gred'=>'C'],['gred'=>[]],['jawatan'=>[]],['bil_unit'=>'2'],['kekerapan'=>'2'],['bil_bulan'=>'12'],['unit'=>'Unit'],['tarikh_mula'=>'2028-01-01'],['preview'=>'500']] as $extra)$this->postJson($url,$this->payload($bid,$estimate,[$this->fees($place),$this->row($place,$extra)]))->assertUnprocessable();
        foreach([['kategori2'=>'Other'],['kategori3'=>'Other'],['tujuan'=>'Not applicable']] as $extra)$this->postJson($url,$this->payload($bid,$estimate,[$this->travel($place,$extra)]))->assertUnprocessable();
        foreach([['kadar'=>[]],['kadar'=>'1e2']] as $extra)$this->postJson($url,$this->payload($bid,$estimate,[$this->fees($place,$extra)]))->assertUnprocessable();
        $this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('rekod_audit',0);
    }

    public function test_live_phase_scope_permissions_and_source_bid_revision_guards(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$data=$this->payload($bid,$estimate,[$this->fees($place)]);$this->postJson($url,$data)->assertOk();$this->postJson($url,$data)->assertConflict();
        $s=$estimate->butiran()->sole();$this->postJson($url,$this->payload($bid,$estimate,[$this->fees($place,['butiran_id'=>$s->id,'semakan_butiran'=>$s->semakan+1])]))->assertConflict();
        $outside=\App\Models\TempatBertugas::create(['ptj_id'=>$this->other->id,'nama'=>'Sektor Perancangan','jenis'=>'sektor']);$this->postJson($url,$this->payload($bid,$estimate,[$this->fees($outside)]))->assertForbidden();
        TetapanAksesPtj::where('rujukan_os_id',$this->os->id)->update(['dibenarkan'=>false]);$this->postJson($url,$this->payload($bid,$estimate,[$this->fees($place)]))->assertNotFound();
        TetapanAksesPtj::where('rujukan_os_id',$this->os->id)->update(['dibenarkan'=>true]);$this->phase->forceFill(['dibuka'=>false])->save();$this->postJson($url,$this->payload($bid,$estimate,[$this->fees($place)]))->assertForbidden();
    }

    public function test_only_three_existing_exact_mappings_select_benefit_definition(): void
    {
        $c=app(\App\Services\KatalogDefinisiAnggaran::class);
        foreach(['Pengurusan Pentadbiran Negeri'=>'010801','Pendidikan Menengah'=>'030301','Pengurusan Asrama'=>'030313'] as $name=>$code){$a=AktivitiKewangan::firstOrCreate(['nama'=>$name],['singkatan'=>$name]);$bid=Bidaan::forceCreate(['kitaran_id'=>$this->cycle->id,'ptj_id'=>$this->owner->id,'aktiviti_kewangan_id'=>$a->id]);$k=\App\Models\KodAktiviti::firstOrCreate(['kod_aktiviti'=>$code],['aktiviti_kewangan_id'=>$a->id]);PemetaanOs::firstOrCreate(['aktiviti_kewangan_id'=>$a->id,'rujukan_os_id'=>$this->os->id,'kod_aktiviti_id'=>$k->id],['singkatan_sumber'=>$name,'butiran_asal'=>\App\Services\DefinisiFaedah::LABEL,'status_sumber'=>'Aktif']);
            $this->assertInstanceOf(\App\Services\DefinisiFaedah::class,$c->untuk($this->os,$bid));$this->assertNull($c->untuk($this->unconfigured,$bid));PemetaanOs::where('aktiviti_kewangan_id',$a->id)->where('rujukan_os_id',$this->os->id)->delete();$this->assertNull($c->untuk($this->os,$bid));}
        $a=AktivitiKewangan::create(['nama'=>'Pendidikan Awal']);$bid=Bidaan::forceCreate(['kitaran_id'=>$this->cycle->id,'ptj_id'=>$this->owner->id,'aktiviti_kewangan_id'=>$a->id]);$this->assertNull($c->untuk($this->os,$bid));
    }

    public function test_copy_and_audited_delete_keep_manual_source_values_independent(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$this->postJson($url,$this->payload($bid,$estimate,[$this->fees($place)]))->assertOk();$s=$estimate->butiran()->sole();$copy=app(\App\Services\DrafButiranAnggaran::class)->salin($estimate->id,$s->id,['semakan_bidaan'=>$bid->fresh()->semakan,'semakan_anggaran'=>$estimate->fresh()->semakan,'semakan_butiran'=>$s->semakan]);$this->assertNotSame($s->id,$copy->id);$this->assertEquals($s->data_sumber,$copy->data_sumber);
        $this->postJson($url,$this->payload($bid,$estimate,[],[['butiran_id'=>$s->id,'semakan_butiran'=>$s->semakan]]))->assertOk();$this->assertNull($s->fresh());$this->assertNotNull($copy->fresh());$this->assertSame('source.draft_deleted',RekodAudit::orderByDesc('id')->first()->tindakan);
    }
}
