@extends('backend.auth.template')

@section('content')
    <!-- BEGIN FORGOT PASSWORD FORM -->
    <form class="login-form" role="form" method="POST" action="{{ route('backend.password.email') }}">
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

        <h3 class="font-green">Forgot Password ?</h3>
        <p> Enter your e-mail address below to reset your password. </p>

        <input class="form-control placeholder-no-fix form-group{{ $errors->has('email') ? ' has-error' : '' }}" type="text" autocomplete="off" placeholder="Email" name="email" />

        <div class="form-actions">
            <a href="{{ route('backend.login_form') }}" type="button" id="back-btn" class="btn grey btn-default">Back</a>
            <button type="submit" class="btn blue btn-success uppercase pull-right">Send Password Reset Link</button>
        </div>
    </form>
    <!-- END FORGOT PASSWORD FORM -->
@endsection