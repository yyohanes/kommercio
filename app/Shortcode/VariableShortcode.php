<?php
namespace Kommercio\Shortcode;

use Maiorano\Shortcodes\Contracts;

class VariableShortcode implements Contracts\ShortcodeInterface, Contracts\AttributeInterface
{
    use Contracts\Traits\Shortcode, Contracts\Traits\Attribute, Contracts\Traits\ContainerAware;

    /**
     * @var string
     */
    protected $name = 'variable';

    /**
     * @var array
     */
    protected $attributes = array('type'=>'');

    /**
     * @var \Maiorano\Shortcodes\Manager\ManagerInterface
     */
    protected $manager;

    /**
     * @param string|null $content
     * @param array $atts
     * @return string
     */
    public function handle($content = null, array $atts=[])
    {
        $type = $atts['type'];

        $value = '';

        switch($type){
            case 'site_url':
                $value = url('/');
                break;
            default;
                break;
        }

        return $value;
    }
}