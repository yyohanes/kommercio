@extends('backend.account.settings')

@section('settings_title', 'Profile')

@section('breadcrumb')
    <li>
        <span>Account</span>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>Profile</span>
    </li>
@stop

@section('settings_body')
    {!! Form::model($user) !!}
        @include('backend.master.form.fields.text', [
            'name' => 'profile[full_name]',
            'label' => 'Full Name',
            'key' => 'profile.full_name',
            'attr' => [
                'class' => 'form-control',
                'id' => 'profile[full_name]'
            ],
            'two_lines' => TRUE,
            'required' => TRUE
        ])

        @include('backend.master.form.fields.text', [
            'name' => 'profile[phone_number]',
            'label' => 'Phone Number',
            'key' => 'profile.phone_number',
            'attr' => [
                'class' => 'form-control',
                'id' => 'profile[phone_number]'
            ],
            'two_lines' => TRUE,
        ])

        <div class="margin-top-10">
            <button class="btn btn-primary">Save Changes</button>
            <a href="{{ NavigationHelper::getBackUrl() }}" class="btn default"> Cancel </a>
        </div>
    {!! Form::close() !!}
@stop