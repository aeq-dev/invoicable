<?php

namespace Bkfdev\Invoicable;

use PDF;
use App\Models\User;
use Bkfdev\Invoicable\Payment;
use Spatie\MediaLibrary\HasMedia;
use Wildside\Userstamps\Userstamps;
use Illuminate\Support\Facades\View;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
//use GeneaLabs\LaravelModelCaching\Traits\Cachable;
//use Asantibanez\LaravelEloquentStateMachines\Traits\HasStateMachines;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Domain\Shared\StateMachines\InvoiceStateMachine;

class Invoice extends Model  implements HasMedia
{
    //use Cachable;
    use InteractsWithMedia;
    //use HasStateMachines;
    use Userstamps;
    use SoftDeletes;
    use HasUuids;


    protected $guarded = [];
    protected $with = ['customer', 'supplier'];

    public $stateMachines = [
        'status' => InvoiceStateMachine::class
    ];

    public function lines()
    {
        return $this->hasMany(InvoiceLine::class);
    }

    protected $dates = [
        'invoice_date' => 'date:Y-m-d',
        'due_date' => 'date:Y-m-d',
    ];

    protected $casts = [
        'status' => InvoiceStatus::class,
        'type' => InvoiceType::class,
    ];

    /**
     * Get the invoice lines for this invoice
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    /**
     * Use this if the amount does not yet include tax.
     * @param Int $amount The amount in cents, excluding taxes
     * @param String $description The description
     * @param Float $taxPercentage The tax percentage (i.e. 0.21). Defaults to 0
     * @return Illuminate\Database\Eloquent\Model  This instance after recalculation
     */
    public function addAmountExclTax($description, $qty = 1, $price, $unit = 'unit', $taxPercentage = 0, $discount = 0)
    {
        // 	Qty x ((Unit Price -Discount Amount) x (Tax %)) 
        $tax = $qty * ($price - $discount) * $taxPercentage;
        // Amount Before Tax: Qty x (Unit Price – Discount Amount)
        $beforeTax = $qty * ($price - $discount);
        $this->lines()->create([
            'description' => $description,
            'unit' => $unit,
            'qty' => $qty,
            'price' => $price,
            'tax_percentage' => $taxPercentage,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $tax + $beforeTax, //	Tax amount + Amount before tax
        ]);
        return $this->recalculate();
    }

    /**
     * Use this if the amount already includes tax.
     * @param Int $amount The amount in cents, including taxes
     * @param String $description The description
     * @param Float $taxPercentage The tax percentage (i.e. 0.21). Defaults to 0
     * @return Illuminate\Database\Eloquent\Model  This instance after recalculation
     */
    public function addAmountInclTax($price, $description, $taxPercentage = 0, $qty = 1, $unit = 'unit', $discount = 0)
    {
        //Tax Amount :  Qty x [(Unit Price – Discount Amount) – (Unit Price – Discount Amount) / (1+ Tax %))]
        $tax = $qty * (($price - $discount) - ($price - $discount) / (1 + $taxPercentage));
        // Before Tax : Qty x ((Unit Price – Discount Amount) / (1+ Tax %))
        $beforeTax = $qty * (($price - $discount) / (1 + $taxPercentage));

        $this->lines()->create([
            'description' => $description,
            'unit' => $unit,
            'qty' => $qty,
            'price' => $price,
            'tax_percentage' => $taxPercentage,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $tax + $beforeTax, // 	Tax amount + Amount before tax
        ]);
        return $this->recalculate();
    }

    public function addPayment(
        $description,
        $amount,
        $customer_id = null,
        $method = 'Cash',
        $payment_date = null,
        $currency = 'USD',
        $card = null,
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
            'card_id' => $card ? $card->id : null,
            'currency' => $currency,
            'transaction_id' => $transaction_id,
        ]);
        return $this->updateBalance();
    }

    /**
     * Recalculates total and tax based on lines
     * @return Illuminate\Database\Eloquent\Model  This instance
     */
    public function recalculate()
    {
        $total = $this->lines()->sum('total') - $this->discount;
        $this->total = $total;
        $this->balance = $total;
        $this->amount_paid = 0;
        $this->tax = $this->lines()->sum('tax');
        $this->save();
        return $this;
    }

    public function updateBalance()
    {
        $this->balance = $this->total - $this->payments()->sum('amount');
        $this->amount_paid = $this->total - $this->balance;
        $this->save();
        if ($this->balance <= 0)
            $this->update(['status' => InvoiceStatus::PAID]);
        return $this;
    }

    /**
     * Get the View instance for the invoice.
     *
     * @param  array  $data
     * @return \Illuminate\View\View
     */
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

    /**
     * Capture the invoice as a PDF and return the raw bytes.
     *
     * @param  array  $data
     * @return string
     */
    public function pdf(array $data = [])
    {
        /* if (! defined('DOMPDF_ENABLE_AUTOLOAD')) {
            define('DOMPDF_ENABLE_AUTOLOAD', false);
        }

        if (file_exists($configPath = base_path().'/vendor/dompdf/dompdf/dompdf_config.inc.php')) {
            require_once $configPath;
        }
 */
        $dompdf = new PDF();
        $dompdf->loadHtml($this->view($data)->render());
        $dompdf->render();
        return $dompdf->output();
    }

    /**
     * Create an invoice download response.
     *
     * @param  array  $data
     * @return \Symfony\Component\HttpFoundation\Response
     */
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

    public function scopeForCustomer($query, $id)
    {
        return $query->where('customer_id', $id);
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

        /* static::created(function ($model) {
            $model->setReminders($model);
        }); */
    }
}
