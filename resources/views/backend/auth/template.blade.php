@extends('backend.master.layout')

@section('body_class', 'login')

@section('top_styles')
    @parent

    <!-- BEGIN PAGE LEVEL STYLES -->
    <link href="{{ asset('backend/assets/template/global/plugins/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/assets/template/global/plugins/select2/css/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/assets/template/pages/css/login-5.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- END PAGE LEVEL STYLES -->
@stop

@section('theme_layout_styles')
@stop

@section('bottom_scripts')
    @parent

    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <script src="{{ asset('backend/assets/template/global/plugins/jquery-validation/js/jquery.validate.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/jquery-validation/js/additional-methods.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/select2/js/select2.full.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/backstretch/jquery.backstretch.min.js') }}" type="text/javascript"></script>
    <!-- END PAGE LEVEL PLUGINS -->

    <!-- BEGIN PAGE LEVEL SCRIPTS -->
    <script src="{{ asset('backend/assets/scripts/pages/login.js') }}" type="text/javascript"></script>
    <!-- END PAGE LEVEL SCRIPTS -->
@stop

@section('body_content')
<!-- BEGIN : LOGIN PAGE 5-2 -->
<div class="user-login-5">
    <div class="row bs-reset">
        <div class="col-md-6 login-container bs-reset">
            <img class="login-logo login-6" src="{{ asset('backend/assets/images/logo.png') }}" />
            <div class="login-content">
                <h1>{{ env('PROJECT_NAME') }} Back Office</h1>

                @yield('content')
            </div>

            <div class="login-footer">
                <div class="row bs-reset">
                    <div class="col-xs-5 bs-reset">
                        <ul class="login-social">
                            <li>
                                <a href="https://instagram.com/kommercio">
                                    <i class="fa fa-instagram"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="col-xs-7 bs-reset">
                        <div class="login-copyright text-right">
                            <p>Copyright &copy; <a href="http://kommercio.id" target="_blank">Kommercio</a> {{ date('Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 bs-reset">
            <div class="login-bg"> </div>
        </div>
    </div>
</div>
<!-- END : LOGIN PAGE 5-2 -->
@stop