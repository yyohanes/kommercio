<?php

namespace Kommercio\Http\Controllers\Backend\Customer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kommercio\Facades\CurrencyHelper;
use Kommercio\Http\Requests;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\RewardPoint\RewardRule;
use Kommercio\Http\Requests\Backend\Customer\RewardRuleFormRequest;
use Kommercio\Models\Store;

class RewardRuleController extends Controller
{
    public function index()
    {
        $qb = RewardRule::orderBy('sort_order', 'ASC');

        $qb->whereNull('store_id')->orWhereIn('store_id', Auth::user()->getManagedStores()->pluck('id')->all());

        $rewardRules = $qb->get();

        return view('backend.customer.reward_rule.index', [
            'rewardRules' => $rewardRules,
        ]);
    }

    public function create()
    {
        $rewardRule = new RewardRule([
            'active' => TRUE
        ]);

        $typeOptions = RewardRule::getTypeOptions();
        $currencyOptions = ['' => 'All Currencies'] + CurrencyHelper::getCurrencyOptions();

        $storeOptions = Auth::user()->manageAllStores?['' => 'All Stores']:[];
        $storeOptions += Store::getStoreOptions();

        return view('backend.customer.reward_rule.create', [
            'rewardRule' => $rewardRule,
            'typeOptions' => $typeOptions,
            'currencyOptions' => $currencyOptions,
            'storeOptions' => $storeOptions
        ]);
    }

    public function store(RewardRuleFormRequest $request)
    {
        $rewardRule = new RewardRule();
        $rewardRule->fill($request->all());

        $this->processRewardRuleByType($request, $rewardRule);

        $rewardRule->save();

        return redirect()->route('backend.customer.reward_rule.index')->with('success', [$rewardRule->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $user = Auth::user();

        $rewardRule = RewardRule::findOrFail($id);

        if(!$user->can('manage_store', [$rewardRule])){
            abort(401);
        }

        $currencyOptions = ['' => 'All Currencies'] + CurrencyHelper::getCurrencyOptions();

        $storeOptions = $user->manageAllStores?['' => 'All Stores']:[];
        $storeOptions += Store::getStoreOptions();

        $typeOptions = RewardRule::getTypeOptions();

        return view('backend.customer.reward_rule.edit', [
            'rewardRule' => $rewardRule,
            'currencyOptions' => $currencyOptions,
            'storeOptions' => $storeOptions,
            'typeOptions' => $typeOptions,
        ]);
    }

    public function update(RewardRuleFormRequest $request, $id)
    {
        $user = Auth::user();

        $rewardRule = RewardRule::findOrFail($id);

        if(!$user->can('manage_store', [$rewardRule])){
            abort(401);
        }

        $rewardRule->fill($request->all());

        $this->processRewardRuleByType($request, $rewardRule);

        $rewardRule->save();

        return redirect()->route('backend.customer.reward_rule.index')->with('success', [$rewardRule->name.' has successfully been updated.']);
    }

    public function delete(Request $request, $id)
    {
        $user = Auth::user();

        $rewardRule = RewardRule::findOrFail($id);

        if(!$user->can('manage_store', [$rewardRule])){
            abort(401);
        }

        $rewardRule->delete();

        $name = 'Reward Rule '.$rewardRule->name;

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
            $rewardRule = RewardRule::findOrFail($object);
            $rewardRule->update([
                'sort_order' => $idx
            ]);
        }

        if($request->ajax()){
            return response()->json([
                'result' => 'success',
            ]);
        }else{
            return redirect()->route('backend.customer.reward_rule.index');
        }
    }

    public function get(Request $request)
    {
        $data = [
            'currency' => $request->input('currency', null),
            'store_id' => $request->input('store_id', null),
        ];

        $return = [];

        $rewardRules = RewardRule::getRewardRules($data);
        foreach($rewardRules as $rewardRule){
            $return[] = [
                'id' => $rewardRule->id,
                'name' => $rewardRule->name,
                'type' => $rewardRule->type,
                'reward' => $rewardRule->reward + 0,
                'member' => $rewardRule->member,
                'rule' => $rewardRule->getData('rule')
            ];
        }

        return response()->json([
            'data' => $return,
            '_token' => csrf_token()
        ]);
    }

    protected function processRewardRuleByType(RewardRuleFormRequest $request, RewardRule $rewardRule)
    {
        $rewardRule->saveData([
            'rule' => $request->input('rule')
        ]);
    }
}
