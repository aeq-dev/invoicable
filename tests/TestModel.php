<?php

namespace Bkfdev\Invoicable;

use Illuminate\Database\Eloquent\Model;
use Bkfdev\Invoicable\IsInvoicable\IsInvoicableTrait;

class TestModel extends Model
{
    use IsInvoicableTrait;

    protected $guarded = [];
}
