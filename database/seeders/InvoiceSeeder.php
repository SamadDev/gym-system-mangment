<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $payments = Payment::all();
        
        foreach ($payments as $index => $payment) {
            $subtotal = $payment->amount;
            $tax = $subtotal * 0.00; // 0% tax for now
            $discount = 0;
            $total = $subtotal + $tax - $discount;
            
            Invoice::create([
                'payment_id' => $payment->id,
                'invoice_number' => 'INV-' . $payment->payment_date->format('Y') . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => $discount,
                'total' => $total,
                'issue_date' => $payment->payment_date,
            ]);
        }
    }
}
