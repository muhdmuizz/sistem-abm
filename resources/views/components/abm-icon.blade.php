@props(['name'])
<svg {{ $attributes->merge(['class' => 'icon', 'width' => 18, 'height' => 18]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($name)
@case('plus')<path d="M12 5v14M5 12h14"/>@break
@case('save')<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h12l4 4v12a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/>@break
@case('pencil')<path d="m16 3 5 5M4 16 17 3a3.5 3.5 0 0 1 5 5L9 21l-7 1 2-6Z"/>@break
@case('logout')<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>@break
@case('chevron')<path d="m9 18 6-6-6-6"/>@break
@case('trash')<path d="M3 6h18M19 6l-1 14H6L5 6M9 6V3h6v3M10 10v7M14 10v7"/>@break
@endswitch
</svg>
