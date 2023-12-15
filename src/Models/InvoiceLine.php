<?php

namespace Bkfdev\Invoicable\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceLine extends Model
{
    protected $guarded = [];

    public function invoice()
    {
        return $this->belongsTo(config('invoicable.invoice_model'));
    }
}
