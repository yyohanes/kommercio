<?php

namespace Kommercio\Http\Controllers\Backend\Report;

use Carbon\Carbon;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Kommercio\Events\ReportEvent;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Http\Requests;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Order\LineItem;
use Kommercio\Models\Order\Order;
use Kommercio\Models\ShippingMethod\ShippingMethod;
use Kommercio\Models\Store;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function salesYear(Request $request)
    {
        $orderStatusOptions = Order::getStatusOptions();

        $storeOptions = Auth::user()->manageAllStores?['all' => 'All Stores']:[];
        $storeOptions += Store::getStoreOptions();

        $ordersByYear = Order::checkout()->selectRaw("DATE_FORMAT(checkout_at, '%Y') AS order_year")->whereNotNull('checkout_at')->groupBy('order_year')->pluck('order_year', 'order_year')->toArray();
        $yearOptions = $ordersByYear ?: [Carbon::now()->format('Y')];

        $filter = [
            'status' => $request->input('search.status', [Order::STATUS_PENDING, Order::STATUS_PROCESSING, Order::STATUS_SHIPPED, Order::STATUS_COMPLETED]),
            'store' => $request->input('search.store', key($storeOptions)),
            'year' => $request->input('search.year', key($yearOptions))
        ];

        $qb = Order::selectRaw("DATE_FORMAT(checkout_at, '%c') AS month, SUM(total) AS total, SUM(discount_total) AS discount_total, SUM(shipping_total) AS shipping_total, SUM(tax_total - tax_error_total) AS tax_total")
            ->whereIn('status', $filter['status']);

        if($filter['store'] != 'all'){
            $qb->where('store_id', $filter['store']);
        }else{
            $qb->whereIn('store_id', array_keys(Store::getStoreOptions()));
        }

        $qb->whereRaw("DATE_FORMAT(checkout_at, '%Y') = ?", [$filter['year']])
            ->groupBy('month');

        Event::fire(new ReportEvent('sales_year', [
            'request' => $request,
            'filter' => $filter,
            'queryBuilder' => $qb
        ]));

        $orders = $qb->get();

        $results = [];
        foreach ($orders as $order) {
            $results[$order->month] = $order;
        }

        //Get Months
        $months = array();
        for ($i = 1; $i <= 12; $i++) {
            $timestamp = mktime(0, 0, 0, $i, 1);
            $months[date('n', $timestamp)] = date('F', $timestamp);
        }

        if ($request->input('internal_export')) {
            return ['filter' => $filter, 'results' => $results];
        }

        return view('backend.report.sales_year', [
            'filter' => $filter,
            'orderStatusOptions' => $orderStatusOptions,
            'storeOptions' => $storeOptions,
            'yearOptions' => $yearOptions,
            'results' => $results,
            'months' => $months
        ]);
    }

    public function sales(Request $request)
    {
        $now = Carbon::now();

        $orderStatusOptions = Order::getStatusOptions();

        $storeOptions = Auth::user()->manageAllStores?['all' => 'All Stores']:[];
        $storeOptions += Store::getStoreOptions();

        $dateTypeOptions = ['checkout_at' => 'Order Date'];
        if (ProjectHelper::getConfig('enable_delivery_date', false)) {
            $dateTypeOptions['delivery_date'] = 'Delivery Date';
        }

        $filter = [
            'date_type' => $request->input('search.date_type', key($dateTypeOptions)),
            'date' => [
                'from' => $request->input('search.date.from', $now->format('Y-m-01')),
                'to' => $request->input('search.date.to', $now->format('Y-m-t'))
            ],
            'status' => $request->input('search.status', [Order::STATUS_PENDING, Order::STATUS_PROCESSING, Order::STATUS_SHIPPED, Order::STATUS_COMPLETED]),
            'store' => $request->input('search.store', key($storeOptions)),
        ];

        $year = date_create_from_format('Y-m-d', $filter['date']['from']);

        $qb = Order::selectRaw("DATE_FORMAT(" . $filter['date_type'] . ", '%Y-%m-%d') AS date, SUM(total) AS total, SUM(discount_total) AS discount_total, SUM(shipping_total) AS shipping_total, SUM(tax_total - tax_error_total) AS tax_total")
            ->whereIn('status', $filter['status'])
            ->groupBy('date');

        if($filter['store'] != 'all'){
            $qb->where('store_id', $filter['store']);
        }else{
            $qb->whereIn('store_id', array_keys(Store::getStoreOptions()));
        }

        $qb->whereRaw("DATE_FORMAT(" . $filter['date_type'] . ", '%Y-%m-%d') >= ?", [$filter['date']['from']]);

        $qb->whereRaw("DATE_FORMAT(" . $filter['date_type'] . ", '%Y-%m-%d') <= ?", [$filter['date']['to']]);

        Event::fire(new ReportEvent('sales', [
            'request' => $request,
            'filter' => $filter,
            'queryBuilder' => $qb
        ]));

        $orders = $qb->get();

        $results = [];
        foreach ($orders as $order) {
            $results[$order->date] = $order;
        }

        if ($request->input('internal_export')) {
            return ['filter' => $filter, 'results' => $results];
        }

        return view('backend.report.sales', [
            'filter' => $filter,
            'orderStatusOptions' => $orderStatusOptions,
            'storeOptions' => $storeOptions,
            'dateTypeOptions' => $dateTypeOptions,
            'results' => $results,
            'year' => $year->format('Y')
        ]);
    }

    public function delivery(Request $request)
    {
        $date = Carbon::now();
        $date->modify('+1 day');

        $orderStatusOptions = Order::getStatusOptions();

        $storeOptions = Auth::user()->manageAllStores?['all' => 'All Stores']:[];
        $storeOptions += Store::getStoreOptions();

        $dateTypeOptions = [];
        if (ProjectHelper::getConfig('enable_delivery_date', false)) {
            $dateTypeOptions['delivery_date'] = 'Delivery Date';
        }
        $dateTypeOptions['checkout_at'] = 'Order Date';

        $shippingMethods = ShippingMethod::getAvailableMethods();
        foreach($shippingMethods as $shippingMethodIdx=>$shippingMethod)
        {
            $shippingMethodOptions[$shippingMethodIdx] = $shippingMethod['name'];
        }

        $filter = [
            'shipping_method' => $request->input('search.shipping_method', array_keys($shippingMethodOptions)),
            'date_type' => $request->input('search.date_type', 'delivery_date'),
            'date' => $request->input('search.date', $date->format('Y-m-d')),
            'status' => $request->input('search.status', ProjectHelper::getConfig('order_options.processed_order_status')),
            'store' => $request->input('search.store', key($storeOptions)),
        ];

        $qb = Order::with('shippingProfile', 'lineItems')
            ->whereIn('orders.status', $filter['status'])
            ->orderBy('checkout_at', 'ASC')
            ->joinOutstanding()
            ->joinShippingProfile();

        if($filter['store'] != 'all'){
            $qb->where('store_id', $filter['store']);
        }else{
            $qb->whereIn('store_id', array_keys(Store::getStoreOptions()));
        }

        if(count($filter['shipping_method']) != count($shippingMethodOptions)){
            $qb->whereHas('lineItems', function($qb) use ($filter){
                $qb->lineItemType('shipping');

                $qb->searchData('shipping_method', $filter['shipping_method']);
            });

            $shippingMethod = 'Orders';
        }else{
            $shippingMethod = 'All Delivery';
        }

        $qb->whereRaw("DATE_FORMAT(" . $filter['date_type'] . ", '%Y-%m-%d') = ?", [$filter['date']]);

        $date = Carbon::createFromFormat('Y-m-d', $filter['date']);
        $dateType = $filter['date_type'];

        $orders = $qb->get();

        if($request->input('print_invoices', false) && Gate::allows('access', ['print_invoice'])){
            return view('backend.report.delivery_print_invoices', [
                'orders' => $orders,
                'print_template' => ProjectHelper::getViewTemplate('print.order.invoice_content')
            ]);
        }

        if($request->input('print_dos', false) && Gate::allows('access', ['print_delivery_note'])){
            return view('backend.report.delivery_print_dos', [
                'orders' => $orders,
                'print_template' => ProjectHelper::getViewTemplate('print.order.delivery_order_content')
            ]);
        }

        //Get Ordered Products. Use custom query because we want to sort by Sort Order
        $orderedProductsQb = LineItem::lineItemType('product')
            ->whereIn('order_id', $orders->pluck('id')->all())
            ->joinProduct()
            ->orderBy('PD.sort_order', 'ASC');

        $orderedProducts = [];

        foreach($orderedProductsQb->get() as $lineItem){
            if(!isset($orderedProducts[$lineItem->line_item_id])){
                $orderedProducts[$lineItem->line_item_id] = [
                    'quantity' => 0,
                    'product' => $lineItem->product
                ];
            }

            $orderedProducts[$lineItem->line_item_id]['quantity'] += $lineItem->quantity;
        }

        if($request->input('export_to_xls', false)){
            Excel::create('Delivery Report '.$filter['date'], function($excel) use ($filter, $orders, $orderedProducts, $shippingMethod, $date, $dateType) {
                $excel->setDescription('Delivery Report '.$filter['date']);
                $excel->sheet('Sheet 1', function($sheet) use ($filter, $orders, $orderedProducts, $shippingMethod, $date, $dateType){
                    $exportTemplate = ProjectHelper::getViewTemplate('backend.report.export.xls.delivery');
                    $sheet->loadView($exportTemplate, [
                        'filter' => $filter,
                        'orders' => $orders,
                        'shippingMethod' => $shippingMethod,
                        'date' => $date,
                        'dateType' => $dateType,
                        'orderedProducts' => $orderedProducts
                    ]);
                });
            })->download('xls');
        }

        $printAllInvoicesUrl = $request->url().'?'.http_build_query(array_merge($request->query(), ['print_invoices' => TRUE]));
        $printAllDosUrl = $request->url().'?'.http_build_query(array_merge($request->query(), ['print_dos' => TRUE]));
        $exportUrl = $request->url().'?'.http_build_query(array_merge($request->query(), ['export_to_xls' => TRUE]));

        return view('backend.report.delivery', [
            'filter' => $filter,
            'orderStatusOptions' => $orderStatusOptions,
            'storeOptions' => $storeOptions,
            'dateTypeOptions' => $dateTypeOptions,
            'date' => $date,
            'dateType' => $dateType,
            'orders' => $orders,
            'orderedProducts' => $orderedProducts,
            'shippingMethodOptions' => $shippingMethodOptions,
            'shippingMethod' => $shippingMethod,
            'printAllInvoicesUrl' => $printAllInvoicesUrl,
            'printAllDosUrl' => $printAllDosUrl,
            'exportUrl' => $exportUrl
        ]);
    }

    public function productionSchedule(Request $request){
        $date = Carbon::now();
        $date->modify('+1 day');

        $orderStatusOptions = Order::getStatusOptions();

        $storeOptions = Auth::user()->manageAllStores?['all' => 'All Stores']:[];
        $storeOptions += Store::getStoreOptions();

        $shippingMethods = ShippingMethod::getAvailableMethods();

        foreach($shippingMethods as $shippingMethodIdx=>$shippingMethod)
        {
            $shippingMethodOptions[$shippingMethodIdx] = $shippingMethod['name'];
        }

        $filter = [
            'shipping_method' => $request->input('search.shipping_method', array_keys($shippingMethodOptions)),
            'date_type' => 'delivery_date',
            'date' => $request->input('search.date', $date->format('Y-m-d')),
            'status' => $request->input('search.status', ProjectHelper::getConfig('order_options.processed_order_status')),
            'store' => $request->input('search.store', 'all'),
        ];

        $qb = Order::with('shippingProfile', 'lineItems')
            ->whereIn('status', $filter['status'])
            ->orderBy('checkout_at', 'ASC')
            ->joinShippingProfile();

        if($filter['store'] != 'all'){
            $qb->where('store_id', $filter['store']);
        }else{
            $qb->whereIn('store_id', array_keys(Store::getStoreOptions()));
        }

        if(count($filter['shipping_method']) != count($shippingMethodOptions)){
            $qb->whereHas('lineItems', function($qb) use ($filter){
                $qb->lineItemType('shipping');

                $qb->searchData('shipping_method', $filter['shipping_method']);
            });
        }

        $qb->whereRaw("DATE_FORMAT(" . $filter['date_type'] . ", '%Y-%m-%d') = ?", [$filter['date']]);

        $deliveryDate = Carbon::createFromFormat('Y-m-d', $filter['date']);

        $orders = $qb->get();

        //Get Ordered Products. Use custom query because we want to sort by Sort Order
        $orderedProductsQb = LineItem::lineItemType('product')
            ->whereIn('order_id', $orders->pluck('id')->all())
            ->joinProduct()
            ->orderBy('PD.sort_order', 'ASC');

        $orderedProducts = [];
        foreach($orderedProductsQb->get() as $lineItem){
            if(!isset($orderedProducts[$lineItem->line_item_id])){
                $orderedProducts[$lineItem->line_item_id] = [
                    'quantity' => 0,
                    'product' => $lineItem->product
                ];
            }

            $orderedProducts[$lineItem->line_item_id]['quantity'] += $lineItem->quantity;
        }

        return view('backend.report.production_schedule', [
            'filter' => $filter,
            'orderStatusOptions' => $orderStatusOptions,
            'storeOptions' => $storeOptions,
            'deliveryDate' => $deliveryDate,
            'orders' => $orders,
            'orderedProducts' => $orderedProducts,
            'shippingMethodOptions' => $shippingMethodOptions,
            'shippingMethod' => $shippingMethod
        ]);
    }
}
