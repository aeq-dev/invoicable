<?php

namespace Bkfdev\Invoicable;

enum InvoiceTypeEnum: string
{
    case INVOICE = 'invoice';
    case BILL = 'bill';
    case SUBSCRIPTION = 'subscription';
}
