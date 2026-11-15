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

class TaskaSpreadsheetTest extends TestCase
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
        $this->os = RujukanOs::create(['kod_objek' => '42000', 'butiran_perbelanjaan' => 'Subsidi Yuran TASKA']);
        $this->unconfigured = RujukanOs::create(['kod_objek' => '42000', 'butiran_perbelanjaan' => 'Bayaran Bil Utiliti Sekolah Bantuan Kerajaan (SBK)']);
        $code=\App\Models\KodAktiviti::create(['aktiviti_kewangan_id'=>$this->activity->id,'kod_aktiviti'=>'010102']);
        foreach ([$this->os, $this->unconfigured] as $os) {
            PemetaanOs::create(['aktiviti_kewangan_id' => $this->activity->id, 'rujukan_os_id' => $os->id,
                'kod_aktiviti_id'=>$code->id, 'singkatan_sumber' => 'PPN', 'butiran_asal' => $os->butiran_perbelanjaan, 'status_sumber' => 'Aktif']);
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
    private function row($place,array $extra=[]): array {return array_replace(['tempat_bertugas_id'=>$place->id,'nama'=>'Ibu Contoh','bil_unit'=>'2','bil_bulan'=>'12'],$extra);}
    private function gridCapture(string $name,$response): void
    {
        $this->workspaceCapture($name,$response);
        if(getenv('ABM_UI_CAPTURE')==='1')foreach(['abm-money.js','anggaran-grid.css','anggaran-grid.js'] as $asset)copy(public_path($asset),storage_path('app/ui-qa/'.$asset));
    }
    public function test_taska_grid_uses_parent_name_child_quantity_and_single_prescribed_monthly_reference(): void
    {
        [$bid,$estimate]=$this->gridContext();$response=$this->get($this->gridUrl($bid,$estimate))->assertOk();
        $columns=collect($response->viewData('columns'))->keyBy('key');
        $this->assertSame('Nama Ibu/ Bapa',$columns['nama']['label']);$this->assertSame('text',$columns['nama']['type']);
        $this->assertSame('Bil. Anak',$columns['bil_unit']['label']);$this->assertSame('Anak',$columns['unit']['value']);
        $this->assertSame('output',$columns['kadar']['type']);$this->assertSame('TASKA',$columns['kadar']['referenceKey']);
        $this->assertSame(['TASKA'=>['kadar'=>'180.00']],$response->viewData('references'));
        $this->assertSame(['tempat_bertugas_id','nama','bil_unit','bil_bulan'],$columns->where('type','!=','output')->keys()->all());
        $this->assertFalse($columns->has('kategori1'));$this->gridCapture('taska-empty',$response);
        $this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('rekod_audit',0);
        $this->get(route('estimates.show',[$bid->id,$estimate->id]))->assertOk()->assertSee('Buka Jadual Butiran');
    }

    public function test_multiple_children_and_repeated_parent_names_save_as_independent_sources_and_reopen_edit(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place),$this->row($place,['bil_unit'=>'3','bil_bulan'=>'10'])]))->assertOk();
        [$first,$second]=$estimate->butiran()->orderBy('id')->get()->all();$definition=app(\App\Services\DefinisiTaska::class);
        $this->assertNotSame($first->id,$second->id);$this->assertSame('Ibu Contoh',$first->data_sumber->nama);$this->assertSame('Ibu Contoh',$second->data_sumber->nama);
        $this->assertSame('2',$first->data_sumber->bil_unit);$this->assertSame('3',$second->data_sumber->bil_unit);
        $this->assertSame('4320',$definition->preview($first->data_sumber));$this->assertSame('5400',$definition->preview($second->data_sumber));
        $this->assertEqualsCanonicalizing(['definition','nama','bil_unit','bil_bulan'],array_keys(get_object_vars($first->data_sumber)));
        $response=$this->get($url)->assertOk();$this->assertSame($place->id,$response->viewData('records')->first()['tempat_bertugas_id']);$this->gridCapture('taska-populated',$response);
        $row=$this->row($place,['butiran_id'=>$first->id,'semakan_butiran'=>$first->semakan,'nama'=>'Bapa Contoh & Keluarga','bil_unit'=>'4']);
        $this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$this->assertSame('Bapa Contoh & Keluarga',$first->fresh()->data_sumber->nama);
        $this->assertSame('3',$second->fresh()->data_sumber->bil_unit);$this->assertSame($first->id,$first->fresh()->id);
        $row['semakan_butiran']=$first->fresh()->semakan;$this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();
        $this->assertDatabaseCount('rekod_audit',3);$this->assertDatabaseCount('hasil_pengiraan',0);
        $this->assertSame('19455550390240544880',$definition->preview((object)['bil_unit'=>'9007199254740993','bil_bulan'=>'12']));
    }

    public function test_taska_requires_existing_ppn_010102_mapping_and_does_not_enable_sbk_or_amal(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        $code=\App\Models\KodAktiviti::where('aktiviti_kewangan_id',$this->activity->id)->sole();
        $code->update(['kod_aktiviti'=>'010801']);$this->get($url)->assertNotFound();$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertNotFound();
        $code->update(['kod_aktiviti'=>'010102']);$this->get($url)->assertOk();
        $this->activity->update(['nama'=>'Pendidikan Menengah']);$this->get($url)->assertNotFound();
        $this->activity->update(['nama'=>'Pengurusan Pentadbiran Negeri']);
        $registry=app(\App\Services\KatalogDefinisiAnggaran::class);$this->assertNull($registry->untuk($this->unconfigured,$bid->fresh()));
        $amal=RujukanOs::create(['kod_objek'=>'42000','butiran_perbelanjaan'=>'Asrama Malaysia (AMal)','dikecualikan'=>true]);$this->assertNull($registry->untuk($amal,$bid));
        PemetaanOs::where('rujukan_os_id',$this->os->id)->delete();$this->get($url)->assertNotFound();$this->assertDatabaseCount('butiran_anggaran',0);
    }

    public function test_taska_rejects_reference_and_fixed_overrides_unapproved_fields_and_malformed_quantities(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        foreach([['kadar'=>'0'],['unit'=>'Kanak-kanak'],['kategori1'=>'TASKA'],['nama_anak'=>'Anak Contoh'],['preview'=>'0'],
            ['bil_unit'=>'1e3'],['bil_unit'=>2.5],['bil_bulan'=>'1,000'],['bil_bulan'=>'12 months'],['nama'=>['injected']]] as $change){
            $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place),$this->row($place,$change)]))->assertUnprocessable();
            $this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('rekod_audit',0);
        }
    }

    public function test_partial_taska_drafts_save_without_official_outputs_or_invented_quantity_defaults(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['nama'=>null,'bil_unit'=>null,'bil_bulan'=>null])]))->assertOk();$this->assertDatabaseCount('butiran_anggaran',0);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['bil_unit'=>null,'bil_bulan'=>null])]))->assertOk();
        $source=$estimate->butiran()->sole();$this->assertNull($source->data_sumber->bil_unit);$this->assertNull($source->data_sumber->bil_bulan);
        $this->assertNull(app(\App\Services\DefinisiTaska::class)->preview($source->data_sumber));$this->assertDatabaseCount('hasil_pengiraan',0);
        $this->gridCapture('taska-partial',$this->get($url)->assertOk());
    }

    public function test_cross_definition_legacy_source_is_private_and_cannot_be_overwritten_or_removed(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        $source=\App\Models\ButiranAnggaran::forceCreate(['anggaran_id'=>$estimate->id,'ptj_id'=>$bid->ptj_id,'tempat_bertugas_id'=>$place->id,'semakan'=>0,
            'data_sumber'=>(object)['definition'=>'os42000.sbk.v1','kategori2'=>'Air','kadar'=>'5','SECRET_LEGACY'=>'private']]);
        $response=$this->get($url)->assertOk()->assertDontSee('SECRET_LEGACY');$this->assertFalse($response->viewData('records')->first()['compatible']);$this->gridCapture('taska-legacy',$response);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['butiran_id'=>$source->id,'semakan_butiran'=>0])]))->assertConflict();
        $this->postJson($url,$this->payload($bid,$estimate,[],[['butiran_id'=>$source->id,'semakan_butiran'=>0]]))->assertConflict();
        $this->assertSame('private',$source->fresh()->data_sumber->SECRET_LEGACY);$this->assertDatabaseCount('rekod_audit',0);
    }

    public function test_taska_guards_stale_phase_and_read_only_roles_and_retains_source_only_audited_removal(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$payload=$this->payload($bid,$estimate,[$this->row($place)]);
        $this->postJson($url,$payload)->assertOk();$this->postJson($url,$payload)->assertConflict();
        $source=$estimate->butiran()->sole();$this->phase->forceFill(['dibuka'=>false])->save();$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertForbidden();
        $this->phase->forceFill(['dibuka'=>true])->save();$this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PemantauBidaan);
        $response=$this->get($url)->assertOk()->assertDontSee('<button type="submit" class="button primary">',false);$this->gridCapture('taska-readonly',$response);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertForbidden();
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PenyediaAnggaran);
        $this->postJson($url,$this->payload($bid,$estimate,[],[['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan]]))->assertOk();
        $this->assertDatabaseCount('butiran_anggaran',0);$this->assertSame('source.draft_deleted',RekodAudit::orderByDesc('id')->first()->tindakan);
    }
}
