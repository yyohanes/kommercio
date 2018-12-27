<?php

namespace KommercioIndexer\Services;

use KommercioIndexer\Config;

interface IService {

    function __construct(Config $config);

    /**
     * Set service Config
     *
     * @param Config $config
     * @return void
     */
    function setConfig(Config $config);

    /**
     * Base path of endpoint
     *
     * @return string
     */
    function getServicePath();
}
