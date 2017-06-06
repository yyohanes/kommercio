@foreach($variations as $idx => $variation)
    <tr>
        <td> {{ $idx + 1 }} </td>
        <td> {{ $variation->sku }} </td>
        <td> {{ PriceFormatter::formatNumber($variation->getRetailPrice()) }} </td>
        <td> {{ PriceFormatter::formatNumber($variation->getNetPrice()) }} </td>
        <td>
            <ul class="list-unstyled">
            @foreach($variation->productAttributes as $attribute)
                <li><strong>{{ $attribute->name }}:</strong> {{ $attribute->pivot->productAttributeValue->name }}</li>
            @endforeach
            </ul>
        </td>
        @if(ProjectHelper::isFeatureEnabled('catalog.product_features'))
        <td>
            <ul class="list-unstyled">
                @foreach($variation->productFeatures as $feature)
                    <li><strong>{{ $feature->name }}:</strong> {{ $feature->pivot->productFeatureValue->name }}</li>
                @endforeach
            </ul>
        </td>
        @endif
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