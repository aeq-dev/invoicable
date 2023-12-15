<?php

namespace Bkfdev\Invoicable\Models;

use Bkfdev\Invoicable\Models\Invoice;
use App\Models\User;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class Payment extends Model implements HasMedia
{
    use InteractsWithMedia;
    protected $guarded = [];
    protected $casts = [
        'payment_date' => 'date:Y-m-d',
        'cheque_date' => 'date:Y-m-d',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->number = IdGenerator::generate([
                'table' => 'payments',
                'field' => 'number',
                'length' => 20,
                'prefix' => config('invoicable.payment_number'),
                'reset_on_prefix_change' => true
            ]);
        });
    }
}
