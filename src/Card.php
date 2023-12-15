<?php

namespace Bkfdev\Invoicable;

use Bkfdev\Invoicable\Payment;
use Domain\Shared\Models\User;
use Wildside\Userstamps\Userstamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
//use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class Card extends Model
{
    //use Cachable;
    use Userstamps;
    use HasUuids;

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
