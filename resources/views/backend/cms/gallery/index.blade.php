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
        <span>Gallery</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit portlet-datatable bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase"> Gallery </span>
                </div>
                <div class="actions">
                    @can('access', ['create_gallery'])
                    <a href="{{ route('backend.cms.gallery.create', ['backUrl' => Request::fullUrl()]) }}" class="btn btn-sm btn-info">
                        <i class="fa fa-plus"></i> Add </a>
                    @endcan
                </div>
            </div>

            <div class="portlet-body">
                <table class="table table-striped table-bordered table-advance" id="gallery-dataset" data-form_token="{{ csrf_token() }}">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th style="width: 100px;">Image</th>
                        <th>Category</th>
                        <th>Created At</th>
                        <th style="width: 10%;">Active</th>
                        <th style="width: 20%;">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($galleries as $gallery)
                        <tr>
                            <td>{{ $gallery->name }} (ID: {{ $gallery->id }})</td>
                            <td>
                                @if($gallery->thumbnail)
                                    <img class="img-responsive" src="{{ asset($gallery->thumbnail->getImagePath('backend_thumbnail')) }}" />
                                @endif
                            </td>
                            <td>
                                {{ implode(',', $gallery->galleryCategories->pluck('name')->all()) }}
                            </td>
                            <td>
                                {{ $gallery->created_at?$gallery->created_at->format('d M Y H:i'):null }}
                            </td>
                            <td><i class="fa fa-{{ $gallery->active?'check text-success':'remove text-danger' }}"></i></td>
                            <td class="text-center">
                                {!! Form::open(['route' => ['backend.cms.gallery.delete', 'id' => $gallery->id]]) !!}
                                <div class="btn-group btn-group-sm">
                                    @can('access', ['edit_gallery'])
                                    <a class="btn btn-default" href="{{ route('backend.cms.gallery.edit', ['id' => $gallery->id, 'backUrl' => Request::fullUrl()]) }}"><i class="fa fa-pencil"></i> Edit</a>
                                    @endcan
                                    @can('access', ['delete_gallery'])
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