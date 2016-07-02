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
        <span>CMS</span>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <a href="{{ route('backend.cms.banner_group.index') }}"><span>Banner Groups</span></a>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>{{ $bannerGroup->name }} Banners</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit portlet-datatable bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase">{{ $bannerGroup->name }} Banners</span>
                </div>
                <div class="actions">
                    <a href="{{ route('backend.cms.banner.create', ['banner_group_id' => $bannerGroup->id, 'backUrl' => Request::getRequestUri()]) }}" class="btn btn-sm btn-info">
                        <i class="fa fa-plus"></i> Add </a>
                </div>
            </div>

            <div class="portlet-body">
                <table class="table table-striped table-bordered table-advance dataset-reorder" id="banners-dataset" data-form_token="{{ csrf_token() }}" data-row_class="banner-name" data-row_value="banner_id" data-reorder_action="{{ route('backend.cms.banner.reorder') }}">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th style="width: 100px;">Image</th>
                        <th>Status</th>
                        <th style="width: 20%;">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($banners as $banner)
                        <tr>
                            <td><i class="fa fa-reorder"></i> <span class="banner-name" data-banner_id="{{ $banner->id }}">{{ $banner->name }}</span></td>
                            <td>
                                @if($banner->image)
                                <img class="img-responsive" src="{{ asset($banner->image->getImagePath('backend_thumbnail')) }}" />
                                @endif
                            </td>
                            <td><i class="fa fa-{{ $banner->active?'check text-success':'remove text-danger' }}"></i></td>
                            <td class="text-center">
                                {!! Form::open(['route' => ['backend.cms.banner.delete', 'id' => $banner->id]]) !!}
                                <div class="btn-group btn-group-sm">
                                    <a class="btn btn-default" href="{{ route('backend.cms.banner.edit', ['id' => $banner->id, 'backUrl' => Request::getRequestUri()]) }}"><i class="fa fa-pencil"></i> Edit</a>
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

        <a href="{{ route('backend.cms.banner_group.index') }}" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
    </div>
@stop