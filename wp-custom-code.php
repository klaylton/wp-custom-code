<?php
/**
 * @wordpress-plugin
 * Plugin Name:       WP Custom Code
 * Plugin URI:        https://github.com/bboyguil/wp-custom-code
 * Description:       Add custom CSS and JavaScript to your pages and post in simple and handy ways.
 * Version:           1.0.0
 * Author:            Klaylton Fernando
 * Author URI:        https://profiles.wordpress.org/bboyguil/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-custom-code
 * Domain Path:       /languages
 */

    define( 'DIR_PLUGIN', plugin_dir_path( __FILE__ ) );

    require_once( DIR_PLUGIN . 'metabox.php' );
    require_once( DIR_PLUGIN . 'page-settings.php' );


class Css_Custom {

    private static $instance;

    private function __construct() {
        $metaboxes = new Meta_Box_CSS_JS_Custom();
        $settings = new Settings_CSS_JS_Custom();

        // Registro de Metabox
		add_action( 'add_meta_boxes', array( $metaboxes, 'add_meta_boxes' ) );
        add_action( 'save_post', array( $metaboxes, 'save_fields' ) );
        
	    // Hook into the admin menu
		add_action( 'admin_menu', array( $settings, 'create_plugin_settings_page' ) );
	    add_action( 'admin_init', array( $settings, 'setup_sections' ) );
        add_action( 'admin_init', array( $settings, 'setup_fields' ) );
        
        // Registro dos estilos e scripts
        add_action( 'admin_enqueue_scripts', array( $this, 'register_styles'));
        add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts'));

        // Inserir script no front-end
        add_action('wp_print_footer_scripts', array( $this, 'register_custom_css'), PHP_INT_MAX);
        add_action('wp_print_footer_scripts', array( $this, 'register_custom_js'), PHP_INT_MAX);

        // Codemirror
        add_action('admin_enqueue_scripts', array( $this, 'codemirror_enqueue_scripts' ) );

        // tradução
        add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
        
    }

    public static function getInstance() {

        if (self::$instance == NULL) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function register_styles(){
        wp_register_style( 'css-custom', plugin_dir_url(__FILE__).'css/style.css' );
        wp_enqueue_style( 'css-custom' );
    }

    public function register_scripts() {

        wp_enqueue_script( 'codemirror', plugin_dir_url( __FILE__ ) . 'js/script.js', array( 'jquery' ), '1.0.0', true );
    }

    // Codemirror
    public function codemirror_enqueue_scripts($hook) {
        $cm_settings['codeEditor'] = wp_enqueue_code_editor(array('type' => 'text/css'));
        wp_localize_script('jquery', 'cm_settings', $cm_settings);

        wp_enqueue_script('wp-theme-plugin-editor');
        wp_enqueue_style('wp-codemirror');
    }


    function code_custom_insert_script($script = null) {
        if ($script === null) return;
        $script = trim(stripslashes($script));
        if (!$script || $script == '') return;
        echo '<script type="text/javascript"';
        if (stripos($script, '://') == 4 || stripos($script, '://') == 5) {
        echo " src=\"{$script}\">";
        } else {
        echo ">\r\n{$script}\r\n";
        }
        echo "</script>\r\n";
    }

    function register_custom_js() {
        global $post;
        $exts = array('js_external', 'js');

        foreach ($exts as $ext) {
            if ($value = get_option('wp_custom_'.$ext, '')) {
                $this->code_custom_insert_script($value);
            }
        }

        if (isset($post)) {
            foreach ($exts as $ext) {
                if ($value = get_post_meta( $post->ID, 'wp_custom_'.$ext, true )) {
                    $this->code_custom_insert_script($value);
                }
            }
        }
    }

    // imprimi o CSS no Front-End
    function register_custom_css() {
        global $post;

        if ($value = get_option('wp_custom_css', '' )) {
            echo "<style>" . $value . "</style>";
        }

        if (isset($post)) {
            if ($value = get_post_meta( $post->ID, 'wp_custom_css', true )) {
                echo "<style>" . $value . "</style>";
            }
        }
    }

    public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'wp-custom-code',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages/'
        );

	}

}

Css_Custom::getInstance();