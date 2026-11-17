<?php

namespace Tests\Feature;

use App\Models\AktivitiKewangan;
use App\Models\Anggaran;
use App\Models\Bidaan;
use App\Models\ButiranAnggaran;
use App\Models\Kitaran;
use App\Models\Ptj;
use App\Models\RujukanOs;
use Database\Seeders\OrganisationalReferenceSeeder;
use Database\Seeders\OsReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ObsoleteUtilityCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_cleanup_discards_only_old_test_budgets_and_cannot_be_reintroduced_by_reference_import(): void
    {
        $this->seed(OrganisationalReferenceSeeder::class);$this->seed(OsReferenceSeeder::class);
        $current=RujukanOs::where('kod_objek','23000')->sole();$currentId=$current->id;
        $old=RujukanOs::create(['kod_objek'=>'23000','butiran_perbelanjaan'=>'Utiliti Pukal']);
        $mapping=DB::table('pemetaan_os')->where('rujukan_os_id',$currentId)->first();
        DB::table('pemetaan_os')->insert(['aktiviti_kewangan_id'=>$mapping->aktiviti_kewangan_id,'kod_aktiviti_id'=>$mapping->kod_aktiviti_id,'rujukan_os_id'=>$old->id,'singkatan_sumber'=>'PPN','butiran_asal'=>'Utiliti Pukal','status_sumber'=>'Eligible reference','created_at'=>now(),'updated_at'=>now()]);
        $ptj=Ptj::firstOrFail();$cycle=Kitaran::create(['tahun'=>2028]);
        $bid=Bidaan::forceCreate(['ptj_id'=>$ptj->id,'kitaran_id'=>$cycle->id,'aktiviti_kewangan_id'=>$mapping->aktiviti_kewangan_id,'diaktifkan'=>true,'semakan'=>1]);
        $place=\App\Models\TempatBertugas::create(['ptj_id'=>$ptj->id,'nama'=>'Sektor Pengurusan','jenis'=>'sektor']);
        $keep=Anggaran::forceCreate(['bidaan_id'=>$bid->id,'ptj_id'=>$ptj->id,'rujukan_os_id'=>$currentId,'diaktifkan'=>true,'semakan'=>1]);
        $remove=Anggaran::forceCreate(['bidaan_id'=>$bid->id,'ptj_id'=>$ptj->id,'rujukan_os_id'=>$old->id,'diaktifkan'=>true,'semakan'=>1]);
        foreach([$keep,$remove] as $estimate){$source=ButiranAnggaran::forceCreate(['anggaran_id'=>$estimate->id,'ptj_id'=>$ptj->id,'tempat_bertugas_id'=>$place->id,'semakan'=>0,'data_sumber'=>(object)['test'=>'input']]);DB::table('hasil_pengiraan')->insert(['butiran_anggaran_id'=>$source->id,'semakan_sumber'=>0,'turutan'=>1,'data_output'=>'{}','amaun'=>'4320','created_at'=>now(),'updated_at'=>now()]);}
        DB::table('tetapan_akses_ptj')->insert(['ptj_id'=>$ptj->id,'aktiviti_kewangan_id'=>$mapping->aktiviti_kewangan_id,'rujukan_os_id'=>$old->id,'dibenarkan'=>true,'created_at'=>now(),'updated_at'=>now()]);
        $migration=require database_path('migrations/2026_11_15_010000_remove_obsolete_utility_development_data.php');$migration->up();$migration->up();
        $this->assertDatabaseMissing('rujukan_os',['id'=>$old->id]);$this->assertDatabaseMissing('anggaran',['id'=>$remove->id]);
        $this->assertDatabaseCount('butiran_anggaran',1);$this->assertDatabaseCount('hasil_pengiraan',1);$this->assertSame($keep->id,ButiranAnggaran::sole()->anggaran_id);
        $this->assertSame(2,$bid->fresh()->semakan);$this->assertSame(1,$keep->fresh()->semakan);
        $this->assertDatabaseMissing('tetapan_akses_ptj',['rujukan_os_id'=>$old->id]);
        $this->seed(OsReferenceSeeder::class);$this->assertDatabaseCount('rujukan_os',30);$this->assertDatabaseCount('pemetaan_os',105);
        $this->assertSame($currentId,RujukanOs::where('kod_objek','23000')->sole()->id);$this->assertSame(8,$current->pemetaan()->count());
    }

    public function test_missing_current_mapping_is_repointed_without_granting_old_permissions(): void
    {
        $this->seed(OrganisationalReferenceSeeder::class);$this->seed(OsReferenceSeeder::class);$current=RujukanOs::where('kod_objek','23000')->sole();
        $old=RujukanOs::create(['kod_objek'=>'23000','butiran_perbelanjaan'=>'Utiliti Pukal']);
        $mapping=\App\Models\PemetaanOs::where('rujukan_os_id',$current->id)->firstOrFail();$mapping->update(['rujukan_os_id'=>$old->id]);
        $permission=DB::table('tetapan_akses_ptj')->insertGetId(['kategori_ptj'=>'JPNT','aktiviti_kewangan_id'=>$mapping->aktiviti_kewangan_id,'rujukan_os_id'=>$current->id,'dibenarkan'=>false,'created_at'=>now(),'updated_at'=>now()]);
        $migration=require database_path('migrations/2026_11_15_010000_remove_obsolete_utility_development_data.php');$migration->up();
        $this->assertSame($current->id,$mapping->fresh()->rujukan_os_id);$this->assertFalse((bool)DB::table('tetapan_akses_ptj')->where('id',$permission)->value('dibenarkan'));
        $this->assertDatabaseCount('rujukan_os',30);$this->assertDatabaseCount('pemetaan_os',105);
    }
}
