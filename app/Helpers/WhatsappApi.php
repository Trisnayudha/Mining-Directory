<?php

namespace App\Helpers;

class WhatsappApi
{
    public $phone;
    public $document;
    public $message;
    public $res;
    public $image;
    public $caption;

    public function WhatsappMessage()
    {
        try {
            $phone = $this->phone;
            $message = $this->message;
            $token = "7EoagVjJfYgElEkYI1KKXOObIzZoGB7S1QcDQbbOH6dqKNk6SL";

            // Melakukan pengecekan nomor telepon menggunakan endpoint check-number
            $checkUrl = 'https://nusagateway.com/api/check-number.php';
            $checkData = [
                'phone' => $phone,
                'token' => $token
            ];

            $checkResponse = $this->makeCurlRequest($checkUrl, 'POST', $checkData);

            if ($checkResponse['status'] === 'valid') {
                // Nomor telepon valid, lanjutkan proses pengiriman pesan WhatsApp

                $sendMessageUrl = 'https://nusagateway.com/api/send-message.php';
                $sendMessageData = [
                    'token' => $token,
                    'phone' => $phone,
                    'message' => $message
                ];

                $sendMessageResponse = $this->makeCurlRequest($sendMessageUrl, 'POST', $sendMessageData);


                return $this->res = 'valid';
            } else {
                // Nomor telepon tidak valid, kembalikan pesan error
                return $this->res = 'invalid';
            }
        } catch (\Exception $th) {
            return $this->res = $th->getMessage();
        }
    }
    public function WhatsappMessageErina()
    {
        try {
            $phone = $this->phone;
            $message = $this->message;
            $token = "7EoagVjJfYgElEkYI1KKXOObIzZoGB7S1QcDQbbOH6dqKNk6SL";

            // Melakukan pengecekan nomor telepon menggunakan endpoint check-number
            $checkUrl = 'https://nusagateway.com/api/check-number.php';
            $checkData = [
                'phone' => $phone,
                'token' => $token
            ];

            $checkResponse = $this->makeCurlRequest($checkUrl, 'POST', $checkData);

            if ($checkResponse['status'] === 'valid') {
                // Nomor telepon valid, lanjutkan proses pengiriman pesan WhatsApp

                $sendMessageUrl = 'https://nusagateway.com/api/send-message.php';
                $sendMessageData = [
                    'token' => $token,
                    'phone' => $phone,
                    'message' => $message
                ];

                $sendMessageResponse = $this->makeCurlRequest($sendMessageUrl, 'POST', $sendMessageData);


                return $this->res = 'valid';
            } else {
                // Nomor telepon tidak valid, kembalikan pesan error
                return $this->res = 'invalid';
            }
        } catch (\Exception $th) {
            return $this->res = $th->getMessage();
        }
    }

    // Fungsi untuk melakukan permintaan cURL
    private function makeCurlRequest($url, $method, $data)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        $response = curl_exec($ch);

        if ($response === false) {
            throw new \Exception(curl_error($ch));
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($statusCode !== 200) {
            throw new \Exception('Request failed with status code ' . $statusCode);
        }

        return json_decode($response, true);
    }

    public function WhatsappMessageWithImage()
    {
        try {
            $phone = $this->phone;
            $caption = $this->caption;
            $token = "7EoagVjJfYgElEkYI1KKXOObIzZoGB7S1QcDQbbOH6dqKNk6SL";
            $image = "https://indonesiaminer.com" . $this->image;

            $url = 'https://nusagateway.com/api/send-image.php';
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, array(
                'token'    => $token,
                'phone'     => $phone,
                'caption'   => $caption,
                'image'     => $image,
            ));
            $status = curl_exec($curl);
            curl_close($curl);
            return $this->res = $status;
        } catch (\Exception $th) {
            return $this->res = $th->getMessage();
        }
    }

    public function WhatsappMessageWithDocument()
    {
        try {
            $phone = $this->phone;
            $document = $this->document;
            $token = "7EoagVjJfYgElEkYI1KKXOObIzZoGB7S1QcDQbbOH6dqKNk6SL";

            $url = 'https://nusagateway.com/api/send-document.php';
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, array(
                'token'    => $token,
                'phone'     => $phone,
                'document'   => $document,
            ));
            $status = curl_exec($curl);
            curl_close($curl);
            return $this->res = $status;
        } catch (\Exception $th) {
            return $this->res = $th->getMessage();
        }
    }
    public function WhatsappMessageWithDocumentErina()
    {
        try {
            $phone = $this->phone;
            $document = $this->document;
            $token = "7EoagVjJfYgElEkYI1KKXOObIzZoGB7S1QcDQbbOH6dqKNk6SL";

            $url = 'https://nusagateway.com/api/send-document.php';
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, array(
                'token'    => $token,
                'phone'     => $phone,
                'document'   => $document,
            ));
            $status = curl_exec($curl);
            curl_close($curl);
            return $this->res = $status;
        } catch (\Exception $th) {
            return $this->res = $th->getMessage();
        }
    }
}
