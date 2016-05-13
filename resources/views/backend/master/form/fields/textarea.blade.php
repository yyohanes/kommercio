@extends('backend.master.form.fields.master')

@section('form_field')
    <?php
    $defaultValue = isset($defaultValue)?$defaultValue:null;
    ?>
    {!! Form::textarea($name, $defaultValue, $attr) !!}
@overwrite