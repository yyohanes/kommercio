<div class="row">
    <div class="col-md-9">
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

        @include('backend.master.form.fields.textarea', [
            'name' => 'teaser',
            'label' => 'Teaser',
            'key' => 'teaser',
            'attr' => [
                'class' => 'form-control wysiwyg-editor',
                'id' => 'teaser'
            ]
        ])

        @include('backend.master.form.fields.textarea', [
            'name' => 'body',
            'label' => 'Content',
            'key' => 'body',
            'attr' => [
                'class' => 'form-control wysiwyg-editor',
                'id' => 'description'
            ]
        ])

        @include('backend.master.form.fields.images', [
            'name' => 'thumbnail',
            'label' => 'Thumbnail',
            'key' => 'thumbnail',
            'attr' => [
                'class' => 'form-control',
                'id' => 'thumbnail'
            ],
            'multiple' => FALSE,
            'existing' => $post->thumbnail?[$post->thumbnail]:[]
        ])

        @include('backend.master.form.fields.images', [
            'name' => 'image',
            'label' => 'Image',
            'key' => 'image',
            'attr' => [
                'class' => 'form-control',
                'id' => 'image'
            ],
            'multiple' => FALSE,
            'existing' => $post->images
        ])

        @include('backend.master.form.fields.checkbox', [
            'name' => 'active',
            'label' => 'Active',
            'key' => 'active',
            'value' => 1,
            'checked' => $post->exists?$post->active:true,
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
    </div>

    <div class="col-md-3">
        @include('backend.master.form.fields.categories_checkbox_tree', [
            'name' => 'categories[]',
            'label' => false,
            'key' => 'categories',
            'attr' => [
                'class' => 'form-control height-auto',
                'id' => 'categories-checkbox'
            ],
            'existing' => $post->postCategories->pluck('id')->all(),
            'rootCategories' => \Kommercio\Models\CMS\PostCategory::getRootCategories()
        ])

        <div class="col-md-12">
        @include('backend.master.form.fields.text', [
            'name' => 'created_at',
            'label' => 'Post Date',
            'two_lines' => TRUE,
            'key' => 'created_at',
            'attr' => [
                'class' => 'form-control',
                'id' => 'created_at',
                'data-inputmask' => '\'alias\':\'y-m-d h:s:s\'',
            ],
        ])
        </div>
    </div>
</div>