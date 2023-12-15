<?php

namespace Bkfdev\Invoicable\Models;

use App\Models\User;
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
        return $this->belongsTo(User::class);
    }
}
