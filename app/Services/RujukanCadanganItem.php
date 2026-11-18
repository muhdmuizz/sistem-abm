<?php

namespace App\Services;

use App\Models\CadanganItemAnggaran;

class RujukanCadanganItem
{
    public static function normalise(string $name): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $name)));
    }

    public function tambah(array $references, string $os, ?int $ptj): array
    {
        if ($references === []) {
            return $references;
        }

        $items = CadanganItemAnggaran::where('kod_os', $os)
            ->where(function ($query) use ($ptj) {
                $query->where('status', 'diluluskan');
                if (auth()->id() !== null) {
                    $query->orWhere(fn ($own) => $own->where('status', 'menunggu')->where('pemohon_id', auth()->id()));
                }
                if ($ptj !== null) {
                    $query->orWhere(fn ($rejected) => $rejected->where('status', 'ditolak')->where('ptj_id', $ptj));
                }
            })->orderBy('id')->get();

        foreach ($items as $item) {
            if (! isset($references[$item->kategori])) {
                continue;
            }

            $key = 'cadangan:'.$item->id;
            $approved = $item->status === 'diluluskan';
            $rejected = $item->status === 'ditolak';
            $reference = [
                'label' => $item->nama.($approved ? '' : ' — '.CadanganItemAnggaran::STATUS[$item->status]),
                'kadar' => $rejected ? null : ($approved ? $item->kadar_diluluskan : $item->kadar_cadangan),
                'rejected' => $rejected,
                'provisional' => $item->status === 'menunggu',
                'unit' => $item->unit,
                'quantity_label' => 'Bil. '.$item->unit,
                'basis' => $approved ? 'Rujukan diluluskan' : CadanganItemAnggaran::STATUS[$item->status],
                'factors' => ['bil_unit', 'kadar'],
            ];
            $references[$item->kategori][$key] = $reference;
        }

        return $references;
    }
}
