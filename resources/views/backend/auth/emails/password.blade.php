<?php
$link = route('backend.password.form', ['token' => $token, 'email' => $user->getEmailForPasswordReset()]);
?>
Click here to reset your password: <a href="{{ $link }}"> {{ $link }} </a>
