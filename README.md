# laravel Invoicable

[![Software License][ico-license]](LICENSE.md)

Easy invoice creation for Laravel. Unlike Laravel Cashier, this package is payment gateway agnostic.

## Structure

```
database/
resources
src/
tests/
vendor/
```

## Install

Via Composer

```bash
composer require bkfdev/invoicable
```

Next, you must install the service provider if you work with Laravel 5.4:

```php
// config/app.php
'providers' => [
    ...
    Bkfdev\Invoicable\InvoicableServiceProvider::class,
];
```

You can publish the migration with:

```bash
php artisan vendor:publish --provider="Bkfdev\Invoicable\InvoicableServiceProvider" --tag="migrations"
```

After the migration has been published you can create the invoices and invoice_lines tables by running the migrations:

```bash
php artisan migrate
```

Optionally, you can also publish the `invoicable.php` config file with:

```bash
php artisan vendor:publish --provider="Bkfdev\Invoicable\InvoicableServiceProvider" --tag="config"
```

This is what the default config file looks like:

```php

return [
    'invoice_number' => 'INV#' . date('M-y') . '-',
    'payment_number' => 'PAY#' . date('M-y') . '-',
];
```

If you'd like to override the design of the invoice blade view and pdf, publish the view:

```bash
php artisan vendor:publish --provider="Bkfdev\Invoicable\InvoicableServiceProvider" --tag="views"
```

You can now edit `receipt.blade.php` in `<project_root>/resources/views/invoicable/receipt.blade.php` to match your style.

## Usage

**Money figures are in cents!**

Add the invoicable trait to the Eloquent model which needs to be invoiced (typically an Order model):

```php
use Illuminate\Database\Eloquent\Model;
use Bkfdev\Invoicable\IsInvoicable\IsInvoicableTrait;

class Order extends Model
{
    use IsInvoicableTrait; // enables the ->invoices() Eloquent relationship
}
```

Now you can create invoices for an Order:

```php
$order = Order::first();
$invoice = $order->invoices()->create([]);

// To add a line to the invoice, use these example parameters:
//  Amount:
//      121 (€1,21) incl tax
//      100 (€1,00) excl tax
//  Description: 'Some description'
//  Tax percentage: 0.21 (21%)
$invoice = $invoice->addAmountInclTax(121, 'Some description', 0.21);
$invoice = $invoice->addAmountExclTax(100, 'Some description', 0.21);

// Invoice totals are now updated
echo $invoice->total; // 242
echo $invoice->tax; // 42

// Set additional information (optional)
$invoice->currency; // defaults to 'EUR' (see config file)
$invoice->status; // defaults to 'concept' (see config file)
$invoice->receiver_info; // defaults to null
$invoice->sender_info; // defaults to null
$invoice->payment_info; // defaults to null
$invoice->note; // defaults to null

// access individual invoice lines using Eloquent relationship
$invoice->lines;
$invoice->lines();

// Access as pdf
$invoice->download(); // download as pdf (returns http response)
$invoice->pdf(); // or just grab the pdf (raw bytes)

// Handling discounts
// By adding a line with a negative amount.
$invoice = $invoice->addAmountInclTax(-121, 'A nice discount', 0.21);

// Or by applying the discount and discribing the discount manually
$invoice = $invoice->addAmountInclTax(121 * (1 - 0.30), 'Product XYZ incl 30% discount', 0.21);

// Convenience methods
Invoice::findByReference($reference);
Invoice::findByReferenceOrFail($reference);
$invoice->invoicable() // Access the related model
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

```bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email info@Bkfdev.com instead of using the issue tracker.

## Credits

- [Abdelkader Boukhelf][link-author]
- [All Contributors][link-contributors]
- Inspired by [Laravel Cashier](https://github.com/laravel/cashier)'s invoices.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[link-author]: https://github.com/aeq-dev
[link-contributors]: ../../contributors
