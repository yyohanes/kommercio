<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->

<head>
    <meta charset="utf-8" />
    <title>@yield('title', config('project.client_name'))</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <meta content="Kommercio" name="author" />
    <meta name="theme-color" content="#3b4251" />

    @section('top_styles')
        <!-- BEGIN GLOBAL MANDATORY STYLES -->
        <link href="{{ asset('backend/assets/template/global/plugins/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('backend/assets/template/global/plugins/simple-line-icons/simple-line-icons.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('backend/assets/template/global/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('backend/assets/template/global/plugins/uniform/css/uniform.default.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('backend/assets/template/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('backend/assets/template/global/plugins/fancybox/source/jquery.fancybox.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('backend/assets/template/global/plugins/jquery-file-upload/blueimp-gallery/blueimp-gallery.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('backend/assets/template/global/plugins/jquery-file-upload/css/jquery.fileupload.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('backend/assets/template/global/plugins/jquery-file-upload/css/jquery.fileupload-ui.css') }}" rel="stylesheet" type="text/css" />
        <!-- END GLOBAL MANDATORY STYLES -->
    
        <!-- BEGIN PAGE STYLES -->
        @yield('top_page_styles')
        <!-- END PAGE STYLES -->
        <!-- BEGIN THEME GLOBAL STYLES -->
        <link href="{{ asset('backend/assets/template/global/css/components.min.css') }}" rel="stylesheet" id="style_components" type="text/css" />
        <link href="{{ asset('backend/assets/template/global/css/plugins.min.css') }}" rel="stylesheet" type="text/css" />
        <!-- END THEME GLOBAL STYLES -->

        <!-- BEGIN THEME LAYOUT STYLES -->
        @section('theme_layout_styles')
        <link href="{{ asset('backend/assets/template/layouts/layout/css/layout.min.css') }}" rel="stylesheet" type="text/css" />

        <?php
        $now = \Carbon\Carbon::now();

        $themeColor = 'light';

        if(($now->hour >= 18 && $now->hour <= 23) || ($now->hour <= 6 && $now->hour >= 0)){
            $themeColor = 'darkblue';
        }
        ?>
        <link href="{{ asset('backend/assets/template/layouts/layout/css/themes/'.$themeColor.'.min.css') }}" rel="stylesheet" type="text/css" id="style_color" />
        <link href="{{ asset('backend/assets/template/layouts/layout/css/custom.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('backend/assets/styles/app.css') }}" rel="stylesheet" type="text/css" />
        @show
        <!-- END THEME LAYOUT STYLES -->
    @show
    <link rel="shortcut icon" href="{{ asset('backend/assets/favicon.ico') }}" />
</head>
<!-- END HEAD -->

<body class="@yield('body_class', 'page-header-fixed page-sidebar-fixed page-sidebar-closed-hide-logo page-content-white')">
@section('body_content')
        <!-- BEGIN HEADER -->
<div class="page-header navbar navbar-fixed-top">
    <!-- BEGIN HEADER INNER -->
    <div class="page-header-inner ">
        <!-- BEGIN LOGO -->
        <div class="page-logo">
            <a href="{{ route('backend.dashboard') }}">
                <img src="{{ asset('backend/assets/images/logo-invert.png') }}" class="logo-default" alt="logo" /> </a>
            <div class="menu-toggler sidebar-toggler"> </div>
        </div>
        <!-- END LOGO -->
        <!-- BEGIN RESPONSIVE MENU TOGGLER -->
        <a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse"> </a>
        <!-- END RESPONSIVE MENU TOGGLER -->
        <!-- BEGIN TOP NAVIGATION MENU -->
        <div class="top-menu">
            @if(Auth::check())
            <ul class="nav navbar-nav pull-right">
                @if(config('project.enable_store_selector', false))
                    <li class="dropdown dropdown-user">
                        <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                            <i class="fa fa-home"></i>
                            <span class="username"> {{ $activeStore->name }} </span>
                            @if($otherStores)
                            <i class="fa fa-angle-down"></i>
                            @endif
                        </a>
                        @if($otherStores)
                        <ul class="dropdown-menu dropdown-menu-default">
                            @foreach($otherStores as $otherStore)
                            <li>
                                <a href="{{ route('backend.change_store', ['id' => $otherStore->id, 'backUrl' => Request::getRequestUri()]) }}">{{ $otherStore->name }}</a>
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </li>
                @endif

                <!-- BEGIN USER LOGIN DROPDOWN -->
                <!-- DOC: Apply "dropdown-dark" class after below "dropdown-extended" to change the dropdown styte -->
                <li class="dropdown dropdown-user">
                    <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                        <i class="fa fa-user"></i>
                        <span class="username username-hide-on-mobile"> {{ trim(Auth::user()->fullName)?Auth::user()->fullName:'Account' }} </span>
                        <i class="fa fa-angle-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-default">
                        <li>
                            <a href="{{ route('backend.account.credentials', ['backUrl' => Request::fullUrl()]) }}">
                                <i class="icon-user"></i> My Account </a>
                        </li>
                        <li>
                            <a href="{{ route('backend.logout') }}">
                                <i class="icon-key"></i> Log Out </a>
                        </li>
                    </ul>
                </li>
                <!-- END USER LOGIN DROPDOWN -->
            </ul>
            @endif
        </div>
        <!-- END TOP NAVIGATION MENU -->
    </div>
    <!-- END HEADER INNER -->
