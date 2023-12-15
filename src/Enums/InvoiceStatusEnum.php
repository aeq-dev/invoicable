<?php

namespace Bkfdev\Invoicable\Enums;

enum InvoiceStatusEnum: string
{
    case PAID = 'paid';
    case UNPAID = 'unpaid';
    case REFUND = 'refund';
    case DRAFT = 'draft';
    case CANCELED = 'canceled';
}
