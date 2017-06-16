{!! Form::open(['route' => ['backend.sales.order.quick_update', 'type' => $type, 'id' => $order->id], 'id' => 'form-quick-update-'.$type.'-'.$order->id]) !!}
    @include('backend.order.quick_update.form.'.$type)
{!! Form::close() !!}