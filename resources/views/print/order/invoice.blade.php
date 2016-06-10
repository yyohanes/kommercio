@extends('print.master.default')

@section('content')
    <table class="no-border">
        <tr>
            <td>
                <h4>
                    {{ config('project.client_name', config('kommercio.default_name')) }}
                </h4>
            </td>

            <td class="text-right" style="vertical-align: middle;">
                {{ $order->reference }}
            </td>
        </tr>
    </table>

    @include('print.order.order_table', ['order' => $order])
@stop