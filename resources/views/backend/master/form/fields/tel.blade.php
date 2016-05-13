@extends('backend.master.form.fields.master')

@section('form_field')
    <?php
    $defaultValue = isset($defaultValue)?$defaultValue:null;
    ?>
    {!! Form::input('tel', $name, $defaultValue, $attr) !!}
@overwrite