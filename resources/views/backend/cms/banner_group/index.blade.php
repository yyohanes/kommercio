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
        <span>Banner Groups</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit portlet-datatable bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase"> Banner Groups </span>
                </div>
                <div class="actions">
                    @can('access', ['create_product_attribute'])
                    <a href="{{ route('backend.cms.banner_group.create', ['backUrl' => Request::fullUrl()]) }}" class="btn btn-sm btn-info">
                        <i class="fa fa-plus"></i> Add </a>
                    @endcan
                </div>
            </div>

            <div class="portlet-body">
                <table class="table table-striped table-bordered table-advance" id="banner-groups-dataset">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th style="width: 10%;">Banners</th>
                        <th style="width: 20%;">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($bannerGroups as $bannerGroup)
                        <tr>
                            <td><a href="{{ route('backend.cms.banner.index', ['banner_group_id' => $bannerGroup->id]) }}" class="btn btn-sm blue-madison">{{ $bannerGroup->name }}</a></td>
                            <td>{{ $bannerGroup->slug }}</td>
                            <td>{!! nl2br($bannerGroup->description) !!}</td>
                            <td>{{ $bannerGroup->banners->count() }}</td>
                            <td class="text-center">
                                {!! Form::open(['route' => ['backend.cms.banner_group.delete', 'id' => $bannerGroup->id]]) !!}
                                <div class="btn-group btn-group-sm">
                                    @can('access', ['edit_banner_group'])
                                    <a class="btn btn-default" href="{{ route('backend.cms.banner_group.edit', ['id' => $bannerGroup->id, 'backUrl' => Request::fullUrl()]) }}"><i class="fa fa-pencil"></i> Edit</a>
                                    @endcan
                                    @can('access', ['delete_banner_group'])
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