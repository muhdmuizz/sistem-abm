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

class UniformSpreadsheetTest extends TestCase
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
        $this->os = RujukanOs::create(['kod_objek' => '27000', 'butiran_perbelanjaan' => 'Pakaian Seragam']);
        $this->unconfigured = RujukanOs::create(['kod_objek' => '27000', 'butiran_perbelanjaan' => 'Bekalan Alat Pejabat']);
        $code=\App\Models\KodAktiviti::create(['aktiviti_kewangan_id'=>$this->activity->id,'kod_aktiviti'=>'010808']);
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
    private function row($place,array $extra=[]): array {return array_replace(['tempat_bertugas_id'=>$place->id,'kategori1'=>'Pakaian Seragam','jawatan'=>'PEMBANTU KHIDMAT AM','bil_unit'=>'5'],$extra);}
    private function gridCapture(string $name,$response): void
    {
        $this->workspaceCapture($name,$response);
        if(getenv('ABM_UI_CAPTURE')==='1')foreach(['abm-money.js','anggaran-grid.css','anggaran-grid.js'] as $asset)copy(public_path($asset),storage_path('app/ui-qa/'.$asset));
    }
    public function test_uniform_grid_uses_shared_personnel_names_not_a_category_or_grade_and_get_is_side_effect_free(): void
    {
        [$bid,$estimate]=$this->gridContext();$response=$this->get($this->gridUrl($bid,$estimate))->assertOk();
        $columns=collect($response->viewData('columns'))->keyBy('key');
        $this->assertSame('Jawatan',$columns['jawatan']['label']);$this->assertSame('values',$columns['jawatan']['options']);
        $names=app(\App\Services\RujukanJawatan::class)->names();$this->assertSame($names,$columns['jawatan']['values']);
        $this->assertContains('PEMBANTU KHIDMAT AM',$names);$this->assertContains('PEMBANTU MAKMAL',$names);$this->assertGreaterThan(2,count($names));
        $this->assertFalse($columns->has('kategori2'));$this->assertFalse($columns->has('gred'));
        $this->assertSame('Pasang',$columns['unit']['value']);$this->assertSame('Bil. Pengisian',$columns['bil_unit']['label']);$this->assertSame('Bil. Pasang',$columns['kekerapan']['label']);$this->assertSame('output',$columns['kekerapan']['type']);$this->assertSame('2',$columns['kekerapan']['value']);
        $this->gridCapture('uniform-empty',$response);$this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('rekod_audit',0);
        $this->get(route('estimates.show',[$bid->id,$estimate->id]))->assertOk()->assertSee('Buka Jadual Butiran');
    }

    public function test_four_approved_combinations_save_reopen_edit_with_exact_reference_rates_and_no_grade_lookup(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$definition=app(\App\Services\DefinisiPakaianSeragam::class);$rows=[];
        foreach($definition->references() as $category=>$jobs)foreach($jobs as $job=>$ref)$rows[]=$this->row($place,['kategori1'=>$category,'jawatan'=>$job]);
        $url=$this->gridUrl($bid,$estimate);$this->postJson($url,$this->payload($bid,$estimate,$rows))->assertOk();
        $sources=$estimate->butiran()->orderBy('id')->get();$this->assertCount(4,$sources);
        $this->assertSame(['2300','2300','1500','2500'],$sources->map(fn($source)=>$definition->preview($source->data_sumber))->all());
        $this->assertDatabaseCount('hasil_pengiraan',0);$this->assertDatabaseCount('rekod_audit',4);
        $response=$this->get($url)->assertOk();$this->assertSame($place->id,$response->viewData('records')->first()['tempat_bertugas_id']);$this->gridCapture('uniform-populated',$response);
        $first=$sources->first();$row=$rows[0]+['butiran_id'=>$first->id,'semakan_butiran'=>$first->semakan];$row['bil_unit']='7.125';
        $this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$this->assertSame('7.125',$first->fresh()->data_sumber->bil_unit);
        $this->assertSame('3277.5',$definition->preview($first->fresh()->data_sumber));$this->assertSame($first->id,$first->fresh()->id);
        $row['semakan_butiran']=$first->fresh()->semakan;$this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$this->assertDatabaseCount('rekod_audit',5);
    }

    public function test_other_shared_job_can_remain_incomplete_draft_without_fabricated_price_or_eligibility_rule(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$job=collect(app(\App\Services\RujukanJawatan::class)->names())->first(fn($name)=>!in_array($name,['PEMBANTU KHIDMAT AM','PEMBANTU MAKMAL'],true));
        $this->postJson($this->gridUrl($bid,$estimate),$this->payload($bid,$estimate,[$this->row($place,['jawatan'=>$job])]))->assertOk();
        $source=$estimate->butiran()->sole();$this->assertSame($job,$source->data_sumber->jawatan);$this->assertNull(app(\App\Services\DefinisiPakaianSeragam::class)->preview($source->data_sumber));
        $this->gridCapture('uniform-unpriced',$this->get($this->gridUrl($bid,$estimate))->assertOk());$this->assertDatabaseCount('hasil_pengiraan',0);
    }

    public function test_fixed_unit_rate_grade_and_category_injection_are_rejected_atomically(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        foreach([['unit'=>'Pasang'],['kadar'=>'230.00'],['gred'=>'H1'],['kategori2'=>'PEMBANTU KHIDMAT AM'],['jawatan'=>'Unknown job'],['kategori1'=>'Other'],['bil_unit'=>'1,000'],['kekerapan'=>'2']] as $change){
            $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place),$this->row($place,$change)]))->assertUnprocessable();
            $this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('rekod_audit',0);
        }
    }

    public function test_existing_ppn_010808_mapping_is_required_and_other_os27000_variants_are_not_enabled(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$code=\App\Models\KodAktiviti::where('aktiviti_kewangan_id',$this->activity->id)->sole();
        $code->update(['kod_aktiviti'=>'010801']);$this->get($url)->assertNotFound();$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertNotFound();
        $code->update(['kod_aktiviti'=>'010808']);$this->get($url)->assertOk();
        $this->assertNull(app(\App\Services\KatalogDefinisiAnggaran::class)->untuk($this->unconfigured,$bid));
        PemetaanOs::where('rujukan_os_id',$this->os->id)->delete();$this->get($url)->assertNotFound();$this->assertDatabaseCount('butiran_anggaran',0);
    }

    public function test_partial_and_blank_uniform_drafts_do_not_invent_defaults(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        $blank=$this->row($place,array_fill_keys(['kategori1','jawatan','bil_unit'],null));
        $this->postJson($url,$this->payload($bid,$estimate,[$blank]))->assertOk();$this->assertDatabaseCount('butiran_anggaran',0);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['jawatan'=>null])]))->assertOk();
        $source=$estimate->butiran()->sole();$this->assertNull($source->data_sumber->jawatan);$this->assertSame('2',$source->data_sumber->kekerapan);
        $this->assertNull(app(\App\Services\DefinisiPakaianSeragam::class)->preview($source->data_sumber));$this->gridCapture('uniform-partial',$this->get($url)->assertOk());
    }

    public function test_uniform_preserves_live_guards_legacy_protection_and_audited_removal(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$data=$this->payload($bid,$estimate,[$this->row($place)]);
        $this->postJson($url,$data)->assertOk();$this->postJson($url,$data)->assertConflict();$source=$estimate->butiran()->sole();
        $this->phase->forceFill(['dibuka'=>false])->save();$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertForbidden();$this->phase->forceFill(['dibuka'=>true])->save();
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PemantauBidaan);
        $response=$this->get($url)->assertOk()->assertDontSee('<button type="submit" class="button primary">',false);$this->gridCapture('uniform-readonly',$response);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertForbidden();
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PenyediaAnggaran);
        $this->postJson($url,$this->payload($bid,$estimate,[],[['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan]]))->assertOk();$this->assertSame('source.draft_deleted',RekodAudit::orderByDesc('id')->first()->tindakan);
        $legacy=\App\Models\ButiranAnggaran::forceCreate(['anggaran_id'=>$estimate->id,'ptj_id'=>$bid->ptj_id,'tempat_bertugas_id'=>$place->id,'semakan'=>0,'data_sumber'=>(object)['definition'=>'os25000.v1','SECRET_LEGACY'=>'private']]);
        $this->get($url)->assertOk()->assertDontSee('SECRET_LEGACY');$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['butiran_id'=>$legacy->id,'semakan_butiran'=>0])]))->assertConflict();
    }
    public function test_legacy_pairs_are_preserved_until_explicit_audited_save_and_corrected_to_two(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$definition=app(\App\Services\DefinisiPakaianSeragam::class);
        $legacy=\App\Models\ButiranAnggaran::forceCreate(['anggaran_id'=>$estimate->id,'ptj_id'=>$bid->ptj_id,'tempat_bertugas_id'=>$place->id,'semakan'=>0,
            'data_sumber'=>(object)['definition'=>'os27000.pakaian-seragam.v1','kategori1'=>'Pakaian Seragam','jawatan'=>'PEMBANTU KHIDMAT AM','bil_unit'=>'5','kekerapan'=>'7']]);
        $this->get($this->gridUrl($bid,$estimate))->assertOk();$this->assertSame('7',$legacy->fresh()->data_sumber->kekerapan);
        $this->assertTrue($definition->readable($legacy->data_sumber));$this->assertSame('2300',$definition->preview($legacy->data_sumber));
        $this->postJson($this->gridUrl($bid,$estimate),$this->payload($bid,$estimate,[$this->row($place,['butiran_id'=>$legacy->id,'semakan_butiran'=>0])]))->assertOk();
        $this->assertSame('2',$legacy->fresh()->data_sumber->kekerapan);$this->assertSame($definition::VERSION,$legacy->fresh()->data_sumber->definition);
        $this->assertDatabaseCount('rekod_audit',1);
        foreach(['1','3',null] as $value){$this->postJson($this->gridUrl($bid,$estimate),$this->payload($bid,$estimate,[$this->row($place,['kekerapan'=>$value])]))->assertUnprocessable();}
    }

}
