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
    'name' => 'description',
    'label' => 'Description',
    'key' => 'description',
    'attr' => [
        'class' => 'form-control summernote-editor',
        'id' => 'description'
    ]
])

@include('backend.master.form.fields.images', [
    'name' => 'image',
    'label' => 'Category Image',
    'key' => 'image',
    'attr' => [
        'class' => 'form-control',
        'id' => 'image'
    ],
    'multiple' => FALSE,
    'existing' => $category->images
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
    'existing' => $category->thumbnail?[$category->thumbnail]:[]
])

@include('backend.master.form.fields.checkbox', [
    'name' => 'active',
    'label' => 'Active',
    'key' => 'active',
    'value' => 1,
    'checked' => null,
    'attr' => [
        'class' => 'make-switch',
        'id' => 'active',
        'data-on-color' => 'warning'
    ]
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