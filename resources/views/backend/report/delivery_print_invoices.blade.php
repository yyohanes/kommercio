@extends('print.master.default')

@section('content')
    @foreach($orders as $order)
        <div class="page-break"></div>
        @include($print_template, ['order' => $order])
    @endforeach
@endsection