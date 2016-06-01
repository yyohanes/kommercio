<?php

namespace Kommercio\Http\Controllers\Backend\PriceRule;

use Illuminate\Support\Facades\Session;
use Kommercio\Facades\CurrencyHelper;
use Kommercio\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kommercio\Http\Requests\Backend\Catalog\PriceRuleFormRequest;
use Kommercio\Models\PriceRule;
use Kommercio\Models\PriceRuleOptionGroup;
use Kommercio\Models\Product;
use Kommercio\Models\Store;

class ProductPriceRuleController extends Controller
{
    public function mini_index(Request $request, $product_id)
    {
        $qb = PriceRule::where('product_id', $product_id)->orderBy('created_at', 'DESC');

        $priceRules = $qb->get();

        $return = view('backend.price_rule.product.mini_index', [
            'priceRules' => $priceRules,
        ])->render();

        return response()->json([
            'html' => $return,
            '_token' => csrf_token()
        ]);
    }

    public function mini_form(Request $request, $product_id, $id=null)
    {
        $product = Product::findOrFail($product_id);

        if($id){
            $priceRule = PriceRule::findOrFail($id);
        }else{
            $priceRule = new PriceRule();
            $priceRule->fill(['active' => TRUE]);
        }

        if($request->has('price_rule')){
            $oldValues = $request->all();
        }else{
            $oldValues['price_rule'] = $priceRule->toArray();
        }

        Session::flashInput($oldValues);

        $currencyOptions = ['' => 'All Currencies'] + CurrencyHelper::getCurrencyOptions();

        $storeOptions = ['' => 'All Stores'] + Store::getStoreOptions();

        $variationOptions = ['' => 'All Variations'] + $product->variations->pluck('name', 'id')->all();

        $reductionTypeOptions = PriceRule::getModificationTypeOptions();

        $form = view('backend.price_rule.product.mini_form', [
            'product' => $product,
            'priceRule' => $priceRule,
            'currencyOptions' => $currencyOptions,
            'storeOptions' => $storeOptions,
            'variationOptions' => $variationOptions,
            'reductionTypeOptions' => $reductionTypeOptions
        ])->render();

        return response()->json([
            'html' => $form,
            '_token' => csrf_token()
        ]);
    }

    public function mini_save(PriceRuleFormRequest $request, $product_id, $id=null)
    {
        $product = Product::findOrFail($product_id);
        $new = FALSE;
        $priceRule = null;

        if(!$id){
            $priceRule = new PriceRule();

            $new = TRUE;
        }else{
            $priceRule = PriceRule::findOrFail($id);
        }

        $priceRule->fill($request->input('price_rule'));
        $product->priceRules()->save($priceRule);

        if($new){
            $message = 'Price rule is successfully created.';
        }else{
            $message = 'Price rule is successfully updated.';
        }

        return response()->json([
            'message' => $message,
            '_token' => csrf_token(),
            'result' => 'success'
        ]);
    }

    public function index()
    {
        $qb = PriceRule::notProductSpecific()->orderBy('sort_order', 'ASC');
        $priceRules = $qb->get();

        return view('backend.price_rule.product.index', [
            'priceRules' => $priceRules
        ]);
    }

    public function create()
    {
        $priceRule = new PriceRule();
        $priceRule->active = true;

        $currencyOptions = ['' => 'All Currencies'] + CurrencyHelper::getCurrencyOptions();

        $storeOptions = ['' => 'All Stores'] + Store::getStoreOptions();

        $reductionTypeOptions = PriceRule::getModificationTypeOptions();

        return view('backend.price_rule.product.create', [
            'priceRule' => $priceRule,
            'currencyOptions' => $currencyOptions,
            'storeOptions' => $storeOptions,
            'reductionTypeOptions' => $reductionTypeOptions
        ]);
    }

