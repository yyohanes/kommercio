@extends('backend.master.layout')

@section('top_page_styles')
<link href="{{ asset('backend/assets/template/global/plugins/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('backend/assets/template/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css') }}" rel="stylesheet" type="text/css" />
@stop

@section('bottom_page_scripts')
    <script src="{{ asset('backend/assets/template/global/scripts/datatable.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js') }}" type="text/javascript"></script>
@stop

@section('breadcrumb')
    <li>
        <span>CMS</span>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>Menu</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit portlet-datatable bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase"> Menu </span>
                </div>
                <div class="actions">
                    @can('access', ['create_page'])
                    <a href="{{ route('backend.cms.menu.create', ['backUrl' => Request::getRequestUri()]) }}" class="btn btn-sm btn-info">
                        <i class="fa fa-plus"></i> Add </a>
                    @endcan
                </div>
            </div>

            <div class="portlet-body">
                <table class="table table-striped table-bordered table-advance" id="menus-dataset">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Machine Name</th>
                        <th>Description</th>
                        <th style="width: 20%;">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($menus as $menu)
                        <tr>
                            <td><a href="{{ route('backend.cms.menu_item.index', ['menu_id' => $menu->id]) }}" class="btn btn-sm blue-madison">{{ $menu->name }}</a></td>
                            <td>{{ $menu->slug }}</td>
                            <td>{!! nl2br($menu->description) !!}</td>
                            <td class="text-center">
                                {!! Form::open(['route' => ['backend.cms.menu.delete', 'id' => $menu->id]]) !!}
                                <div class="btn-group btn-group-sm">
                                    @can('access', ['edit_menu'])
                                    <a class="btn btn-default" href="{{ route('backend.cms.menu.edit', ['id' => $menu->id, 'backUrl' => Request::fullUrl()]) }}"><i class="fa fa-pencil"></i> Edit</a>
                                    @endcan
                                    @can('access', ['delete_menu'])
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
    </div>
@stop