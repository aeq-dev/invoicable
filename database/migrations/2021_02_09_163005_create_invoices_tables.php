<?php

use Bkfdev\Invoicable\DiscountTypeEnum;
use Bkfdev\Invoicable\InvoiceStatusEnum;
use Bkfdev\Invoicable\InvoiceTypeEnum;
use Bkfdev\Invoicable\PaymentMethodsEnum;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoicesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->string('reference')->nullable();
            $table->morphs('invoicable');
            $table->float('tax')->default(0);
            $table->float('discount')->default(0);
            $table->string('discount_type')->default(DiscountTypeEnum::FIXED->value);
            $table->float('total')->default(0);
            $table->float('balance')->default(0);
            $table->float('paid_amount')->default(0);
            $table->date('invoice_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->text('note')->nullable();

            $table->string('type')->default(InvoiceTypeEnum::INVOICE->value);
            $table->string('status')->default(InvoiceStatusEnum::UNPAID->value);

            $table->unsignedBigInteger('currency_id')->nullable();
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->unsignedBigInteger('receiver_id')->nullable();

            $table->timestamps();
        });

        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->float('price')->default(0);
            $table->float('tax')->default(0);
            $table->float('tax_percentage')->default(0);
            $table->float('discount')->default(0);
            $table->float('quantity')->default(0);
            $table->float('total')->default(0);
            $table->string('unit')->default('unit');
            $table->string('description');

            $table->unsignedBigInteger('invoice_id');
            $table->timestamps();
        });

        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('number');
            $table->string('exp_month');
            $table->string('exp_year');
            $table->string('cvc');
            $table->boolean('default')->default(false);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->string('description');

            $table->float('amount')->default(0);
            $table->string('method')->default(PaymentMethodsEnum::CASH_ON_DELIVERY);
            $table->string('cheque_number')->nullable();
            $table->date('cheque_date')->nullable();

            $table->string('transaction_id')->nullable();
            $table->date('payment_date')->default(now());

            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('card_id')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_lines');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('cards');
        Schema::dropIfExists('invoices');
    }
}
