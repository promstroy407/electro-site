@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\Service> $directions */
    /** @var list<\App\Support\Article> $articles */
    /** @var \Illuminate\Support\Collection<int, \App\Models\GalleryPhoto> $photos */
    /** @var \App\Support\SiteInfo $site */
    // Ответы на боли частников (документ «Боли частников…», 18.11).
    // Факты о чистоте, оплате, гарантии и допусках подтвердил владелец 18.11.
    $perks = [
        ['Своя бригада, без посредников', 'На объект выходят наши монтажники — заказ не перепродаём другим бригадам.'],
        ['Чисто за собой', 'Штробим с пылесосом, регулярно убираем. Мебель выносим или укрываем.'],
        ['Смета до начала работ', 'Считаем все работы заранее. Дополнительные работы — только по согласованию с вами.'],
        ['Оплата по этапам', 'Аванс, оплата по этапам и окончательный расчёт после окончания работ.'],
        ['Материал ваш или наш', 'Работаем с вашим материалом или закупаем сами по согласованию, с чеками.'],
        ['Гарантия 2 года', 'На все наши работы. Монтажники с группой допуска по электробезопасности III–IV.'],
    ];
    $works = [
        ['Электрика под ключ', 'Новая проводка в квартире или доме: схема, щит, розетки, выключатели, свет.'],
        ['Замена старой проводки', 'Меняем алюминиевую и старую медную проводку во вторичном жилье.'],
        ['Щит и защита', 'Автоматы, УЗО и дифавтоматы, реле напряжения. Каждая линия подписана.'],
        ['Частный дом и участок', 'Ввод в дом, электрика в бане и гараже, свет на участке.'],
        ['Тёплый пол и техника', 'Отдельные линии под плиту, духовку, бойлер, кондиционер и тёплый пол.'],
        ['Видеонаблюдение и сигнализация', 'Камеры и охранная сигнализация для дома — вместе с электрикой.'],
    ];
    $steps = [
        ['Осмотр', 'Приезжаем, смотрим квартиру или дом, обсуждаем, где нужны розетки и свет.'],
        ['Смета', 'Считаем работы и материал. Стоимость и порядок оплаты согласуем до начала.'],
        ['Черновая электрика', 'Прокладываем кабель и ставим коробки до штукатурки и плитки.'],
        ['Чистовая электрика', 'После отделки ставим розетки, выключатели, светильники, собираем щит.'],
        ['Сдача', 'Проверяем каждую линию, подписываем автоматы и отдаём вам схему щита.'],
    ];
    $faq = [
        ['Сколько стоит электрика в квартире?', 'Зависит от площади, числа точек и материала. Примерную стоимость покажет калькулятор, точную — смета после осмотра.'],
        ['Будет ли грязь?', 'Штробим с пылесосом и регулярно убираем за собой. Мебель выносим или укрываем до начала работ.'],
        ['Как платить?', 'Аванс, затем оплата по этапам и окончательный расчёт после окончания работ. Наличный и безналичный расчёт.'],
        ['Материал покупать самим?', 'Как вам удобнее: работаем с вашим материалом или закупаем сами по согласованию и отчитываемся чеками.'],
        ['Есть ли гарантия?', 'Да, гарантия на работы — 2 года.'],
        ['Кто будет работать?', 'Наши монтажники, без посредников. У них группа допуска по электробезопасности III–IV.'],
    ];
@endphp
@inject('structured', \App\Services\StructuredData::class)
@push('structured-data')
    <x-json-ld :data="$structured->breadcrumbs([['Главная', route('home')], ['Для дома', route('for-home')]])" />
    <x-json-ld :data="$structured->faq($faq)" />
@endpush
<x-layouts.site
    :title="'Электрик в '.$site->cityIn.' — электромонтаж в квартире и доме'"
    active="for-home"
    :description="'Услуги электрика в '.$site->cityIn.': замена проводки, щит, розетки, свет, электрика под ключ в квартире и частном доме. Своя бригада без посредников, чисто, гарантия 2 года.'"
