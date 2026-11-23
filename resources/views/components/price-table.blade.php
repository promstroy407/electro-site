@props(['items'])
@php /** @var \Illuminate\Support\Collection<int, \App\Models\PriceItem> $items */ @endphp
@inject('rubles', \App\Services\RubleFormatter::class)
<table class="w-full border-collapse text-sm">
    <tbody>
        @foreach ($items as $item)
            <tr class="border-b border-line last:border-b-0">
                <td class="py-3 pr-4">{{ $item->title }}</td>
                <td class="py-3 text-right font-semibold whitespace-nowrap">
                    {{ $item->is_from ? 'от ' : '' }}{{ $rubles->format($item->price_kopecks) }}
                    <span class="font-normal text-muted">/ {{ $item->unit->short() }}</span>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
