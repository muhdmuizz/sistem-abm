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

class SbkSpreadsheetTest extends TestCase
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
        $this->activity = AktivitiKewangan::create(['nama' => 'Pendidikan Rendah', 'singkatan' => 'Pendidikan Rendah']);
        $this->cycle = Kitaran::create(['tahun' => 2028]);
        $this->phase = Fasa::forceCreate(['kitaran_id' => $this->cycle->id, 'tahun_kitaran' => 2028,
            'jenis' => 'bidaan_awal', 'tarikh_mula' => '2028-05-01', 'tarikh_tamat' => '2028-05-31',
            'jenis_skop' => 'global', 'dibuka' => true, 'pernah_dibuka' => true]);
        $this->phase->skop()->create(['jenis_skop' => 'global']);
        $this->os = RujukanOs::create(['kod_objek' => '42000', 'butiran_perbelanjaan' => 'Bayaran Bil Utiliti Sekolah Bantuan Kerajaan (SBK)']);
        $this->unconfigured = RujukanOs::create(['kod_objek' => '42000', 'butiran_perbelanjaan' => 'Subsidi Yuran TASKA']);
        $code=\App\Models\KodAktiviti::create(['aktiviti_kewangan_id'=>$this->activity->id,'kod_aktiviti'=>'030228']);
        foreach ([$this->os, $this->unconfigured] as $os) {
            PemetaanOs::create(['aktiviti_kewangan_id' => $this->activity->id, 'rujukan_os_id' => $os->id,
                'kod_aktiviti_id'=>$code->id, 'singkatan_sumber' => 'Pendidikan Rendah', 'butiran_asal' => $os->butiran_perbelanjaan, 'status_sumber' => 'Aktif']);
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
    private function row($place,array $extra=[]): array {return array_replace(['tempat_bertugas_id'=>$place->id,'kategori2'=>'Air','kadar'=>'123.4567'],$extra);}
    private function gridCapture(string $name,$response): void
    {
        $this->workspaceCapture($name,$response);
        if(getenv('ABM_UI_CAPTURE')==='1')foreach(['abm-money.js','anggaran-grid.css','anggaran-grid.js'] as $asset)copy(public_path($asset),storage_path('app/ui-qa/'.$asset));
    }
    private function secondaryContext(): array
    {
        $activity=AktivitiKewangan::create(['nama'=>'Pendidikan Menengah','singkatan'=>'Pendidikan Menengah']);
        $code=\App\Models\KodAktiviti::create(['aktiviti_kewangan_id'=>$activity->id,'kod_aktiviti'=>'030310']);
        PemetaanOs::create(['aktiviti_kewangan_id'=>$activity->id,'kod_aktiviti_id'=>$code->id,'rujukan_os_id'=>$this->os->id,
            'singkatan_sumber'=>'Pendidikan Menengah','butiran_asal'=>'Bayaran Bit Utiliti Sekolah Bantuan Kerajaan (SBK)','status_sumber'=>'Aktif']);
        foreach([null,$this->os->id] as $osId)TetapanAksesPtj::create(['kategori_ptj'=>'JPNT','aktiviti_kewangan_id'=>$activity->id,'rujukan_os_id'=>$osId,'dibenarkan'=>true]);
        $this->operator->penugasanPeranan()->create(['peranan'=>Peranan::PenyediaAnggaran,'ptj_id'=>$this->owner->id,'aktiviti_kewangan_id'=>$activity->id]);
        $bid=Bidaan::forceCreate(['kitaran_id'=>$this->cycle->id,'ptj_id'=>$this->owner->id,'aktiviti_kewangan_id'=>$activity->id,'diaktifkan'=>true,'semakan'=>1]);
        $estimate=Anggaran::forceCreate(['bidaan_id'=>$bid->id,'ptj_id'=>$bid->ptj_id,'rujukan_os_id'=>$this->os->id,'diaktifkan'=>true,'semakan'=>1]);
        return [$bid,$estimate];
    }

    public function test_empty_sbk_grid_has_only_four_shared_utilities_and_manual_rate_with_fixed_outputs(): void
    {
        [$bid,$estimate]=$this->gridContext();
        $response=$this->get($this->gridUrl($bid,$estimate))->assertOk()->assertSee('KADAR Bulanan')->assertSee('diisi sendiri')->assertDontSee('UNIT dan KADAR diisi daripada rujukan');
        $this->assertSame(['Air','Elektrik','Telefon/ Faks','Pembetungan'],array_keys($response->viewData('references')));
        $columns=collect($response->viewData('columns'))->keyBy('key');
        $this->assertSame('decimal',$columns['kadar']['type']);$this->assertSame('Pukal',$columns['kategori1']['value']);
        $this->assertSame('12',$columns['bil_bulan']['value']);
        $this->assertSame(['tempat_bertugas_id','kategori2','kadar'],$columns->where('type','!=','output')->keys()->all());
        $this->gridCapture('sbk-empty',$response);$this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('rekod_audit',0);
        $this->get(route('estimates.show',[$bid->id,$estimate->id]))->assertOk()->assertSee('Buka Jadual Butiran');
    }

    public function test_all_four_utilities_save_reopen_edit_and_preview_exact_monthly_rate_times_twelve(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        $rows=array_map(fn($utility)=>$this->row($place,['kategori2'=>$utility]),['Air','Elektrik','Telefon/ Faks','Pembetungan']);
        $this->postJson($url,$this->payload($bid,$estimate,$rows))->assertOk();
        $sources=$estimate->butiran()->orderBy('id')->get();$this->assertCount(4,$sources);
        foreach($sources as $source){
            $this->assertSame('os42000.sbk.v1',$source->data_sumber->definition);$this->assertSame('123.4567',$source->data_sumber->kadar);
            $this->assertEqualsCanonicalizing(['definition','kategori2','kadar'],array_keys(get_object_vars($source->data_sumber)));
            $this->assertSame('1481.4804',app(\App\Services\DefinisiUtilitiSbk::class)->preview($source->data_sumber));
        }
        $response=$this->get($url)->assertOk();$this->assertSame($place->id,$response->viewData('records')->first()['tempat_bertugas_id']);
        $this->gridCapture('sbk-populated',$response);$this->assertDatabaseCount('hasil_pengiraan',0);
        $first=$sources->first();$row=$this->row($place,['butiran_id'=>$first->id,'semakan_butiran'=>$first->semakan,'kadar'=>'0.001']);
        $this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();
        $this->assertSame('0.001',$first->fresh()->data_sumber->kadar);$this->assertSame($first->id,$first->fresh()->id);
        $row['semakan_butiran']=$first->fresh()->semakan;
        $this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$this->assertDatabaseCount('rekod_audit',5);
        $this->assertSame('108086391056891916',app(\App\Services\DefinisiUtilitiSbk::class)->preview((object)['kategori2'=>'Air','kadar'=>'9007199254740993']));
    }

    public function test_shared_template_preserves_distinct_activity_mappings_and_cannot_move_source_between_bids(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();[$secondary,$secondEstimate]=$this->secondaryContext();
        $this->postJson($this->gridUrl($bid,$estimate),$this->payload($bid,$estimate,[$this->row($place)]))->assertOk();
        $source=$estimate->butiran()->sole();
        $this->postJson($this->gridUrl($secondary,$secondEstimate),$this->payload($secondary,$secondEstimate,
            [$this->row($place,['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan])]))->assertNotFound();
        $this->postJson($this->gridUrl($secondary,$secondEstimate),$this->payload($secondary,$secondEstimate,[$this->row($place,['kategori2'=>'Elektrik'])]))->assertOk();
        $response=$this->get($this->gridUrl($secondary,$secondEstimate))->assertOk()->assertSee('Pendidikan Menengah');$this->gridCapture('sbk-menengah',$response);
        $this->assertNotSame($source->id,$secondEstimate->butiran()->sole()->id);
        $this->assertSame(['030228','030310'],PemetaanOs::where('rujukan_os_id',$this->os->id)->with('kodAktiviti')->get()->pluck('kodAktiviti.kod_aktiviti')->sort()->values()->all());
        $this->assertDatabaseHas('pemetaan_os',['aktiviti_kewangan_id'=>$secondary->aktiviti_kewangan_id,'butiran_asal'=>'Bayaran Bit Utiliti Sekolah Bantuan Kerajaan (SBK)']);
        $this->get($this->gridUrl($bid,$secondEstimate))->assertNotFound();
    }

    public function test_wrong_or_removed_activity_mapping_and_other_os42000_variants_are_not_enabled(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        $code=\App\Models\KodAktiviti::where('aktiviti_kewangan_id',$this->activity->id)->sole();
        $code->update(['kod_aktiviti'=>'030310']);$this->get($url)->assertNotFound();
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertNotFound();
        $code->update(['kod_aktiviti'=>'030228']);$this->get($url)->assertOk();
        PemetaanOs::where('rujukan_os_id',$this->os->id)->delete();$this->get($url)->assertNotFound();
        $registry=app(\App\Services\KatalogDefinisiAnggaran::class);
        $this->assertNull($registry->untuk($this->unconfigured,$bid));
        $excluded=RujukanOs::create(['kod_objek'=>'42000','butiran_perbelanjaan'=>'Asrama Malaysia (AMal)','dikecualikan'=>true]);
        $this->assertNull($registry->untuk($excluded,$bid));$this->assertDatabaseCount('butiran_anggaran',0);
    }

    public function test_fixed_fields_derived_fields_non_pukal_values_and_invalid_rate_formats_are_rejected_atomically(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        foreach([['kategori1'=>'Pukal'],['bil_bulan'=>'1'],['preview'=>'0'],['bil_unit'=>'1'],['definition'=>'os25000.v1'],
            ['kategori2'=>'Pos/ Mesin Franking'],['kategori2'=>'Lain-lain'],['kadar'=>'1e4'],['kadar'=>1.5],['kadar'=>'1,00']] as $change){
            $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place),$this->row($place,$change)]))->assertUnprocessable();
            $this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('rekod_audit',0);
        }
    }

    public function test_partial_drafts_do_not_fabricate_monthly_rate_or_claim_official_outputs(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['kategori2'=>null,'kadar'=>null])]))->assertOk();$this->assertDatabaseCount('butiran_anggaran',0);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['kadar'=>null])]))->assertOk();
        $source=$estimate->butiran()->sole();$this->assertNull($source->data_sumber->kadar);
        $this->assertNull(app(\App\Services\DefinisiUtilitiSbk::class)->preview($source->data_sumber));
        $this->assertDatabaseCount('hasil_pengiraan',0);
        $response=$this->get($url)->assertOk();$this->gridCapture('sbk-partial',$response);
    }

    public function test_other_definition_and_legacy_payloads_remain_private_and_cannot_be_overwritten_or_deleted(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        $source=\App\Models\ButiranAnggaran::forceCreate(['anggaran_id'=>$estimate->id,'ptj_id'=>$bid->ptj_id,'tempat_bertugas_id'=>$place->id,
            'semakan'=>0,'data_sumber'=>(object)['definition'=>'os25000.v1','kategori1'=>'Air Minuman','bil_unit'=>'2','SECRET_LEGACY'=>'private']]);
        $response=$this->get($url)->assertOk()->assertDontSee('SECRET_LEGACY')->assertDontSee('Air Minuman');$this->assertFalse($response->viewData('records')->first()['compatible']);
        $this->gridCapture('sbk-legacy',$response);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan])]))->assertConflict();
        $this->postJson($url,$this->payload($bid,$estimate,[],[['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan]]))->assertConflict();
        $this->assertSame('private',$source->fresh()->data_sumber->SECRET_LEGACY);$this->assertDatabaseCount('rekod_audit',0);
    }

    public function test_sbk_live_guards_stale_state_and_read_only_roles_use_existing_boundaries(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$payload=$this->payload($bid,$estimate,[$this->row($place)]);
        $this->postJson($url,$payload)->assertOk();$this->postJson($url,$payload)->assertConflict();
        $this->phase->forceFill(['dibuka'=>false])->save();$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertForbidden();
        $this->phase->forceFill(['dibuka'=>true])->save();
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PemantauBidaan);
        $response=$this->get($url)->assertOk()->assertDontSee('<button type="submit" class="button primary">',false);$this->gridCapture('sbk-readonly',$response);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertForbidden();
        $this->assertDatabaseCount('butiran_anggaran',1);$this->assertDatabaseCount('rekod_audit',1);
    }
    public function test_grouped_monetary_paste_round_trips_exact_source_through_http_save_and_reopen(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['kadar'=>'4,320.001'])]))->assertOk();
        $source=$estimate->butiran()->sole();$this->assertSame('4320.001',$source->data_sumber->kadar);
        $response=$this->get($url)->assertOk();$this->assertSame('4320.001',$response->viewData('records')->first()['kadar']);
        $this->assertSame('51840.012',app(\App\Services\DefinisiUtilitiSbk::class)->preview($source->data_sumber));
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['kadar'=>'4,32.00'])]))->assertUnprocessable();
        $this->assertDatabaseCount('butiran_anggaran',1);
        $source->forceFill(['data_sumber'=>(object)['definition'=>'os42000.sbk.v1','kategori2'=>'Air','kadar'=>'4,320.001']])->save();
        $response=$this->get($url)->assertOk();$this->assertFalse($response->viewData('records')->first()['compatible']);
    }
}
