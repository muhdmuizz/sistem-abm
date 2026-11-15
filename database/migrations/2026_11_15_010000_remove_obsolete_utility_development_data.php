<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Owner-authorised development cleanup: only the obsolete OS23000 identity.
        DB::transaction(function () {
            $old=DB::table('rujukan_os')->where('kod_objek','23000')->where('butiran_perbelanjaan','Utiliti Pukal')->first();
            if(!$old)return;
            $current=DB::table('rujukan_os')->where('kod_objek','23000')->where('butiran_perbelanjaan','Perhubungan dan Utiliti')->first();
            $currentId=$current?->id ?? DB::table('rujukan_os')->insertGetId(['kod_objek'=>'23000','butiran_perbelanjaan'=>'Perhubungan dan Utiliti','dikecualikan'=>false,'created_at'=>now(),'updated_at'=>now()]);
            $estimates=DB::table('anggaran')->where('rujukan_os_id',$old->id)->pluck('id');
            $bids=DB::table('anggaran')->whereIn('id',$estimates)->pluck('bidaan_id')->unique();
            $sources=DB::table('butiran_anggaran')->whereIn('anggaran_id',$estimates)->pluck('id');
            DB::table('hasil_pengiraan')->whereIn('butiran_anggaran_id',$sources)->delete();
            DB::table('butiran_anggaran')->whereIn('id',$sources)->delete();
            DB::table('anggaran')->whereIn('id',$estimates)->delete();
            DB::table('bidaan')->whereIn('id',$bids)->increment('semakan',1,['updated_at'=>now()]);
            DB::table('tetapan_akses_ptj')->where('rujukan_os_id',$old->id)->delete();
            foreach(DB::table('pemetaan_os')->where('rujukan_os_id',$old->id)->get() as $mapping){
                $exists=DB::table('pemetaan_os')->where('rujukan_os_id',$currentId)->where('aktiviti_kewangan_id',$mapping->aktiviti_kewangan_id)->where('kod_aktiviti_id',$mapping->kod_aktiviti_id)->exists();
                if($exists)DB::table('pemetaan_os')->where('id',$mapping->id)->delete();
                else DB::table('pemetaan_os')->where('id',$mapping->id)->update(['rujukan_os_id'=>$currentId,'updated_at'=>now()]);
            }
            DB::table('rujukan_os')->where('id',$old->id)->delete();
        });
    }

    public function down(): void
    {
        // Deleted development inputs are not recreated or fabricated on rollback.
    }
};
