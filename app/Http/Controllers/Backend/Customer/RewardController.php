<?php

namespace Kommercio\Http\Controllers\Backend\Customer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kommercio\Facades\CurrencyHelper;
use Kommercio\Http\Requests;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\PriceRule\CartPriceRule;
use Kommercio\Models\RewardPoint\Reward;
use Kommercio\Http\Requests\Backend\Customer\RewardFormRequest;
use Kommercio\Models\Store;

class RewardController extends Controller
{
    public function index()
    {
        $qb = Reward::query();

        $qb->whereNull('store_id')->orWhereIn('store_id', Auth::user()->getManagedStores()->pluck('id')->all());

        $rewards = $qb->get();

        return view('backend.customer.reward.index', [
            'rewards' => $rewards,
        ]);
    }

    public function create()
    {
        $reward = new Reward([
            'active' => TRUE
        ]);

        $typeOptions = Reward::getTypeOptions();

        $storeOptions = Auth::user()->manageAllStores?['' => 'All Stores']:[];
        $storeOptions += Store::getStoreOptions();

        $cartPriceRuleOptions = CartPriceRule::pluck('name', 'id')->all();

        return view('backend.customer.reward.create', [
            'reward' => $reward,
            'typeOptions' => $typeOptions,
            'storeOptions' => $storeOptions,
            'cartPriceRuleOptions' => $cartPriceRuleOptions
        ]);
    }

    public function store(RewardFormRequest $request)
    {
        $reward = new Reward();
        $reward->fill($request->all());

        if($request->has('store_id')){
            $reward->store()->associate($request->input('store_id'));
        }else{
            $reward->store()->dissociate();
        }

        $this->processRewardByType($request, $reward);

        $reward->save();

        if($request->has('images')){
            foreach($request->input('images', []) as $idx=>$image){
                $images[$image] = [
                    'type' => 'image',
                    'caption' => $request->input('images_caption.'.$idx, null),
                    'locale' => $reward->getTranslation()->locale
                ];
            }
            $reward->getTranslation()->attachMedia($images, 'image');
        }

        return redirect()->route('backend.customer.reward.index')->with('success', [$reward->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $user = Auth::user();

        $reward = Reward::findOrFail($id);

        if(!$user->can('manage_store', [$reward])){
            abort(401);
        }

        $cartPriceRuleOptions = CartPriceRule::pluck('name', 'id')->all();

        $storeOptions = $user->manageAllStores?['' => 'All Stores']:[];
        $storeOptions += Store::getStoreOptions();

        $typeOptions = Reward::getTypeOptions();

        return view('backend.customer.reward.edit', [
            'reward' => $reward,
            'cartPriceRuleOptions' => $cartPriceRuleOptions,
            'storeOptions' => $storeOptions,
            'typeOptions' => $typeOptions,
        ]);
    }

    public function update(RewardFormRequest $request, $id)
    {
        $user = Auth::user();

        $reward = Reward::findOrFail($id);

        if(!$user->can('manage_store', [$reward])){
            abort(401);
        }

        $reward->fill($request->all());

        if($request->has('store_id')){
            $reward->store()->associate($request->input('store_id'));
        }else{
            $reward->store()->dissociate();
        }

        $this->processRewardByType($request, $reward);

        $reward->save();

        $images = [];
        foreach($request->input('images', []) as $idx=>$image){
            $images[$image] = [
                'type' => 'image',
                'caption' => $request->input('images_caption.'.$idx, null),
                'locale' => $reward->getTranslation()->locale
            ];
        }
        $reward->getTranslation()->syncMedia($images, 'image');

        return redirect()->route('backend.customer.reward.index')->with('success', [$reward->name.' has successfully been updated.']);
    }

    public function delete(Request $request, $id)
    {
        $user = Auth::user();

        $reward = RewardRule::findOrFail($id);

        if(!$user->can('manage_store', [$reward])){
            abort(401);
        }

        $reward->delete();

        //Remove all media first. We do it manually because Translation model is cascaded, so we can't do this on Translation delete
        foreach($reward->translations as $translation){
            $translation->deleteMedia('image');
        }

        $name = 'Reward '.$reward->name;

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

    protected function processRewardByType(RewardFormRequest $request, Reward $reward)
    {
        $reward->cartPriceRule()->dissociate();

        if($reward->type == Reward::TYPE_ONLINE_COUPON){
            if($request->has('cart_price_rule_id')){
                $reward->cartPriceRule()->associate($request->input('cart_price_rule_id'));
            }
        }
    }
}
