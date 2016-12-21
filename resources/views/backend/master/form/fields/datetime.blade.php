@extends('backend.master.form.fields.master')

@section('form_field')
    <?php
    $defaultValue = isset($defaultValue)?$defaultValue:null;
    $keepSecond = isset($keepSecond)?$keepSecond:false;
    ?>
    <div class="input-group date form_datetime" id="{{ isset($attr['id'])?$attr['id']:'' }}">
        {!! Form::text($name, $defaultValue, ['readonly' => TRUE, 'class' => 'datetime-picker '.($keepSecond?'keep-second':'').' form-control']) !!}
        <span class="btn default input-group-addon">
            <i class="fa fa-times"></i>
        </span>
        <span class="btn default input-group-addon">
            <i class="fa fa-calendar"></i>
        </span>
    </div>
@overwrite