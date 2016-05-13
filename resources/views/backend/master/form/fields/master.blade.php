<?php
$required = isset($required)?$required:FALSE;

if(empty($label)){
    $valueColumnClass = isset($valueColumnClass)?$valueColumnClass:'col-md-12';
}
?>

@if(isset($two_lines) && $two_lines)
    <div class="form-group {{ $errors->has($key)?'has-error':'' }}">
        @if(!empty($label))<label for="{{ $name }}" class="control-label">{{ $label }} {!! $required?'<span class="required">*</span>':'' !!}</label>@endif
        @yield('form_field')

        @if(!empty($errors->has($key)))
            <span class="help-block"> {{ $errors->first($key) }} </span>
        @endif
    </div>
@else
    <div class="form-group {{ $errors->has($key)?'has-error':'' }}">
        @if(!empty($label))<label for="{{ $name }}" class="control-label {{ isset($labelColumnClass)?$labelColumnClass:'col-md-3' }}">{{ $label }} {!! $required?'<span class="required">*</span>':'' !!}</label>@endif
        <div class="{{ isset($valueColumnClass)?$valueColumnClass:'col-md-9' }}">
            @yield('form_field'){!! isset($appends)?$appends:'' !!}

            @if(!empty($errors->has($key)))
                <span class="help-block"> {{ $errors->first($key) }} </span>
            @endif

            @if(!empty($help_text))
                <span class="help-block"> {{ $help_text }} </span>
            @endif
        </div>
    </div>
@endif