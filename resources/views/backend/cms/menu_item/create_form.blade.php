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

@include('backend.master.form.fields.text', [
    'name' => 'menu_class',
    'label' => 'Class',
    'key' => 'menu_class',
    'attr' => [
        'class' => 'form-control',
        'id' => 'menu_class'
    ],
])

@include('backend.master.form.fields.checkbox', [
    'name' => 'active',
    'label' => 'Active',
    'key' => 'active',
    'value' => 1,
    'checked' => $menuItem->exists?$menuItem->active:true,
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

<hr/>

@include('backend.master.form.fields.text', [
    'name' => 'url',
    'label' => 'Path',
    'key' => 'url',
    'attr' => [
        'class' => 'form-control',
        'id' => 'url'
    ]
])

@include('backend.master.form.fields.select', [
    'name' => 'data[target]',
    'label' => 'Open path in',
    'key' => 'data.target',
    'options' => \Kommercio\Models\CMS\MenuItem::getLinkTargetOptions(),
    'attr' => [
        'class' => 'form-control select2',
        'id' => 'data[target]'
    ],
    'defaultOptions' => old('data.target', $menuItem->exists?$menuItem->getTranslation()->getData('target'):null)
])

{!! Form::hidden('menu_id', $menu->id) !!}