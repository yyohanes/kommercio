<?php

namespace Kommercio\Http\Controllers\Backend\Utility;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Kommercio\Facades\AddressHelper;
use Kommercio\Http\Requests;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Controllers\Backend\Customer\CustomerController;
use Kommercio\Models\Customer;
use Kommercio\Utility\Export\Batch;
use Kommercio\Utility\Export\Item;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function customer(Request $request)
    {
        // Inject internal_export to $request
        $request->replace($request->all() + ['internal_export' => TRUE]);

        $customerController = new CustomerController();
        $customers = $customerController->index($request);

        $return = $this->processBatch($customers, $request, 'customer', [], function($ids, $rowNumber){
            $data = [];

            if($rowNumber == 0){
                $data[] = ['salute', 'first_name', 'last_name', 'email', 'phone_number', 'address_1', 'address_2', 'area', 'district', 'city', 'state', 'country', 'postal_code', 'customer_since', 'birthday'];
            }

            foreach($ids as $customerId){
                $customer = Customer::find($customerId);
                $customer->loadProfileFields();

                if($customer){
                    $addressFields = AddressHelper::extractAddressFields($customer->getProfile()->getAddress());

                    $data[] = [
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
                        $customer->getProfile()->birthday?\Carbon\Carbon::createFromFormat('Y-m-d', $customer->getProfile()->birthday)->format('d M Y'):''
                    ];
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

    protected function processBatch(Collection $rows, Request $request, $name, $additionalRules = [], \Closure $closure)
    {
        $routeName = $request->route()->getName();

        if($request->isMethod('POST')){
            $rules = [];

            $rules = array_merge($rules, $additionalRules);

            $this->validate($request, $rules);

            $batch = Batch::init($rows, $name);

            return [
                'url' => route($routeName, array_merge(['filter' => $request->input('filter')], ['run' => 1, 'batch_id' => $batch->id, 'row' => 0])),
                'row' => null
            ];
        }else{
            if($request->has('run')){
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
                        'url' => route($routeName, array_merge(['filter' => $request->input('filter')], ['run' => 1, 'batch_id' => $batch->id, 'row' => $request->input('row') + 1])),
                        'row' => $item
                    ];
                }else{
                    $batch->combineFiles();
                    $batch->clean();

                    return redirect()->route($routeName, array_merge(['filter' => $request->input('filter')], ['success' => 1, 'batch_id' => $batch->id]))->with('success', [$batch->name.' is successfully export']);
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

        if($request->has('success') && $request->has('batch_id')){
            $batch = Batch::findOrFail($request->input('batch_id'));
            $rows = $batch->items;
        }else{
            $rows = collect([]);
        }

        $viewOptions = $getAdditionalViewOptions?$getAdditionalViewOptions():[];

        return view($view_name, array_merge([
            'runUrl' => $runUrl,
            'rows' => $rows,
            'filter' => $request->input('filter', [])
        ], $viewOptions));
    }
}
