<?php

namespace Bkfdev\Invoicable\IsInvoicable;

use Bkfdev\Invoicable\Invoice;

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
