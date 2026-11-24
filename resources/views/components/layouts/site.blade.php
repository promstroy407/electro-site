@props([
    'title' => null,
    'description' => null,
    'active' => null,
])
@inject('structured', \App\Services\StructuredData::class)
@php
    /** @var \App\Support\SiteInfo $site */
    $pageTitle = $title ? $title.' — '.$site->companyName : $site->companyName;
    $nav = [
        ['services', route('services.index'), 'Услуги'],
        ['subcontract', route('subcontract'), 'Субподряд'],
        ['for-home', route('for-home'), 'Для дома'],
        ['gallery', route('gallery'), 'Объекты'],
        ['articles', route('articles.index'), 'Статьи'],
        ['contacts', route('home').'#kontakty', 'Контакты'],
    ];
@endphp
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $pageTitle }}</title>
    @php $shareDescription = $description ?: 'Электромонтаж и слаботочные системы под ключ. '.$site->city.', работаем по всей России.'; @endphp
    <meta name="description" content="{{ $shareDescription }}">
    {{-- Превью ссылки в мессенджерах и соцсетях: заголовок, описание, картинка. --}}
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ru_RU">
    <meta property="og:site_name" content="{{ $site->companyName }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $shareDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('img/og.jpg') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <meta name="theme-color" content="#101318">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-metrika />
    <x-json-ld :data="$structured->organization()" />
    @stack('structured-data')
</head>
<body class="flex min-h-screen flex-col">
    <header class="header" id="top">
        <div class="wrap header__in">
            <a class="logo" href="{{ route('home') }}">
                <img class="logo__mark" src="{{ asset('img/logo-mark.png') }}" alt="" width="42" height="42">
                <span class="logo__name">{{ $site->companyName }}<small>электромонтаж</small></span>
            </a>
            <nav class="nav" aria-label="Разделы сайта">
                @foreach ($nav as [$key, $href, $label])
                    <a href="{{ $href }}" @if ($active === $key) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </nav>
            @if ($site->phone !== '')
                <a class="header__phone" href="tel:{{ $site->phoneHref() }}">{{ $site->phone }}</a>
            @endif
            <a class="btn btn--accent btn--sm" href="{{ route('home') }}#zayavka">Оставить заявку</a>
            <button class="burger" type="button" aria-label="Меню" aria-expanded="false" data-burger>
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
        </div>
    </header>

    <main class="flex-1">
        {{ $slot }}
    </main>

    <footer class="footer" id="podval">
        <div class="wrap">
            <div class="footer__grid">
                <div>
                    <a class="logo" href="{{ route('home') }}">
                        <img class="logo__mark" src="{{ asset('img/logo-mark.png') }}" alt="" width="42" height="42">
                        <span class="logo__name text-paper">{{ $site->companyName }}<small>электромонтаж</small></span>
                    </a>
                    <p class="mt-4 max-w-[34ch]">Электромонтаж и слаботочные системы. Находимся в {{ $site->cityIn }}, работаем по всей России.</p>
                </div>
                <div>
                    <h4>Услуги</h4>
                    <ul>
                        <li><a href="{{ route('services.index', ['dlya' => 'business']) }}">Все услуги</a></li>
                        <li><a href="{{ route('subcontract') }}">Субподряд для генподрядчиков</a></li>
                        <li><a href="{{ route('for-home') }}">Электрика для квартиры и дома</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Информация</h4>
                    <ul>
                        <li><a href="{{ route('gallery') }}">Реальные фото с наших объектов</a></li>
                        <li><a href="{{ route('articles.index') }}">Статьи</a></li>
                        <li><a href="{{ route('partners') }}">Партнёры</a></li>
                        <li><a href="{{ route('home') }}#voprosy">Частые вопросы</a></li>
                        <li><a href="{{ route('home') }}#zayavka">Оставить заявку</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Контакты</h4>
                    <ul>
                        @if ($site->phone !== '')
                            <li><a href="tel:{{ $site->phoneHref() }}">{{ $site->phone }}</a></li>
                        @endif
                        @if ($site->telegram !== '')
                            <li><a href="{{ $site->telegram }}" target="_blank" rel="noopener">Telegram</a></li>
                        @endif
                        @foreach ($site->socialLinks() as $link)
                            <li><a href="{{ $link['url'] }}" target="_blank" rel="noopener">{{ $link['label'] }}</a></li>
                        @endforeach
                        @if ($site->email !== '')
                            <li><a href="mailto:{{ $site->email }}">{{ $site->email }}</a></li>
                        @endif
                        <li>{{ $site->city }}, {{ $site->region }}</li>
                        @if ($site->workHours !== '')
                            <li>{{ $site->workHours }}</li>
                        @endif
                    </ul>
                </div>
            </div>
            <div class="footer__bottom">
                <span>© {{ now()->year }} {{ $site->companyName }}</span>
                @if ($site->requisites() !== '')
                    <span class="footer__requisites">{{ $site->requisites() }}</span>
                @endif
                <a href="{{ route('privacy') }}">Политика конфиденциальности</a>
            </div>
        </div>
    </footer>

</body>
</html>
