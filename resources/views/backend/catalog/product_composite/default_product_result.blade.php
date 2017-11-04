<div class="col-md-3 text-center product-item" data-product_id="{{ $product->id }}">
    @if($product->thumbnail)<img class="img-responsive" src="{{ url($product->thumbnail->getImagePath('backend_thumbnail')) }}">@endif
    <div>{{ $product->name }}</div>
    <input name="{{ $relation }}_quantity[]" type="text" class="form-control" placeholder="Default qty" value="{{ $quantity }}">
    <input name="{{ $relation }}_product[]" type="hidden" value="{{ $product->id }}">
    <a href="#" class="product-item-remove"><i class="fa fa-remove"></i></a>
</div>