<?php

namespace App\Enums;

enum Peranan: string
{
    case Superadmin = 'superadmin';
    case KetuaBidaan = 'ketua_bidaan';
    case PenyeliaBidaan = 'penyelia_bidaan';
    case PenyediaBidaan = 'penyedia_bidaan';
    case PenyediaAnggaran = 'penyedia_anggaran';
    case PemantauBidaan = 'pemantau_bidaan';
}
