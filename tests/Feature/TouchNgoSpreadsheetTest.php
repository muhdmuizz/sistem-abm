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

class TouchNgoSpreadsheetTest extends TestCase
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
        $this->os = RujukanOs::create(['kod_objek' => '27000', 'butiran_perbelanjaan' => 'Bekalan dan Bahan-bahan Lain']);
        $this->unconfigured = RujukanOs::create(['kod_objek' => '27000', 'butiran_perbelanjaan' => 'Bekalan Alat Pejabat']);
        $code=\App\Models\KodAktiviti::create(['aktiviti_kewangan_id'=>$this->activity->id,'kod_aktiviti'=>'010809']);
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
    private function row($place,array $extra=[]): array {return array_replace(['tempat_bertugas_id'=>$place->id,'tujuan'=>'Van JPNT','bil_unit'=>'3','kadar'=>'200','bil_bulan'=>'12'],$extra);}
    private function gridCapture(string $name,$response): void
    {
        $this->workspaceCapture($name,$response);
        if(getenv('ABM_UI_CAPTURE')==='1')foreach(['abm-money.js','anggaran-grid.css','anggaran-grid.js'] as $asset)copy(public_path($asset),storage_path('app/ui-qa/'.$asset));
    }
    public function test_touchngo_grid_has_monthly_source_fields_without_categories_or_frequency(): void
    {
        [$bid,$estimate]=$this->gridContext();$response=$this->get($this->gridUrl($bid,$estimate))->assertOk();
        $columns=collect($response->viewData('columns'))->keyBy('key');
        $this->assertSame(['tempat_bertugas_id','tujuan','kadar','bil_unit','bil_bulan','preview'],$columns->keys()->all());
        $this->assertSame('Tujuan',$columns['tujuan']['label']);$this->assertTrue($columns['kadar']['money']);
        $this->assertSame([],$response->viewData('references'));$this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('rekod_audit',0);
        $this->gridCapture('touchngo-empty',$response);
    }

    public function test_monthly_projection_saves_reopens_edits_and_preserves_exact_source(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$definition=app(\App\Services\DefinisiTouchNgo::class);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['kadar'=>'2,000.125'])]))->assertOk();
        $source=$estimate->butiran()->sole();$this->assertSame('2000.125',$source->data_sumber->kadar);$this->assertSame('72004.5',$definition->preview($source->data_sumber));
        $row=$this->row($place,['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan]);
        $this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$this->assertSame('7200',$definition->preview($source->fresh()->data_sumber));
        $this->gridCapture('touchngo-populated',$this->get($url)->assertOk());
        $row['semakan_butiran']=$source->fresh()->semakan;$this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();
        $this->assertDatabaseCount('rekod_audit',2);$this->assertDatabaseCount('hasil_pengiraan',0);$this->assertDatabaseCount('butiran_anggaran',1);
    }

    public function test_all_three_existing_contexts_require_their_own_mapping(): void
    {
        [$bid,$estimate]=$this->gridContext();$code=\App\Models\KodAktiviti::where('aktiviti_kewangan_id',$this->activity->id)->sole();
        foreach(['Pengurusan Pentadbiran Negeri'=>'010809','Pendidikan Menengah'=>'030304','Pengurusan Asrama'=>'030316'] as $name=>$value){
            $this->activity->update(['nama'=>$name]);$code->update(['kod_aktiviti'=>$value]);$this->get($this->gridUrl($bid,$estimate))->assertOk();
            $code->update(['kod_aktiviti'=>'999999']);$this->get($this->gridUrl($bid,$estimate))->assertNotFound();
        }
        $this->activity->update(['nama'=>'Pendidikan Rendah']);$this->get($this->gridUrl($bid,$estimate))->assertNotFound();
        $this->assertNull(app(\App\Services\KatalogDefinisiAnggaran::class)->untuk($this->unconfigured,$bid->fresh()));
    }

    public function test_blank_partial_and_tampered_sources_are_handled_atomically(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        $blank=$this->row($place,array_fill_keys(['tujuan','bil_unit','kadar','bil_bulan'],null));
        $this->postJson($url,$this->payload($bid,$estimate,[$blank]))->assertOk();$this->assertDatabaseCount('butiran_anggaran',0);
        foreach([['unit'=>'Unit'],['kategori1'=>'Pukal'],['kekerapan'=>'2'],['preview'=>'7200'],['bil_unit'=>'1,000'],['kadar'=>'2,00'],['bil_bulan'=>'1e2']] as $extra){
            $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place),$this->row($place,$extra)]))->assertUnprocessable();
            $this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('rekod_audit',0);
        }
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['kadar'=>null])]))->assertOk();
        $this->assertNull(app(\App\Services\DefinisiTouchNgo::class)->preview($estimate->butiran()->sole()->data_sumber));
        $this->gridCapture('touchngo-partial',$this->get($url)->assertOk());
    }

    public function test_live_guards_readonly_legacy_and_audited_removal_are_retained(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$data=$this->payload($bid,$estimate,[$this->row($place)]);
        $this->postJson($url,$data)->assertOk();$this->postJson($url,$data)->assertConflict();$source=$estimate->butiran()->sole();
        $this->phase->forceFill(['dibuka'=>false])->save();$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertForbidden();$this->phase->forceFill(['dibuka'=>true])->save();
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PemantauBidaan);
        $this->gridCapture('touchngo-readonly',$this->get($url)->assertOk());$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertForbidden();
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PenyediaAnggaran);
        $this->postJson($url,$this->payload($bid,$estimate,[],[['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan]]))->assertOk();
        $this->assertSame('source.draft_deleted',RekodAudit::orderByDesc('id')->first()->tindakan);
        \App\Models\ButiranAnggaran::forceCreate(['anggaran_id'=>$estimate->id,'ptj_id'=>$bid->ptj_id,'tempat_bertugas_id'=>$place->id,'semakan'=>0,'data_sumber'=>(object)['definition'=>'unknown','SECRET'=>'private']]);
        $this->get($url)->assertOk()->assertDontSee('SECRET');
    }
}
