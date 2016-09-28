@extends('backend.master.form.fields.master')

@section('form_field')
    <?php
    $defaultValue = isset($defaultValue)?$defaultValue:null;
    $keepSecond = isset($keepSecond)?$keepSecond:false;
    ?>
    <div class="input-group date form_datetime" id="{{ isset($attr['id'])?$attr['id']:'' }}">
        {!! Form::text($name, $defaultValue, ['readonly' => TRUE, 'class' => 'datetime-picker '.($keepSecond?'keep-second':'').' form-control']) !!}
        <span class="input-group-btn">
            <button class="btn default date-reset" type="button">
                <i class="fa fa-times"></i>
            </button>
            <button class="btn default date-set" type="button">
                <i class="fa fa-calendar"></i>
            </button>
        </span>
    </div>
@overwrite