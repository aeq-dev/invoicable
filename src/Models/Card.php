<?php

namespace Bkfdev\Invoicable\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $guarded = [];

    public function payments()
    {
        return $this->hasMany(config('invoicable.payment_model'));
    }

    public function user()
    {
        return $this->belongsTo(config('invoicable.user_model'));
    }
}
