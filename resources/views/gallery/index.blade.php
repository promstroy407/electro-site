@php
    /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\GalleryPhoto> $photos */
    /** @var list<array{category: \App\Enums\PhotoCategory, count: int}> $categories */
    /** @var array<string, int> $places */
@endphp
@inject('files', \App\Services\GalleryPhotos::class)
<x-layouts.site
    title="Реальные фото с наших объектов"
    description="Фото и видео электромонтажных работ: электрощитовые, ВРУ, кабельные линии, освещение, шкафы управления и пожарная автоматика."
    active="gallery"
>
    <section class="page-head">
        <div class="wrap">
            <nav class="crumbs" aria-label="Путь"><a href="{{ route('home') }}">Главная</a><span>/</span><span>Фото объектов</span></nav>
            <h1>Реальные фото с наших объектов</h1>
            <p class="lead">Электрощитовые и ВРУ, кабельные линии, освещение, шкафы управления и пожарная автоматика. Все снимки сделаны нашими монтажниками на объектах.</p>
        </div>
    </section>

    <section class="band band--white !pt-10">
        <div class="wrap">
            @if ($photos->isEmpty())
                <p class="text-muted">Фото скоро появятся.</p>
            @else
                <div class="filters" role="group" aria-label="Разделы">
                    <button class="filter" type="button" data-filter="all" aria-pressed="true">Все<span>{{ $photos->count() }}</span></button>
                    @foreach ($categories as $item)
                        <button class="filter" type="button" data-filter="{{ $item['category']->value }}" aria-pressed="false">{{ $item['category']->label() }}<span>{{ $item['count'] }}</span></button>
                    @endforeach
                </div>
                @if ($places !== [])
                    <div class="filters" role="group" aria-label="Объекты">
                        @foreach ($places as $place => $count)
                            <button class="filter" type="button" data-filter="{{ $place }}" aria-pressed="false">{{ $place }}<span>{{ $count }}</span></button>
                        @endforeach
                    </div>
                @endif

                <div class="masonry" data-gallery>
                    @foreach ($photos as $photo)
                        @php $meta = $photo->category->label().($photo->place ? ' · '.$photo->place : ''); @endphp
                        <figure
                            data-kind="{{ $photo->kind->value }}"
                            data-category="{{ $photo->category->value }}"
                            data-place="{{ $photo->place }}"
                            data-full="{{ $files->url($photo->path) }}"
                            data-caption="{{ $photo->caption }}"
                            data-meta="{{ $meta }}"
                        >
                            <button type="button" aria-label="{{ $photo->isVideo() ? 'Смотреть видео' : 'Открыть фото' }}: {{ $photo->caption }}">
                                @if ($photo->previewPath() !== null)
                                    <img src="{{ $files->url((string) $photo->previewPath()) }}" alt="{{ $photo->caption }}" loading="lazy" decoding="async">
                                @else
                                    {{-- Видео без обложки: браузер покажет первый кадр. --}}
                                    <video src="{{ $files->url($photo->path) }}#t=0.5" preload="metadata" muted playsinline></video>
                                @endif
                                @if ($photo->isVideo())
                                    <span class="play" aria-hidden="true">▶</span>
                                @endif
                            </button>
                            <figcaption>{{ $photo->caption }}<small>{{ $meta }}</small></figcaption>
                        </figure>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <div class="lightbox" hidden role="dialog" aria-modal="true" aria-label="Просмотр фото" data-lightbox>
        <button class="lb-btn lb-close" type="button" aria-label="Закрыть" data-close>✕</button>
        <img alt="">
        <video controls playsinline preload="none" hidden></video>
        <div class="lightbox__bar">
            <div class="lightbox__cap" data-caption></div>
            <div class="lb-nav">
                <button class="lb-btn" type="button" aria-label="Предыдущее фото" data-prev>←</button>
                <button class="lb-btn" type="button" aria-label="Следующее фото" data-next>→</button>
            </div>
        </div>
    </div>
</x-layouts.site>
