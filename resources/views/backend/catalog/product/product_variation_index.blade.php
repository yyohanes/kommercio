@foreach($variations as $variation)
    <tr>
        <td> {{ $variation->sku }} </td>
        <td> {{ PriceFormatter::formatNumber($variation->getRetailPrice()) }} </td>
        <td>
            <ul class="list-unstyled">
            @foreach($variation->productAttributes as $attribute)
                <li><strong>{{ $attribute->name }}:</strong> {{ $attribute->pivot->productAttributeValue->name }}</li>
            @endforeach
            </ul>
        </td>
        <td style="width: 20%;">
            <div class="btn-group btn-group-sm">
                <a class="variation-edit-btn btn btn-default" href="#" data-variation_edit="{{ route('backend.catalog.product.variation_form', ['id' => $product->id, 'variation_id' => $variation->id]) }}"><i class="fa fa-pencil"></i> Edit</a>
                <button class="btn btn-default"
                        data-variation_delete="{{ route('backend.catalog.product.delete', ['id' => $variation->id]) }}"
                        data-toggle="confirmation"
                        data-original-title="Are you sure?"
                        data-on-confirm="variationFormBehaviors.deleteVariation"
                        title>
                    <i class="fa fa-trash-o"></i> Delete</button>
            </div>
        </td>
    </tr>
@endforeach