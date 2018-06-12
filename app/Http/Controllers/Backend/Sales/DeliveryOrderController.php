<?php

namespace Kommercio\Http\Controllers\Backend\Sales;

use Illuminate\Http\Request;
use Kommercio\Facades\OrderHelper;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Order\DeliveryOrder\DeliveryOrder;
use Kommercio\Models\Order\Order;
use Kommercio\Models\User;
use Maatwebsite\Excel\Facades\Excel;

class DeliveryOrderController extends Controller{
    public function miniView($delivery_order_id)
    {
        $deliveryOrder = DeliveryOrder::find($delivery_order_id);

        return view('backend.order.delivery_orders.mini_view', [
            'deliveryOrder' => $deliveryOrder
        ]);
    }

    public function quickStatusUpdate(Request $request, $id, $status)
    {
        $deliveryOrder = DeliveryOrder::findOrFail($id);

        if($request->isMethod('GET')){
            return view('backend.order.delivery_orders.quick_status_update_form.'.$status.'_form', [
                'deliveryOrder' => $deliveryOrder,
                'backUrl' => $request->input('backUrl', null)
            ]);
        }else{
            $rules = [];

            if(!in_array($status, [DeliveryOrder::STATUS_CANCELLED, DeliveryOrder::STATUS_SHIPPED])){
                abort(403, 'Delivery order status not found.');
            }

            switch($status){
                case DeliveryOrder::STATUS_CANCELLED:
                    $rules = [
                        'cancel_notes' => 'required'
                    ];

                    if(!$deliveryOrder->isCancellable){
                        abort(403, 'Delivery order is not cancellable.');
                    }

                    $note = $request->input('cancel_notes');
                    break;
                case DeliveryOrder::STATUS_SHIPPED:
                    if(!$deliveryOrder->isShippable){
                        abort(403, 'Delivery order is not shippable.');
                    }
                    $note = null;
                    break;
            }

            $this->validate($request, $rules);

            if(!empty($request->input('notes'))){
                $deliveryOrder->notes = $request->input('notes');
            }

            $deliveryOrder->saveData([
                'tracking_number' => $request->input('tracking_number', $deliveryOrder->getData('tracking_number')),
                'delivered_by' => $request->input('delivered_by', $deliveryOrder->getData('delivered_by'))
            ]);

            $deliveryOrder->save();

            $deliveryOrder->changeStatus($status, $request->input('send_notification', false), $note);

            return redirect($request->input('backUrl', route('backend.sales.order.view', ['id' => $deliveryOrder->order->id])))->with('success', ['Delivery Order #'.$deliveryOrder->reference.' status has been set to '.DeliveryOrder::getStatusOptions($status)]);
        }
    }

    public function resendEmail(Request $request, $id, $process)
    {
        $user = $request->user();
        $deliveryOrder = DeliveryOrder::findOrFail($id);

        if($request->isMethod('GET')){
            $options = [
                'process' => $process,
                'deliveryOrder' => $deliveryOrder,
                'backUrl' => $request->get('backUrl', route('backend.sales.order.view', ['id' => $deliveryOrder->order->id]))
            ];

            return view('backend.order.delivery_orders.resend_email', $options);
        }else{
            $rules = [
                'email' => 'required|email'
            ];
            $this->validate($request, $rules);

            switch($process){
                case 'shipped':
                    $orderComment = 'Resend Delivery Order #'.$deliveryOrder->reference.' shipped email.';
                    break;
                default:
                    return response('No process is selected.');
                    break;
            }

            OrderHelper::saveOrderComment($orderComment, 'delivery_order_'.$process, $deliveryOrder->order, $user);
            OrderHelper::sendDeliveryOrderEmail($deliveryOrder, $process, $request->input('email'));

            $message = ucfirst($process).' email is successfully queued for resend.';

            return redirect($request->input('backUrl', route('backend.sales.order.view', ['id' => $deliveryOrder->order->id])))->with('success', [$message]);
        }
    }

    public function printDeliveryOrder(Request $request, $id, $type = null)
    {
        $user = $request->user();
        $deliveryOrder = DeliveryOrder::findOrFail($id);

        if ($type === 'packaging_slip')
            return $this->printPackagingSlip($deliveryOrder, $user);

        OrderHelper::saveOrderComment('Delivery Order #'.$deliveryOrder->reference.' is printed.', 'print_delivery_order', $deliveryOrder->order, $user);

        if(config('project.print_format', config('kommercio.print_format')) == 'xls'){
            Excel::create('Delivery Order #'.$deliveryOrder->reference, function($excel) use ($deliveryOrder) {
                $excel->setDescription('Delivery Order #'.$deliveryOrder->reference);
                $excel->sheet('Sheet 1', function($sheet) use ($deliveryOrder, $excel){
                    $sheet->loadView(ProjectHelper::getViewTemplate('print.excel.order.delivery_order'), [
                        'deliveryOrder' => $deliveryOrder,
                        'excel' => $excel
                    ]);
                });
            })->download('xls');
        }

        return view(ProjectHelper::getViewTemplate('print.order.delivery_order'), [
            'deliveryOrder' => $deliveryOrder,
            'order' => $deliveryOrder->order
        ]);
    }

    protected function printPackagingSlip(DeliveryOrder $deliveryOrder, User  $user)
    {
        $shippingMethod = $deliveryOrder->shippingMethod;

        if (!$shippingMethod) {
            return redirect()->back()->withErrors(['Delivery Order doesn\'t have any shipping method attached to it.']);
        }

        OrderHelper::saveOrderComment('Packaging Slip for Delivery Order #'.$deliveryOrder->reference.' is printed.', 'print_packaging_slip', $deliveryOrder->order, $user);

        if ($shippingMethod->getProcessor()->useCustomPackagingSlip($deliveryOrder)) {
            $customPackagingSlip = $shippingMethod->getProcessor()->customPackagingSlip($deliveryOrder);

            if ($customPackagingSlip) {
                $fileName = $deliveryOrder->order->reference . '.pdf';

                return response($customPackagingSlip)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Description', 'DHL label ' . $deliveryOrder->order->reference)
                    ->header('Content-Disposition', 'attachment; filename=' . $fileName)
                    ->header('Filename', $fileName);
            };
        }

        return view(ProjectHelper::getViewTemplate('print.order.delivery_order'), [
            'deliveryOrder' => $deliveryOrder,
            'order' => $deliveryOrder->order,
        ]);
    }
}
