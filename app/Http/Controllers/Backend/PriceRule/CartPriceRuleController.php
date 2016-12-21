<?php

namespace Kommercio\Http\Controllers\Backend\PriceRule;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Kommercio\Facades\CurrencyHelper;
use Kommercio\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kommercio\Http\Requests\Backend\PriceRule\CartPriceRuleFormRequest;
use Kommercio\Models\Customer;
use Kommercio\Models\Order\Order;
use Kommercio\Models\PriceRule\CartPriceRule;
use Kommercio\Models\PriceRule\CartPriceRuleOptionGroup;
use Kommercio\Models\PriceRule\Coupon;
use Kommercio\Models\Product;
use Kommercio\Models\ShippingMethod\ShippingMethod;
use Kommercio\Models\Store;

class CartPriceRuleController extends Controller
{
    public function index()
    {
        $qb = CartPriceRule::orderBy('sort_order', 'ASC');

        $qb->whereNull('store_id')->orWhereIn('store_id', Auth::user()->getManagedStores()->pluck('id')->all());

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

        $storeOptions = Auth::user()->manageAllStores?['' => 'All Stores']:[];
        $storeOptions += Store::getStoreOptions();

        $reductionTypeOptions = CartPriceRule::getModificationTypeOptions();

        $offerTypeOptions = CartPriceRule::getOfferTypeOptions();

        $modificationSourceOptions = CartPriceRule::getModificationSourceOptions();

        $shippingMethodOptions = ShippingMethod::getShippingMethodObjects()->pluck('name', 'id')->all();

        $defaultProducts = [];
        foreach(old('products', []) as $item){
            $itemObj = Product::findOrFail($item);
            $defaultProducts[$itemObj->id] = $itemObj->getName();
        }

        return view('backend.price_rule.cart.create', [
            'priceRule' => $priceRule,
            'currencyOptions' => $currencyOptions,
            'storeOptions' => $storeOptions,
            'reductionTypeOptions' => $reductionTypeOptions,
            'offerTypeOptions' => $offerTypeOptions,
            'shippingMethodOptions' => $shippingMethodOptions,
            'modificationSourceOptions' => $modificationSourceOptions,
            'defaultProducts' => $defaultProducts
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

        $lastSortOrder = CartPriceRule::orderBy('sort_order', 'DESC')->first();
        $priceRule->sort_order = $lastSortOrder?$lastSortOrder->sort_order+1:0;

        $priceRule->save();
        $priceRule->products()->sync($request->input('products', []));

        $this->processPriceRuleOptionGroups($priceRule, $request);

        return redirect()->route('backend.price_rule.cart.index')->with('success', [$priceRule->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $user = Auth::user();

        $priceRule = CartPriceRule::findOrFail($id);

        if(!$user->can('manage_store', [$priceRule])){
            abort(401);
        }

        $currencyOptions = ['' => 'All Currencies'] + CurrencyHelper::getCurrencyOptions();

        $storeOptions = $user->manageAllStores?['' => 'All Stores']:[];
        $storeOptions += Store::getStoreOptions();

        $reductionTypeOptions = CartPriceRule::getModificationTypeOptions();

        $offerTypeOptions = CartPriceRule::getOfferTypeOptions();

        $modificationSourceOptions = CartPriceRule::getModificationSourceOptions();

        $shippingMethodOptions = ShippingMethod::getShippingMethodObjects()->pluck('name', 'id')->all();

        $defaultProducts = [];
        foreach(old('items', $priceRule->products) as $item){
            $defaultProducts[$item->id] = $item->getName();
        }

        $oldCartPriceOptionGroups = old('cart_price_rule_option_groups');

        if(!$oldCartPriceOptionGroups && $priceRule->productOptionGroups){
            //Flash other attributes because we flashed options value
            $flashedInput = $priceRule->attributesToArray();

            foreach($priceRule->productOptionGroups as $idx=>$priceRuleOptionGroup){
                $idx += 1;

                foreach($priceRuleOptionGroup->optionFields as $optionField){
                    $flashedInput['options'][$idx][$optionField] = $priceRuleOptionGroup->{$optionField}->pluck('id')->all();
                }

                $flashedInput['cart_price_rule_option_groups'][$idx] = $priceRuleOptionGroup->id;
            }

            Session::flashInput($flashedInput);
        }

        return view('backend.price_rule.cart.edit', [
            'priceRule' => $priceRule,
            'currencyOptions' => $currencyOptions,
            'storeOptions' => $storeOptions,
            'reductionTypeOptions' => $reductionTypeOptions,
            'offerTypeOptions' => $offerTypeOptions,
            'shippingMethodOptions' => $shippingMethodOptions,
            'modificationSourceOptions' => $modificationSourceOptions,
            'defaultProducts' => $defaultProducts
        ]);
    }

    public function update(CartPriceRuleFormRequest $request, $id)
    {
        $user = Auth::user();

        $priceRule = CartPriceRule::findOrFail($id);

        if(!$user->can('manage_store', [$priceRule])){
            abort(401);
        }

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
        $priceRule->products()->sync($request->input('products', []));

        $this->processPriceRuleOptionGroups($priceRule, $request);

        return redirect()->route('backend.price_rule.cart.index')->with('success', [$priceRule->name.' has successfully been updated.']);
    }

    public function delete(Request $request, $id)
    {
        $user = Auth::user();

        $priceRule = CartPriceRule::findOrFail($id);

        if(!$user->can('manage_store', [$priceRule])){
            abort(401);
        }

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

    public function couponIndex($cart_price_rule_id)
    {
        $cartPriceRule = CartPriceRule::findOrFail($cart_price_rule_id);

        $coupons = $cartPriceRule->coupons;

        $index = view('backend.price_rule.cart.coupon.mini_index', [
            'coupons' => $coupons,
            'cartPriceRule' => $cartPriceRule
        ])->render();

        return response()->json([
            'html' => $index,
            '_token' => csrf_token()
        ]);
    }

    public function couponForm($cart_price_rule_id, $id = null)
    {
        $cartPriceRule = CartPriceRule::findOrFail($cart_price_rule_id);
        $coupon = Coupon::find($id);

        $form = view('backend.price_rule.cart.coupon.mini_form', [
            'cartPriceRule' => $cartPriceRule,
            'coupon' => $coupon
        ])->render();

        //Clear flashed input
        Session::pull('_old_input');

        return response()->json([
            'html' => $form,
            '_token' => csrf_token()
        ]);
    }

    public function couponSave(Request $request, $cart_price_rule_id)
    {
        $cartPriceRule = CartPriceRule::findOrFail($cart_price_rule_id);

        $rules = [
            'coupon_code' => 'required|unique:coupons,coupon_code'.($request->has('coupon_id')?','.$request->input('coupon_id'):null),
            'customer_id' => 'integer|exists:customers,id',
            'coupon_id' => 'integer'
        ];

        $this->validate($request, $rules);

        $coupon = Coupon::find($request->input('coupon_id', null));

        if(!$coupon){
            $coupon = new Coupon();
        }

        $coupon->fill($request->all());

        if(!$request->has('max_usage')){
            $coupon->max_usage = null;
        }

        $coupon->cartPriceRule()->associate($cartPriceRule);

        if($request->has('customer_id') && $request->has('customer')){
            $coupon->customer()->associate($request->input('customer_id'));
        }else{
            $coupon->customer()->dissociate();
        }

        $coupon->save();

        $message = 'Coupon code '.$coupon->coupon_code.' is successfully saved.';

        return response()->json([
            'result' => 'success',
            'message' => $message
        ]);
    }

    public function couponDelete($cart_price_rule_id, $id)
    {
        $cartPriceRule = CartPriceRule::findOrFail($cart_price_rule_id);
        $coupon = Coupon::findOrFail($id);

        if(!in_array($coupon->id, $cartPriceRule->coupons->pluck('id')->all())){
            return response()->json(['Coupon doesn\'t belong to Price Rule'], 422);
        }

        $message = 'Coupon code '.$coupon->coupon_code.' is successfully deleted.';

        $coupon->delete();

        return response()->json([
            'result' => 'success',
            'message' => $message
        ]);
    }

    protected function processPriceRuleOptionGroups($priceRule, $request)
    {
        if(!empty($request->input('shipping', []))){
            if($priceRule->shippingOptionGroup){
                $shippingOptionGroup = $priceRule->shippingOptionGroup;
            }else{
                $shippingOptionGroup = new CartPriceRuleOptionGroup();
                $shippingOptionGroup->type = CartPriceRuleOptionGroup::TYPE_SHIPPING;
                $priceRule->shippingOptionGroup()->save($shippingOptionGroup);
            }

            $shippingOptionGroup->shippingMethods()->sync($request->input('shipping', []));
        }elseif($priceRule->shippingOptionGroup){
            $priceRule->shippingOptionGroup->delete();
        }

        $priceRuleOptionGroupIds = $priceRule->productOptionGroups->pluck('id')->all();

        $sortOrder = 0;
        foreach($request->input('cart_price_rule_option_groups', []) as $idx=>$priceRuleId){
            $sortOrder += 1;
            if($priceRuleId && in_array($priceRuleId, $priceRuleOptionGroupIds)){
                $priceRuleOptionGroup = CartPriceRuleOptionGroup::findOrFail($priceRuleId);
            }else{
                $priceRuleOptionGroup = new CartPriceRuleOptionGroup();
                $priceRuleOptionGroup->type = CartPriceRuleOptionGroup::TYPE_PRODUCTS;
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
        $toBeDeleted = array_diff($priceRuleOptionGroupIds, $request->input('cart_price_rule_option_groups', []));
        if($toBeDeleted){
            CartPriceRuleOptionGroup::destroy($toBeDeleted);
        }
    }

    protected function deleteable($id)
    {
        $orderCount = Order::checkout()->whereHasLineItem($id, 'cart_price_rule')->count();

        return $orderCount < 1;
    }
}