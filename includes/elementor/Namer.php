<?php
/**
 * Namer Helper Class
 * For generating control names with prefix and group
 */

namespace SYRW\Core\Elementor;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper class for naming Elementor controls
 */
class Namer
{
    /**
     * Current prefix
     */
    private string $prefix = '';

    /**
     * Current group
     */
    private string $group = '';

    /**
     * Set prefix
     */
    public function prefix(string $prefix): self
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * Set group
     */
    public function group(string $group): self
    {
        $this->group = $group;
        return $this;
    }

    /**
     * Get full control name
     */
    public function get(string $name): string
    {
        $parts = array_filter([
            $this->prefix,
            $this->group,
            $name
        ]);

        return implode('_', $parts);
    }

    /**
     * Reset prefix and group
     */
    public function reset(): self
    {
        $this->prefix = '';
        $this->group = '';
        return $this;
    }
}
