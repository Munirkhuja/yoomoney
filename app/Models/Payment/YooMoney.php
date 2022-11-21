<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YooMoney extends Model
{
    use HasFactory;

    protected $fillable = [
        'yoo_money_id',
        'user_id',
        'amount',
        'currency',
        'description',
        'metadata',
        'paid',
        'status',
        'recipient_account_id',
        'recipient_gateway_id',
        'refundable',
        'test',
        'yoo_created_at'
    ];

    public function yoo_money()
    {
        return $this->hasMany(YooMoneyChangeStatus::class, 'yoo_money_id', 'yoo_money_id');
    }
}
