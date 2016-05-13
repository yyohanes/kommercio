@extends('backend.master.form.fields.master')

@section('form_field')
    <?php
    $defaultValue = isset($defaultValue)?$defaultValue:null;
    ?>
    {!! Form::text($name, $defaultValue, $attr) !!}
@overwrite