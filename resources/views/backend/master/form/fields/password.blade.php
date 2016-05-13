@extends('backend.master.form.fields.master')

@section('form_field')
    {!! Form::password($name, $attr) !!}
@overwrite