<?php

namespace Kommercio\Http\Controllers\Backend\Sales;

use Illuminate\Http\Request;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Order\OrderComment;

class OrderCommentController extends Controller{
    public function internalIndex($order_id)
    {
        $order = Order::findOrFail($order_id);

        $internalMemos = $order->internalMemos;

        $index = view('backend.order.memos.internal.index', [
            'internalMemos' => $internalMemos
        ])->render();

        return response()->json([
            'html' => $index,
            '_token' => csrf_token()
        ]);
    }

    public function internalForm($order_id)
    {
        $orderComment = new OrderComment();
        $order = Order::findOrFail($order_id);

        $form = view('backend.order.memos.internal.form', [
            'orderComment' => $orderComment,
            'order' => $order,
        ])->render();

        return response()->json([
            'html' => $form,
            '_token' => csrf_token()
        ]);
    }

    public function internalSave(Request $request, $order_id)
    {
        $order = Order::findOrFail($order_id);

        $rules = [
            'internal_memo.body' => 'required',
        ];

        $this->validate($request, $rules);

        $fullName = $request->user()->fullName;
        if(empty(trim($fullName))){
            $fullName = $request->user()->email;
        }

        $internalMemo = new OrderComment();
        $internalMemo->saveData(['author_name' => $fullName]);
        $internalMemo->fill($request->input('internal_memo'));
        $internalMemo->order()->associate($order);
        $internalMemo->type = OrderComment::TYPE_INTERNAL_MEMO;

        $internalMemo->save();

        return response()->json([
            'result' => 'success',
            'message' => 'Internal Memo is successfully saved.'
        ]);
    }

    public function externalIndex($order_id)
    {
        $order = Order::findOrFail($order_id);

        $externalMemos = $order->externalMemos;

        $index = view('backend.order.memos.external.index', [
            'externalMemos' => $externalMemos
        ])->render();

        return response()->json([
            'html' => $index,
            '_token' => csrf_token()
        ]);
    }

    public function externalForm($order_id, $id = null)
    {
        $orderComment = OrderComment::find($id);

        if(empty($orderComment)){
            $orderComment = new OrderComment();
        }

        $order = Order::findOrFail($order_id);

        $form = view('backend.order.memos.external.form', [
            'orderComment' => $orderComment,
            'order' => $order,
        ])->render();

        return response()->json([
            'html' => $form,
            '_token' => csrf_token()
        ]);
    }

    public function externalSave(Request $request, $order_id, $id = null)
    {
        $order = Order::findOrFail($order_id);

        $rules = [
            'external_memo.body' => 'required',
        ];

        $this->validate($request, $rules);

        $fullName = $request->user()->fullName;
        if(empty(trim($fullName))){
            $fullName = $request->user()->email;
        }

        $orderComment = OrderComment::find($id);

        if(empty($orderComment)){
            $orderComment = new OrderComment([
                'type' => OrderComment::TYPE_EXTERNAL_MEMO
            ]);

            $orderComment->order()->associate($order);
        }

        $orderComment->saveData(['author_name' => $fullName]);
        $orderComment->fill($request->input('external_memo'));
        $orderComment->save();

        return response()->json([
            'result' => 'success',
            'message' => 'External Memo is successfully saved.'
        ]);
    }

    public function externalDelete(Request $request, $order_id, $id)
    {
        $orderComment = OrderComment::findOrFail($id);

        if($orderComment->order_id != $order_id){
            abort(400, 'This memo doesn\'t belong to this Order.');
        }

        $orderComment->delete();

        return response()->json([
            'result' => 'success',
            'message' => 'External Memo is successfully deleted.'
        ]);
    }
}