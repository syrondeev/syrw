<?php
/**
 * Collection Helper Class
 * Simplified version for SYRW Elementor Widgets
 */

namespace SYRW\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Collection class for working with arrays
 */
class Collect
{
    /**
     * The items contained in the collection
     */
    private array $items = [];

    /**
     * Initialize collection
     */
    public function __construct(mixed $items = [])
    {
        $this->items = $this->to_array_items($items);
    }

    /**
     * Convert items to array
     */
    private function to_array_items(mixed $items): array
    {
        if (is_array($items)) {
            return $items;
        }

        if ($items instanceof self) {
            return $items->all();
        }

        return (array) $items;
    }

    /**
     * Create new instance
     */
    public static function make(mixed $items = []): self
    {
        return new static($items);
    }

    /**
     * Get all items
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Get an item by key
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->items)) {
            $value = $this->items[$key];
            
            if (is_array($value)) {
                return new static($value);
            }
            
            return $value;
        }

        return $default;
    }

    /**
     * Put an item in the collection by key
     */
    public function put(string $key, mixed $value): self
    {
        $this->items[$key] = $value;
        return $this;
    }

    /**
     * Check if key exists
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Check if key matches value
     */
    public function match(string $key, mixed $value): bool
    {
        return $this->get($key) === $value;
    }

    /**
     * Check if key doesn't match value
     */
    public function not_match(string $key, mixed $value): bool
    {
        return !$this->match($key, $value);
    }

    /**
     * Check if key is empty
     */
    public function is_empty_key(string $key): bool
    {
        $value = $this->get($key);
        return empty($value);
    }

    /**
     * Check if key is not empty
     */
    public function is_not_empty_key(string $key): bool
    {
        return !$this->is_empty_key($key);
    }

    /**
     * Map over items
     */
    public function map(callable $callback): self
    {
        $result = [];
        
        foreach ($this->items as $key => $value) {
            $result[$key] = $callback(
                is_array($value) ? new static($value) : $value,
                $key
            );
        }

        return new static($result);
    }

    /**
     * Filter items
     */
    public function filter(callable $callback = null): self
    {
        if ($callback === null) {
            return new static(array_filter($this->items));
        }

        $result = [];
        
        foreach ($this->items as $key => $value) {
            if ($callback(is_array($value) ? new static($value) : $value, $key)) {
                $result[$key] = $value;
            }
        }

        return new static($result);
    }

    /**
     * Walk over items (foreach without return)
     */
    public function walk(callable $callback): void
    {
        foreach ($this->items as $key => $value) {
            $callback(
                is_array($value) ? new static($value) : $value,
                $key
            );
        }
    }

    /**
     * Chunk the collection into chunks of the given size
     */
    public function chunk(int $size): self
    {
        $chunks = [];
        
        foreach (array_chunk($this->items, $size, true) as $chunk) {
            $chunks[] = $chunk;
        }

        return new static($chunks);
    }

    /**
     * Convert collection to self (for nested arrays)
     */
    public function to_self(): self
    {
        return $this;
    }

    /**
     * Convert to array
     */
    public function to_array(): self
    {
        return $this;
    }

    /**
     * Merge items
     */
    public function merge(array $items): self
    {
        return new static(array_merge($this->items, $items));
    }

    /**
     * Merge items into self
     */
    public function merge_self(array $items): self
    {
        $this->items = array_merge($this->items, $items);
        return $this;
    }

    /**
     * Get only specified keys
     */
    public function only(array $keys): self
    {
        return new static(array_intersect_key(
            $this->items,
            array_flip($keys)
        ));
    }

    /**
     * Get slice of items
     */
    public function slice(int $offset, int $length = null): self
    {
        return new static(array_slice($this->items, $offset, $length, true));
    }

    /**
     * Rekey the collection
     */
    public function rekey(string $key): self
    {
        $result = [];
        
        foreach ($this->items as $item) {
            if (is_array($item) && isset($item[$key])) {
                $result[$item[$key]] = $item;
            }
        }

        return new static($result);
    }

    /**
     * Check if collection is empty
     */
    public function is_empty(): bool
    {
        return empty($this->items);
    }

    /**
     * Check if collection is not empty
     */
    public function is_not_empty(): bool
    {
        return !$this->is_empty();
    }

    /**
     * Get first item
     */
    public function first(mixed $default = null): mixed
    {
        if (empty($this->items)) {
            return $default;
        }

        $value = reset($this->items);
        return is_array($value) ? new static($value) : $value;
    }

    /**
     * Get last item
     */
    public function last(mixed $default = null): mixed
    {
        if (empty($this->items)) {
            return $default;
        }

        $value = end($this->items);
        return is_array($value) ? new static($value) : $value;
    }

    /**
     * Count items
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Create keyed range
     */
    public function keyed_range(int $start, int $end): self
    {
        $result = [];
        for ($i = $start; $i <= $end; $i++) {
            $result[$i] = $i;
        }
        return new static($result);
    }
}
