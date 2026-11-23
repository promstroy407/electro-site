@props(['project'])
@php /** @var \App\Models\Project $project */ @endphp
@inject('photos', \App\Services\ProjectPhotos::class)
<article class="flex flex-col overflow-hidden rounded-lg border border-line bg-surface">
    <a href="{{ route('projects.show', $project->slug) }}" class="block aspect-[4/3] bg-ink" tabindex="-1" aria-hidden="true">
        @if ($project->coverPath() !== null)
            <img src="{{ $photos->url($project->coverPath()) }}" alt="" loading="lazy" class="h-full w-full object-cover">
        @else
            <x-icons.obshchee class="m-auto h-full w-16 text-muted" />
        @endif
    </a>
    <div class="flex flex-1 flex-col p-5">
        <p class="text-sm text-muted">{{ $project->city }}, {{ $project->year }}</p>
        <h3 class="mt-1 text-lg font-bold">
            <a href="{{ route('projects.show', $project->slug) }}" class="hover:text-accent">{{ $project->title }}</a>
        </h3>
        @if ($project->summary)
            <p class="mt-2 text-sm text-muted">{{ $project->summary }}</p>
        @endif
        @if ($project->services->isNotEmpty())
            <ul class="mt-auto flex flex-wrap gap-2 pt-4 text-xs" aria-label="Выполненные работы">
                @foreach ($project->services as $service)
                    <li class="rounded-full border border-line px-3 py-1 text-paper/80">{{ $service->title }}</li>
                @endforeach
            </ul>
        @endif
    </div>
</article>
