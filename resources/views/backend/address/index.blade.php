@extends('backend.master.layout')

@section('top_page_styles')
<link href="{{ asset('backend/assets/template/global/plugins/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('backend/assets/template/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css') }}" rel="stylesheet" type="text/css" />
@stop

@section('bottom_page_scripts')
    <script src="{{ asset('backend/assets/template/global/scripts/datatable.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js') }}" type="text/javascript"></script>

    <script src="{{ asset('backend/assets/scripts/pages/table_reorder.js') }}" type="text/javascript"></script>
@stop

@section('breadcrumb')
    <li>
        <span>Configuration</span>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>Address</span>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>{{ $pageTitle }}</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit portlet-datatable bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase">{{ $pageTitle }}</span>
                </div>
                <div class="actions">
                    @can('access', ['create_address'])
                    <a href="{{ route('backend.configuration.address.create', ['type' => $type, 'parent_id' => $parentObj?$parentObj->id:null, 'backUrl' => Request::fullUrl()]) }}" class="btn btn-sm btn-info">
                        <i class="fa fa-plus"></i> Add </a>
                    @endcan
                </div>
            </div>

            <div class="portlet-body">
                <table class="table table-striped table-bordered table-advance dataset-reorder" id="addresses-dataset" data-form_token="{{ csrf_token() }}" data-row_class="address-name" data-row_value="address_id" data-reorder_action="{{ route('backend.configuration.address.reorder', ['type' => $type, 'parent_id' => $parentObj?$parentObj->id:null]) }}">
                    <thead>
                    <tr>
                        <th>Name</th>
                        @if($type != 'country')
                            <th>{{ ucfirst($parentObj->addressType) }}</th>
                        @else
                            <th>ISO Code</th>
                            <th>Country Code</th>
                        @endif
                        <th>Active</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($addresses as $address)
                        <tr>
                            <td><i class="fa fa-reorder"></i> <span class="address-name" data-address_id="{{ $address->id }}">{{ $address->name }}</span></td>
                            @if($type != 'country')
                                <td>{{ $address->getParent()->name }}</td>
                            @else
                                <td>{{ $address->iso_code }}</td>
                                <td>+{{ $address->country_code }}</td>
                            @endif
                            <td><i class="fa fa-{{ $address->active?'check text-success':'remove text-danger' }}"></i></td>
                            <td>
                                {!! Form::open(['route' => ['backend.configuration.address.delete', 'type' => $type, 'id' => $address->id]]) !!}
                                <div class="btn-group btn-group-sm">
                                    @if($address->has_descendant)
                                    <a class="btn btn-default" href="{{ route('backend.configuration.address.index', ['parent_id' => $address->id, 'type' => $address->childType]) }}"><i class="fa fa-eye"></i> View {{ ucfirst(str_plural($address->childType)) }}</a>
                                    @endif

                                    @can('access', ['edit_address'])
                                    <a class="btn btn-default" href="{{ route('backend.configuration.address.edit', ['id' => $address->id, 'type' => $type, 'backUrl' => Request::fullUrl()]) }}"><i class="fa fa-pencil"></i> Edit</a>
                                    @endcan

                                    @can('access', ['delete_address'])
                                    <button class="btn btn-default" data-toggle="confirmation" data-original-title="Are you sure?" title=""><i class="fa fa-trash-o"></i> Delete</button>
                                    @endcan
                                </div>
                                {!! Form::close() !!}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($parentObj)
            <a href="{{ route('backend.configuration.address.index', ['type' => $parentObj->addressType, 'parent_id' => $parentObj->parentType?$parentObj->getParent()->id:null]) }}" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
        @endif
    </div>
@stop