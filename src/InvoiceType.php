<?php

namespace Bkfdev\Invoicable;

enum InvoiceType: string
{
    case INVOICE = 'invoice';
    case BILL = 'bill';
    case SUBSCRIPTION = 'subscription';
}
