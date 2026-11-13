<?php

namespace App\Http\Controllers;

use App\Enums\Peranan;
use App\Models\AktivitiKewangan;
use App\Models\Ptj;
use App\Models\RujukanOs;
use App\Models\TempatBertugas;
use Illuminate\Http\Request;

class ReferenceController extends Controller
{
    public function index(Request $request, string $section)
    {
        abort_unless($request->user()->penugasanPeranan()->where('peranan', Peranan::Superadmin->value)->exists(), 403);
        $definitions = [
            'ptj' => ['PTJ', Ptj::query()->orderBy('kod_ptj'), ['Kod PTJ', 'Nama PTJ', 'Kategori', 'Daerah']],
            'aktiviti' => ['Aktiviti Kewangan', AktivitiKewangan::query()->withCount('kodAktiviti')->orderBy('nama'), ['Aktiviti Kewangan', 'Singkatan', 'Bil. Kod Aktiviti']],
            'tempat-bertugas' => ['Tempat Bertugas', TempatBertugas::query()->with('ptj')->orderBy('nama'), ['Tempat Bertugas', 'Jenis', 'PTJ']],
            'os' => ['OS', RujukanOs::query()->withCount('pemetaan')->orderBy('kod_objek')->orderBy('butiran_perbelanjaan'), ['Kod Objek', 'Butiran Perbelanjaan', 'Pemetaan', 'Status Rujukan']],
        ];
        abort_unless(isset($definitions[$section]), 404);
        [$title, $query, $headers] = $definitions[$section];
        $records = $query->paginate(25);
        $rows = $records->getCollection()->map(fn ($record) => match ($section) {
            'ptj' => [$record->kod_ptj, $record->nama, $record->kategori, $record->daerah ?? '—'],
            'aktiviti' => [$record->nama, $record->singkatan ?? '—', $record->kod_aktiviti_count],
            'tempat-bertugas' => [$record->nama, match ($record->jenis) {
                'sektor' => 'Sektor', 'sekolah_non_ptj' => 'Sekolah Bukan PTJ', 'smk_ptj' => 'SMK PTJ', default => $record->jenis,
            }, $record->ptj->nama],
            'os' => [$record->kod_objek, $record->butiran_perbelanjaan, $record->pemetaan_count,
                $record->dikecualikan ? 'Dikecualikan' : 'Rujukan tersedia'],
        });
        return view('abm.references', compact('title', 'section', 'headers', 'records', 'rows'));
    }
}
