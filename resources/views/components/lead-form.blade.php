@props([
    'directions',
    'selected' => null,
    'title' => 'Рассчитаем стоимость',
])
@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\Service> $directions */
    $selectedId = (string) old('service_id', $selected);
    $field = 'mt-1 block w-full rounded-md border border-line bg-ink px-3 py-2 text-paper placeholder:text-muted focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/40';
@endphp
<section id="zayavka" class="scroll-mt-6 rounded-lg border border-line bg-surface p-6 sm:p-8">
    <h2 class="text-2xl font-extrabold">{{ $title }}</h2>
    <p class="mt-1 text-sm text-muted">Оставьте телефон — перезвоним, уточним объём работ и подготовим смету.</p>

    @if (session('lead_sent') && session('lead_form') !== 'estimate')
        <div role="status" class="mt-5 rounded-md border border-green-700 bg-green-950/40 px-4 py-3 text-green-200">
            <p class="font-semibold">Спасибо! Заявка отправлена.</p>
            <p class="text-sm">Мы свяжемся с вами в рабочее время{{ $site->workHours !== '' ? ' ('.$site->workHours.')' : '' }}.</p>
        </div>
    @endif

    <form method="POST" action="{{ route('leads.store') }}" class="mt-6 grid gap-4 sm:grid-cols-2" novalidate>
        @csrf

        <label class="block">
            <span class="text-sm font-semibold">Имя <span class="text-red-400" aria-hidden="true">*</span></span>
            <input type="text" name="name" value="{{ old('name') }}" maxlength="100" required autocomplete="name"
                   class="{{ $field }}" @error('name') aria-invalid="true" @enderror>
            @error('name') <span class="mt-1 block text-sm text-red-400">{{ $message }}</span> @enderror
        </label>

        <label class="block">
            <span class="text-sm font-semibold">Телефон <span class="text-red-400" aria-hidden="true">*</span></span>
            <input type="tel" name="phone" value="{{ old('phone') }}" maxlength="30" required autocomplete="tel"
                   placeholder="+7 ___ ___-__-__" class="{{ $field }}" @error('phone') aria-invalid="true" @enderror>
            @error('phone') <span class="mt-1 block text-sm text-red-400">{{ $message }}</span> @enderror
        </label>

        <label class="block">
            <span class="text-sm font-semibold">Организация</span>
            <input type="text" name="organization" value="{{ old('organization') }}" maxlength="150" autocomplete="organization"
                   placeholder="Если вы от компании" class="{{ $field }}" @error('organization') aria-invalid="true" @enderror>
            @error('organization') <span class="mt-1 block text-sm text-red-400">{{ $message }}</span> @enderror
        </label>

        <label class="block">
            <span class="text-sm font-semibold">Объект</span>
            <input type="text" name="facility" value="{{ old('facility') }}" maxlength="200"
                   placeholder="Например: склад 2 000 м², Йошкар-Ола" class="{{ $field }}" @error('facility') aria-invalid="true" @enderror>
            @error('facility') <span class="mt-1 block text-sm text-red-400">{{ $message }}</span> @enderror
        </label>

        <label class="block">
            <span class="text-sm font-semibold">Email</span>
            <input type="email" name="email" value="{{ old('email') }}" maxlength="255" autocomplete="email"
                   class="{{ $field }}" @error('email') aria-invalid="true" @enderror>
            @error('email') <span class="mt-1 block text-sm text-red-400">{{ $message }}</span> @enderror
        </label>

        <label class="block">
            <span class="text-sm font-semibold">Услуга</span>
            <select name="service_id" class="{{ $field }}" @error('service_id') aria-invalid="true" @enderror>
                <option value="">Не знаю / несколько</option>
                @foreach ($directions as $direction)
                    <optgroup label="{{ $direction->title }}">
                        <option value="{{ $direction->id }}" @selected($selectedId === (string) $direction->id)>{{ $direction->title }} — в целом</option>
                        @foreach ($direction->children as $child)
                            <option value="{{ $child->id }}" @selected($selectedId === (string) $child->id)>{{ $child->title }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            @error('service_id') <span class="mt-1 block text-sm text-red-400">{{ $message }}</span> @enderror
        </label>

        <label class="block sm:col-span-2">
            <span class="text-sm font-semibold">Откуда вы о нас узнали?</span>
            <select name="origin" class="{{ $field }}" @error('origin') aria-invalid="true" @enderror>
                <option value="">Не помню / не важно</option>
                @foreach (\App\Enums\LeadOrigin::cases() as $origin)
                    <option value="{{ $origin->value }}" @selected(old('origin') === $origin->value)>{{ $origin->label() }}</option>
                @endforeach
            </select>
            @error('origin') <span class="mt-1 block text-sm text-red-400">{{ $message }}</span> @enderror
        </label>

        <label class="block sm:col-span-2">
            <span class="text-sm font-semibold">Что нужно сделать</span>
            <textarea name="message" rows="4" maxlength="2000" class="{{ $field }}"
                      placeholder="Объём работ, сроки, есть ли проект — всё, что знаете"
                      @error('message') aria-invalid="true" @enderror>{{ old('message') }}</textarea>
            @error('message') <span class="mt-1 block text-sm text-red-400">{{ $message }}</span> @enderror
        </label>

        {{-- Ловушка для ботов: человек это поле не видит и не заполняет. --}}
        <div class="hidden" aria-hidden="true">
            <label>Сайт компании <input type="text" name="{{ \App\Http\Requests\StoreLeadRequest::HONEYPOT }}" value="" tabindex="-1" autocomplete="off"></label>
        </div>

        <div class="sm:col-span-2">
            <label class="flex items-start gap-3 text-sm">
                <input type="checkbox" name="consent" value="1" required @checked(old('consent'))
                       class="mt-1 h-4 w-4 accent-accent" @error('consent') aria-invalid="true" @enderror>
                <span>
                    Я согласен на обработку персональных данных в соответствии с
                    <a href="{{ route('privacy') }}" class="underline" target="_blank">политикой конфиденциальности</a>.
                </span>
            </label>
            @error('consent') <span class="mt-1 block text-sm text-red-400">{{ $message }}</span> @enderror
        </div>

        <div class="sm:col-span-2">
            <button type="submit" class="btn-accent w-full sm:w-auto">Отправить заявку</button>
        </div>
    </form>
</section>
