@include('backend.master.form.fields.text', [
    'name' => 'name',
    'label' => 'Name',
    'key' => 'name',
    'attr' => [
        'class' => 'form-control',
        'id' => 'name'
    ],
    'required' => TRUE
])

@include('backend.master.form.fields.select', [
    'name' => 'parent_id',
    'label' => 'Parent',
    'key' => 'parent_id',
    'options' => $parentOptions,
    'attr' => [
        'class' => 'form-control select2',
        'id' => 'parent_id'
    ],
])

@include('backend.master.form.fields.textarea', [
    'name' => 'body',
    'label' => 'Content',
    'key' => 'body',
    'attr' => [
        'class' => 'form-control summernote-editor',
        'id' => 'description'
    ]
])

@include('backend.master.form.fields.images', [
    'name' => 'image',
    'label' => 'Page Image',
    'key' => 'image',
    'attr' => [
        'class' => 'form-control',
        'id' => 'image'
    ],
    'multiple' => FALSE,
    'existing' => $page->images
])

@include('backend.master.form.fields.checkbox', [
    'name' => 'active',
    'label' => 'Active',
    'key' => 'active',
    'value' => 1,
    'checked' => $page->exists?$page->active:true,
    'attr' => [
        'class' => 'make-switch',
        'id' => 'active',
        'data-on-color' => 'warning'
    ],
    'appends' => '<a class="btn btn-default" href="#active-schedule-modal" data-toggle="modal"><i class="fa fa-calendar"></i></a>'
])

<div id="active-schedule-modal" class="modal fade" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">Active Schedule</h4>
            </div>
            <div class="modal-body">
                @include('backend.master.form.fields.datetime', [
                    'name' => 'active_date_from',
                    'label' => 'Active From',
                    'key' => 'active_date_from',
                    'attr' => [
                        'id' => 'active_date_from'
                    ],
                ])

                @include('backend.master.form.fields.datetime', [
                    'name' => 'active_date_to',
                    'label' => 'Active Until',
                    'key' => 'active_date_to',
                    'attr' => [
                        'id' => 'active_date_to'
                    ],
                ])
            </div>
            <div class="modal-footer">
                <button class="btn green" data-dismiss="modal" aria-hidden="true">Done</button>
            </div>
        </div>
    </div>
</div>

<h4 class="form-section">SEO</h4>

@include('backend.master.form.fields.text', [
    'name' => 'slug',
    'label' => 'Friendly URL',
    'key' => 'slug',
    'attr' => [
        'class' => 'form-control',
        'id' => 'slug',
        'data-slug_source' => '#name'
    ],
    'required' => TRUE
])

@include('backend.master.form.fields.text', [
    'name' => 'meta_title',
    'label' => 'Meta Title',
    'key' => 'meta_title',
    'attr' => [
        'class' => 'form-control',
        'id' => 'meta_title'
    ]
])

@include('backend.master.form.fields.textarea', [
    'name' => 'meta_description',
    'label' => 'Meta Description',
    'key' => 'meta_description',
    'attr' => [
        'class' => 'form-control',
        'id' => 'meta_description'
    ]
])