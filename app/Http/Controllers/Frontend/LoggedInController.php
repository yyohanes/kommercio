<?php

namespace Kommercio\Http\Controllers\Frontend;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Kommercio\Http\Requests;
use Kommercio\Http\Controllers\Controller;

class LoggedInController extends Controller
{
    public $user;
    public $customer;

    public function __construct()
    {
        $this->user = Auth::user();
        $this->customer = $this->user?$this->user->customer:null;
    }
}
