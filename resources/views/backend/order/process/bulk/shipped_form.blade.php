<?php
$processedCount = count($processedOrders);
$unprocessedCount = count($unprocessedOrders);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
    <h4 class="modal-title">Ship {{ $processedCount.' '.str_plural('Order', $processedCount) }}</h4>
</div>

{!! Form::open(['route' => ['backend.sales.order.bulk_action']]) !!}
<div class="modal-body">
    @if($unprocessedCount > 0)
        <div class="alert alert-danger">
            {{ $unprocessedCount }} {{ str_plural('order', $unprocessedCount) }} can't be set to Shipped.
            <ul>
                @foreach($unprocessedOrders as $unprocessedOrder)
                    <li>{{ $unprocessedOrder->reference }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="form-body">
        @if($processedCount > 0)
            <p>You are about to set {{ $processedCount }} {{ str_plural('order', $processedCount) }} to <em>Shipped</em>.</p>
        @endif

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

<div class="modal-footer text-center">
    <div class="pull-left">
        <div class="checkbox-list text-left">
            <label class="checkbox">
                {!! Form::checkbox('mark_shipped', 1, ProjectHelper::getConfig('delivery_order_options.check_shipped_on_new_delivery_order'), ['id' => 'mark-shipped-checkbox']) !!} Mark as shipped
            </label>

            <label data-enabled-dependent="mark-shipped-checkbox" class="checkbox">
                {!! Form::checkbox('send_notification', 1, true) !!} Send email notification to customer
            </label>
        </div>
    </div>

    <div class="text-right">
        <button name="confirm" value="1" class="btn btn-primary"><i class="fa fa-check"></i> Confirm </button>
        <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-remove"></i> Cancel</button>
        {!! Form::hidden('backUrl', $backUrl) !!}
        {!! Form::hidden('action', 'process:shipped') !!}
        @foreach($processedOrders as $processedOrder)
            {!! Form::hidden('order_id[]', $processedOrder->id) !!}
        @endforeach
    </div>
</div>
{!! Form::close() !!}