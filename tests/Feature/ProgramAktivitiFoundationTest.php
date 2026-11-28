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

class ProgramAktivitiFoundationTest extends TestCase
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
        $this->ptj = Ptj::create(['kod_ptj' => 'PROG-001', 'nama' => 'Programme fixture PTJ', 'kategori' => 'PPD']);
        $this->activity = AktivitiKewangan::create(['nama' => 'Programme fixture activity']);
        $this->place = TempatBertugas::create(['ptj_id' => $this->ptj->id, 'nama' => 'Programme fixture sector', 'jenis' => 'sektor']);
        $this->cycle = Kitaran::create(['tahun' => 2028]);
        $this->phase = Fasa::forceCreate(['kitaran_id' => $this->cycle->id, 'tahun_kitaran' => 2028,
            'jenis' => 'bidaan_awal', 'tarikh_mula' => '2026-11-01', 'tarikh_tamat' => '2026-12-31',
            'jenis_skop' => 'global', 'dibuka' => true, 'pernah_dibuka' => true]);
        $this->phase->skop()->create(['jenis_skop' => 'global']);
        TetapanAksesPtj::create(['kategori_ptj' => 'PPD', 'aktiviti_kewangan_id' => $this->activity->id,
            'rujukan_os_id' => null, 'dibenarkan' => true]);
        $this->actor = User::factory()->create();
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

    public function test_approved_rpm_hierarchy_is_exact_and_idempotent_with_local_parent_numbering(): void
    {
        $before = DB::table('prakarsa_rpm')->pluck('id')->all();
        $this->seed(RpmReferenceSeeder::class);
        $this->assertDatabaseCount('teras_strategik', 7);
        $this->assertDatabaseCount('strategi_rpm', 25);
        $this->assertDatabaseCount('prakarsa_rpm', 44);
        $this->assertSame($before, DB::table('prakarsa_rpm')->pluck('id')->all());
        $this->assertSame(7, StrategiRpm::where('nombor', 1)->count());
        $this->assertSame('Meningkatkan Kecekapan Tadbir Urus dan Sistem Penyampaian', TerasStrategik::where('nombor', 7)->sole()->tajuk);
        $expected = [];
        $t = $s = null;
        foreach (explode("\n", file_get_contents(base_path('docs/references/REF-06-teras-strategik-strategi-prakarsa.md'))) as $line) {
            if (preg_match('/^TERAS STRATEGIK (\\d+): (.+)$/', $line, $m)) { $t = (int) $m[1]; $expected['T'.$t] = $m[2]; }
            elseif (preg_match('/^- Strategi (\\d+): (.+)$/', $line, $m)) { $s = (int) $m[1]; $expected['T'.$t.'S'.$s] = $m[2]; }
            elseif (preg_match('/^  \\* Prakarsa (\\d+): (.+)$/', $line, $m)) { $expected['T'.$t.'S'.$s.'P'.$m[1]] = $m[2]; }
        }
        $actual = [];
        foreach (TerasStrategik::orderBy('nombor')->with(['strategi' => fn ($q) => $q->orderBy('nombor'),
            'strategi.prakarsa' => fn ($q) => $q->orderBy('nombor')])->get() as $teras) {
            $actual['T'.$teras->nombor] = $teras->tajuk;
            foreach ($teras->strategi as $strategy) {
                $actual['T'.$teras->nombor.'S'.$strategy->nombor] = $strategy->tajuk;
                foreach ($strategy->prakarsa as $initiative) {
                    $actual['T'.$teras->nombor.'S'.$strategy->nombor.'P'.$initiative->nombor] = $initiative->tajuk;
                }
            }
        }
        $this->assertSame($expected, $actual);
    }

    public function test_programme_checklist_and_scoped_financial_source_link(): void
    {
        $os = \App\Models\RujukanOs::create(['kod_objek'=>'25000','butiran_perbelanjaan'=>'Bahan-bahan Makanan dan Minuman']);
        $code = \App\Models\KodAktiviti::create(['aktiviti_kewangan_id'=>$this->activity->id,'kod_aktiviti'=>'010801']);
        \App\Models\PemetaanOs::create(['aktiviti_kewangan_id'=>$this->activity->id,'rujukan_os_id'=>$os->id,
            'kod_aktiviti_id'=>$code->id,'singkatan_sumber'=>'PPN','butiran_asal'=>$os->butiran_perbelanjaan,'status_sumber'=>'Aktif']);
        TetapanAksesPtj::create(['kategori_ptj'=>'PPD','aktiviti_kewangan_id'=>$this->activity->id,'rujukan_os_id'=>$os->id,'dibenarkan'=>true]);
        $program = $this->programSave(null,null,['os_terpilih'=>[$os->id]]);
        $this->assertSame([$os->id],$program->os_terpilih);
        $this->assertDatabaseCount('butiran_anggaran',0);
        $estimate = app(\App\Services\PengaktifanKewangan::class)->aktifkanOs($this->bid->id,$os->id,$this->bid->fresh()->semakan);
        $drafts = app(\App\Services\DrafButiranAnggaran::class);
        $source = $drafts->simpan($estimate->id,null,['tempat_bertugas_id'=>$this->place->id,
            'program_aktiviti_id'=>$program->id,'data_sumber'=>(object)[],
            'semakan_bidaan'=>$this->bid->fresh()->semakan,'semakan_anggaran'=>$estimate->fresh()->semakan]);
        $this->assertSame($program->id,$source->program_aktiviti_id);
        $this->programDenied(fn () => $this->programSave(null,$program,['os_terpilih'=>[]]),409);
        $this->get(route('programs.edit',[$this->bid->id,$program->id]))->assertOk()->assertSee('E. Butiran Anggaran')->assertSee('Buka');
        $this->get(route('estimates.editor',[$this->bid->id,$estimate->id,'program'=>$program->id]))->assertOk()->assertSee('Kembali ke Program/ Aktiviti');
    }

    public function test_programme_stores_four_sections_and_exact_derived_headcounts_without_financial_records(): void
    {
        $program = $this->programSave();
        $this->assertSame($this->bid->id, $program->bidaan_id);
        $this->assertSame($this->place->id, $program->tempat_bertugas_id);
        $this->assertSame($this->actor->id, $program->created_by);
        $this->assertSame('2028-02-01', $program->tarikh_mula->toDateString());
        $this->assertSame('3', $program->jumlah('jemputan'));
        $this->assertSame('27', $program->jumlah('peserta'));
        $this->assertSame([], $program->kekuranganMaklumat());
        $this->assertArrayNotHasKey('status', $program->getAttributes());
        $this->assertArrayNotHasKey('jumlah_peserta', $program->getAttributes());
        $this->assertSame(['Pegawai','Guru','AKP','Murid Rendah','Murid Menengah','Lain-lain'],
            $program->bilangan()->where('kumpulan', 'peserta')->orderBy('turutan')->pluck('kategori')->all());
        $audit = RekodAudit::sole();
        $this->assertSame('program.draft_created', $audit->tindakan);
        $this->assertSame($this->actor->id, $audit->pelaku_id);
        $this->assertSame(Peranan::PenyediaAnggaran, $audit->peranan);
        $this->assertSame($this->place->id, $audit->tempat_bertugas_id);
        $this->assertNull($audit->versi_penghantaran);
        $this->assertSame(2, $this->bid->fresh()->semakan);
        $this->assertDatabaseCount('anggaran', 0);
        $this->assertDatabaseCount('butiran_anggaran', 0);
        $this->assertDatabaseCount('hasil_pengiraan', 0);
    }

    public function test_progressive_draft_keeps_strict_objective_defaults_and_reports_missing_metadata_only(): void
    {
        $program = $this->programSave($this->programObjective());
        $this->assertNull($program->nama);
        $this->assertContains('nama', $program->kekuranganMaklumat());
        $this->assertContains('jemputan', $program->kekuranganMaklumat());
        $this->assertContains('peserta', $program->kekuranganMaklumat());
        $this->assertSame('0', $program->jumlah('peserta'));
        foreach (array_keys($this->programObjective()) as $field) {
            $this->programDenied(fn () => $this->programSave(array_replace($this->programObjective(), [$field => null])), 422);
        }
        $this->assertDatabaseCount('program_aktiviti', 1);
        $this->assertDatabaseCount('rekod_audit', 1);
    }

    public function test_incompatible_objective_parent_selections_are_rejected_before_partial_storage(): void
    {
        $otherTeras = TerasStrategik::where('nombor', 2)->sole();
        $otherStrategy = $otherTeras->strategi()->firstOrFail();
        $otherInitiative = $this->teras->strategi()->whereKeyNot($this->strategy->id)->firstOrFail()->prakarsa()->firstOrFail();
        $this->programDenied(fn () => $this->programSave($this->programMetadata(['strategi_id' => $otherStrategy->id])), 422);
        $this->programDenied(fn () => $this->programSave($this->programMetadata(['prakarsa_id' => $otherInitiative->id])), 422);
        $this->assertDatabaseCount('program_aktiviti', 0);
        $this->assertDatabaseCount('bilangan_program', 0);
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public function test_edits_retain_programme_child_identities_and_noop_does_not_advance_revisions(): void
    {
        $program = $this->programSave();
        $ids = $program->bilangan()->orderBy('id')->pluck('id')->all();
        $same = $this->programSave($this->programMetadata(), $program);
        $this->assertSame(1, $same->semakan);
        $this->assertDatabaseCount('rekod_audit', 1);
        $edited = $this->programSave($this->programMetadata(['nama' => 'Nama Dipinda']), $program);
        $this->assertSame($program->id, $edited->id);
        $this->assertSame($ids, $edited->bilangan()->orderBy('id')->pluck('id')->all());
        $this->assertSame(2, $edited->semakan);
        $audit = RekodAudit::orderByDesc('id')->first();
        $this->assertSame('Mesyuarat Perancangan', $audit->sebelum['maklumat']['nama']);
        $this->assertSame('Nama Dipinda', $audit->selepas['maklumat']['nama']);
    }

    public function test_identical_programme_names_remain_distinct_and_stale_create_retry_conflicts(): void
    {
        $input = $this->programInput($this->programMetadata());
        $first = app(DrafProgramAktiviti::class)->simpan($this->bid->id, null, $input);
        $this->programDenied(fn () => app(DrafProgramAktiviti::class)->simpan($this->bid->id, null, $input), 409);
        $second = $this->programSave();
        $this->assertNotSame($first->id, $second->id);
        $this->assertDatabaseCount('program_aktiviti', 2);
        $this->assertDatabaseCount('rekod_audit', 2);
    }

    public function test_both_stale_revisions_protect_programme_and_children_from_overwrite(): void
    {
        $program = $this->programSave();
        foreach (['semakan_bidaan','semakan_program'] as $field) {
            $this->programDenied(fn () => $this->programSave($this->programMetadata(['nama' => 'Stale change']), $program, [$field => 0]), 409);
        }
        $this->assertSame('Mesyuarat Perancangan', $program->fresh()->nama);
        $this->assertSame('27', $program->jumlah('peserta'));
        $this->assertSame(1, $program->fresh()->semakan);
        $this->assertDatabaseCount('rekod_audit', 1);
    }

    public function test_dates_are_budget_year_bound_while_fasa_opens_in_2026_and_partial_dates_can_be_drafts(): void
    {
        $program = $this->programSave();
        $this->assertSame(2028, $program->tahun_kitaran);
        foreach ([
            ['tarikh_mula' => '2026-11-14'], ['tarikh_tamat' => '2029-01-01'],
            ['tarikh_mula' => '2028-02-03'], ['tarikh_mula' => '2028-02-30'],
        ] as $bad) { $this->programDenied(fn () => $this->programSave($this->programMetadata($bad)), 422); }
        $partial = $this->programSave($this->programObjective() + ['tarikh_mula' => '2028-02-29']);
        $this->assertSame('2028-02-29', $partial->tarikh_mula->toDateString());
        $this->assertContains('tarikh_tamat', $partial->kekuranganMaklumat());
    }

    #[DataProvider('invalidProgramHeadcounts')]
    public function test_invalid_headcounts_are_rejected_without_source_or_audit_mutation(string $group, array $row): void
    {
        $this->programDenied(fn () => $this->programSave($this->programMetadata([$group => [$row]])), 422);
        $this->assertDatabaseCount('program_aktiviti', 0);
        $this->assertDatabaseCount('bilangan_program', 0);
        $this->assertDatabaseCount('rekod_audit', 0);
        $this->assertSame(1, $this->bid->fresh()->semakan);
    }

    public static function invalidProgramHeadcounts(): array
    {
        return [
            ['jemputan',['kategori'=>'Guru','klasifikasi'=>9,'bilangan'=>1]],
            ['jemputan',['kategori'=>'Murid Rendah','klasifikasi'=>1,'bilangan'=>1]],
            ['jemputan',['kategori'=>'Lain-lain','catatan'=>'Fixture','bilangan'=>1]],
            ['peserta',['kategori'=>'Pegawai','klasifikasi'=>8,'bilangan'=>1]],
            ['peserta',['kategori'=>'Guru','klasifikasi'=>8,'bilangan'=>1]],
            ['peserta',['kategori'=>'AKP','klasifikasi'=>9,'bilangan'=>1]],
            ['peserta',['kategori'=>'AKP','klasifikasi'=>0,'bilangan'=>1]],
            ['peserta',['kategori'=>'Murid Rendah','klasifikasi'=>7,'bilangan'=>1]],
            ['peserta',['kategori'=>'Murid Menengah','klasifikasi'=>0,'bilangan'=>1]],
            ['peserta',['kategori'=>'Lain-lain','catatan'=>'  ','bilangan'=>1]],
            ['peserta',['kategori'=>'Lain-lain','catatan'=>'Fixture','klasifikasi'=>9,'bilangan'=>1]],
            ['peserta',['kategori'=>'Pegawai','klasifikasi'=>9,'catatan'=>'Unsupported note','bilangan'=>1]],
            ['peserta',['kategori'=>'Pegawai','bilangan'=>1]],
            ['peserta',['kategori'=>'Pegawai','klasifikasi'=>9,'bilangan'=>0]],
            ['peserta',['kategori'=>'Pegawai','klasifikasi'=>9,'bilangan'=>-1]],
            ['peserta',['kategori'=>'Pegawai','klasifikasi'=>9,'bilangan'=>1.5]],
            ['peserta',['kategori'=>'Pegawai','klasifikasi'=>9,'bilangan'=>'2.5']],
            ['peserta',['kategori'=>'Pegawai & Guru','klasifikasi'=>9,'bilangan'=>1]],
            ['peserta',['kategori'=>'Pegawai','klasifikasi'=>9,'bilangan'=>true]],
        ];
    }

    public function test_headcount_totals_remain_exact_above_php_integer_range(): void
    {
        $program = $this->programSave($this->programMetadata(['peserta' => [
            ['kategori'=>'Pegawai','klasifikasi'=>9,'bilangan'=>(string) PHP_INT_MAX],
            ['kategori'=>'Guru','klasifikasi'=>9,'bilangan'=>1],
        ]]));
        $this->assertSame('9223372036854775808', $program->jumlah('peserta'));
        $this->assertSame(2, $program->bilangan()->where('kumpulan','peserta')->count());
    }

    public function test_package_components_use_four_approved_labels_and_never_activate_os(): void
    {
        $program = $this->programSave($this->programMetadata(['kaedah'=>'Pakej','komponen'=>DrafProgramAktiviti::KOMPONEN]));
        $this->assertSame(4, $program->komponen()->count());
        foreach ([
            ['kaedah'=>'Pakej','komponen'=>[]],
            ['kaedah'=>'Pakej','komponen'=>['Dewan']],
            ['kaedah'=>'Pakej','komponen'=>['Penginapan','Penginapan']],
            ['kaedah'=>'Bukan Pakej','komponen'=>['Makan Minum']],
        ] as $bad) { $this->programDenied(fn () => $this->programSave($this->programMetadata($bad)), 422); }
        $this->assertDatabaseCount('anggaran', 0);
        $this->assertDatabaseCount('butiran_anggaran', 0);
        $this->assertDatabaseCount('rekod_audit', 1);
    }

    public function test_strict_envelope_blocks_ownership_totals_status_os_and_optionality_injection(): void
    {
        foreach ([
            ['ptj_id'=>$this->ptj->id], ['created_by'=>$this->actor->id], ['status'=>'lengkap'],
            ['semakan_program'=>0], ['jumlah_peserta'=>99],
            ['maklumat'=>$this->programMetadata()+['os_ids'=>[1]]],
            ['maklumat'=>$this->programMetadata()+['allow_blank_strategi'=>true]],
            ['maklumat'=>$this->programMetadata(['jenis'=>'Mesyuarat'])],
            ['maklumat'=>$this->programMetadata(['kategori'=>'Umum'])],
            ['maklumat'=>$this->programMetadata(['peserta'=>['named'=>['kategori'=>'Guru','klasifikasi'=>9,'bilangan'=>1]]])],
        ] as $bad) { $this->programDenied(fn () => $this->programSave(replace:$bad), 422); }
        $this->assertDatabaseCount('program_aktiviti', 0);
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public function test_own_workplace_scope_applies_to_reads_creation_and_both_sides_of_edits(): void
    {
        $own = $this->programSave();
        $other = TempatBertugas::create(['ptj_id'=>$this->ptj->id,'nama'=>'Private programme sector','jenis'=>'sektor']);
        $foreign = $this->programSave($this->programMetadata(['nama'=>'Private programme']), replace:['tempat_bertugas_id'=>$other->id]);
        $this->actor->penugasanPeranan()->delete(); $this->programAssign($this->place->id);
        $query = app(RuangProgramAktiviti::class)->query($this->actor,$this->bid);
        $this->assertSame([$own->id], $query->pluck('id')->all());
        $this->programDenied(fn () => $this->programSave($this->programMetadata(),$foreign),403);
        $this->programDenied(fn () => $this->programSave($this->programMetadata(),$own,['tempat_bertugas_id'=>$other->id]),403);
        $this->programDenied(fn () => $this->programSave(replace:['tempat_bertugas_id'=>$other->id]),403);
        $this->assertSame($other->id,$foreign->fresh()->tempat_bertugas_id);
        $this->assertDatabaseCount('program_aktiviti',2);
        $this->assertDatabaseCount('rekod_audit',2);
    }

    public function test_ptj_preparer_may_change_workplace_without_changing_programme_identity_or_creator(): void
    {
        $program=$this->programSave();
        $other=TempatBertugas::create(['ptj_id'=>$this->ptj->id,'nama'=>'Other programme sector','jenis'=>'sektor']);
        $edited=$this->programSave($this->programMetadata(),$program,['tempat_bertugas_id'=>$other->id]);
        $this->assertSame($program->id,$edited->id);
        $this->assertSame($this->actor->id,$edited->created_by);
        $this->assertSame($other->id,$edited->tempat_bertugas_id);
        $audit=RekodAudit::orderByDesc('id')->first();
        $this->assertSame($this->place->id,$audit->sebelum['tempat_bertugas_id']);
        $this->assertSame($other->id,$audit->selepas['tempat_bertugas_id']);
    }

    public function test_cross_ptj_workplace_and_nested_programme_parent_mismatch_are_denied(): void
    {
        $other=Ptj::create(['kod_ptj'=>'PROG-002','nama'=>'Other programme PTJ','kategori'=>'PPD']);
        $place=TempatBertugas::create(['ptj_id'=>$other->id,'nama'=>'Foreign programme workplace','jenis'=>'sektor']);
        $this->programDenied(fn () => $this->programSave(replace:['tempat_bertugas_id'=>$place->id]),403);
        $program=$this->programSave();
        $otherCycle=Kitaran::create(['tahun'=>2029]);
        $otherBid=Bidaan::forceCreate(['ptj_id'=>$this->ptj->id,'aktiviti_kewangan_id'=>$this->activity->id,
            'kitaran_id'=>$otherCycle->id,'diaktifkan'=>true]);
        $this->programDenied(fn () => app(DrafProgramAktiviti::class)->simpan($otherBid->id,$program->id,
            $this->programInput($this->programMetadata(),$program)),404);
        $this->assertDatabaseCount('program_aktiviti',1);
    }

    #[DataProvider('programNonWriterRoles')]
    public function test_read_or_review_roles_cannot_write_programme_drafts(Peranan $role): void
    {
        $this->actor->penugasanPeranan()->delete();
        $scope=['peranan'=>$role];
        if (in_array($role,[Peranan::PenyediaBidaan,Peranan::PenyeliaBidaan,Peranan::KetuaBidaan],true)) {
            $scope['aktiviti_kewangan_id']=$this->activity->id;
        }
        if ($role===Peranan::PenyediaBidaan) { $scope['ptj_id']=$this->ptj->id; }
        $this->actor->penugasanPeranan()->create($scope);
        $this->programDenied(fn () => $this->programSave(),403);
        if (in_array($role,[Peranan::PenyediaBidaan,Peranan::PemantauBidaan],true)) {
            $this->assertSame(0,app(RuangProgramAktiviti::class)->query($this->actor,$this->bid)->count());
        } else { $this->programDenied(fn () => app(RuangProgramAktiviti::class)->query($this->actor,$this->bid)->get(),403); }
        $this->assertDatabaseCount('rekod_audit',0);
    }

    public static function programNonWriterRoles(): array
    {
        return [[Peranan::PenyediaBidaan],[Peranan::PemantauBidaan],[Peranan::PenyeliaBidaan],[Peranan::KetuaBidaan]];
    }

    public function test_closed_phase_central_revocation_and_session_revision_are_rechecked_even_for_noops(): void
    {
        $program=$this->programSave();
        $this->phase->forceFill(['dibuka'=>false])->save();
        $this->programDenied(fn () => $this->programSave($this->programMetadata(),$program),403);
        $this->phase->forceFill(['dibuka'=>true])->save();
        TetapanAksesPtj::query()->update(['dibenarkan'=>false]);
        $this->programDenied(fn () => $this->programSave($this->programMetadata(),$program),403);
        $this->programDenied(fn () => app(RuangProgramAktiviti::class)->query($this->actor,$this->bid)->get(),403);
        TetapanAksesPtj::query()->update(['dibenarkan'=>true]);
        $this->actor->increment('access_revision');
        $this->programDenied(fn () => $this->programSave($this->programMetadata(),$program),403);
        $this->assertDatabaseCount('rekod_audit',1);
        $this->assertSame(1,$program->fresh()->semakan);
    }

    public function test_expired_session_inactive_bid_and_protected_statuses_block_drafts_but_returned_state_is_preserved(): void
    {
        $this->bid->forceFill(['diaktifkan'=>false])->save();
        $this->programDenied(fn () => $this->programSave(),409);
        $this->bid->forceFill(['diaktifkan'=>true])->save();
        foreach ([StatusBidaan::DalamSemakan,StatusBidaan::Muktamad] as $status) {
            $this->bid->forceFill(['status'=>$status])->save(); $this->programDenied(fn () => $this->programSave(),409);
        }
        $this->bid->forceFill(['status'=>StatusBidaan::Dikembalikan])->save();
        $program=$this->programSave();
        $this->assertSame(StatusBidaan::Dikembalikan,$this->bid->fresh()->status);
        session()->put('abm_last_activity',now()->timestamp-1800);
        $this->programDenied(fn () => $this->programSave($this->programMetadata(),$program),403);
        $this->assertDatabaseCount('rekod_audit',1);
    }

    public function test_superadmin_override_uses_actual_identity_and_reason_but_keeps_workflow_and_dates(): void
    {
        $this->actor->penugasanPeranan()->delete();
        $this->actor->penugasanPeranan()->create(['peranan'=>Peranan::Superadmin]);
        $this->phase->forceFill(['dibuka'=>false])->save(); TetapanAksesPtj::query()->update(['dibenarkan'=>false]);
        $this->programDenied(fn () => $this->programSave(),422);
        $program=$this->programSave(replace:['reason'=>'Pembukaan program pentadbiran yang diluluskan.']);
        $audit=RekodAudit::sole();
        $this->assertSame($this->actor->id,$program->created_by);
        $this->assertSame($this->actor->id,$audit->pelaku_id);
        $this->assertSame(Peranan::Superadmin,$audit->peranan);
        $this->assertTrue($audit->selepas['pintasan']['fasa']);
        $this->programDenied(fn () => $this->programSave($this->programMetadata(['tarikh_mula'=>'2026-11-14']),replace:['reason'=>'Pentadbiran.']),422);
        $this->bid->forceFill(['status'=>StatusBidaan::Muktamad])->save();
        $this->programDenied(fn () => $this->programSave($this->programMetadata(),$program,['reason'=>'Pentadbiran.']),409);
        $this->assertDatabaseCount('program_aktiviti',1);
    }

    public function test_actual_audit_insert_failure_rolls_back_programme_children_and_parent_revision_on_create(): void
    {
        $this->programAuditFailure();
        try { $this->programSave($this->programMetadata(['kaedah'=>'Pakej','komponen'=>['Penginapan']])); $this->fail('Audit failure ignored.'); }
        catch (QueryException $e) { $this->assertSame('P0001',(string)$e->getCode()); }
        $this->assertDatabaseCount('program_aktiviti',0);
        $this->assertDatabaseCount('komponen_program',0);
        $this->assertDatabaseCount('bilangan_program',0);
        $this->assertDatabaseCount('rekod_audit',0);
        $this->assertSame(1,$this->bid->fresh()->semakan);
    }

    public function test_actual_audit_failure_restores_existing_children_and_full_programme_metadata(): void
    {
        $program=$this->programSave($this->programMetadata(['kaedah'=>'Pakej','komponen'=>['Penginapan']]));
        $headcountIds=$program->bilangan()->orderBy('id')->pluck('id')->all();
        $this->programAuditFailure();
        try {
            $this->programSave($this->programMetadata(['nama'=>'Should roll back','jemputan'=>[['kategori'=>'Pegawai','klasifikasi'=>9,'bilangan'=>99]]]),$program);
            $this->fail('Audit failure ignored.');
        } catch (QueryException $e) { $this->assertSame('P0001',(string)$e->getCode()); }
        $this->assertSame('Mesyuarat Perancangan',$program->fresh()->nama);
        $this->assertSame('Pakej',$program->fresh()->kaedah);
        $this->assertSame(['Penginapan'],$program->komponen()->pluck('komponen')->all());
        $this->assertSame($headcountIds,$program->bilangan()->orderBy('id')->pluck('id')->all());
        $this->assertSame('3',$program->jumlah('jemputan'));
        $this->assertSame(1,$program->fresh()->semakan);
        $this->assertSame(2,$this->bid->fresh()->semakan);
        $this->assertDatabaseCount('rekod_audit',1);
    }

    #[DataProvider('programDatabaseViolations')]
    public function test_database_constraints_reject_programme_context_or_domain_corruption(string $mode): void
    {
        $program=$this->programSave();
        $otherTeras=TerasStrategik::where('nombor',2)->sole();
        $otherStrategy=$otherTeras->strategi()->firstOrFail();
        $otherPtj=Ptj::create(['kod_ptj'=>'PROG-003','nama'=>'Constraint other PTJ','kategori'=>'PPD']);
        $otherPlace=TempatBertugas::create(['ptj_id'=>$otherPtj->id,'nama'=>'Constraint other workplace','jenis'=>'sektor']);
        $changes=match($mode) {
            'date-year'=>['tarikh_mula'=>'2026-11-14'],
            'date-order'=>['tarikh_tamat'=>'2028-01-01'],
            'objective-parent'=>['strategi_id'=>$otherStrategy->id],
            'workplace-parent'=>['tempat_bertugas_id'=>$otherPlace->id],
            'cycle-year'=>['tahun_kitaran'=>2029],
        };
        try {
            DB::transaction(fn () => DB::table('program_aktiviti')->where('id',$program->id)->update($changes));
            $this->fail('Invalid database state accepted.');
        } catch (QueryException $e) { $this->assertContains((string)$e->getCode(),['23503','23514']); }
        $this->assertSame('2028-02-01',$program->fresh()->tarikh_mula->toDateString());
        $this->assertSame($this->place->id,$program->fresh()->tempat_bertugas_id);
    }

    public static function programDatabaseViolations(): array
    {
        return [['date-year'],['date-order'],['objective-parent'],['workplace-parent'],['cycle-year']];
    }

    public function test_database_rejects_headcount_category_classification_and_zero_quantities(): void
    {
        $program=$this->programSave();
        $row=$program->bilangan()->where('kumpulan','jemputan')->where('kategori','Pegawai')->firstOrFail();
        foreach ([['kategori'=>'Guru'],['klasifikasi'=>8],['bilangan'=>0]] as $bad) {
            try {
                DB::transaction(fn () => DB::table('bilangan_program')->where('id',$row->id)->update($bad));
                $this->fail('Invalid headcount accepted.');
            } catch (QueryException $e) { $this->assertSame('23514',(string)$e->getCode()); }
        }
        $this->assertSame('3',$program->jumlah('jemputan'));
    }
    public function test_zero_text_name_is_preserved_as_a_nonblank_programme_title(): void
    {
        $program=$this->programSave($this->programMetadata(['nama'=>'0']));
        $this->assertSame('0',$program->nama);
        $this->assertNotContains('nama',$program->kekuranganMaklumat());
    }

    public function test_foundation_migration_down_up_preserves_existing_bid_and_other_financial_tables(): void
    {
        $migration=require database_path('migrations/2026_11_14_050000_create_program_aktiviti_foundation.php');
        $financialLinks=require database_path('migrations/2026_11_28_000001_add_programme_financial_links.php');
        $financialLinks->down();
        $migration->down();
        $this->assertDatabaseCount('bidaan',1);
        $this->assertDatabaseCount('anggaran',0);
        $migration->up();
        $financialLinks->up();
        $this->seed(RpmReferenceSeeder::class);
        $this->assertDatabaseCount('teras_strategik',7);
        $this->assertDatabaseCount('strategi_rpm',25);
        $this->assertDatabaseCount('prakarsa_rpm',44);
        $this->assertDatabaseCount('bidaan',1);
        $this->assertDatabaseCount('program_aktiviti',0);
    }

    public function test_programme_metadata_rejects_non_scalar_values_and_boolean_reference_identities(): void
    {
        foreach ([
            ['jenis'=>(object)['fixture'=>'Umum']],
            ['tarikh_mula'=>['2028-02-01']],
            ['teras_strategik_id'=>true],
            ['peserta'=>[['kategori'=>(object)['fixture'=>'Pegawai'],'klasifikasi'=>9,'bilangan'=>1]]],
            ['peserta'=>[['kategori'=>'Pegawai','klasifikasi'=>(object)[],'bilangan'=>1]]],
            ['peserta'=>[['kategori'=>'Pegawai','klasifikasi'=>9,'bilangan'=>(object)[]]]],
        ] as $bad) { $this->programDenied(fn () => $this->programSave($this->programMetadata($bad)),422); }
        $this->assertDatabaseCount('program_aktiviti',0);
        $this->assertDatabaseCount('rekod_audit',0);
    }

}
