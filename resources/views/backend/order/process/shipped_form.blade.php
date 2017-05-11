<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
    <h4 class="modal-title">Ship Order</h4>
</div>

{!! Form::open(['route' => ['backend.sales.order.process', 'process' => 'shipped', 'id' => $order->id], 'class' => 'form-client-validation']) !!}
<div class="modal-body">
    <div class="form-body">
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

        <div class="clearfix"></div>
    </div>
</div>
<div class="modal-footer">
    <div class="text-center">
        <button class="btn btn-primary"><i class="fa fa-check"></i> Confirm </button>
        <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-remove"></i> Cancel</button>
        {!! Form::hidden('backUrl', $backUrl.'#tab_delivery_orders') !!}
    </div>
</div>
{!! Form::close() !!}