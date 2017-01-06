@extends('backend.master.layout')

@section('top_page_styles')
<link href="{{ asset('backend/assets/template/global/plugins/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('backend/assets/template/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css') }}" rel="stylesheet" type="text/css" />
@stop

@section('bottom_page_scripts')
    <script src="{{ asset('backend/assets/template/global/scripts/datatable.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js') }}" type="text/javascript"></script>

    <script src="{{ asset('backend/assets/scripts/pages/table_reorder.js') }}" type="text/javascript"></script>
@stop

@section('breadcrumb')
    <li>
        <span>Catalog</span>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>Price Rules</span>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>Cart Price Rules</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit portlet-datatable bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase">Cart Price Rules</span>
                </div>
                <div class="actions">
                    @can('access', ['create_cart_price_rule'])
                    <a href="{{ route('backend.price_rule.cart.create', ['backUrl' => Request::fullUrl()]) }}" class="btn btn-sm btn-info">
                        <i class="fa fa-plus"></i> Add </a>
                    @endcan
                </div>
            </div>

            <div class="portlet-body">
                <table class="table table-striped table-bordered table-advance dataset-reorder" id="price-rules-dataset" data-form_token="{{ csrf_token() }}" data-row_class="price-rule-name" data-row_value="price_rule_id" data-reorder_action="{{ route('backend.price_rule.cart.reorder') }}">
                    <thead>
                    <tr>
                        <th style="width: 10px;"></th>
                        <th> Name </th>
                        <th> Type </th>
                        <th> Coupon </th>
                        <th> Modification </th>
                        <th> Currency </th>
                        <th> Store </th>
                        <th> Active </th>
                        <th style="width: 20%;"> Actions </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($priceRules as $priceRule)
                        <?php
                        $canStoreManage = Gate::allows('manage_store', [$priceRule]);
                        ?>
                        <tr>
                            <td><i class="fa fa-reorder"></i></td>
                            <td class="price-rule-name" data-price_rule_id="{{ $priceRule->id }}">{{ $priceRule->name?$priceRule->name:'-' }}</td>
                            <td> {{ \Kommercio\Models\PriceRule\CartPriceRule::getOfferTypeOptions($priceRule->offer_type) }} </td>
                            <td>
                                @foreach($priceRule->coupons as $coupon)
                                    <div>{{ $coupon->coupon_code }}</div>
                                @endforeach
                            </td>
                            <td> {{ $priceRule->modification?$priceRule->getModificationOutput():'-' }} </td>
                            <td> {{ $priceRule->currency?CurrencyHelper::getCurrency($priceRule->currency)['iso']:'All' }} </td>
                            <td> {{ $priceRule->store?$priceRule->store->name:'All' }} </td>
                            <td> <i class="fa {{ $priceRule->active?'fa-check text-success':'fa-remove text-danger' }}"></i> </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    {!! Form::open(['route' => ['backend.price_rule.cart.delete', 'id' => $priceRule->id]]) !!}
                                    <div class="btn-group btn-group-sm">
                                        @if(Gate::allows('access', ['edit_cart_price_rule']) && $canStoreManage)
                                        <a class="btn btn-default" href="{{ route('backend.price_rule.cart.edit', ['id' => $priceRule->id, 'backUrl' => Request::fullUrl()]) }}"><i class="fa fa-pencil"></i> Edit</a>
                                        @endif
                                        @if(Gate::allows('access', ['delete_cart_price_rule']) && $canStoreManage)
                                        <button class="btn btn-default" data-toggle="confirmation" data-original-title="Are you sure?" title=""><i class="fa fa-trash-o"></i> Delete</button>
                                        @endif
                                    </div>
                                    {!! Form::close() !!}
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop