<?php

namespace App\Http\Controllers\Api\v1\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Services\Payment\YooMoney;
use Illuminate\Http\Client\Request;

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

    public function index()
    {
        return view('create_payment');
    }

    public function payment_check($uniq_id)
    {
        $paid = YooMoney::check_payment($uniq_id);
        return view('paid')->with(['paid' => $paid ? 'paid' : 'not paid']);
    }
}
