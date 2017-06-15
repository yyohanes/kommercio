@extends('print.master.default')

@section('content')
    @foreach($orders as $order)
        @foreach($order->deliveryOrders as $deliveryOrder)
            <div class="page-break"></div>
            @include($print_template, ['order' => $order, 'deliveryOrder' => $deliveryOrder])
        @endforeach
    @endforeach
@endsection