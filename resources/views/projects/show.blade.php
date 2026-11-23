@php
    /** @var \App\Models\Project $project */
    /** @var \Illuminate\Support\HtmlString $bodyHtml */
    /** @var \Illuminate\Support\Collection<int, \App\Models\Service> $directions */
@endphp
@inject('photos', \App\Services\ProjectPhotos::class)
<x-layouts.site :title="$project->title" :description="$project->summary">
    <div class="container-site py-10">
        <nav class="text-sm text-muted" aria-label="Навигация">
            <a href="{{ route('projects.index') }}" class="hover:text-accent">Наши объекты</a>
        </nav>

        <h1 class="mt-4 text-3xl font-extrabold sm:text-4xl">{{ $project->title }}</h1>
        <p class="mt-2 text-muted">{{ $project->city }}, {{ $project->year }}</p>

        @if ($project->services->isNotEmpty())
            <ul class="mt-4 flex flex-wrap gap-2 text-sm" aria-label="Выполненные работы">
                @foreach ($project->services as $service)
                    <li>
                        <a href="{{ route('services.show', $service->slug) }}" class="block rounded-full border border-line px-3 py-1 hover:border-accent">{{ $service->title }}</a>
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($project->summary)
            <p class="mt-6 max-w-3xl text-lg text-paper/85">{{ $project->summary }}</p>
        @endif

        @if ($project->photos !== [])
            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                @foreach ($project->photos as $index => $path)
                    <a href="{{ $photos->url($path) }}" target="_blank" class="block overflow-hidden rounded-lg border border-line bg-surface">
                        <img src="{{ $photos->url($path) }}" alt="{{ $project->title }} — фото {{ $index + 1 }}"
                             loading="{{ $index === 0 ? 'eager' : 'lazy' }}" class="aspect-[4/3] w-full object-cover">
                    </a>
                @endforeach
            </div>
        @endif

        <div class="prose-site mt-8 max-w-3xl">{{ $bodyHtml }}</div>

        <div class="mt-12">
            <x-lead-form :directions="$directions" title="Нужно так же? Рассчитаем стоимость" />
        </div>
    </div>
</x-layouts.site>
