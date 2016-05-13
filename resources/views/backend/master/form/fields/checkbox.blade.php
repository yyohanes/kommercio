@extends('backend.master.form.fields.master')

@section('form_field')
    {!! Form::checkbox($name, $value, $checked, $attr) !!}
@overwrite