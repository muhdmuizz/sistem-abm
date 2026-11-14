<?php

namespace App\Enums;

enum StatusBidaan: string
{
    case DalamPenyediaan = 'dalam_penyediaan';
    case DalamSemakan = 'dalam_semakan';
    case Dikembalikan = 'dikembalikan';
    case Muktamad = 'muktamad';
}
