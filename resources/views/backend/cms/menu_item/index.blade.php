@extends('backend.master.layout')

@section('top_page_styles')
<link href="{{ asset('backend/assets/template/global/plugins/jquery-nestable/jquery.nestable.css') }}" rel="stylesheet" type="text/css" />
@stop

@section('bottom_page_scripts')
    @parent

    <script>
        global_vars.reorder_path = '{{ route('backend.cms.menu_item.reorder', ['menu_id' => $menu->id]) }}';
    </script>

    <script src="{{ asset('backend/assets/template/global/plugins/jquery-nestable/jquery.nestable.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/scripts/pages/menu_index.js') }}" type="text/javascript"></script>
@stop

@section('breadcrumb')
    <li>
        <span>CMS</span>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <a href="{{ route('backend.cms.menu.index') }}"><span>Menus</span></a>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>{{ $menu->name }}</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit portlet-datatable bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase"> {{ $menu->name }} </span>
                </div>
                <div class="actions">
                    @can('access', ['create_menu_item'])
                    <a href="{{ route('backend.cms.menu_item.create', ['menu_id' => $menu->id, 'backUrl' => Request::getRequestUri()]) }}" class="btn btn-sm btn-info">
                        <i class="fa fa-plus"></i> Add </a>
                    @endcan
                </div>
            </div>

            <div class="portlet-body">
                <div class="dd" id="menu-items">
                    @include('backend.cms.menu_item.child_row', ['menuItems' => $menuItems])
                </div>
            </div>
        </div>
    </div>
@stop