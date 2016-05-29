<?php

namespace Kommercio\Http\Middleware\Backend;

use Closure;
use Kommercio\Models\Order\Order;

class OrderEditable
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $order = Order::findOrFail($request->route('id'));

        if(!$order->isEditable){
            return redirect()->back()->withErrors(['This order is settled. It can\'t be edited.']);
        }

        return $next($request);
    }
}
