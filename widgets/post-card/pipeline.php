<?php
/**
 * Post Card Widget - Pipeline
 * Process settings and prepare data for template
 */

namespace SYRW\Widgets\Post_Card;

use SYRW\Core\Collect;
use SYRW\Core\Elementor\Pipeline_Core;
use WP_Query;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Post Card Pipeline
 */
final class Pipeline extends Pipeline_Core
{
    /**
     * Get processed configs
     */
    public function get_configs(Collect $configs): Collect
    {
        $settings = $configs;

        // Process query
        $query_args = $this->process_query($settings);
        $configs->put('query_args', $query_args);

        // Execute query
        $query = new WP_Query($query_args);
        $configs->put('wp_query', $query);

        // Process layout settings
        $layout = $this->process_layout($settings);
        $configs->put('layout', $layout);

        // Process posts
        if ($query->have_posts()) {
            $posts = $this->process_posts($query, $settings);
            $configs->put('posts', $posts);
        } else {
            $configs->put('posts', new Collect([]));
        }

        // Process pagination
        $pagination = $this->process_pagination($query, $settings);
        $configs->put('pagination', $pagination);

        return $configs;
    }

    /**
     * Process query arguments
     */
    private function process_query(Collect $settings): array
    {
        $query_args = [
            'post_type' => $settings->get('query_post_type', 'post'),
            'posts_per_page' => $settings->get('query_posts_per_page', 6),
            'orderby' => $settings->get('query_orderby', 'date'),
            'order' => $settings->get('query_order', 'DESC'),
            'offset' => $settings->get('query_offset', 0),
            'post_status' => 'publish',
            'ignore_sticky_posts' => true,
        ];

        // Pagination
        if (get_query_var('paged')) {
            $query_args['paged'] = get_query_var('paged');
        } elseif (get_query_var('page')) {
            $query_args['paged'] = get_query_var('page');
        }

        // Exclude current post
        if ($settings->get('query_exclude_current') === 'yes' && is_single()) {
            $query_args['post__not_in'] = [get_the_ID()];
        }

        // Include specific posts
        $include_ids = $settings->get('query_include_ids', '');
        if (!empty($include_ids)) {
            $include_ids = array_map('trim', explode(',', $include_ids));
            $include_ids = array_filter($include_ids, 'is_numeric');
            if (!empty($include_ids)) {
                $query_args['post__in'] = $include_ids;
            }
        }

        // Exclude specific posts
        $exclude_ids = $settings->get('query_exclude_ids', '');
        if (!empty($exclude_ids)) {
            $exclude_ids = array_map('trim', explode(',', $exclude_ids));
            $exclude_ids = array_filter($exclude_ids, 'is_numeric');
            if (!empty($exclude_ids)) {
                $query_args['post__not_in'] = isset($query_args['post__not_in']) 
                    ? array_merge($query_args['post__not_in'], $exclude_ids)
                    : $exclude_ids;
            }
        }

        // Taxonomy filters
        $tax_query = [];

        // Categories
        $categories = $settings->get('query_categories', []);
        if (!empty($categories)) {
            $tax_query[] = [
                'taxonomy' => 'category',
                'field' => 'term_id',
                'terms' => $categories,
            ];
        }

        // Tags
        $tags = $settings->get('query_tags', []);
        if (!empty($tags)) {
            $tax_query[] = [
                'taxonomy' => 'post_tag',
                'field' => 'term_id',
                'terms' => $tags,
            ];
        }

        if (!empty($tax_query)) {
            $query_args['tax_query'] = $tax_query;
        }

        return $query_args;
    }

    /**
     * Process layout settings
     */
    private function process_layout(Collect $settings): Collect
    {
        return new Collect([
            'type' => $settings->get('layout_type', 'grid'),
            'columns' => $settings->get('layout_columns', '3'),
            'image' => [
                'visibility' => $settings->get('layout_image_visibility', 'visible'),
                'size' => $settings->get('layout_image_size_size', 'large'),
                'ratio' => $settings->get('layout_image_ratio', '16-9'),
            ],
            'title' => [
                'visibility' => $settings->get('layout_title_visibility', 'visible'),
                'tag' => $settings->get('layout_title_tag', 'h3'),
                'length' => $settings->get('layout_title_length', 0),
            ],
            'excerpt' => [
                'visibility' => $settings->get('layout_excerpt_visibility', 'visible'),
                'length' => $settings->get('layout_excerpt_length', 20),
            ],
            'meta' => [
                'show_author' => $settings->get('layout_meta_show_author', 'visible'),
                'show_date' => $settings->get('layout_meta_show_date', 'visible'),
                'show_categories' => $settings->get('layout_meta_show_categories', 'hidden'),
                'show_comments' => $settings->get('layout_meta_show_comments', 'hidden'),
                'separator' => $settings->get('layout_meta_separator', '•'),
            ],
            'readmore' => [
                'visibility' => $settings->get('layout_readmore_visibility', 'visible'),
                'text' => $settings->get('layout_readmore_text', __('Read More', 'syrw-widgets')),
            ],
        ]);
    }

