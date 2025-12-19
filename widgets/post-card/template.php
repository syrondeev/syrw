<?php
/**
 * Post Card Widget - Template
 * Render HTML output
 */

namespace SYRW\Widgets\Post_Card;

use SYRW\Core\Collect;
use SYRW\Core\Elementor\Template_Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Post Card Template
 */
final class Template extends Template_Core
{
    /**
     * Render template
     */
    public function render(Collect $configs, $module): void
    {
        $posts = $configs->get('posts', new Collect([]));
        $layout = $configs->get('layout', new Collect([]));
        $pagination = $configs->get('pagination', new Collect([]));

        // Wrapper
        $el_wrap = $this->element->create('div', [
            'class' => [
                'syron-post-card',
                'elementor-widget-container',
            ],
        ]);

        $el_wrap->render(function () use ($posts, $layout, $pagination): void {

            // Check if we have posts
            if ($posts->is_empty()) {
                $this->render_no_posts();
                return;
            }

            // Grid Container
            $el_grid = $this->element->create('div', [
                'class' => [
                    'syron-post-card__grid',
                    sprintf('syron-post-card__grid--%s', $layout->get('type', 'grid')),
                ],
            ]);

            $el_grid->render(function () use ($posts, $layout): void {

                // Loop through posts
                $posts->walk(function ($post) use ($layout): void {
                    $this->render_post_item($post, $layout);
                });

            });

            // Render pagination
            if ($pagination->get('type') !== 'none') {
                $this->render_pagination($pagination);
            }

        });
    }

    /**
     * Render single post item
     */
    private function render_post_item(Collect $post, Collect $layout): void
    {
        $el_item = $this->element->create('article', [
            'class' => [
                'syron-post-card__item',
            ],
        ]);

        $el_item->render(function () use ($post, $layout): void {

            // Image
            $image = $post->get('image');
            if ($image->get('visibility') === 'visible' && !empty($image->get('url'))) {
                $this->render_image($image, $post);
            }

            // Content wrapper
            $el_content = $this->element->create('div', [
                'class' => ['syron-post-card__content'],
            ]);

            $el_content->render(function () use ($post, $layout): void {

                // Meta (before title)
                $meta = $post->get('meta');
                if (!$meta->is_empty()) {
                    $this->render_meta($meta);
                }

                // Title
                $title = $post->get('title');
                if ($title->get('visibility') === 'visible') {
                    $this->render_title($title, $post->get('link'));
                }

                // Excerpt
                $excerpt = $post->get('excerpt');
                if ($excerpt->get('visibility') === 'visible' && !empty($excerpt->get('text'))) {
                    $this->render_excerpt($excerpt);
                }

                // Read More
                $readmore = $post->get('readmore');
                if ($readmore->get('visibility') === 'visible') {
                    $this->render_readmore($readmore, $post->get('link'));
                }

            });

        });
    }

    /**
     * Render post image
     */
    private function render_image(Collect $image, Collect $post): void
    {
        $ratio = $image->get('ratio', '16-9');
        $ratio_class = sprintf('syron-post-card__image--ratio-%s', $ratio);

        $el_image = $this->element->create('div', [
            'class' => [
                'syron-post-card__image',
                $ratio_class,
            ],
        ]);

        $el_image->render(function () use ($image, $post): void {

            $el_link = $this->element->create('a', [
                'href' => $post->get('link'),
                'class' => ['syron-post-card__image-link'],
            ]);

            $el_link->render(function () use ($image): void {

                $el_img = $this->element->create('img', [
                    'src' => $image->get('url'),
                    'alt' => $image->get('alt'),
                    'class' => ['syron-post-card__image-img'],
                    'loading' => 'lazy',
                ]);

                $el_img->render();

            });

        });
    }

    /**
     * Render post title
     */
    private function render_title(Collect $title, string $link): void
    {
        $tag = $title->get('tag', 'h3');

        $el_title = $this->element->create($tag, [
            'class' => ['syron-post-card__title'],
        ]);

        $el_title->render(function () use ($title, $link): void {

            $el_link = $this->element->create('a', [
                'href' => $link,
                'class' => ['syron-post-card__title-link'],
            ]);

            $el_link->render(function () use ($title): void {
                echo esc_html($title->get('text'));
            });

        });
    }

