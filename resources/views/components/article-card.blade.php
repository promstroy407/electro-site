@props(['article'])
@php /** @var \App\Support\Article $article */ @endphp
<a class="post" href="{{ route('articles.show', $article->slug) }}">
    <div class="post__img"><x-dynamic-component :component="'illustrations.stati.'.$article->illustration" /></div>
    <div class="post__body">
        <span class="chip">{{ $article->tag }}</span>
        <h3>{{ $article->title }}</h3>
        <p>{{ $article->excerpt }}</p>
        <div class="post__meta">{{ $article->readingMinutes() }} мин чтения</div>
    </div>
</a>
