@props(['partner'])
@php
    /** @var \App\Models\Partner $partner */
@endphp
@inject('logos', \App\Services\PartnerLogos::class)
@php
    $tag = $partner->url !== null ? 'a' : 'div';
@endphp
<{{ $tag }} class="partner"
    @if ($partner->url !== null) href="{{ $partner->url }}" target="_blank" rel="noopener" @endif
>
    <span class="partner__logo">
        @if ($partner->logo_path !== null)
            <img src="{{ $logos->url($partner->logo_path) }}" alt="{{ $partner->name }}" loading="lazy">
        @else
            <span class="partner__initial" aria-hidden="true">{{ $partner->initial() }}</span>
        @endif
    </span>
    <span class="partner__body">
        <b class="partner__name">{{ $partner->name }}</b>
        @if ($partner->description !== null)
            <span class="partner__text">{{ $partner->description }}</span>
        @endif
        @if ($partner->host() !== null)
            <span class="partner__host">{{ $partner->host() }} ↗</span>
        @endif
    </span>
</{{ $tag }}>
