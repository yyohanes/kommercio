@extends('backend.auth.template')

@section('content')
    <form action="{{ route('backend.login') }}" class="login-form" method="post">
        {!! csrf_field() !!}

        @if(!empty($errors->all()))
            <div class="alert alert-danger">
                <button class="close" data-close="alert"></button>
                @foreach($errors->all() as $error)
                    <span>{{ $error }}</span><br/>
                @endforeach
            </div>
        @endif

        <div class="row">
            <div class="col-xs-6">
                <input class="form-control form-control-solid placeholder-no-fix form-group{{ $errors->has('email') ? ' has-error' : '' }}" type="text" autocomplete="off" placeholder="Email" name="email" required/> </div>
            <div class="col-xs-6">
                <input class="form-control form-control-solid placeholder-no-fix form-group{{ $errors->has('password') ? ' has-error' : '' }}" type="password" autocomplete="off" placeholder="Password" name="password" required/> </div>
        </div>
        <div class="row">
            <div class="col-sm-4">
                <div class="rem-password">
                    <label>Remember Me
                        <input type="checkbox" name="remember" class="rem-checkbox" />
                    </label>
                </div>
            </div>
            <div class="col-sm-8 text-right">
                <div class="forgot-password">
                    <a href="{{ route('backend.password.form') }}" id="forget-password" class="forget-password">Forgot Password?</a>
                </div>
                <button class="btn blue" type="submit">Sign In</button>
            </div>
        </div>
    </form>
@stop