    /**
     * Render post excerpt
     */
    private function render_excerpt(Collect $excerpt): void
    {
        $el_excerpt = $this->element->create('div', [
            'class' => ['syron-post-card__excerpt'],
        ]);

        $el_excerpt->render(function () use ($excerpt): void {
            echo esc_html($excerpt->get('text'));
        });
    }

    /**
     * Render post meta
     */
    private function render_meta(Collect $meta): void
    {
        $el_meta = $this->element->create('div', [
            'class' => ['syron-post-card__meta'],
        ]);

        $el_meta->render(function () use ($meta): void {

            $items = [];
            $separator = $meta->get('separator', '•');

            // Author
            if ($meta->has('author')) {
                $author = $meta->get('author');
                $items[] = sprintf(
                    '<a href="%s" class="syron-post-card__meta-author">%s</a>',
                    esc_url($author->get('url')),
                    esc_html($author->get('name'))
                );
            }

            // Date
            if ($meta->has('date')) {
                $date = $meta->get('date');
                $items[] = sprintf(
                    '<span class="syron-post-card__meta-date">%s</span>',
                    esc_html($date->get('text'))
                );
            }

            // Categories
            if ($meta->has('categories')) {
                $categories = $meta->get('categories');
                $category_links = [];
                foreach ($categories as $category) {
                    $cat = new Collect($category);
                    $category_links[] = sprintf(
                        '<a href="%s">%s</a>',
                        esc_url($cat->get('url')),
                        esc_html($cat->get('name'))
                    );
                }
                if (!empty($category_links)) {
                    $items[] = sprintf(
                        '<span class="syron-post-card__meta-categories">%s</span>',
                        implode(', ', $category_links)
                    );
                }
            }

            // Comments
            if ($meta->has('comments')) {
                $comments = $meta->get('comments');
                $items[] = sprintf(
                    '<span class="syron-post-card__meta-comments">%s</span>',
                    esc_html($comments->get('text'))
                );
            }

            // Output with separator
            echo implode(sprintf(' <span class="syron-post-card__meta-separator">%s</span> ', esc_html($separator)), $items);

        });
    }

    /**
     * Render read more button
     */
    private function render_readmore(Collect $readmore, string $link): void
    {
        $el_readmore = $this->element->create('div', [
            'class' => ['syron-post-card__readmore'],
        ]);

        $el_readmore->render(function () use ($readmore, $link): void {

            $el_link = $this->element->create('a', [
                'href' => $link,
                'class' => ['syron-post-card__readmore-link'],
            ]);

            $el_link->render(function () use ($readmore): void {
                echo esc_html($readmore->get('text'));
            });

        });
    }

    /**
     * Render pagination
     */
    private function render_pagination(Collect $pagination): void
    {
        $align = $pagination->get('align', 'center');

        $el_pagination = $this->element->create('div', [
            'class' => [
                'syron-post-card__pagination',
                sprintf('syron-post-card__pagination--align-%s', $align),
            ],
        ]);

        $el_pagination->render(function () use ($pagination): void {

            $type = $pagination->get('type');
            $links = $pagination->get('links', new Collect([]));

            if ($type === 'numbers' || $type === 'prev_next') {

                $el_nav = $this->element->create('nav', [
                    'class' => ['syron-post-card__pagination-nav'],
                    'aria-label' => 'Pagination',
                ]);

                $el_nav->render(function () use ($links): void {

                    $links->walk(function ($link): void {
                        echo $link;
                    });

                });

            } elseif ($type === 'load_more') {

                $el_button = $this->element->create('button', [
                    'class' => ['syron-post-card__load-more'],
                    'type' => 'button',
                ]);

                $el_button->render(function (): void {
                    echo esc_html__('Load More', 'syrw-widgets');
                });

            }

        });
    }

    /**
     * Render no posts message
     */
    private function render_no_posts(): void
    {
        $el_message = $this->element->create('div', [
            'class' => ['syron-post-card__no-posts'],
        ]);

        $el_message->render(function (): void {
            echo esc_html__('No posts found', 'syrw-widgets');
        });
    }
}
