<div class="slide {{ $class }}">
    @foreach($banner->images as $idx => $bannerImage)
        <div class="layer layer-{{ $idx }} {{ $banner->images->count() == 1?'single-layer':'' }}">
            <img src="{{ asset($bannerImage->getImagePath($imageStyle)) }}" class="img-responsive" />
        </div>
    @endforeach

    @if(!empty($banner->body))
        {!! $banner->body !!}
    @endif

    @if($link)
    <a href="{{ $link?FrontendHelper::getUrl($link):'#' }}" target="{{ $target }}">{{ $callToAction }}</a>
    @endif
</div>