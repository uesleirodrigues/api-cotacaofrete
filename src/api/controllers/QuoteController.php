<?php

require_once __DIR__ . '/../services/FreteRapidoService.php';

if (!defined('TESTING')) {
    require_once dirname(__DIR__) . '/db/database.php';
}

class QuoteController
{
    private $freteService;
    private $pdo;

    public function __construct($freteService = null, $pdo = null)
    {
        $this->freteService = $freteService ?? new FreteRapidoService();
        $this->pdo = $pdo ?? (function_exists('getConnection') ? getConnection() : null);
    }

    /**
     * Processa uma solicitação de cotação de frete.
     * Permite injetar $inputData para facilitar testes.
     */
    public function handleQuoteRequest(array $inputData = null)
    {
        $data = $inputData ?? json_decode(file_get_contents('php://input'), true);

        // Verificação de campos obrigatórios
        if (
            $data === null ||
            !isset($data['origin_cep']) ||
            !isset($data['destination_cep']) ||
            !isset($data['shipper']) ||
            !isset($data['volumes'])
        ) {
            echo json_encode([
                'code' => 400,
                'error' => 'Requisição inválida. Campos origin_cep, destination_cep, shipper e volumes são obrigatórios.'
            ]);
            return;
        }

        $origin_cep = preg_replace('/[^0-9]/', '', $data['origin_cep']);
        $destination_cep = preg_replace('/[^0-9]/', '', $data['destination_cep']);
        $shipper = $data['shipper'];
        $volumes = $data['volumes'];

        // Chama o serviço para obter as ofertas de frete
        $offers = $this->freteService->simularFrete($origin_cep, $destination_cep, $shipper, $volumes);

        // Caso não haja ofertas de frete
        if (empty($offers)) {
            echo json_encode([
                'code' => 204,
                'message' => 'Nenhuma cotação de frete encontrada.'
            ]);
            return;
        }

        // Salva as ofertas no banco de dados, se a conexão com o PDO estiver disponível
        if ($this->pdo) {
            $this->saveOffers($offers, $origin_cep, $destination_cep);
        }

        // Resposta de sucesso com as cotações
        echo json_encode([
            'code' => 200,
            'message' => 'Cotações realizadas com sucesso.',
            'offers' => $offers
        ]);
    }

    private function saveOffers(array $offers, string $origin_cep, string $destination_cep): void
    {
        foreach ($offers as $offer) {
            $stmt = $this->pdo->prepare("
                INSERT INTO quotes (
                    origin_cep,
                    destination_cep,
                    price,
                    delivery_time,
                    carrier,
                    service
                ) VALUES (
                    :origin,
                    :dest,
                    :price,
                    :delivery_time,
                    :carrier,
                    :service
                )
            ");

            $stmt->execute([
                ':origin' => $origin_cep,
                ':dest' => $destination_cep,
                ':price' => $offer['final_price'] ?? 0,
                ':delivery_time' => $offer['delivery_time']['days'] ?? null,
                ':carrier' => $offer['carrier']['name'] ?? 'Desconhecida',
                ':service' => $offer['service'] ?? 'Não informado',
            ]);
        }
    }
}
