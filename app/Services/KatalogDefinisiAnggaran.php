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
        $travel=new DefinisiPerjalanan($bid->kitaran->tahun);
        if($travel->supports($os)){
            if($bid->aktivitiKewangan->nama!=='Pengurusan Pentadbiran Negeri')return null;
            $code=$os->butiran_perbelanjaan==='Perjalanan Sara Hidup (Umum)'?'010801':'010803';
            return PemetaanOs::where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id)->where('rujukan_os_id',$os->id)->whereHas('kodAktiviti',fn($q)=>$q->where('kod_aktiviti',$code)->where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id))->exists()?$travel:null;
        }

        $overtime=new DefinisiLebihMasa($bid->kitaran->tahun);
        if($overtime->supports($os)){
            $code=match($bid->aktivitiKewangan->nama){'Pengurusan Pentadbiran Negeri'=>'010801','Pendidikan Rendah'=>'030219','Pendidikan Menengah'=>'030301','Pengurusan Asrama'=>'030313',default=>null};
            if($code===null)return null;
            if(!PemetaanOs::where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id)->where('rujukan_os_id',$os->id)->whereHas('kodAktiviti',fn($q)=>$q->where('kod_aktiviti',$code)->where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id))->exists())return null;
            $programmes=[];$actor=auth()->user();
            $programmeQuery=app(RuangBidaPeruntukan::class)->admin($actor)
                ?\App\Models\ProgramAktiviti::where('bidaan_id',$bid->id)->where('ptj_id',$bid->ptj_id)
                :app(RuangProgramAktiviti::class)->query($actor,$bid);
            foreach($programmeQuery->orderBy('id')->get(['id','nama','tempat_bertugas_id']) as $p)if(trim($p->nama??'')!=='')$programmes['Program #'.$p->id.' — '.$p->nama]=['place'=>$p->tempat_bertugas_id];
            return new DefinisiLebihMasa($bid->kitaran->tahun,$programmes);
        }

        $benefit=new DefinisiFaedah;
        if($benefit->supports($os)){
            $code=match($bid->aktivitiKewangan->nama){'Pengurusan Pentadbiran Negeri'=>'010801','Pendidikan Menengah'=>'030301','Pengurusan Asrama'=>'030313',default=>null};
            if($code===null)return null;
            return PemetaanOs::where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id)->where('rujukan_os_id',$os->id)
                ->whereHas('kodAktiviti',fn($q)=>$q->where('kod_aktiviti',$code)->where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id))->exists()?$benefit:null;
        }

        $medical=new DefinisiPerubatan($bid->kitaran->tahun);
        if($medical->supports($os)){
            if($bid->aktivitiKewangan->nama!=='Pengurusan Pentadbiran Negeri')return null;
            return PemetaanOs::where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id)->where('rujukan_os_id',$os->id)
                ->whereHas('kodAktiviti',fn($q)=>$q->where('kod_aktiviti','010804')->where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id))->exists()?$medical:null;
        }

        $office=new DefinisiAlatPejabat;
        if($office->supports($os)){
            $code=match($bid->aktivitiKewangan->nama){'Pengurusan Pentadbiran Negeri'=>'010801','Pendidikan Rendah'=>'030219','Pendidikan Awal'=>'030101','Pendidikan Menengah'=>'030301','Pengurusan Asrama'=>'030313',default=>null};
            if($code===null)return null;
            return PemetaanOs::where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id)->where('rujukan_os_id',$os->id)
                ->whereHas('kodAktiviti',fn($q)=>$q->where('kod_aktiviti',$code)->where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id))->exists()?$office:null;
        }

        $raw=new DefinisiBahanMentah($bid->kitaran->tahun);
        if($raw->supports($os)){
            $codes=match($bid->aktivitiKewangan->nama){
                'Pengurusan Pentadbiran Negeri'=>['Bukan Pukal'=>'010801','Pukal'=>'010809'],
                'Pendidikan Rendah'=>['Bukan Pukal'=>'030219','Pukal'=>'030222'],
                'Pendidikan Awal'=>['Bukan Pukal'=>'030101'],
                'Pendidikan Menengah'=>['Bukan Pukal'=>'030301','Pukal'=>'030304'],
                'Pengurusan Asrama'=>['Bukan Pukal'=>'030313','Pukal'=>'030316'],default=>[],
            };
            $allowed=[];
            foreach($codes as $path=>$code)if(PemetaanOs::where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id)->where('rujukan_os_id',$os->id)
                ->whereHas('kodAktiviti',fn($q)=>$q->where('kod_aktiviti',$code)->where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id))->exists())$allowed[]=$path;
            return $allowed?new DefinisiBahanMentah($bid->kitaran->tahun,$allowed):null;
        }

        $goods=new DefinisiPengangkutan;
        if($goods->supports($os)){
            $codes=match($bid->aktivitiKewangan->nama){'Pengurusan Pentadbiran Negeri'=>['010801'],'Pendidikan Rendah'=>['030220'],'Pendidikan Awal'=>['030102'],'Pendidikan Menengah'=>['030301','030302'],'Pengurusan Asrama'=>['030313','030314'],default=>[]};
            if(!$codes)return null;
            return PemetaanOs::where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id)->where('rujukan_os_id',$os->id)->whereHas('kodAktiviti',fn($q)=>$q->whereIn('kod_aktiviti',$codes)->where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id))->exists()?$goods:null;
        }

        $transfer=new DefinisiPertukaran;
        if($transfer->supports($os)){
            $code=match($bid->aktivitiKewangan->nama){'Pengurusan Pentadbiran Negeri'=>'010805','Pendidikan Rendah'=>'030220','Pendidikan Awal'=>'030102','Pendidikan Menengah'=>'030302','Pengurusan Asrama'=>'030314',default=>null};
            if($code===null)return null;
            return PemetaanOs::where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id)->where('rujukan_os_id',$os->id)->whereHas('kodAktiviti',fn($q)=>$q->where('kod_aktiviti',$code)->where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id))->exists()?$transfer:null;
        }

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

        $photocopier=new DefinisiSewaanFotostat($bid->kitaran->tahun);
        if($photocopier->supports($os)){
            $code=match($bid->aktivitiKewangan->nama){'Pengurusan Pentadbiran Negeri'=>'010807','Pendidikan Rendah'=>'030221','Pendidikan Menengah'=>'030303','Pengurusan Asrama'=>'030315',default=>null};
            if($code===null)return null;
            return PemetaanOs::where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id)->where('rujukan_os_id',$os->id)->whereHas('kodAktiviti',fn($q)=>$q->where('kod_aktiviti',$code)->where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id))->exists()?$photocopier:null;
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
