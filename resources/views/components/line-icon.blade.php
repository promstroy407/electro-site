@props(['name', 'size' => 24])
{{-- Линейные иконки по макету. Имя — значение App\Enums\ServiceIcon или bolt. --}}
@php
    $paths = match ($name) {
        'slabotochka' => '<rect x="3" y="3" width="18" height="7" rx="1.5"/><path d="M7 10v4M12 10v4M17 10v4M5 14h4v6H5zM10 14h4v6h-4zM15 14h4v6h-4z"/>',
        'pozharka' => '<path d="M12 22c4 0 7-3 7-7 0-4-3-6-4-10-2 2-3 4-3 6-1-1-2-2-2-4-3 3-5 5-5 8 0 4 3 7 7 7Z"/>',
        'video' => '<path d="M3 7h12l3 3v4l-3 3H3z"/><path d="M18 11l3-2v6l-3-2"/><circle cx="9" cy="12" r="2.5"/>',
        'skud' => '<rect x="5" y="3" width="14" height="18" rx="2"/><circle cx="12" cy="10" r="2.5"/><path d="M9 16h6"/>',
        'calculator' => '<rect x="5" y="2" width="14" height="20" rx="2"/><path d="M8 6h8v4H8z"/><path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01M16 18h.01"/>',
        'ohrana' => '<path d="M12 2 4 5v6c0 5 3.5 9 8 11 4.5-2 8-6 8-11V5l-8-3Z"/><path d="m9 12 2 2 4-4"/>',
        default => '<path d="M13 2 4 14h7l-1 8 9-12h-7l1-8Z"/>',
    };
@endphp
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" {{ $attributes->merge(['fill' => 'none', 'stroke' => 'currentColor']) }}>{!! $paths !!}</svg>
