<?php

namespace App\Http\Controllers\Callback;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ResponseHelper;
use App\Models\PaymentInvoice;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Xendit\Invoice\Invoice;

class XenditController extends Controller
{
    use ResponseHelper; // Gunakan trait di sini
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $secretKey;
    protected $publicKey;

    public function __construct()
    {
        // Tentukan apakah menggunakan environment production atau test (staging)
        if (env('XENDIT_ISPROD') === 'true') {
            $this->secretKey = env('XENDIT_SECRET_KEY_PROD');
            $this->publicKey = env('XENDIT_PUBLIC_KEY_PROD');
        } else {
            $this->secretKey = env('XENDIT_SECRET_KEY_TEST');
            $this->publicKey = env('XENDIT_PUBLIC_KEY_TEST');
        }
    }

    // Validasi signature berdasarkan key yang disimpan di constructor
    private function validateSignature(Request $request)
    {
        $calculatedHmac = hash_hmac('sha256', $request->getContent(), $this->secretKey);
        $xSignature = $request->header('x-callback-token');

        if ($calculatedHmac !== $xSignature) {
            Log::error('Invalid signature received from Xendit.');
            return $this->sendResponse('Invalid signature', null, 403);
        }

        return true;
    }

    // Handle Xendit Invoice callback
    public function handlePaymentCallback(Request $request)
    {
        // Ambil external_id dari request yang dikirimkan oleh Xendit
        $external_id = $request->input('external_id');
        $status = $request->input('status');

        // Temukan invoice di database berdasarkan external_id
        $invoice = PaymentInvoice::where('external_id', $external_id)->first();

        // Jika invoice ditemukan, perbarui status dan informasi lainnya
        if ($invoice) {
            // Konversi expiry_date dari ISO 8601 ke format MySQL
            $expiryDate = (new DateTime($request->input('expiry_date')))->format('Y-m-d H:i:s');

            $invoice->update([
                'status' => $status,
                'paid_amount' => $request->input('paid_amount', $invoice->paid_amount),
                'bank_code' => $request->input('bank_code', $invoice->bank_code),
                'paid_at' => $request->input('paid_at', $invoice->paid_at),
                'fees_paid_amount' => $request->input('fees_paid_amount', $invoice->fees_paid_amount),
                'updated_at' => date('Y-m-d H:i:s'), // Menggunakan fungsi date() PHP
                'expiry_date' => $expiryDate, // Konversi expiry_date ke format MySQL
            ]);

            // Menggunakan sendResponse untuk mengirim response sukses
            return $this->sendResponse('Successfully processed invoice callback', [
                'request_data' => $request->all(),
                'invoice_data' => $invoice
            ], 200);
        }

        // Jika invoice tidak ditemukan, kembalikan response error
        return $this->sendResponse('Invoice not found', [
            'request_data' => $request->all()
        ], 404);
    }


    // Handle Virtual Account callback
    public function handleVirtualAccount(Request $request)
    {
        $signatureValidation = $this->validateSignature($request);

        if ($signatureValidation !== true) {
            return $signatureValidation;
        }

        try {
            Log::info('Virtual Account Callback: ', $request->all());

            $data = $request->all();  // Proses data callback untuk virtual account

            return $this->sendResponse('Successfully processed virtual account', $data, 200);
        } catch (\Exception $e) {
            Log::error('Error processing virtual account callback: ' . $e->getMessage());
            return $this->sendResponse('Internal server error', null, 500);
        }
    }

    // Handle E-Wallet callback
    public function handleEWallet(Request $request)
    {
        $signatureValidation = $this->validateSignature($request);

        if ($signatureValidation !== true) {
            return $signatureValidation;
        }

        try {
            Log::info('E-Wallet Callback: ', $request->all());

            $data = $request->all();  // Proses data callback untuk e-wallet

            return $this->sendResponse('Successfully processed e-wallet', $data, 200);
        } catch (\Exception $e) {
            Log::error('Error processing e-wallet callback: ' . $e->getMessage());
            return $this->sendResponse('Internal server error', null, 500);
        }
    }

    // Format sendResponse yang digunakan untuk merespons callback
    public function sendResponse($message, $data = null, $status = 200)
    {
        return response()->json([
            'message' => $message,
            'data' => $data
        ], $status);
    }
}
