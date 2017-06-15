<div class="portlet light">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject">Delivery Order #{{ $deliveryOrder->reference }}</span>
        </div>
    </div>

    <div class="portlet-body">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th colspan="2">Product</th>
                <th>Quantity</th>
            </tr>
            </thead>
            <tbody>
            @if($deliveryOrder->items->count() > 0)
                @foreach($deliveryOrder->items as $idx=>$doItem)
                    @include('emails.delivery_order.line_items.product', ['key' => $idx, 'doItem' => $doItem, 'lineItem' => $doItem->lineItem, 'showPrice' => FALSE])
                @endforeach
            @endif
            </tbody>
        </table>

        <div class="row">
            <div class="col-md-6">
                <div class="portlet light bordered">
                    <div class="portlet-body">
                        <div class="row static-info">
                            <div class="col-md-4 name"> Email: </div>
                            <div class="col-md-8 value"> {{ $shippingProfile->email }} </div>
                        </div>

                        <div class="row static-info">
                            <div class="col-md-4 name"> Name: </div>
                            <div class="col-md-8 value"> {{ $shippingProfile->full_name }} </div>
                        </div>

                        <div class="row static-info">
                            <div class="col-md-4 name"> Phone Number: </div>
                            <div class="col-md-8 value"> {{ $shippingProfile->phone_number }} </div>
                        </div>

                        <div class="row static-info">
                            <div class="col-md-4 name"> Address: </div>
                            <div class="col-md-8 value"> {!! AddressHelper::printAddress($shippingProfile->getDetails()) !!} </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="portlet light bordered">
                    <div class="portlet-body">
                        <div class="row static-info">
                            <div class="col-md-5 name"> Notes: </div>
                            <div class="col-md-7 value">
                                @if(!empty($deliveryOrder->notes))
                                    {!! nl2br($deliveryOrder->notes) !!}
                                @else
                                    -
                                @endif
                            </div>
                        </div>

                        <div class="row static-info">
                            <div class="col-md-5 name"> Tracking Number: </div>
                            <div class="col-md-7 value">
                                @if(!empty($deliveryOrder->getData('tracking_number', null)))
                                    {{ $deliveryOrder->getData('tracking_number') }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>

                        <div class="row static-info">
                            <div class="col-md-5 name"> Delivered by: </div>
                            <div class="col-md-7 value">
                                @if(!empty($deliveryOrder->getData('delivered_by', null)))
                                    {{ $deliveryOrder->getData('delivered_by') }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <ul class="list-group">
                    @foreach($deliveryOrder->getHistory() as $history)
                        <li class="list-group-item">
                            Status set to <span class="label bg-{{ OrderHelper::getDeliveryOrderStatusLabelClass($history->value) }}">{{ \Kommercio\Models\Order\DeliveryOrder\DeliveryOrder::getStatusOptions($history->value) }}</span> by {{ $history->author }}<br/>
                            @if($history->notes)
                                <pre>Reason: {!! nl2br($history->notes) !!}</pre>
                            @endif
                            <span class="badge badge-default">{{ $history->created_at->format('d-m-Y H:i') }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>