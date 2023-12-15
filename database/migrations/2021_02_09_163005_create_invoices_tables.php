<?php

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
            $table->uuid('id')->primary();
            $table->string('number');
            $table->morphs('invoicable');
            $table->decimal('tax', 17, 4)->default(0); //
            $table->decimal('discount', 17, 4)->default(0);
            $table->string('discount_type')->default('amount'); //percentage,amount
            $table->decimal('total', 17, 4)->default(0);
            $table->decimal('balance', 17, 4)->default(0);
            $table->decimal('amount_paid', 17, 4)->default(0);
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->string('status')->default('unpaid');
            $table->date('invoice_date')->nullable();
            $table->date('due_date')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->string('type')->default('invoice');

            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('price', 17, 4)->default(0);
            $table->decimal('tax')->default(0);
            $table->decimal('discount', 17, 4)->default(0);
            $table->decimal('qty')->default(1);
            $table->decimal('total', 17, 4)->default(1);
            $table->float('tax_percentage')->default(0);
            $table->string('unit')->default('unit');
            $table->uuid('invoice_id');
            $table->string('description');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('number');
            $table->string('exp_month');
            $table->string('exp_year');
            $table->string('cvc');
            $table->boolean('default')->default(false);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number');
            $table->string('description');

            $table->decimal('amount', 17, 4)->default(0);
            $table->char('currency', 3);
            $table->string('method')->default('cash');
            $table->string('cheque_number')->nullable();
            $table->date('cheque_date')->nullable();

            $table->string('transaction_id')->nullable();
            $table->date('payment_date')->default(now());

            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('card_id')->nullable();
            $table->uuid('invoice_id')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();
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
