<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YooMoneyChangeStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'yoo_money_id',
        'paid',
        'status',
        'authorization_details',
        'payment_method',
        'expires_at',
        'description',
        'metadata',
        'recipient_account_id',
        'recipient_gateway_id',
        'refundable',
        'test',
        'yoo_created_at'
    ];

    public function yoo_money()
    {
        return $this->belongsTo(YooMoney::class, 'id', 'yoo_money_id');
    }
}