>
    <section class="band band--dark">
        <div class="wrap">
            <div class="section-head">
                <span class="eyebrow">Для квартир и частных домов</span>
                <h1 class="h2">Электрик для квартиры и частного дома</h1>
                <p class="lead">Делаем электрику своей бригадой, без посредников: от замены проводки до электрики под ключ. Убираем за собой, смету считаем до начала работ, на работы даём гарантию 2 года.</p>
            </div>
            <div class="hero__actions">
                <a class="btn btn--accent" href="{{ route('calculator') }}">Рассчитать стоимость</a>
                <a class="btn btn--ghost" href="#zayavka">Оставить заявку</a>
            </div>
        </div>
    </section>

    <section class="band band--paper" id="pochemu">
        <div class="wrap">
            <div class="section-head">
                <span class="eyebrow">Без нервов</span>
                <h2 class="h2">Чего боятся при ремонте — и как работаем мы</h2>
            </div>
            <div class="perks perks--three">
                @foreach ($perks as [$perkTitle, $perkText])
                    <div class="perk"><h3>{{ $perkTitle }}</h3><p>{{ $perkText }}</p></div>
                @endforeach
            </div>
        </div>
    </section>

    @if ($photos->isNotEmpty())
        <section class="band band--dark" id="primery">
            <div class="wrap">
                <div class="section-head section-head--row">
                    <div>
                        <span class="eyebrow">Примеры работ</span>
                        <h2 class="h2">Квартиры и дома, где мы делали электрику</h2>
                        <p class="lead">Фото с наших объектов в {{ $site->cityIn }}: замена проводки, щиты, свет, тёплый пол.</p>
                    </div>
                    <a class="btn btn--ghost" href="{{ route('gallery') }}">Все фото</a>
                </div>
                <x-photo-grid :photos="$photos" />
            </div>
        </section>
    @endif

    <section class="band band--white" id="raboty">
        <div class="wrap">
            <div class="section-head section-head--row">
                <div>
                    <span class="eyebrow">Работы</span>
                    <h2 class="h2">Что делаем в квартире и доме</h2>
                </div>
                <a class="btn btn--line" href="{{ route('prices.index') }}">Цены</a>
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
                <h2 class="h2">Как проходит работа</h2>
            </div>
            <x-steps :steps="$steps" />
        </div>
    </section>

    @if ($articles !== [])
        <section class="band band--paper" id="stati">
            <div class="wrap">
                <div class="section-head section-head--row">
                    <div>
                        <span class="eyebrow">Статьи</span>
                        <h2 class="h2">Полезно перед ремонтом</h2>
                    </div>
                    <a class="btn btn--line" href="{{ route('articles.index') }}">Все статьи</a>
                </div>
                <div class="posts">
                    @foreach ($articles as $article)
                        <x-article-card :article="$article" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="band band--white" id="voprosy">
        <div class="wrap">
            <div class="section-head"><span class="eyebrow">Вопросы</span><h2 class="h2">Частые вопросы</h2></div>
            <x-faq :items="$faq" />
        </div>
    </section>

    <section class="band band--dark" id="kontakty">
        <div class="wrap contact">
            <div>
                <span class="eyebrow">Заявка</span>
                <h2 class="h2">Расскажите о квартире или доме</h2>
                <p class="lead">Оставьте заявку — перезвоним, ответим на вопросы и договоримся об осмотре.</p>
                <div class="contact__list">
                    @if ($site->phone !== '')
                        <div class="contact__item"><small>Телефон</small><a href="tel:{{ $site->phoneHref() }}">{{ $site->phone }}</a></div>
                    @endif
                    @if ($site->telegram !== '')
                        <div class="contact__item"><small>Telegram</small><a href="{{ $site->telegram }}" target="_blank" rel="noopener">Написать</a></div>
                    @endif
                </div>
            </div>
            <x-lead-form :directions="$directions" title="Заявка на электрику" />
        </div>
    </section>
</x-layouts.site>
