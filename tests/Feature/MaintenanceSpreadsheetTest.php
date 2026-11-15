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

class MaintenanceSpreadsheetTest extends TestCase
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
        $this->os = RujukanOs::create(['kod_objek' => '28000', 'butiran_perbelanjaan' => 'Penyelenggaraan dan Pembaikan Kecil Yang Dibeli']);
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
    private function row($place,array $extra=[]): array {return array_replace(['tempat_bertugas_id'=>$place->id,'tujuan'=>'Penyelenggaraan aset','kategori1'=>'Penyelenggaraan Alat Pemadam Api','kategori2'=>'Alat Pemadam Api','bil_unit'=>'3','kadar'=>null,'kekerapan'=>'1','bil_bulan'=>'12'],$extra);}
    private function gridCapture(string $name,$response): void
    {
        $this->workspaceCapture($name,$response);
        if(getenv('ABM_UI_CAPTURE')==='1')foreach(['abm-money.js','anggaran-grid.css','anggaran-grid.js'] as $asset)copy(public_path($asset),storage_path('app/ui-qa/'.$asset));
    }
    public function test_maintenance_grid_exposes_exact_ten_combinations_and_blank_tree_equipment_without_side_effects(): void
    {
        [$bid,$estimate]=$this->gridContext();$response=$this->get($this->gridUrl($bid,$estimate))->assertOk();
        $refs=$response->viewData('references');$this->assertCount(4,$refs);$this->assertSame(10,array_sum(array_map('count',$refs)));
        $this->assertSame([''],$this->keys($refs['Tebang Pokok']));$this->assertSame('1500.00',$refs['Tebang Pokok']['']['kadar']);
        $this->assertSame('100.00',$refs['Penyelenggaraan Alat Pemadam Api']['Alat Pemadam Api']['kadar']);
        $this->assertNull($refs['Penyelenggaraan Kenderaan Jabatan']['Sedan']['kadar']);$this->gridCapture('maintenance-empty',$response);
        $this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('rekod_audit',0);
        $this->get(route('estimates.show',[$bid->id,$estimate->id]))->assertOk()->assertSee('Buka Jadual Butiran');
    }
    private function keys(array $array): array { return array_keys($array); }

    public function test_all_ten_combinations_save_reopen_with_authoritative_mixed_rates_and_exact_formula(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$definition=app(\App\Services\DefinisiPenyelenggaraan::class);$rows=[];
        foreach($definition->references() as $parent=>$children)foreach($children as $child=>$reference){
            $rows[]=$this->row($place,['kategori1'=>$parent,'kategori2'=>$child===''?null:$child,'kadar'=>$reference['mode']==='FIXED_REFERENCE'?null:'1,234.5678']);
        }
        $this->postJson($this->gridUrl($bid,$estimate),$this->payload($bid,$estimate,$rows))->assertOk();
        $sources=$estimate->butiran()->orderBy('id')->get();$this->assertCount(10,$sources);
        foreach($sources as $source){
            $data=$source->data_sumber;$fixed=$data->kategori1==='Tebang Pokok'||$data->kategori2==='Alat Pemadam Api';
            $this->assertSame($fixed?null:'1234.5678',$data->kadar);
            $this->assertSame($data->kategori1==='Tebang Pokok'?'54000':($fixed?'3600':'44444.4408'),$definition->preview($data));
        }
        $this->assertDatabaseCount('hasil_pengiraan',0);$this->assertDatabaseCount('rekod_audit',10);
        $this->gridCapture('maintenance-populated',$this->get($this->gridUrl($bid,$estimate))->assertOk());
        $first=$sources->first();$row=$rows[0]+['butiran_id'=>$first->id,'semakan_butiran'=>$first->semakan];$row['tujuan']='Pembaikan kenderaan';
        $this->postJson($this->gridUrl($bid,$estimate),$this->payload($bid,$estimate,[$row]))->assertOk();
        $this->assertSame('Pembaikan kenderaan',$first->fresh()->data_sumber->tujuan);$this->assertSame('1234.5678',$first->fresh()->data_sumber->kadar);
    }

    public function test_invalid_dependencies_fixed_rate_overrides_and_unapproved_fields_reject_entire_save(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        foreach([['kategori2'=>'Sedan'],['kadar'=>'100.00'],['kategori1'=>'Tebang Pokok','kategori2'=>'Pokok','kadar'=>null],
            ['kategori3'=>'Other'],['bil_unit'=>'1,000'],['kekerapan'=>'1e3'],['kadar'=>'1,00.00'],['preview'=>'0']] as $change){
            $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place),$this->row($place,$change)]))->assertUnprocessable();
            $this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('rekod_audit',0);
        }
    }

    public function test_partial_drafts_and_tree_blank_equipment_do_not_invent_defaults_or_official_amounts(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        $blank=$this->row($place,array_fill_keys(['tujuan','kategori1','kategori2','bil_unit','kadar','kekerapan','bil_bulan'],null));
        $this->postJson($url,$this->payload($bid,$estimate,[$blank]))->assertOk();$this->assertDatabaseCount('butiran_anggaran',0);
        $partial=$this->row($place,['kategori1'=>'Tebang Pokok','kategori2'=>null,'bil_unit'=>null,'bil_bulan'=>null]);
        $this->postJson($url,$this->payload($bid,$estimate,[$partial]))->assertOk();$source=$estimate->butiran()->sole();
        $this->assertNull($source->data_sumber->kategori2);$this->assertNull($source->data_sumber->kadar);
        $this->assertNull(app(\App\Services\DefinisiPenyelenggaraan::class)->preview($source->data_sumber));$this->assertDatabaseCount('hasil_pengiraan',0);
        $this->gridCapture('maintenance-partial',$this->get($url)->assertOk());
    }

    public function test_parent_change_rejects_stale_manual_rate_when_switching_to_reference_and_preserves_source_identity(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['kategori2'=>'Sistem Pencegahan Kebakaran','kadar'=>'4,320.001'])]))->assertOk();
        $source=$estimate->butiran()->sole();$this->assertSame('4320.001',$source->data_sumber->kadar);
        $row=$this->row($place,['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan,'kategori1'=>'Tebang Pokok','kategori2'=>null,'kadar'=>'4320.001']);
        $this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertUnprocessable();$row['kadar']=null;
        $this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$this->assertSame($source->id,$source->fresh()->id);$this->assertNull($source->fresh()->data_sumber->kadar);
    }

    public function test_live_phase_stale_role_and_legacy_boundaries_survive_maintenance_definition(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$data=$this->payload($bid,$estimate,[$this->row($place)]);
        $this->postJson($url,$data)->assertOk();$this->postJson($url,$data)->assertConflict();
        $this->phase->forceFill(['dibuka'=>false])->save();$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertForbidden();$this->phase->forceFill(['dibuka'=>true])->save();
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PemantauBidaan);
        $response=$this->get($url)->assertOk()->assertDontSee('<button type="submit" class="button primary">',false);$this->gridCapture('maintenance-readonly',$response);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertForbidden();
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PenyediaAnggaran);
        $source=$estimate->butiran()->sole();$source->forceFill(['data_sumber'=>(object)['definition'=>'os25000.v1','SECRET_LEGACY'=>'private']])->save();
        $this->get($url)->assertOk()->assertDontSee('SECRET_LEGACY');
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan])]))->assertConflict();
    }
}
