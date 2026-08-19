<?php
/**
 * Plugin Name: Zorgkosten Calculator for Elementor
 * Description: Custom Elementor widget: multi-step healthcare cost calculator (insurer → policy → deductible → estimate). Every step, option, amount and text is editable from the Elementor editor.
 * Version:     3.0.0
 * Author:      Jakir
 * Text Domain: zorgkosten-calculator
 * Requires Plugins: elementor
 * Elementor tested up to: 4.2.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'ZKC_VERSION', '3.0.0' );
define( 'ZKC_FILE', __FILE__ );
define( 'ZKC_PATH', plugin_dir_path( __FILE__ ) );
define( 'ZKC_URL', plugin_dir_url( __FILE__ ) );

final class ZKC_Plugin {

	const MIN_ELEMENTOR_VERSION = '3.5.0';
	const MIN_PHP_VERSION       = '7.4';

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'plugins_loaded', [ $this, 'init' ] );
	}

	public function init() {

		// Check if Elementor is installed and activated.
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_elementor' ] );
			return;
		}

		if ( ! version_compare( ELEMENTOR_VERSION, self::MIN_ELEMENTOR_VERSION, '>=' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
			return;
		}

		if ( version_compare( PHP_VERSION, self::MIN_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
			return;
		}

		add_action( 'elementor/elements/categories_registered', [ $this, 'register_category' ] );
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );

		// Register assets so the widget can declare them as dependencies.
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
		add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'register_assets' ] );
	}

	public function register_assets() {
		wp_register_style(
			'zkc-calculator',
			ZKC_URL . 'assets/css/calculator.css',
			[],
			ZKC_VERSION
		);
		wp_register_script(
			'zkc-calculator',
			ZKC_URL . 'assets/js/calculator.js',
			[],
			ZKC_VERSION,
			true
		);
	}

	public function register_category( $elements_manager ) {
		$elements_manager->add_category(
			'zorgkosten',
			[
				'title' => esc_html__( 'Zorgkosten', 'zorgkosten-calculator' ),
				'icon'  => 'fa fa-calculator',
			]
		);
	}

	public function register_widgets( $widgets_manager ) {
		require_once ZKC_PATH . 'includes/widget-cost-calculator.php';
		$widgets_manager->register( new \ZKC_Cost_Calculator_Widget() );
	}

	public function admin_notice_missing_elementor() {
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
		printf(
			'<div class="notice notice-warning is-dismissible"><p>%s</p></div>',
			esc_html__( '"Zorgkosten Calculator for Elementor" requires Elementor to be installed and activated.', 'zorgkosten-calculator' )
		);
	}

	public function admin_notice_minimum_elementor_version() {
		printf(
			'<div class="notice notice-warning is-dismissible"><p>%s</p></div>',
			sprintf(
				/* translators: %s: minimum Elementor version */
				esc_html__( '"Zorgkosten Calculator for Elementor" requires Elementor version %s or greater.', 'zorgkosten-calculator' ),
				self::MIN_ELEMENTOR_VERSION
			)
		);
	}

	public function admin_notice_minimum_php_version() {
		printf(
			'<div class="notice notice-warning is-dismissible"><p>%s</p></div>',
			sprintf(
				/* translators: %s: minimum PHP version */
				esc_html__( '"Zorgkosten Calculator for Elementor" requires PHP version %s or greater.', 'zorgkosten-calculator' ),
				self::MIN_PHP_VERSION
			)
		);
	}
}

ZKC_Plugin::instance();
