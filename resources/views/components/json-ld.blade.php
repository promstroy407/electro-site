@props(['data'])
{{-- Микроразметка schema.org. JSON_HEX_TAG не даёт тексту закрыть тег </script>. --}}
<script type="application/ld+json">{!! json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR) !!}</script>
