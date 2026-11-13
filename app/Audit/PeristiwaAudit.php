<?php

namespace App\Audit;

/** Server-constructed metadata and explicit changes only; never pass raw request/model data. */
final readonly class PeristiwaAudit
{
    public function __construct(
        public string $tindakan,
        public string $jenisEntiti,
        public string $idEntiti,
        public array $sebelum = [],
        public array $selepas = [],
        public ?string $alasan = null,
        public bool $wajibAlasan = false,
        public ?int $ptjId = null,
        public ?int $aktivitiId = null,
        public ?int $tempatBertugasId = null,
        public ?int $kitaranId = null,
        public ?string $fasaRujukan = null,
        public ?int $versiPenghantaran = null,
    ) {}
}
