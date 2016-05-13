@extends('backend.master.layout')

@section('page_title', 'Dashboard')

@section('breadcrumb')
    <li>
        <span>Dashboard</span>
    </li>
@stop

@section('content')
    <?php $user = Auth::user(); ?>
    <div class="col-md-12">Welcome {{ $user->first_name }}</div>
@stop