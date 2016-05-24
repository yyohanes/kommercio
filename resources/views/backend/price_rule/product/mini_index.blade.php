<div class="table-scrollable">
    <table class="table table-hover">
        <thead>
        <tr>
            <th> Rule </th>
            <th> Variation </th>
            <th> Price </th>
            <th> Modification </th>
            <th> Currency </th>
            <th> Store </th>
            <th> Active </th>
            <th> Actions </th>
        </tr>
        </thead>
        <tbody>
        @foreach($priceRules as $priceRule)
            <tr>
                <td> {{ $priceRule->name?$priceRule->name:'-' }} </td>
                <td> {{ $priceRule->variation?$priceRule->variation->name:'-' }} </td>
                <td> {{ $priceRule->price?PriceFormatter::formatNumber($priceRule->price, $priceRule->currency):'-' }} </td>
                <td> {{ $priceRule->modification?$priceRule->getModificationOutput():'-' }} </td>
                <td> {{ $priceRule->currency?CurrencyHelper::getCurrency($priceRule->currency)['iso']:'All' }} </td>
                <td> {{ $priceRule->store?$priceRule->store->name:'All' }} </td>
                <td> <i class="fa {{ $priceRule->active?'fa-check text-success':'fa-remove text-danger' }}"></i> </td>
                <td style="width: 20%;">
                    <div class="btn-group btn-group-sm">
                        <a class="price-rule-edit-btn btn btn-default" href="#" data-price_rule_edit="{{ route('backend.price_rule.product.mini_form', ['id' => $priceRule->id, 'product_id' => $priceRule->product_id]) }}"><i class="fa fa-pencil"></i> Edit</a>
                        <button class="btn btn-default"
                                data-price_rule_delete="{{ route('backend.price_rule.product.delete', ['id' => $priceRule->id]) }}"
                                data-toggle="confirmation"
                                data-original-title="Are you sure?"
                                data-on-confirm="ProductFormPrice.deletePriceRule"
                                title>
                            <i class="fa fa-trash-o"></i> Delete</button>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>