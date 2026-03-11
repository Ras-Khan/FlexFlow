<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function generate(Request $request)
    {
        return response()->json([
            'filename' => 'invoice_'.now()->format('Ymd').'.pdf',
            'url' => 'http://localhost:8000/storage/invoices/invoice.pdf'
        ]);
    }
}
