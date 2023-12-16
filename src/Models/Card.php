<?php

namespace Bkfdev\Invoicable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Card extends Model
{
    protected $guarded = [];

    public function payments(): HasMany
    {
        return $this->hasMany(config('invoicable.payment_model'));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('invoicable.user_model'));
    }
}
