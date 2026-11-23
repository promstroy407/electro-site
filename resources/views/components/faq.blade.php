@props(['items'])
@php /** @var list<array{string, string}> $items [вопрос, ответ] */ @endphp
<div class="faq">
    @foreach ($items as [$question, $answer])
        <details><summary>{{ $question }}</summary><p>{{ $answer }}</p></details>
    @endforeach
</div>
