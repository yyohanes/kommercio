<div class="portlet light bordered">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject">Delivery Order #{{ $deliveryOrder->reference }}</span>
        </div>
    </div>

    <div class="portlet-body">
        {!! Form::open(['route' => ['backend.sales.order.delivery_order.mini_save', 'id' => $deliveryOrder->id], 'class' => 'form-horizontal']) !!}
        <table class="table">
            <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Delivered</th>
                <th>Ordered</th>
            </tr>
            </thead>
            <tbody>
            @foreach($order->getProductLineItems() as $productLineItem)
                <tr>
                    <td>{{ $productLineItem->name }}</td>
                    <td>
                        @php
                            $max = $productLineItem->quantity - $productLineItem->shippedQuantity;
                        @endphp
                        @include('backend.master.form.fields.number', [
                            'name' => 'line_items['.$productLineItem->id.'][quantity]',
                            'label' => FALSE,
                            'key' => 'line_items.'.$productLineItem->id.'.quantity',
                            'attr' => [
                                'class' => 'form-control input-sm',
                                'id' => 'line_items['.$productLineItem->id.'][quantity]',
                                'data-rule-required' => 'true',
                                'data-rule-min' => 0,
                                'data-rule-max' => $max
                            ],
                            'two_lines' => TRUE,
                            'required' => TRUE,
                            'defaultValue' => $max
                        ])
                    </td>
                    <td>{{ $productLineItem->deliveredQuantity }}</td>
                    <td>{{ $productLineItem->quantity }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <hr/>

        @include('backend.master.form.fields.textarea', [
            'name' => 'notes',
            'label' => 'Notes',
            'key' => 'notes',
            'attr' => [
                'class' => 'form-control',
                'id' => 'notes',
                'rows' => 4,
                'placeholder' => 'Notes'
            ],
        ])

        <div class="clearfix"></div>

        <hr/>

        @include('backend.master.form.fields.text', [
            'name' => 'tracking_number',
            'label' => 'Tracking Number (if any)',
            'key' => 'tracking_number',
            'attr' => [
                'class' => 'form-control',
                'id' => 'tracking_number',
            ]
        ])

        <div class="clearfix"></div>

        @include('backend.master.form.fields.text', [
            'name' => 'delivered_by',
            'label' => 'Delivered by (if any)',
            'key' => 'delivered_by',
            'attr' => [
                'class' => 'form-control',
                'id' => 'delivered_by',
            ]
        ])

        <div class="margin-top-15 text-center">
            <button id="delivery-order-save" data-payment_save="{{ route('backend.sales.order.delivery_order.mini_save', ['id' => $deliveryOrder->id]) }}" class="btn btn-info"><i class="fa fa-save"></i> Save</button>
            <button id="delivery-order-cancel" class="btn btn-default"><i class="fa fa-remove"></i> Cancel</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>