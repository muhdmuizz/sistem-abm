<?php

namespace Tests\Feature;

use App\Enums\Peranan;
use App\Models\AktivitiKewangan;
use App\Models\Ptj;
use App\Models\RekodAudit;
use App\Models\RujukanOs;
use App\Models\TetapanAksesPtj;
use App\Models\User;
use App\Services\AksesPeruntukan;
use App\Services\JejakAudit;
use App\Services\PengurusanAksesPeruntukan;
use Database\Seeders\AktivitiPermissionDefaultSeeder;
use Database\Seeders\OrganisationalReferenceSeeder;
use Database\Seeders\OsReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class PengurusanAksesPeruntukanTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private AktivitiKewangan $activity;
    private Ptj $ppd;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([OrganisationalReferenceSeeder::class, OsReferenceSeeder::class, AktivitiPermissionDefaultSeeder::class]);
        $jpnt = Ptj::where('kategori', 'JPNT')->firstOrFail();
        $this->ppd = Ptj::where('kategori', 'PPD')->firstOrFail();
        $this->activity = AktivitiKewangan::where('nama', 'Pengurusan Pentadbiran Negeri')->sole();
        $this->admin = User::factory()->create();
        $this->admin->forceFill(['is_active' => true, 'ptj_id' => $jpnt->id, 'google_subject' => 'verified-admin'])->save();
        $this->admin->penugasanPeranan()->create(['peranan' => Peranan::Superadmin]);
        $this->admin->refresh();
        $this->actingAs($this->admin)->withSession(['abm_authenticated_at' => now()->timestamp,
            'abm_last_activity' => now()->timestamp, 'abm_access_revision' => $this->admin->access_revision]);
    }

    private function service(): PengurusanAksesPeruntukan { return app(PengurusanAksesPeruntukan::class); }

    private function state(string $kind, string $key, ?AktivitiKewangan $activity = null): array
    {
        return $this->service()->keadaan($this->service()->sasaran($kind, $key), $activity ?? $this->activity);
    }

    private function packet(string $kind, string $key, ?AktivitiKewangan $activity = null): array
    {
        $state = $this->state($kind, $key, $activity);
        $settings = [];
        foreach ($state['rows'] as $row) {
            $settings[$row['os']->id] = ['configured' => (int) $row['configured'], 'allowed' => (int) ($row['allowed'] === true)];
        }
        return ['revision' => $state['revision'], 'reason' => 'Pelarasan akses yang diluluskan.',
            'activity' => ['configured' => $kind === 'kategori' ? 1 : (int) $state['parent']['configured'],
                'allowed' => (int) ($state['parent']['allowed'] === true)], 'settings' => $settings];
    }

    private function url(string $kind, string $key, ?AktivitiKewangan $activity = null): string
    {
        return '/pentadbiran/aktiviti-kewangan/'.$kind.'/'.$key.'/aktiviti/'.($activity ?? $this->activity)->id;
    }

    public function test_category_save_is_attributed_and_enabling_parent_never_grants_unconfigured_os(): void
    {
        $input = $this->packet('kategori', 'ppd');
        $this->post($this->url('kategori', 'ppd'), $input)->assertRedirect();
        $state = $this->state('kategori', 'ppd');
        $this->assertSame(0, $state['count']);
        $this->assertSame(15, TetapanAksesPtj::count());
        $this->assertSame(0, TetapanAksesPtj::whereNotNull('rujukan_os_id')->count());
        $audit = RekodAudit::sole();
        $this->assertSame($this->admin->id, $audit->pelaku_id);
        $this->assertSame('access.category_permissions_updated', $audit->tindakan);
        $this->assertSame($this->activity->id, $audit->aktiviti_kewangan_id);
        $this->assertSame('Pelarasan akses yang diluluskan.', $audit->alasan);
    }

    public function test_category_changes_affect_inheritors_and_preserve_both_true_and_false_ptj_overrides(): void
    {
        $all = Ptj::where('kategori', 'PPD')->get();
        $os = $this->state('kategori', 'ppd')['rows']->first()['os'];
        foreach ([[$all[0], false], [$all[1], true]] as [$ptj, $value]) {
            TetapanAksesPtj::create(['ptj_id' => $ptj->id, 'aktiviti_kewangan_id' => $this->activity->id,
                'rujukan_os_id' => $os->id, 'dibenarkan' => $value]);
        }
        $input = $this->packet('kategori', 'ppd');
        $input['settings'][$os->id] = ['configured' => 1, 'allowed' => 1];
        $this->post($this->url('kategori', 'ppd'), $input)->assertRedirect();
        $resolver = app(AksesPeruntukan::class);
        $this->assertFalse($resolver->bolehAksesOs($all[0]->id, $this->activity->id, $os->id));
        $this->assertTrue($resolver->bolehAksesOs($all[1]->id, $this->activity->id, $os->id));
        $this->assertTrue($resolver->bolehAksesOs($all[2]->id, $this->activity->id, $os->id));
        $this->assertSame(2, TetapanAksesPtj::whereNotNull('ptj_id')->count());
    }

    public function test_ptj_reset_restores_per_setting_inheritance_without_changing_another_ptj(): void
    {
        $os = $this->state('kategori', 'ppd')['rows']->first()['os'];
        $input = $this->packet('kategori', 'ppd');
        $input['settings'][$os->id] = ['configured' => 1, 'allowed' => 1];
        $this->service()->simpan('kategori', 'ppd', $this->activity->id, $input);
        $key = (string) $this->ppd->id;
        $input = $this->packet('ptj', $key);
        $input['settings'][$os->id] = ['configured' => 1, 'allowed' => 0];
        $input['ptj_id'] = Ptj::where('kategori', 'PPD')->whereKeyNot($this->ppd->id)->firstOrFail()->id;
        $this->post($this->url('ptj', $key), $input)->assertRedirect();
        $resolver = app(AksesPeruntukan::class);
        $this->assertFalse($resolver->bolehAksesOs($this->ppd->id, $this->activity->id, $os->id));
        $this->assertTrue($resolver->bolehAksesOs($input['ptj_id'], $this->activity->id, $os->id));
        $input = $this->packet('ptj', $key);
        $input['settings'][$os->id]['configured'] = 0;
        $this->post($this->url('ptj', $key), $input)->assertRedirect();
        $this->assertTrue($resolver->bolehAksesOs($this->ppd->id, $this->activity->id, $os->id));
        $this->assertSame(0, TetapanAksesPtj::whereNotNull('ptj_id')->count());
    }

    public function test_parent_disable_masks_but_preserves_os_configuration_and_unchanged_row_identity(): void
    {
        $os = $this->state('kategori', 'ppd')['rows']->first()['os'];
        $input = $this->packet('kategori', 'ppd');
        $input['settings'][$os->id] = ['configured' => 1, 'allowed' => 1];
        $this->service()->simpan('kategori', 'ppd', $this->activity->id, $input);
        $id = TetapanAksesPtj::where('kategori_ptj', 'PPD')->where('rujukan_os_id', $os->id)->sole()->id;
        $input = $this->packet('kategori', 'ppd');
        $input['activity']['allowed'] = 0;
        $this->post($this->url('kategori', 'ppd'), $input)->assertRedirect();
        $resolver = app(AksesPeruntukan::class);
        $this->assertTrue($resolver->tetapanOs($this->ppd->id, $this->activity->id, $os->id));
        $this->assertFalse($resolver->bolehAksesOs($this->ppd->id, $this->activity->id, $os->id));
        $this->assertSame($id, TetapanAksesPtj::where('kategori_ptj', 'PPD')->where('rujukan_os_id', $os->id)->sole()->id);
        $input = $this->packet('kategori', 'ppd'); $input['activity']['allowed'] = 1;
        $this->post($this->url('kategori', 'ppd'), $input)->assertRedirect();
        $this->assertTrue($resolver->bolehAksesOs($this->ppd->id, $this->activity->id, $os->id));
    }

    public function test_false_and_unconfigured_os_are_distinct_and_can_be_reset_without_new_defaults(): void
    {
        $os = $this->state('kategori', 'ppd')['rows']->first()['os'];
        $input = $this->packet('kategori', 'ppd');
        $input['settings'][$os->id] = ['configured' => 1, 'allowed' => 0];
        $this->post($this->url('kategori', 'ppd'), $input)->assertRedirect();
        $this->assertFalse(app(AksesPeruntukan::class)->tetapanOs($this->ppd->id, $this->activity->id, $os->id));
        $input = $this->packet('kategori', 'ppd'); $input['settings'][$os->id]['configured'] = 0;
        $this->post($this->url('kategori', 'ppd'), $input)->assertRedirect();
        $this->assertNull(app(AksesPeruntukan::class)->tetapanOs($this->ppd->id, $this->activity->id, $os->id));
    }

    public function test_same_code_os_variants_are_separate_permissions(): void
    {
        $rows = $this->state('kategori', 'ppd')['rows'];
        $group = $rows->groupBy(fn ($row) => $row['os']->kod_objek)->first(fn ($group) => $group->count() > 1)->values();
        $a = $group[0]['os']; $b = $group[1]['os'];
        $input = $this->packet('kategori', 'ppd');
        $input['settings'][$a->id] = ['configured' => 1, 'allowed' => 1];
        $input['settings'][$b->id] = ['configured' => 1, 'allowed' => 0];
        $this->post($this->url('kategori', 'ppd'), $input)->assertRedirect();
        $resolver = app(AksesPeruntukan::class);
        $this->assertTrue($resolver->bolehAksesOs($this->ppd->id, $this->activity->id, $a->id));
        $this->assertFalse($resolver->bolehAksesOs($this->ppd->id, $this->activity->id, $b->id));
    }

    public function test_incomplete_excluded_unmapped_and_unknown_os_payloads_are_rejected_without_writes(): void
    {
        $excluded = RujukanOs::where('dikecualikan', true)->firstOrFail();
        $unmapped = RujukanOs::create(['kod_objek' => '99999', 'butiran_perbelanjaan' => 'Unmapped test reference', 'dikecualikan' => false]);
        $base = $this->packet('kategori', 'ppd');
        $missing = $base; array_pop($missing['settings']);
        $variants = [$missing];
        foreach ([$excluded->id, $unmapped->id, 999999] as $id) {
            $input = $base; $input['settings'][$id] = ['configured' => 1, 'allowed' => 1]; $variants[] = $input;
        }
        foreach ($variants as $input) { $this->post($this->url('kategori', 'ppd'), $input)->assertSessionHasErrors('settings'); }
        $this->assertSame(15, TetapanAksesPtj::count());
        $this->assertSame(0, RekodAudit::count());
    }

    public function test_stale_editor_conflicts_after_target_or_inherited_category_changes(): void
    {
        $stale = $this->packet('ptj', (string) $this->ppd->id);
        $input = $this->packet('kategori', 'ppd'); $input['activity']['allowed'] = 0;
        $this->post($this->url('kategori', 'ppd'), $input)->assertRedirect();
        $this->post($this->url('ptj', (string) $this->ppd->id), $stale)->assertConflict();
        $this->post($this->url('kategori', 'ppd'), $input)->assertConflict();
        $this->assertSame(1, RekodAudit::count());
        $this->assertSame(0, TetapanAksesPtj::whereNotNull('ptj_id')->count());
    }

    public function test_catalogue_change_also_conflicts_with_a_preloaded_editor(): void
    {
        $input = $this->packet('kategori', 'ppd');
        $os = $this->state('kategori', 'ppd')['rows']->first()['os'];
        $os->update(['dikecualikan' => true]);
        $this->post($this->url('kategori', 'ppd'), $input)->assertConflict();
        $this->assertSame(0, RekodAudit::count());
    }

    public function test_all_non_admin_roles_are_denied_both_dimensions_and_mutation_endpoints(): void
    {
        foreach (array_filter(Peranan::cases(), fn ($role) => $role !== Peranan::Superadmin) as $role) {
            $this->admin->penugasanPeranan()->delete();
            $tuple = ['peranan' => $role];
            if (in_array($role, [Peranan::KetuaBidaan, Peranan::PenyeliaBidaan, Peranan::PenyediaBidaan, Peranan::PenyediaAnggaran])) { $tuple['aktiviti_kewangan_id'] = $this->activity->id; }
            if (in_array($role, [Peranan::PenyediaBidaan, Peranan::PenyediaAnggaran])) { $tuple['ptj_id'] = $this->admin->ptj_id; }
            $this->admin->penugasanPeranan()->create($tuple);
            $this->get('/pentadbiran/aktiviti-kewangan')->assertForbidden();
            $this->get('/pentadbiran/aktiviti-kewangan/kategori/ppd')->assertForbidden();
            $this->get('/pentadbiran/aktiviti-kewangan/ptj/'.$this->ppd->id)->assertForbidden();
            $this->post($this->url('kategori', 'ppd'), [])->assertForbidden();
            $this->post($this->url('ptj', (string) $this->ppd->id), [])->assertForbidden();
        }
        $this->assertSame(0, RekodAudit::count());
    }

    public function test_service_rechecks_actor_role_and_session_revision_inside_mutation(): void
    {
        $packet = $this->packet('kategori', 'ppd');
        $this->admin->increment('access_revision');
        try {
            $this->service()->simpan('kategori', 'ppd', $this->activity->id, $packet);
            $this->fail('Revoked session was allowed.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        $this->withSession(['abm_access_revision' => $this->admin->fresh()->access_revision]);
        $this->admin->penugasanPeranan()->delete();
        try {
            $this->service()->simpan('kategori', 'ppd', $this->activity->id, $packet);
            $this->fail('Revoked role was allowed.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        $this->assertSame(15, TetapanAksesPtj::count());
        $this->assertSame(0, RekodAudit::count());
    }

    public function test_audit_failure_rolls_back_activity_and_os_changes_together(): void
    {
        $input = $this->packet('kategori', 'ppd');
        $id = array_key_first($input['settings']);
        $input['activity']['allowed'] = 0; $input['settings'][$id] = ['configured' => 1, 'allowed' => 1];
        $this->mock(JejakAudit::class, fn ($mock) => $mock->shouldReceive('jalankan')->once()->andReturnUsing(function ($role, $operation) {
            $operation(); throw new RuntimeException('Audit unavailable.');
        }));
        try {
            $this->service()->simpan('kategori', 'ppd', $this->activity->id, $input);
            $this->fail('Audit failure was ignored.');
        } catch (RuntimeException $exception) { $this->assertSame('Audit unavailable.', $exception->getMessage()); }
        $resolver = app(AksesPeruntukan::class);
        $this->assertTrue($resolver->bolehAksesAktiviti($this->ppd->id, $this->activity->id));
        $this->assertNull($resolver->tetapanOs($this->ppd->id, $this->activity->id, $id));
        $this->assertSame(15, TetapanAksesPtj::count());
        $this->assertSame(0, RekodAudit::count());
    }

    public function test_missing_reason_or_invalid_boolean_does_not_change_permissions(): void
    {
        $input = $this->packet('kategori', 'ppd'); $input['reason'] = '';
        $this->post($this->url('kategori', 'ppd'), $input)->assertSessionHasErrors('reason');
        $input = $this->packet('kategori', 'ppd'); $input['activity']['allowed'] = 'anything';
        $this->post($this->url('kategori', 'ppd'), $input)->assertSessionHasErrors('activity.allowed');
        $this->assertSame(0, RekodAudit::count());
    }

    public function test_view_counts_match_the_read_resolver_for_category_and_ptj_state(): void
    {
        foreach (Ptj::whereIn('kategori', ['JPNT', 'PPD', 'SMK PTJ'])->get()->unique('kategori') as $ptj) {
            foreach (AktivitiKewangan::all() as $activity) {
                $state = $this->state('ptj', (string) $ptj->id, $activity);
                $resolver = app(AksesPeruntukan::class);
                $this->assertSame($resolver->bolehAksesAktiviti($ptj->id, $activity->id), $state['parent']['effective'] === true);
                foreach ($state['rows'] as $row) {
                    $this->assertSame($resolver->bolehAksesOs($ptj->id, $activity->id, $row['os']->id),
                        $state['parent']['effective'] === true && $row['effective'] === true);
                }
            }
        }
    }

    public function test_search_pagination_targets_exclusions_and_fixture_pages(): void
    {
        $list = $this->get('/pentadbiran/aktiviti-kewangan')->assertOk()->assertSee('69 PTJ');
        $this->get('/pentadbiran/aktiviti-kewangan?page=2')->assertOk()->assertSee('Halaman 2 daripada 3');
        $this->get('/pentadbiran/aktiviti-kewangan?q=41620101')->assertOk()->assertSee('1 PTJ');
        $category = $this->get('/pentadbiran/aktiviti-kewangan/kategori/ppd')->assertOk()->assertSee('Lalai Kategori');
        $ptj = $this->get('/pentadbiran/aktiviti-kewangan/ptj/'.$this->ppd->id)->assertOk()->assertSee('Tetapan khusus');
        foreach (RujukanOs::where('dikecualikan', true)->get() as $excluded) { $category->assertDontSee($excluded->butiran_perbelanjaan); }
        $this->get('/pentadbiran/aktiviti-kewangan/kategori/unknown')->assertNotFound();
        $this->get('/pentadbiran/aktiviti-kewangan/ptj/999999')->assertNotFound();
        if (getenv('ABM_UI_CAPTURE') === '1') {
            $directory = storage_path('app/ui-qa'); if (!is_dir($directory)) { mkdir($directory, 0775, true); }
            foreach (['permissions' => $list, 'permissions-category' => $category, 'permissions-ptj' => $ptj] as $name => $response) {
                file_put_contents($directory.'/'.$name.'.html', str_replace('http://localhost', 'http://127.0.0.1:4173', $response->getContent()));
            }
            foreach (['abm.css', 'abm.js', 'permissions.js'] as $asset) { copy(public_path($asset), $directory.'/'.$asset); }
        }
    }
}
