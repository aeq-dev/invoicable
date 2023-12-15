<?php

namespace Bkfdev\Invoicable\Models;

use PDF;
use App\Models\User;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\View;
use Bkfdev\Invoicable\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use Bkfdev\Invoicable\Models\InvoiceLine;
use Spatie\MediaLibrary\InteractsWithMedia;
use Bkfdev\Invoicable\Enums\InvoiceTypeEnum;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Bkfdev\Invoicable\Enums\InvoiceStatusEnum;
use Symfony\Component\HttpFoundation\Response;

class Invoice extends Model implements HasMedia
{
    use InteractsWithMedia;


    protected $guarded = [];
    protected $with = ['sender', 'receiver'];

    public function lines()
    {
        return $this->hasMany(InvoiceLine::class);
    }

    protected $dates = [
        'invoice_date' => 'date:Y-m-d',
        'due_date' => 'date:Y-m-d',
    ];

    protected $casts = [
        'status' => InvoiceStatusEnum::class,
        'type' => InvoiceTypeEnum::class,
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
    public function addAmountExclTax($description, $quantity = 1, $price, $taxPercentage = 0, $discount = 0, $unit = 'unit')
    {
        $sub_total = $quantity * $price;
        $tax = $quantity * ($price - $discount) * $taxPercentage;
        $discounted_amount = $quantity * ($price - $discount);
        $this->lines()->create([
            'description' => $description,
            'unit' => $unit,
            'quantity' => $quantity,
            'price' => $price,
            'sub_total' => $sub_total,
            'tax_percentage' => $taxPercentage,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $tax + $discounted_amount,
        ]);
        return $this->recalculate();
    }
    public function addAmountInclTax($description, $quantity = 1, $price, $taxPercentage = 0, $discount = 0, $unit = 'unit')
    {
        $discounted_amount = $quantity * ($price - $discount);
        $tax = $discounted_amount - $discounted_amount / (1 + $taxPercentage);
        $sub_total = $quantity * $price;

        $this->lines()->create([
            'description' => $description,
            'unit' => $unit,
            'quantity' => $quantity,
            'price' => $price,
            'sub_total' => $sub_total,
            'tax_percentage' => $taxPercentage,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $tax + $discounted_amount,
        ]);
        return $this->recalculate();
    }

    public function addPayment(
        $description,
        $amount = 0,
        $receiver_id = null,
        $method = null,
        $payment_date = null,
        $currency_id = null,
        $card_id = null,
        $transaction_id = null,
        $cheque_number = null,
        $cheque_date = null
    ) {
        $this->payments()->create([
            'amount' => $amount,
            'description' => $description,
            'receiver_id' => $receiver_id,
            'method' => $method,
            'cheque_number' => $cheque_number,
            'cheque_date' => $cheque_date,
            'payment_date' => $payment_date ?? now(),
            'card_id' => $card_id,
            'currency_id' => $currency_id,
            'transaction_id' => $transaction_id,
        ]);
        return $this->updateBalance();
    }

    public function recalculate()
    {
        $total = $this->lines()->sum('total');
        $this->discount = $this->lines()->sum('discount');
        $this->sub_total = $this->lines()->sum('$sub_total');
        $this->tax = $this->lines()->sum('tax');
        $this->total = $total;
        $this->due_amount = $total;
        $this->paid_amount = 0;
        $this->save();
        return $this;
    }

    public function updateBalance()
    {
        $this->due_amount = $this->total - $this->payments()->sum('amount');
        $this->paid_amount = $this->total - $this->due_amount;
        $this->save();
        if ($this->due_amount <= 0)
            $this->update(['status' => InvoiceStatusEnum::PAID]);
        return $this;
    }
    public function view(array $data = [])
    {
        return View::make('invoicable::receipt', array_merge($data, [
            'invoice' => $this,
            'moneyFormatter' => new MoneyFormatter(
                $this->currency,
                config('invoicable.locale')
            ),
        ]));
    }

    public function pdf(array $data = [])
    {
        $dompdf = new PDF();
        $dompdf->loadHtml($this->view($data)->render());
        $dompdf->render();
        return $dompdf->output();
    }
    public function download(array $data = [])
    {
        $filename = $this->reference . '.pdf';

        return new Response($this->pdf($data), 200, [
            'Content-Description' => 'File Transfer',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Type' => 'application/pdf',
        ]);
    }

    public static function findByNumber($reference)
    {
        return static::where('number', $reference)->first();
    }

    public function invoicable()
    {
        return $this->morphTo();
    }

    public function scopeUnpaid($query)
    {
        return $query->where('balance', '>', 0);
    }

    public function scopePaid($query)
    {
        return $query->where('balance', 0);
    }

    public function scopeForReceiver($query, $id)
    {
        return $query->where('receiver_id', $id);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->number = IdGenerator::generate([
                'table' => 'invoices',
                'field' => 'number',
                'length' => 20,
                'prefix' => config('invoicable.invoice_number'),
                'reset_on_prefix_change' => true
            ]);
        });
    }
}
