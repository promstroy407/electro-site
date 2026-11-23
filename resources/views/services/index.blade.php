@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\Service> $directions */
    /** @var array<int, int> $pricesFrom */
    /** @var \App\Enums\Audience $audience */
    $tabs = [
        ['value' => null, 'label' => 'Все услуги', 'active' => $audience === \App\Enums\Audience::All],
        ['value' => 'business', 'label' => 'Для бизнеса', 'active' => $audience === \App\Enums\Audience::Business],
        ['value' => 'home', 'label' => 'Для дома', 'active' => $audience === \App\Enums\Audience::Home],
    ];
@endphp
<x-layouts.site title="Услуги" description="Электромонтажные работы и слаботочные системы: полный перечень услуг." active="services">
    <section class="page-head">
        <div class="wrap">
            <nav class="crumbs" aria-label="Путь"><a href="{{ route('home') }}">Главная</a><span>/</span><span>Услуги</span></nav>
            <h1>Услуги</h1>
            <p class="lead">Электромонтаж и слаботочные системы одной командой: от кабельной трассы до сдачи объекта с документацией.</p>
            <nav class="filters mt-8 !mb-0" aria-label="Для кого">
                @foreach ($tabs as $tab)
                    <a
                        href="{{ route('services.index', $tab['value'] ? ['dlya' => $tab['value']] : []) }}"
                        class="filter"
                        aria-pressed="{{ $tab['active'] ? 'true' : 'false' }}"
                        @if ($tab['active']) aria-current="page" @endif
                    >{{ $tab['label'] }}</a>
                @endforeach
            </nav>
        </div>
    </section>

    <section class="band band--white !pt-12">
        <div class="wrap">
            @if ($directions->isEmpty())
                <p class="text-muted">Услуги скоро появятся.</p>
            @else
                <div class="cards">
                    @foreach ($directions as $direction)
                        <x-services.direction-card :direction="$direction" :price-from="$pricesFrom[$direction->id] ?? null" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</x-layouts.site>
