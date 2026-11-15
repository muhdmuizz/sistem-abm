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

class MystepSpreadsheetTest extends TestCase
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
        $this->os = RujukanOs::create(['kod_objek' => '29000', 'butiran_perbelanjaan' => 'MySTEP']);
        $this->unconfigured = RujukanOs::create(['kod_objek' => '29000', 'butiran_perbelanjaan' => 'Pakej (Mesyuarat/ Bengkel/ Kursus)']);
        $code=\App\Models\KodAktiviti::create(['aktiviti_kewangan_id'=>$this->activity->id,'kod_aktiviti'=>'010812']);
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
    private function row($place,array $extra=[]): array {return array_replace(['tempat_bertugas_id'=>$place->id,'kategori1'=>'Guru Ganti','tujuan'=>'Cuti Bersalin','kategori2'=>'Setaraf Ijazah Sarjana Muda','tarikh_mula'=>'2028-02-28','tarikh_tamat'=>'2028-03-01','bil_unit'=>'2','bil_bulan'=>'12'],$extra);}
    private function gridCapture(string $name,$response): void
    {
        $this->workspaceCapture($name,$response);
        if(getenv('ABM_UI_CAPTURE')==='1')foreach(['abm-money.js','anggaran-grid.css','anggaran-grid.js'] as $asset)copy(public_path($asset),storage_path('app/ui-qa/'.$asset));
    }
    public function test_shared_template_has_independent_choices_prescribed_totals_and_budget_year_dates(): void
    {
        [$bid,$estimate]=$this->gridContext();$response=$this->get($this->gridUrl($bid,$estimate))->assertOk();$columns=collect($response->viewData('columns'))->keyBy('key');
        $this->assertCount(3,$columns['kategori1']['values']);$this->assertCount(6,$columns['tujuan']['values']);$this->assertFalse(isset($columns['tujuan']['dependsOn']));
        $this->assertSame(['2638.35','2408.85','2179.35','1949.85'],array_column($response->viewData('references'),'kadar'));
        $this->assertSame('2028-01-01',$columns['tarikh_mula']['min']);$this->assertSame('2028-12-31',$columns['tarikh_tamat']['max']);$this->assertSame('Orang',$columns['unit']['value']);
        foreach(['nama','jawatan','gred','kategori3'] as $key)$this->assertFalse($columns->has($key));
        $this->gridCapture('mystep-empty',$response);$this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('rekod_audit',0);
    }
    public function test_all_purposes_apply_to_all_categories_and_exact_monthly_rates_save_reopen_edit(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$definition=new \App\Services\DefinisiMystep(2028);$rows=[];
        foreach($definition->categories() as $category)foreach($definition->purposes() as $purpose)$rows[]=$this->row($place,['kategori1'=>$category,'tujuan'=>$purpose]);
        $this->postJson($url,$this->payload($bid,$estimate,$rows))->assertOk();$this->assertDatabaseCount('butiran_anggaran',18);
        $first=$estimate->butiran()->orderBy('id')->first();$this->assertSame('63320.4',$definition->preview($first->data_sumber));$this->assertSame(3,$definition->duration($first->data_sumber));
        $this->gridCapture('mystep-populated',$this->get($url)->assertOk());
        foreach(array_combine(array_keys($definition->references()),['63320.4','57812.4','52304.4','46796.4']) as $qualification=>$amount){
            $row=$this->row($place,['butiran_id'=>$first->id,'semakan_butiran'=>$first->fresh()->semakan,'kategori2'=>$qualification]);
            $this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$this->assertSame($amount,$definition->preview($first->fresh()->data_sumber));
        }
        $row['semakan_butiran']=$first->fresh()->semakan;$count=RekodAudit::count();$this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$this->assertSame($count,RekodAudit::count());
        $this->assertDatabaseCount('hasil_pengiraan',0);$this->assertEqualsCanonicalizing(['definition','kategori1','tujuan','kategori2','tarikh_mula','tarikh_tamat','bil_unit','bil_bulan'],array_keys(get_object_vars($first->fresh()->data_sumber)));
    }
    public function test_all_seven_existing_variant_mappings_use_the_same_definition_and_other_contexts_are_rejected(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$code=\App\Models\KodAktiviti::where('aktiviti_kewangan_id',$this->activity->id)->sole();
        foreach([
            ['Pengurusan Pentadbiran Negeri','MySTEP','010812'],['Pendidikan Rendah','MySTEP - Guru Ganti','030223'],['Pendidikan Rendah','MySTEP - Pembantu Pengurusan Murid (PPM) Ganti','030226'],
            ['Pendidikan Awal','MySTEP - Guru Ganti','030106'],['Pendidikan Awal','MySTEP - Pembantu Pengurusan Murid (PPM) Ganti','030105'],
            ['Pendidikan Menengah','MySTEP - Guru Ganti','030305'],['Pendidikan Menengah','MySTEP - Pembantu Pengurusan Murid (PPM) Ganti','030309'],
        ] as [$activity,$label,$value]){
            $this->activity->update(['nama'=>$activity]);$this->os->update(['butiran_perbelanjaan'=>$label]);$code->update(['kod_aktiviti'=>$value]);
            $this->get($this->gridUrl($bid,$estimate))->assertOk();$this->assertInstanceOf(\App\Services\DefinisiMystep::class,app(\App\Services\KatalogDefinisiAnggaran::class)->untuk($this->os,$bid->fresh()));
            $code->update(['kod_aktiviti'=>'999999']);$this->get($this->gridUrl($bid,$estimate))->assertNotFound();
        }
        $this->activity->update(['nama'=>'Pengurusan Asrama']);$this->get($this->gridUrl($bid,$estimate))->assertNotFound();
        $this->assertNull(app(\App\Services\KatalogDefinisiAnggaran::class)->untuk($this->unconfigured,$bid->fresh()));
    }
    public function test_invalid_dates_references_and_derived_field_injection_are_atomic(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        foreach([['kadar'=>'2638.35'],['unit'=>'Orang'],['tempoh'=>'3'],['nama'=>'Someone'],['kategori1'=>'Other'],['tujuan'=>'Other'],['kategori2'=>'Other'],['tarikh_mula'=>'2027-12-31'],['tarikh_tamat'=>'2029-01-01'],['tarikh_mula'=>'2028-02-30'],['tarikh_tamat'=>'2028-02-27'],['bil_unit'=>'1,000']] as $extra){
            $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place),$this->row($place,$extra)]))->assertUnprocessable();$this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('rekod_audit',0);
        }
        $definition=new \App\Services\DefinisiMystep(2028);$same=$definition->source($this->row($place,['tarikh_mula'=>'2028-02-29','tarikh_tamat'=>'2028-02-29','bil_bulan'=>'5']));
        $this->assertSame(1,$definition->duration($same));$this->assertSame('26383.5',$definition->preview($same));
    }
    public function test_blank_partial_dates_and_months_remain_independent_without_defaults(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$keys=['kategori1','tujuan','kategori2','tarikh_mula','tarikh_tamat','bil_unit','bil_bulan'];
        $blank=$this->row($place,array_fill_keys($keys,null));$this->postJson($url,$this->payload($bid,$estimate,[$blank]))->assertOk();$this->assertDatabaseCount('butiran_anggaran',0);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['kategori2'=>null,'tarikh_tamat'=>null,'bil_bulan'=>null])]))->assertOk();$source=$estimate->butiran()->sole()->data_sumber;
        $definition=new \App\Services\DefinisiMystep(2028);$this->assertNull($definition->preview($source));$this->assertNull($definition->duration($source));$this->assertNull($source->bil_bulan);$this->gridCapture('mystep-partial',$this->get($url)->assertOk());
    }
    public function test_live_guards_readonly_and_audited_removal_are_retained(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$data=$this->payload($bid,$estimate,[$this->row($place)]);
        $this->postJson($url,$data)->assertOk();$this->postJson($url,$data)->assertConflict();$source=$estimate->butiran()->sole();
        $this->phase->forceFill(['dibuka'=>false])->save();$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertForbidden();$this->phase->forceFill(['dibuka'=>true])->save();
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PemantauBidaan);$this->gridCapture('mystep-readonly',$this->get($url)->assertOk());
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertForbidden();
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PenyediaAnggaran);
        $this->postJson($url,$this->payload($bid,$estimate,[],[['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan]]))->assertOk();$this->assertSame('source.draft_deleted',RekodAudit::orderByDesc('id')->first()->tindakan);
    }
}
