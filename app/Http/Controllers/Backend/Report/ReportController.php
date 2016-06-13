<?php

namespace Kommercio\Http\Controllers\Backend\Report;

use Carbon\Carbon;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Kommercio\Http\Requests;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Store;

class ReportController extends Controller
{
    public function salesYear(Request $request)
    {
        $orderStatusOptions = Order::getStatusOptions();
        $storeOptions = Store::getStoreOptions();

        $ordersByYear = Order::checkout()->selectRaw("DATE_FORMAT(checkout_at, '%Y') AS order_year")->groupBy('order_year')->pluck('order_year', 'order_year')->toArray();
        $yearOptions = $ordersByYear?:[Carbon::now()->format('Y')];

        $filter = [
            'order_date' => [
                'from' => $request->input('search.order_date.from'),
                'to' => $request->input('search.order_date.to')
            ],
            'delivery_date' => [
                'from' => $request->input('search.delivery_date.from'),
                'to' => $request->input('search.delivery_date.to')
            ],
            'status' => $request->input('search.status', [Order::STATUS_PENDING, Order::STATUS_PROCESSING, Order::STATUS_COMPLETED]),
            'store' => $request->input('search.store', array_keys($storeOptions)),
            'year' => $request->input('search.year', key($yearOptions))
        ];

        $qb = Order::selectRaw("DATE_FORMAT(checkout_at, '%c') AS month, SUM(total) AS total, SUM(discount_total) AS discount_total, SUM(shipping_total) AS shipping_total")
            ->whereIn('status', $filter['status'])
            ->whereIn('store_id', $filter['store'])
            ->whereRaw("DATE_FORMAT(checkout_at, '%Y') = ?", [$filter['year']])
            ->groupBy('month');

        $orders = $qb->get();

        $results = [];
        foreach($orders as $order){
            $results[$order->month] = $order;
        }

        //Get Months
        $months = array();
        for ($i = 1; $i <= 12; $i++) {
            $timestamp = mktime(0, 0, 0, $i, 1);
            $months[date('n', $timestamp)] = date('F', $timestamp);
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
        $storeOptions = Store::getStoreOptions();

        $dateTypeOptions = ['checkout_at' => 'Order Date'];
        if(config('project.enable_delivery_date', false)){
            $dateTypeOptions['delivery_date'] = 'Delivery Date';
        }

        $filter = [
            'date_type' => $request->input('search.date_type', key($dateTypeOptions)),
            'date' => [
                'from' => $request->input('search.date.from', $now->format('Y-m-01')),
                'to' => $request->input('search.date.to', $now->format('Y-m-t'))
            ],
            'status' => $request->input('search.status', [Order::STATUS_PENDING, Order::STATUS_PROCESSING, Order::STATUS_COMPLETED]),
            'store' => $request->input('search.store', array_keys($storeOptions)),
        ];

        $year = date_create_from_format('Y-m-d', $filter['date']['from']);

        $qb = Order::selectRaw("DATE_FORMAT(".$filter['date_type'].", '%Y-%m-%d') AS date, SUM(total) AS total, SUM(discount_total) AS discount_total, SUM(shipping_total) AS shipping_total")
            ->whereIn('status', $filter['status'])
            ->whereIn('store_id', $filter['store'])
            ->groupBy('date');

        $qb->whereRaw("DATE_FORMAT(".$filter['date_type'].", '%Y-%m-%d') >= ?", [$filter['date']['from']]);

        $qb->whereRaw("DATE_FORMAT(".$filter['date_type'].", '%Y-%m-%d') <= ?", [$filter['date']['to']]);

        $orders = $qb->get();

        $results = [];
        foreach($orders as $order){
            $results[$order->date] = $order;
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
}
