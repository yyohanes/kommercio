<div class="portlet light bordered">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject">Redeem</span>
        </div>
    </div>

    <div class="portlet-body">
        {!! Form::open(['route' => ['backend.customer.reward_point.redeem', 'customer_id' => $customer->id], 'class' => 'form-horizontal']) !!}
        @include('backend.master.form.fields.select', [
            'name' => 'reward',
            'label' => 'Reward',
            'key' => 'reward',
            'attr' => [
                'class' => 'form-control select2',
                'id' => 'reward'
            ],
            'valueColumnClass' => 'col-md-6',
            'options' => $rewardOptions,
            'required' => TRUE,
        ])

        <div class="margin-top-15 text-center">
            <button id="redeem-save" class="btn btn-info"><i class="fa fa-save"></i> Redeem</button>
            <a id="redeem-cancel" class="btn btn-default" href="#"><i class="fa fa-remove"></i> Cancel</a>
        </div>
        {!! Form::close() !!}
    </div>
</div>