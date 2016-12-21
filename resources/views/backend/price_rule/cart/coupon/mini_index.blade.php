@if($coupons->count() > 0)
    <div class="table-scrollable">
        <table class="table table-hover">
            <thead>
            <tr>
                <th> No </th>
                <th> Code </th>
                <th> Max Usage </th>
                <th> Customer </th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach($coupons as $idx => $coupon)
                <tr>
                    <td>
                        {{ $idx + 1 }}
                    </td>
                    <td>
                        {{ $coupon->coupon_code }}
                    </td>
                    <td>
                        {{ $coupon->max_usage }}
                    </td>
                    <td>
                        {{ $coupon->customer?$coupon->customer->fullName.' ('.$coupon->customer->getProfile()->email.')':'All customers' }}
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            @can('access', ['edit_cart_price_rule'])
                            <a class="coupon-edit-btn btn btn-default" href="#" data-coupon_edit="{{ route('backend.price_rule.cart.coupon.form', ['cart_price_rule_id' => $cartPriceRule->id, 'id' => $coupon->id]) }}"><i class="fa fa-pencil"></i> Edit</a>
                            @endcan
                            @can('access', ['delete_cart_price_rule'])
                            <button class="btn btn-default"
                                    data-coupon_delete="{{ route('backend.price_rule.cart.coupon.delete', ['cart_price_rule_id' => $cartPriceRule->id, 'id' => $coupon->id]) }}"
                                    data-toggle="confirmation"
                                    data-original-title="Are you sure?"
                                    data-on-confirm="cartPriceRuleFormBehaviors.deleteCoupon"
                                    title>
                                <i class="fa fa-trash-o"></i> Delete</button>
                            @endcan
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endif