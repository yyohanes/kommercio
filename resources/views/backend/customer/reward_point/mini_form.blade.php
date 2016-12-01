<div class="portlet light bordered">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject">{{ \Kommercio\Models\RewardPoint\RewardPointTransaction::getTypeOptions($type) }} Reward Point</span>
        </div>
    </div>

    <div class="portlet-body">
        {!! Form::open(['route' => ['backend.customer.reward_point.mini_save', 'customer_id' => $customer->id], 'class' => 'form-horizontal']) !!}
        @include('backend.master.form.fields.number', [
            'name' => 'amount',
            'label' => 'Points to '.\Kommercio\Models\RewardPoint\RewardPointTransaction::getTypeOptions($type),
            'key' => 'amount',
            'attr' => [
                'class' => 'form-control input-sm',
                'id' => 'amount'
            ],
            'required' => TRUE,
            'defaultValue' => 0,
            'unitPosition' => 'front',
            'valueColumnClass' => 'col-md-6',
        ])

        @include('backend.master.form.fields.textarea', [
            'name' => 'reason',
            'label' => 'Why do you '.\Kommercio\Models\RewardPoint\RewardPointTransaction::getTypeOptions($type).' point?',
            'key' => 'reason',
            'attr' => [
                'class' => 'form-control',
                'id' => 'reason',
                'rows' => 3,
            ],
            'valueColumnClass' => 'col-md-6',
            'required' => TRUE
        ])

        @include('backend.master.form.fields.textarea', [
            'name' => 'notes',
            'label' => 'Notes',
            'key' => 'notes',
            'attr' => [
                'class' => 'form-control',
                'id' => 'notes',
                'rows' => 3,
            ],
            'valueColumnClass' => 'col-md-6',
        ])

        {!! Form::hidden('type', $type) !!}

        <div class="margin-top-15 text-center">
            <button id="reward-point-save" data-reward_point_save="{{ route('backend.customer.reward_point.mini_save', ['customer_id' => $customer->id]) }}" class="btn btn-info"><i class="fa fa-save"></i> Save</button>
            <a id="reward-point-cancel" class="btn btn-default" href="#"><i class="fa fa-remove"></i> Cancel</a>
        </div>
        {!! Form::close() !!}
    </div>
</div>