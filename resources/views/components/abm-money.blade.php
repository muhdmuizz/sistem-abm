@props(['amount'])

@if($amount === null || $amount === '')
    <span {{ $attributes }}>—</span>
@else
    @php
        $decimal = \Brick\Math\BigDecimal::of($amount);
        $display = $decimal->toScale(2, \Brick\Math\RoundingMode::HalfUp);
        [$integer, $fraction] = explode('.', (string) $display);
        $grouped = preg_replace('/\B(?=(\d{3})+(?!\d))/', ',', $integer);
    @endphp
    <span {{ $attributes }}>RM {{ $grouped }}.{{ $fraction }}</span>
@endif
