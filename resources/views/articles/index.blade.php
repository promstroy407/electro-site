@php /** @var list<\App\Support\Article> $articles */ @endphp
<x-layouts.site
    title="Статьи об электрике и слаботочных системах"
    description="Статьи об электромонтаже, кабеле, пожарной сигнализации, видеонаблюдении, СКУД и СКС."
    active="articles"
>
    <section class="page-head">
        <div class="wrap">
            <nav class="crumbs" aria-label="Путь"><a href="{{ route('home') }}">Главная</a><span>/</span><span>Статьи</span></nav>
            <h1>Статьи об электрике и слаботочных системах</h1>
            <p class="lead">Как сделать электрику надёжно, из каких материалов и какие технологии сейчас стоит выбирать.</p>
            @if ($site->socialLinks() !== [])
                <p class="mt-4 flex flex-wrap items-center gap-2">
                    <span>Короткие советы два раза в неделю:</span>
                    @foreach ($site->socialLinks() as $link)
                        <a class="btn btn--ghost btn--sm" href="{{ $link['url'] }}" target="_blank" rel="noopener">{{ $link['label'] }}</a>
                    @endforeach
                </p>
            @endif
        </div>
    </section>

    <section class="band band--white !pt-12">
        <div class="wrap">
            <div class="posts">
                @foreach ($articles as $article)
                    <x-article-card :article="$article" />
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.site>
