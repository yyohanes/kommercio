<?php

namespace Kommercio\Http\Controllers\Backend\Customer;

use Collective\Html\FormFacade;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Gate;
use Kommercio\Http\Requests;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Customer;
use Kommercio\Models\PriceRule\Coupon;
use Kommercio\Models\RewardPoint\Redemption;

class RedemptionController extends Controller
{
    public function index(Request $request)
    {
        $qb = Redemption::query();

        if($request->ajax() || $request->wantsJson()){
            $totalRecords = $qb->count();

            foreach($request->input('filter', []) as $searchKey=>$search){
                if(trim($search) != ''){
                    if($searchKey == 'reward') {
                        $qb->whereHas('reward', function($query) use ($search){
                            $query->whereTranslationLike('name', '%'.$search.'%');
                        });
                        $qb->orWhereHas('coupon', function($query) use ($search){
                            $query->where('coupon_code', 'LIKE', '%'.$search.'%');
                        });
                    }elseif($searchKey == 'customer') {
                        $customers = Customer::searchCustomers($search);

                        if($customers->count() > 0){
                            $qb->whereIn('customer_id', $customers->pluck('id')->all());
                        }else{
                            $qb->where('customer_id', 'not found');
                        }
                    }elseif($searchKey == 'status'){
                        switch($search){
                            case 'used':
                                $qb->has('coupon.rewardUsageLogs');
                                break;
                            case 'unused':
                                $qb->doesntHave('coupon.rewardUsageLogs');
                                break;
                        }
                    }
                }
            }

            $filteredRecords = $qb->count();

            $columns = $request->input('columns');
            foreach($request->input('order', []) as $order){
                $orderColumn = $columns[$order['column']];

                $qb->orderBy($orderColumn['name'], $order['dir']);
            }

            if($request->has('length')){
                $qb->take($request->input('length'));
            }

            if($request->has('start') && $request->input('start') > 0){
                $qb->skip($request->input('start'));
            }

            $redemptions = $qb->get();

            $meat = $this->prepareDatatables($redemptions, $request->input('start'));

            $data = [
                'draw' => $request->input('draw'),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $meat
            ];

            return response()->json($data);
        }

        return view('backend.customer.redemption.index');
    }

    protected function prepareDatatables($redemptions, $orderingStart=0)
    {
        $meat = [];

        foreach($redemptions as $idx=>$redemption){
            $redemptionAction = FormFacade::open(['route' => ['backend.customer.redemption.mark_used', 'id' => $redemption->id]]);
            $redemptionAction .= '<div class="btn-group btn-group-xs">';
            if(Gate::allows('access', ['mark_used_redemptions']) && $redemption->coupon->getRewardUsageCount() < 1 && $redemption->coupon->type == Coupon::TYPE_OFFLINE):
                $redemptionAction .= '<button class="btn btn-default" data-toggle="confirmation" data-original-title="Are you sure?" title=""><i class="fa fa-check"></i> Mark as Used</button></div>';
            endif;

            $redemptionAction .= FormFacade::close();

            if($redemption->coupon->getRewardUsageCount() > 0){
                $redemptionAction .= 'Used at: <span class="label label-sm label-default">'.$redemption->coupon->rewardUsageLogs->first()->created_at->format('d M Y H:i').'</span>';
            }

            $rowMeat = [
                $idx + 1 + $orderingStart,
                $redemption->reward->name.($redemption->coupon->type == Coupon::TYPE_OFFLINE?' <code>'.$redemption->coupon->coupon_code.'</code>':''),
                $redemption->points + 0,
                $redemption->customer->fullName.'<br/>'.$redemption->customer->profile->email,
                $redemption->created_at->format('d M Y H:i'),
                $redemption->coupon->getRewardUsageCount() < 1?'<span class="label label-info">Unused</span>':'<span class="label label-warning">Used</span>',
                $redemptionAction,
            ];

            $meat[] = $rowMeat;
        }

        return $meat;
    }

    public function markUsed($id)
    {
        $redemption = Redemption::findOrFail($id);

        $redemption->coupon->markRewardUsed();

        return redirect()->back()->with('success', ['Coupon is marked as used.']);
    }
}
