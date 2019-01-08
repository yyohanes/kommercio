<?php

namespace KommercioIndexer\Services;

use GuzzleHttp\Exception\GuzzleException;

class OrderService extends BaseService {
    /**
     * @param array $orderData
     * @return array
     * @throws GuzzleException
     */
    public function indexOrder(array $orderData) {
        $response = $this
            ->getClient()
            ->request(
                'POST',
                $this->getBasePath(),
                [
                    'json' => $orderData,
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                    'query' => [
                        'site_id' => $this->getSiteId(),
                    ],
                ]
            );

        $jsonResponse = json_decode($response->getBody()->getContents(), true);

        return $jsonResponse;
    }

    /**
     * @inheritdoc
     */
    public function getServicePath() {
        return 'api/sales/orders';
    }
}
