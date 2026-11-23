@props(['photos'])
@inject('files', \App\Services\GalleryPhotos::class)
@php /** @var \Illuminate\Support\Collection<int, \App\Models\GalleryPhoto> $photos */ @endphp
<div class="gal">
    @foreach ($photos as $photo)
        <a href="{{ route('gallery') }}"><img src="{{ $files->url($photo->path) }}" alt="{{ $photo->caption }}" loading="lazy"><span>{{ $photo->caption }}</span></a>
    @endforeach
</div>
