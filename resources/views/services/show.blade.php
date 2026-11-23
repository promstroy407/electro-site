@php
    /** @var \App\Models\Service $service */
    /** @var \Illuminate\Support\HtmlString $bodyHtml */
    /** @var \Illuminate\Support\Collection<int, \App\Models\Service> $directions */
    /** @var int|null $priceFrom */
    /** @var \Illuminate\Support\Collection<int, \App\Models\Project> $projects */
    $priceGroups = collect([$service, ...$service->children])->filter(fn ($s) => $s->priceItems->isNotEmpty());
    $crumbs = [['Главная', route('home')], ['Услуги', route('services.index')]];
    if ($service->parent) {
        $crumbs[] = [$service->parent->title, route('services.show', $service->parent->slug)];
    }
    $crumbs[] = [$service->title, route('services.show', $service->slug)];
@endphp
@inject('structured', \App\Services\StructuredData::class)
@push('structured-data')
    <x-json-ld :data="$structured->service($service, $priceFrom)" />
    <x-json-ld :data="$structured->breadcrumbs($crumbs)" />
@endpush
<x-layouts.site
    :title="$service->meta_title ?: $service->title"
    :description="$service->meta_description ?: $service->summary"
>
    <div class="container-site py-10">
        <nav class="text-sm text-muted" aria-label="Навигация">
            <a href="{{ route('services.index') }}" class="hover:text-accent">Услуги</a>
            @if ($service->parent)
                <span aria-hidden="true">/</span>
                <a href="{{ route('services.show', $service->parent->slug) }}" class="hover:text-accent">{{ $service->parent->title }}</a>
            @endif
        </nav>

        <div class="mt-4 grid gap-10 lg:grid-cols-[1fr_320px]">
            <article>
                <h1 class="text-3xl font-extrabold sm:text-4xl">{{ $service->title }}</h1>
                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <p class="inline-block rounded bg-surface px-2 py-1 text-xs font-semibold text-muted">{{ $service->audience->label() }}</p>
                    @if ($priceFrom !== null)
                        @inject('rubles', \App\Services\RubleFormatter::class)
                        <p class="text-lg font-bold text-accent">от {{ $rubles->format($priceFrom) }}</p>
                    @endif
                </div>
                @if ($service->summary)
                    <p class="mt-5 text-lg text-paper/85">{{ $service->summary }}</p>
                @endif

                <div class="prose-site mt-6">{{ $bodyHtml }}</div>

                @if ($priceGroups->isNotEmpty())
                    <h2 class="mt-10 text-2xl font-bold">Цены</h2>
                    <div class="mt-4 space-y-6 rounded-lg border border-line bg-surface p-6">
                        @foreach ($priceGroups as $group)
                            <div>
                                @if ($group->isNot($service))
                                    <h3 class="mb-1 font-semibold">{{ $group->title }}</h3>
                                @endif
                                <x-price-table :items="$group->priceItems" />
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-2 text-sm text-muted">
                        Цены ориентировочные. Точная стоимость — по смете после осмотра.
                        <a href="{{ route('prices.index') }}" class="underline">Весь прайс</a>
                    </p>
                @endif

                @if ($service->children->isNotEmpty())
                    <h2 class="mt-10 text-2xl font-bold">Что входит в направление</h2>
                    <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach ($service->children as $child)
                            <li class="rounded-lg border border-line bg-surface p-4">
                                <a href="{{ route('services.show', $child->slug) }}" class="font-semibold hover:text-accent">{{ $child->title }}</a>
                                @if ($child->summary)
                                    <p class="mt-1 text-sm text-muted">{{ $child->summary }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </article>

            <aside class="h-fit rounded-lg border border-accent/40 bg-surface p-6 text-paper lg:sticky lg:top-6">
                <p class="text-lg font-bold">Рассчитаем стоимость</p>
                <p class="mt-2 text-sm text-paper/75">Оставьте заявку или позвоните — подготовим смету по вашему объекту.</p>
                <a href="#zayavka" class="btn-accent mt-5 w-full">Оставить заявку</a>
                @if ($site->phone !== '')
                    <a href="tel:{{ $site->phoneHref() }}" class="mt-3 block text-center text-lg font-bold">{{ $site->phone }}</a>
                @endif
                @if ($site->email !== '')
                    <a href="mailto:{{ $site->email }}" class="mt-3 block text-center text-sm underline">{{ $site->email }}</a>
                @endif
            </aside>
        </div>

        @if ($projects->isNotEmpty())
            <section class="mt-12">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <h2 class="text-2xl font-bold">Объекты с этой работой</h2>
                    <a href="{{ route('projects.index', ['rabota' => ($service->parent ?? $service)->slug]) }}" class="font-semibold text-accent hover:underline">Все такие объекты</a>
                </div>
                <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($projects as $project)
                        <x-project-card :project="$project" />
                    @endforeach
                </div>
            </section>
        @endif

        <div class="mt-12">
            <x-lead-form :directions="$directions" :selected="$service->id" />
        </div>
    </div>
</x-layouts.site>
