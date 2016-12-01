<?php

namespace Kommercio\Http\Controllers\Backend\Customer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Kommercio\Events\RewardPointEvent;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Customer;
use Collective\Html\FormFacade;
use Illuminate\Support\Facades\Request as RequestFacade;
use Kommercio\Models\RewardPoint\RewardPointTransaction;

class RewardPointController extends Controller{
    public function index(Request $request)
    {
        $qb = RewardPointTransaction::with('customer', 'order');

        if($request->ajax() || $request->wantsJson()){
            $totalRecords = $qb->count();

            foreach($request->input('filter', []) as $searchKey=>$search){
                if(is_array($search) || trim($search) != ''){
                    if($searchKey == 'customer') {

                    }elseif($searchKey == 'created_at'){
                        if(!empty($search['from'])){
                            $qb->whereRaw('DATE_FORMAT(created_at, \'%Y-%m-%d\') >= ?', [$search['from']]);
                        }

                        if(!empty($search['to'])){
                            $qb->whereRaw('DATE_FORMAT(created_at, \'%Y-%m-%d\') <= ?', [$search['to']]);
                        }
                    }else{
                        $qb->where($searchKey, 'LIKE','%'.$search.'%');
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

            $customers = $qb->get();

            $meat = $this->prepareDatatables($customers, $request->input('start'));

            $data = [
                'draw' => $request->input('draw'),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $meat
            ];

            return response()->json($data);
        }

        return view('backend.customer.reward_point.index');
    }

    protected function prepareDatatables($rows, $orderingStart=0)
    {
        $meat= [];

        foreach($rows as $idx=>$row){
            $rowAction = '<div class="btn-group btn-group-xs">';
            if(Gate::allows('access', ['approve_reward_points']) && $row->status == RewardPointTransaction::STATUS_REVIEW):
                $rowAction .= FormFacade::open(['route' => ['backend.customer.reward_point.approve', 'id' => $row->id], 'class' => 'form-in-btn-group']);
                $rowAction .= '<button class="btn btn-xs btn-default" data-toggle="confirmation" data-original-title="Are you sure?" title=""><i class="fa fa-check"></i> Approve</button></div>';
                $rowAction .= FormFacade::close();
            endif;
            if(Gate::allows('access', ['reject_reward_points']) && $row->status == RewardPointTransaction::STATUS_REVIEW):
                $rowAction .= FormFacade::open(['route' => ['backend.customer.reward_point.reject', 'id' => $row->id, 'class' => 'form-in-btn-group']]);
                $rowAction .= '<button class="btn btn-xs btn-default" data-toggle="confirmation" data-original-title="Are you sure?" title=""><i class="fa fa-remove"></i> Reject</button></div>';
                $rowAction .= FormFacade::close();
            endif;
            $rowAction .= '</div>';

            $meat[] = [
                $idx + 1 + $orderingStart,
                $row->customer->full_name.'<br/>'.$row->customer->email,
                $row->amount + 0,
                nl2br($row->reason),
                '<span class="label label-'.($row->type == \Kommercio\Models\RewardPoint\RewardPointTransaction::TYPE_ADD?'info':'warning').'">'.\Kommercio\Models\RewardPoint\RewardPointTransaction::getTypeOptions($row->type).'</span>',
                $this->printStatus($row->status),
                $row->createdBy->fullName,
                nl2br($row->notes),
                $row->created_at->format('d M Y H:i'),
                $rowAction
            ];
        }

        return $meat;
    }

    protected function printStatus($status)
    {
        if($status == RewardPointTransaction::STATUS_APPROVED){
            $return = '<span class="text-success"><i class="fa fa-check"></i></span>';
        }elseif($status == RewardPointTransaction::STATUS_DECLINED){
            $return = '<span class="text-danger"><i class="fa fa-remove"></i></span>';
        }else{
            $return = '<i class="fa fa-clock-o"></i>';
        }

        return $return;
    }

    public function approve($id)
    {
        $rewardPointTransaction = RewardPointTransaction::findOrFail($id);

        if($rewardPointTransaction->status != RewardPointTransaction::STATUS_REVIEW){
            return redirect()->back()->withErrors(['Reward Point can\'t be processed anymore.']);
        }

        $rewardPointTransaction->update([
            'status' => RewardPointTransaction::STATUS_APPROVED
        ]);

        Event::fire(new RewardPointEvent('approve', $rewardPointTransaction));

        return redirect()->back()->with('success', ['Reward point '.RewardPointTransaction::getTypeOptions($rewardPointTransaction->type).' has been approved.']);
    }

    public function reject($id)
    {
        $rewardPointTransaction = RewardPointTransaction::findOrFail($id);

        if($rewardPointTransaction->status != RewardPointTransaction::STATUS_REVIEW){
            return redirect()->back()->withErrors(['Reward Point can\'t be processed anymore.']);
        }

        $rewardPointTransaction->update([
            'status' => RewardPointTransaction::STATUS_DECLINED
        ]);

        Event::fire(new RewardPointEvent('reject', $rewardPointTransaction));

        return redirect()->back()->with('success', ['Reward point '.RewardPointTransaction::getTypeOptions($rewardPointTransaction->type).' has been rejected.']);
    }
}