    /**
     * Process posts
     */
    private function process_posts(WP_Query $query, Collect $settings): Collect
    {
        $posts = new Collect([]);
        $layout = $this->process_layout($settings);

        while ($query->have_posts()) {
            $query->the_post();

            $post_data = new Collect([
                'id' => get_the_ID(),
                'title' => $this->get_title($layout),
                'excerpt' => $this->get_excerpt($layout),
                'link' => get_permalink(),
                'image' => $this->get_image($layout),
                'meta' => $this->get_meta($layout),
                'readmore' => $layout->get('readmore'),
            ]);

            $posts->put(get_the_ID(), $post_data);
        }

        wp_reset_postdata();

        return $posts;
    }

    /**
     * Get title
     */
    private function get_title(Collect $layout): Collect
    {
        $title_config = $layout->get('title');
        $title = get_the_title();

        // Limit title length
        $length = $title_config->get('length', 0);
        if ($length > 0) {
            $words = explode(' ', $title);
            if (count($words) > $length) {
                $title = implode(' ', array_slice($words, 0, $length)) . '...';
            }
        }

        return new Collect([
            'text' => $title,
            'tag' => $title_config->get('tag', 'h3'),
            'visibility' => $title_config->get('visibility', 'visible'),
        ]);
    }

    /**
     * Get excerpt
     */
    private function get_excerpt(Collect $layout): Collect
    {
        $excerpt_config = $layout->get('excerpt');
        $length = $excerpt_config->get('length', 20);

        // Get excerpt
        $excerpt = '';
        if (has_excerpt()) {
            $excerpt = get_the_excerpt();
        } else {
            $content = get_the_content();
            $content = strip_shortcodes($content);
            $content = wp_strip_all_tags($content);
            $excerpt = $content;
        }

        // Limit excerpt length
        if ($length > 0) {
            $words = explode(' ', $excerpt);
            if (count($words) > $length) {
                $excerpt = implode(' ', array_slice($words, 0, $length)) . '...';
            }
        }

        return new Collect([
            'text' => $excerpt,
            'visibility' => $excerpt_config->get('visibility', 'visible'),
        ]);
    }

    /**
     * Get image
     */
    private function get_image(Collect $layout): Collect
    {
        $image_config = $layout->get('image');

        $image_data = new Collect([
            'visibility' => $image_config->get('visibility', 'visible'),
            'url' => '',
            'alt' => get_the_title(),
            'ratio' => $image_config->get('ratio', '16-9'),
        ]);

        if (has_post_thumbnail()) {
            $size = $image_config->get('size', 'large');
            $image_url = get_the_post_thumbnail_url(get_the_ID(), $size);
            $image_data->put('url', $image_url);
        }

        return $image_data;
    }

    /**
     * Get meta information
     */
    private function get_meta(Collect $layout): Collect
    {
        $meta_config = $layout->get('meta');
        $meta_items = new Collect([]);

        // Author
        if ($meta_config->get('show_author') === 'visible') {
            $meta_items->put('author', [
                'name' => get_the_author(),
                'url' => get_author_posts_url(get_the_author_meta('ID')),
            ]);
        }

        // Date
        if ($meta_config->get('show_date') === 'visible') {
            $meta_items->put('date', [
                'text' => get_the_date(),
                'timestamp' => get_the_time('U'),
            ]);
        }

        // Categories
        if ($meta_config->get('show_categories') === 'visible') {
            $categories = get_the_category();
            if (!empty($categories)) {
                $category_list = [];
                foreach ($categories as $category) {
                    $category_list[] = [
                        'name' => $category->name,
                        'url' => get_category_link($category->term_id),
                    ];
                }
                $meta_items->put('categories', $category_list);
            }
        }

        // Comments
        if ($meta_config->get('show_comments') === 'visible') {
            $comments_count = get_comments_number();
            $meta_items->put('comments', [
                'count' => $comments_count,
                'text' => sprintf(_n('%s Comment', '%s Comments', $comments_count, 'syrw-widgets'), $comments_count),
            ]);
        }

        $meta_items->put('separator', $meta_config->get('separator', '•'));

        return $meta_items;
    }

    /**
     * Process pagination
     */
    private function process_pagination(WP_Query $query, Collect $settings): Collect
    {
        $pagination_type = $settings->get('pagination_type', 'numbers');

        if ($pagination_type === 'none') {
            return new Collect(['type' => 'none']);
        }

        $pagination = new Collect([
            'type' => $pagination_type,
            'align' => $settings->get('pagination_align', 'center'),
            'total_pages' => $query->max_num_pages,
            'current_page' => max(1, get_query_var('paged', 1)),
        ]);

        // Generate pagination links
        if ($pagination_type === 'numbers' || $pagination_type === 'prev_next') {
            $links = paginate_links([
                'total' => $query->max_num_pages,
                'current' => $pagination->get('current_page'),
                'type' => 'array',
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
            ]);

            $pagination->put('links', $links ? new Collect($links) : new Collect([]));
        }

        return $pagination;
    }
}