    public function store(PriceRuleFormRequest $request)
    {
        $priceRule = new PriceRule();
        $priceRule->fill($request->input('price_rule'));
        $priceRule->save();

        $this->processPriceRuleOptionGroups($priceRule, $request);

        return redirect()->route('backend.price_rule.product.index')->with('success', [$priceRule->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $priceRule = PriceRule::findOrFail($id);

        $currencyOptions = ['' => 'All Currencies'] + CurrencyHelper::getCurrencyOptions();

        $storeOptions = ['' => 'All Stores'] + Store::getStoreOptions();

        $reductionTypeOptions = PriceRule::getModificationTypeOptions();

        $old = old('price_rule_option_groups');

        if(!$old && $priceRule->priceRuleOptionGroups){
            //Flash other attributes because we flashed options value
            $flashedInput = $priceRule->attributesToArray();

            foreach($priceRule->priceRuleOptionGroups as $idx=>$priceRuleOptionGroup){
                $idx += 1;

                foreach($priceRuleOptionGroup->optionFields as $optionField){
                    $flashedInput['options'][$idx][$optionField] = $priceRuleOptionGroup->{$optionField}->pluck('id')->all();
                }

                $flashedInput['price_rule_option_groups'][$idx] = $priceRuleOptionGroup->id;
            }

            Session::flashInput($flashedInput);
        }

        return view('backend.price_rule.product.edit', [
            'priceRule' => $priceRule,
            'currencyOptions' => $currencyOptions,
            'storeOptions' => $storeOptions,
            'reductionTypeOptions' => $reductionTypeOptions
        ]);
    }

    public function update(PriceRuleFormRequest $request, $id)
    {
        $priceRule = PriceRule::with('priceRuleOptionGroups')->findOrFail($id);
        $priceRule->fill($request->input('price_rule'));
        $priceRule->save();

        $this->processPriceRuleOptionGroups($priceRule, $request);

        return redirect()->route('backend.price_rule.product.index')->with('success', [$priceRule->name.' has successfully been updated.']);
    }

    public function delete(Request $request, $id)
    {
        $priceRule = PriceRule::findOrFail($id);

        $priceRule->delete();

        $name = $priceRule->name?'Price rule '.$priceRule->name:'Price rule';

        if($request->ajax()){
            return response()->json([
                'result' => 'success',
                'message' => $name.' has been deleted.',
                '_token' => csrf_token()
            ]);
        }else{
            return redirect()->back()->with('success', [$name.' has been deleted.']);
        }
    }

    public function reorder(Request $request)
    {
        foreach($request->input('objects') as $idx=>$object){
            $priceRule = PriceRule::findOrFail($object);
            $priceRule->update([
                'sort_order' => $idx
            ]);
        }

        if($request->ajax()){
            return response()->json([
                'result' => 'success',
            ]);
        }else{
            return redirect()->route('backend.price_rule.product.index');
        }
    }

    protected function processPriceRuleOptionGroups($priceRule, $request)
    {
        $priceRuleOptionGroupIds = $priceRule->priceRuleOptionGroups->pluck('id')->all();

        $sortOrder = 0;
        foreach($request->input('price_rule_option_groups', []) as $idx=>$priceRuleId){
            $sortOrder += 1;
            if($priceRuleId && in_array($priceRuleId, $priceRuleOptionGroupIds)){
                $priceRuleOptionGroup = PriceRuleOptionGroup::findOrFail($priceRuleId);
            }else{
                $priceRuleOptionGroup = new PriceRuleOptionGroup();
                $priceRuleOptionGroup->priceRule()->associate($priceRule);
            }

            if($request->has('options.'.$idx)){
                $priceRuleOptionGroup->sort_order = $sortOrder;
                $priceRuleOptionGroup->save();

                foreach($priceRuleOptionGroup->optionFields as $optionField){
                    $priceRuleOptionGroup->{$optionField}()->sync($request->input('options.'.$idx.'.'.$optionField, []));
                }
            }else{
                $priceRuleOptionGroup->delete();
            }
        }

        //Delete old ones
        $toBeDeleted = array_diff($priceRuleOptionGroupIds, $request->input('price_rule_option_groups', []));
        if($toBeDeleted){
            PriceRuleOptionGroup::destroy($toBeDeleted);
        }
    }
}