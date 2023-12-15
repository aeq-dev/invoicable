<?php

namespace Bkfdev\Invoicable\Models;

use Bkfdev\Invoicable\Models\Invoice;
use Illuminate\Database\Eloquent\Model;

class InvoiceLine extends Model
{
    protected $guarded = [];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
