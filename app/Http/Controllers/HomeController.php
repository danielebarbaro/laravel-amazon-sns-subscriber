<?php

namespace App\Http\Controllers;

use App\Models\SnsResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $sns_result = SnsResponse::where('notification_type', 'bounce')->paginate(50);
        return view('sns-response.index', compact('sns_result'));
    }
}
