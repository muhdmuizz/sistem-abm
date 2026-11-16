<?php

namespace App\Services;

use App\Models\Bidaan;
use App\Models\PemetaanOs;
use App\Models\RujukanOs;

/** Definition selection is context-aware; it never grants financial access. */
class KatalogDefinisiAnggaran
{
    public function untuk(RujukanOs $os, Bidaan $bid): ?DefinisiAnggaran
    {
        $mystep=new DefinisiMystep($bid->kitaran->tahun);
        if($mystep->supports($os)) {
            $codes=match($bid->aktivitiKewangan->nama){
                'Pengurusan Pentadbiran Negeri'=>['MySTEP'=>'010812'],
                'Pendidikan Rendah'=>['MySTEP - Guru Ganti'=>'030223','MySTEP - Pembantu Pengurusan Murid (PPM) Ganti'=>'030226'],
                'Pendidikan Awal'=>['MySTEP - Guru Ganti'=>'030106','MySTEP - Pembantu Pengurusan Murid (PPM) Ganti'=>'030105'],
                'Pendidikan Menengah'=>['MySTEP - Guru Ganti'=>'030305','MySTEP - Pembantu Pengurusan Murid (PPM) Ganti'=>'030309'],default=>[],
            };
            $code=$codes[$os->butiran_perbelanjaan] ?? null;if($code===null)return null;
            $mapped=PemetaanOs::where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id)->where('rujukan_os_id',$os->id)
                ->whereHas('kodAktiviti',fn($query)=>$query->where('kod_aktiviti',$code)->where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id))->exists();
            return $mapped?$mystep:null;
        }

        $pakej=new DefinisiPakejBerkumpulan($bid->kitaran->tahun);
        if($pakej->supports($os)) {
            $code=match($bid->aktivitiKewangan->nama){
                'Pengurusan Pentadbiran Negeri'=>'010810','Pendidikan Rendah'=>'030225','Pendidikan Awal'=>'030104',
                'Pendidikan Menengah'=>'030307','Pengurusan Asrama'=>'030317',default=>null,
            };
            if($code===null)return null;
            $mapped=PemetaanOs::where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id)->where('rujukan_os_id',$os->id)
                ->whereHas('kodAktiviti',fn($query)=>$query->where('kod_aktiviti',$code)->where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id))->exists();
            return $mapped?$pakej:null;
        }

        $umum=new DefinisiUmumBerkumpulan($bid->kitaran->tahun);
        if($umum->supports($os)){
            $code=match($bid->aktivitiKewangan->nama){'Pengurusan Pentadbiran Negeri'=>'010801','Pendidikan Rendah'=>'030219','Pendidikan Awal'=>'030101','Pendidikan Menengah'=>'030301','Pengurusan Asrama'=>'030313',default=>null};
            if($code===null)return null;
            $mapped=PemetaanOs::where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id)->where('rujukan_os_id',$os->id)
                ->whereHas('kodAktiviti',fn($q)=>$q->where('kod_aktiviti',$code)->where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id))->exists();
            return $mapped?$umum:null;
        }

        $meter=new DefinisiBacaanMeter($bid->kitaran->tahun);
        if($meter->supports($os)) {
            $code=match($bid->aktivitiKewangan->nama){
                'Pengurusan Pentadbiran Negeri'=>'010807','Pendidikan Rendah'=>'030221',
                'Pendidikan Menengah'=>'030303','Pengurusan Asrama'=>'030315',default=>null,
            };
            if($code===null)return null;
            $mapped=PemetaanOs::where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id)->where('rujukan_os_id',$os->id)
                ->whereHas('kodAktiviti',fn($q)=>$q->where('kod_aktiviti',$code)->where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id))->exists();
            return $mapped?$meter:null;
        }

        $rental=new DefinisiSewaanUmum($bid->kitaran->tahun);
        if($rental->supports($os)){
            $code=match($bid->aktivitiKewangan->nama){'Pengurusan Pentadbiran Negeri'=>'010801','Pendidikan Rendah'=>'030219','Pendidikan Awal'=>'030101','Pendidikan Menengah'=>'030301','Pengurusan Asrama'=>'030313',default=>null};
            if($code===null)return null;
            $mapped=PemetaanOs::where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id)->where('rujukan_os_id',$os->id)
                ->whereHas('kodAktiviti',fn($q)=>$q->where('kod_aktiviti',$code)->where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id))->exists();
            return $mapped?$rental:null;
        }

        $food = app(DefinisiAnggaran::class);
        if ($food->supports($os)) { return $food; }

        $maintenance=app(DefinisiPenyelenggaraan::class);
        if($maintenance->supports($os)) { return $maintenance; }

        $utilities=app(DefinisiPerhubunganUtiliti::class);
        if($utilities->supports($os)) {
            $code=match($bid->aktivitiKewangan->nama){
                'Pengurusan Pentadbiran Negeri'=>'010801','Pendidikan Rendah'=>'030219','Pendidikan Awal'=>'030101',
                'Pendidikan Menengah'=>'030301','Pengurusan Asrama'=>'030313',default=>null,
            };
            if($code===null)return null;
            $mapped=PemetaanOs::where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id)->where('rujukan_os_id',$os->id)
                ->whereHas('kodAktiviti',fn($query)=>$query->where('kod_aktiviti',$code)->where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id))->exists();
            return $mapped?$utilities:null;
        }

        $uniform=app(DefinisiPakaianSeragam::class);
        if($uniform->supports($os)) {
            if($bid->aktivitiKewangan->nama!=='Pengurusan Pentadbiran Negeri')return null;
            $mapped=PemetaanOs::where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id)->where('rujukan_os_id',$os->id)
                ->whereHas('kodAktiviti',fn($query)=>$query->where('kod_aktiviti','010808')->where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id))->exists();
            return $mapped?$uniform:null;
        }

        $touch=app(DefinisiTouchNgo::class);
        if($touch->supports($os)) {
            $code=match($bid->aktivitiKewangan->nama){
                'Pengurusan Pentadbiran Negeri'=>'010809','Pendidikan Menengah'=>'030304','Pengurusan Asrama'=>'030316',default=>null,
            };
            if($code===null)return null;
            $mapped=PemetaanOs::where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id)->where('rujukan_os_id',$os->id)
                ->whereHas('kodAktiviti',fn($query)=>$query->where('kod_aktiviti',$code)->where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id))->exists();
            return $mapped?$touch:null;
        }

        $taska = app(DefinisiTaska::class);
        if ($taska->supports($os)) {
            if ($bid->aktivitiKewangan->nama !== 'Pengurusan Pentadbiran Negeri') { return null; }
            $mapped = PemetaanOs::where('aktiviti_kewangan_id', $bid->aktiviti_kewangan_id)
                ->where('rujukan_os_id', $os->id)
                ->whereHas('kodAktiviti', fn ($query) => $query->where('kod_aktiviti', '010102')
                    ->where('aktiviti_kewangan_id', $bid->aktiviti_kewangan_id))->exists();
            return $mapped ? $taska : null;
        }

        $sbk = app(DefinisiUtilitiSbk::class);
        if (! $sbk->supports($os)) { return null; }
        $code = match ($bid->aktivitiKewangan->nama) {
            'Pendidikan Rendah' => '030228',
            'Pendidikan Menengah' => '030310',
            default => null,
        };
        if ($code === null) { return null; }
        $mapped = PemetaanOs::where('aktiviti_kewangan_id', $bid->aktiviti_kewangan_id)
            ->where('rujukan_os_id', $os->id)
            ->whereHas('kodAktiviti', fn ($query) => $query->where('kod_aktiviti', $code)
                ->where('aktiviti_kewangan_id', $bid->aktiviti_kewangan_id))->exists();
        return $mapped ? $sbk : null;
    }
}
