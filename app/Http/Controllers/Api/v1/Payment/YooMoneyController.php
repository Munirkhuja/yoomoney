<?php

namespace App\Http\Controllers\Api\v1\Payment;

use App\Events\YooMoneyEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Services\Payment\YooMoney;
use Illuminate\Http\Request;

class YooMoneyController extends Controller
{
    public function __invoke(PaymentRequest $request, $user_id = false)
    {
        if ($user_id === false) {
            $user_id = auth()->id();
        }
        return YooMoney::makePayment($request->amount, $request->currency, $user_id);
    }

    public function change_statuses(Request $request)
    {
        YooMoney::change_webhook($request);
    }

    public function websocket_send($m)
    {
//        YooMoneyEvent::dispatch($m);
        return response()->json($m);
    }
    public function index()
    {
        return view('create_payment');
    }

    public function payment_check(Request $request)
    {
        $paid = YooMoney::check_payment($request->id);
        return view('paid')->with(['paid' => $paid ? 'paid' : 'not paid']);
    }
}
