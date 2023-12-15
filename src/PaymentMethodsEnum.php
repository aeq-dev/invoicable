<?php

namespace Bkfdev\Invoicable;

enum PaymentMethodsEnum: string
{
    case CASH = 'cash';
    case CASH_ON_DELIVERY = 'cash on delivery';
    case PAYPAL = 'paypal';
    case BANK = 'bank';
}
