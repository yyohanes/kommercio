@extends('backend.master.form.fields.master')

<?php
$attr = array_merge(['multiple' => TRUE], $attr);
$attr['class'] = ($attr['class']?$attr['class']:'').' files-upload';
$limit = isset($limit)?$limit:999999;
$multiple = isset($multiple)?$multiple:true;
$caption = isset($caption)?$caption:true;
$existingVideos = [];

if($multiple === FALSE){
    unset($attr['multiple']);
    $limit = 1;
}

$oldVideos = old($name, []);

if(!empty($oldVideos)){
    foreach($oldVideos as $oldVideo){
        $existingVideos[] = \Kommercio\Models\Media::findOrFail($oldVideo);
    }
}

if(isset($existing) && empty($existingVideos)){
    $existingVideos = $existing;
}
?>

@section('form_field')
<div class="videos-upload" data-name="{{ $name }}" data-caption="{{ $caption?1:0 }}" data-limit="{{ $limit }}" data-upload_url="{{ route('backend.file.upload', ['type' => 'video']) }}">
    <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
    <div class="row fileupload-buttonbar">
        <div role="presentation" class="uploaded-box clearfix">
            <div class="files">
                @foreach($existingVideos as $idx=>$existingVideo)
                    <div class="col-md-3 text-center uploaded-video">
                        <div class="video-name">{{ $existingVideo->filename }}</div>
                        @if($caption)
                        <input name="{{ $name }}_caption[]" type="text" class="form-control input-sm" placeholder="Caption" value="{{ old($name.'_caption.'.$idx, $existingVideo->pivot?$existingVideo->pivot->caption:null) }}" />
                        @endif
                        <input name="{{ $name }}[]" type="hidden" value="{{ $existingVideo->id }}" />
                        <a href="#" class="uploaded-video-remove"><i class="fa fa-remove"></i></a>
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