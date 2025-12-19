<?php
/**
 * Template Core Class
 * Base class for widget templates (HTML rendering)
 */

namespace SYRW\Core\Elementor;

use SYRW\Core\Collect;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Abstract base class for templates
 */
abstract class Template_Core
{
    /**
     * Element builder
     */
    protected Element $element;

    /**
     * Text domain
     */
    protected string $text_domain = 'syrw-widgets';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->element = new Element('div');
    }

    /**
     * Render template (to be implemented by child class)
     */
    abstract public function render(Collect $configs, $module): void;
}
