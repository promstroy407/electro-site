@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\Service> $directions */
    /** @var \Illuminate\Support\Collection<int, \App\Models\Project> $projects */
    /** @var \Illuminate\Support\Collection<int, \App\Models\GalleryPhoto> $photos */
    /** @var int $photoCount */
    /** @var list<array{place: string, count: int, cover: \App\Models\GalleryPhoto, categories: list<\App\Enums\PhotoCategory>}> $placeCards */
    /** @var list<\App\Support\Article> $articles */
    $clients = [
        ['Генподрядчикам и застройщикам', 'Берём электромонтаж и слаботочные системы на объекте: работаем по вашему проекту и графику, сдаём участок с документацией.'],
        ['Производствам и складам', 'Силовые линии и щиты, освещение цехов и складов, шкафы управления, кабельные трассы, видеонаблюдение и СКУД.'],
        ['Торговым центрам и офисам', 'Электрика и слаботочка под арендаторов: щиты, освещение, СКС, видеонаблюдение, контроль доступа.'],
        ['Учреждениям и управляющим компаниям', 'Школы, детские сады, больницы, жилые дома: замена и модернизация электрики, слаботочные сети.'],
    ];
    $steps = [
        ['Проект или ТЗ', 'Присылаете проект, техзадание или описание объекта. Если проекта нет — разберёмся на осмотре.'],
        ['Осмотр и смета', 'Выезжаем на объект, уточняем объём и считаем смету. Стоимость фиксируем до начала работ.'],
        ['Договор', 'Согласуем сроки, график и порядок оплаты, подписываем договор.'],
        ['Монтаж', 'Бригада ведёт монтаж по графику. Силовые и слаботочные трассы планируем вместе.'],
        ['Сдача', 'Проверяем линии и системы, передаём схемы и исполнительную документацию.'],
    ];
    $faq = [
        ['Работаете на субподряде у генподрядчика?', 'Да. Берём электромонтаж и слаботочные системы на объекте, работаем по проекту и графику генподрядчика.'],
        ['Можно работать с нашим материалом?', 'Да, работаем с материалом заказчика. Если удобнее, закупаем материал сами по согласованию и отчитываемся чеками.'],
        ['Вы работаете только в '.$site->cityIn.'?', 'Мы находимся в '.$site->cityIn.' и выезжаем на объекты по всей России. Условия выезда обсуждаем при расчёте.'],
        ['Как формируется цена?', 'По проекту или техзаданию и после осмотра объекта мы составляем смету. Стоимость фиксируем в договоре до начала работ.'],
        ['Какой расчёт — наличный или безналичный?', 'Работаем и за наличный, и за безналичный расчёт.'],
        ['Электрику и слаботочные системы делаете одной бригадой?', 'Да. Поэтому силовые и слаботочные трассы мы планируем вместе, чтобы кабели не мешали друг другу и не давали наводок.'],
    ];
@endphp
@inject('files', \App\Services\GalleryPhotos::class)
@inject('structured', \App\Services\StructuredData::class)
@push('structured-data')
    <x-json-ld :data="$structured->faq($faq)" />
@endpush
<x-layouts.site
    :description="'Электромонтажные работы и слаботочные системы на объектах: электрика до 10 кВ, СКС, видеонаблюдение, СКУД. Работаем по договору, сдаём с документацией. '.$site->city.', выезд по всей России.'"
