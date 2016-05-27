@extends('backend.master.form.fields.master')

@section('form_field')
    {!! Form::radio($name, $value, $checked, $attr) !!}
@overwrite