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

class FotostatLinkTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;
    private Ptj $owner;
    private Ptj $other;
    private AktivitiKewangan $activity;
    private Kitaran $cycle;
    private Fasa $phase;
    private RujukanOs $os;
    private RujukanOs $meter;
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
        $this->os = RujukanOs::create(['kod_objek' => '24000', 'butiran_perbelanjaan' => 'Sewaan Mesin Fotostat dan Riso']);
        $this->meter=RujukanOs::create(['kod_objek'=>'29000','butiran_perbelanjaan'=>'Bacaan Meter Mesin Fotostat']);
        $this->unconfigured = RujukanOs::create(['kod_objek' => '29000', 'butiran_perbelanjaan' => 'Pakej (Mesyuarat/ Bengkel/ Kursus)']);
        $code=\App\Models\KodAktiviti::create(['aktiviti_kewangan_id'=>$this->activity->id,'kod_aktiviti'=>'010807']);
        foreach ([$this->os, $this->meter,$this->unconfigured] as $os) {
            PemetaanOs::create(['aktiviti_kewangan_id' => $this->activity->id, 'rujukan_os_id' => $os->id,
                'kod_aktiviti_id'=>$code->id,'singkatan_sumber' => 'PPN', 'butiran_asal' => $os->butiran_perbelanjaan, 'status_sumber' => 'Aktif']);
        }
        foreach ([null, $this->os->id,$this->meter->id] as $osId) {
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
        app(\App\Services\PengaktifanKewangan::class)->aktifkanOs($bid->id,$this->os->id,$bid->fresh()->semakan);
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
    private function row($place,array $extra=[]): array {return array_replace(['tempat_bertugas_id'=>$place->id,'kategori1'=>'Berwarna','tarikh_mula'=>'2028-01-01','tarikh_tamat'=>'2028-12-31','tarikh_mula_kontrak'=>'2026-02-01','tarikh_tamat_kontrak'=>'2030-01-31','bil_unit'=>'2','lokasi'=>'Bilik Cetak','kadar'=>'180','bil_bulan'=>'12'],$extra);}
    private function gridCapture(string $name,$response): void
    {
        $this->workspaceCapture($name,$response);
        if(getenv('ABM_UI_CAPTURE')==='1')foreach(['abm-money.js','anggaran-grid.css','anggaran-grid.js','fotostat-review.js','fotostat-review.css'] as $asset)copy(public_path($asset),storage_path('app/ui-qa/'.$asset));
    }

    private function httpFailure(int $status,callable $fn): void {try{$fn();$this->fail('Expected HTTP failure');}catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame($status,$e->getStatusCode());}}
    private function meterEstimate(Bidaan $bid): Anggaran {return $bid->anggaran()->where('rujukan_os_id',$this->meter->id)->sole();}
    public function test_activation_is_atomic_pair_only_and_meter_permissions_are_not_bypassed(): void {
        $bid=$this->workspaceBid();$service=app(\App\Services\PengaktifanKewangan::class);
        $this->httpFailure(409,fn()=>$service->aktifkanOs($bid->id,$this->meter->id,$bid->semakan));$this->assertDatabaseCount('anggaran',0);
        TetapanAksesPtj::where('rujukan_os_id',$this->meter->id)->update(['dibenarkan'=>false]);
        $this->httpFailure(403,fn()=>$service->aktifkanOs($bid->id,$this->os->id,$bid->semakan));$this->assertDatabaseCount('anggaran',0);$this->assertDatabaseCount('rekod_audit',0);
        TetapanAksesPtj::where('rujukan_os_id',$this->meter->id)->update(['dibenarkan'=>true]);
        $r=$service->aktifkanOs($bid->id,$this->os->id,$bid->semakan);$this->assertTrue($r->diaktifkan);$this->assertTrue($this->meterEstimate($bid)->diaktifkan);
        $this->assertDatabaseCount('anggaran',2);$rev=$bid->fresh()->semakan;$audit=RekodAudit::count();$service->aktifkanOs($bid->id,$this->os->id,$rev);$this->assertSame($audit,RekodAudit::count());
        $this->httpFailure(409,fn()=>$service->aktifkanOs($bid->id,$this->meter->id,$rev));
        $this->gridCapture('fotostat-activation',$this->get(route('bids.show',$bid->id))->assertOk());
    }
    public function test_rental_save_creates_one_meter_and_updates_shared_fields_without_copying_financial_inputs(): void {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        $this->gridCapture('fotostat-empty',$this->get($url)->assertOk());
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertOk();
        $r=$estimate->butiran()->sole();$m=$this->meterEstimate($bid)->butiran()->sole();$this->assertDatabaseCount('pautan_fotostat',1);
        foreach(\App\Services\PautanFotostat::SHARED as $key)$this->assertSame($r->data_sumber->$key,$m->data_sumber->$key);
        foreach(\App\Services\PautanFotostat::FINANCIAL as $key)$this->assertNull($m->data_sumber->$key);
        $this->assertSame('4320',(new \App\Services\DefinisiSewaanFotostat(2028))->preview($r->data_sumber));
        $row=$this->row($place,['butiran_id'=>$r->id,'semakan_butiran'=>$r->semakan]);$count=RekodAudit::count();$this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$this->assertSame($count,RekodAudit::count());
        $meter=$this->meterEstimate($bid);$meterRow=$this->row($place,['butiran_id'=>$m->id,'semakan_butiran'=>$m->semakan,'bil_unit'=>'12000','kadar'=>'0.03','bil_bulan'=>'12']);
        $this->postJson($this->gridUrl($bid,$meter),$this->payload($bid,$meter,[$meterRow]))->assertOk();
        $row['semakan_butiran']=$r->fresh()->semakan;$row['lokasi']='Bilik Cetak Baharu';$row['tarikh_tamat_kontrak']='2032-01-31';$row['kadar']='999';
        $this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$m=$m->fresh();$this->assertSame('Bilik Cetak Baharu',$m->data_sumber->lokasi);$this->assertSame('2032-01-31',$m->data_sumber->tarikh_tamat_kontrak);
        $this->assertSame('12000',$m->data_sumber->bil_unit);$this->assertSame('0.03',$m->data_sumber->kadar);$this->assertSame('12',$m->data_sumber->bil_bulan);
        $response=$this->get($this->gridUrl($bid,$meter))->assertOk();$this->assertContains('kategori1',$response->viewData('records')[0]['readonly_fields']);$this->assertFalse($response->viewData('records')[0]['selectable']);
        $this->gridCapture('fotostat-linked-meter',$response);$this->gridCapture('fotostat-populated',$this->get($url)->assertOk());$this->assertDatabaseCount('butiran_anggaran',2);$this->assertDatabaseCount('hasil_pengiraan',0);
        $this->gridCapture('fotostat-links-empty',$this->get(route('fotostat.links',$bid->id))->assertOk());
        $this->gridCapture('fotostat-deactivate',$this->get(route('fotostat.deactivate-form',$bid->id))->assertOk());
    }
    public function test_linked_shared_field_overrides_and_direct_copy_delete_are_rejected(): void {
        [$bid,$estimate,$place]=$this->gridContext();$this->postJson($this->gridUrl($bid,$estimate),$this->payload($bid,$estimate,[$this->row($place)]))->assertOk();
        $m=$this->meterEstimate($bid)->butiran()->sole();$meter=$m->anggaran;$row=$this->row($place,['butiran_id'=>$m->id,'semakan_butiran'=>$m->semakan,'lokasi'=>'Override']);
        $this->postJson($this->gridUrl($bid,$meter),$this->payload($bid,$meter,[$row]))->assertUnprocessable();
        $this->postJson($this->gridUrl($bid,$meter),$this->payload($bid,$meter,[],[['butiran_id'=>$m->id,'semakan_butiran'=>$m->semakan]]))->assertConflict();
        $this->httpFailure(409,fn()=>app(\App\Services\DrafButiranAnggaran::class)->salin($meter->id,$m->id,['semakan_bidaan'=>$bid->fresh()->semakan,'semakan_anggaran'=>$meter->fresh()->semakan,'semakan_butiran'=>$m->semakan]));
        $this->assertDatabaseCount('butiran_anggaran',2);$this->assertDatabaseCount('pautan_fotostat',1);
    }
    public function test_rental_internal_copy_creates_an_independent_blank_meter_pair(): void {
        [$bid,$estimate,$place]=$this->gridContext();$this->postJson($this->gridUrl($bid,$estimate),$this->payload($bid,$estimate,[$this->row($place)]))->assertOk();$r=$estimate->butiran()->sole();
        $copy=app(\App\Services\DrafButiranAnggaran::class)->salin($estimate->id,$r->id,['semakan_bidaan'=>$bid->fresh()->semakan,'semakan_anggaran'=>$estimate->fresh()->semakan,'semakan_butiran'=>$r->semakan]);
        $this->assertNotSame($r->id,$copy->id);$this->assertDatabaseCount('butiran_anggaran',4);$this->assertDatabaseCount('pautan_fotostat',2);
        $this->assertNotSame(DB::table('pautan_fotostat')->where('sewaan_id',$r->id)->value('meter_id'),DB::table('pautan_fotostat')->where('sewaan_id',$copy->id)->value('meter_id'));
    }
    public function test_confirmed_pair_deletion_checks_meter_revision_and_keeps_unrelated_drafts(): void {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertOk();$r=$estimate->butiran()->sole();$m=$this->meterEstimate($bid)->butiran()->sole();
        $deletion=['butiran_id'=>$r->id,'semakan_butiran'=>$r->semakan];
        $this->postJson($url,$this->payload($bid,$estimate,[],[$deletion]))->assertConflict();$this->assertDatabaseCount('butiran_anggaran',2);
        $this->postJson($url,$this->payload($bid,$estimate,[],[$deletion+['sahkan_meter'=>true,'semakan_meter'=>999]]))->assertConflict();
        $this->postJson($url,$this->payload($bid,$estimate,[],[$deletion+['sahkan_meter'=>true,'semakan_meter'=>$m->semakan]]))->assertOk();
        $this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('pautan_fotostat',0);$this->assertSame('source.draft_deleted',RekodAudit::orderByDesc('id')->first()->tindakan);
    }
    public function test_deactivation_retains_both_drafts_and_reactivation_keeps_ids_and_links(): void {
        [$bid,$estimate,$place]=$this->gridContext();$this->postJson($this->gridUrl($bid,$estimate),$this->payload($bid,$estimate,[$this->row($place)]))->assertOk();$ids=\App\Models\ButiranAnggaran::pluck('id')->all();
        $this->post(route('fotostat.deactivate',$bid->id),['semakan_bidaan'=>$bid->fresh()->semakan,'sahkan'=>'1'])->assertRedirect();
        $this->assertSame(0,$bid->anggaran()->where('diaktifkan',true)->count());$this->assertSame($ids,\App\Models\ButiranAnggaran::pluck('id')->all());$this->assertDatabaseCount('pautan_fotostat',1);
        app(\App\Services\PengaktifanKewangan::class)->aktifkanOs($bid->id,$this->os->id,$bid->fresh()->semakan);$this->assertSame(2,$bid->anggaran()->where('diaktifkan',true)->count());$this->assertSame($ids,\App\Models\ButiranAnggaran::pluck('id')->all());
        $bid->forceFill(['status'=>StatusBidaan::Muktamad])->save();$this->httpFailure(409,fn()=>app(\App\Services\PengaktifanKewangan::class)->nyahaktifkanFotostat($bid->id,$bid->semakan));
    }
    public function test_explicit_link_replaces_only_blank_generated_meter_and_preserves_selected_financial_values(): void {
        [$bid,$estimate,$place]=$this->gridContext();$this->postJson($this->gridUrl($bid,$estimate),$this->payload($bid,$estimate,[$this->row($place)]))->assertOk();$r=$estimate->butiran()->sole();$old=$this->meterEstimate($bid)->butiran()->sole();$meter=$old->anggaran;
        $this->postJson($this->gridUrl($bid,$meter),$this->payload($bid,$meter,[$this->row($place,['kategori1'=>'Hitam dan Putih','lokasi'=>'Bilik Lama','bil_unit'=>'1000','kadar'=>'0.02','bil_bulan'=>'6'])]))->assertOk();
        $m=$meter->butiran()->where('id','<>',$old->id)->sole();$this->assertDatabaseCount('pautan_fotostat',1);
        $review=$this->get(route('fotostat.links',['bid'=>$bid->id,'sewaan_id'=>$r->id,'meter_id'=>$m->id]))->assertOk()->assertSee('Bilik Lama')->assertSee('Bilik Cetak');$this->gridCapture('fotostat-link-review',$review);
        $data=['sewaan_id'=>$r->id,'meter_id'=>$m->id,'semakan_bidaan'=>$bid->fresh()->semakan,'semakan_sewaan'=>$r->semakan,'semakan_meter'=>$m->semakan,'sahkan'=>'1'];
        $this->post(route('fotostat.attach',$bid->id),$data)->assertRedirect();$m=$m->fresh();$this->assertNull($old->fresh());$this->assertSame('Berwarna',$m->data_sumber->kategori1);$this->assertSame('Bilik Cetak',$m->data_sumber->lokasi);
        $this->assertSame('1000',$m->data_sumber->bil_unit);$this->assertSame('0.02',$m->data_sumber->kadar);$this->assertSame('6',$m->data_sumber->bil_bulan);
        $this->assertSame($m->id,DB::table('pautan_fotostat')->where('sewaan_id',$r->id)->value('meter_id'));$this->assertDatabaseCount('pautan_fotostat',1);
        $this->postJson(route('fotostat.attach',$bid->id),$data)->assertConflict();
    }
    public function test_missing_meter_permission_rolls_back_source_save_and_live_phase_guards_remain(): void {
        [$bid,$estimate,$place]=$this->gridContext();TetapanAksesPtj::where('rujukan_os_id',$this->meter->id)->update(['dibenarkan'=>false]);$audit=RekodAudit::count();
        $this->postJson($this->gridUrl($bid,$estimate),$this->payload($bid,$estimate,[$this->row($place)]))->assertForbidden();$this->assertDatabaseCount('butiran_anggaran',0);$this->assertSame($audit,RekodAudit::count());
        TetapanAksesPtj::where('rujukan_os_id',$this->meter->id)->update(['dibenarkan'=>true]);$this->phase->forceFill(['dibuka'=>false])->save();
        $this->postJson($this->gridUrl($bid,$estimate),$this->payload($bid,$estimate,[$this->row($place)]))->assertForbidden();$this->assertDatabaseCount('pautan_fotostat',0);
    }

    public function test_audit_failure_rolls_back_rental_meter_link_and_all_revisions(): void {
        [$bid,$estimate,$place]=$this->gridContext();$revision=$bid->fresh()->semakan;$estimateRevision=$estimate->fresh()->semakan;$audit=RekodAudit::count();
        DB::unprepared("CREATE FUNCTION pg_temp.reject_fotostat_link() RETURNS trigger LANGUAGE plpgsql AS $$ BEGIN RAISE EXCEPTION 'test link audit failure'; END $$");
        DB::unprepared("CREATE TRIGGER reject_fotostat_link BEFORE INSERT ON rekod_audit FOR EACH ROW WHEN (NEW.tindakan = 'source.meter_linked') EXECUTE FUNCTION pg_temp.reject_fotostat_link()");
        $this->postJson($this->gridUrl($bid,$estimate),$this->payload($bid,$estimate,[$this->row($place)]))->assertStatus(503);
        $this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('pautan_fotostat',0);$this->assertSame($revision,$bid->fresh()->semakan);$this->assertSame($estimateRevision,$estimate->fresh()->semakan);$this->assertSame($audit,RekodAudit::count());
    }
    public function test_populated_linked_meter_cannot_be_replaced_and_cross_ptj_writes_fail(): void {
        [$bid,$estimate,$place]=$this->gridContext();$this->postJson($this->gridUrl($bid,$estimate),$this->payload($bid,$estimate,[$this->row($place)]))->assertOk();
        $r=$estimate->butiran()->sole();$meter=$this->meterEstimate($bid);$m=$meter->butiran()->sole();
        $this->postJson($this->gridUrl($bid,$meter),$this->payload($bid,$meter,[$this->row($place,['butiran_id'=>$m->id,'semakan_butiran'=>$m->semakan,'bil_unit'=>'0','kadar'=>'0','bil_bulan'=>'0']),$this->row($place,['bil_unit'=>'1000','kadar'=>'0.03'])]))->assertOk();
        $other=$meter->butiran()->where('id','<>',$m->id)->sole();$oldLink=DB::table('pautan_fotostat')->where('sewaan_id',$r->id)->sole();
        $this->postJson(route('fotostat.attach',$bid->id),['sewaan_id'=>$r->id,'meter_id'=>$other->id,'semakan_bidaan'=>$bid->fresh()->semakan,'semakan_sewaan'=>$r->semakan,'semakan_meter'=>$other->semakan,'sahkan'=>'1'])->assertConflict();
        $this->assertSame($oldLink->meter_id,DB::table('pautan_fotostat')->where('sewaan_id',$r->id)->value('meter_id'));$this->assertDatabaseCount('butiran_anggaran',3);
        $foreign=\App\Models\TempatBertugas::create(['ptj_id'=>$this->other->id,'nama'=>'Sektor Perancangan','jenis'=>'sektor']);
        $this->postJson($this->gridUrl($bid,$estimate),$this->payload($bid,$estimate,[$this->row($foreign,['butiran_id'=>$r->id,'semakan_butiran'=>$r->fresh()->semakan])]))->assertForbidden();
        $this->assertSame($place->id,$r->fresh()->tempat_bertugas_id);
    }
    public function test_four_exact_mappings_and_readonly_role_preserve_pair_boundaries(): void {
        [$bid,$estimate,$place]=$this->gridContext();$code=\App\Models\KodAktiviti::where('aktiviti_kewangan_id',$this->activity->id)->sole();
        foreach([['Pengurusan Pentadbiran Negeri','010807'],['Pendidikan Rendah','030221'],['Pendidikan Menengah','030303'],['Pengurusan Asrama','030315']] as [$name,$value]){
            $this->activity->update(['nama'=>$name]);$code->update(['kod_aktiviti'=>$value]);$this->get($this->gridUrl($bid,$estimate))->assertOk();
            $this->assertInstanceOf(\App\Services\DefinisiSewaanFotostat::class,app(\App\Services\KatalogDefinisiAnggaran::class)->untuk($this->os,$bid->fresh()));
        }
        $this->activity->update(['nama'=>'Pengurusan Pentadbiran Negeri']);$code->update(['kod_aktiviti'=>'010807']);
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PemantauBidaan);$this->gridCapture('fotostat-readonly',$this->get($this->gridUrl($bid,$estimate))->assertOk());
        $this->postJson($this->gridUrl($bid,$estimate),$this->payload($bid,$estimate,[$this->row($place)]))->assertForbidden();
        $this->postJson(route('fotostat.deactivate',$bid->id),['semakan_bidaan'=>$bid->fresh()->semakan,'sahkan'=>'1'])->assertForbidden();
    }
}
