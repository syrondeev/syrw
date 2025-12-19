<?php
/**
 * Widget Base Class
 * Base class for all SYRW widgets
 */

namespace SYRW\Core\Elementor;

use Elementor\Widget_Base as Elementor_Widget_Base;
use SYRW\Core\Collect;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Abstract base class for widgets
 */
abstract class Widget_Base extends Elementor_Widget_Base
{
    /**
     * Namer instance
     */
    protected Namer $namer;

    /**
     * Collect helper
     */
    protected Collect $collect;

    /**
     * Configuration
     */
    protected Collect $configure;

    /**
     * Services (pipeline, template)
     */
    protected Collect $services;

    /**
     * Text domain
     */
    protected string $text_domain = 'syrw-widgets';

    /**
     * Ranges helper
     */
    protected mixed $_ranges;

    /**
     * Constructor
     */
    public function __construct($data = [], $args = null)
    {
        $this->namer = new Namer();
        $this->collect = new Collect();
        $this->configure = new Collect();
        $this->services = new Collect();

        $this->_ranges = function (int $start = 1, int $end = 32): Collect {
            return $this->collect->keyed_range($start, $end);
        };

        parent::__construct($data, $args);
    }

    /**
     * Get ranges
     */
    public function __get($name)
    {
        if ($name === 'ranges' && is_callable($this->_ranges)) {
            return call_user_func($this->_ranges);
        }

        return null;
    }

    /**
     * Get widget categories
     */
    public function get_categories(): array
    {
        return ['syrw-widgets'];
    }

    /**
     * Register controls
     */
    protected function register_controls(): void
    {
        $this->define_controls();
    }

    /**
     * Define controls (to be implemented by child class)
     */
    abstract protected function define_controls(): void;

    /**
     * Render widget output
     */
    protected function render(): void
    {
        $settings = $this->get_settings_for_display();
        $configs = new Collect($settings);

        // Get pipeline if available
        if ($this->services->has('pipeline')) {
            $pipeline = $this->services->get('pipeline');
            if (is_callable($pipeline)) {
                $pipeline_instance = $pipeline($configs);
                $configs = $pipeline_instance->get_configs($configs);
            }
        }

        // Get template if available
        if ($this->services->has('template')) {
            $template = $this->services->get('template');
            if (is_callable($template)) {
                $template_instance = $template();
                $template_instance->render($configs, $this);
            }
        }
    }

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
