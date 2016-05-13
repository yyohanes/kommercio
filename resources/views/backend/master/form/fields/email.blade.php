@extends('backend.master.form.fields.master')

@section('form_field')
    <?php
    $defaultValue = isset($defaultValue)?$defaultValue:null;
    ?>
    {!! Form::email($name, $defaultValue, $attr) !!}
@overwrite