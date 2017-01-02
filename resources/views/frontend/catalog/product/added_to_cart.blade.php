@foreach($added_products as $product)
    <div class="product-wrapper">
        <figure class="product-image">
            @if($product->hasThumbnail())<img src="{{ asset($product->getThumbnail()->getImagePath('product_thumbnail')) }}" alt="{{ $product->name }}">@endif
        </figure>

        <h2 class="product-name">
            {{ $product->getName().' '.$product->printProductAttributes(false) }}
        </h2>

        <?php
        $netPrice = $product->getNetPrice();
        $oldPrice = $product->getOldPrice();
        ?>
        <div class="product-price">
            @if($oldPrice)
                <span class="discount-price">{{ PriceFormatter::formatNumber($oldPrice) }}</span>
            @endif
            {{ PriceFormatter::formatNumber($netPrice) }}
        </div>
        @if($oldPrice)
            <?php $discountPercent = round($netPrice / $oldPrice * 100); ?>
            <div class="promo">{{ $discountPercent }}%</div>
        @endif
    </div>
@endforeach

<div class="button-wrapper">
    <div class="button checkout">
        <a href="{{ route('frontend.order.cart') }}">Checkout</a>
    </div>

    <div class="button continue">
        <a href="{{ route('frontend.catalog.shop') }}">Continue Shopping</a>
    </div>
</div>