</div>
<!-- END HEADER -->
<!-- BEGIN HEADER & CONTENT DIVIDER -->
<div class="clearfix"> </div>
<!-- END HEADER & CONTENT DIVIDER -->
<!-- BEGIN CONTAINER -->
<div class="page-container">
    <!-- BEGIN SIDEBAR MENU -->
    <div class="page-sidebar-wrapper">
        <div class="page-sidebar navbar-collapse collapse">
            @include('backend.master.menu')
            <!-- END SIDEBAR MENU -->
        </div>
    </div>
    <!-- END HEADER MENU -->
    <!-- BEGIN CONTENT -->
    <div class="page-content-wrapper">
        <!-- BEGIN CONTENT BODY -->
        <div class="page-content">
            <!-- BEGIN PAGE HEADER-->
            <!-- BEGIN PAGE BAR -->
            <div class="page-bar">
                <ul class="page-breadcrumb">
                    <li>
                        <a href="{{ route('backend.dashboard') }}">Home</a>
                        <i class="fa fa-circle"></i>
                    </li>
                    @yield('breadcrumb')
                </ul>
            </div>
            <!-- END PAGE BAR -->
            <!-- BEGIN PAGE TITLE-->
            <h3 class="page-title">@yield('page_title')
                <small>@yield('page_description')</small>
            </h3>
            <!-- END PAGE TITLE-->
            <!-- END PAGE HEADER-->

            <div class="row">
                @if(!empty($errors->all()))
                <div class="col-md-12">
                    <div class="alert alert-danger alert-dismissable">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(Session::has('success'))
                    <div class="col-md-12" style="display: none;">
                        @foreach(Session::get('success') as $success)
                        <div class="alert alert-success">
                            {{ $success }}
                        </div>
                        @endforeach
                    </div>
                @endif

                @if(Request::ajax())<div id="ajax-meat">@endif
                @yield('content')
                @if(Request::ajax())</div>@endif

                <div class="modal fade" id="ajax_modal" role="basic" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-body">
                                <img src="{{ asset('backend/assets/template/global/img/loading-spinner-grey.gif') }}" alt="" class="loading">
                                <span> &nbsp;&nbsp;Loading... </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END CONTENT BODY -->
    </div>
    <!-- END CONTENT -->
</div>
<!-- END CONTAINER -->
<!-- BEGIN FOOTER -->
<!-- BEGIN INNER FOOTER -->
<div class="page-footer">
    <div class="page-footer-inner"> {{ date('Y') }} &copy; Powered by Kommercio.</div>
</div>
<div class="scroll-to-top">
    <i class="icon-arrow-up"></i>
</div>
<!-- END INNER FOOTER -->
<!-- END FOOTER -->
@show

<script type="text/javascript">
    var global_vars = {
        base_path: '{{ url('/') }}',
        images_path: '{{ config('kommercio.images_path') }}',
        asset_path: '{{ asset('/backend/assets') }}',
        max_upload_size: {{ ProjectHelper::getMaxUploadSize() }},
        default_currency: '{{ CurrencyHelper::getCurrentCurrency()['code'] }}',
        currencies: {!! json_encode(CurrencyHelper::getActiveCurrencies()) !!},
        line_item_total_precision: {{ config('project.line_item_total_precision') }},
        total_precision: {{ config('project.total_precision') }},
        total_rounding: '{{ config('project.total_rounding') }}',
        csrf_token: '{{ csrf_token() }}',
        current_path: '{{ Request::fullUrl() }}'
    };
</script>

@section('bottom_scripts')
    <!--[if lt IE 9]>
    <script src="{{ asset('backend/assets/template/global/plugins/respond.min.js') }}"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/excanvas.min.js') }}"></script>
    <![endif]-->
    <!-- BEGIN CORE PLUGINS -->
    <script src="{{ asset('backend/assets/template/global/plugins/jquery.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/bootstrap/js/bootstrap.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/js.cookie.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/jquery.blockui.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/uniform/jquery.uniform.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/bootstrap-confirmation/bootstrap-confirmation.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/bootstrap-growl/jquery.bootstrap-growl.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/fancybox/source/jquery.fancybox.pack.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/jquery-validation/js/jquery.validate.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/jquery-validation/js/additional-methods.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/jquery-file-upload/js/vendor/jquery.ui.widget.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/jquery-file-upload/js/vendor/load-image.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/jquery-file-upload/js/vendor/canvas-to-blob.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/jquery-file-upload/js/jquery.iframe-transport.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/jquery-file-upload/js/jquery.fileupload.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/jquery-file-upload/js/jquery.fileupload-process.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/jquery-file-upload/js/jquery.fileupload-image.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/jquery-file-upload/js/jquery.fileupload-audio.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/jquery-file-upload/js/jquery.fileupload-video.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/jquery-file-upload/js/jquery.fileupload-validate.js') }}" type="text/javascript"></script>
    <!-- END CORE PLUGINS -->
    <!-- BEGIN PAGE SCRIPTS -->
    @yield('bottom_page_scripts')
    <!-- END PAGE SCRIPTS -->

    <!-- BEGIN THEME GLOBAL SCRIPTS -->
    <script src="{{ asset('backend/assets/template/global/scripts/app.min.js') }}" type="text/javascript"></script>
    <!-- END THEME GLOBAL SCRIPTS -->
    <!-- BEGIN THEME LAYOUT SCRIPTS -->
    <script src="{{ asset('backend/assets/template/layouts/layout/scripts/layout.min.js') }}" type="text/javascript"></script>
    <!-- END THEME LAYOUT SCRIPTS -->

    <!-- BEGIN MODIFIED SCRIPTS -->
    <script src="{{ asset('backend/assets/scripts/plugins/jquery-migrate/jquery-migrate.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/scripts/plugins/jquery.ba-bbq/jquery.ba-bbq.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/scripts/plugins/mathjs/math.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/scripts/app.js') }}" type="text/javascript"></script>
    <!-- END MODIFIED SCRIPTS -->
@show
</body>

</html>