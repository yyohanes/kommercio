<?php

namespace KommercioIndexer\Services;

use GuzzleHttp\Client;
use KommercioIndexer\Config;

abstract class BaseService implements IService {
    /** @var Config $_config */
    private $_config;

    public function __construct(Config $config) {
        $this->setConfig($config);
    }

    /**
     * Get service base path
     *
     * @return string
     */
    public function getBasePath() {
        return sprintf('%s%s', $this->_config->getBaseUrl(), $this->getServicePath());
    }

    /**
     * Get site id
     *
     * @return string
     */
    public function getSiteId() {
        return $this->_config->getSiteId();
    }

    /**
     * @inheritdoc
     */
    public function setConfig(Config $config) {
        $this->_config = $config;
    }

    /**
     * @return Client
     */
    protected function getClient() {
        if (!isset($this->client)) {
            $this->client = $this->buildClient();
        }

        return $this->client;
    }

    protected function buildClient() {
        $options = [
            'base_uri' => $this->_config->getBaseUrl(),
        ];

        if (!empty($this->_config->getApiKey())) {
            $options = array_merge_recursive(
                $options,
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->apiKey,
                    ],
                ]
            );
        }

        return new Client($options);
    }
}
