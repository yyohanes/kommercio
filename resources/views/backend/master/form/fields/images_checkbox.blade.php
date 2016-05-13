@extends('backend.master.form.fields.master')

<?php
$existingImages = [];

if(isset($existing) && empty($existingImages)){
    $existingImages = $existing;
}
?>

@section('form_field')
<div class="row">
    <div role="presentation" class="uploaded-box clearfix">
        <div class="files">
            @foreach($existingImages as $idx=>$existingImage)
                <div class="col-md-3 text-center uploaded-image">
                    <label><img class="img-responsive" src="{{ asset(config('kommercio.images_path').'/backend_thumbnail/'.$existingImage->folder.$existingImage->filename) }}" />
                    <h6>{{ $existingImage->pivot?$existingImage->pivot->caption:null }}</h6>
                    {!! Form::checkbox($name.'[]', $existingImage->id) !!}
                    </label>
                </div>
            @endforeach
        </div>
    </div>
</div>
@overwrite