>
    <section class="hero">
        <div class="hero__bg" style="background-image: url('{{ asset('img/hero.jpg') }}')"></div>
        <div class="wrap hero__in">
            <span class="hero__tag"><x-line-icon name="bolt" :size="16" /> {{ $site->city }} · работаем по всей России</span>
            <h1>Электромонтаж и слаботочные системы <em>на объектах</em></h1>
            <p class="hero__text">Электрика до 10 кВ, щиты и освещение, кабельные трассы, СКС, видеонаблюдение и СКУД — на производствах, складах, в торговых центрах, офисах и учреждениях. Работаем бригадой по договору, сдаём объект с документацией.</p>
            <div class="hero__actions">
                <a class="btn btn--accent" href="#zayavka">Отправить проект на расчёт</a>
                <a class="btn btn--ghost" href="{{ route('gallery') }}">Наши объекты</a>
            </div>
        </div>
        <div class="facts">
            <div class="wrap facts__in">
                <div class="fact"><b>15+ лет</b><span>в электромонтаже</span></div>
                <div class="fact"><b>До 30 человек</b><span>монтажников на объекте</span></div>
                <div class="fact"><b>Документация</b><span>исполнительная, для сдачи</span></div>
                <div class="fact"><b>Вся Россия</b><span>выезжаем на объекты</span></div>
            </div>
        </div>
    </section>

    @if ($seasonal !== null)
        <section class="season" aria-label="Сезонное предложение">
            <div class="wrap season__in">
                <div class="season__text">
                    <span class="season__tag"><x-line-icon name="bolt" :size="16" /> Сейчас сезон</span>
                    <h2 class="season__title">{{ $seasonal->title }}</h2>
                    <p>{{ $seasonal->summary }}</p>
                </div>
                <a class="btn btn--accent season__btn" href="{{ route('services.show', $seasonal->slug) }}">Подробнее и расчёт</a>
            </div>
        </section>
    @endif

    <section class="band band--paper" id="dlya-kogo">
        <div class="wrap">
            <div class="section-head">
                <span class="eyebrow">Заказчики</span>
                <h2 class="h2">Для кого работаем</h2>
                <p class="lead">Объекты, где важны сроки, порядок и документы для сдачи.</p>
            </div>
            <div class="perks perks--four">
                @foreach ($clients as [$clientTitle, $clientText])
                    <div class="perk"><h3>{{ $clientTitle }}</h3><p>{{ $clientText }}</p>@if ($loop->first) <a class="perk__link" href="{{ route('subcontract') }}">Подробнее о субподряде →</a>@endif</div>
                @endforeach
            </div>
        </div>
    </section>

    @if ($directions->isNotEmpty())
        <section class="band band--white" id="uslugi">
            <div class="wrap">
                <div class="section-head">
                    <span class="eyebrow">Услуги</span>
                    <h2 class="h2">Что мы делаем</h2>
                    <p class="lead">Электромонтаж и слаботочные системы одной командой: от кабельной трассы до сдачи объекта с документацией.</p>
                </div>
                <div class="cards">
                    @foreach ($directions as $direction)
                        <x-services.direction-card :direction="$direction" :limit="5" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="band band--paper" id="o-nas">
        <div class="wrap about">
            <div class="about__photo">
                <img src="{{ asset('img/about.jpg') }}" alt="Шкаф управления оборудованием цеха, собранный нашими монтажниками" loading="lazy">
                <div class="about__badge"><b>Наш объект:</b> шкаф управления оборудованием цеха сортировки</div>
            </div>
            <div>
                <span class="eyebrow">О компании</span>
                <h2 class="h2">Почему нам доверяют электрику</h2>
                <p class="lead">{{ $site->companyName }} — в электромонтаже больше 15 лет. Базируемся в {{ $site->cityIn }}, работаем на производствах, складах, в торговых центрах, офисах и учреждениях по всей России.</p>
                <div class="perks">
                    <div class="perk"><h3>Договор и смета</h3><p>Объём и стоимость фиксируем в договоре до начала монтажа.</p></div>
                    <div class="perk"><h3>Материал заказчика</h3><p>Работаем с вашим материалом или закупаем сами по согласованию, с чеками.</p></div>
                    <div class="perk"><h3>Электрика и слаботочка одной бригадой</h3><p>Силовые и слаботочные трассы планируем вместе, без наводок и переделок.</p></div>
                    <div class="perk"><h3>Документы для сдачи</h3><p>Маркируем линии, передаём схемы и исполнительную документацию.</p></div>
                </div>
            </div>
        </div>
    </section>

    <section class="band band--dark" id="etapy">
        <div class="wrap">
            <div class="section-head">
                <span class="eyebrow">Этапы</span>
                <h2 class="h2">Как мы работаем</h2>
                <p class="lead">Сроки, объём и стоимость понятны до начала работ.</p>
            </div>
            <x-steps :steps="$steps" />
            <div class="steps-cta"><a class="btn btn--accent" href="#zayavka">Отправить проект на расчёт</a><span>Ответим по телефону{{ $site->telegram !== '' ? ' или в Telegram' : '' }}.</span></div>
        </div>
    </section>

    @if ($placeCards !== [])
        <section class="band band--white" id="obekty">
            <div class="wrap">
                <div class="section-head section-head--row">
                    <div>
                        <span class="eyebrow">Наши работы</span>
                        <h2 class="h2">Объекты</h2>
                        <p class="lead">Объекты, на которых мы вели электромонтаж. Все фото — с наших работ, без стоковых картинок.</p>
                    </div>
                    <a class="btn btn--line" href="{{ route('gallery') }}">Все фото ({{ $photoCount }})</a>
                </div>
                <div class="objs">
                    @foreach ($placeCards as $card)
                        <a class="obj" href="{{ route('gallery') }}#{{ rawurlencode($card['place']) }}">
                            <div class="obj__img"><img src="{{ $files->url($card['cover']->path) }}" alt="{{ $card['cover']->caption }}" loading="lazy"></div>
                            <div class="obj__body">
                                <span class="chip">{{ $card['count'] }} фото</span>
                                <h3>{{ $card['place'] }}</h3>
                                <ul class="ticks text-muted">
                                    @foreach ($card['categories'] as $category)
                                        <li>{{ $category->label() }}</li>
                                    @endforeach
                                </ul>
                                <span class="obj__more">Смотреть фото →</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($projects->isNotEmpty())
        <section class="band band--white">
            <div class="wrap">
                <div class="section-head section-head--row">
                    <div><span class="eyebrow">Портфолио</span><h2 class="h2">Наши объекты</h2></div>
                    <a class="btn btn--line" href="{{ route('projects.index') }}">Все объекты</a>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($projects as $project)
                        <x-project-card :project="$project" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($photos->isNotEmpty())
        <section class="band band--dark" id="foto">
            <div class="wrap">
                <div class="section-head section-head--row">
                    <div>
                        <span class="eyebrow">Галерея</span>
                        <h2 class="h2">Реальные фото с наших объектов</h2>
                        <p class="lead">Щитовые, кабельные линии, освещение, пожарная автоматика.</p>
                    </div>
                    <a class="btn btn--ghost" href="{{ route('gallery') }}">Смотреть все фото ({{ $photoCount }})</a>
                </div>
                <x-photo-grid :photos="$photos" />
            </div>
        </section>
    @endif

    @if ($partners->isNotEmpty())
        <section class="band band--white" id="partnery">
            <div class="wrap">
                <div class="section-head section-head--row">
                    <div><span class="eyebrow">Партнёры</span><h2 class="h2">С кем работаем</h2></div>
                    <a class="btn btn--line" href="{{ route('partners') }}">Все партнёры</a>
                </div>
                <div class="partners">
                    @foreach ($partners as $partner)
                        <x-partner-card :partner="$partner" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="band band--paper" id="voprosy">
        <div class="wrap">
            <div class="section-head"><span class="eyebrow">Вопросы</span><h2 class="h2">Частые вопросы</h2></div>
            <x-faq :items="$faq" />
        </div>
    </section>

    @if ($articles !== [])
        <section class="band band--white" id="stati">
            <div class="wrap">
                <div class="section-head section-head--row">
                    <div>
                        <span class="eyebrow">Статьи</span>
                        <h2 class="h2">Статьи об электрике и слаботочке</h2>
                        <p class="lead">Простыми словами о безопасности, материалах и новых технологиях — чтобы вы принимали решения осознанно.</p>
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

    <section class="band band--dark" id="kontakty">
        <div class="wrap contact">
            <div>
                <span class="eyebrow">Заявка</span>
                <h2 class="h2">Рассчитаем ваш объект</h2>
                <p class="lead">Опишите объект и объём работ или пришлите проект на почту — перезвоним, уточним детали и договоримся об осмотре.</p>
                <div class="contact__list">
                    @if ($site->phone !== '')
                        <div class="contact__item"><small>Телефон</small><a href="tel:{{ $site->phoneHref() }}">{{ $site->phone }}</a></div>
                    @endif
                    @if ($site->email !== '')
                        <div class="contact__item"><small>Почта</small><a href="mailto:{{ $site->email }}">{{ $site->email }}</a></div>
                    @endif
                    <div class="contact__item"><small>Где мы</small><b>{{ $site->city }}, работаем по всей России</b></div>
                </div>
                @if ($site->telegram !== '' || $site->socialLinks() !== [])
                    <div class="messengers">
                        @if ($site->telegram !== '')
                            <a class="btn btn--ghost" href="{{ $site->telegram }}" target="_blank" rel="noopener">Написать в Telegram</a>
                        @endif
                        @foreach ($site->socialLinks() as $link)
                            <a class="btn btn--ghost" href="{{ $link['url'] }}" target="_blank" rel="noopener">{{ $link['label'] }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
            <x-lead-form :directions="$directions" title="Оставить заявку" />
        </div>
    </section>
</x-layouts.site>
