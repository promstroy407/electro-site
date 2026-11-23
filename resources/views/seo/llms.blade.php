@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\Service> $directions */
    /** @var list<\App\Support\Article> $articles */
@endphp
# {{ $site->companyName }}

> Электромонтаж и слаботочные системы под ключ: электрика до 10 кВ, щиты и освещение, пожарная и охранная сигнализация, видеонаблюдение, СКУД, СКС. Находимся в {{ $site->cityIn }} ({{ $site->region }}), работаем по всей России, с юрлицами и частными клиентами. Стоимость фиксируем в смете до начала работ.

## Контакты

@if ($site->phone !== '')
- Телефон: {{ $site->phone }}
@endif
@if ($site->email !== '')
- Почта: {{ $site->email }}
@endif
@if ($site->workHours !== '')
- Часы работы: {{ $site->workHours }}
@endif
- Заявка и предварительный расчёт: {{ route('home') }}
- Цены: {{ route('prices.index') }}

## Услуги

@foreach ($directions as $direction)
- [{{ $direction->title }}]({{ route('services.show', $direction->slug) }}){{ $direction->summary ? ': '.$direction->summary : '' }}
@foreach ($direction->children as $child)
  - [{{ $child->title }}]({{ route('services.show', $child->slug) }})
@endforeach
@endforeach

## Статьи

@foreach ($articles as $article)
- [{{ $article->title }}]({{ route('articles.show', $article->slug) }}): {{ $article->excerpt }}
@endforeach
