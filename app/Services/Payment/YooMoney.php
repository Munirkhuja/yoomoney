<?php


namespace App\Services\Payment;


use App\Models\Payment\YooMoneyChangeStatus;
use Illuminate\Support\Facades\Log;

class YooMoney
{
    public static function makePayment($amount, $currency, $user_id)
    {
        $client = new \YooKassa\Client();
        $client->setAuth(config('yoo-money.app_id'), config('yoo-money.app_key'));
        try {
            $uniq_id = uniqid('', true);
            $payment = $client->createPayment(
                array(
                    'amount' => array(
                        'value' => $amount,
                        'currency' => $currency,
                    ),
                    'confirmation' => array(
                        'type' => 'redirect',
                        'return_url' => route('payment.redirect', $uniq_id),
                    ),
                    'capture' => true,
                    'description' => 'Заказ №1',
                ),
                $uniq_id
            );
            self::store_create_payment($payment->jsonSerialize(), $user_id);
            return $payment->getConfirmation()->getConfirmationUrl();
        } catch (\Exception $e) {
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/yoo-money.log'),
            ])->error($e->getMessage(), $e->getTrace());
            return false;
        }
    }

    private static function store_create_payment($payment_responce, $user_id)
    {
        \App\Models\Payment\YooMoney::create([
            'yoo_money_id' => $payment_responce['id'],
            'user_id' => $user_id,
            'amount' => $payment_responce['amount']['value'],
            'currency' => $payment_responce['amount']['currency'],
            'description' => $payment_responce['description'] ?? "",
            'metadata' => $payment_responce['metadata'] ?? "",
            'paid' => $payment_responce['paid'],
            'status' => $payment_responce['status'],
            'recipient_account_id' => $payment_responce['recipient']['account_id'],
            'recipient_gateway_id' => $payment_responce['recipient']['gateway_id'],
            'refundable' => $payment_responce['refundable'],
            'test' => $payment_responce['test'],
            'yoo_created_at' => $payment_responce['created_at'],
        ]);
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

    public static function check_payment($uniq_id)
    {
        $paid = YooMoneyChangeStatus::select('paid')
            ->where('yoo_money_id', $uniq_id)
            ->where('paid', true)
            ->first();
        if (!$paid) {
            $paid = \App\Models\Payment\YooMoney::select('paid')
                ->where('yoo_money_id', $uniq_id)
                ->firstOrFail();
        }
        return $paid->paid;
    }
}
