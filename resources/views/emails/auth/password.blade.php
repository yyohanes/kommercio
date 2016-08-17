@extends('emails.master.default')

@section('content')
        <!-- content -->
<div class="content">
    <table bgcolor="" class="social" width="100%">
        <tr>
            <td>
                <h1>LINK TO RESET YOUR PASSWORD</h1>

                <p class="text">Dear {{ $user->customer->fullName }},</p>
                <p class="text">
                    Click here to reset your password: <a href="{{ $link = url('password/reset', $token).'?email='.urlencode($user->getEmailForPasswordReset()) }}"> {{ $link }} </a>
                </p>
            </td>
        </tr>
    </table>
</div>
@stop