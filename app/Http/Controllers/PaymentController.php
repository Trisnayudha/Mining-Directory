<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use App\Models\PaymentInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Xendit\Invoice as XenditInvoice;
use Xendit\Invoice\Invoice;
use Xendit\Xendit;
use Goutte\Client;

class PaymentController extends Controller
{
    use ResponseHelper;

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
    /**
     * Mendapatkan informasi perusahaan yang terautentikasi.
     *
     * @return \App\Models\Company
     */
    protected function getAuthenticatedCompany()
    {
        return Auth::guard('company')->user(); // Mendapatkan user company yang sedang login
    }

    /**
     * Membuat invoice pembayaran menggunakan Xendit
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function payment(Request $request)
    {
        // Ambil informasi company yang terautentikasi
        $company = $this->getAuthenticatedCompany();
        $usd = $request->input('price');
        $pricing_period = $request->input('pricing_period');
        $type = $request->input('type');

        $amount = $usd * $this->scrape();
        $description = 'Invoice package ' . $type;

        // Membuat invoice menggunakan Xendit
        try {
            // Parameter invoice yang dikirimkan ke Xendit
            $params = [
                'external_id' => 'invoice_' . time(),
                'payer_email' => $company->email,
                'description' => $description,
                'amount' => $amount,
            ];
            Xendit::setApiKey($this->secretKey);
            // Membuat invoice menggunakan Xendit SDK
            $invoice = XenditInvoice::create($params);

            // Konversi expiry_date dari ISO 8601 ke format MySQL
            $expiryDate = (new \DateTime($invoice['expiry_date']))->format('Y-m-d H:i:s');

            // Split bagian ID setelah /web/ pada invoice_url
            $urlParts = explode('/web/', $invoice['invoice_url']);
            $urlWebId = $urlParts[1]; // Mengambil bagian setelah /web/

            // Simpan invoice ke database
            $savedInvoice = PaymentInvoice::create([
                'external_id' => $invoice['external_id'],
                'user_id' => $invoice['user_id'],
                'company_id' => $company->id,
                'type' => $type,
                'pricing_period' => $pricing_period,
                'status' => $invoice['status'],
                'merchant_name' => 'Mining Directory',
                'merchant_profile_picture_url' => $company->logo_url ?? null,
                'amount' => $invoice['amount'],
                'payer_email' => $invoice['payer_email'],
                'description' => $invoice['description'],
                'expiry_date' => $expiryDate, // Pastikan sudah dikonversi ke format MySQL
                'invoice_url' => $invoice['invoice_url'],
            ]);

            // Mengembalikan response sukses dengan tambahan url_web
            return $this->sendResponse('Successfully processed invoice', [
                'request_data' => $request->all(),
                'invoice_data' => $savedInvoice,
                'url_web' => $urlWebId, // Bagian ID yang diambil dari URL
            ], 200);
        } catch (\Exception $e) {
            return $this->sendResponse('Gagal membuat invoice', [
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function scrape()
    {
        $client = new Client();

        // URL target
        $url = 'https://kursdollar.org/real-time/USD/';
        // Mengirim permintaan GET ke halaman web
        $crawler = $client->request('GET', $url);

        // Mencari elemen dengan ID "nilai"
        $value = $crawler->filter('.in_table tr:nth-child(3) > td:first-child')->text();

        // Menghilangkan titik dan mengganti koma dengan titik
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);

        // Mengonversi nilai tukar menjadi float
        $floatValue = (float) $value;

        // Mengonversi nilai tukar menjadi integer (dengan pembulatan)
        $intValue = (int) round($floatValue);

        // Mengembalikan nilai tukar dalam format integer
        return $intValue;
    }
}
