@php /** @var \Illuminate\Support\Collection<int, \App\Models\Service> $directions */ @endphp
<x-layouts.site
    title="Цены на электромонтаж и слаботочные системы"
    description="Цены на электромонтажные работы, пожарную сигнализацию, видеонаблюдение, СКУД и СКС. Точная стоимость — по смете."
    active="prices"
>
    <section class="page-head">
        <div class="wrap">
            <nav class="crumbs" aria-label="Путь"><a href="{{ route('home') }}">Главная</a><span>/</span><span>Цены</span></nav>
            <h1>Цены</h1>
            <p class="lead">
                Точная стоимость зависит от объекта и материалов. После осмотра или по проекту подготовим смету,
                где расписана каждая позиция, и зафиксируем цену до начала работ.
            </p>
        </div>
    </section>

    <section class="band band--white !pt-12">
        <div class="wrap">
            @forelse ($directions as $direction)
                <section class="mt-10 first:mt-0">
                    <h2 class="text-2xl font-extrabold">
                        <a href="{{ route('services.show', $direction->slug) }}" class="hover:text-accent">{{ $direction->title }}</a>
                    </h2>
                    <div class="mt-4 divide-y divide-line rounded-[10px] border border-line bg-surface px-6">
                        @foreach (collect([$direction, ...$direction->children]) as $service)
                            @if ($service->priceItems->isNotEmpty())
                                <div class="py-5">
                                    @if ($service->isNot($direction))
                                        <h3 class="mb-1 font-bold">
                                            <a href="{{ route('services.show', $service->slug) }}" class="hover:text-accent">{{ $service->title }}</a>
                                        </h3>
                                    @endif
                                    <x-price-table :items="$service->priceItems" />
                                </div>
                            @elseif ($service->isNot($direction))
                                <div class="flex flex-wrap items-center justify-between gap-2 py-4">
                                    <a href="{{ route('services.show', $service->slug) }}" class="font-semibold hover:text-accent">{{ $service->title }}</a>
                                    <span class="text-sm text-muted">по запросу</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </section>
            @empty
                <p class="text-muted">Прайс скоро появится. Пока оставьте заявку — рассчитаем стоимость под ваш объект.</p>
            @endforelse

            <div class="mt-12">
                <x-lead-form :directions="$directions" />
            </div>
        </div>
    </section>
</x-layouts.site>
