<?php
/**
 * Post Card Widget - Module
 * Main widget class with all controls
 */

namespace SYRW\Widgets\Post_Card;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Image_Size;
use SYRW\Core\Elementor\Widget_Base;
use SYRW\Core\Collect;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Post Card Widget
 */
final class Module extends Widget_Base
{
    /**
     * Get widget name
     */
    public function get_name(): string
    {
        return 'syrw-post-card';
    }

    /**
     * Get widget title
     */
    public function get_title(): string
    {
        return esc_html__('Post Card', 'syrw-widgets');
    }

    /**
     * Get widget icon
     */
    public function get_icon(): string
    {
        return 'eicon-posts-grid';
    }

    /**
     * Get widget keywords
     */
    public function get_keywords(): array
    {
        return ['post', 'card', 'grid', 'blog', 'article', 'syrw'];
    }

    /**
     * Define controls
     */
    protected function define_controls(): void
    {
        // Register services
        $this->services->put('pipeline', function ($configs): Pipeline {
            return new Pipeline($configs);
        });

        $this->services->put('template', function (): Template {
            return new Template();
        });

        $this->content_section();
        $this->style_section();
    }

    /**
     * Content Section
     */
    private function content_section(): void
    {
        $this->content_query();
        $this->content_layout();
        $this->content_pagination();
    }

