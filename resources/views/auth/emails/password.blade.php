@if($user->isCustomer)
    <?php
    $viewTemplate = ProjectHelper::getViewTemplate('emails.auth.password');
    ?>
    @include($viewTemplate)
@else
    @include('backend.auth.emails.password')
@endif