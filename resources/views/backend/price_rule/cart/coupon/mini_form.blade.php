<div class="portlet light bordered">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject">{{ $coupon?$coupon->coupon_code:'Add' }} Coupon Code</span>
        </div>
    </div>

    <div class="portlet-body">
        {!! Form::open(['route' => ['backend.price_rule.cart.coupon.save', 'cart_price_rule_id' => $cartPriceRule->id], 'class' => 'form-horizontal']) !!}
        @include('backend.master.form.fields.text', [
            'name' => 'coupon_code',
            'label' => 'Coupon Code',
            'required' => TRUE,
            'key' => 'coupon_code',
            'attr' => [
                'class' => 'form-control',
                'id' => 'coupon_code',
            ],
            'defaultValue' => $coupon?$coupon->coupon_code:null,
        ])

        @include('backend.master.form.fields.number', [
            'name' => 'max_usage',
            'label' => 'Max Usage',
            'key' => 'max_usage',
            'attr' => [
                'class' => 'form-control',
                'id' => 'max_usage'
            ],
            'unitPosition' => 'front',
            'defaultValue' => $coupon?$coupon->max_usage:null,
            'help_text' => 'If left empty, it is considered Unlimited.'
        ])


        @include('backend.master.form.fields.text', [
            'name' => 'customer',
            'label' => 'Customer',
            'key' => 'customer',
            'attr' => [
                'class' => 'form-control',
                'id' => 'customer',
                'data-typeahead_remote' => route('backend.customer.autocomplete'),
                'data-typeahead_display' => 'full_name',
                'data-typeahead_label' => 'name',
                'placeholder' => 'Search Customer'
            ],
            'defaultValue' => $coupon && $coupon->customer?$coupon->customer->fullName:null,
        ])

        {!! Form::hidden('customer_id', null, ['id' => 'customer-id-value']) !!}
        {!! Form::hidden('coupon_id', $coupon?$coupon->id:null) !!}

        <div class="margin-top-15 text-center">
            <button id="coupon-save" data-coupon_save="{{ route('backend.price_rule.cart.coupon.save', ['cart_price_rule_id' => $cartPriceRule->id]) }}" class="btn btn-info"><i class="fa fa-save"></i> Save</button>
            <a id="coupon-cancel" class="btn btn-default" href="#"><i class="fa fa-remove"></i> Cancel</a>
        </div>
        {!! Form::close() !!}
    </div>
</div>