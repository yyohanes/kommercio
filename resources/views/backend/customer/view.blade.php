@extends('backend.master.form_template')

@section('breadcrumb')
    <li>
        <a href="{{ route('backend.customer.index') }}"><span>Customer</span></a>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>Customer - {{ $customer->fullName }}</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="margin-bottom-10"><a href="{{ NavigationHelper::getBackUrl() }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Back</a></div>

        <div class="portlet light portlet-fit portlet-form bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase">Customer - {{ $customer->fullName }}</span>
                </div>
                <div class="actions">
                    @if(Gate::allows('access', ['edit_view']))
                        <a href="{{ route('backend.customer.edit', ['id' => $customer->id, 'backUrl' => Request::fullUrl()]) }}" class="btn btn-info"><i class="fa fa-pencil"></i> Edit </a>
                    @endif
                </div>
            </div>

            <div class="portlet-body">
                <div class="form-body" id="customer-wrapper">
                    <div class="tabbable-bordered">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="active" role="presentation">
                                <a href="#tab_details" data-toggle="tab"> Details </a>
                            </li>
                            @can('access', ['view_order'])
                            <li role="presentation">
                                <a href="#tab_orders" data-toggle="tab"> Orders </a>
                            </li>
                            @endcan

                            <li role="presentation">
                                <a href="#tab_address" data-toggle="tab"> Address </a>
                            </li>

                            @if(Gate::allows('access', ['view_reward_points']) && ProjectHelper::isFeatureEnabled('customer.reward_points'))
                                <li role="presentation">
                                    <a href="#tab_reward_points" data-toggle="tab"> Reward Points </a>
                                </li>
                            @endif
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane active" id="tab_details">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="portlet light bordered">
                                            <div class="portlet-title">
                                                <div class="caption">
                                                    <i class="fa fa-user"></i>
                                                    <span class="caption-subject">Customer Information</span>
                                                </div>
                                            </div>
                                            <div class="portlet-body">
                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> Account: </div>
                                                    <div class="col-md-7 value"> <i class="fa fa-{{ isset($customer->user)?'check text-success':'remove text-danger' }}"></i> </div>
                                                </div>

                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> Status: </div>
                                                    <div class="col-md-7 value"> <i class="fa fa-{{ isset($customer->user) && $customer->user->status == \Kommercio\Models\User::STATUS_ACTIVE?'check text-success':'remove text-danger' }}"></i> </div>
                                                </div>

                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> Salute: </div>
                                                    <div class="col-md-7 value"> {{ $customer->getProfile()->salute }} </div>
                                                </div>

                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> Name: </div>
                                                    <div class="col-md-7 value"> {{ $customer->fullName }} </div>
                                                </div>

                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> Email: </div>
                                                    <div class="col-md-7 value"> {{ $customer->getProfile()->email }} </div>
                                                </div>

                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> Phone Number: </div>
                                                    <div class="col-md-7 value"> {{ $customer->getProfile()->phone_number }} </div>
                                                </div>

                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> Address: </div>
                                                    <div class="col-md-7 value"> {!! AddressHelper::printAddress($customer->getProfile()->getDetails()) !!} </div>
                                                </div>

                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> Birthday: </div>
                                                    <div class="col-md-7 value"> {{ $customer->getProfile()->birthday?\Carbon\Carbon::createFromFormat('Y-m-d', $customer->getProfile()->birthday)->format('d M Y'):'' }} </div>
                                                </div>

                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> Customer Since: </div>
                                                    <div class="col-md-7 value"> {{ $customer->created_at->format('d M Y, H:i') }} </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="portlet light bordered">
                                            <div class="portlet-title">
                                                <div class="caption">
                                                    <i class="fa fa-shopping-cart"></i>
                                                    <span class="caption-subject">Order Information</span>
                                                </div>
                                            </div>
                                            <div class="portlet-body">
                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> Number of Order: </div>
                                                    <div class="col-md-7 value"> {{ $customer->orders->count() }} </div>
                                                </div>

                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> Total Order: </div>
                                                    <div class="col-md-7 value"> {{ PriceFormatter::formatNumber($customer->total) }} </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if(Gate::allows('access', ['view_reward_points']) && ProjectHelper::isFeatureEnabled('customer.reward_points'))
                                        <div class="portlet light bordered">
                                            <div class="portlet-title">
                                                <div class="caption">
                                                    <i class="fa fa-shopping-cart"></i>
                                                    <span class="caption-subject">Reward Points</span>
                                                </div>
                                            </div>
                                            <div class="portlet-body">
                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> Points: </div>
                                                    <div class="col-md-7 value"> <span class="current-reward-point">{{ $customer->reward_points + 0 }}</span> </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>

                                    <div class="clearfix"></div>
                                </div>
                            </div>

                            @can('access', ['view_order'])
                            <div class="tab-pane" id="tab_orders">
                                <div class="form-body">
                                    <div class="table-scrollable">
                                        <table class="table table-hover">
                                            <thead>
                                            <tr>
                                                <th> # </th>
                                                <th> Order # </th>
                                                <th>Purchased On</th>
                                                @if(config('project.enable_delivery_date', FALSE))
                                                    <th>Delivery Date</th>
                                                @endif
                                                <th>Total</th>
                                                <th>Status</th>
                                                <th> </th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($customer->orders as $idx => $order)
                                                    <tr>
                                                        <td>{{ $idx + 1 }}</td>
                                                        <td>{{ $order->reference }}</td>
                                                        <td>{{ $order->checkout_at->format('d M Y, H:i') }}</td>
                                                        @if(config('project.enable_delivery_date', FALSE))
                                                            <td>{{ $order->delivery_date->format('d M Y, H:i') }}</td>
                                                        @endif
                                                        <td>{{ PriceFormatter::formatNumber($order->total) }}</td>
                                                        <td><label class="label label-sm bg-{{ OrderHelper::getOrderStatusLabelClass($order->status) }} bg-font-{{ OrderHelper::getOrderStatusLabelClass($order->status) }}">{{ \Kommercio\Models\Order\Order::getStatusOptions($order->status, TRUE) }}</label></td>
                                                        <td><a href="{{ route('backend.sales.order.view', ['id' => $order->id, 'backUrl' => Request::getRequestUri()]) }}" class="btn btn-sm btn-default"><i class="fa fa-search"></i></a></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endcan

                            <div class="tab-pane" id="tab_address">
                                <div class="form-body">
                                    <div class="margin-bottom-10">
                                        @can('access', ['edit_customer'])
                                        <a class="btn btn-default" id="address-add-btn" href="#">
                                            <i class="icon-plus"></i> Add Address
                                        </a>
                                        @endcan
                                    </div>

                                    <div id="address-form-wrapper"
                                         data-address_form="{{ route('backend.customer.address.form', ['customer_id' => $customer->id]) }}"
                                         data-address_index="{{ route('backend.customer.address.index', ['customer_id' => $customer->id]) }}"></div>

                                    <div id="address-index-wrapper">
                                        @include('backend.customer.address.index', ['profiles' => $customer->savedProfiles])
                                    </div>
                                </div>
                            </div>

                            @if(Gate::allows('access', ['view_reward_points']) && ProjectHelper::isFeatureEnabled('customer.reward_points'))
                                <div class="tab-pane" id="tab_reward_points">
                                    <div class="form-body">
                                        <div class="well well-large">Current Reward Points: <strong class="current-reward-point">{{ $customer->reward_points + 0 }}</strong></div>

                                        <div class="margin-bottom-10">
                                            @can('access', ['add_reward_points'])
                                            <a class="btn btn-default reward-point-action-button" data-form="{{ route('backend.customer.reward_point.mini_form', ['customer_id' => $customer->id, 'type' => \Kommercio\Models\RewardPoint\RewardPointTransaction::TYPE_ADD]) }}" href="#">
                                                <i class="fa fa-plus"></i> Add Reward Point
                                            </a>
                                            @endcan
                                            @can('access', ['deduct_reward_points'])
                                            <a class="btn btn-default reward-point-action-button" data-form="{{ route('backend.customer.reward_point.mini_form', ['customer_id' => $customer->id, 'type' => \Kommercio\Models\RewardPoint\RewardPointTransaction::TYPE_DEDUCT]) }}" href="#">
                                                <i class="fa fa-minus"></i> Deduct Reward Point
                                            </a>
                                            @endcan
                                        </div>

                                        <div id="reward-point-form-wrapper"
                                             data-reward_point_index="{{ route('backend.customer.reward_point.mini_index', ['customer_id' => $customer->id]) }}"></div>

                                        <div id="reward-point-index-wrapper">
                                            @include('backend.customer.reward_point.mini_index', ['rewardPointTransactions' => $customer->rewardPointTransactions])
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('bottom_page_scripts')
    @parent

    <script src="{{ asset('backend/assets/scripts/pages/customer_view.js') }}" type="text/javascript"></script>
@stop