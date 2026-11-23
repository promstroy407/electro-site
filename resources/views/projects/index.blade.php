@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\Service> $directions */
    /** @var \App\Models\Service|null $active */
    /** @var \Illuminate\Support\Collection<int, \App\Models\Project> $projects */
    $chip = 'rounded-full border px-4 py-2 text-sm font-semibold';
@endphp
<x-layouts.site title="Наши объекты" description="Выполненные объекты: электромонтаж, пожарная сигнализация, видеонаблюдение, СКУД и СКС.">
    <div class="container-site py-10">
        <h1 class="text-3xl font-extrabold sm:text-4xl">Наши объекты</h1>
        <p class="mt-3 max-w-3xl text-muted">Объекты, где мы выполнили монтаж. Выберите вид работ, чтобы увидеть похожие задачи.</p>

        <nav class="mt-6 flex flex-wrap gap-2" aria-label="Вид работ">
            <a href="{{ route('projects.index') }}"
               @class([$chip, 'border-accent bg-accent text-ink' => $active === null, 'border-line bg-surface hover:border-accent' => $active !== null])
               @if ($active === null) aria-current="page" @endif>Все объекты</a>
            @foreach ($directions as $direction)
                @php $isActive = $active !== null && $active->is($direction); @endphp
                <a href="{{ route('projects.index', ['rabota' => $direction->slug]) }}"
                   @class([$chip, 'border-accent bg-accent text-ink' => $isActive, 'border-line bg-surface hover:border-accent' => ! $isActive])
                   @if ($isActive) aria-current="page" @endif>{{ $direction->title }}</a>
            @endforeach
        </nav>

        @if ($projects->isEmpty())
            <p class="mt-10 text-muted">
                {{ $active === null ? 'Объекты скоро появятся.' : 'По этому виду работ объектов пока нет — посмотрите все объекты или оставьте заявку.' }}
            </p>
        @else
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($projects as $project)
                    <x-project-card :project="$project" />
                @endforeach
            </div>
        @endif

        <div class="mt-12">
            <x-lead-form :directions="$directions" />
        </div>
    </div>
</x-layouts.site>
