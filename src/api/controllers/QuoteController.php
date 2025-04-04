<?php

require_once __DIR__ . '/../services/FreteRapidoService.php';
require_once dirname(__DIR__) . '/db/database.php';

class QuoteController
{
    private $freteService;

    public function __construct($freteService = null)
    {
        $this->freteService = $freteService ?? new FreteRapidoService();
    }

    public function handleQuoteRequest()
    {
        $request_body = file_get_contents('php://input');
        $data = json_decode($request_body, true);

        if (
            $data === null ||
            !isset($data['origin_cep']) ||
            !isset($data['destination_cep']) ||
            !isset($data['shipper']) ||
            !isset($data['volumes'])
        ) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Requisição inválida. Campos origin_cep, destination_cep, shipper e volumes são obrigatórios.'
            ]);
            return;
        }

        $origin_cep = preg_replace('/[^0-9]/', '', $data['origin_cep']);
        $destination_cep = preg_replace('/[^0-9]/', '', $data['destination_cep']);
        $shipper = $data['shipper'];
        $volumes = $data['volumes'];

        $offers = $this->freteService->simularFrete($origin_cep, $destination_cep, $shipper, $volumes);

        if (empty($offers)) {
            http_response_code(204);
            echo json_encode(['message' => 'Nenhuma cotação de frete encontrada.']);
            return;
        }

        $pdo = getConnection();
        foreach ($offers as $offer) {
            $stmt = $pdo->prepare("
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

        echo json_encode(['message' => 'Cotações realizadas com sucesso.', 'offers' => $offers]);
    }
}
