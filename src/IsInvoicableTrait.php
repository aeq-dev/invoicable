<?php

namespace Bkfdev\Invoicable;

use Bkfdev\Invoicable\Models\Invoice;


trait IsInvoicableTrait
{
    /**
     * Set the polymorphic relation.
     *
     * @return mixed
     */
    public function invoices()
    {
        return $this->morphMany(Invoice::class, 'invoicable');
    }
}
