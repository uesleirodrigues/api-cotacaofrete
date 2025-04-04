<?php

use PHPUnit\Framework\TestCase;

define('TESTING', true);
require_once __DIR__ . '/../controllers/MetricsController.php';

class MetricsControllerTest extends TestCase
{
    public function testHandleMetricsRequestWithQuotes()
    {
        // Mock do banco de dados (PDO)
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        // Simula o retorno das cotações do banco
        $mockStmt->method('fetchAll')->willReturn([
            [
                'carrier' => 'Transportadora Teste',
                'price' => 123.45
            ],
            [
                'carrier' => 'Transportadora Teste',
                'price' => 150.00
            ],
            [
                'carrier' => 'Outra Transportadora',
                'price' => 100.00
            ]
        ]);
        $mockPdo->method('prepare')->willReturn($mockStmt);

        // Criação do controller com o PDO mockado
        $controller = new MetricsController($mockPdo);

        // Captura da saída (JSON)
        ob_start();
        $controller->handleMetricsRequest();
        $output = ob_get_clean();

        // Decodifica o JSON de resposta
        $response = json_decode($output, true);

        // Verifica se o código de resposta é 200 e se a estrutura das métricas está correta
        $this->assertArrayHasKey('per_carrier', $response);
        $this->assertArrayHasKey('min_price', $response);
        $this->assertArrayHasKey('max_price', $response);
        $this->assertEquals(100.00, $response['min_price']);
        $this->assertEquals(150.00, $response['max_price']);

        // Verifica as métricas por transportadora
        $this->assertArrayHasKey('Transportadora Teste', $response['per_carrier']);
        $this->assertEquals(2, $response['per_carrier']['Transportadora Teste']['count']);
        $this->assertEquals(136.73, $response['per_carrier']['Transportadora Teste']['avg_price']);
        $this->assertArrayHasKey('Outra Transportadora', $response['per_carrier']);
        $this->assertEquals(1, $response['per_carrier']['Outra Transportadora']['count']);
        $this->assertEquals(100.00, $response['per_carrier']['Outra Transportadora']['avg_price']);
    }

    public function testHandleMetricsRequestWithNoQuotes()
    {
        // Mock do banco de dados (PDO)
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        // Simula o retorno de nenhuma cotação do banco
        $mockStmt->method('fetchAll')->willReturn([]);
        $mockPdo->method('prepare')->willReturn($mockStmt);

        // Criação do controller com o PDO mockado
        $controller = new MetricsController($mockPdo);

        // Captura da saída (JSON)
        ob_start();
        $controller->handleMetricsRequest();
        $output = ob_get_clean();

        // Decodifica o JSON de resposta
        $response = json_decode($output, true);

        // Verifica se o código de resposta é 204 e se a mensagem está correta
        $this->assertEquals(204, $response['code']);
        $this->assertStringContainsString('Nenhuma cotação encontrada.', $response['message']);
    }
}
