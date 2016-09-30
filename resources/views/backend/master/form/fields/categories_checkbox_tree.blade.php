@extends('backend.master.form.fields.master')

@section('form_field')
    <div id="{{ isset($attr['id'])?$attr['id']:'' }}" class="{{ isset($attr['class'])?$attr['class']:'' }}">
        <div class="scroller" style="height:275px;" data-always-visible="1">
            @if($rootCategories->count() > 0)
            <ul class="list-unstyled">
                @foreach($rootCategories as $rootCategory)
                    @include('backend.master.form.fields.categories_checkbox_tree_render', [
                        'item' => $rootCategory
                    ])
                @endforeach
            </ul>
            @endif
        </div>
    </div>
@overwrite