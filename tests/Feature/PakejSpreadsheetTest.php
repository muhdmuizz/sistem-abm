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

class PakejSpreadsheetTest extends TestCase
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
        $this->os = RujukanOs::create(['kod_objek' => '29000', 'butiran_perbelanjaan' => 'Pakej (Mesyuarat/ Bengkel/ Kursus)']);
        $this->unconfigured = RujukanOs::create(['kod_objek' => '29000', 'butiran_perbelanjaan' => 'Other service']);
        $code=\App\Models\KodAktiviti::create(['aktiviti_kewangan_id'=>$this->activity->id,'kod_aktiviti'=>'010810']);
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
    private function row($place,array $extra=[]): array {return array_replace(['tempat_bertugas_id'=>$place->id,'kategori1'=>'Penginapan','tujuan'=>'Bengkel Pengurusan','tarikh_mula'=>'2028-02-28','tarikh_tamat'=>'2028-03-01','bil_unit'=>'2','kadar'=>'180','bil_hari'=>'2'],$extra);}
    private function gridCapture(string $name,$response): void
    {
        $this->workspaceCapture($name,$response);
        if(getenv('ABM_UI_CAPTURE')==='1')foreach(['abm-money.js','anggaran-grid.css','anggaran-grid.js','pakej-grid.css','pakej-grid.js'] as $asset)copy(public_path($asset),storage_path('app/ui-qa/'.$asset));
    }
    private function pakejComponent(string $name='Penginapan',string $quantity='10',?string $rate='180',?string $duration='2'): array
    {
        return ['id'=>(string)\Illuminate\Support\Str::uuid(),'kategori1'=>$name,'bil_unit'=>$quantity,'kadar'=>$rate,'bil_hari'=>$duration];
    }
    private function groupRow($place,array $components,array $extra=[]): array
    {
        return array_replace(['tempat_bertugas_id'=>$place->id,'tujuan'=>'Bengkel Pengurusan Kewangan','tarikh_mula'=>'2028-02-28','tarikh_tamat'=>'2028-03-01','komponen'=>$components],$extra);
    }
    public function test_grouped_activity_saves_shared_fields_once_and_components_atomically_with_exact_subtotal(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        $row=$this->groupRow($place,[$this->pakejComponent(),$this->pakejComponent('Makan Minum','20','35','3'),$this->pakejComponent('Sewaan Dewan','1','500','3')]);
        $this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$source=$estimate->butiran()->sole();$definition=new \App\Services\DefinisiPakejBerkumpulan(2028);
        $this->assertSame('os29000.pakej.v2',$source->data_sumber->definition);$this->assertCount(3,$source->data_sumber->komponen);$this->assertSame('7200',$definition->preview($source->data_sumber));$this->assertTrue($definition->readable($source->data_sumber));
        foreach($source->data_sumber->komponen as $component){$this->assertObjectNotHasProperty('tujuan',$component);$this->assertObjectNotHasProperty('tarikh_mula',$component);}
        $response=$this->get($url)->assertOk()->assertViewIs('abm.estimates.pakej');$this->gridCapture('pakej-grouped-populated',$response);$this->assertCount(3,$response->viewData('records')[0]['komponen']);
        $row+=['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan];$row['tarikh_tamat']='2028-12-31';$row['komponen'][1]['kadar']='1,234.567';
        $this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$source->refresh();$this->assertSame('1234.567',$source->data_sumber->komponen[1]->kadar);$this->assertSame('79174.02',$definition->preview($source->data_sumber));$this->assertSame('3',$source->data_sumber->komponen[1]->bil_hari);
        $this->assertSame($row['komponen'][0]['id'],$source->data_sumber->komponen[0]->id);$row['komponen'][1]['kadar']='1234.567';$row['semakan_butiran']=$source->semakan;$audit=RekodAudit::count();
        $this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$this->assertSame($audit,RekodAudit::count());$this->assertDatabaseCount('hasil_pengiraan',0);
    }
    public function test_nested_component_errors_and_injected_shared_or_derived_fields_roll_back_whole_save(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$good=$this->groupRow($place,[$this->pakejComponent()]);
        foreach([['kadar'=>'1,23'],['unit'=>'Bilik'],['tujuan'=>'different'],['id'=>'bad'],['bil_hari'=>'1,000'],['kategori1'=>'Other']] as $change){
            $bad=$this->groupRow($place,[$this->pakejComponent(),array_replace($this->pakejComponent(),$change)]);
            $this->postJson($url,$this->payload($bid,$estimate,[$good,$bad]))->assertUnprocessable();$this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('rekod_audit',0);
        }
        $bad=$good;$bad['komponen'][]=$bad['komponen'][0];$this->postJson($url,$this->payload($bid,$estimate,[$bad]))->assertUnprocessable();
        $bad=$good;$bad['komponen'][0]['kadar']='oops';$this->postJson($url,$this->payload($bid,$estimate,[$bad]))->assertUnprocessable()->assertJsonValidationErrors('rekod.0.komponen.0.kadar');
        $bad=$good;$bad['tarikh_mula']='2027-12-31';$this->postJson($url,$this->payload($bid,$estimate,[$bad]))->assertUnprocessable()->assertJsonValidationErrors('rekod.0.tarikh_mula');
        $bad=$good;$bad['program_id']=1;$this->postJson($url,$this->payload($bid,$estimate,[$bad]))->assertUnprocessable();
        $bad=$good;$bad['komponen']=['x'=>$bad['komponen'][0]];$this->postJson($url,$this->payload($bid,$estimate,[$bad]))->assertUnprocessable();
    }
    public function test_grouped_partial_and_empty_activity_drafts_do_not_fabricate_subtotals(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$definition=new \App\Services\DefinisiPakejBerkumpulan(2028);
        $this->gridCapture('pakej-grouped-empty',$this->get($url)->assertOk());
        $blank=$this->groupRow($place,[],['tujuan'=>null,'tarikh_mula'=>null,'tarikh_tamat'=>null]);$this->postJson($url,$this->payload($bid,$estimate,[$blank]))->assertOk();$this->assertDatabaseCount('butiran_anggaran',0);
        $row=$this->groupRow($place,[$this->pakejComponent(),$this->pakejComponent('Makan Minum','20',null,null)]);$this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$source=$estimate->butiran()->sole();$this->assertNull($definition->preview($source->data_sumber));
        $this->gridCapture('pakej-grouped-partial',$this->get($url)->assertOk());
        $malformed=clone $source->data_sumber;$malformed->komponen=(object)[];$this->assertFalse($definition->readable($malformed));
        $malformed=unserialize(serialize($source->data_sumber));$malformed->komponen[0]->kadar='1,234.50';$this->assertFalse($definition->readable($malformed));
        $row['komponen']=[];$row+=['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan];$this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$this->assertCount(0,$source->fresh()->data_sumber->komponen);$this->assertNull($definition->preview($source->fresh()->data_sumber));
    }
    public function test_old_components_reopen_separately_and_group_updates_keep_live_guards_and_audit(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place),$this->row($place)]))->assertOk();
        $response=$this->get($url)->assertOk();$records=$response->viewData('records');$this->assertCount(2,$records);foreach($records as $record)$this->assertCount(1,$record['komponen']);
        $source=$estimate->butiran()->orderBy('id')->first();$record=$records[0];$row=$this->groupRow($place,$record['komponen'],['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan]);$row['komponen'][]=$this->pakejComponent('Makan Minum','20','35','3');
        $data=$this->payload($bid,$estimate,[$row]);$this->postJson($url,$data)->assertOk();$this->assertSame($source->id,$source->fresh()->id);$this->assertSame('source.draft_updated',RekodAudit::orderByDesc('id')->first()->tindakan);$this->postJson($url,$data)->assertConflict();
        $this->phase->forceFill(['dibuka'=>false])->save();$this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertForbidden();$this->phase->forceFill(['dibuka'=>true])->save();
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PemantauBidaan);$this->gridCapture('pakej-grouped-readonly',$this->get($url)->assertOk());$this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertForbidden();
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PenyediaAnggaran);$this->postJson($url,$this->payload($bid,$estimate,[],[['butiran_id'=>$source->id,'semakan_butiran'=>$source->fresh()->semakan]]))->assertOk();$this->assertDatabaseCount('butiran_anggaran',1);
    }
    public function test_pagination_never_splits_a_multi_component_activity_and_scope_stays_at_parent(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$rows=[];
        for($i=0;$i<26;$i++)$rows[]=$this->groupRow($place,[$this->pakejComponent(),$this->pakejComponent('Makan Minum','20','35','3')],['tujuan'=>'Aktiviti '.($i+1)]);
        $this->postJson($url,$this->payload($bid,$estimate,$rows))->assertOk();$response=$this->get($url)->assertOk();$this->assertCount(25,$response->viewData('records'));foreach($response->viewData('records') as $record)$this->assertCount(2,$record['komponen']);
        $response=$this->get($url.'?page=2')->assertOk();$this->assertCount(1,$response->viewData('records'));$this->assertCount(2,$response->viewData('records')[0]['komponen']);
        $otherPlace=\App\Models\TempatBertugas::create(['ptj_id'=>$this->other->id,'nama'=>'Sektor Perancangan','jenis'=>'sektor']);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->groupRow($otherPlace,[$this->pakejComponent()])]))->assertForbidden();$this->assertDatabaseCount('butiran_anggaran',26);
    }
    public function test_four_components_have_manual_rates_and_contextual_units_and_charge_labels(): void
    {
        [$bid,$estimate]=$this->gridContext();$response=$this->get($this->gridUrl($bid,$estimate))->assertOk();$refs=$response->viewData('references');
        $this->assertSame(['Penginapan','Makan Minum','Sewaan Dewan','Sewaan Peralatan'],array_keys($refs));
        $this->assertSame(['Bilik','Orang','Dewan','Unit'],array_column($refs,'unit'));$this->assertSame('Bil. Malam',$refs['Penginapan']['duration_label']);$this->assertSame('RM seorang sehari',$refs['Makan Minum']['basis']);
        foreach($refs as $ref)$this->assertArrayNotHasKey('kadar',$ref);
        $columns=collect($response->viewData('columns'))->keyBy('key');$this->assertSame('decimal',$columns['kadar']['type']);$this->assertTrue($columns['kadar']['money']);$this->assertSame('2028-01-01',$columns['tarikh_mula']['min']);
        foreach(['kategori2','kategori3','bil_bulan','nama','jawatan','gred'] as $key)$this->assertFalse($columns->has($key));
        $this->gridCapture('pakej-empty',$response);$this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('rekod_audit',0);
    }
    public function test_all_components_save_reopen_exact_manual_rates_and_independent_chargeable_duration(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$definition=new \App\Services\DefinisiPakej(2028);$rows=[];
        foreach(array_keys($definition->references()) as $component)$rows[]=$this->row($place,['kategori1'=>$component,'kadar'=>'1,234.567']);
        $this->postJson($url,$this->payload($bid,$estimate,$rows))->assertOk();$sources=$estimate->butiran()->orderBy('id')->get();$this->assertCount(4,$sources);
        foreach($sources as $source){$this->assertSame('1234.567',$source->data_sumber->kadar);$this->assertSame('4938.268',$definition->preview($source->data_sumber));$this->assertSame(3,$definition->duration($source->data_sumber));$this->assertSame('2',$source->data_sumber->bil_hari);}
        $this->gridCapture('pakej-populated',$this->get($url)->assertOk());$first=$sources->first();
        $row=$this->row($place,['butiran_id'=>$first->id,'semakan_butiran'=>$first->semakan,'kadar'=>'1234.567','bil_unit'=>'5']);
        $this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$this->assertSame('12345.67',$definition->preview($first->fresh()->data_sumber));
        $row['semakan_butiran']=$first->fresh()->semakan;$count=RekodAudit::count();$this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$this->assertSame($count,RekodAudit::count());
        $this->assertDatabaseCount('hasil_pengiraan',0);$this->assertSame('2',$first->fresh()->data_sumber->bil_hari);
    }
    public function test_all_five_existing_mappings_are_required_and_other_variants_stay_unconfigured(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$code=\App\Models\KodAktiviti::where('aktiviti_kewangan_id',$this->activity->id)->sole();
        foreach(['Pengurusan Pentadbiran Negeri'=>'010810','Pendidikan Rendah'=>'030225','Pendidikan Awal'=>'030104','Pendidikan Menengah'=>'030307','Pengurusan Asrama'=>'030317'] as $name=>$value){
            $this->activity->update(['nama'=>$name]);$code->update(['kod_aktiviti'=>$value]);$this->get($this->gridUrl($bid,$estimate))->assertOk();
            $code->update(['kod_aktiviti'=>'999999']);$this->get($this->gridUrl($bid,$estimate))->assertNotFound();
        }
        $this->assertNull(app(\App\Services\KatalogDefinisiAnggaran::class)->untuk($this->unconfigured,$bid->fresh()));
    }
    public function test_dates_injection_and_malformed_values_reject_atomically_without_new_programme_rules(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        foreach([['unit'=>'Bilik'],['tempoh'=>'3'],['program_id'=>1],['kategori2'=>'Other'],['kategori1'=>'Other'],['kadar'=>'1,23'],['bil_hari'=>'1,000'],['tarikh_mula'=>'2027-12-31'],['tarikh_tamat'=>'2029-01-01'],['tarikh_mula'=>'2028-02-30'],['tarikh_tamat'=>'2028-02-27']] as $change){
            $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place),$this->row($place,$change)]))->assertUnprocessable();$this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('rekod_audit',0);
        }
        $definition=new \App\Services\DefinisiPakej(2028);$source=$definition->source($this->row($place,['tarikh_mula'=>'2028-02-29','tarikh_tamat'=>'2028-02-29','bil_hari'=>'2']));
        $this->assertSame(1,$definition->duration($source));$this->assertSame('720',$definition->preview($source));
    }
    public function test_blank_and_partial_drafts_do_not_invent_manual_rate_or_chargeable_days(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$keys=['kategori1','tujuan','tarikh_mula','tarikh_tamat','bil_unit','kadar','bil_hari'];
        $blank=$this->row($place,array_fill_keys($keys,null));$this->postJson($url,$this->payload($bid,$estimate,[$blank]))->assertOk();$this->assertDatabaseCount('butiran_anggaran',0);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['kadar'=>null,'bil_hari'=>null])]))->assertOk();$source=$estimate->butiran()->sole()->data_sumber;
        $this->assertNull($source->kadar);$this->assertNull($source->bil_hari);$this->assertNull((new \App\Services\DefinisiPakej(2028))->preview($source));$this->gridCapture('pakej-partial',$this->get($url)->assertOk());
    }
    public function test_live_guards_readonly_and_audited_removal_are_retained(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$data=$this->payload($bid,$estimate,[$this->row($place)]);
        $this->postJson($url,$data)->assertOk();$this->postJson($url,$data)->assertConflict();$source=$estimate->butiran()->sole();
        $this->phase->forceFill(['dibuka'=>false])->save();$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertForbidden();$this->phase->forceFill(['dibuka'=>true])->save();
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PemantauBidaan);$this->gridCapture('pakej-readonly',$this->get($url)->assertOk());$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertForbidden();
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PenyediaAnggaran);
        $this->postJson($url,$this->payload($bid,$estimate,[],[['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan]]))->assertOk();$this->assertSame('source.draft_deleted',RekodAudit::orderByDesc('id')->first()->tindakan);
    }
}