    /**
     * Query Settings
     */
    private function content_query(): void
    {
        $this->namer->prefix('query');

        $this->start_controls_section(
            $this->namer->get('section'),
            [
                'label' => esc_html__('Query Settings', 'syrw-widgets'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        // Post Type
        $this->add_control(
            $this->namer->get('post_type'),
            [
                'label' => esc_html__('Post Type', 'syrw-widgets'),
                'type' => Controls_Manager::SELECT,
                'options' => $this->get_post_types(),
                'default' => 'post',
            ]
        );

        // Posts Per Page
        $this->add_control(
            $this->namer->get('posts_per_page'),
            [
                'label' => esc_html__('Posts Per Page', 'syrw-widgets'),
                'type' => Controls_Manager::NUMBER,
                'default' => 6,
                'min' => 1,
                'max' => 100,
            ]
        );

        // Order By
        $this->add_control(
            $this->namer->get('orderby'),
            [
                'label' => esc_html__('Order By', 'syrw-widgets'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'date' => esc_html__('Date', 'syrw-widgets'),
                    'title' => esc_html__('Title', 'syrw-widgets'),
                    'modified' => esc_html__('Modified', 'syrw-widgets'),
                    'rand' => esc_html__('Random', 'syrw-widgets'),
                    'comment_count' => esc_html__('Comment Count', 'syrw-widgets'),
                    'menu_order' => esc_html__('Menu Order', 'syrw-widgets'),
                ],
                'default' => 'date',
            ]
        );

        // Order
        $this->add_control(
            $this->namer->get('order'),
            [
                'label' => esc_html__('Order', 'syrw-widgets'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'DESC' => esc_html__('Descending', 'syrw-widgets'),
                    'ASC' => esc_html__('Ascending', 'syrw-widgets'),
                ],
                'default' => 'DESC',
            ]
        );

        // Offset
        $this->add_control(
            $this->namer->get('offset'),
            [
                'label' => esc_html__('Offset', 'syrw-widgets'),
                'type' => Controls_Manager::NUMBER,
                'default' => 0,
                'min' => 0,
            ]
        );

        // Exclude Current Post
        $this->add_control(
            $this->namer->get('exclude_current'),
            [
                'label' => esc_html__('Exclude Current Post', 'syrw-widgets'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'no' => esc_html__('No', 'syrw-widgets'),
                    'yes' => esc_html__('Yes', 'syrw-widgets'),
                ],
                'default' => 'yes',
            ]
        );

        $this->add_control(
            $this->namer->get('hr_1'),
            [
                'type' => Controls_Manager::DIVIDER,
            ]
        );

        // Include Posts
        $this->add_control(
            $this->namer->get('include_ids'),
            [
                'label' => esc_html__('Include Posts by IDs', 'syrw-widgets'),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'placeholder' => esc_html__('Enter post IDs separated by comma', 'syrw-widgets'),
                'description' => esc_html__('Example: 12, 34, 56', 'syrw-widgets'),
            ]
        );

        // Exclude Posts
        $this->add_control(
            $this->namer->get('exclude_ids'),
            [
                'label' => esc_html__('Exclude Posts by IDs', 'syrw-widgets'),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'placeholder' => esc_html__('Enter post IDs separated by comma', 'syrw-widgets'),
            ]
        );

        $this->add_control(
            $this->namer->get('hr_2'),
            [
                'type' => Controls_Manager::DIVIDER,
            ]
        );

        // Filter by Categories
        $this->add_control(
            $this->namer->get('categories'),
            [
                'label' => esc_html__('Filter by Categories', 'syrw-widgets'),
                'type' => Controls_Manager::SELECT2,
                'label_block' => true,
                'multiple' => true,
                'options' => $this->get_taxonomies('category'),
                'condition' => [
                    $this->namer->get('post_type') => 'post',
                ],
            ]
        );

        // Filter by Tags
        $this->add_control(
            $this->namer->get('tags'),
            [
                'label' => esc_html__('Filter by Tags', 'syrw-widgets'),
                'type' => Controls_Manager::SELECT2,
                'label_block' => true,
                'multiple' => true,
                'options' => $this->get_taxonomies('post_tag'),
                'condition' => [
                    $this->namer->get('post_type') => 'post',
                ],
            ]
        );

        $this->namer->reset();

        $this->end_controls_section();
    }

    /**
     * Layout Settings
     */
    private function content_layout(): void
    {
        $this->namer->prefix('layout');

        $this->start_controls_section(
            $this->namer->get('section'),
            [
                'label' => esc_html__('Layout Settings', 'syrw-widgets'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        // Layout Type
        $this->add_control(
            $this->namer->get('type'),
            [
                'label' => esc_html__('Layout Type', 'syrw-widgets'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'grid' => esc_html__('Grid', 'syrw-widgets'),
                    'masonry' => esc_html__('Masonry', 'syrw-widgets'),
                    'list' => esc_html__('List', 'syrw-widgets'),
                ],
                'default' => 'grid',
            ]
        );

        // Columns
        $this->add_responsive_control(
            $this->namer->get('columns'),
            [
                'label' => esc_html__('Columns', 'syrw-widgets'),
                'type' => Controls_Manager::SELECT,
                'options' => $this->ranges(1, 6)->map(function ($num) {
                    return sprintf('%d', $num);
                })->all(),
                'default' => '3',
                'tablet_default' => '2',
                'mobile_default' => '1',
                'selectors' => [
                    '{{WRAPPER}} .syron-post-card__grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
                ],
                'condition' => [
                    $this->namer->get('type') => ['grid', 'masonry'],
                ],
            ]
        );

        // Column Gap
        $this->add_responsive_control(
            $this->namer->get('column_gap'),
            [
                'label' => esc_html__('Column Gap', 'syrw-widgets'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'size' => 20,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .syron-post-card__grid' => 'column-gap: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    $this->namer->get('type') => ['grid', 'masonry'],
                ],
            ]
        );

        // Row Gap
        $this->add_responsive_control(
            $this->namer->get('row_gap'),
            [
                'label' => esc_html__('Row Gap', 'syrw-widgets'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'size' => 20,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .syron-post-card__grid' => 'row-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            $this->namer->get('hr_3'),
            [
                'type' => Controls_Manager::DIVIDER,
            ]
        );

        // Image Settings
        $this->namer->group('image');

        $this->add_control(
            $this->namer->get('visibility'),
            [
                'label' => esc_html__('Show Featured Image', 'syrw-widgets'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'visible' => esc_html__('Visible', 'syrw-widgets'),
                    'hidden' => esc_html__('Hidden', 'syrw-widgets'),
                ],
                'default' => 'visible',
            ]
        );

        $this->add_group_control(
            Group_Control_Image_Size::get_type(),
            [
                'name' => $this->namer->get('size'),
                'default' => 'large',
                'condition' => [
                    $this->namer->get('visibility') => 'visible',
                ],
            ]
        );

        $this->add_control(
            $this->namer->get('ratio'),
            [
                'label' => esc_html__('Image Ratio', 'syrw-widgets'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '1-1' => esc_html__('1:1 Square', 'syrw-widgets'),
                    '4-3' => esc_html__('4:3 Standard', 'syrw-widgets'),
                    '16-9' => esc_html__('16:9 Widescreen', 'syrw-widgets'),
                    '21-9' => esc_html__('21:9 Ultrawide', 'syrw-widgets'),
                    'custom' => esc_html__('Custom', 'syrw-widgets'),
                ],
                'default' => '16-9',
                'condition' => [
                    $this->namer->get('visibility') => 'visible',
                ],
            ]
        );

        $this->add_control(
            $this->namer->get('hr_4'),
            [
                'type' => Controls_Manager::DIVIDER,
            ]
        );

        // Title Settings
        $this->namer->group('title');

        $this->add_control(
            $this->namer->get('visibility'),
            [
                'label' => esc_html__('Show Title', 'syrw-widgets'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'visible' => esc_html__('Visible', 'syrw-widgets'),
                    'hidden' => esc_html__('Hidden', 'syrw-widgets'),
                ],
                'default' => 'visible',
            ]
        );

        $this->add_control(
            $this->namer->get('tag'),
            [
                'label' => esc_html__('Title Tag', 'syrw-widgets'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                    'div' => 'DIV',
                    'span' => 'SPAN',
                    'p' => 'P',
                ],
                'default' => 'h3',
                'condition' => [
                    $this->namer->get('visibility') => 'visible',
                ],
            ]
        );

        $this->add_control(
            $this->namer->get('length'),
            [
                'label' => esc_html__('Title Length', 'syrw-widgets'),
                'type' => Controls_Manager::NUMBER,
                'default' => 0,
                'min' => 0,
                'description' => esc_html__('0 for no limit', 'syrw-widgets'),
                'condition' => [
                    $this->namer->get('visibility') => 'visible',
                ],
            ]
        );

        $this->add_control(
            $this->namer->get('hr_5'),
            [
                'type' => Controls_Manager::DIVIDER,
            ]
        );

        // Excerpt Settings
        $this->namer->group('excerpt');

        $this->add_control(
            $this->namer->get('visibility'),
            [
                'label' => esc_html__('Show Excerpt', 'syrw-widgets'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'visible' => esc_html__('Visible', 'syrw-widgets'),
                    'hidden' => esc_html__('Hidden', 'syrw-widgets'),
                ],
                'default' => 'visible',
            ]
        );

        $this->add_control(
            $this->namer->get('length'),
            [
                'label' => esc_html__('Excerpt Length', 'syrw-widgets'),
                'type' => Controls_Manager::NUMBER,
                'default' => 20,
                'min' => 0,
                'description' => esc_html__('Number of words', 'syrw-widgets'),
                'condition' => [
                    $this->namer->get('visibility') => 'visible',
                ],
            ]
        );

        $this->add_control(
            $this->namer->get('hr_6'),
            [
                'type' => Controls_Manager::DIVIDER,
            ]
        );

        // Meta Settings
        $this->namer->group('meta');

        $this->add_control(
            $this->namer->get('show_author'),
            [
                'label' => esc_html__('Show Author', 'syrw-widgets'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'visible' => esc_html__('Visible', 'syrw-widgets'),
                    'hidden' => esc_html__('Hidden', 'syrw-widgets'),
                ],
                'default' => 'visible',
            ]
        );

        $this->add_control(
            $this->namer->get('show_date'),
            [
                'label' => esc_html__('Show Date', 'syrw-widgets'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'visible' => esc_html__('Visible', 'syrw-widgets'),
                    'hidden' => esc_html__('Hidden', 'syrw-widgets'),
                ],
                'default' => 'visible',
            ]
        );

        $this->add_control(
            $this->namer->get('show_categories'),
            [
                'label' => esc_html__('Show Categories', 'syrw-widgets'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'visible' => esc_html__('Visible', 'syrw-widgets'),
                    'hidden' => esc_html__('Hidden', 'syrw-widgets'),
                ],
                'default' => 'hidden',
            ]
        );

        $this->add_control(
            $this->namer->get('show_comments'),
            [
                'label' => esc_html__('Show Comments Count', 'syrw-widgets'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'visible' => esc_html__('Visible', 'syrw-widgets'),
                    'hidden' => esc_html__('Hidden', 'syrw-widgets'),
                ],
                'default' => 'hidden',
            ]
        );

        $this->add_control(
            $this->namer->get('separator'),
            [
                'label' => esc_html__('Meta Separator', 'syrw-widgets'),
                'type' => Controls_Manager::TEXT,
                'default' => '•',
                'label_block' => false,
            ]
        );

        $this->add_control(
            $this->namer->get('hr_7'),
            [
                'type' => Controls_Manager::DIVIDER,
            ]
        );

        // Read More Settings
        $this->namer->group('readmore');

        $this->add_control(
            $this->namer->get('visibility'),
            [
                'label' => esc_html__('Show Read More', 'syrw-widgets'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'visible' => esc_html__('Visible', 'syrw-widgets'),
                    'hidden' => esc_html__('Hidden', 'syrw-widgets'),
                ],
                'default' => 'visible',
            ]
        );

        $this->add_control(
            $this->namer->get('text'),
            [
                'label' => esc_html__('Read More Text', 'syrw-widgets'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Read More', 'syrw-widgets'),
                'label_block' => false,
                'condition' => [
                    $this->namer->get('visibility') => 'visible',
                ],
            ]
        );

        $this->namer->reset();

        $this->end_controls_section();
    }

    /**
     * Pagination Settings
     */
    private function content_pagination(): void
    {
        $this->namer->prefix('pagination');

        $this->start_controls_section(
            $this->namer->get('section'),
            [
                'label' => esc_html__('Pagination Settings', 'syrw-widgets'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            $this->namer->get('type'),
            [
                'label' => esc_html__('Pagination Type', 'syrw-widgets'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'none' => esc_html__('None', 'syrw-widgets'),
                    'numbers' => esc_html__('Numbers', 'syrw-widgets'),
                    'prev_next' => esc_html__('Previous/Next', 'syrw-widgets'),
                    'load_more' => esc_html__('Load More Button', 'syrw-widgets'),
                ],
                'default' => 'numbers',
            ]
        );

        $this->add_control(
            $this->namer->get('align'),
            [
                'label' => esc_html__('Pagination Align', 'syrw-widgets'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'syrw-widgets'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'syrw-widgets'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'syrw-widgets'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .syron-post-card__pagination' => 'text-align: {{VALUE}};',
                ],
                'condition' => [
                    $this->namer->get('type!') => 'none',
                ],
            ]
        );

        $this->namer->reset();

        $this->end_controls_section();
    }

    /**
     * Style Section
     */
    private function style_section(): void
    {
        $this->style_card();
        $this->style_image();
        $this->style_title();
        $this->style_excerpt();
        $this->style_meta();
    }

    /**
     * Card Style
     */
    private function style_card(): void
    {
        $this->namer->prefix('card');

        $this->start_controls_section(
            $this->namer->get('style_section'),
            [
                'label' => esc_html__('Card Style', 'syrw-widgets'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            $this->namer->get('background'),
            [
                'label' => esc_html__('Background Color', 'syrw-widgets'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .syron-post-card__item' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            $this->namer->get('padding'),
            [
                'label' => esc_html__('Padding', 'syrw-widgets'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', 'rem', '%'],
                'selectors' => [
                    '{{WRAPPER}} .syron-post-card__item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => $this->namer->get('border'),
                'selector' => '{{WRAPPER}} .syron-post-card__item',
            ]
        );

        $this->add_responsive_control(
            $this->namer->get('border_radius'),
            [
                'label' => esc_html__('Border Radius', 'syrw-widgets'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .syron-post-card__item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => $this->namer->get('box_shadow'),
                'selector' => '{{WRAPPER}} .syron-post-card__item',
            ]
        );

        $this->namer->reset();

        $this->end_controls_section();
    }

    /**
     * Image Style
     */
    private function style_image(): void
    {
        $this->namer->prefix('image');

        $this->start_controls_section(
            $this->namer->get('style_section'),
            [
                'label' => esc_html__('Image Style', 'syrw-widgets'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            $this->namer->get('border_radius'),
            [
                'label' => esc_html__('Border Radius', 'syrw-widgets'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .syron-post-card__image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->namer->reset();

        $this->end_controls_section();
    }

    /**
     * Title Style
     */
    private function style_title(): void
    {
        $this->namer->prefix('title');

        $this->start_controls_section(
            $this->namer->get('style_section'),
            [
                'label' => esc_html__('Title Style', 'syrw-widgets'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            $this->namer->get('color'),
            [
                'label' => esc_html__('Text Color', 'syrw-widgets'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .syron-post-card__title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            $this->namer->get('hover_color'),
            [
                'label' => esc_html__('Hover Color', 'syrw-widgets'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .syron-post-card__title:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => $this->namer->get('typography'),
                'selector' => '{{WRAPPER}} .syron-post-card__title',
            ]
        );

        $this->add_responsive_control(
            $this->namer->get('spacing'),
            [
                'label' => esc_html__('Spacing', 'syrw-widgets'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .syron-post-card__title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->namer->reset();

        $this->end_controls_section();
    }

    /**
     * Excerpt Style
     */
    private function style_excerpt(): void
    {
        $this->namer->prefix('excerpt');

        $this->start_controls_section(
            $this->namer->get('style_section'),
            [
                'label' => esc_html__('Excerpt Style', 'syrw-widgets'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            $this->namer->get('color'),
            [
                'label' => esc_html__('Text Color', 'syrw-widgets'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .syron-post-card__excerpt' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => $this->namer->get('typography'),
                'selector' => '{{WRAPPER}} .syron-post-card__excerpt',
            ]
        );

        $this->add_responsive_control(
            $this->namer->get('spacing'),
            [
                'label' => esc_html__('Spacing', 'syrw-widgets'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .syron-post-card__excerpt' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->namer->reset();

        $this->end_controls_section();
    }

    /**
     * Meta Style
     */
    private function style_meta(): void
    {
        $this->namer->prefix('meta');

        $this->start_controls_section(
            $this->namer->get('style_section'),
            [
                'label' => esc_html__('Meta Style', 'syrw-widgets'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            $this->namer->get('color'),
            [
                'label' => esc_html__('Text Color', 'syrw-widgets'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .syron-post-card__meta' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => $this->namer->get('typography'),
                'selector' => '{{WRAPPER}} .syron-post-card__meta',
            ]
        );

        $this->namer->reset();

        $this->end_controls_section();
    }

    /**
     * Get post types
     */
    private function get_post_types(): array
    {
        $post_types = get_post_types(['public' => true], 'objects');
        $options = [];

        foreach ($post_types as $post_type) {
            $options[$post_type->name] = $post_type->label;
        }

        return $options;
    }

    /**
     * Get taxonomies
     */
    private function get_taxonomies(string $taxonomy): array
    {
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ]);

        $options = [];

        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                $options[$term->term_id] = $term->name;
            }
        }

        return $options;
    }
}
