<?php

require_once dirname(__DIR__) . '/db/database.php';

class MetricsController
{
    public function handleMetricsRequest()
    {
        $pdo = getConnection();

        $limit = isset($_GET['last_quotes']) ? (int)$_GET['last_quotes'] : null;

        $sql = "SELECT * FROM quotes ORDER BY id DESC";
        if ($limit !== null && $limit > 0) {
            $sql .= " LIMIT :limit";
        }

        $stmt = $pdo->prepare($sql);
        if ($limit !== null && $limit > 0) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }
        $stmt->execute();
        $quotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($quotes)) {
            http_response_code(204);
            echo json_encode(['message' => 'Nenhuma cotação encontrada.']);
            return;
        }

        $metrics = [];

        foreach ($quotes as $quote) {
            $carrier = $quote['carrier'];
            $price = (float)$quote['price'];

            if (!isset($metrics[$carrier])) {
                $metrics[$carrier] = [
                    'count' => 0,
                    'total_price' => 0.0,
                ];
            }

            $metrics[$carrier]['count'] += 1;
            $metrics[$carrier]['total_price'] += $price;
        }

        foreach ($metrics as $carrier => &$data) {
            $data['avg_price'] = $data['count'] > 0
                ? round($data['total_price'] / $data['count'], 2)
                : 0;
        }

        // Frete mais barato e mais caro
        $sorted = array_column($quotes, 'price');
        $min_price = min($sorted);
        $max_price = max($sorted);

        echo json_encode([
            'per_carrier' => $metrics,
            'min_price' => $min_price,
            'max_price' => $max_price,
        ]);
    }
}
