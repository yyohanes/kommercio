<?php

namespace Kommercio\Http\Controllers\Backend\Report;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Barryvdh\DomPDF\Facade as PDF;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Order\DeliveryOrder\DeliveryOrder;
use Kommercio\Models\ShippingMethod\ShippingMethod;
use Kommercio\Models\Store;

class ShipmentController extends Controller
{
    public function index(Request $request)
    {
        $date = Carbon::now();
        $date->modify('+1 day');

        $statusOptions = DeliveryOrder::getStatusOptions();

        $storeOptions = Auth::user()->manageAllStores ? ['all' => 'All Stores'] : [];
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

        $tableName = with(new DeliveryOrder())->getTable();
        $qb = DeliveryOrder::with(['shippingProfile', 'items', 'order'])
            ->whereIn($tableName . '.status', $filter['status'])
            ->whereHas('order', function($query) use ($filter) {
                $query->whereRaw("DATE_FORMAT(" . $filter['date_type'] . ", '%Y-%m-%d') = ?", [$filter['date']]);
            })
            ->orderBy('created_at', 'DESC');

        if($filter['store'] != 'all'){
            $qb->where('store_id', $filter['store']);
        }else{
            $qb->whereIn('store_id', array_keys(Store::getStoreOptions()));
        }

        if(count($filter['shipping_method']) != count($shippingMethodOptions)){
            $qb->searchData('shipping_method', $filter['shipping_method']);

            $shippingMethod = 'Orders';
        }else{
            $shippingMethod = 'All Delivery';
        }

        $qb->whereRaw("DATE_FORMAT(" . $filter['date_type'] . ", '%Y-%m-%d') = ?", [$filter['date']]);

        $date = Carbon::createFromFormat('Y-m-d', $filter['date']);
        $dateType = $filter['date_type'];

        $deliveryOrders = $qb->get();

        if ($request->input('print_invoices', false) && Gate::allows('access', ['print_invoice'])) {
            $orders = $deliveryOrders->map(function($deliveryOrder) {
                return $deliveryOrder->order;
            });

            return view('backend.report.delivery_print_invoices', [
                'orders' => $orders,
                'print_template' => ProjectHelper::getViewTemplate('print.order.invoice_content')
            ]);
        }

        if ($request->input('print_dos', false) && Gate::allows('access', ['print_delivery_order'])) {
            $orders = $deliveryOrders->map(function($deliveryOrder) {
                return $deliveryOrder->order;
            });

            return view('backend.report.delivery_print_dos', [
                'orders' => $orders,
                'print_template' => ProjectHelper::getViewTemplate('print.order.delivery_order_content')
            ]);
        }

        if ($request->input('download_packaging_slips', false) && Gate::allows('access', ['print_delivery_order'])) {
            $tmpFolder = sys_get_temp_dir();

            // Collect all packaging slip and zip it
            $errors = [];
            $queue = [];
            foreach ($deliveryOrders as $deliveryOrder) {
                $shippingMethod = $deliveryOrder->shippingMethod;
                try {
                    if ($shippingMethod->getProcessor()->useCustomPackagingSlip($deliveryOrder)) {
                        $packagingSlip = $shippingMethod->getProcessor()->customPackagingSlip($deliveryOrder);
                    } else {
                        $packagingSlip = PDF::loadView(ProjectHelper::getViewTemplate('print.order.delivery_order'), [
                            'deliveryOrder' => $deliveryOrder,
                            'order' => $deliveryOrder->order,
                        ])->output();
                    }

                    // Store contents to tmp
                    $fileName = $deliveryOrder->order->reference . '.pdf';
                    $pdfPath = $tmpFolder . '/' . $fileName;
                    File::put($pdfPath, $packagingSlip);

                    $queue[$fileName] = $pdfPath;
                } catch (\Throwable $e) {
                    \Log::error($e);
                    $errors[] = 'Error generating packaging slip order #' . $deliveryOrder->order->reference. '. Reason: ' . $e->getMessage();
                }
            }

            if (count($errors) > 0) {
                return redirect()->back()->withErrors($errors);
            }

            try {
                $destinationFile = $tmpFolder . '/packaging-slips.zip';
                $zip = new \ZipArchive();

                if ($zip->open($destinationFile, \ZipArchive::CREATE) === TRUE) {
                    foreach ($queue as $fileName => $pdfPath) {
                        $zip->addFile($pdfPath, $fileName);
                    }

                    $zip->close();
                }
            } catch (\Throwable $e) {
                \Log::error($e);
                return redirect()->back()->withErrors([
                    'Error occurred. Please contact Tech',
                ]);
            }

            return response()->download($destinationFile);
        }

        $printAllInvoicesUrl = $request->url() . '?'. http_build_query(array_merge($request->query(), ['print_invoices' => TRUE]));
        $printAllDosUrl = $request->url() . '?' . http_build_query(array_merge($request->query(), ['print_dos' => TRUE]));
        $downloadPackagingSlips = $request->url() . '?' . http_build_query(array_merge($request->query(), ['download_packaging_slips' => TRUE]));

        return view('backend.report.shipment.shipment', [
            'filter' => $filter,
            'statusOptions' => $statusOptions,
            'storeOptions' => $storeOptions,
            'dateTypeOptions' => $dateTypeOptions,
            'date' => $date,
            'dateType' => $dateType,
            'deliveryOrders' => $deliveryOrders,
            'shippingMethodOptions' => $shippingMethodOptions,
            'shippingMethod' => $shippingMethod,
            'printAllInvoicesUrl' => $printAllInvoicesUrl,
            'printAllDosUrl' => $printAllDosUrl,
            'downloadPackagingSlips' => $downloadPackagingSlips,
        ]);
    }
}
