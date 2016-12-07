@include('backend.master.form.fields.text', [
    'name' => 'name',
    'label' => 'Name',
    'key' => 'name',
    'attr' => [
        'class' => 'form-control',
        'id' => 'name'
    ],
    'required' => TRUE,
    'valueColumnClass' => 'col-md-6',
])

@include('backend.master.form.fields.select', [
    'name' => 'type',
    'label' => 'Type',
    'key' => 'type',
    'attr' => [
        'class' => 'form-control',
        'id' => 'type',
    ],
    'options' => $typeOptions,
    'valueColumnClass' => 'col-md-6',
])

@foreach($typeOptions as $typeOptionId => $typeOption)
    @include('backend.customer.reward_rule.rule.'.$typeOptionId, ['type' => $typeOptionId])
@endforeach

@include('backend.master.form.fields.number', [
    'name' => 'reward',
    'label' => 'Reward Point',
    'key' => 'reward',
    'attr' => [
        'class' => 'form-control input-sm',
        'id' => 'reward'
    ],
    'required' => TRUE,
    'unitPosition' => 'front',
    'valueColumnClass' => 'col-md-6',
])

@include('backend.master.form.fields.checkbox', [
    'name' => 'active',
    'label' => 'Active',
    'key' => 'active',
    'value' => 1,
    'attr' => [
        'class' => 'make-switch',
        'id' => 'active',
        'data-on-color' => 'warning'
    ],
    'checked' => $rewardRule->active,
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

@include('backend.master.form.fields.checkbox', [
    'name' => 'member',
    'label' => 'Member Only',
    'key' => 'member',
    'value' => 1,
    'attr' => [
        'class' => 'make-switch',
        'id' => 'member',
        'data-on-color' => 'warning'
    ],
    'checked' => $rewardRule->member,
])

<div class="row">
    <label class="control-label col-md-3">For</label>
    <div class="col-sm-3">
        @include('backend.master.form.fields.select', [
            'name' => 'currency',
            'label' => null,
            'key' => 'currency',
            'attr' => [
                'class' => 'form-control',
                'id' => 'currency',
            ],
            'options' => $currencyOptions,
        ])
    </div>

    <div class="col-sm-3">
        @include('backend.master.form.fields.select', [
            'name' => 'store_id',
            'label' => null,
            'key' => 'store_id',
            'attr' => [
                'class' => 'form-control',
                'id' => 'store_id',
            ],
            'options' => $storeOptions,
        ])
    </div>
</div>