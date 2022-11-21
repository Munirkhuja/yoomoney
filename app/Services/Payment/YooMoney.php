<?php


namespace App\Services\Payment;


use App\Models\Payment\YooMoneyChangeStatus;
use Illuminate\Support\Facades\Log;

class YooMoney
{
    public static function makePayment($amount, $currency, $user_id)
    {
        $amount = ((int)($amount * 100)) / 100;
        $client = new \YooKassa\Client();
        $client->setAuth(config('yoo-money.app_id'), config('yoo-money.app_key'));
        $yoo_money = new \App\Models\Payment\YooMoney();
        $yoo_money->user_id = $user_id;
        $yoo_money->amount = $amount;
        $yoo_money->currency = $currency;
        $yoo_money->save();
        try {
            $payment = $client->createPayment(
                array(
                    'amount' => array(
                        'value' => $amount,
                        'currency' => $currency,
                    ),
                    'confirmation' => array(
                        'type' => 'redirect',
                        'return_url' => route('payment.redirect', ['id' => $yoo_money->id]),
                    ),
                    'capture' => true,
                    'description' => 'Заказ №1',
                ),
                uniqid('', true)
            );
            self::store_create_payment($payment->jsonSerialize(), $yoo_money);
            return $payment->getConfirmation()->getConfirmationUrl();
        } catch (\Exception $e) {
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/yoo-money.log'),
            ])->error($e->getMessage(), $e->getTrace());
            return false;
        }
    }

    private static function store_create_payment($payment_responce, $yoo_money)
    {
        $yoo_money->yoo_money_id = $payment_responce['id'];
        $yoo_money->description = $payment_responce['description'] ?? "";
        $yoo_money->metadata = $payment_responce['metadata'] ?? "";
        $yoo_money->paid = $payment_responce['paid'];
        $yoo_money->status = $payment_responce['status'];
        $yoo_money->recipient_account_id = $payment_responce['recipient']['account_id'];
        $yoo_money->recipient_gateway_id = $payment_responce['recipient']['gateway_id'];
        $yoo_money->refundable = $payment_responce['refundable'];
        $yoo_money->test = $payment_responce['test'];
        $yoo_money->yoo_created_at = $payment_responce['created_at'];
        $yoo_money->save();
    }

    public static function change_webhook($request)
    {
        try {
            \App\Models\Payment\YooMoneyChangeStatus::create([
                'yoo_money_id' => $request->id,
                'paid' => $request->paid,
                'status' => $request->status,
                'authorization_details' => $request->authorization_details,
                'payment_method' => $request->payment_method,
                'expires_at' => $request->expires_at,
            ]);
        } catch (\Exception $e) {
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/yoo-money.log'),
            ])->error($e->getMessage(), $e->getTrace());
        }
    }

    public static function check_payment($id)
    {
        $yoo_money = \App\Models\Payment\YooMoney::select('yoo_money_id')
            ->finOrFail($id);
        $paid = YooMoneyChangeStatus::select('paid')
            ->where('yoo_money_id', $yoo_money->yoo_money_id)
            ->where('paid', true)
            ->first();
        return $paid->paid;
    }
}
