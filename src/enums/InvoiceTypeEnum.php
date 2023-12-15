<?php

namespace Bkfdev\Invoicable\Enums;

enum InvoiceTypeEnum: string
{
    case INVOICE = 'invoice';
    case BILL = 'bill';
    case SUBSCRIPTION = 'subscription';
}
