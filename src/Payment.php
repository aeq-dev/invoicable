<?php

namespace Bkfdev\Invoicable;

use Bkfdev\Invoicable\Invoice;
use Domain\Shared\Models\User;
use Spatie\MediaLibrary\HasMedia;
use Wildside\Userstamps\Userstamps;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
//use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class Payment extends Model implements HasMedia
{
    use InteractsWithMedia;
    //use Cachable;
    use Userstamps;
    use HasUuids;

    protected $guarded = [];
    /* 
    protected $dates = [
        'payment_date' => 'date:Y-m-d',
        'cheque_date' => 'date:Y-m-d',
    ]; */

    protected $casts = [
        'payment_date' => 'date:Y-m-d',
        'cheque_date' => 'date:Y-m-d',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
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
        /*   
        static::created(function ($model) {
            $model->invoice->supplier->notifyAt(new PaymentReceipt($model), now());
        }); */
    }
}
