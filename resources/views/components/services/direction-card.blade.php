@props(['direction', 'priceFrom' => null, 'limit' => null])
@php
    /** @var \App\Models\Service $direction */
    /** @var int|null $priceFrom */
    /** @var int|null $limit — сколько подуслуг показать; null — все (страница «Услуги») */
    $children = $direction->children;
    $shown = $limit === null ? $children : $children->take($limit);
    $hidden = $children->count() - $shown->count();
@endphp
@inject('rubles', \App\Services\RubleFormatter::class)
@inject('plural', \App\Services\RussianPlural::class)
<article class="svc">
    <div class="svc__icon"><x-line-icon :name="$direction->iconOrDefault()->value" :size="26" /></div>
    <h3><a href="{{ route('services.show', $direction->slug) }}">{{ $direction->title }}</a></h3>
    @if ($direction->summary)
        <p>{{ $direction->summary }}</p>
    @else
        <p></p>
    @endif
    <ul class="ticks">
        @foreach ($shown as $child)
            <li><a href="{{ route('services.show', $child->slug) }}">{{ $child->title }}</a></li>
        @endforeach
    </ul>
    @if ($hidden > 0)
        <a class="svc__more" href="{{ route('services.show', $direction->slug) }}">Ещё {{ $hidden }} {{ $plural->choose($hidden, 'услуга', 'услуги', 'услуг') }} →</a>
    @endif
    <div class="svc__foot">
        <span>{{ $priceFrom !== null ? 'от '.$rubles->format($priceFrom) : 'Цена по запросу' }}</span>
        <a href="{{ route('services.show', $direction->slug) }}">Подробнее →</a>
    </div>
</article>
