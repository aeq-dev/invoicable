<?php

namespace Bkfdev\Invoicable;

enum DiscountTypeEnum: string
{
    case FIXED = 'fixed';
    case PERCENTAGE = 'percentage';
}
