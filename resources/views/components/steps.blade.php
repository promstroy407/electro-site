@props(['steps'])
@php /** @var list<array{string, string}> $steps [название, текст] */ @endphp
<div class="steps">
    @foreach ($steps as $i => [$stepTitle, $stepText])
        <div class="step"><span class="step__n">{{ sprintf('%02d', $i + 1) }}</span><h3>{{ $stepTitle }}</h3><p>{{ $stepText }}</p></div>
    @endforeach
</div>
