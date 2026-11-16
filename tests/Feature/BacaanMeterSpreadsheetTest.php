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

class BacaanMeterSpreadsheetTest extends TestCase
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
        $this->os = RujukanOs::create(['kod_objek' => '29000', 'butiran_perbelanjaan' => 'Bacaan Meter Mesin Fotostat']);
        $this->unconfigured = RujukanOs::create(['kod_objek' => '29000', 'butiran_perbelanjaan' => 'Pakej (Mesyuarat/ Bengkel/ Kursus)']);
        $code=\App\Models\KodAktiviti::create(['aktiviti_kewangan_id'=>$this->activity->id,'kod_aktiviti'=>'010807']);
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
    private function row($place,array $extra=[]): array {return array_replace(['tempat_bertugas_id'=>$place->id,'kategori1'=>'Berwarna','tarikh_mula'=>'2028-01-01','tarikh_tamat'=>'2028-12-31','tarikh_mula_kontrak'=>'2026-02-01','tarikh_tamat_kontrak'=>'2030-01-31','bil_unit'=>'12000','lokasi'=>'Bilik Cetak','kadar'=>'0.03','bil_bulan'=>'12'],$extra);}
    private function gridCapture(string $name,$response): void
    {
        $this->workspaceCapture($name,$response);
        if(getenv('ABM_UI_CAPTURE')==='1')foreach(['abm-money.js','anggaran-grid.css','anggaran-grid.js'] as $asset)copy(public_path($asset),storage_path('app/ui-qa/'.$asset));
    }
    public function test_shared_print_choices_labels_and_independent_contract_fields(): void
    {
        [$bid,$estimate]=$this->gridContext();$response=$this->get($this->gridUrl($bid,$estimate))->assertOk();$columns=collect($response->viewData('columns'))->keyBy('key');
        $this->assertSame(['Berwarna','Hitam dan Putih'],array_keys($response->viewData('references')));
        $this->assertSame('Bil. Salinan (Sebulan)',$columns['bil_unit']['label']);$this->assertSame('Lokasi Mesin Fotostat',$columns['lokasi']['label']);
        $this->assertSame('Kadar (RM/ Salinan)',$columns['kadar']['label']);$this->assertSame('decimal',$columns['kadar']['type']);$this->assertTrue($columns['kadar']['money']);
        $this->assertSame('2028-01-01',$columns['tarikh_mula']['min']);$this->assertSame('2028-12-31',$columns['tarikh_tamat']['max']);
        $this->assertSame('0001-01-01',$columns['tarikh_mula_kontrak']['min']);$this->assertSame('9999-12-31',$columns['tarikh_tamat_kontrak']['max']);
        foreach(['nama','tujuan','jawatan','gred','kategori2','kategori3','unit'] as $key)$this->assertFalse($columns->has($key));
        $this->gridCapture('meter-empty',$response);$this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('rekod_audit',0);
    }

    public function test_monthly_copies_rates_and_independent_months_save_reopen_edit_exactly(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$definition=new \App\Services\DefinisiBacaanMeter(2028);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place),$this->row($place,['kategori1'=>'Hitam dan Putih','kadar'=>'0.012345','bil_unit'=>'12345.6','bil_bulan'=>'7'])]))->assertOk();
        $sources=$estimate->butiran()->orderBy('id')->get();$first=$sources[0];
        $this->assertSame('4320',$definition->preview($first->data_sumber));$this->assertSame('1066.845024',$definition->preview($sources[1]->data_sumber));
        $projection=$definition->project($first->data_sumber);$this->assertSame('1 tahun, 0 bulan, 0 hari',$projection['tempoh']);$this->assertSame('4 tahun, 0 bulan, 0 hari',$projection['tempoh_kontrak']);
        $this->gridCapture('meter-populated',$this->get($url)->assertOk());
        $this->assertSame('0.012345',$sources[1]->data_sumber->kadar);
        $row=$this->row($place,['butiran_id'=>$first->id,'semakan_butiran'=>$first->semakan,'bil_bulan'=>'5','tarikh_mula_kontrak'=>'2025-01-01','tarikh_tamat_kontrak'=>'2035-12-31']);
        $this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$fresh=$first->fresh();$this->assertSame('1800',$definition->preview($fresh->data_sumber));$this->assertSame('5',$fresh->data_sumber->bil_bulan);
        $row['semakan_butiran']=$fresh->semakan;$count=RekodAudit::count();$this->postJson($url,$this->payload($bid,$estimate,[$row]))->assertOk();$this->assertSame($count,RekodAudit::count());
        $this->assertDatabaseCount('hasil_pengiraan',0);
        $this->assertEqualsCanonicalizing(['definition','kategori1','tarikh_mula','tarikh_tamat','tarikh_mula_kontrak','tarikh_tamat_kontrak','bil_unit','lokasi','kadar','bil_bulan'],array_keys(get_object_vars($fresh->data_sumber)));
        $this->assertTrue($definition->readable($fresh->data_sumber));$bad=clone $fresh->data_sumber;$bad->tempoh='injected';$this->assertFalse($definition->readable($bad));
    }

    public function test_only_four_existing_meter_mappings_are_supported_and_rental_stays_separate(): void
    {
        [$bid,$estimate]=$this->gridContext();$code=\App\Models\KodAktiviti::where('aktiviti_kewangan_id',$this->activity->id)->sole();
        foreach([['Pengurusan Pentadbiran Negeri','010807'],['Pendidikan Rendah','030221'],['Pendidikan Menengah','030303'],['Pengurusan Asrama','030315']] as [$activity,$value]){
            $this->activity->update(['nama'=>$activity]);$code->update(['kod_aktiviti'=>$value]);
            $this->get($this->gridUrl($bid,$estimate))->assertOk();$this->assertInstanceOf(\App\Services\DefinisiBacaanMeter::class,app(\App\Services\KatalogDefinisiAnggaran::class)->untuk($this->os,$bid->fresh()));
            $code->update(['kod_aktiviti'=>'999999']);$this->get($this->gridUrl($bid,$estimate))->assertNotFound();
        }
        $this->activity->update(['nama'=>'Pendidikan Awal']);$this->get($this->gridUrl($bid,$estimate))->assertNotFound();
        $definition=new \App\Services\DefinisiBacaanMeter(2028);
        foreach([['24000','Sewaan Mesin Fotostat dan Riso'],['29000','Perkhidmatan Iktisas dan Perkhidmatan Lain Yang Dibeli dan Hospitaliti']] as [$object,$label])$this->assertFalse($definition->supports(new RujukanOs(['kod_objek'=>$object,'butiran_perbelanjaan'=>$label])));
        $this->os->dikecualikan=true;$this->assertFalse($definition->supports($this->os));
    }

    public function test_invalid_dates_inactive_fields_and_derived_injections_reject_the_entire_save(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        foreach([['kategori1'=>'Tidak Berwarna'],['kategori2'=>'Other'],['unit'=>'Salinan'],['tempoh'=>'12'],['tempoh_kontrak'=>'4 tahun'],['preview'=>'4320'],['nama'=>'Someone'],['tarikh_mula'=>'2027-12-31'],['tarikh_tamat'=>'2029-01-01'],['tarikh_mula'=>'2028-02-30'],['tarikh_tamat'=>'2028-01-00'],['tarikh_mula_kontrak'=>'2027-02-29'],['tarikh_mula_kontrak'=>'0000-01-01'],['tarikh_tamat_kontrak'=>'2025-01-01'],['bil_unit'=>'12,000'],['kadar'=>'0,03'],['bil_bulan'=>'1e2'],['lokasi'=>str_repeat('x',1001)]] as $extra){
            $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place),$this->row($place,$extra)]))->assertUnprocessable();
            $this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('rekod_audit',0);
        }
    }

    public function test_calendar_durations_are_inclusive_independent_and_cover_leap_and_month_end_boundaries(): void
    {
        $definition=new \App\Services\DefinisiBacaanMeter(2028);
        foreach([
            ['2028-02-29','2028-02-29','0 tahun, 0 bulan, 1 hari'],
            ['2028-01-01','2028-12-31','1 tahun, 0 bulan, 0 hari'],
            ['2027-01-01','2027-12-31','1 tahun, 0 bulan, 0 hari'],
            ['2028-02-01','2028-02-29','0 tahun, 1 bulan, 0 hari'],
            ['2027-02-01','2027-02-28','0 tahun, 1 bulan, 0 hari'],
            ['2028-01-31','2028-02-28','0 tahun, 1 bulan, 0 hari'],
            ['2028-01-31','2028-02-29','0 tahun, 1 bulan, 1 hari'],
            ['2028-02-29','2029-02-27','1 tahun, 0 bulan, 0 hari'],
            ['2028-02-29','2029-02-28','1 tahun, 0 bulan, 1 hari'],
            ['2028-12-31','2029-01-01','0 tahun, 0 bulan, 2 hari'],
            ['2026-02-01','2030-01-31','4 tahun, 0 bulan, 0 hari'],
            ['9999-12-31','9999-12-31','0 tahun, 0 bulan, 1 hari'],
        ] as [$start,$end,$expected])$this->assertSame($expected,$definition->duration($start,$end),$start.' / '.$end);
        foreach([[null,null],['2028-02-30','2028-03-01'],['2028-03-01','2028-02-29']] as [$start,$end])$this->assertNull($definition->duration($start,$end));
    }

    public function test_partial_drafts_have_no_assumed_rate_months_or_contract_duration(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$definition=new \App\Services\DefinisiBacaanMeter(2028);
        $keys=['kategori1','tarikh_mula','tarikh_tamat','tarikh_mula_kontrak','tarikh_tamat_kontrak','bil_unit','lokasi','kadar','bil_bulan'];
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,array_fill_keys($keys,null))]))->assertOk();$this->assertDatabaseCount('butiran_anggaran',0);
        foreach(['kategori1','bil_unit','kadar','bil_bulan'] as $missing)$this->assertNull($definition->preview($definition->source($this->row($place,[$missing=>null]))));
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['kadar'=>null,'bil_bulan'=>null,'tarikh_tamat'=>null,'tarikh_tamat_kontrak'=>null])]))->assertOk();
        $source=$estimate->butiran()->sole()->data_sumber;$this->assertNull($source->kadar);$this->assertNull($source->bil_bulan);
        $projection=$definition->project($source);$this->assertNull($projection['tempoh']);$this->assertNull($projection['tempoh_kontrak']);
        $this->gridCapture('meter-partial',$this->get($url)->assertOk());
        $this->assertSame('0',$definition->preview($definition->source($this->row($place,['kadar'=>'0']))));
    }

    public function test_live_revision_fasa_role_and_cross_ptj_guards_and_audited_removal(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$data=$this->payload($bid,$estimate,[$this->row($place)]);
        $this->postJson($url,$data)->assertOk();$this->postJson($url,$data)->assertConflict();$source=$estimate->butiran()->sole();
        $this->phase->forceFill(['dibuka'=>false])->save();$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertForbidden();$this->phase->forceFill(['dibuka'=>true])->save();
        $other=\App\Models\TempatBertugas::create(['ptj_id'=>$this->other->id,'nama'=>'Sektor Perancangan','jenis'=>'sektor']);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($other)]))->assertForbidden();
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PemantauBidaan);$this->gridCapture('meter-readonly',$this->get($url)->assertOk());
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertForbidden();
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PenyediaAnggaran);
        $this->postJson($url,$this->payload($bid,$estimate,[],[['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan]]))->assertOk();
        $this->assertSame('source.draft_deleted',RekodAudit::orderByDesc('id')->first()->tindakan);
    }
}
