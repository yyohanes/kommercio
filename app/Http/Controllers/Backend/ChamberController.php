<?php

namespace Kommercio\Http\Controllers\Backend;

use Kommercio\Http\Controllers\Controller;

class ChamberController extends Controller{
    public function dashboard()
    {
        return view('backend.dashboard');
    }
}