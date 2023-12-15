<?php

namespace Bkfdev\Invoicable;

enum InvoiceStatus: string
{
    case PAID = 'paid';
    case UNPAID = 'unpaid';
    case REFUND = 'refund';
    case DRAFT = 'draft';
    case CANCELED = 'canceled';
}
