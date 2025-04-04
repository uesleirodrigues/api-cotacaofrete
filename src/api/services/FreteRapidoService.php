<?php

class FreteRapidoService
{
    private $default_cnpj = '25438296000158';
    private $default_token = '1d52a9b6b78cf07b08586152459a5c90';
    private $default_platform_code = '5AKVkHqCn';

    public function simularFrete($origin_cep, $destination_cep, $shipper, $volumes)
    {
        $payload = [
            'simulation_type' => [0, 1],
            'shipper' => [
                'registered_number' => $shipper['registered_number'] ?? $this->default_cnpj,
                'token' => $shipper['token'] ?? $this->default_token,
                'platform_code' => $shipper['platform_code'] ?? $this->default_platform_code,
            ],
            'recipient' => [
                'type' => 0,
                'country' => 'BRA',
                'zipcode' => (int)$destination_cep,
            ],
            'dispatchers' => [
                [
                    'registered_number' => $shipper['registered_number'] ?? $this->default_cnpj,
                    'zipcode' => (int)$origin_cep,
                    'volumes' => $volumes,
                ],
            ],
        ];

        $ch = curl_init('https://sp.freterapido.com/api/v3/quote/simulate');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response, true);
        return $json['dispatchers'][0]['offers'] ?? [];
    }
}
