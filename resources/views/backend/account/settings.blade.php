@extends('backend.master.form_template')

@section('page_title', 'Account Settings')

@section('page_description', 'Manage your account profile page')

@section('top_page_styles')
    <link href="{{ asset('backend/assets/template/pages/css/profile.min.css') }}" rel="stylesheet" type="text/css" />
@stop

@section('content')
    <div class="col-md-12">
        <!-- BEGIN PROFILE CONTENT -->
        <div class="profile-content">
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet light bordered">
                        <div class="portlet-title tabbable-line">
                            <div class="caption caption-md">
                                <i class="icon-globe theme-font hide"></i>
                                <span class="caption-subject font-blue-madison bold uppercase">@yield('settings_title')</span>
                            </div>
                            <ul class="nav nav-tabs">
                                <li class="{{ NavigationHelper::activeClass('backend.account.credentials')?'active':'' }}">
                                    <a href="{{ route('backend.account.credentials') }}">Change Email & Password</a>
                                </li>
                                <li class="{{ NavigationHelper::activeClass('backend.account.profile')?'active':'' }}">
                                    <a href="{{ route('backend.account.profile') }}">Profile</a>
                                </li>
                            </ul>
                        </div>
                        <div class="portlet-body">
                            @yield('settings_body')
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END PROFILE CONTENT -->
    </div>
@stop

