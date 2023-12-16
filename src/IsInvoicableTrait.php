<?php

namespace Bkfdev\Invoicable;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait IsInvoicableTrait
{
    /**
     * Set the polymorphic relation.
     *
     * @return mixed
     */
    public function invoices(): MorphMany
    {
        return $this->morphMany(config('invoicable.invoice_model'), 'invoicable');
    }
}
