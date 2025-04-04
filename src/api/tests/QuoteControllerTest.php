<?php

use PHPUnit\Framework\TestCase;

define('TESTING', true);
require_once __DIR__ . '/../controllers/QuoteController.php';

class QuoteControllerTest extends TestCase
{
    public function testHandleQuoteRequestWithValidData()
    {
        // Mock do serviço de cotação
        $mockService = $this->createMock(FreteRapidoService::class);
        $mockService->method('simularFrete')->willReturn([
            [
                'final_price' => 123.45,
                'delivery_time' => ['days' => 3],
                'carrier' => ['name' => 'Transportadora Teste'],
                'service' => 'Expresso',
            ]
        ]);

        // Criação do controller
        $controller = new QuoteController($mockService);

        // Captura da saída (JSON)
        ob_start();
        $controller->handleQuoteRequest([
            'origin_cep' => '12345678',
            'destination_cep' => '87654321',
            'shipper' => ['id' => 1],
            'volumes' => [['weight' => 2, 'price' => 50, 'sku' => '123']]
        ]);
        $output = ob_get_clean();

        // Decodifica o JSON de resposta
        $response = json_decode($output, true);

        // Verifica se o código de resposta é 200 e se a mensagem está correta
        $this->assertEquals(200, $response['code']);
    }

    public function testHandleQuoteRequestWithInvalidData()
    {
        // Mock do serviço de cotação
        $mockService = $this->createMock(FreteRapidoService::class);

        // Criação do controller
        $controller = new QuoteController($mockService);

        // Dados de entrada inválidos
        $invalidData = [
            'origin_cep' => '12345678', // Falta destination_cep, shipper e volumes
        ];

        // Captura da saída (JSON)
        ob_start();
        $controller->handleQuoteRequest($invalidData);
        $output = ob_get_clean();

        // Decodifica o JSON de resposta
        $response = json_decode($output, true);

        // Verifica se o código de resposta é 400 e se a mensagem de erro está correta
        $this->assertEquals(400, $response['code']);
    }
}
