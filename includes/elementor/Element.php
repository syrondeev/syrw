<?php
/**
 * Element Helper Class
 * For creating HTML elements
 */

namespace SYRW\Core\Elementor;

use SYRW\Core\Collect;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * HTML Element Builder
 */
class Element
{
    /**
     * Tag name
     */
    private string $tag;

    /**
     * Attributes
     */
    private Collect $attrs;

    /**
     * Create element
     */
    public function __construct(string $tag, array $attributes = [])
    {
        $this->tag = $tag;
        $this->attrs = new Collect($attributes);
    }

    /**
     * Create new element
     */
    public function create(string $tag, array $attributes = []): self
    {
        return new self($tag, $attributes);
    }

    /**
     * Add classes
     */
    public function classes(array $classes): self
    {
        $existing = $this->attrs->get('class', []);
        
        if (is_string($existing)) {
            $existing = explode(' ', $existing);
        }

        $this->attrs->put('class', array_merge($existing, $classes));
        
        return $this;
    }

    /**
     * Add attributes
     */
    public function attributes(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if ($key === 'class') {
                $this->classes(is_array($value) ? $value : [$value]);
            } else {
                $existing = $this->attrs->get($key);
                if (is_array($existing) && is_array($value)) {
                    $this->attrs->put($key, array_merge($existing, $value));
                } else {
                    $this->attrs->put($key, $value);
                }
            }
        }
        
        return $this;
    }

    /**
     * Build attributes string
     */
    private function build_attributes(): string
    {
        $output = [];

        $this->attrs->walk(function ($value, $key): void use (&$output) {
            if (is_array($value)) {
                if ($key === 'class') {
                    $value = implode(' ', array_filter($value));
                } else {
                    $value = implode(' ', $value);
                }
            }

            if ($value === true) {
                $output[] = esc_attr($key);
            } elseif ($value !== false && $value !== null) {
                $output[] = sprintf(
                    '%s="%s"',
                    esc_attr($key),
                    esc_attr($value)
                );
            }
        });

        return implode(' ', $output);
    }

    /**
     * Render element
     */
    public function render(callable $content = null): void
    {
        $attrs = $this->build_attributes();
        
        if ($this->is_self_closing()) {
            printf('<%s %s />', $this->tag, $attrs);
        } else {
            printf('<%s %s>', $this->tag, $attrs);
            
            if ($content !== null) {
                $content();
            }
            
            printf('</%s>', $this->tag);
        }
    }

    /**
     * Check if tag is self-closing
     */
    private function is_self_closing(): bool
    {
        $self_closing = [
            'area', 'base', 'br', 'col', 'embed', 'hr', 
            'img', 'input', 'link', 'meta', 'param', 
            'source', 'track', 'wbr'
        ];

        return in_array($this->tag, $self_closing);
    }
}
