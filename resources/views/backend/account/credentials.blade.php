@extends('backend.account.settings')

@section('settings_title', 'Credentials')

@section('breadcrumb')
    <li>
        <span>Account</span>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>Credentials</span>
    </li>
@stop

@section('settings_body')
    {!! Form::model($user) !!}
        @include('backend.master.form.fields.email', [
            'name' => 'email',
            'label' => 'Email',
            'key' => 'email',
            'attr' => [
                'class' => 'form-control',
                'id' => 'email'
            ],
            'two_lines' => TRUE,
            'required' => TRUE
        ])

        @include('backend.master.form.fields.password', [
            'name' => 'password',
            'label' => 'Password',
            'key' => 'password',
            'attr' => [
                'class' => 'form-control',
                'id' => 'password'
            ],
            'two_lines' => TRUE
        ])

        @include('backend.master.form.fields.password', [
            'name' => 'password_confirmation',
            'label' => 'Confirm Password',
            'key' => 'password_confirmation',
            'attr' => [
                'class' => 'form-control',
                'id' => 'password_confirmation'
            ],
            'two_lines' => TRUE
        ])

        <div class="margin-top-10">
            <button class="btn btn-primary">Save Changes</button>
            <a href="{{ NavigationHelper::getBackUrl() }}" class="btn default"> Cancel </a>
        </div>
    {!! Form::close() !!}
@stop