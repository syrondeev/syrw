<?php
/**
 * Plugin Name: SYRW Elementor Widgets
 * Description: مجموعه ویجت‌های حرفه‌ای برای Elementor
 * Plugin URI: https://github.com/syrw/syrw
 * Version: 1.0.0
 * Author: SYRW
 * Author URI: https://github.com/syrw
 * Text Domain: syrw-widgets
 * Domain Path: /languages
 * Elementor tested up to: 3.25.0
 * Elementor Pro tested up to: 3.25.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('SYRW_VERSION', '1.0.0');
define('SYRW_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SYRW_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main SYRW Elementor Widgets Class
 */
final class SYRW_Elementor_Widgets {

    /**
     * Minimum Elementor Version
     */
    const MINIMUM_ELEMENTOR_VERSION = '3.0.0';

    /**
     * Minimum PHP Version
     */
    const MINIMUM_PHP_VERSION = '7.4';

    /**
     * Instance
     */
    private static $_instance = null;

    /**
     * Instance
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        add_action('plugins_loaded', [$this, 'init']);
    }

    /**
     * Initialize the plugin
     */
    public function init() {
        // Check if Elementor installed and activated
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_main_plugin']);
            return;
        }

        // Check for required Elementor version
        if (!version_compare(ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_elementor_version']);
            return;
        }

        // Check for required PHP version
        if (version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '<')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_php_version']);
            return;
        }

        // Load autoloader
        require_once SYRW_PLUGIN_DIR . 'includes/autoloader.php';

        // Add Plugin actions
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
        add_action('elementor/elements/categories_registered', [$this, 'register_widget_categories']);
        
        // Register widget styles
        add_action('elementor/frontend/after_enqueue_styles', [$this, 'widget_styles']);
        
        // Register widget scripts
        add_action('elementor/frontend/after_register_scripts', [$this, 'widget_scripts']);

        // Load text domain
        add_action('init', [$this, 'i18n']);
    }

    /**
     * Load Textdomain
     */
    public function i18n() {
        load_plugin_textdomain('syrw-widgets', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * Admin notice - Missing Elementor
     */
    public function admin_notice_missing_main_plugin() {
        if (isset($_GET['activate'])) unset($_GET['activate']);

        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'syrw-widgets'),
            '<strong>' . esc_html__('SYRW Elementor Widgets', 'syrw-widgets') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'syrw-widgets') . '</strong>'
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    /**
     * Admin notice - Minimum Elementor version
     */
    public function admin_notice_minimum_elementor_version() {
        if (isset($_GET['activate'])) unset($_GET['activate']);

        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'syrw-widgets'),
            '<strong>' . esc_html__('SYRW Elementor Widgets', 'syrw-widgets') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'syrw-widgets') . '</strong>',
            self::MINIMUM_ELEMENTOR_VERSION
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    /**
     * Admin notice - Minimum PHP version
     */
    public function admin_notice_minimum_php_version() {
        if (isset($_GET['activate'])) unset($_GET['activate']);

        $message = sprintf(
            esc_html__('"%1$s" requires PHP version %2$s or greater.', 'syrw-widgets'),
            '<strong>' . esc_html__('SYRW Elementor Widgets', 'syrw-widgets') . '</strong>',
            self::MINIMUM_PHP_VERSION
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    /**
     * Register Widget Categories
     */
    public function register_widget_categories($elements_manager) {
        $elements_manager->add_category(
            'syrw-widgets',
            [
                'title' => esc_html__('SYRW Widgets', 'syrw-widgets'),
                'icon' => 'fa fa-plug',
            ]
        );
    }

    /**
     * Register Widgets
     */
    public function register_widgets($widgets_manager) {
        // Include widget files
        $widgets_dir = SYRW_PLUGIN_DIR . 'widgets/';
        
        // Array of widgets to load
        $widgets = [
            'post-card/module.php',
            // Add more widgets here
        ];

        foreach ($widgets as $widget_file) {
            $widget_path = $widgets_dir . $widget_file;
            if (file_exists($widget_path)) {
                require_once $widget_path;
                
                // Get class name from file
                $class_name = $this->get_widget_class_name($widget_file);
                if (class_exists($class_name)) {
                    $widgets_manager->register(new $class_name());
                }
            }
        }
    }

    /**
     * Get widget class name from file path
     */
    private function get_widget_class_name($file_path) {
        // Extract widget name from path (e.g., 'post-card/module.php' -> 'PostCard')
        $widget_name = dirname($file_path);
        $class_name = str_replace('-', '_', $widget_name);
        $class_name = str_replace(' ', '_', ucwords(str_replace('_', ' ', $class_name)));
        
        return 'SYRW\\Widgets\\' . $class_name . '\\Module';
    }

    /**
     * Register Widget Styles
     */
    public function widget_styles() {
        wp_register_style(
            'syrw-widgets-style',
            SYRW_PLUGIN_URL . 'assets/css/syrw-widgets.css',
            [],
            SYRW_VERSION
        );
    }

    /**
     * Register Widget Scripts
     */
    public function widget_scripts() {
        wp_register_script(
            'syrw-widgets-script',
            SYRW_PLUGIN_URL . 'assets/js/syrw-widgets.js',
            ['jquery'],
            SYRW_VERSION,
            true
        );
    }
}

SYRW_Elementor_Widgets::instance();
