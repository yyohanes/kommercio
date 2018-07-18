<?php

namespace Kommercio\Http\Controllers\Backend\Utility;

use Illuminate\Support\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;
use Kommercio\Facades\AddressHelper;
use Kommercio\Http\Controllers\Backend\Report\ReportController;
use Kommercio\Http\Requests;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Controllers\Backend\Customer\CustomerController;
use Kommercio\Http\Controllers\Backend\Sales\OrderController;
use Kommercio\Models\Customer;
use Kommercio\Models\Order\Order;
use Kommercio\Utility\Export\Batch;
use Kommercio\Utility\Export\Item;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function salesReport(Request $request)
    {
        // Inject internal_export to $request
        $request->replace($request->all() + ['internal_export' => TRUE]);

        $reportController = new ReportController();

        if ($request->filled('search.year')) {
            $resultsFromController = $reportController->salesYear($request);
            $filter = $resultsFromController['filter'];
            $results = collect($resultsFromController['results']);

            $year = $filter['year'];

            $months = array();
            for ($i = 1; $i <= 12; $i++) {
                $timestamp = mktime(0, 0, 0, $i, 1);
                $months[date('n', $timestamp)] = date('F', $timestamp);
            }

            $resultsMonths = collect([]);

            foreach ($months as $idx => $month) {
                $resultsMonths[] = [
                    $month.' '.$year,
                    isset($results[$idx])?$results[$idx]->total:0,
                    isset($results[$idx])?abs($results[$idx]->discount_total):0,
                    isset($results[$idx])?$results[$idx]->shipping_total:0,
                    isset($results[$idx])?$results[$idx]->tax_total:0,
                ];
            }

            $return = $this->processBatch($resultsMonths, $request, 'sales_report', [], function($resultsMonths, $rowNumber){
                $data = [];

                if($rowNumber == 0){
                    $data[] = ['month', 'sales', 'discount', 'shipping', 'tax'];
                }

                foreach ($resultsMonths as $resultsMonth) {
                    $data[] = $resultsMonth;
                }

                return [
                    'rows' => $data
                ];
            });
        } else {
            $resultsFromController = $reportController->sales($request);
            $filter = $resultsFromController['filter'];
            $results = collect($resultsFromController['results']);

            $dateFrom = \Carbon\Carbon::createFromFormat('Y-m-d', $filter['date']['from']);
            $dateTo = \Carbon\Carbon::createFromFormat('Y-m-d', $filter['date']['to']);

            $resultsDates = collect([]);

            while ($dateFrom->lte($dateTo)) {
                $idx = $dateFrom->format('Y-m-d');

                $resultsDates[] = [
                    $dateFrom->format('d F y'),
                    isset($results[$idx])?$results[$idx]->total:0,
                    isset($results[$idx])?abs($results[$idx]->discount_total):0,
                    isset($results[$idx])?$results[$idx]->shipping_total:0,
                    isset($results[$idx])?$results[$idx]->tax_total:0,
                ];

                $dateFrom->modify('+1 day');
            }

            $return = $this->processBatch($resultsDates, $request, 'sales_report', [], function($resultsDates, $rowNumber){
                $data = [];

                if($rowNumber == 0){
                    $data[] = ['date', 'sales', 'discount', 'shipping', 'tax'];
                }

                foreach ($resultsDates as $resultsDate) {
                    $data[] = $resultsDate;
                }

                return [
                    'rows' => $data
                ];
            });
        }

        return $this->processResponse('backend.utility.export.form.sales_report', $return, $request, function() use ($filter) {
            return [
                'filter' => $filter
            ];
        });
    }

    public function customer(Request $request)
    {
        // Inject internal_export to $request
        $request->replace($request->all() + ['internal_export' => TRUE]);

        $customerController = new CustomerController();
        $customers = $customerController->index($request);

        $return = $this->processBatch($customers, $request, 'customer', [], function($ids, $rowNumber){
            $data = [];

            if($rowNumber == 0){
                $headers = ['salute', 'first_name', 'last_name', 'email', 'phone_number', 'address_1', 'address_2', 'area', 'district', 'city', 'state', 'country', 'postal_code', 'customer_since', 'last_seen', 'birthday'];

                if (Gate::allows('access', ['view_sales_report'])) {
                    $headers = array_merge($headers, [
                        'num_orders',
                        'orders_total',
                    ]);
                }

                $data[] = $headers;
            }

            foreach($ids as $customerId){
                $customer = Customer::find($customerId);
                $customer->loadProfileFields();

                if($customer){
                    $addressFields = AddressHelper::extractAddressFields($customer->getProfile()->getAddress());

                    $rowData = [
                        $customer->salute?Customer::getSaluteOptions($customer->salute):'',
                        $customer->getProfile()->first_name,
                        $customer->getProfile()->last_name,
                        $customer->getProfile()->email,
                        $customer->getProfile()->phone_number,
                        $addressFields['address_1'],
                        $addressFields['address_2'],
                        $addressFields['area'],
                        $addressFields['district'],
                        $addressFields['city'],
                        $addressFields['state'],
                        $addressFields['country'],
                        $addressFields['postal_code'],
                        $customer->created_at->format('d M Y, H:i:s'),
                        $customer->last_active ? $customer->last_active->format('d M Y, H:i:s') : '',
                        $customer->getProfile()->birthday?\Carbon\Carbon::createFromFormat('Y-m-d', $customer->getProfile()->birthday)->format('d M Y'):''
                    ];

                    if (Gate::allows('access', ['view_sales_report'])) {
                        $orders = Order::whereIn('status', Order::getUsageCountedStatus())
                            ->where('customer_id', $customer->id)->get();
                        $rowData = array_merge($rowData, [
                            $orders->count(),
                            $orders->sum('total'),
                        ]);
                    }

                    $data[] = $rowData;
                }
            }

            return [
                'rows' => $data
            ];
        });

        return $this->processResponse('backend.utility.export.form.customer', $return, $request, function() use ($customers){
            $totalCustomers = $customers->count();

            return [
                'totalCustomers' => $totalCustomers
            ];
        });
    }

    public function order(Request $request)
    {
        // Inject internal_export to $request
        $request->replace($request->all() + ['internal_export' => TRUE]);

        $orderController = new OrderController();
        $orders = $orderController->index($request);

        $return = $this->processBatch($orders, $request, 'order', [], function($ids, $rowNumber){
            $data = [];

            if($rowNumber == 0){
                $data[] = ['reference', 'checkout_at', 'delivery_date', 'customer', 'customer_name', 'customer_phone', 'recipient', 'recipient_name', 'recipient_phone', 'total', 'payment_method', 'outstanding', 'status', 'store'];
            }

            foreach($ids as $orderId){
                $order = Order::find($orderId);

                if($order){
                    $data[] = [
                        $order->reference,
                        $order->checkout_at ? $order->checkout_at->format('Y-m-d H:i:s') : null,
                        $order->delivery_date ? $order->delivery_date->format('Y-m-d H:i:s') : null,
                        $order->billingInformation->email,
                        $order->billingInformation->full_name,
                        $order->billingInformation->phone_number,
                        $order->shippingInformation->email,
                        $order->shippingInformation->full_name,
                        $order->shippingInformation->phone_number,
                        $order->total,
                        $order->paymentMethod->name,
                        $order->getOutstandingAmount(),
                        Order::getStatusOptions($order->status, TRUE),
                        $order->store->name,
                    ];
                }
            }

            return [
                'rows' => $data
            ];
        });

        return $this->processResponse('backend.utility.export.form.order', $return, $request, function() use ($orders){
            $totalOrders = $orders->count();

            return [
                'totalOrders' => $totalOrders
            ];
        });
    }

    protected function processBatch(Collection $rows, Request $request, $name, $additionalRules = [], \Closure $closure)
    {
        $routeName = $request->route()->getName();

        if($request->isMethod('POST')){
            $rules = [];

            $rules = array_merge($rules, $additionalRules);

            $this->validate($request, $rules);

            $batch = Batch::init($rows, $name);

            return [
                'url' => route($routeName, array_merge($request->except('backUrl'), ['run' => 1, 'batch_id' => $batch->id, 'row' => 0])),
                'row' => null
            ];
        }else{
            if($request->filled('run')){
                $rules = [
                    'batch_id' => 'required|integer|exists:export_batches,id',
                    'row' => 'required|integer'
                ];

                $validator = Validator::make($request->all(), $rules);

                if ($validator->fails()) {
                    $errors = $validator->errors()->getMessages();

                    return redirect()->back()->withErrors($errors);
                }

                $batch = Batch::findOrFail($request->input('batch_id'));

                if($batch->hasRow($request->input('row'))){
                    $item = $batch->process($request->input('row'), $closure);

                    return [
                        'url' => route($routeName, array_merge($request->except('backUrl'), ['run' => 1, 'batch_id' => $batch->id, 'row' => $request->input('row') + 1])),
                        'row' => $item
                    ];
                }else{
                    $batch->combineFiles();
                    $batch->clean();

                    return redirect()->route($routeName, array_merge($request->except(['backUrl', 'row', 'run']), ['success' => 1, 'batch_id' => $batch->id]))->with('success', [$batch->name.' is successfully export']);
                }
            }
        }
    }

    public function download($batch_id)
    {
        $batch = Batch::findOrFail($batch_id);

        Excel::load($batch->getStoragePath().'/'.$batch->getFilename())->convert('xls');
    }

    protected function processResponse($view_name, $return, Request $request, \Closure $getAdditionalViewOptions = null)
    {
        if($request->ajax()){
            if($return instanceof RedirectResponse){
                $json = [
                    'nextUrl' => null,
                    'reload' => $return->getTargetUrl(),
                    'row' => null
                ];
            }else{
                $json = [
                    'nextUrl' => $return['url'],
                    'reload' => null,
                    'row' => $return['row']
                ];
            }

            return new JsonResponse($json);
        }

        if($return instanceof RedirectResponse){
            return $return;
        }else{
            $runUrl = $return['url'];
        }

        if($request->filled('success') && $request->filled('batch_id')){
            $batch = Batch::findOrFail($request->input('batch_id'));
            $rows = $batch->items;
        }else{
            $rows = collect([]);
        }

        $viewOptions = $getAdditionalViewOptions?$getAdditionalViewOptions():[];

        return view($view_name, array_merge([
            'runUrl' => $runUrl,
            'rows' => $rows,
        ], $request->except('backUrl'), $viewOptions));
    }
}
