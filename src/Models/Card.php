<?php

namespace Bkfdev\Invoicable\Models;

use Bkfdev\Invoicable\Models\Payment;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $guarded = [];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function user()
    {
        return $this->belongsTo(config('invoicable.user_model'));
    }
}
