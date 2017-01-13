<?php

namespace Kommercio\Http\Controllers\Backend\Utility;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Kommercio\Http\Requests;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Manufacturer;
use Kommercio\Models\ProductAttribute\ProductAttribute;
use Kommercio\Utility\Import\Batch;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function manufacturer(Request $request)
    {
        if($request->isMethod('POST')){
            $rules = [
                'file' => 'required|mimes:xlsx,xls'
            ];

            //$this->validate($request, $rules);

            $file = $request->file('file');

            $timestamp = str_replace([' ', ':'], '-', Carbon::now()->toDateTimeString());
            $name = 'import-'.$timestamp. '-' .$file->getClientOriginalName();

            $importBatch = Batch::create([
                'name' => $name
            ]);

            $file->move(storage_path('tmp'), $name);

            $fullFilePath = storage_path('tmp').'/'.$name;

            $foul = [];

            Excel::load($fullFilePath, function($reader) use (&$foul, $importBatch){
                // Getting all results
                $results = $reader->get();

                foreach($results as $idx=>$result) {
                    $manufacturer = Manufacturer::where('name', $result->name)->first();

                    if(!$manufacturer){
                        $manufacturer = new Manufacturer();
                    }

                    $manufacturer->name = $result->name;
                    $manufacturer->deleteMedia('logo');
                    $manufacturer->save();

                    $newMedia = [];

                    if($result->image){
                        $downloadedImage = \Kommercio\Models\File::downloadFromUrl($result->image);

                        if($downloadedImage){
                            $newMedia[$downloadedImage->id] = [
                                'type' => 'logo'
                            ];
                        }
                    }

                    $manufacturer->syncMedia($newMedia, 'logo');
                }
            });

            File::delete($fullFilePath);

            return redirect()->back()->with('success', ['Manufacturer imported successfully.']);
        }

        return view('backend.utility.import.manufacturer');
    }

    public function productAttribute(Request $request)
    {
        $productAttributes = ProductAttribute::orderBy('sort_order', 'ASC')->get();
        $productAttributeOptions = [];

        foreach($productAttributes as $productAttribute){
            $productAttributeOptions[$productAttribute->id] = $productAttribute->name;
        }

        return view('backend.utility.import.product_attribute', [
            'productAttributeOptions' => $productAttributeOptions
        ]);
    }

    public function product(Request $request)
    {
        return view('backend.utility.import.product');
    }
}
