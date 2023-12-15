<?php

return [
    'user_model' => \App\Models\User::class,
    'invoice_model' => \Bkfdev\Invoicable\Models\Invoice::class,
    'payment_model' => \Bkfdev\Invoicable\Models\Payment::class,
    'card_model' => \Bkfdev\Invoicable\Models\Card::class,
    'invoice_line_model' => \Bkfdev\Invoicable\Models\InvoiceLine::class,
];
