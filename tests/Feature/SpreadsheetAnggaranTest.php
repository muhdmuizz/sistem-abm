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

class SpreadsheetAnggaranTest extends TestCase
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
        $this->os = RujukanOs::create(['kod_objek' => '25000', 'butiran_perbelanjaan' => 'Bahan-bahan Makanan dan Minuman']);
        $this->unconfigured = RujukanOs::create(['kod_objek' => '15000', 'butiran_perbelanjaan' => 'Faedah Kewangan Lain']);
        foreach ([$this->os, $this->unconfigured] as $os) {
            PemetaanOs::create(['aktiviti_kewangan_id' => $this->activity->id, 'rujukan_os_id' => $os->id,
                'singkatan_sumber' => 'PPN', 'butiran_asal' => $os->butiran_perbelanjaan, 'status_sumber' => 'Aktif']);
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
    private function row($place,array $extra=[]): array {return array_replace(['tempat_bertugas_id'=>$place->id,'kategori1'=>'Air Minuman','bil_unit'=>'2.625'],$extra);}
    private function gridCapture(string $name,$response): void
    {
        $this->workspaceCapture($name,$response);
        if(getenv('ABM_UI_CAPTURE')==='1')foreach(['abm-money.js','anggaran-grid.css','anggaran-grid.js'] as $asset)copy(public_path($asset),storage_path('app/ui-qa/'.$asset));
    }
    public function test_grid_gets_are_scoped_and_never_create_drafts_and_keep_reference_values(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();
        $response=$this->get($this->gridUrl($bid,$estimate))->assertOk()->assertSee('Tambah Baris')->assertSee('Simpan Draf')->assertDontSee('data_sumber');
        $this->gridCapture('grid-empty',$response);
        $this->get(route('estimates.show',[$bid->id,$estimate->id]))->assertOk()->assertSee('Buka Jadual Butiran');
        $refs=app(\App\Services\DefinisiAnggaran::class)->references();
        $this->assertSame('Balang/ Paket',$refs['Gula-gula']['unit']);$this->assertSame('15.00',$refs['Air Minuman']['kadar']);
        $this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('rekod_audit',0);
    }
    public function test_actual_multirow_json_save_reopen_edit_and_no_op_preserve_source_ids_and_decimal_text(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place),$this->row($place)]))->assertOk();
        $sources=$estimate->butiran()->orderBy('id')->get();$this->assertCount(2,$sources);$first=$sources->first();
        $this->assertSame('2.625',$first->data_sumber->bil_unit);$this->assertSame('39.375',app(\App\Services\DefinisiAnggaran::class)->preview($first->data_sumber));
        $this->assertDatabaseCount('rekod_audit',2);$this->assertDatabaseCount('hasil_pengiraan',0);
        $response=$this->get($url)->assertOk();$this->assertSame($place->id,$response->viewData('records')->first()['tempat_bertugas_id']);$this->gridCapture('grid-populated',$response);
        $row=$this->row($place,['butiran_id'=>$first->id,'semakan_butiran'=>$first->semakan,'bil_unit'=>'3']);
        $this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();
        $this->assertSame('3',$first->fresh()->data_sumber->bil_unit);$this->assertSame($first->id,$first->fresh()->id);
        $row['semakan_butiran']=$first->fresh()->semakan;
        $this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$this->assertDatabaseCount('rekod_audit',3);
    }
    public function test_partial_drafts_are_saved_but_added_blank_rows_do_not_create_records(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['kategori1'=>null,'bil_unit'=>null])]))->assertOk();
        $this->assertDatabaseCount('butiran_anggaran',0);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['bil_unit'=>null])]))->assertOk();
        $this->assertDatabaseCount('butiran_anggaran',1);$this->assertNull(app(\App\Services\DefinisiAnggaran::class)->preview($estimate->butiran()->sole()->data_sumber));
    }
    public function test_unapproved_fields_categories_numeric_formats_and_sparse_rows_are_rejected_atomically(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        foreach([['kategori1'=>'Other'],['bil_unit'=>'1e4'],['bil_unit'=>2.5],['bil_unit'=>'1,000'],['kadar'=>'0'],['program_id'=>1]] as $replace){
            $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place),$this->row($place,$replace)]))->assertUnprocessable();
            $this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('rekod_audit',0);
        }
        $data=$this->payload($bid,$estimate,[$this->row($place)]);$data['rekod']=[3=>$data['rekod'][0]];
        $this->postJson($url,$data)->assertUnprocessable();
    }
    public function test_stale_parent_and_row_revisions_are_rejected_without_partial_saves(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$old=$this->payload($bid,$estimate,[$this->row($place)]);
        $this->postJson($url,$old)->assertOk();$this->postJson($url,$old)->assertConflict();
        $source=$estimate->butiran()->sole();
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place),$this->row($place,['butiran_id'=>$source->id,'semakan_butiran'=>0])]))->assertConflict();
        $this->assertDatabaseCount('butiran_anggaran',1);$this->assertDatabaseCount('rekod_audit',1);
    }
    public function test_live_phase_central_workflow_session_and_role_restrictions_survive_grid_routes(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        $this->phase->forceFill(['dibuka'=>false])->save();
        $this->get($url)->assertOk()->assertDontSee('<button type="submit" class="button primary">', false);$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertForbidden();
        $this->phase->forceFill(['dibuka'=>true])->save();$bid->forceFill(['status'=>StatusBidaan::DalamSemakan])->save();
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertConflict();
        $bid->forceFill(['status'=>StatusBidaan::DalamPenyediaan])->save();
        TetapanAksesPtj::where('rujukan_os_id',$this->os->id)->update(['dibenarkan'=>false]);$this->get($url)->assertNotFound();
        TetapanAksesPtj::where('rujukan_os_id',$this->os->id)->update(['dibenarkan'=>true]);
        $this->travel(31)->minutes();$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertRedirect('/login');
        $this->assertDatabaseCount('butiran_anggaran',0);
    }
    public function test_workplace_scoping_blocks_id_injection_and_old_to_new_workplace_takeover(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        $otherPlace=\App\Models\TempatBertugas::create(['ptj_id'=>$bid->ptj_id,'nama'=>'Sektor Pengurusan','jenis'=>'sektor']);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($otherPlace)]))->assertOk();$otherSource=$estimate->butiran()->sole();
        $this->operator->penugasanPeranan()->delete();$this->operator->penugasanPeranan()->create(['peranan'=>Peranan::PenyediaAnggaran,'ptj_id'=>$bid->ptj_id,'aktiviti_kewangan_id'=>$bid->aktiviti_kewangan_id,'tempat_bertugas_id'=>$place->id]);
        $response=$this->get($url)->assertOk();$this->assertSame(0,$response->viewData('sources')->total());
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['butiran_id'=>$otherSource->id,'semakan_butiran'=>$otherSource->semakan])]))->assertForbidden();
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($otherPlace)]))->assertForbidden();
        $this->assertDatabaseCount('rekod_audit',1);
    }
    public function test_monitor_and_bid_preparer_can_read_but_cannot_save_and_opaque_legacy_rows_are_preserved(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        $source=\App\Models\ButiranAnggaran::forceCreate(['anggaran_id'=>$estimate->id,'ptj_id'=>$bid->ptj_id,'tempat_bertugas_id'=>$place->id,'data_sumber'=>(object)['SECRET_LEGACY'=>'do not expose']]);
        $response=$this->get($url)->assertOk()->assertDontSee('SECRET_LEGACY')->assertDontSee('do not expose');$this->gridCapture('grid-legacy',$response);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['butiran_id'=>$source->id,'semakan_butiran'=>0])]))->assertConflict();
        foreach([Peranan::PemantauBidaan,Peranan::PenyediaBidaan] as $role){
            $this->operator->penugasanPeranan()->delete();$this->workspaceRole($role);
            $response=$this->get($url)->assertOk()->assertDontSee('<button type="submit" class="button primary">', false);$this->gridCapture('grid-readonly',$response);
            $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertForbidden();
        }
        $this->assertSame('do not expose',$source->fresh()->data_sumber->SECRET_LEGACY);
    }
    public function test_duplicate_client_rows_get_independent_ids_and_authorised_deletion_is_audited_in_scoped_history(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place),$this->row($place)]))->assertOk();
        $source=$estimate->butiran()->orderBy('id')->first();
        $this->postJson($url,$this->payload($bid,$estimate,[],[['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan]]))->assertOk();
        $this->assertDatabaseCount('butiran_anggaran',1);$this->assertSame('source.draft_deleted',RekodAudit::orderByDesc('id')->first()->tindakan);
        $this->get(route('estimates.show',[$bid->id,$estimate->id]))->assertOk()->assertSee('Draf Butiran dipadam')->assertDontSee('data_sumber_json');
    }
    public function test_actual_audit_failure_rolls_back_all_rows_and_preserves_inputs(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();
        DB::unprepared("CREATE FUNCTION grid_audit_fail() RETURNS trigger LANGUAGE plpgsql AS $$ BEGIN RAISE EXCEPTION 'Audit unavailable'; END; $$; CREATE TRIGGER grid_audit_fail BEFORE INSERT ON rekod_audit FOR EACH ROW EXECUTE FUNCTION grid_audit_fail();");
        $this->postJson($this->gridUrl($bid,$estimate),$this->payload($bid,$estimate,[$this->row($place)]))->assertStatus(503);
        $this->assertDatabaseCount('butiran_anggaran',0);$this->assertSame(1,$bid->fresh()->semakan);$this->assertSame(1,$estimate->fresh()->semakan);
    }
    public function test_delete_audit_failure_rolls_back_other_row_edits_and_preserves_deleted_source(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place),$this->row($place)]))->assertOk();
        [$first,$second]=$estimate->butiran()->orderBy('id')->get()->all();
        $bidRevision=$bid->fresh()->semakan;$estimateRevision=$estimate->fresh()->semakan;
        DB::unprepared("CREATE FUNCTION grid_delete_audit_fail() RETURNS trigger LANGUAGE plpgsql AS $$ BEGIN IF NEW.tindakan='source.draft_deleted' THEN RAISE EXCEPTION 'Delete audit unavailable'; END IF; RETURN NEW; END; $$; CREATE TRIGGER grid_delete_audit_fail BEFORE INSERT ON rekod_audit FOR EACH ROW EXECUTE FUNCTION grid_delete_audit_fail();");
        $this->postJson($url,$this->payload($bid,$estimate,
            [$this->row($place,['butiran_id'=>$first->id,'semakan_butiran'=>$first->semakan,'bil_unit'=>'9'])],
            [['butiran_id'=>$second->id,'semakan_butiran'=>$second->semakan]]))->assertStatus(503);
        $this->assertDatabaseCount('butiran_anggaran',2);$this->assertDatabaseCount('rekod_audit',2);
        $this->assertSame('2.625',$first->fresh()->data_sumber->bil_unit);
        $this->assertNotNull($second->fresh());$this->assertSame($bidRevision,$bid->fresh()->semakan);
        $this->assertSame($estimateRevision,$estimate->fresh()->semakan);
    }
    public function test_superadmin_override_requires_reason_and_keeps_actual_actor(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::Superadmin);$this->phase->forceFill(['dibuka'=>false])->save();
        $this->get($url)->assertOk()->assertSee('Alasan pintasan Superadmin');
        $data=$this->payload($bid,$estimate,[$this->row($place)]);$this->postJson($url,$data)->assertUnprocessable();
        $this->postJson($url,$data+['reason'=>'Semakan pentadbir'])->assertOk();$this->assertSame(Peranan::Superadmin,RekodAudit::sole()->peranan);
    }
    public function test_exact_preview_never_rounds_and_unsupported_os_and_wrong_bid_are_not_exposed(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$definition=app(\App\Services\DefinisiAnggaran::class);
        $this->assertSame('135107988821114895',$definition->preview((object)['kategori1'=>'Air Minuman','bil_unit'=>'9007199254740993']));
        $this->assertSame('-0.015',$definition->preview((object)['kategori1'=>'Air Minuman','bil_unit'=>'-0.001']));
        $estimate->forceFill(['rujukan_os_id'=>$this->unconfigured->id])->save();$this->get($this->gridUrl($bid,$estimate))->assertNotFound();
    }
}
