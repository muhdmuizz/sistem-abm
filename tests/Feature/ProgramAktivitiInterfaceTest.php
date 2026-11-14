<?php

namespace Tests\Feature;

use App\Enums\Peranan;
use App\Enums\StatusBidaan;
use App\Models\AktivitiKewangan;
use App\Models\Bidaan;
use App\Models\Fasa;
use App\Models\Kitaran;
use App\Models\Ptj;
use App\Models\ProgramAktiviti;
use App\Models\TerasStrategik;
use App\Models\StrategiRpm;
use App\Models\PrakarsaRpm;
use App\Models\RekodAudit;
use App\Models\TempatBertugas;
use App\Models\TetapanAksesPtj;
use App\Models\User;
use App\Services\DrafProgramAktiviti;
use App\Services\RuangProgramAktiviti;
use Carbon\CarbonImmutable;
use Database\Seeders\RpmReferenceSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ProgramAktivitiInterfaceTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;
    private Ptj $ptj;
    private AktivitiKewangan $activity;
    private Kitaran $cycle;
    private Fasa $phase;
    private Bidaan $bid;
    private TempatBertugas $place;
    private TerasStrategik $teras;
    private StrategiRpm $strategy;
    private PrakarsaRpm $initiative;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-11-14 12:00:00', 'Asia/Kuala_Lumpur'));
        $this->seed(RpmReferenceSeeder::class);
        $this->teras = TerasStrategik::orderBy('nombor')->firstOrFail();
        $this->strategy = $this->teras->strategi()->orderBy('nombor')->firstOrFail();
        $this->initiative = $this->strategy->prakarsa()->orderBy('nombor')->firstOrFail();
        $this->seed([\Database\Seeders\OrganisationalReferenceSeeder::class, \Database\Seeders\TempatBertugasReferenceSeeder::class]);
        $this->ptj = Ptj::where('kod_ptj','41621001')->sole();
        $this->activity = AktivitiKewangan::where('nama','Pengurusan Pentadbiran Negeri')->sole();
        $this->place = TempatBertugas::where('ptj_id',$this->ptj->id)->where('jenis','sektor')->orderBy('nama')->firstOrFail();
        $this->cycle = Kitaran::create(['tahun' => 2028]);
        $this->phase = Fasa::forceCreate(['kitaran_id' => $this->cycle->id, 'tahun_kitaran' => 2028,
            'jenis' => 'bidaan_awal', 'tarikh_mula' => '2026-11-01', 'tarikh_tamat' => '2026-12-31',
            'jenis_skop' => 'global', 'dibuka' => true, 'pernah_dibuka' => true]);
        $this->phase->skop()->create(['jenis_skop' => 'global']);
        TetapanAksesPtj::create(['kategori_ptj' => 'PPD', 'aktiviti_kewangan_id' => $this->activity->id,
            'rujukan_os_id' => null, 'dibenarkan' => true]);
        $this->actor = User::factory()->create(['name'=>'Penyedia Anggaran']);
        $this->actor->forceFill(['is_active' => true, 'ptj_id' => $this->ptj->id,
            'tempat_bertugas_id' => $this->place->id, 'google_subject' => 'verified-program-fixture'])->save();
        $this->programAssign();
        $this->programAuthenticate($this->actor);
        $this->bid = Bidaan::forceCreate(['ptj_id' => $this->ptj->id, 'aktiviti_kewangan_id' => $this->activity->id,
            'kitaran_id' => $this->cycle->id, 'diaktifkan' => true, 'semakan' => 1])->fresh();
    }

    private function programAuthenticate(User $actor): void
    {
        $this->actingAs($actor)->withSession(['abm_access_revision' => $actor->fresh()->access_revision,
            'abm_authenticated_at' => now()->timestamp, 'abm_last_activity' => now()->timestamp]);
    }

    private function programAssign(?int $place = null): void
    {
        $this->actor->penugasanPeranan()->create(['peranan' => Peranan::PenyediaAnggaran,
            'ptj_id' => $this->ptj->id, 'aktiviti_kewangan_id' => $this->activity->id, 'tempat_bertugas_id' => $place]);
    }

    private function programObjective(): array
    {
        return ['teras_strategik_id' => $this->teras->id, 'strategi_id' => $this->strategy->id, 'prakarsa_id' => $this->initiative->id];
    }

    private function programMetadata(array $replace = []): array
    {
        return array_replace($this->programObjective() + ['nama' => 'Mesyuarat Perancangan',
            'jenis' => 'Umum', 'kategori' => 'Mesyuarat', 'peringkat' => 'Negeri', 'kaedah' => 'Bukan Pakej',
            'tarikh_mula' => '2028-02-01', 'tarikh_tamat' => '2028-02-02', 'komponen' => [],
            'jemputan' => [['kategori' => 'Pegawai', 'klasifikasi' => 9, 'bilangan' => 2],
                ['kategori' => 'AKP', 'klasifikasi' => 1, 'bilangan' => 1]],
            'peserta' => [['kategori' => 'Pegawai', 'klasifikasi' => 9, 'bilangan' => 4],
                ['kategori' => 'Guru', 'klasifikasi' => 10, 'bilangan' => 5],
                ['kategori' => 'AKP', 'klasifikasi' => 8, 'bilangan' => 1],
                ['kategori' => 'Murid Rendah', 'klasifikasi' => 1, 'bilangan' => 8],
                ['kategori' => 'Murid Menengah', 'klasifikasi' => 6, 'bilangan' => 7],
                ['kategori' => 'Lain-lain', 'catatan' => 'Komuniti', 'bilangan' => 2]]], $replace);
    }

    private function programInput(array $metadata, ?ProgramAktiviti $program = null, array $replace = []): array
    {
        $data = ['tempat_bertugas_id' => $this->place->id, 'semakan_bidaan' => $this->bid->fresh()->semakan, 'maklumat' => $metadata];
        if ($program) { $data['semakan_program'] = $program->fresh()->semakan; }
        return array_replace($data, $replace);
    }

    private function programSave(?array $metadata = null, ?ProgramAktiviti $program = null, array $replace = []): ProgramAktiviti
    {
        return app(DrafProgramAktiviti::class)->simpan($this->bid->id, $program?->id, $this->programInput($metadata ?? $this->programMetadata(), $program, $replace));
    }

    private function programDenied(callable $operation, int $code): void
    {
        try { $operation(); $this->fail('Expected programme rejection.'); }
        catch (HttpException $e) { $this->assertSame($code, $e->getStatusCode()); }
        catch (ModelNotFoundException) { $this->assertSame(404, $code); }
        catch (ValidationException) { $this->assertSame(422, $code); }
    }

    private function programAuditFailure(): void
    {
        DB::unprepared(<<<'SQL'
CREATE FUNCTION program_test_audit_failure() RETURNS trigger LANGUAGE plpgsql AS $program_test$
BEGIN RAISE EXCEPTION 'Programme audit unavailable'; END;
$program_test$;
CREATE TRIGGER program_test_audit_failure BEFORE INSERT ON rekod_audit
FOR EACH ROW EXECUTE FUNCTION program_test_audit_failure();
SQL);
    }

    private function url(string $action, ?ProgramAktiviti $program = null): string
    {
        return route('programs.'.$action, $program ? [$this->bid->id,$program->id] : [$this->bid->id]);
    }

    private function capture(string $name, $response): void
    {
        if (getenv('ABM_UI_CAPTURE') !== '1') { return; }
        $directory = storage_path('app/ui-qa');
        if (!is_dir($directory)) { mkdir($directory, 0775, true); }
        $html = preg_replace('/data-deadline="\d+"/', 'data-deadline="'.(time()+3600).'"', $response->getContent());
        file_put_contents($directory.'/'.$name.'.html', str_replace('http://localhost','http://127.0.0.1:4173',$html));
        foreach (['abm.css','abm.js','programs.css','programs.js'] as $asset) { copy(public_path($asset),$directory.'/'.$asset); }
    }

    public function test_empty_create_and_populated_form_gets_have_no_side_effects(): void
    {
        $this->capture('program-empty',$this->get($this->url('index'))->assertOk()->assertSee('Tiada Program/ Aktiviti dalam skop anda.'));
        $create = $this->get($this->url('create'))->assertOk()->assertSee('Teras Strategik')->assertSee('Simpan Draf')
            ->assertSee('min="2028-01-01"',false)->assertDontSee('Tandakan Lengkap');
        $this->capture('program-create',$create);
        $this->assertDatabaseCount('program_aktiviti',0);
        $this->assertDatabaseCount('rekod_audit',0);
        $program = $this->programSave();
        $this->capture('program-edit',$this->get($this->url('edit',$program))->assertOk()->assertSee('Mesyuarat Perancangan'));
        $this->capture('program-list',$this->get($this->url('index'))->assertOk()->assertSee('Mesyuarat Perancangan'));
        $this->assertDatabaseCount('rekod_audit',1);
    }

    public function test_http_create_edit_and_no_op_use_actual_service_and_stable_identity(): void
    {
        $data = $this->programInput($this->programMetadata());
        $this->post($this->url('store'),$data)->assertRedirect();
        $program = ProgramAktiviti::sole();
        $this->assertSame('27',$program->jumlah('peserta'));
        $this->assertSame($this->actor->id,RekodAudit::sole()->pelaku_id);
        $data = $this->programInput($this->programMetadata(['nama'=>'Pindaan Nama']),$program);
        $this->put($this->url('update',$program),$data)->assertRedirect($this->url('edit',$program));
        $this->assertSame('Pindaan Nama',$program->fresh()->nama);
        $this->put($this->url('update',$program),$this->programInput($this->programMetadata(['nama'=>'Pindaan Nama']),$program))->assertRedirect();
        $this->assertDatabaseCount('program_aktiviti',1);
        $this->assertDatabaseCount('rekod_audit',2);
        $this->assertDatabaseCount('anggaran',0);
    }

    public function test_http_draft_allows_other_metadata_missing_but_not_missing_objectives(): void
    {
        $data=$this->programInput($this->programObjective());
        $this->post($this->url('store'),$data)->assertRedirect();
        $this->assertNull(ProgramAktiviti::sole()->nama);
        unset($data['maklumat']['strategi_id']);
        $this->from($this->url('create'))->post($this->url('store'),$data)->assertSessionHasErrors('rekod.maklumat.strategi_id');
        $this->get($this->url('create'))->assertOk()->assertSee('Draf belum disimpan');
        $this->assertDatabaseCount('rekod_audit',1);
    }

    public function test_validation_preserves_inputs_and_never_partially_saves(): void
    {
        $data=$this->programInput($this->programMetadata(['tarikh_mula'=>'2026-11-14','nama'=>'Nama Belum Disimpan']));
        $this->from($this->url('create'))->post($this->url('store'),$data)->assertSessionHasErrors('rekod.maklumat.tarikh_mula')->assertSessionHasInput('maklumat.nama','Nama Belum Disimpan');
        dump(['before_program_get'=>session('errors'),'before_old'=>session()->getOldInput()]);
        $response=$this->get($this->url('create'))->assertOk();
        dump(['after_program_get'=>session('errors'),'view_program_errors'=>$response->viewData('errors')]);
        $this->capture('program-validation',$response);
        $response->assertSee('Draf belum disimpan')->assertSee('Nama Belum Disimpan');
        $this->assertDatabaseCount('program_aktiviti',0);
        $this->assertDatabaseCount('rekod_audit',0);
    }

    public function test_stale_http_edit_preserves_original_revision_and_user_changes(): void
    {
        $program=$this->programSave();
        $data=$this->programInput($this->programMetadata(['nama'=>'Stale unpublished']),$program);
        $this->programSave($this->programMetadata(['nama'=>'Current saved']),$program);
        $this->from($this->url('edit',$program))->put($this->url('update',$program),$data)->assertSessionHasErrors('save');
        $response=$this->get($this->url('edit',$program))->assertOk()->assertSee('Stale unpublished')->assertSee('name="semakan_program" value="1"',false);
        $this->capture('program-conflict',$response);
        $this->assertSame('Current saved',$program->fresh()->nama);
        $this->assertDatabaseCount('rekod_audit',2);
    }

    public function test_removed_child_lists_are_full_replacements_and_same_names_stay_independent(): void
    {
        $program=$this->programSave($this->programMetadata(['kaedah'=>'Pakej','komponen'=>['Penginapan']]));
        $data=$this->programInput($this->programMetadata(),$program);
        unset($data['maklumat']['jemputan'],$data['maklumat']['peserta'],$data['maklumat']['komponen']);
        $this->put($this->url('update',$program),$data)->assertRedirect();
        $this->assertSame('0',$program->jumlah('peserta'));
        $this->assertDatabaseCount('komponen_program',0);
        $this->post($this->url('store'),$this->programInput($this->programMetadata()))->assertRedirect();
        $this->assertDatabaseCount('program_aktiviti',2);
    }

    public function test_list_and_direct_urls_are_scoped_before_counts_and_writes(): void
    {
        $own=$this->programSave();
        $other=TempatBertugas::create(['ptj_id'=>$this->ptj->id,'nama'=>'Other restricted workplace','jenis'=>'sektor']);
        $foreign=$this->programSave($this->programMetadata(['nama'=>'SECRET OTHER PROGRAM']),null,['tempat_bertugas_id'=>$other->id]);
        $this->actor->penugasanPeranan()->delete();$this->programAssign($this->place->id);
        $response=$this->get($this->url('index'))->assertOk()->assertDontSee('SECRET OTHER PROGRAM');
        $this->assertSame(1,$response->viewData('programs')->total());
        $this->get($this->url('edit',$foreign))->assertNotFound();
        $this->put($this->url('update',$foreign),$this->programInput($this->programMetadata(),$foreign))->assertNotFound();
        $this->post($this->url('store'),$this->programInput($this->programMetadata(),null,['tempat_bertugas_id'=>$other->id]))->assertForbidden();
        $this->get($this->url('edit',$own))->assertOk()->assertDontSee('Other restricted workplace');
        $this->assertDatabaseCount('rekod_audit',2);
    }

    public function test_monitor_and_bid_preparer_are_read_only_and_cannot_post(): void
    {
        $program=$this->programSave();
        foreach ([Peranan::PemantauBidaan,Peranan::PenyediaBidaan] as $role) {
            $this->actor->penugasanPeranan()->delete();
            $this->actor->penugasanPeranan()->create(['peranan'=>$role,'ptj_id'=>$role===Peranan::PemantauBidaan ? null : $this->ptj->id,'aktiviti_kewangan_id'=>$role===Peranan::PemantauBidaan ? null : $this->activity->id]);
            $this->get($this->url('index'))->assertOk()->assertDontSee('Tambah Program/ Aktiviti');
            $response=$this->get($this->url('edit',$program))->assertOk()->assertSee('Paparan sahaja')->assertDontSee('Simpan Draf');
            $this->capture('program-readonly',$response);
            $this->get($this->url('create'))->assertForbidden();
            $this->post($this->url('store'),$this->programInput($this->programMetadata()))->assertForbidden();
        }
        $this->assertDatabaseCount('rekod_audit',1);
    }

    public function test_live_phase_workflow_central_session_and_injected_ownership_are_rechecked(): void
    {
        $program=$this->programSave();$data=$this->programInput($this->programMetadata(),$program);
        $this->phase->forceFill(['dibuka'=>false])->save();
        $this->get($this->url('edit',$program))->assertOk()->assertDontSee('Simpan Draf');
        $this->put($this->url('update',$program),$data)->assertForbidden();
        $this->phase->forceFill(['dibuka'=>true])->save();
        $this->bid->forceFill(['status'=>StatusBidaan::DalamSemakan])->save();
        $this->put($this->url('update',$program),$data)->assertSessionHasErrors('save');
        $this->bid->forceFill(['status'=>StatusBidaan::DalamPenyediaan])->save();
        $this->put($this->url('update',$program),$data+['ptj_id'=>$this->ptj->id])->assertSessionHasErrors('rekod');
        TetapanAksesPtj::query()->update(['dibenarkan'=>false]);
        $this->get($this->url('index'))->assertForbidden();
        TetapanAksesPtj::query()->update(['dibenarkan'=>true]);
        session()->put('abm_last_activity',now()->timestamp-1800);
        $this->put($this->url('update',$program),$data)->assertRedirect('/login');
        $this->assertDatabaseCount('rekod_audit',1);
    }

    public function test_superadmin_requires_reason_for_closed_phase_and_keeps_actual_audit_role(): void
    {
        $this->actor->penugasanPeranan()->delete();$this->actor->penugasanPeranan()->create(['peranan'=>Peranan::Superadmin]);
        $this->phase->forceFill(['dibuka'=>false])->save();
        $this->capture('program-override',$this->get($this->url('create'))->assertOk()->assertSee('Alasan pintasan Superadmin'));
        $data=$this->programInput($this->programMetadata());
        $this->post($this->url('store'),$data)->assertSessionHasErrors('reason');
        $this->post($this->url('store'),$data+['reason'=>'Semakan pentadbir'])->assertRedirect();
        $this->assertSame(Peranan::Superadmin,RekodAudit::sole()->peranan);
    }

    public function test_html_escapes_metadata_and_malformed_old_input_is_renderable(): void
    {
        $program=$this->programSave($this->programMetadata(['nama'=>'<script>alert(1)</script>']));
        $this->get($this->url('index'))->assertOk()->assertDontSee('<script>alert(1)</script>',false);
        $data=$this->programInput($this->programMetadata(['nama'=>['bad'],'peserta'=>['bad']]));
        $this->from($this->url('create'))->post($this->url('store'),$data)->assertSessionHasErrors();
        $this->get($this->url('create'))->assertOk()->assertSee('Draf belum disimpan');
    }

    public function test_wrong_bid_identity_and_invalid_pagination_are_rejected(): void
    {
        $program=$this->programSave();
        $otherCycle=Kitaran::create(['tahun'=>2029]);
        $other=Bidaan::forceCreate(['ptj_id'=>$this->ptj->id,'aktiviti_kewangan_id'=>$this->activity->id,'kitaran_id'=>$otherCycle->id]);
        $this->get(route('programs.edit',[$other->id,$program->id]))->assertNotFound();
        $this->put(route('programs.update',[$other->id,$program->id]),$this->programInput($this->programMetadata(),$program))->assertNotFound();
        $this->get($this->url('index').'?page=0')->assertSessionHasErrors('page');
        $this->assertDatabaseCount('rekod_audit',1);
    }
    public function test_http_audit_failure_rolls_back_metadata_children_and_revisions(): void
    {
        $program=$this->programSave();
        $revision=$this->bid->fresh()->semakan;
        $this->programAuditFailure();
        $data=$this->programInput($this->programMetadata(['nama'=>'Must roll back','peserta'=>[]]),$program);
        $this->from($this->url('edit',$program))->put($this->url('update',$program),$data)->assertSessionHasErrors('save');
        $this->assertSame('Mesyuarat Perancangan',$program->fresh()->nama);
        $this->assertSame('27',$program->jumlah('peserta'));
        $this->assertSame($revision,$this->bid->fresh()->semakan);
        $this->assertDatabaseCount('rekod_audit',1);
    }

    public function test_returned_bid_is_editable_but_inactive_bid_is_not(): void
    {
        $this->bid->forceFill(['status'=>StatusBidaan::Dikembalikan])->save();
        $this->get($this->url('create'))->assertOk();
        $this->post($this->url('store'),$this->programInput($this->programMetadata()))->assertRedirect();
        $program=ProgramAktiviti::sole();
        $this->bid->forceFill(['diaktifkan'=>false])->save();
        $this->get($this->url('edit',$program))->assertOk()->assertDontSee('Simpan Draf');
        $this->put($this->url('update',$program),$this->programInput($this->programMetadata(['nama'=>'Blocked']),$program))->assertSessionHasErrors('save');
        $this->assertDatabaseCount('rekod_audit',1);
    }

    public function test_programme_list_paginates_and_reviewer_only_access_is_denied(): void
    {
        $program=$this->programSave();
        for($i=0;$i<20;$i++) { $copy=$program->replicate(); $copy->save(); }
        $first=$this->get($this->url('index'))->assertOk()->assertSee('Seterusnya');
        $this->assertSame(21,$first->viewData('programs')->total());
        $this->assertCount(20,$first->viewData('programs'));
        $this->assertCount(1,$this->get($this->url('index').'?page=2')->assertOk()->viewData('programs'));
        $this->actor->penugasanPeranan()->delete();
        $this->actor->penugasanPeranan()->create(['peranan'=>Peranan::PenyeliaBidaan,'aktiviti_kewangan_id'=>$this->activity->id]);
        $this->get($this->url('index'))->assertForbidden();
        $this->get($this->url('edit',$program))->assertForbidden();
        $this->assertDatabaseCount('rekod_audit',1);
    }

}
