<?php

namespace Kommercio\Scout\ElasticSearch\Configurators;

use ScoutElastic\IndexConfigurator;
use ScoutElastic\Migratable;

class LineItemIndexConfigurator extends IndexConfigurator
{
    use Migratable;

    protected $name = 'line_items';

    /**
     * @var array
     */
    protected $settings = [
        'analysis' => [
            'analyzer' => [
                'default' => [
                    'type' => 'standard',
                ],
            ],
        ]
    ];
}
