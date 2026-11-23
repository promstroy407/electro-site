@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\Service> $directions */
    /** @var \App\Support\SiteInfo $site */
    // Только то, что бригада делает сама. Пожарную сигнализацию сюда не
    // пишем: на неё нужна лицензия МЧС, а её нет (решение владельца 17.11).
    $works = [
        ['Силовые сети и щиты', 'ВРУ и распределительные щиты, силовые линии к оборудованию, шкафы управления.'],
        ['Освещение', 'Внутреннее и наружное освещение, аварийное освещение по проекту.'],
        ['Кабельные трассы', 'Лотки, короба, трубы, прокладка и маркировка кабеля.'],
        ['Высоковольтные линии', 'Кабельные линии 6–10 кВ, подключение к сетям.'],
        ['Заземление и молниезащита', 'Контуры заземления, уравнивание потенциалов, молниезащита зданий.'],
        ['Слаботочные системы', 'СКС и локальные сети, видеонаблюдение, СКУД, охранная сигнализация.'],
    ];
    $steps = [
        ['Проект или ТЗ', 'Присылаете проект, ведомость объёмов или техзадание и график работ на объекте.'],
        ['Смета', 'Считаем работы по вашим объёмам. Если нужно — выезжаем на объект до расчёта.'],
        ['Договор', 'Фиксируем объём, стоимость, сроки и порядок оплаты.'],
        ['Монтаж', 'Бригада работает по вашему графику и на связи с вашим прорабом.'],
        ['Сдача участка', 'Проверяем линии, маркируем, передаём схемы и исполнительную документацию.'],
    ];
    $perks = [
        ['До 30 монтажников на объекте', 'Выходим на объект своими людьми и наращиваем бригаду под ваш график.'],
        ['Материал ваш или наш', 'Работаем с материалом генподрядчика или закупаем сами по согласованию, с чеками.'],
        ['Электрика и слаботочка вместе', 'Одна бригада ведёт силовые и слаботочные трассы — без наводок и переделок.'],
        ['Выезд по всей России', 'Базируемся в '.$site->cityIn.'. Условия выезда и проживания бригады обсуждаем при расчёте.'],
    ];
    $faq = [
        ['Работаете с материалом генподрядчика?', 'Да. Можем работать с вашим материалом или закупить его сами по согласованию и отчитаться чеками.'],
        ['Что нужно для расчёта?', 'Проект или ведомость объёмов, адрес объекта и сроки. Если проекта нет — опишите задачу, уточним на осмотре.'],
        ['Работаете в других регионах?', 'Да, выезжаем на объекты по всей России. Условия выезда и проживания бригады обсуждаем при расчёте.'],
        ['Как оплачивать?', 'Наличный и безналичный расчёт, порядок оплаты фиксируем в договоре.'],
        ['Какие документы передаёте при сдаче?', 'Схемы и исполнительную документацию по нашему участку работ, линии и щиты промаркированы.'],
    ];
    $reviewsUrl = 'https://yandex.ru/maps/org/206971941014/reviews/';
@endphp
@inject('structured', \App\Services\StructuredData::class)
@push('structured-data')
    <x-json-ld :data="$structured->breadcrumbs([['Главная', route('home')], ['Субподряд', route('subcontract')]])" />
    <x-json-ld :data="$structured->faq($faq)" />
@endpush
<x-layouts.site
    title="Субподряд на электромонтаж и слаботочные системы"
    active="subcontract"
    :description="'Берём на субподряд электромонтаж и слаботочные системы: работаем по проекту и графику генподрядчика, до 30 монтажников на объекте, сдача с исполнительной документацией. '.$site->city.', выезд по всей России.'"
>
    <section class="band band--dark">
        <div class="wrap">
            <div class="section-head">
                <span class="eyebrow">Для генподрядчиков и застройщиков</span>
                <h1 class="h2">Субподряд на электромонтаж и слаботочные системы</h1>
                <p class="lead">Берём участок электрики и слаботочки на вашем объекте: работаем по вашему проекту и графику, сдаём с исполнительной документацией. До 30 монтажников на объекте, выезжаем по всей России.</p>
            </div>
            <div class="hero__actions">
                <a class="btn btn--accent" href="#zayavka">Отправить проект на расчёт</a>
                <a class="btn btn--ghost" href="{{ route('gallery') }}">Наши объекты</a>
            </div>
        </div>
    </section>

    <section class="band band--paper" id="raboty">
        <div class="wrap">
            <div class="section-head">
                <span class="eyebrow">Работы</span>
                <h2 class="h2">Что берём на субподряд</h2>
            </div>
            <div class="perks perks--three">
                @foreach ($works as [$workTitle, $workText])
                    <div class="perk"><h3>{{ $workTitle }}</h3><p>{{ $workText }}</p></div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="band band--dark" id="etapy">
        <div class="wrap">
            <div class="section-head">
                <span class="eyebrow">Этапы</span>
                <h2 class="h2">Как работаем с генподрядчиком</h2>
            </div>
            <x-steps :steps="$steps" />
        </div>
    </section>

    <section class="band band--paper" id="pochemu">
        <div class="wrap">
            <div class="section-head">
                <span class="eyebrow">Условия</span>
                <h2 class="h2">Почему с нами удобно</h2>
            </div>
            <div class="perks perks--four">
                @foreach ($perks as [$perkTitle, $perkText])
                    <div class="perk"><h3>{{ $perkTitle }}</h3><p>{{ $perkText }}</p></div>
                @endforeach
            </div>
            <blockquote class="quote">
                <p>«Отличная компания, брали в субподряд, сделали очень быстро и качественно»</p>
                <footer>СК М ГРУПП, <a href="{{ $reviewsUrl }}" target="_blank" rel="noopener">отзыв на Яндекс Картах</a>, ноябрь 2026</footer>
            </blockquote>
        </div>
    </section>

    <section class="band band--white" id="voprosy">
        <div class="wrap">
            <div class="section-head"><span class="eyebrow">Вопросы</span><h2 class="h2">Частые вопросы генподрядчиков</h2></div>
            <x-faq :items="$faq" />
        </div>
    </section>

    <section class="band band--dark" id="kontakty">
        <div class="wrap contact">
            <div>
                <span class="eyebrow">Расчёт</span>
                <h2 class="h2">Пришлите проект на расчёт</h2>
                <p class="lead">Опишите объект и объём работ или пришлите проект на почту — перезвоним, уточним детали и сроки.</p>
                <div class="contact__list">
                    @if ($site->phone !== '')
                        <div class="contact__item"><small>Телефон</small><a href="tel:{{ $site->phoneHref() }}">{{ $site->phone }}</a></div>
                    @endif
                    @if ($site->email !== '')
                        <div class="contact__item"><small>Почта для проектов</small><a href="mailto:{{ $site->email }}">{{ $site->email }}</a></div>
                    @endif
                </div>
            </div>
            <x-lead-form :directions="$directions" title="Заявка на субподряд" />
        </div>
    </section>
</x-layouts.site>
