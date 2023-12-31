<?php

namespace Bkfdev\Invoicable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $guarded = [];
    protected $with = ['sender', 'receiver'];

    public function lines(): HasMany
    {
        return $this->hasMany(config('invoicable.invoice_line_model'));
    }

    public function payments(): HasMany
    {
        return $this->hasMany(config('invoicable.payment_model'));
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(config('invoicable.user_model'), 'receiver_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(config('invoicable.user_model'), 'sender_id');
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
        $customer_id = null,
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
            'customer_id' => $customer_id,
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
        $this->sub_total = $this->lines()->sum('sub_total');
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
        return $this;
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
        return $query->where('due_amount', '>', 0);
    }

    public function scopePaid($query)
    {
        return $query->where('due_amount', '<=', 0);
    }

    public function scopeForReceiver($query, $id)
    {
        return $query->where('receiver_id', $id);
    }
}
