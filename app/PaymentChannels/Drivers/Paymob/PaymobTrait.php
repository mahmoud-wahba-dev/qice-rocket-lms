<?php

namespace App\PaymentChannels\Drivers\Paymob;


trait PaymobTrait
{
    private function paymobPost(string $url, array $payload): ?\stdClass
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException('Paymob request failed: ' . $error);
        }

        curl_close($ch);

        $decoded = json_decode($response);

        if (!is_object($decoded)) {
            throw new \RuntimeException('Invalid Paymob response');
        }

        return $decoded;
    }

    private function paymobErrorMessage(?\stdClass $response): string
    {
        if (empty($response)) {
            return 'Empty response from Paymob';
        }

        if (!empty($response->detail)) {
            return is_string($response->detail) ? $response->detail : json_encode($response->detail);
        }

        if (!empty($response->message)) {
            return is_string($response->message) ? $response->message : json_encode($response->message);
        }

        return 'Unknown Paymob error';
    }

    public function AuthenticationRequest()
    {
        if (!empty($this->api_key)) {
            return $this->paymobPost('https://ksa.paymob.com/api/auth/tokens', [
                'api_key' => $this->api_key,
            ]);
        }

        return $this->paymobPost('https://ksa.paymob.com/api/auth/tokens', [
            'username' => $this->username,
            'password' => $this->password,
        ]);
    }

    public function OrderRegistrationAPI(array $requestData)
    {
        return $this->paymobPost('https://ksa.paymob.com/api/ecommerce/orders', $requestData);
    }

    public function PaymentKeyRequest($requestData)
    {
        $requestData['expiration'] = 3600;
        $requestData['integration_id'] = (int) $this->integration_id;

        return $this->paymobPost('https://ksa.paymob.com/api/acceptance/payment_keys', $requestData);
    }

    public function calcHMAC($request)
    {
        $values = $request->only([
            'amount_cents',
            'created_at',
            'currency',
            'error_occured',
            'has_parent_transaction',
            'id',
            'integration_id',
            'is_3d_secure',
            'is_auth',
            'is_capture',
            'is_refunded',
            'is_standalone_payment',
            'is_voided',
            'order',
            'owner',
            'pending',
            'source_data_pan',
            'source_data_sub_type',
            'source_data_type',
            'success'
        ]);

        foreach ($values as &$val) {
            if (is_array($val)) {
                $val = array_values($val);
                $val = implode($val);
            }
            if ($val === true) $val = "true";
            if ($val === false) $val = "false";
        }
        $concatenate = implode($values);
        $hash = hash_hmac('sha512', $concatenate, $this->HMAC);

        return $hash;
    }

}
