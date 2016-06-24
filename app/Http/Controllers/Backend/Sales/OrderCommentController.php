<?php

namespace Kommercio\Http\Controllers\Backend\Sales;

use Illuminate\Http\Request;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Order\OrderComment;

class OrderCommentController extends Controller{
    public function orderCommentIndex($order_id)
    {
        $order = Order::findOrFail($order_id);

        $internalMemos = $order->internalMemos;

        $index = view('backend.order.internal_memos.index', [
            'internalMemos' => $internalMemos
        ])->render();

        return response()->json([
            'html' => $index,
            '_token' => csrf_token()
        ]);
    }

    public function orderCommentForm($order_id)
    {
        $orderComment = new OrderComment();
        $order = Order::findOrFail($order_id);

        $form = view('backend.order.internal_memos.form', [
            'orderComment' => $orderComment,
            'order' => $order,
        ])->render();

        return response()->json([
            'html' => $form,
            '_token' => csrf_token()
        ]);
    }

    public function orderCommentSave(Request $request, $order_id)
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
}