@extends('backend.master.form.fields.master')

<?php
$attr = array_merge(['multiple' => TRUE], $attr);
$attr['class'] = ($attr['class']?$attr['class']:'').' files-upload';
$limit = 999999;
$multiple = isset($multiple)?$multiple:true;
$caption = isset($caption)?$caption:true;
$existingImages = [];

if($multiple === FALSE){
    unset($attr['multiple']);
    $limit = 1;
}

$oldImages = old($name, []);

if(!empty($oldImages)){
    foreach($oldImages as $oldImage){
        $existingImages[] = \Kommercio\Models\Media::findOrFail($oldImage);
    }
}

if(isset($existing) && empty($existingImages)){
    $existingImages = $existing;
}
?>

@section('form_field')
<div class="images-upload" data-name="{{ $name }}" data-caption="{{ $caption?1:0 }}" data-limit="{{ $limit }}" data-upload_url="{{ route('backend.file.upload') }}">
    <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
    <div class="row fileupload-buttonbar">
        <div role="presentation" class="uploaded-box clearfix">
            <div class="files">
                @foreach($existingImages as $idx=>$existingImage)
                    <div class="col-md-3 text-center uploaded-image">
                        <img class="img-responsive" src="{{ asset(config('kommercio.images_path').'/backend_thumbnail/'.$existingImage->folder.$existingImage->filename) }}" />
                        @if($caption)
                        <input name="{{ $name }}_caption[]" type="text" class="form-control input-sm" placeholder="Caption" value="{{ old($name.'_caption.'.$idx, $existingImage->pivot?$existingImage->pivot->caption:null) }}" />
                        @endif
                        <input name="{{ $name }}[]" type="hidden" value="{{ $existingImage->id }}" />
                        <a href="#" class="uploaded-image-remove"><i class="fa fa-remove"></i></a>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="col-sm-12"><div class="dropzone">
                Drop your file{{ $multiple?'s':'' }} here to upload or
                <!-- The fileinput-button span is used to style the file input field as button -->
                <span class="btn btn-sm btn-info fileinput-button">
                    <i class="fa fa-plus"></i>
                    <span> Add file{{ $multiple?'s':'' }}... </span>
                    {!! Form::file('_'.$name.'[]', $attr) !!}
                </span>
            </div></div>
    </div>
</div>
@overwrite