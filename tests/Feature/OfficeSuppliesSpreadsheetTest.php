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

class OfficeSuppliesSpreadsheetTest extends TestCase
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
        $this->os = RujukanOs::create(['kod_objek' => '27000', 'butiran_perbelanjaan' => 'Bekalan Alat Pejabat']);
        $this->unconfigured = RujukanOs::create(['kod_objek' => '27000', 'butiran_perbelanjaan' => 'Pakaian Seragam']);
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
    private function row($place,array $extra=[]): array {return array_replace(['tempat_bertugas_id'=>$place->id,'kategori1'=>'Dakwat/ Toner Mesin Pencetak','kategori2'=>'Canon (Black)','bil_unit'=>'2'],$extra);}
    private function gridCapture(string $name,$response): void
    {
        $this->workspaceCapture($name,$response);
        if(getenv('ABM_UI_CAPTURE')==='1')foreach(['abm-money.js','anggaran-grid.css','anggaran-grid.js'] as $asset)copy(public_path($asset),storage_path('app/ui-qa/'.$asset));
    }
    public function test_proposal_is_private_until_superadmin_approval_and_reprices_the_saved_draft(): void
    {
        [$bid, $estimate, $place] = $this->gridContext();
        $url = route('estimates.item-proposals.store', [$bid->id, $estimate->id]);
        $input = ['kategori' => 'Dakwat/ Toner Mesin Pencetak', 'nama' => 'Toner Cadangan',
            'kadar' => '15.00', 'justifikasi' => 'Bekalan tambahan diperlukan.'];
        $this->get($url)->assertOk()->assertSee('Cadangan Item Baharu');
        $this->postJson($url, array_replace($input, ['nama' => ' canon   (black) ']))->assertUnprocessable();
        $this->post($url, $input)->assertRedirect($this->gridUrl($bid, $estimate));
        $this->postJson($url, $input)->assertUnprocessable();

        $proposal = \App\Models\CadanganItemAnggaran::sole();
        $this->post($url, array_replace($input, ['nama' => 'Toner Ditolak']))->assertRedirect();
        $rejected = \App\Models\CadanganItemAnggaran::where('nama', 'Toner Ditolak')->sole();
        $rejectedKey = 'cadangan:'.$rejected->id;
        $key = 'cadangan:'.$proposal->id;
        $row = $this->row($place, ['kategori2' => $key]);
        $this->postJson($this->gridUrl($bid, $estimate), $this->payload($bid, $estimate, [$row,
            $this->row($place, ['kategori2' => $rejectedKey])]))->assertOk();
        $this->get($this->gridUrl($bid, $estimate))->assertOk()->assertViewHas('records', fn ($rows) =>
            $rows->first()['preview'] === '30')
            ->assertViewHas('completeness', fn ($summary) => $summary['status'] === 'Belum Lengkap'
                && $summary['total'] === '0' && $summary['incomplete'] === 2);

        $decide = route('item-proposals.decide', $proposal->id);
        $decision = ['status' => 'diluluskan', 'kadar' => '17.25', 'catatan' => 'Kadar disahkan.'];
        $this->postJson($decide, $decision)->assertForbidden();

        $reviewer = User::factory()->create();
        $reviewer->forceFill(['is_active' => true, 'ptj_id' => $this->owner->id,
            'google_subject' => 'proposal-reviewer'])->save();
        $reviewer->penugasanPeranan()->create(['peranan' => Peranan::Superadmin]);
        $this->actingAs($reviewer)->withSession(['abm_access_revision' => $reviewer->fresh()->access_revision,
            'abm_authenticated_at' => now()->timestamp, 'abm_last_activity' => now()->timestamp]);
        $this->get(route('item-proposals.index'))->assertOk()->assertSee('Toner Cadangan');
        $this->assertArrayNotHasKey($key, (new \App\Services\DefinisiAlatPejabat)->references()[$input['kategori']]);
        $this->post($decide, $decision)->assertRedirect(route('item-proposals.index'));
        $this->postJson($decide, $decision)->assertConflict();
        $this->get($this->gridUrl($bid, $estimate))->assertOk()->assertViewHas('records', fn ($rows) =>
            $rows->first()['preview'] === '34.5')
            ->assertViewHas('completeness', fn ($summary) => $summary['status'] === 'Belum Lengkap'
                && $summary['total'] === '34.5' && $summary['incomplete'] === 1);
        $this->post(route('item-proposals.decide', $rejected->id), ['status' => 'ditolak',
            'catatan' => 'Gunakan item sedia ada.'])->assertRedirect(route('item-proposals.index'));
        $this->actingAs($this->operator)->withSession(['abm_access_revision' => $this->operator->fresh()->access_revision,
            'abm_authenticated_at' => now()->timestamp, 'abm_last_activity' => now()->timestamp]);
        $this->postJson($this->gridUrl($bid, $estimate), $this->payload($bid, $estimate, [
            $this->row($place, ['kategori2' => $rejectedKey]),
        ]))->assertUnprocessable();
        $source = $estimate->butiran()->orderByDesc('id')->firstOrFail();
        $this->postJson($this->gridUrl($bid, $estimate), $this->payload($bid, $estimate, [], [
            ['butiran_id' => $source->id, 'semakan_butiran' => $source->semakan],
        ]))->assertOk();
        $this->assertDatabaseCount('butiran_anggaran', 1);
        $this->get($this->gridUrl($bid, $estimate))->assertOk()
            ->assertViewHas('completeness', fn ($summary) => $summary['status'] === 'Lengkap'
                && $summary['total'] === '34.5' && $summary['incomplete'] === 0)
            ->assertSee('RM 34.50');
        $this->assertSame('RM 1,955.00', strip_tags(trim(\Illuminate\Support\Facades\Blade::render(
            '<x-abm-money amount="1955" />'
        ))));
        $this->assertDatabaseHas('rekod_audit', ['tindakan' => 'item.reviewed', 'pelaku_id' => $reviewer->id]);
        $this->assertDatabaseCount('hasil_pengiraan', 0);
        $this->assertSame(StatusBidaan::DalamPenyediaan, $bid->fresh()->status);
    }

    public function test_all_194_items_match_original_normalised_names_and_only_the_two_approved_higher_rates(): void
    {
        $d=new \App\Services\DefinisiAlatPejabat;$refs=$d->references();$this->assertCount(4,$refs);
        $this->assertSame([22,156,8,8],array_map('count',array_values($refs)));
        $h=fopen(base_path('docs/references/OS27000-bekalan-alat-pejabat-source.csv'),'rb');fgetcsv($h,0,',','"','');$all=[];$count=0;
        while(($r=fgetcsv($h,0,',','"',''))!==false){$a=preg_replace('/\s+/u',' ',trim($r[0]));$b=preg_replace('/\s+/u',' ',trim($r[1]));$key=$a.'|'.$b;$all[$key]??=['a'=>$a,'b'=>$b,'rates'=>[]];$all[$key]['rates'][]=$r[2];$count++;}fclose($h);
        $this->assertSame(196,$count);$this->assertCount(194,$all);$duplicates=[];
        foreach($all as $item){$this->assertArrayHasKey($item['b'],$refs[$item['a']]);$rates=$item['rates'];if(count($rates)>1)$duplicates[]=$item['b'];usort($rates,fn($a,$b)=>\Brick\Math\BigDecimal::of($b)->compareTo($a));$this->assertSame($rates[0],$refs[$item['a']][$item['b']]['kadar']);}
        $this->assertEqualsCanonicalizing(['Klip Kertas Segitiga Saiz 31MM','Tali Pengikat Rafia Saiz 100 Meter'],$duplicates);
        $this->assertSame('1.20',$refs['Alat Tulis Pejabat']['Klip Kertas Segitiga Saiz 31MM']['kadar']);$this->assertSame('36.00',$refs['Alat Tulis Pejabat']['Tali Pengikat Rafia Saiz 100 Meter']['kadar']);
    }

    public function test_exact_formula_partial_drafts_and_no_invented_unit_or_programme_fields(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$d=new \App\Services\DefinisiAlatPejabat;
        $this->assertSame('280',$d->preview($d->source($d->validate([$this->row($place)])[0])));
        $r=$this->row($place,['kategori1'=>'Alat Tulis Pejabat','kategori2'=>'Klip Kertas Segitiga Saiz 31MM','bil_unit'=>'3600.102875']);$this->assertSame('4320.12345',$d->preview($d->source($r)));
        $this->assertNull($d->preview($d->source($this->row($place,['bil_unit'=>null]))));$this->assertNull($d->preview($d->source($this->row($place,['kategori2'=>null]))));
        $keys=array_column($d->columns(),'key');foreach(['unit','kategori3','tujuan','tarikh_mula','tarikh_tamat','kekerapan','bil_bulan'] as $key)$this->assertNotContains($key,$keys);
    }

    public function test_http_save_reopen_edit_noop_partial_readonly_and_accounting_fixtures(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$this->gridCapture('office-empty',$this->get($url)->assertOk());
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertOk();$source=$estimate->butiran()->sole();$response=$this->get($url)->assertOk();$this->assertSame('280',$response->viewData('records')[0]['preview']);$this->gridCapture('office-populated',$response);
        $saved=(new \App\Services\DefinisiAlatPejabat)->project($source->data_sumber)+['tempat_bertugas_id'=>$place->id,'butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan];$audit=RekodAudit::count();$this->postJson($url,$this->payload($bid,$estimate,[$saved]))->assertOk();$this->assertSame($audit,RekodAudit::count());
        $saved['bil_unit']='2.12345';$this->postJson($url,$this->payload($bid,$estimate,[$saved]))->assertOk();$this->assertSame('2.12345',$source->fresh()->data_sumber->bil_unit);
        $this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['kategori2'=>null,'bil_unit'=>null])]))->assertOk();$response=$this->get($url)->assertOk();$this->assertNull($response->viewData('records')->last()['preview']);$this->gridCapture('office-partial',$response);
        $this->assertDatabaseCount('hasil_pengiraan',0);
        $this->operator->penugasanPeranan()->delete();$this->workspaceRole(Peranan::PemantauBidaan);$response=$this->get($url)->assertOk();$this->assertFalse($response->viewData('editable'));$this->gridCapture('office-readonly',$response);
    }

    public function test_inactive_derived_cross_category_and_malformed_injections_are_atomic(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);
        foreach([['kategori1'=>'Other'],['kategori2'=>'Glossy Paper'],['kategori1'=>null],['kategori1'=>[]],['kategori2'=>[]],['bil_unit'=>[]],['bil_unit'=>'1,000'],['bil_unit'=>'1e2'],['kadar'=>'999'],['unit'=>'Box'],['kategori3'=>'Item'],['tujuan'=>'Program'],['tarikh_mula'=>'2028-01-01'],['tarikh_tamat'=>'2028-02-01'],['kekerapan'=>'3'],['bil_bulan'=>'12'],['preview'=>'100']] as $extra)$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place),$this->row($place,$extra)]))->assertUnprocessable();
        $this->assertDatabaseCount('butiran_anggaran',0);$this->assertDatabaseCount('rekod_audit',0);
    }

    public function test_live_permissions_phase_scope_and_revision_guards(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$data=$this->payload($bid,$estimate,[$this->row($place)]);$this->postJson($url,$data)->assertOk();$this->postJson($url,$data)->assertConflict();
        $source=$estimate->butiran()->sole();$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place,['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan+1])]))->assertConflict();
        $outside=\App\Models\TempatBertugas::create(['ptj_id'=>$this->other->id,'nama'=>'Sektor Perancangan','jenis'=>'sektor']);$this->postJson($url,$this->payload($bid,$estimate,[$this->row($outside)]))->assertForbidden();
        TetapanAksesPtj::where('rujukan_os_id',$this->os->id)->update(['dibenarkan'=>false]);$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertNotFound();
        TetapanAksesPtj::where('rujukan_os_id',$this->os->id)->update(['dibenarkan'=>true]);$this->phase->forceFill(['dibuka'=>false])->save();$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertForbidden();
    }

    public function test_five_exact_activity_mappings_and_separation_from_other_os27000_variants(): void
    {
        $catalogue=app(\App\Services\KatalogDefinisiAnggaran::class);$maps=['Pengurusan Pentadbiran Negeri'=>'010801','Pendidikan Rendah'=>'030219','Pendidikan Awal'=>'030101','Pendidikan Menengah'=>'030301','Pengurusan Asrama'=>'030313'];
        foreach($maps as $name=>$code){$a=AktivitiKewangan::firstOrCreate(['nama'=>$name],['singkatan'=>$name]);$bid=Bidaan::forceCreate(['kitaran_id'=>$this->cycle->id,'ptj_id'=>$this->owner->id,'aktiviti_kewangan_id'=>$a->id]);$k=\App\Models\KodAktiviti::firstOrCreate(['kod_aktiviti'=>$code],['aktiviti_kewangan_id'=>$a->id]);
            PemetaanOs::firstOrCreate(['aktiviti_kewangan_id'=>$a->id,'rujukan_os_id'=>$this->os->id,'kod_aktiviti_id'=>$k->id],['singkatan_sumber'=>$name,'butiran_asal'=>'Bekalan Alat Pejabat','status_sumber'=>'Aktif']);
            $this->assertInstanceOf(\App\Services\DefinisiAlatPejabat::class,$catalogue->untuk($this->os,$bid));
            PemetaanOs::where('aktiviti_kewangan_id',$a->id)->where('rujukan_os_id',$this->os->id)->delete();$this->assertNull($catalogue->untuk($this->os,$bid));
        }
        $d=new \App\Services\DefinisiAlatPejabat;$this->assertFalse($d->supports($this->unconfigured));$this->assertFalse($d->supports(RujukanOs::create(['kod_objek'=>'27000','butiran_perbelanjaan'=>'Bekalan dan Bahan-bahan Lain'])));
    }

    public function test_copy_and_audited_delete_preserve_independent_sources(): void
    {
        [$bid,$estimate,$place]=$this->gridContext();$url=$this->gridUrl($bid,$estimate);$this->postJson($url,$this->payload($bid,$estimate,[$this->row($place)]))->assertOk();$source=$estimate->butiran()->sole();
        $copy=app(\App\Services\DrafButiranAnggaran::class)->salin($estimate->id,$source->id,['semakan_bidaan'=>$bid->fresh()->semakan,'semakan_anggaran'=>$estimate->fresh()->semakan,'semakan_butiran'=>$source->semakan]);$this->assertNotSame($source->id,$copy->id);$this->assertEquals($source->data_sumber,$copy->data_sumber);
        $this->postJson($url,$this->payload($bid,$estimate,[],[['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan]]))->assertOk();$this->assertNull($source->fresh());$this->assertNotNull($copy->fresh());$this->assertSame('source.draft_deleted',RekodAudit::orderByDesc('id')->first()->tindakan);
    }
}
