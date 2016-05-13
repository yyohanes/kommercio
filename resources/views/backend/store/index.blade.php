@extends('backend.master.layout')

@section('breadcrumb')
    <li>
        <span>Store</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit portlet-datatable bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase"> Stores </span>
                </div>
                <div class="actions">
                    <a href="{{ route('backend.store.create', ['backUrl' => Request::fullUrl()]) }}" class="btn btn-sm btn-info">
                        <i class="fa fa-plus"></i> Add </a>
                </div>
            </div>

            <div class="portlet-body">
                <table class="table table-striped table-bordered table-advance">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Product</th>
                        <th>Warehouse</th>
                        <th style="width: 20%;">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($stores as $store)
                        <tr>
                            <td>{{ $store->name }}</td>
                            <td>{!! \Kommercio\Models\Store::getTypeOptions($store->type) !!}</td>
                            <td>{{ $store->productCount }}</td>
                            <td>
                                <ul>
                                    @foreach($store->warehouses as $warehouse)
                                        <li>{{ $warehouse->name }}</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="text-center">
                                {!! Form::open(['route' => ['backend.store.delete', 'id' => $store->id]]) !!}
                                <div class="btn-group btn-group-sm">
                                    <a class="btn btn-default" href="{{ route('backend.store.edit', ['id' => $store->id, 'backUrl' => Request::fullUrl()]) }}"><i class="fa fa-pencil"></i> Edit</a>
                                    <button class="btn btn-default" data-toggle="confirmation" data-original-title="Are you sure?" title=""><i class="fa fa-trash-o"></i> Delete</button>
                                </div>
                                {!! Form::close() !!}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop