{{-- Блок «Мы в соцсетях» сбоку статьи: канал MAX и группа ВК. Без ссылок в .env не выводится. --}}
@if ($site->socialLinks() !== [])
    <div class="aside__box">
        <h3>Советы электрика</h3>
        <p>Короткие советы по электрике и новые статьи — два раза в неделю.</p>
        <div class="flex flex-wrap gap-2">
            @foreach ($site->socialLinks() as $link)
                <a class="btn btn--ghost btn--sm" href="{{ $link['url'] }}" target="_blank" rel="noopener">{{ $link['label'] }}</a>
            @endforeach
        </div>
    </div>
@endif
