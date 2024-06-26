<?php
/*
Plugin Name: HLJS Enhanced Code Blocks
Description: Add syntax highlighting to code blocks
Version: 1.0.0
*/

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

if (!class_exists('HECB_Plugin')) {
    class HECB_Plugin
    {
        const DEFAULT_THEME = 'default';
        const SETTINGS_PAGE = 'general';
        const THEME_FIELD_ID = 'hecb_theme';

        // Static property to hold our singleton instance
        static $instance = false;

        // Static property to hold JSON settings
        static $settings = [];

        /**
         * This is our constructor
         *
         * @return void
         */
        private function __construct() {
            $contents = file_get_contents(__DIR__ . '/settings.json');
            self::$settings = json_decode($contents, true);

            // Admin Settings
            add_action('admin_init', [$this, 'settingsInit']);

            // Enqueue front-end styles and scripts
            add_action('wp_enqueue_scripts', [$this, 'enqueueFrontEndScripts']);
        }

        public function settingsInit() {
            add_settings_field(
                self::THEME_FIELD_ID,
                'HLJS Code Block Theme',
                [$this, 'themeSelectCallback'],
                self::SETTINGS_PAGE,
                'default',
                [
                    'label_for' => self::THEME_FIELD_ID,
                ]
            ); 
            
            register_setting(
                self::SETTINGS_PAGE,
                self::THEME_FIELD_ID,
                [
                    'type' => 'string',
                ]
            );
        }

        public function themeSelectCallback($args) {
            $theme = get_option(self::THEME_FIELD_ID, self::DEFAULT_THEME);
            $themeOptions = self::$settings['themes'] ?? [];
            ?>
            <select
            id="<?php echo esc_attr($args['label_for']); ?>"
            name="<?php echo esc_attr($args['label_for']); ?>"
            >
                <?php foreach ($themeOptions as $option) : ?>
                    <option value="<?php echo esc_attr($option); ?>" <?php echo selected($theme, $option, false); ?>>
                        <?php esc_html_e($option); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php
        }

        public function enqueueFrontEndScripts() {
            $theme = get_option(self::THEME_FIELD_ID, self::DEFAULT_THEME);

            wp_enqueue_style(
                'hecb_theme',
                plugins_url('/css/hljs-themes/' . $theme . '.min.css', __FILE__)
            );

            wp_enqueue_style(
                'hebc_main_css',
                plugins_url('/css/main.css', __FILE__),
                ['hecb_theme']
            );

            wp_enqueue_script(
                'hecb_hljs_js',
                plugins_url('/js/highlight.min.js', __FILE__),
                [],
                false,
                [
                    'in_footer' => true,
                ]
            );

            wp_enqueue_script(
                'hecb_main_js',
                plugins_url('/js/main.js', __FILE__),
                ['hecb_hljs_js'],
                false,
                [
                    'in_footer' => true,
                ]
            );
        }

        /**
         * If an instance exists, return it.
         * If not, create it and return it.
         * 
         * @return HECB_Plugin
         */
        public static function getInstance() {
            if (!self::$instance) {
                self::$instance = new self;
            }

            return self::$instance;
        }
    }

    // Instantiate plugin
    $HECB_Plugin = HECB_Plugin::getInstance();
}
