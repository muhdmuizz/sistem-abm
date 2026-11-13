<?php

namespace Tests\Feature;

use App\Enums\Peranan;
use App\Models\AktivitiKewangan;
use App\Models\Fasa;
use App\Models\Kitaran;
use App\Models\Ptj;
use App\Models\RekodAudit;
use App\Models\User;
use App\Services\JejakAudit;
use App\Services\PembukaanFasa;
use App\Services\PengurusanKitaran;
use Carbon\CarbonImmutable;
use Database\Seeders\OrganisationalReferenceSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class KitaranFasaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Kitaran $cycle;
    private Ptj $ptjA;
    private Ptj $ptjB;
    private AktivitiKewangan $activityA;
    private AktivitiKewangan $activityB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganisationalReferenceSeeder::class);
        $this->ptjA = Ptj::where('kategori','JPNT')->firstOrFail();
        $this->ptjB = Ptj::where('kategori','PPD')->firstOrFail();
        $this->activityA = AktivitiKewangan::firstOrFail();
        $this->activityB = AktivitiKewangan::whereKeyNot($this->activityA->id)->firstOrFail();
        $this->admin = User::factory()->create();
        $this->admin->forceFill(['is_active'=>true,'ptj_id'=>$this->ptjA->id,'google_subject'=>'verified-cycle-admin'])->save();
        $this->admin->penugasanPeranan()->create(['peranan'=>Peranan::Superadmin]);
        $this->admin->refresh();
        $this->actingAs($this->admin)->withSession(['abm_access_revision'=>$this->admin->access_revision,
            'abm_authenticated_at'=>now()->timestamp,'abm_last_activity'=>now()->timestamp]);
        $this->cycle = Kitaran::create(['tahun'=>2028]);
    }

    private function phaseInput(array $replace = []): array
    {
        return array_replace(['jenis'=>'bidaan_awal','tarikh_mula'=>'2028-05-01','tarikh_tamat'=>'2028-05-31',
            'jenis_skop'=>'global'], $replace);
    }

    private function makePhase(array $replace = [], ?Kitaran $cycle = null): Fasa
    {
        return app(PengurusanKitaran::class)->simpanFasa(($cycle ?? $this->cycle)->id, null, $this->phaseInput($replace));
    }

    private function actionPhase(Fasa $phase, string $action, array $replace = []): Fasa
    {
        return app(PengurusanKitaran::class)->tindakan($phase->kitaran_id, $phase->id, $action,
            array_replace(['semakan'=>$phase->semakan,'reason'=>'Tindakan pentadbir yang diluluskan.'], $replace));
    }

    private function phasePath(Fasa $phase, string $suffix = ''): string
    {
        return '/pentadbiran/kitaran/'.$phase->kitaran_id.'/fasa/'.$phase->id.$suffix;
    }

    public function test_year_only_cycle_creation_is_unique_and_attributably_audited(): void
    {
        $this->post('/pentadbiran/kitaran',['tahun'=>2029])->assertRedirect();
        $cycle = Kitaran::where('tahun',2029)->sole();
        $audit = RekodAudit::sole();
        $this->assertSame($this->admin->id,$audit->pelaku_id);
        $this->assertSame($cycle->id,$audit->kitaran_id);
        $this->assertSame('cycle.created',$audit->tindakan);
        $this->post('/pentadbiran/kitaran',['tahun'=>2029])->assertSessionHasErrors('tahun');
        $this->post('/pentadbiran/kitaran',[])->assertSessionHasErrors('tahun');
        $this->assertSame(2,Kitaran::count());
        $this->assertSame(1,RekodAudit::count());
        $form=$this->get('/pentadbiran/kitaran/tambah')->assertOk()->assertSee('Tahun Kitaran')->assertDontSee('name="nama"',false)->assertDontSee('name="reason"',false);
    }

    public function test_new_phase_is_always_closed_even_when_dates_arrive_and_payload_requests_open(): void
    {
        $phase=$this->makePhase(['dibuka'=>true,'pernah_dibuka'=>true,'nombor_pindaan'=>77]);
        $this->assertFalse($phase->dibuka);
        $this->assertFalse($phase->pernah_dibuka);
        $this->assertNull($phase->nombor_pindaan);
        $this->assertSame(0,$phase->semakan);
        $this->assertFalse(app(PembukaanFasa::class)->dibenarkan($this->cycle->id,$this->ptjA->id,$this->activityA->id,
            CarbonImmutable::parse('2028-05-15','Asia/Kuala_Lumpur')));
        $this->assertDatabaseHas('rekod_audit',['tindakan'=>'phase.created','kitaran_id'=>$this->cycle->id]);
    }

    public function test_pindaan_numbers_are_sequential_per_cycle_and_overlapping_phases_are_allowed(): void
    {
        $a=$this->makePhase(['jenis'=>'pindaan']);
        $b=$this->makePhase(['jenis'=>'pindaan']);
        $awal=$this->makePhase();
        $c=$this->makePhase(['jenis'=>'pindaan']);
        $other=Kitaran::create(['tahun'=>2029]);
        $d=$this->makePhase(['jenis'=>'pindaan','tarikh_mula'=>'2029-01-01','tarikh_tamat'=>'2029-02-01'],$other);
        $this->assertSame([1,2,3,1],[$a->nombor_pindaan,$b->nombor_pindaan,$c->nombor_pindaan,$d->nombor_pindaan]);
        $this->assertNull($awal->nombor_pindaan);
        foreach ([$a,$b,$c,$awal] as $phase) { $this->actionPhase($phase,'buka'); }
        $this->assertSame(4,$this->cycle->fasa()->where('dibuka',true)->count());
    }

    public function test_invalid_dates_or_scope_shapes_are_rejected_without_partial_phase_or_audit(): void
    {
        $variants=[
            ['tarikh_mula'=>'2027-12-31'], ['tarikh_tamat'=>'2029-01-01'], ['tarikh_tamat'=>'2028-04-30'],
            ['tarikh_mula'=>'2028-02-30'], ['jenis_skop'=>'ptj'], ['jenis_skop'=>'aktiviti'],
            ['jenis_skop'=>'gabungan'], ['jenis_skop'=>'global','ptj_ids'=>[$this->ptjA->id]],
            ['jenis_skop'=>'ptj','ptj_ids'=>[999999]],
            ['jenis_skop'=>'gabungan','pasangan'=>[
                ['ptj_id'=>$this->ptjA->id,'aktiviti_kewangan_id'=>$this->activityA->id],
                ['ptj_id'=>$this->ptjA->id,'aktiviti_kewangan_id'=>$this->activityA->id],
            ]],
        ];
        foreach ($variants as $variant) {
            $this->post('/pentadbiran/kitaran/'.$this->cycle->id.'/fasa',$this->phaseInput($variant))->assertSessionHasErrors();
        }
        $this->assertDatabaseCount('fasa',0); $this->assertDatabaseCount('skop_fasa',0); $this->assertDatabaseCount('rekod_audit',0);
    }

    public function test_each_scope_dimension_matches_only_its_explicit_context_and_pair_rows_do_not_cross_combine(): void
    {
        $resolver=app(PembukaanFasa::class);
        $when=CarbonImmutable::parse('2028-05-15','Asia/Kuala_Lumpur');
        $cases=[
            ['jenis_skop'=>'global'],
            ['jenis_skop'=>'ptj','ptj_ids'=>[$this->ptjA->id]],
            ['jenis_skop'=>'aktiviti','aktiviti_ids'=>[$this->activityA->id]],
            ['jenis_skop'=>'gabungan','pasangan'=>[
                ['ptj_id'=>$this->ptjA->id,'aktiviti_kewangan_id'=>$this->activityA->id],
                ['ptj_id'=>$this->ptjB->id,'aktiviti_kewangan_id'=>$this->activityB->id],
            ]],
        ];
        foreach ($cases as $scope) {
            $phase=$this->actionPhase($this->makePhase($scope),'buka');
            $this->assertTrue($resolver->dibenarkan($this->cycle->id,$this->ptjA->id,$this->activityA->id,$when));
            if ($scope['jenis_skop']==='ptj') {
                $this->assertFalse($resolver->dibenarkan($this->cycle->id,$this->ptjB->id,$this->activityA->id,$when));
                $this->assertTrue($resolver->dibenarkan($this->cycle->id,$this->ptjA->id,$this->activityB->id,$when));
            }
            if ($scope['jenis_skop']==='aktiviti') {
                $this->assertFalse($resolver->dibenarkan($this->cycle->id,$this->ptjA->id,$this->activityB->id,$when));
                $this->assertTrue($resolver->dibenarkan($this->cycle->id,$this->ptjB->id,$this->activityA->id,$when));
            }
            if ($scope['jenis_skop']==='gabungan') {
                $this->assertFalse($resolver->dibenarkan($this->cycle->id,$this->ptjA->id,$this->activityB->id,$when));
                $this->assertFalse($resolver->dibenarkan($this->cycle->id,$this->ptjB->id,$this->activityA->id,$when));
                $this->assertTrue($resolver->dibenarkan($this->cycle->id,$this->ptjB->id,$this->activityB->id,$when));
            }
            $this->actionPhase($phase,'tutup');
        }
        $this->assertFalse($resolver->dibenarkan($this->cycle->id,999999,$this->activityA->id,$when));
    }

    public function test_window_boundaries_use_malaysia_calendar_without_automatic_flag_changes(): void
    {
        $phase=$this->actionPhase($this->makePhase(),'buka');
        $resolver=app(PembukaanFasa::class);
        foreach (['2028-04-30'=>false,'2028-05-01'=>true,'2028-05-31'=>true,'2028-06-01'=>false] as $date=>$allowed) {
            $this->assertSame($allowed,$resolver->dibenarkan($this->cycle->id,$this->ptjA->id,$this->activityA->id,CarbonImmutable::parse($date,'Asia/Kuala_Lumpur')));
        }
        $this->assertTrue($resolver->dibenarkan($this->cycle->id,$this->ptjA->id,$this->activityA->id,CarbonImmutable::parse('2028-04-30T16:00:00Z')));
        $this->assertTrue($phase->fresh()->dibuka);
    }

    public function test_open_close_reopen_actions_are_explicit_reasoned_audited_and_stale_safe(): void
    {
        $phase=$this->makePhase();
        $path=$this->phasePath($phase,'/tindakan/');
        $this->post($path.'buka',['semakan'=>0,'reason'=>''])->assertSessionHasErrors('reason');
        $this->post($path.'buka',['semakan'=>0,'reason'=>'Buka fasa.'])->assertRedirect();
        $this->assertTrue($phase->fresh()->dibuka);
        $this->post($path.'tutup',['semakan'=>0,'reason'=>'Stale'])->assertConflict();
        $this->post($path.'tutup',['semakan'=>1,'reason'=>'Tutup fasa.'])->assertRedirect();
        $this->assertFalse($phase->fresh()->dibuka);
        $this->post($path.'buka',['semakan'=>2,'reason'=>'Wrong action'])->assertConflict();
        $this->post($path.'buka-semula',['semakan'=>2,'reason'=>'Buka semula terpilih.'])->assertRedirect();
        $this->assertTrue($phase->fresh()->dibuka);
        $this->assertSame(3,$phase->fresh()->semakan);
        $this->assertSame(['phase.created','phase.buka','phase.tutup','phase.buka-semula'],RekodAudit::orderBy('id')->pluck('tindakan')->all());
    }

    public function test_extension_preserves_closed_state_identity_and_scope_and_stays_inside_year(): void
    {
        $phase=$this->makePhase(['jenis'=>'pindaan','jenis_skop'=>'ptj','ptj_ids'=>[$this->ptjA->id]]);
        $scopeId=$phase->skop()->sole()->id;
        $path=$this->phasePath($phase,'/tindakan/lanjutkan');
        foreach (['2028-05-31','2028-05-01','2029-01-01'] as $end) {
            $this->post($path,['semakan'=>0,'reason'=>'Extend','tarikh_tamat'=>$end])->assertSessionHasErrors('tarikh_tamat');
        }
        $this->post($path,['semakan'=>0,'reason'=>'Lanjutkan tempoh.','tarikh_tamat'=>'2028-06-30'])->assertRedirect();
        $this->assertFalse($phase->fresh()->dibuka);
        $this->assertSame(1,$phase->fresh()->nombor_pindaan);
        $this->assertSame($scopeId,$phase->skop()->sole()->id);
        $this->assertSame('2028-06-30',$phase->fresh()->tarikh_tamat->toDateString());
    }

    public function test_edit_preserves_phase_identity_and_open_state_and_changes_scope_atomically(): void
    {
        $phase=$this->actionPhase($this->makePhase(['jenis'=>'pindaan']),'buka');
        $input=$this->phaseInput(['jenis'=>'pindaan','jenis_skop'=>'ptj','ptj_ids'=>[$this->ptjB->id],
            'semakan'=>$phase->semakan,'reason'=>'Hadkan skop pembukaan.']);
        $this->put($this->phasePath($phase),$input)->assertRedirect();
        $this->assertTrue($phase->fresh()->dibuka);
        $this->assertSame(1,$phase->fresh()->nombor_pindaan);
        $this->assertSame($this->ptjB->id,$phase->skop()->sole()->ptj_id);
        $this->put($this->phasePath($phase),$input)->assertConflict();
        $input['semakan']=$phase->fresh()->semakan; $input['jenis']='bidaan_awal';
        $this->put($this->phasePath($phase),$input)->assertSessionHasErrors('jenis');
    }

    public function test_unchanged_scope_tuples_retain_ids_and_scopes_do_not_grant_when_missing(): void
    {
        $phase=$this->makePhase();
        $scopeId=$phase->skop()->sole()->id;
        $this->put($this->phasePath($phase),$this->phaseInput(['semakan'=>0,'reason'=>'Semak tarikh.']))->assertRedirect();
        $this->assertSame($scopeId,$phase->skop()->sole()->id);
        $phase=$this->actionPhase($phase->fresh(),'buka');
        $phase->skop()->delete();
        $this->assertFalse(app(PembukaanFasa::class)->dibenarkan($this->cycle->id,$this->ptjA->id,$this->activityA->id,CarbonImmutable::parse('2028-05-15','Asia/Kuala_Lumpur')));
    }

    public function test_nested_cycle_phase_mismatch_cannot_read_or_mutate_another_cycle(): void
    {
        $phase=$this->makePhase(); $other=Kitaran::create(['tahun'=>2029]);
        $base='/pentadbiran/kitaran/'.$other->id.'/fasa/'.$phase->id;
        $this->get($base.'/pinda')->assertNotFound();
        $this->post($base.'/tindakan/buka',['semakan'=>0,'reason'=>'Wrong parent'])->assertNotFound();
        $this->assertFalse($phase->fresh()->dibuka);
    }

    public function test_non_admin_roles_are_denied_cycle_phase_and_action_routes(): void
    {
        $phase=$this->makePhase();
        foreach (array_filter(Peranan::cases(),fn($r)=>$r!==Peranan::Superadmin) as $role) {
            $this->admin->penugasanPeranan()->delete();
            $tuple=['peranan'=>$role];
            if(in_array($role,[Peranan::KetuaBidaan,Peranan::PenyeliaBidaan,Peranan::PenyediaBidaan,Peranan::PenyediaAnggaran])) $tuple['aktiviti_kewangan_id']=$this->activityA->id;
            if(in_array($role,[Peranan::PenyediaBidaan,Peranan::PenyediaAnggaran])) $tuple['ptj_id']=$this->ptjA->id;
            $this->admin->penugasanPeranan()->create($tuple);
            $this->get('/pentadbiran/kitaran')->assertForbidden();
            $this->get('/pentadbiran/kitaran/'.$this->cycle->id.'/fasa/tambah')->assertForbidden();
            $this->get($this->phasePath($phase,'/pinda'))->assertForbidden();
            $this->post('/pentadbiran/kitaran',['tahun'=>2029])->assertForbidden();
            $this->post('/pentadbiran/kitaran/'.$this->cycle->id.'/fasa',$this->phaseInput())->assertForbidden();
            $this->put($this->phasePath($phase),$this->phaseInput(['semakan'=>0,'reason'=>'Test']))->assertForbidden();
            $this->post($this->phasePath($phase,'/tindakan/buka'),['semakan'=>0,'reason'=>'Test'])->assertForbidden();
        }
        $this->assertSame(1,Fasa::count());
        $this->assertFalse($phase->fresh()->dibuka);
    }

    public function test_service_rechecks_revoked_session_and_role_before_mutating(): void
    {
        $this->admin->increment('access_revision');
        try { app(PengurusanKitaran::class)->ciptaKitaran(['tahun'=>2029]); $this->fail('Revoked session allowed.'); }
        catch(\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(403,$e->getStatusCode()); }
        $this->withSession(['abm_access_revision'=>$this->admin->fresh()->access_revision]);
        $this->admin->penugasanPeranan()->delete();
        try { $this->makePhase(); $this->fail('Revoked role allowed.'); }
        catch(\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(403,$e->getStatusCode()); }
        $this->assertDatabaseCount('fasa',0); $this->assertDatabaseCount('rekod_audit',0);
    }

    public function test_audit_failure_rolls_back_phase_scopes_and_sequence_allocation(): void
    {
        $this->mock(JejakAudit::class,fn($mock)=>$mock->shouldReceive('jalankan')->once()->andReturnUsing(function($role,$operation) {
            $operation(); throw new RuntimeException('Audit unavailable.');
        }));
        try { $this->makePhase(['jenis'=>'pindaan']); $this->fail('Audit failure ignored.'); }
        catch(RuntimeException $e) { $this->assertSame('Audit unavailable.',$e->getMessage()); }
        $this->assertDatabaseCount('fasa',0); $this->assertDatabaseCount('skop_fasa',0); $this->assertDatabaseCount('rekod_audit',0);
    }

    public function test_audit_failure_restores_an_open_phase_after_attempted_close(): void
    {
        $phase=$this->actionPhase($this->makePhase(),'buka');
        $this->mock(JejakAudit::class,fn($mock)=>$mock->shouldReceive('jalankan')->once()->andReturnUsing(function($role,$operation) {
            $operation(); throw new RuntimeException('Audit unavailable.');
        }));
        try { $this->actionPhase($phase,'tutup'); $this->fail('Audit failure ignored.'); }
        catch(RuntimeException $e) { $this->assertSame('Audit unavailable.',$e->getMessage()); }
        $this->assertTrue($phase->fresh()->dibuka); $this->assertSame(1,$phase->fresh()->semakan);
        $this->assertDatabaseCount('rekod_audit',2);
    }

    public function test_database_rejects_cross_year_dates_even_outside_service(): void
    {
        $phase=$this->makePhase();
        $this->expectException(QueryException::class);
        DB::table('fasa')->where('id',$phase->id)->update(['tarikh_tamat'=>'2029-01-01']);
    }

    public function test_database_rejects_scope_shape_and_phase_kind_mismatch(): void
    {
        $phase=$this->makePhase();
        $this->expectException(QueryException::class);
        DB::table('skop_fasa')->insert(['fasa_id'=>$phase->id,'jenis_skop'=>'ptj','ptj_id'=>$this->ptjA->id]);
    }

    public function test_database_rejects_duplicate_pindaan_number(): void
    {
        $a=$this->makePhase(['jenis'=>'pindaan']); $b=$this->makePhase(['jenis'=>'pindaan']);
        $this->expectException(QueryException::class);
        DB::table('fasa')->where('id',$b->id)->update(['nombor_pindaan'=>$a->nombor_pindaan]);
    }

    public function test_pages_show_only_valid_controls_and_capture_real_blade_fixtures(): void
    {
        $phase=$this->makePhase();
        $closed=$this->get('/pentadbiran/kitaran?tahun=2028')->assertOk()->assertSee('Ditutup')->assertSee('Buka</a>',false)->assertDontSee('Tutup</a>',false);
        $form=$this->get('/pentadbiran/kitaran/'.$this->cycle->id.'/fasa/tambah')->assertOk()->assertSee('Skop Pembukaan')->assertSee('Fasa baharu bermula Ditutup');
        $this->get($this->phasePath($phase,'/pinda'))->assertOk()->assertSee('Alasan perubahan');
        $this->get($this->phasePath($phase,'/tindakan/buka'))->assertOk()->assertSee('Alasan tindakan');
        $phase=$this->actionPhase($phase,'buka');
        $this->get('/pentadbiran/kitaran?tahun=2028')->assertOk()->assertSee('Tutup</a>',false)->assertDontSee('Buka</a>',false);
        $this->actionPhase($phase,'tutup');
        $this->get('/pentadbiran/kitaran?tahun=2028')->assertSee('Buka Semula</a>',false);
        if(getenv('ABM_UI_CAPTURE')==='1') {
            $directory=storage_path('app/ui-qa'); if(!is_dir($directory)) mkdir($directory,0775,true);
            foreach(['cycles'=>$closed,'phase-form'=>$form] as $name=>$response) file_put_contents($directory.'/'.$name.'.html',str_replace('http://localhost','http://127.0.0.1:4173',$response->getContent()));
            foreach(['abm.css','abm.js','fasa.js'] as $asset) copy(public_path($asset),$directory.'/'.$asset);
        }
    }
}
