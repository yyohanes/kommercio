<div class="row">
    @foreach($profiles as $idx => $savedProfile)
        <?php $savedProfile->getDetails(); ?>
        @if($idx%2 == 0)
            <div class="clearfix"></div>
        @endif
        <div class="col-md-6 ">
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption">{{ \Kommercio\Models\Customer::getProfileNameOptions($savedProfile->pivot->name) }}</div>
                    <div class="actions">
                        <a href="#" data-address_edit="{{ route('backend.customer.address.form', ['customer_id' => $customer->id, 'id' => $savedProfile->id]) }}" class="btn address-edit-btn btn-default btn-sm">
                            <i class="fa fa-pencil"></i> Edit </a>
                        <button data-address_delete="{{ route('backend.customer.address.delete', ['customer_id' => $customer->id, 'id' => $savedProfile->id]) }}" class="btn address-delete-btn btn-default btn-sm"
                           data-toggle="confirmation"
                           data-original-title="Are you sure?"
                           data-on-confirm="addressFormBehaviors.deleteAddress">
                            <i class="fa fa-trash"></i> Delete </button>
                    </div>
                </div>
                <div class="portlet-body">
                    <div class="row static-info">
                        <div class="col-md-5 name"> Name: </div>
                        <div class="col-md-7 value"> {{ $savedProfile->salute?\Kommercio\Models\Customer::getSaluteOptions($savedProfile->salute).'. ':null }}{{ $savedProfile->full_name }} </div>
                    </div>

                    <div class="row static-info">
                        <div class="col-md-5 name"> Phone Number: </div>
                        <div class="col-md-7 value"> {{ $savedProfile->phone_number }} </div>
                    </div>

                    @if($savedProfile->home_phone)
                    <div class="row static-info">
                        <div class="col-md-5 name"> Home Phone: </div>
                        <div class="col-md-7 value"> {{ $savedProfile->home_phone }} </div>
                    </div>
                    @endif

                    <div class="row static-info">
                        <div class="col-md-5 name"> Address: </div>
                        <div class="col-md-7 value"> {!! AddressHelper::printAddress($savedProfile->getDetails()) !!} </div>
                    </div>

                    <div class="row static-info">
                        <div class="col-md-5 name"> Default Billing: </div>
                        <div class="col-md-7 value"> <i class="fa fa-{{ $savedProfile->pivot->billing?'check text-success':'remove text-danger' }}"></i> </div>
                    </div>

                    <div class="row static-info">
                        <div class="col-md-5 name"> Default Shipping: </div>
                        <div class="col-md-7 value"> <i class="fa fa-{{ $savedProfile->pivot->shipping?'check text-success':'remove text-danger' }}"></i> </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>