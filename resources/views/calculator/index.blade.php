@php /** @var \Illuminate\Support\Collection<int, \App\Models\CalculatorRate> $rates */ @endphp
<x-layouts.site
    title="Калькулятор стоимости электромонтажа"
    description="Предварительный расчёт стоимости электромонтажа, видеонаблюдения и сигнализации по площади объекта. Точная цена — в смете после осмотра."
    active="calculator"
>
    <section class="page-head">
        <div class="wrap">
            <nav class="crumbs" aria-label="Путь"><a href="{{ route('home') }}">Главная</a><span>/</span><span>Калькулятор</span></nav>
            <h1>Сколько стоит электрика на вашем объекте</h1>
            <p class="lead">Укажите площадь и отметьте нужные работы — сразу покажем ориентировочную стоимость.</p>
        </div>
    </section>

    <section class="band band--dark !pt-12 scroll-mt-6" id="raschet">
        <div class="wrap">
            <x-calculator :rates="$rates" />
        </div>
    </section>
</x-layouts.site>
