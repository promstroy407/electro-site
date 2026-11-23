@php
    /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Partner> $partners */
    /** @var \App\Support\SiteInfo $site */
@endphp
@inject('structured', \App\Services\StructuredData::class)
@push('structured-data')
    <x-json-ld :data="$structured->breadcrumbs([['Главная', route('home')], ['Партнёры', route('partners')]])" />
@endpush
<x-layouts.site
    title="Партнёры"
    :description="'Строительные компании, с которыми работает '.$site->companyName.' на объектах.'"
    active="partners"
>
    <section class="page-head">
        <div class="wrap">
            <nav class="crumbs" aria-label="Путь"><a href="{{ route('home') }}">Главная</a><span>/</span><span>Партнёры</span></nav>
            <h1>Наши партнёры</h1>
            <p class="lead">Строительные компании, с которыми мы работаем на объектах.</p>
        </div>
    </section>

    <section class="band band--white !pt-10">
        <div class="wrap">
            @if ($partners->isEmpty())
                <p class="text-muted">Список партнёров скоро появится.</p>
            @else
                <div class="partners">
                    @foreach ($partners as $partner)
                        <x-partner-card :partner="$partner" />
                    @endforeach
                </div>
            @endif

            <div class="partners__cta">
                <h2 class="partners__cta-title">Ищете электромонтажную бригаду на объект?</h2>
                <p class="text-muted">Работаем на субподряде с генподрядчиками и застройщиками — пришлите проект или объёмы на расчёт.</p>
                <a class="btn btn--accent" href="{{ route('subcontract') }}">Условия субподряда</a>
            </div>
        </div>
    </section>
</x-layouts.site>
