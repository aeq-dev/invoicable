<?php

namespace Bkfdev\Invoicable\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $guarded = [];
    protected $casts = [
        'payment_date' => 'date:Y-m-d',
        'cheque_date' => 'date:Y-m-d',
    ];

    public function invoice()
    {
        return $this->belongsTo(config('invoicable.invoice_model'));
    }

    public function customer()
    {
        return $this->belongsTo(config('invoicable.user_model'), 'customer_id');
    }
}
