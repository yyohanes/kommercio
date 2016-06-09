<?php

namespace Kommercio\Http\Controllers\Backend\PriceRule;

use Illuminate\Support\Facades\Session;
use Kommercio\Facades\CurrencyHelper;
use Kommercio\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kommercio\Http\Requests\Backend\PriceRule\CartPriceRuleFormRequest;
use Kommercio\Models\Customer;
use Kommercio\Models\Order\Order;
use Kommercio\Models\PriceRule\CartPriceRule;
use Kommercio\Models\PriceRule\CartPriceRuleOptionGroup;
use Kommercio\Models\ShippingMethod\ShippingMethod;
use Kommercio\Models\Store;

class CartPriceRuleController extends Controller
{
    public function index()
    {
        $qb = CartPriceRule::orderBy('sort_order', 'ASC');
        $priceRules = $qb->get();

        return view('backend.price_rule.cart.index', [
            'priceRules' => $priceRules
        ]);
    }

    public function create()
    {
        $priceRule = new CartPriceRule();
        $priceRule->active = true;

        $currencyOptions = ['' => 'All Currencies'] + CurrencyHelper::getCurrencyOptions();

        $storeOptions = ['' => 'All Stores'] + Store::getStoreOptions();

        $reductionTypeOptions = CartPriceRule::getModificationTypeOptions();

        $offerTypeOptions = CartPriceRule::getOfferTypeOptions();

        $shippingMethodOptions = ShippingMethod::getShippingMethodObjects()->pluck('name', 'id')->all();

        return view('backend.price_rule.cart.create', [
            'priceRule' => $priceRule,
            'currencyOptions' => $currencyOptions,
            'storeOptions' => $storeOptions,
            'reductionTypeOptions' => $reductionTypeOptions,
            'offerTypeOptions' => $offerTypeOptions,
            'shippingMethodOptions' => $shippingMethodOptions
        ]);
    }

    public function store(CartPriceRuleFormRequest $request)
    {
        $priceRule = new CartPriceRule();
        $priceRule->fill($request->all());

        if($request->has('customer')){
            $customer = Customer::whereField('email', $request->input('customer'))->first();

            if($customer){
                $priceRule->customer()->associate($customer);
            }
        }else{
            $priceRule->customer()->dissociate();
        }

        $priceRule->save();

        $this->processPriceRuleOptionGroups($priceRule, $request);

        return redirect()->route('backend.price_rule.cart.index')->with('success', [$priceRule->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $priceRule = CartPriceRule::findOrFail($id);

        $currencyOptions = ['' => 'All Currencies'] + CurrencyHelper::getCurrencyOptions();

        $storeOptions = ['' => 'All Stores'] + Store::getStoreOptions();

        $reductionTypeOptions = CartPriceRule::getModificationTypeOptions();

        $offerTypeOptions = CartPriceRule::getOfferTypeOptions();

        $shippingMethodOptions = ShippingMethod::getShippingMethodObjects()->pluck('name', 'id')->all();

        return view('backend.price_rule.cart.edit', [
            'priceRule' => $priceRule,
            'currencyOptions' => $currencyOptions,
            'storeOptions' => $storeOptions,
            'reductionTypeOptions' => $reductionTypeOptions,
            'offerTypeOptions' => $offerTypeOptions,
            'shippingMethodOptions' => $shippingMethodOptions
        ]);
    }

    public function update(CartPriceRuleFormRequest $request, $id)
    {
        $priceRule = CartPriceRule::findOrFail($id);
        $priceRule->fill($request->all());

        if($request->has('customer')){
            $customer = Customer::whereField('email', $request->input('customer'))->first();

            if($customer){
                $priceRule->customer()->associate($customer);
            }
        }else{
            $priceRule->customer()->dissociate();
        }

        $priceRule->save();

        $this->processPriceRuleOptionGroups($priceRule, $request);

        return redirect()->route('backend.price_rule.cart.index')->with('success', [$priceRule->name.' has successfully been updated.']);
    }

    public function delete(Request $request, $id)
    {
        $priceRule = CartPriceRule::findOrFail($id);

        if(!$this->deleteable($priceRule->id)){
            return redirect()->back()->withErrors(['Can\'t delete this product. It is used in settled Orders.']);
        }

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
            $priceRule = CartPriceRule::findOrFail($object);
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
        if($priceRule->shippingOptionGroup){
            $shippingOptionGroup = $priceRule->shippingOptionGroup;
        }else{
            $shippingOptionGroup = new CartPriceRuleOptionGroup();
            $shippingOptionGroup->type = CartPriceRuleOptionGroup::TYPE_SHIPPING;
            $priceRule->shippingOptionGroup()->save($shippingOptionGroup);
        }

        $shippingOptionGroup->shippingMethods()->sync($request->input('shipping', []));
    }

    protected function deleteable($id)
    {
        $orderCount = Order::checkout()->whereHasLineItem($id, 'cart_price_rule')->count();

        return $orderCount < 1;
    }
}