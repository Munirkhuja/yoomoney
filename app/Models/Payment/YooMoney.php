<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YooMoney extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'currency',
    ];

    public function yoo_money()
    {
        return $this->hasMany(YooMoneyChangeStatus::class, 'yoo_money_id', 'id');
    }
}
