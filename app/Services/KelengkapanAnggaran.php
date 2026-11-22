<?php

namespace App\Services;

use Brick\Math\BigDecimal;

class KelengkapanAnggaran
{
    public function ringkasan(
        DefinisiBahanMentah|DefinisiAlatPejabat $definition,
        iterable $sources,
        array $references,
        array $visibleIds,
    ): array {
        $total = BigDecimal::zero();
        $count = 0;
        $incomplete = 0;
        $issues = [];
        $visible = array_fill_keys($visibleIds, true);

        foreach ($sources as $source) {
            $count++;
            [$amount, $issue] = $this->semak($definition, $source->data_sumber, $references);
            if ($issue !== null) {
                $incomplete++;
                if (isset($visible[$source->id])) {
                    $issues[$source->id] = $issue;
                }
                continue;
            }

            $total = $total->plus($amount);
        }

        $amount = (string) $total;

        return [
            'status' => match (true) {
                $count === 0 => 'Belum Mula',
                $incomplete > 0 => 'Belum Lengkap',
                default => 'Lengkap',
            },
            'total' => str_contains($amount, '.') ? rtrim(rtrim($amount, '0'), '.') : $amount,
            'incomplete' => $incomplete,
            'issues' => $issues,
        ];
    }

    private function semak(
        DefinisiBahanMentah|DefinisiAlatPejabat $definition,
        mixed $source,
        array $references,
    ): array {
        if (! $definition->readable($source, $references)) {
            return [null, 'Semak butiran tersimpan dan pilihan rujukan.'];
        }

        $reference = $references[$source->kategori1 ?? ''][$source->kategori2 ?? ''] ?? [];
        if ($definition instanceof DefinisiBahanMentah) {
            if ($source->kategori1 === 'Pukal') {
                return [null, 'Semakan ini belum meliputi butiran Pukal.'];
            }
            $reference = $reference[$source->kategori3 ?? ''] ?? [];
        }

        if ($reference['rejected'] ?? false) {
            return [null, 'Gantikan item yang ditolak.'];
        }
        if ($reference['provisional'] ?? false) {
            return [null, 'Cadangan item masih menunggu keputusan.'];
        }
        if ($reference === []) {
            return [null, 'Lengkapkan pilihan kategori dan item.'];
        }
        if (($reference['kadar'] ?? null) === null) {
            return [null, 'Kadar rujukan belum tersedia.'];
        }
        if ($definition instanceof DefinisiBahanMentah && trim($source->tujuan ?? '') === '') {
            return [null, 'Isi Nama Program/ Aktiviti/ Tujuan.'];
        }

        $amount = $definition->preview($source, $references);

        return $amount === null ? [null, 'Isi Bil. Unit.'] : [$amount, null];
    }
}
