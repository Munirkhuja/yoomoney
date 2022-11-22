<?php


namespace App\Services\Payment;


use App\Models\Payment\YooMoneyChangeStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class YooMoney
{
    public static function makePayment($amount, $currency, $user_id)
    {
        $amount = ((int)($amount * 100)) / 100;
        $client = new \YooKassa\Client();
        $client->setAuth(config('yoo-money.app_id'), config('yoo-money.app_key'));
        $yoo_money = new \App\Models\Payment\YooMoney();
        $yoo_money->user_id = $user_id??User::first()->id;
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
            $yoo_money_change = new YooMoneyChangeStatus();
            self::store_create_payment($yoo_money_change, $payment->jsonSerialize(), $yoo_money->id);
            return $payment->getConfirmation()->getConfirmationUrl();
        } catch (\Exception $e) {
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/yoo-money.log'),
            ])->error($e->getMessage(), $e->getTrace());
            return false;
        }
    }

    private static function store_create_payment($yoo_money_change, $payment_responce, $yoo_money_id = false)
    {
        if ($yoo_money_id !== false) {
            $yoo_money_change->yoo_money_id = $yoo_money_id;
        }
        $yoo_money_change->id = $payment_responce['id'];
        $yoo_money_change->description = $payment_responce['description'] ?? "";
        $yoo_money_change->metadata = $payment_responce['metadata'] ?? "";
        $yoo_money_change->paid = $payment_responce['paid'];
        $yoo_money_change->status = $payment_responce['status'];
        $yoo_money_change->recipient_account_id = $payment_responce['recipient']['account_id'];
        $yoo_money_change->recipient_gateway_id = $payment_responce['recipient']['gateway_id'];
        $yoo_money_change->refundable = $payment_responce['refundable'];
        $yoo_money_change->test = $payment_responce['test'];
        $yoo_money_change->yoo_created_at = $payment_responce['created_at'];
        $yoo_money_change->authorization_details = $payment_responce['authorization_details'] ?? json_encode([]);
        $yoo_money_change->payment_method = $payment_responce['payment_method'] ?? json_encode([]);
        $yoo_money_change->expires_at = $payment_responce['expires_at'] ?? Carbon::now();
        $yoo_money_change->save();
    }

    public static function change_webhook($request)
    {
        try {
            $yoo_money_change = YooMoneyChangeStatus::where('yoo_money_id', $request->id)->firstOrFail();
            self::store_create_payment($yoo_money_change, $request->all());
        } catch (\Exception $e) {
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/yoo-money.log'),
            ])->error($e->getMessage(), $e->getTrace());
        }
    }

    public static function check_payment($id)
    {
        $paid = YooMoneyChangeStatus::select('paid')
            ->where('yoo_money_id', $id)
            ->first();
        return $paid->paid ?? false;
    }

    public function get_change_payment()
    {
        $client = new \YooKassa\Client();
        $client->setAuth(config('yoo-money.app_id'), config('yoo-money.app_key'));
        try {
            $yoo_money_changes = YooMoneyChangeStatus::select('id')->where('paid', false)->get();
            foreach ($yoo_money_changes as $item) {
                $payment = $client->getPaymentInfo($item->id);
                if ($payment) {
                    $yoo_money_change = YooMoneyChangeStatus::where('id', $item->id)->first();
                    self::store_create_payment($yoo_money_change, $payment->jsonSerialize());
                }
            }
        } catch (\Exception $e) {
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/yoo-money.log'),
            ])->error($e->getMessage(), $e->getTrace());
        }
    }
}
