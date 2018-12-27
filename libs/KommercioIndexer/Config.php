<?php

namespace KommercioIndexer;

class Config {
    private $_baseUrl;
    private $_siteId;
    private $_apiKey = '';

    public function __construct(string $siteId, string $baseUrl) {
        $this->_baseUrl = $baseUrl;
        $this->_siteId = $siteId;
    }

    /**
     * Will have forward-slash as suffix
     *
     * @return string
     */
    public function getBaseUrl() {
        return preg_match('/\/$/', $this->_baseUrl) ? $this->_baseUrl : sprintf('%s/', $this->_baseUrl);
    }

    public function getSiteId() {
        return $this->_siteId;
    }

    /**
     * TODO: Complete this method
     *
     * @return string
     */
    public function getApiKey() {
        return $this->_apiKey;
    }
}
