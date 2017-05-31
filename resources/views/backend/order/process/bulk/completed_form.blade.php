<?php
$processedCount = count($processedOrders);
$unprocessedCount = count($unprocessedOrders);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
    <h4 class="modal-title">Complete {{ $processedCount.' '.str_plural('Order', $processedCount) }}</h4>
</div>

{!! Form::open(['route' => ['backend.sales.order.bulk_action']]) !!}
<div class="modal-body">
    <div class="form-body">
        @if($unprocessedCount > 0)
            <div class="alert alert-danger">
                {{ $unprocessedCount }} {{ str_plural('order', $unprocessedCount) }} can't be set to Completed.
                <ul>
                    @foreach($unprocessedOrders as $unprocessedOrder)
                        <li>{{ $unprocessedOrder->reference }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($processedCount > 0)
            <p>You are about to set {{ $processedCount }} {{ str_plural('order', $processedCount) }} to <em>Completed</em>.</p>
        @endif
    </div>
</div>

<div class="modal-footer text-center">
    <div class="pull-left">
        <div class="checkbox-list">
            <label class="checkbox-inline">
                {!! Form::checkbox('send_notification', 1, true) !!} Send email notification to customer
            </label>
        </div>
    </div>

    <div class="pull-right">
        <button name="confirm" value="1" class="btn btn-primary"><i class="fa fa-check"></i> Confirm </button>
        <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-remove"></i> Cancel</button>
        {!! Form::hidden('backUrl', $backUrl) !!}
        {!! Form::hidden('action', 'process:completed') !!}
        @foreach($processedOrders as $processedOrder)
            {!! Form::hidden('order_id[]', $processedOrder->id) !!}
        @endforeach
    </div>
</div>
{!! Form::close() !!}