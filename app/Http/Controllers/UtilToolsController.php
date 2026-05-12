<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class UtilToolsController extends Controller
{
    public function tasbihDigital(Request $request): \Inertia\Response
    {
        return Inertia::render('tools/utility/TasbihDigital');
    }

    public function papanSkor(Request $request): \Inertia\Response
    {
        return Inertia::render('tools/utility/PapanSkor');
    }

    public function kocokDadu(Request $request): \Inertia\Response
    {
        return Inertia::render('tools/utility/KocokDadu');
    }

    public function invoiceMaker(Request $request): \Inertia\Response
    {
        return Inertia::render('tools/utility/InvoiceMaker');
    }

    public function invoiceMakerDownload(Request $request)
    {
        $items = collect($request->input('items', []))->map(fn($i) => [
            'description' => $i['description'] ?? '',
            'price'       => (float) ($i['price']  ?? 0),
            'qty'         => (int)   ($i['qty']     ?? 1),
        ])->toArray();

        $taxRate      = (float) $request->input('taxRate',      0);
        $discountAmt  = (float) $request->input('discountAmt',  0);
        $shippingAmt  = (float) $request->input('shippingAmt',  0);
        $showTax      = (bool)  $request->input('showTax',      false);
        $showDiscount = (bool)  $request->input('showDiscount', false);
        $showShipping = (bool)  $request->input('showShipping', false);

        $subtotal       = array_reduce($items, fn($carry, $i) => $carry + $i['price'] * $i['qty'], 0);
        $taxAmount      = $showTax      ? $subtotal * $taxRate / 100 : 0;
        $discountAmount = $showDiscount ? $discountAmt               : 0;
        $shippingAmount = $showShipping ? $shippingAmt               : 0;
        $balanceDue     = $subtotal + $taxAmount - $discountAmount + $shippingAmount;

        $data = [
            'invoiceType'      => $request->input('invoiceType',   'Invoice'),
            'invoiceNumber'    => $request->input('invoiceNumber', '001'),
            'fromText'         => $request->input('fromText',      ''),
            'toText'           => $request->input('toText',        ''),
            'terms'            => $request->input('terms',         'Due On Receipt'),
            'date'             => $request->input('invoiceDate',   now()->format('m/d/Y')),
            'poNumber'         => $request->input('poNumber',      ''),
            'paymentDetails'   => $request->input('paymentDetails',''),
            'extraNotes'       => $request->input('extraNotes',    ''),
            'items'            => $items,
            'taxRate'          => $taxRate,
            'taxAmount'        => $taxAmount,
            'discountAmount'   => $discountAmount,
            'shippingAmount'   => $shippingAmount,
            'subtotal'         => $subtotal,
            'balanceDue'       => $balanceDue,
            'currencySymbol'   => $request->input('currencySymbol',  'Rp'),
            'currencyLocale'   => $request->input('currencyLocale',  'id-ID'),
            'currencyDecimals' => (int) $request->input('currencyDecimals', 0),
        ];

        $pdf = Pdf::loadView('pdf.invoice', $data)
                  ->setPaper('a4', 'portrait');

        $filename = 'invoice-' . $data['invoiceNumber'] . '-' . now()->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }
}
