@php
    /** @var \App\Support\Article $article */
    /** @var \Illuminate\Support\HtmlString $html */
    /** @var list<array{id: string, title: string}> $toc */
    /** @var list<\App\Support\Article> $others */
@endphp
@inject('structured', \App\Services\StructuredData::class)
@push('structured-data')
    <x-json-ld :data="$structured->article($article)" />
    <x-json-ld :data="$structured->breadcrumbs([
        ['Главная', route('home')],
        ['Статьи', route('articles.index')],
        [$article->title, route('articles.show', $article->slug)],
    ])" />
@endpush
<x-layouts.site :title="$article->seoTitle" :description="$article->seoDescription" active="articles">
    <section class="page-head">
        <div class="wrap">
            <nav class="crumbs" aria-label="Путь">
                <a href="{{ route('home') }}">Главная</a><span>/</span>
                <a href="{{ route('articles.index') }}">Статьи</a><span>/</span>
                <span>{{ $article->tag }}</span>
            </nav>
            <h1>{{ $article->title }}</h1>
            <div class="meta-row"><span class="chip">{{ $article->tag }}</span><span>{{ $article->readingMinutes() }} мин чтения</span></div>
        </div>
    </section>

    <section class="band band--white !pt-12">
        <div class="wrap article">
            <article>
                <div class="article__cover"><x-dynamic-component :component="'illustrations.stati.'.$article->illustration" /></div>
                <div class="prose">{{ $html }}</div>
            </article>
            <aside class="aside">
                @if ($toc !== [])
                    <div class="aside__box">
                        <h3>Содержание</h3>
                        <ol>
                            @foreach ($toc as $item)
                                <li><a href="#{{ $item['id'] }}">{{ $item['title'] }}</a></li>
                            @endforeach
                        </ol>
                    </div>
                @endif
                <div class="aside__box aside__box--dark">
                    <h3>Нужен расчёт?</h3>
                    <p>Опишите задачу — посчитаем смету до начала работ.</p>
                    <a class="btn btn--accent btn--sm" href="{{ route('home') }}#zayavka">Оставить заявку</a>
                </div>
                <x-social-box />
                @if ($others !== [])
                    <div class="aside__box">
                        <h3>Другие статьи</h3>
                        <ol>
                            @foreach ($others as $other)
                                <li><a href="{{ route('articles.show', $other->slug) }}">{{ $other->title }}</a></li>
                            @endforeach
                        </ol>
                    </div>
                @endif
            </aside>
        </div>
    </section>
</x-layouts.site>
