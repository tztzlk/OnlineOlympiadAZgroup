<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OlympiadRequest;

class PaymentController extends Controller
{
    public function show($id)
    {
        $request = OlympiadRequest::with('subject')->findOrFail($id);

        // Здесь можно генерировать реальный QR через библиотеку,
        // например https://github.com/endroid/qr-code
        // Пока для примера — просто ссылка на изображение QR
        $qrImage = "/images/qr_placeholder.png"; 

        return view('payment.qr', compact('request', 'qrImage'));
    }
}
