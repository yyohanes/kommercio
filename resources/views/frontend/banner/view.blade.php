@php
$link = $banner->getData('url')?FrontendHelper::get_url($banner->getData('url')):'#';
$target = $banner->getData('target', '_self');
$callToAction = $banner->getData('call_to_action', '_self');
@endphp
<div class="slide {{ $banner->getData('class') }}">
    @foreach($banner->images as $idx => $bannerImage)
        <div class="layer layer-{{ $idx }} {{ $banner->images->count() == 1?'single-layer':'' }}">
            <img src="{{ asset($bannerImage->getImagePath($imageStyle)) }}" class="img-responsive" />
        </div>
    @endforeach

    <div class="slide-content">
        @if(!empty($banner->body))
            {!! $banner->body !!}
        @endif

        @if($link)
            <a class="call-to-action" href="{{ $link?FrontendHelper::getUrl($link):'#' }}" target="{{ $target }}">{{ $callToAction }}</a>
        @endif
    </div>
</div>