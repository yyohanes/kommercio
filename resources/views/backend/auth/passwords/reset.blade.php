@extends('backend.auth.template')

@section('content')
    <form class="login-form" role="form" method="POST" action="{{ route('backend.password.reset') }}">
        {!! csrf_field() !!}

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if(!empty($errors->all()))
            <div class="alert alert-danger">
                <button class="close" data-close="alert"></button>
                @foreach($errors->all() as $error)
                    <span>{{ $error }}</span><br/>
                @endforeach
            </div>
        @endif

        <input type="hidden" name="token" value="{{ $token }}">

        <h3 class="font-green">Reset Password</h3>
        <p> Enter your new password below </p>

        <input value="{{ $email or old('email') }}" class="form-control placeholder-no-fix form-group{{ $errors->has('email') ? ' has-error' : '' }}" type="text" autocomplete="off" placeholder="Email" name="email" />
        @if ($errors->has('email'))
            <span class="help-block">
                    <strong>{{ $errors->first('email') }}</strong>
                </span>
        @endif

        <input placeholder="Password" type="password" class="form-control placeholder-no-fix form-group{{ $errors->has('password') ? ' has-error' : '' }}" name="password">

        @if ($errors->has('password'))
            <span class="help-block">
                <strong>{{ $errors->first('password') }}</strong>
            </span>
        @endif

        <input placeholder="Repeat Password" type="password" class="form-control placeholder-no-fix form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}" name="password_confirmation">

        @if ($errors->has('password'))
            <span class="help-block">
                <strong>{{ $errors->first('password_confirmation') }}</strong>
            </span>
        @endif

        <div class="clearfix"></div>

        <div class="form-actions">
            <a href="{{ route('backend.login_form') }}" type="button" id="back-btn" class="btn grey btn-default">Back</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-btn fa-refresh"></i>Reset Password
            </button>
        </div>
    </form>
@endsection
