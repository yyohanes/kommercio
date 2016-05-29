@extends('backend.master.form.fields.master')

@section('form_field')
    <?php
    $attr['class'] = (isset($attr['class'])?$attr['class']:'').' city-select';
    $attr['data-first_option'] = 'Select City';
    $defaultOptions = isset($defaultOptions)?$defaultOptions:null;
    $active_only = isset($active_only)?$active_only:false;

    $options = AddressHelper::getCityOptions($parent, $active_only);
    $options = !empty($options)?(['' => $attr['data-first_option']] + $options):[];
    ?>

    {!! Form::select($name, $options, $defaultOptions, $attr) !!}
@overwrite