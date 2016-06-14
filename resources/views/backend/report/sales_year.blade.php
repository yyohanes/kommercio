@extends('backend.master.form_template')

@section('breadcrumb')
    <li>
        <span>Report</span>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>Sales - {{ $filter['year'] }}</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit bordered">
            <div class="portlet-body">
                {!! Form::open(['method' => 'GET']) !!}
                <div class="row">
                    <div class="col-md-3">
                        <label class="control-label">Year</label>

                        {!! Form::select('search[year]',
                        $yearOptions, old('search.year', $filter['year']), [
                        'class' => 'form-control select2']) !!}
                    </div>
                    <div class="col-md-3">
                        <label class="control-label">Status</label>
                        {!! Form::select('search[status][]',
                        $orderStatusOptions, old('search.status', $filter['status']), [
                        'class' => 'form-control select2', 'multiple' => TRUE]) !!}
                    </div>
                    <div class="col-md-2">
                        <label class="control-label">Store</label>
                        {!! Form::select('search[store]',
                        $storeOptions, old('search.store', $filter['store']), [
                        'class' => 'form-control select2']) !!}
                    </div>
                    <div class="col-md-2">
                        <div>&nbsp;</div>
                        <button class="btn btn-info btn-sm"><i class="fa fa-search"></i> Search</button>
                        <a class="btn btn-default btn-sm" href="{{ route('backend.report.sales_year') }}">Reset</a>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>

        <div class="portlet light portlet-fit bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase">Sales - {{ $filter['year'] }}</span>
                </div>
            </div>

            <div class="portlet-body">
                <div id="sales-chart" class="chart" style="height: 400px;"></div>

                <div class="table-scrollable">
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th> Month </th>
                            <th> Sales </th>
                            <th> Discount </th>
                            <th> Shipping </th>
                        </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total = 0;
                            $shippingTotal = 0;
                            $discountTotal = 0;
                            ?>
                            @foreach($months as $idx=>$month)
                                <?php
                                    $total += isset($results[$idx])?$results[$idx]->total:0;
                                    $shippingTotal += isset($results[$idx])?$results[$idx]->shipping_total:0;
                                    $discountTotal += isset($results[$idx])?$results[$idx]->discount_total:0;
                                    $date = date_create_from_format('Y-n', $filter['year'].'-'.$idx);
                                ?>
                                <tr>
                                    <td>{{ $month }} <a class="btn btn-xs btn-link" href="{{ route('backend.report.sales', ['search' => ['date' => ['from' => $date->format('Y-m-01'), 'to' => $date->format('Y-m-t')]]]) }}">View</a></td>
                                    <td>{{ PriceFormatter::formatNumber((isset($results[$idx])?$results[$idx]->total:0)) }}</td>
                                    <td>{{ PriceFormatter::formatNumber((isset($results[$idx])?abs($results[$idx]->discount_total):0)) }}</td>
                                    <td>{{ PriceFormatter::formatNumber((isset($results[$idx])?$results[$idx]->shipping_total:0)) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="text-right">Total</td>
                                <td>{{ PriceFormatter::formatNumber($total) }}</td>
                                <td>{{ PriceFormatter::formatNumber(abs($discountTotal)) }}</td>
                                <td>{{ PriceFormatter::formatNumber($shippingTotal) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('bottom_page_scripts')
    @parent

    <script src="{{ asset('backend/assets/template/global/plugins/amcharts/amcharts/amcharts.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/amcharts/amcharts/serial.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/amcharts/amcharts/pie.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/amcharts/amcharts/radar.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/amcharts/amcharts/themes/light.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/amcharts/amcharts/themes/patterns.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/amcharts/amcharts/themes/chalk.js') }}" type="text/javascript"></script>

    <script src="{{ asset('backend/assets/scripts/pages/report.js') }}" type="text/javascript"></script>

    <script type="text/javascript">
        var chartData = [];

        @foreach($months as $idx=>$month)
        chartData.push({
            'month': '{{ $month }}',
            'sales': {{ isset($results[$idx])?$results[$idx]->total:0 }},
            'sales_print': '{{ PriceFormatter::formatNumber((isset($results[$idx])?$results[$idx]->total:0)) }}',
            'shipping': {{ isset($results[$idx])?$results[$idx]->shipping_total:0 }},
            'shipping_print': '{{ PriceFormatter::formatNumber((isset($results[$idx])?$results[$idx]->shipping_total:0)) }}',
            'discount': {{ isset($results[$idx])?abs($results[$idx]->discount_total):0 }},
            'discount_print': '{{ PriceFormatter::formatNumber((isset($results[$idx])?abs($results[$idx]->discount_total):0)) }}',
        });
        @endforeach

        jQuery(document).ready(function($){
            var salesChart = AmCharts.makeChart("sales-chart", {
                "type": "serial",
                "theme": "light",
                "pathToImages": App.getGlobalPluginsPath() + "amcharts/amcharts/images/",
                "autoMargins": false,
                "marginLeft": 30,
                "marginRight": 8,
                "marginTop": 10,
                "marginBottom": 26,

                "fontFamily": 'Open Sans',
                "color":    '#888',

                "dataProvider": chartData,
                "valueAxes": [{
                    "axisAlpha": 0,
                    "position": "left"
                }],
                "startDuration": 1,
                "graphs": [{
                    "alphaField": "alpha",
                    "balloonText": "<span style='font-size:13px;'>[[title]] in [[category]] : <b>[[sales_print]]</b></span>",
                    "dashLengthField": "dashLengthColumn",
                    "fillAlphas": 1,
                    "title": "Sales",
                    "type": "column",
                    "valueField": "sales"
                }, {
                    "balloonText": "<span style='font-size:13px;'>[[title]] in [[category]] : <b>[[shipping_print]]</b></span>",
                    "bullet": "round",
                    "dashLengthField": "dashLengthLine",
                    "lineThickness": 3,
                    "bulletSize": 7,
                    "bulletBorderAlpha": 1,
                    "bulletColor": "#FFFFFF",
                    "useLineColorForBulletBorder": true,
                    "bulletBorderThickness": 3,
                    "fillAlphas": 0,
                    "lineAlpha": 1,
                    "title": "Shipping",
                    "valueField": "shipping"
                }, {
                    "balloonText": "<span style='font-size:13px;'>[[title]] in [[category]] : <b>[[discount_print]]</b></span>",
                    "bullet": "round",
                    "dashLengthField": "dashLengthLine",
                    "lineColor": "#ff9c00",
                    "lineThickness": 3,
                    "bulletSize": 7,
                    "bulletBorderAlpha": 1,
                    "bulletColor": "#FFFFFF",
                    "useLineColorForBulletBorder": true,
                    "bulletBorderThickness": 3,
                    "fillAlphas": 0,
                    "lineAlpha": 1,
                    "title": "Discount",
                    "valueField": "discount"
                }],
                "categoryField": "month",
                "categoryAxis": {
                    "gridPosition": "start",
                    "axisAlpha": 0,
                    "tickLength": 0
                }
            });
        });
    </script>
@stop
