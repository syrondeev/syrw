<?php
/**
 * Pipeline Core Class
 * Base class for widget pipelines (data processing)
 */

namespace SYRW\Core\Elementor;

use SYRW\Core\Collect;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Abstract base class for pipelines
 */
abstract class Pipeline_Core
{
    /**
     * Widget configs
     */
    protected Collect $configs;

    /**
     * Text domain
     */
    protected string $text_domain = 'syrw-widgets';

    /**
     * Constructor
     */
    public function __construct(Collect $configs)
    {
        $this->configs = $configs;
    }

    /**
     * Get processed configs (to be implemented by child class)
     */
    abstract public function get_configs(Collect $configs): Collect;

    /**
     * Compose link attributes
     */
    protected function compose_link(Collect $config, array $classes = []): Collect
    {
        $link = $config->get('link', []);
        if (!($link instanceof Collect)) {
            $link = new Collect($link);
        }

        $attributes = new Collect([
            'href' => $link->get('url', '#'),
            'class' => $classes,
        ]);

        if ($link->get('is_external') === 'on' || $link->get('is_external') === true) {
            $attributes->put('target', '_blank');
        }

        if ($link->get('nofollow') === 'on' || $link->get('nofollow') === true) {
            $attributes->put('rel', 'nofollow');
        }

        return $attributes;
    }
}
