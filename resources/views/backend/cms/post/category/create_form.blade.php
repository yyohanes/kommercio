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
    'required' => TRUE
])

@include('backend.master.form.fields.textarea', [
    'name' => 'body',
    'label' => 'Body',
    'key' => 'body',
    'attr' => [
        'class' => 'form-control wysiwyg-editor',
        'id' => 'body'
    ]
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
    'existing' => $postCategory->images
])

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