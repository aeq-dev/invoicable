<?php

namespace Bkfdev\Invoicable\Models;

use Bkfdev\Invoicable\Models\Invoice;
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
        return $this->belongsTo(Invoice::class);
    }

    public function customer()
    {
        return $this->belongsTo(config('invoicable.user_model'), 'customer_id');
    }
}
