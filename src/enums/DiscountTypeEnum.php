<?php

namespace Bkfdev\Invoicable\Enums;

enum DiscountTypeEnum: string
{
    case FIXED = 'fixed';
    case PERCENTAGE = 'percentage';
}
