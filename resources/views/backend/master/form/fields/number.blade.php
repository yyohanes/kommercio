@extends('backend.master.form.fields.master')

@section('form_field')
    <?php
    $attr['data-inputmask'] = '\'alias\': \'decimal\', \'groupSeparator\': \',\', \'autoGroup\': true, \'autoUnmask\': true, \'removeMaskOnSubmit\': true';

    $unitPosition = isset($unitPosition)?$unitPosition:'behind';
    if($unitPosition == 'front'){
        $attr['data-inputmask'] .= ', \'rightAlign\': false';
    }

    $defaultValue = isset($defaultValue)?$defaultValue:null;
    ?>

    @if(isset($unit))
    <div class="input-group">
        @if($unitPosition == 'front')
        <span class="input-group-addon">{{ $unit }}</span>
        @endif
        {!! Form::text($name, $defaultValue, $attr) !!}
        @if($unitPosition != 'front')
        <span class="input-group-addon">{{ $unit }}</span>
        @endif
    </div>
    @else
        {!! Form::text($name, $defaultValue, $attr) !!}
    @endif
@overwrite