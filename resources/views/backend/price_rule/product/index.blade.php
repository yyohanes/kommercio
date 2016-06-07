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
        <span>Product Price Rules</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit portlet-datatable bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase">Product Price Rules</span>
                </div>
                <div class="actions">
                    <a href="{{ route('backend.price_rule.product.create', ['backUrl' => Request::fullUrl()]) }}" class="btn btn-sm btn-info">
                        <i class="fa fa-plus"></i> Add </a>
                </div>
            </div>

            <div class="portlet-body">
                <table class="table table-striped table-bordered table-advance dataset-reorder" id="price-rules-dataset" data-form_token="{{ csrf_token() }}" data-row_class="price-rule-name" data-row_value="price_rule_id" data-reorder_action="{{ route('backend.price_rule.product.reorder') }}">
                    <thead>
                    <tr>
                        <th style="width: 10px;"></th>
                        <th> Rule </th>
                        <th> Price </th>
                        <th> Modification </th>
                        <th> Currency </th>
                        <th> Store </th>
                        <th> Active </th>
                        <th style="width: 20%;"> Actions </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($priceRules as $priceRule)
                        <tr>
                            <td><i class="fa fa-reorder"></i></td>
                            <td class="price-rule-name" data-price_rule_id="{{ $priceRule->id }}">{{ $priceRule->name?$priceRule->name:'-' }}</td>
                            <td> {{ $priceRule->price?PriceFormatter::formatNumber($priceRule->price, $priceRule->currency):'-' }} </td>
                            <td> {{ $priceRule->modification?$priceRule->getModificationOutput():'-' }} </td>
                            <td> {{ $priceRule->currency?CurrencyHelper::getCurrency($priceRule->currency)['iso']:'All' }} </td>
                            <td> {{ $priceRule->store?$priceRule->store->name:'All' }} </td>
                            <td> <i class="fa {{ $priceRule->active?'fa-check text-success':'fa-remove text-danger' }}"></i> </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    {!! Form::open(['route' => ['backend.price_rule.product.delete', 'id' => $priceRule->id]]) !!}
                                    <div class="btn-group btn-group-sm">
                                        <a class="btn btn-default" href="{{ route('backend.price_rule.product.edit', ['id' => $priceRule->id, 'backUrl' => Request::fullUrl()]) }}"><i class="fa fa-pencil"></i> Edit</a>
                                        <button class="btn btn-default" data-toggle="confirmation" data-original-title="Are you sure?" title=""><i class="fa fa-trash-o"></i> Delete</button>
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