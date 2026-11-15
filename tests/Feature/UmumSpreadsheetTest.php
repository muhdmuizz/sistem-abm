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

class UmumSpreadsheetTest extends TestCase
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
        $this->os = RujukanOs::create(['kod_objek' => '29000', 'butiran_perbelanjaan' => 'Perkhidmatan Iktisas dan Perkhidmatan Lain Yang Dibeli dan Hospitaliti']);
        $this->unconfigured = RujukanOs::create(['kod_objek' => '29000', 'butiran_perbelanjaan' => 'Other service']);
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
    private function row($place,array $extra=[]): array {return array_replace(['tempat_bertugas_id'=>$place->id,'kategori1'=>'Keraian, Hospitaliti, Honorarium dll.','kategori2'=>'Makan Minum (Mesyuarat)','nama'=>null,'jawatan'=>null,'gred'=>null,'tujuan'=>'Bengkel Pengurusan','tarikh_mula'=>'2028-02-28','tarikh_tamat'=>'2028-03-01','bil_unit'=>'2','kadar'=>null],$extra);}
    private function gridCapture(string $name,$response): void
    {
        $this->workspaceCapture($name,$response);
        if(getenv('ABM_UI_CAPTURE')==='1')foreach(['abm-money.js','anggaran-grid.css','anggaran-grid.js','pakej-grid.css','pakej-grid.js'] as $asset)copy(public_path($asset),storage_path('app/ui-qa/'.$asset));
    }
    private function personnelRow($place,array $extra=[]): array {return $this->row($place,array_replace(['kategori1'=>'Bayaran Penceramah/ Fasilitator','kategori2'=>'Bayaran Penceramah','nama'=>'AHMAD','jawatan'=>'JURUTERA','gred'=>'J14'],$extra));}
    public function test_shared_reference_grades_classify_all_131_pairs_and_rates_keep_18_auto_four_manual(): void
    {
        [$bid,$estimate]=$this->gridContext();$response=$this->get($this->gridUrl($bid,$estimate))->assertOk();$refs=$response->viewData('references');$definition=new \App\Services\DefinisiUmum(2028);$jobs=app(\App\Services\RujukanJawatan::class)->grades();
        $this->assertCount(3,$refs);$automatic=0;$manual=0;foreach($refs as $services)foreach($services as $ref){if($ref['mode']==='FIXED_REFERENCE')$automatic+=isset($ref['speaker_rates'])?count($ref['speaker_rates']):1;else $manual++;}
        $this->assertSame(18,$automatic);$this->assertSame(4,$manual);$count=0;$unique=[];
        foreach($jobs as $job=>$grades)foreach($grades as $grade){$this->assertNotNull($definition->classification($grade));$this->assertNotNull($definition->rowReference(['kategori1'=>'Bayaran Penceramah/ Fasilitator','kategori2'=>'Bayaran Penceramah','jawatan'=>$job,'gred'=>$grade])['kadar']);$unique[$grade]=true;$count++;}
        $this->assertSame(131,$count);$this->assertCount(82,$unique);$this->assertNull($definition->classification('DG99'));$this->assertSame('Kumpulan Pengurusan Tertinggi',$definition->classification('VU7'));
        $this->gridCapture('umum-empty',$response);$this->assertDatabaseCount('butiran_anggaran',0);
    }
    public function test_all_services_save_exact_sources_reopen_and_never_store_derived_fields(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$d=new \App\Services\DefinisiUmum(2028);$rows=[];
        foreach($d->references() as $parent=>$services)foreach($services as $service=>$ref){$row=$this->row($place,['kategori1'=>$parent,'kategori2'=>$service,'kadar'=>$ref['mode']==='USER_INPUT_KADAR'?'1,234.567':null]);if($ref['personnel'])$row=array_replace($row,['nama'=>'AHMAD','jawatan'=>'JURUTERA','gred'=>'J14']);$rows[]=$row;}
        $this->postJson($url,$this->payload($bid,$estimate,$rows))->assertOk();$sources=$estimate->butiran()->orderBy('id')->get();$this->assertCount(14,$sources);
        foreach($sources as $source){$this->assertTrue($d->readable($source->data_sumber));$this->assertNotNull($d->preview($source->data_sumber));foreach(['unit','payment_grade','preview','kategori3'] as $field)$this->assertObjectNotHasProperty($field,$source->data_sumber);}
        $this->gridCapture('umum-populated',$this->get($url)->assertOk());$first=$sources->first();$row=$this->personnelRow($place,['butiran_id'=>$first->id,'semakan_butiran'=>$first->semakan,'bil_unit'=>'3']);
        $this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$this->assertSame('600',$d->preview($first->fresh()->data_sumber));$this->assertSame($first->id,$first->fresh()->id);$row['semakan_butiran']=$first->fresh()->semakan;$audit=RekodAudit::count();$this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$this->assertSame($audit,RekodAudit::count());$this->assertDatabaseCount('hasil_pengiraan',0);
    }
    public function test_classification_boundaries_and_conditional_fields_previews_and_vu7(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$d=new \App\Services\DefinisiUmum(2028);
        foreach(['J14'=>'200.00','J13'=>'150.00','J12'=>'150.00','J10'=>'120.00','J9'=>'120.00'] as $grade=>$rate){$s=$d->source($this->personnelRow($place,['gred'=>$grade]));$this->assertSame($rate,$d->rowReference(get_object_vars($s))['kadar']);}
        $s=$d->source($this->personnelRow($place,['jawatan'=>'PENGARAH PENDIDIKAN NEGERI','gred'=>'VU7']));$this->assertSame('600',$d->preview($s));
        foreach(['nama','jawatan','gred'] as $key)$this->assertNull($d->preview($d->source($this->personnelRow($place,[$key=>null]))));
        $this->assertSame('400',$d->preview($d->source($this->personnelRow($place,['nama'=>'0']))));
        $fee=$this->personnelRow($place,['kategori1'=>'Bayaran Yuran Bengkel/ Kursus/ Taklimat/ Seminar','kategori2'=>'Bayaran Yuran','kadar'=>'1234.567']);$this->assertSame('2469.134',$d->preview($d->source($fee)));$this->assertArrayNotHasKey('payment_grade',$d->rowReference($fee));
        $gift=$this->row($place,['kategori2'=>'Hadiah Penghargaan Pesara']);$this->assertSame('1200',$d->preview($d->source($gift)));$this->assertFalse($d->rowReference($gift)['personnel']);
    }
    public function test_bad_pairs_derived_injection_fixed_rate_overrides_and_dates_reject_atomically(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        foreach([['unit'=>'Jam'],['payment_grade'=>'Other'],['kategori3'=>'Other'],['program_id'=>1],['kadar'=>'200'],['kategori2'=>'Dobi'],['gred'=>'W1'],['gred'=>'J99'],['tarikh_mula'=>'2027-12-31'],['tarikh_tamat'=>'2028-02-27'],['tarikh_mula'=>'2028-02-30']] as $change){$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place),$this->personnelRow($place,$change)]))->assertUnprocessable();$this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('rekod_audit',0);}
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['nama'=>'Not applicable'])]))->assertUnprocessable();
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['kategori2'=>'Dobi','kadar'=>'1,23'])]))->assertUnprocessable();
    }
    public function test_partial_drafts_and_live_role_phase_revision_and_removal_guards(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$row=$this->personnelRow($place,['gred'=>null]);$data=$this->payload($bid,$estimate,[$row]);$this->postJson($url,$data)->assertOk();$this->postJson($url,$data)->assertConflict();$source=$estimate->butiran()->sole();$this->assertNull((new \App\Services\DefinisiUmum(2028))->preview($source->data_sumber));$this->gridCapture('umum-partial',$this->get($url)->assertOk());
        $this->phase->forceFill(['dibuka'=>false])->save();$this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertForbidden();$this->phase->forceFill(['dibuka'=>true])->save();
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PemantauBidaan);$this->gridCapture('umum-readonly',$this->get($url)->assertOk());$this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertForbidden();
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PenyediaAnggaran);$this->postJson($url,$this->payload($bid,$estimate,[],[['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan]]))->assertOk();$this->assertSame('source.draft_deleted',RekodAudit::orderByDesc('id')->first()->tindakan);
    }
    public function test_five_exact_activity_mappings_and_other_variants_stay_separate(): void
    {
        [$bid,$estimate]=$this->gridContext();$code=\App\Models\KodAktiviti::where('aktiviti_kewangan_id',$this->activity->id)->sole();foreach(['Pengurusan Pentadbiran Negeri'=>'010801','Pendidikan Rendah'=>'030219','Pendidikan Awal'=>'030101','Pendidikan Menengah'=>'030301','Pengurusan Asrama'=>'030313'] as $name=>$value){$this->activity->update(['nama'=>$name]);$code->update(['kod_aktiviti'=>$value]);$this->get($this->gridUrl($bid,$estimate))->assertOk();$code->update(['kod_aktiviti'=>'999999']);$this->get($this->gridUrl($bid,$estimate))->assertNotFound();}
        $this->assertNull(app(\App\Services\KatalogDefinisiAnggaran::class)->untuk($this->unconfigured,$bid->fresh()));
    }
    private function umumComponent(array $row): array
    {
        return ['id'=>(string)\Illuminate\Support\Str::uuid()]+array_intersect_key($row,array_flip(['kategori1','kategori2','nama','jawatan','gred','bil_unit','kadar']));
    }
    private function umumActivity($place,array $components,array $extra=[]): array
    {
        return array_replace(['tempat_bertugas_id'=>$place->id,'tujuan'=>'Bengkel Pengurusan Kewangan','tarikh_mula'=>'2028-02-28','tarikh_tamat'=>'2028-03-01','komponen'=>$components],$extra);
    }
    public function test_grouped_umum_shares_activity_fields_and_keeps_component_personnel_rates_and_exact_subtotal(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$d=new \App\Services\DefinisiUmumBerkumpulan(2028);
        $row=$this->umumActivity($place,[$this->umumComponent($this->personnelRow($place,['bil_unit'=>'3'])),$this->umumComponent($this->row($place,['bil_unit'=>'20'])),$this->umumComponent($this->row($place,['kategori2'=>'Dobi','kadar'=>'1,234.567']))]);
        $this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$source=$estimate->butiran()->sole();$this->assertSame('os29000.umum.v2',$source->data_sumber->definition);$this->assertSame('3229.134',$d->preview($source->data_sumber));$this->assertTrue($d->readable($source->data_sumber));$this->assertCount(3,$source->data_sumber->komponen);$this->assertObjectNotHasProperty('nama',$source->data_sumber);
        foreach($source->data_sumber->komponen as $component){$this->assertObjectNotHasProperty('tujuan',$component);$this->assertObjectNotHasProperty('tarikh_mula',$component);$this->assertObjectNotHasProperty('payment_grade',$component);}
        $this->assertSame('AHMAD',$source->data_sumber->komponen[0]->nama);$this->assertNull($source->data_sumber->komponen[1]->nama);$this->assertNull($source->data_sumber->komponen[0]->kadar);$this->assertSame('1234.567',$source->data_sumber->komponen[2]->kadar);
        $response=$this->get($url)->assertOk()->assertViewIs('abm.estimates.pakej');$this->assertCount(1,$response->viewData('records'));$this->assertCount(3,$response->viewData('records')[0]['komponen']);$this->gridCapture('umum-grouped-populated',$response);
        $row['komponen'][2]['kadar']='1234.567';$row+=['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan];$audit=RekodAudit::count();$this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$this->assertSame($audit,RekodAudit::count());
        $row['tujuan']='Bengkel Baharu';$row['tarikh_tamat']='2028-12-31';$this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$source->refresh();$this->assertSame($row['komponen'][0]['id'],$source->data_sumber->komponen[0]->id);$this->assertSame('3229.134',$d->preview($source->data_sumber));$this->assertSame('source.draft_updated',RekodAudit::orderByDesc('id')->first()->tindakan);$this->assertDatabaseCount('hasil_pengiraan',0);
    }
    public function test_grouped_umum_invalid_component_and_shared_inputs_roll_back_every_activity(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$good=$this->umumActivity($place,[$this->umumComponent($this->personnelRow($place))]);
        foreach([['kadar'=>'200'],['payment_grade'=>'Other'],['tujuan'=>'Override'],['nama'=>[]],['gred'=>'W1'],['kategori2'=>'Dobi'],['id'=>'bad']] as $change){$bad=$good;$bad['komponen'][0]=array_replace($bad['komponen'][0],$change);$this->postJson($url,$this->payload($bid,$estimate,[$good,$bad]))->assertUnprocessable();$this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('rekod_audit',0);}
        $bad=$good;$bad['komponen'][]=$bad['komponen'][0];$this->postJson($url,$this->payload($bid,$estimate,[$bad]))->assertUnprocessable()->assertJsonValidationErrors('rekod.0.komponen.0.id');
        $bad=$good;$bad['komponen'][0]['gred']='J99';$this->postJson($url,$this->payload($bid,$estimate,[$bad]))->assertUnprocessable()->assertJsonValidationErrors('rekod.0.komponen.0.gred');
        $bad=$good;$bad['tarikh_mula']='2027-01-01';$this->postJson($url,$this->payload($bid,$estimate,[$bad]))->assertUnprocessable()->assertJsonValidationErrors('rekod.0.tarikh_mula');
        $bad=$good;$bad['komponen']=['x'=>$bad['komponen'][0]];$this->postJson($url,$this->payload($bid,$estimate,[$bad]))->assertUnprocessable();
    }
    public function test_grouped_umum_partial_and_component_free_drafts_have_no_invented_subtotal(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$d=new \App\Services\DefinisiUmumBerkumpulan(2028);$this->gridCapture('umum-grouped-empty',$this->get($url)->assertOk());
        $blank=$this->umumActivity($place,[],['tujuan'=>null,'tarikh_mula'=>null,'tarikh_tamat'=>null]);$this->postJson($url,$this->payload($bid,$estimate,[$blank]))->assertOk();$this->assertDatabaseCount('butiran_anggaran',0);
        $row=$this->umumActivity($place,[$this->umumComponent($this->personnelRow($place,['gred'=>null])),$this->umumComponent($this->row($place))]);$this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$source=$estimate->butiran()->sole();$this->assertNull($d->preview($source->data_sumber));$this->gridCapture('umum-grouped-partial',$this->get($url)->assertOk());
        $malformed=clone $source->data_sumber;$malformed->komponen=(object)[];$this->assertFalse($d->readable($malformed));
        $row['komponen']=[];$row+=['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan];$this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$this->assertCount(0,$source->fresh()->data_sumber->komponen);$this->assertNull($d->preview($source->fresh()->data_sumber));
    }
    public function test_umum_v1_rows_reopen_separately_and_grouped_updates_keep_guards_and_audit(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place),$this->row($place)]))->assertOk();$records=$this->get($url)->assertOk()->viewData('records');$this->assertCount(2,$records);foreach($records as $record)$this->assertCount(1,$record['komponen']);
        $source=$estimate->butiran()->orderBy('id')->first();$row=$this->umumActivity($place,[$this->umumComponent($this->personnelRow($place,['bil_unit'=>'3'])),$this->umumComponent($this->row($place,['bil_unit'=>'20']))],['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan]);$payload=$this->payload($bid,$estimate,[$row]);$this->postJson($url,$payload)->assertOk();$this->assertSame($source->id,$source->fresh()->id);$this->postJson($url,$payload)->assertConflict();
        $this->phase->forceFill(['dibuka'=>false])->save();$this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertForbidden();$this->phase->forceFill(['dibuka'=>true])->save();
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PemantauBidaan);$this->gridCapture('umum-grouped-readonly',$this->get($url)->assertOk());$this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertForbidden();
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PenyediaAnggaran);$this->postJson($url,$this->payload($bid,$estimate,[],[['butiran_id'=>$source->id,'semakan_butiran'=>$source->fresh()->semakan]]))->assertOk();$this->assertDatabaseCount('butiran_anggaran',1);$this->assertSame('source.draft_deleted',RekodAudit::orderByDesc('id')->first()->tindakan);
    }
    public function test_grouped_umum_pagination_keeps_components_together_and_rejects_cross_ptj_owner(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$rows=[];for($i=0;$i<26;$i++)$rows[]=$this->umumActivity($place,[$this->umumComponent($this->personnelRow($place)),$this->umumComponent($this->row($place))],['tujuan'=>'Aktiviti '.($i+1)]);
        $this->postJson($url,$this->payload($bid,$estimate,$rows))->assertOk();$response=$this->get($url)->assertOk();$this->assertCount(25,$response->viewData('records'));foreach($response->viewData('records') as $record)$this->assertCount(2,$record['komponen']);$response=$this->get($url.'?page=2')->assertOk();$this->assertCount(1,$response->viewData('records'));$this->assertCount(2,$response->viewData('records')[0]['komponen']);
        $otherPlace=\App\Models\TempatBertugas::create(['ptj_id'=>$this->other->id,'nama'=>'Sektor Perancangan','jenis'=>'sektor']);$this->postJson($url,$this->payload($bid,$estimate,[$this->umumActivity($otherPlace,[$this->umumComponent($this->row($otherPlace))])]))->assertForbidden();$this->assertDatabaseCount('butiran_anggaran',26);
    }

}
