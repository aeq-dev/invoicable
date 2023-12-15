<?php

namespace Bkfdev\Invoicable;

use Illuminate\Database\Eloquent\Model;
use Bkfdev\Invoicable\Invoice;
use Wildside\Userstamps\Userstamps;

class InvoiceLine extends Model
{
    use Userstamps;

    protected $guarded = [];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
