<?php

namespace Kommercio\Scout\ElasticSearch\Configurators;

use ScoutElastic\IndexConfigurator;
use ScoutElastic\Migratable;

class OrderIndexConfigurator extends IndexConfigurator
{
    use Migratable;

    protected $name = 'orders';

    /**
     * @var array
     */
    protected $settings = [
        'analysis' => [
            'analyzer' => [
                'default' => [
                    'type' => 'custom',
                    'tokenizer' => 'standard',
                    'char_filter' => [
                        'html_strip',
                    ],
                    'filter' => [
                        'lowercase',
                        'word_delimiter',
                        'stopwords' => [
                            'type' => 'stop',
                            'stopwords' => '_english_',
                        ],
                    ],
                ],
            ],
        ]
    ];
}
