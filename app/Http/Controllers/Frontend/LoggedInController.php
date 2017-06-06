<?php

namespace Kommercio\Http\Controllers\Frontend;

use Illuminate\Contracts\Auth\Guard;
use Kommercio\Http\Controllers\Controller;

class LoggedInController extends Controller
{
    public $user;
    public $customer;

    public function __construct(Guard $guard)
    {
        $this->middleware(function($request, $next){
            $this->user = $request->user();
            $this->customer = $this->user?$this->user->customer:null;

            return $next($request);
        });
    }
}
