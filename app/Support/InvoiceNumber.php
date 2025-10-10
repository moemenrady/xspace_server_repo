<?php
// app/Support/InvoiceNumber.php
namespace App\Support;

use App\Models\Invoice;

class InvoiceNumber
{
    public static function next(): string
    {
        $date = now()->format('Ymd');
        $prefix = "INV-{$date}-";
        $last = Invoice::where('invoice_number','like',$prefix.'%')
            ->orderByDesc('id')->value('invoice_number');

        $seq = 1;
        if ($last) {
            $seq = (int)substr($last, -4) + 1;
        }
        return $prefix . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }
}
