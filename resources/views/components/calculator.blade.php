@props(['rates'])
@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\CalculatorRate> $rates */
    $form = \App\Http\Requests\StoreLeadRequest::class;
    // Ошибки калькулятора в своём мешке: у обычной формы заявки их не видно.
    $calcErrors = $errors->getBag($form::ESTIMATE_ERRORS);
    $field = 'mt-1 block w-full rounded-md border border-line bg-ink px-3 py-2 text-paper placeholder:text-muted focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/40';
    $fromCalc = old($form::ESTIMATE_AREA) !== null;
    $checked = $fromCalc
        ? array_map('intval', (array) old($form::ESTIMATE_RATES, []))
        : $rates->where('is_default', true)->pluck('id')->all();
@endphp
<div class="calc" data-calculator>
    <form method="POST" action="{{ route('leads.store') }}" class="grid gap-8 lg:grid-cols-2" novalidate>
        @csrf

        <div class="calc__inputs">
            <label class="block">
                <span class="text-sm font-semibold">Площадь объекта, м²</span>
                <input type="number" name="{{ $form::ESTIMATE_AREA }}" inputmode="numeric" min="1" max="100000" step="1"
                       value="{{ old($form::ESTIMATE_AREA) }}" placeholder="Например, 60" required data-calc-area
                       class="{{ $field }} text-lg" @if ($calcErrors->has($form::ESTIMATE_AREA)) aria-invalid="true" @endif>
                @if ($calcErrors->has($form::ESTIMATE_AREA)) <span class="mt-1 block text-sm text-red-400">{{ $calcErrors->first($form::ESTIMATE_AREA) }}</span> @endif
            </label>

            <fieldset class="mt-6">
                <legend class="text-sm font-semibold">Что нужно сделать</legend>
                <div class="mt-2 grid gap-2">
                    @foreach ($rates as $rate)
                        <label class="calc__option">
                            <input type="checkbox" name="{{ $form::ESTIMATE_RATES }}[]" value="{{ $rate->id }}"
                                   data-calc-rate="{{ $rate->rate_kopecks }}" class="h-5 w-5 accent-accent"
                                   @checked(in_array($rate->id, $checked, true))>
                            {{-- Ставку за м² не показываем — владелец хочет только итог. --}}
                            <span class="flex-1">{{ $rate->title }}</span>
                        </label>
                    @endforeach
                </div>
                @if ($calcErrors->has($form::ESTIMATE_RATES) || $calcErrors->has($form::ESTIMATE_RATES.'.*'))
                    <span class="mt-1 block text-sm text-red-400">{{ $calcErrors->first($form::ESTIMATE_RATES) ?: $calcErrors->first($form::ESTIMATE_RATES.'.*') }}</span>
                @endif
            </fieldset>
        </div>

        <div class="calc__result">
            <p class="text-sm font-semibold uppercase tracking-wider text-muted">Предварительный расчёт</p>
            <p class="calc__total" aria-live="polite"><span data-calc-total>—</span></p>
            <p class="text-sm text-muted">Расчёт ориентировочный: материалы не входят, точную стоимость фиксируем в смете после осмотра объекта.</p>

            @if (session('lead_sent') && session('lead_form') === 'estimate')
                <div role="status" class="mt-5 rounded-md border border-green-700 bg-green-950/40 px-4 py-3 text-green-200">
                    <p class="font-semibold">Спасибо! Расчёт отправлен.</p>
                    <p class="text-sm">Перезвоним, уточним детали и договоримся об осмотре.</p>
                </div>
            @endif

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-semibold">Имя <span class="text-red-400" aria-hidden="true">*</span></span>
                    <input type="text" name="name" value="{{ $fromCalc ? old('name') : '' }}" maxlength="100" required autocomplete="name"
                           class="{{ $field }}" @if ($calcErrors->has('name')) aria-invalid="true" @endif>
                    @if ($calcErrors->has('name')) <span class="mt-1 block text-sm text-red-400">{{ $calcErrors->first('name') }}</span> @endif
                </label>
                <label class="block">
                    <span class="text-sm font-semibold">Телефон <span class="text-red-400" aria-hidden="true">*</span></span>
                    <input type="tel" name="phone" value="{{ $fromCalc ? old('phone') : '' }}" maxlength="30" required autocomplete="tel"
                           placeholder="+7 ___ ___-__-__" class="{{ $field }}" @if ($calcErrors->has('phone')) aria-invalid="true" @endif>
                    @if ($calcErrors->has('phone')) <span class="mt-1 block text-sm text-red-400">{{ $calcErrors->first('phone') }}</span> @endif
                </label>
            </div>

            {{-- Ловушка для ботов: человек это поле не видит и не заполняет. --}}
            <div class="hidden" aria-hidden="true">
                <label>Сайт компании <input type="text" name="{{ $form::HONEYPOT }}" value="" tabindex="-1" autocomplete="off"></label>
            </div>

            <label class="mt-4 flex items-start gap-3 text-sm">
                <input type="checkbox" name="consent" value="1" required @checked($fromCalc && old('consent'))
                       class="mt-1 h-4 w-4 accent-accent" @if ($calcErrors->has('consent')) aria-invalid="true" @endif>
                <span>
                    Я согласен на обработку персональных данных в соответствии с
                    <a href="{{ route('privacy') }}" class="underline" target="_blank">политикой конфиденциальности</a>.
                </span>
            </label>
            @if ($calcErrors->has('consent')) <span class="mt-1 block text-sm text-red-400">{{ $calcErrors->first('consent') }}</span> @endif

            <button type="submit" class="btn-accent mt-5 w-full sm:w-auto">Отправить расчёт</button>
        </div>
    </form>
</div>
