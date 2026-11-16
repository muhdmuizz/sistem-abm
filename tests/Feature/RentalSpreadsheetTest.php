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

class RentalSpreadsheetTest extends TestCase
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
        $this->os = RujukanOs::create(['kod_objek' => '29000', 'butiran_perbelanjaan' => 'Sewaan']);
        $this->unconfigured = RujukanOs::create(['kod_objek' => '29000', 'butiran_perbelanjaan' => 'Pakej (Mesyuarat/ Bengkel/ Kursus)']);
        $code=\App\Models\KodAktiviti::create(['aktiviti_kewangan_id'=>$this->activity->id,'kod_aktiviti'=>'010801']);
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
    private function row($place,array $extra=[]): array {return array_replace(['tempat_bertugas_id'=>$place->id,'tujuan'=>'Sewaan Dewan Mesyuarat','kategori1'=>'Berkontrak','kategori2'=>'Pelbagai','kategori3'=>'Dewan (Venue)','tarikh_mula'=>'2028-02-28','tarikh_tamat'=>'2028-03-01','bil_unit'=>'2','kadar'=>'180','bil_bulan'=>'12'],$extra);}
    private function gridCapture(string $name,$response): void
    {
        $this->workspaceCapture($name,$response);
        if(getenv('ABM_UI_CAPTURE')==='1')foreach(['abm-money.js','anggaran-grid.css','anggaran-grid.js'] as $asset)copy(public_path($asset),storage_path('app/ui-qa/'.$asset));
    }
    public function test_three_category_levels_manual_amounts_and_conditional_date_metadata(): void
    {
        [$bid,$estimate]=$this->gridContext();$response=$this->get($this->gridUrl($bid,$estimate))->assertOk();
        $columns=collect($response->viewData('columns'))->keyBy('key');$refs=$response->viewData('references');
        $this->assertSame(['Berkontrak','Tidak Berkontrak'],array_keys($refs));
        foreach($refs as $branches){$this->assertSame(32,array_sum(array_map('count',$branches)));$this->assertSame([''],array_keys($branches['Tanah']));}
        $this->assertSame(['kategori1','kategori2'],$columns['kategori3']['referenceParents']);$this->assertTrue($columns['kategori3']['referenceLeaf']);
        $this->assertSame(['field'=>'kategori1','value'=>'Berkontrak'],$columns['tarikh_mula']['activeWhen']);
        $this->assertSame('2028-01-01',$columns['tarikh_mula']['min']);$this->assertSame('2028-12-31',$columns['tarikh_tamat']['max']);
        foreach(['nama','jawatan','gred','unit','lokasi','tarikh_mula_kontrak','tarikh_tamat_kontrak'] as $key)$this->assertFalse($columns->has($key));
        $this->gridCapture('rental-empty',$response);$this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('rekod_audit',0);
    }

    public function test_all_64_approved_combinations_save_with_blank_land_and_exact_broadcast_labels(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$definition=new \App\Services\DefinisiSewaanUmum(2028);$rows=[];
        foreach($definition->references() as $one=>$branches)foreach($branches as $two=>$types)foreach($types as $three=>$reference){
            $rows[]=$this->row($place,['kategori1'=>$one,'kategori2'=>$two,'kategori3'=>$three===''?null:$three,
                'tarikh_mula'=>$one==='Berkontrak'?'2028-02-28':null,'tarikh_tamat'=>$one==='Berkontrak'?'2028-03-01':null]);
        }
        $this->assertCount(64,$rows);$this->postJson($this->gridUrl($bid,$estimate),$this->payload($bid,$estimate,$rows))->assertOk();$this->assertDatabaseCount('butiran_anggaran',64);
        foreach($estimate->butiran()->get() as $source){$this->assertTrue($definition->readable($source->data_sumber));$this->assertSame('4320',$definition->preview($source->data_sumber));if($source->data_sumber->kategori2==='Tanah')$this->assertNull($source->data_sumber->kategori3);}
        $this->assertArrayHasKey('Sewa Alat Kelengkapan Penyiaran',$definition->references()['Berkontrak']);
        $this->assertArrayHasKey('Alat Kelengkapan Penyiaran',$definition->references()['Tidak Berkontrak']);
        $first=$this->get($this->gridUrl($bid,$estimate))->assertOk();$this->assertCount(25,$first->viewData('records'));
        $last=$this->get($this->gridUrl($bid,$estimate).'?page=3')->assertOk();$this->assertCount(14,$last->viewData('records'));
        $this->assertDatabaseCount('hasil_pengiraan',0);
    }

    public function test_rental_source_save_reopen_edit_noop_and_exact_manual_precision(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$definition=new \App\Services\DefinisiSewaanUmum(2028);
        $rows=[$this->row($place),$this->row($place,['kategori1'=>'Tidak Berkontrak','kategori2'=>'Tanah','kategori3'=>null,'tarikh_mula'=>null,'tarikh_tamat'=>null,'kadar'=>'1234.567','bil_bulan'=>'3'])];
        $this->postJson($url,$this->payload($bid,$estimate,$rows))->assertOk();$sources=$estimate->butiran()->orderBy('id')->get();$first=$sources[0];
        $this->assertSame('4320',$definition->preview($first->data_sumber));$this->assertSame(3,$definition->duration($first->data_sumber));
        $this->assertSame('7407.402',$definition->preview($sources[1]->data_sumber));$this->assertNull($definition->duration($sources[1]->data_sumber));$this->assertSame('1234.567',$sources[1]->data_sumber->kadar);
        $this->gridCapture('rental-populated',$this->get($url)->assertOk());
        $row=$this->row($place,['butiran_id'=>$first->id,'semakan_butiran'=>$first->semakan,'bil_bulan'=>'5','tarikh_mula'=>'2028-01-01','tarikh_tamat'=>'2028-12-31']);
        $this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$fresh=$first->fresh();
        $this->assertSame('1800',$definition->preview($fresh->data_sumber));$this->assertSame(366,$definition->duration($fresh->data_sumber));$this->assertSame('5',$fresh->data_sumber->bil_bulan);
        $row['semakan_butiran']=$fresh->semakan;$audit=RekodAudit::count();$this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$this->assertSame($audit,RekodAudit::count());
        $row=array_replace($row,['semakan_butiran'=>$first->fresh()->semakan,'kategori1'=>'Tidak Berkontrak','tarikh_mula'=>null,'tarikh_tamat'=>null]);
        $this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$this->assertNull($first->fresh()->data_sumber->tarikh_mula);
        $this->assertEqualsCanonicalizing(['definition','tujuan','kategori1','kategori2','kategori3','tarikh_mula','tarikh_tamat','bil_unit','kadar','bil_bulan'],array_keys(get_object_vars($fresh->data_sumber)));
        $bad=clone $fresh->data_sumber;$bad->tempoh=366;$this->assertFalse($definition->readable($bad));
    }

    public function test_five_existing_general_rental_mappings_are_separate_from_machine_rental(): void
    {
        [$bid,$estimate]=$this->gridContext();$code=\App\Models\KodAktiviti::where('aktiviti_kewangan_id',$this->activity->id)->sole();
        foreach([['Pengurusan Pentadbiran Negeri','010801'],['Pendidikan Rendah','030219'],['Pendidikan Awal','030101'],['Pendidikan Menengah','030301'],['Pengurusan Asrama','030313']] as [$name,$value]){
            $this->activity->update(['nama'=>$name]);$code->update(['kod_aktiviti'=>$value]);$this->get($this->gridUrl($bid,$estimate))->assertOk();
            $this->assertInstanceOf(\App\Services\DefinisiSewaanUmum::class,app(\App\Services\KatalogDefinisiAnggaran::class)->untuk($this->os,$bid->fresh()));
            $code->update(['kod_aktiviti'=>'999999']);$this->get($this->gridUrl($bid,$estimate))->assertNotFound();
        }
        $definition=new \App\Services\DefinisiSewaanUmum(2028);
        foreach([['24000','Sewaan Mesin Fotostat dan Riso'],['29000','Bacaan Meter Mesin Fotostat'],['29000','Sewaan']] as [$object,$name])$this->assertFalse($definition->supports(new RujukanOs(['kod_objek'=>$object,'butiran_perbelanjaan'=>$name])));
        $this->os->dikecualikan=true;$this->assertFalse($definition->supports($this->os));
    }

    public function test_invalid_categories_conditional_dates_and_inactive_or_derived_injections_are_atomic(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        foreach([['kategori1'=>'Other'],['kategori2'=>'Other'],['kategori2'=>'Kenderaan','kategori3'=>'Dewan (Venue)'],
            ['kategori2'=>'Tanah','kategori3'=>'Tanah'],['kategori1'=>'Berkontrak','kategori2'=>'Alat Kelengkapan Penyiaran','kategori3'=>'Alat Kelengkapan Penyiaran'],
            ['kategori1'=>'Tidak Berkontrak','kategori2'=>'Sewa Alat Kelengkapan Penyiaran','kategori3'=>'Alat Kelengkapan Penyiaran','tarikh_mula'=>null,'tarikh_tamat'=>null],
            ['kategori1'=>'Tidak Berkontrak'],['kategori1'=>null],['tarikh_mula'=>'2027-12-31'],['tarikh_tamat'=>'2029-01-01'],['tarikh_mula'=>'2028-02-30'],['tarikh_tamat'=>'2028-02-27'],
            ['nama'=>'Someone'],['unit'=>'Unit'],['lokasi'=>'Office'],['tempoh'=>'3'],['preview'=>'4320'],['bil_unit'=>'1,000'],['kadar'=>'1e2'],['bil_bulan'=>'12x'],['tujuan'=>str_repeat('x',1001)],
            ['kategori1'=>[]],['kategori2'=>[]],['kategori3'=>[]]] as $extra){
            $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place),$this->row($place,$extra)]))->assertUnprocessable();
            $this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('rekod_audit',0);
        }
    }

    public function test_partial_paths_missing_money_and_inactive_dates_do_not_invent_values(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$definition=new \App\Services\DefinisiSewaanUmum(2028);
        $keys=['tujuan','kategori1','kategori2','kategori3','tarikh_mula','tarikh_tamat','bil_unit','kadar','bil_bulan'];
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,array_fill_keys($keys,null))]))->assertOk();$this->assertDatabaseCount('butiran_anggaran',0);
        foreach(['kategori3','bil_unit','kadar','bil_bulan'] as $missing)$this->assertNull($definition->preview($definition->source($this->row($place,[$missing=>null]))));
        $partial=$this->row($place,['kategori3'=>null,'tarikh_tamat'=>null,'kadar'=>null,'bil_bulan'=>null]);
        $this->postJson($url,$this->payload($bid,$estimate,[$partial]))->assertOk();$source=$estimate->butiran()->sole()->data_sumber;
        $this->assertNull($source->kadar);$this->assertNull($source->bil_bulan);$this->assertNull($definition->duration($source));$this->gridCapture('rental-partial',$this->get($url)->assertOk());
        $this->assertSame(1,$definition->duration($definition->source($this->row($place,['tarikh_mula'=>'2028-02-29','tarikh_tamat'=>'2028-02-29']))));
        $this->assertSame('0',$definition->preview($definition->source($this->row($place,['kadar'=>'0']))));
    }

    public function test_live_guards_readonly_cross_ptj_and_audited_removal_are_preserved(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$data=$this->payload($bid,$estimate,[$this->row($place)]);
        $this->postJson($url,$data)->assertOk();$this->postJson($url,$data)->assertConflict();$source=$estimate->butiran()->sole();
        $other=\App\Models\TempatBertugas::create(['ptj_id'=>$this->other->id,'nama'=>'Sektor Perancangan','jenis'=>'sektor']);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($other)]))->assertForbidden();
        $this->phase->forceFill(['dibuka'=>false])->save();$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertForbidden();$this->phase->forceFill(['dibuka'=>true])->save();
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PemantauBidaan);$this->gridCapture('rental-readonly',$this->get($url)->assertOk());
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertForbidden();
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PenyediaAnggaran);
        $this->postJson($url,$this->payload($bid,$estimate,[],[['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan]]))->assertOk();
        $this->assertSame('source.draft_deleted',RekodAudit::orderByDesc('id')->first()->tindakan);
    }
}
