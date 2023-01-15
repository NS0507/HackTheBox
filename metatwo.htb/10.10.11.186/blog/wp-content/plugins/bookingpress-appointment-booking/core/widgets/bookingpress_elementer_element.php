<?php
namespace ElementorBookingpress;

class elementor_bookingpress_element {

	private static $_instance = null;


	public function __construct() {

		add_action( 'elementor/widgets/widgets_registered', array( $this, 'register_widgets' ) );
	}

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	private function include_widgets_files() {
		require_once __DIR__ . '/bookingpress_element_add.php';
	}

	public function register_widgets() {

		$this->include_widgets_files();
		// Register Widgets
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\bookingpress_form_shortcode() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\bookingpress_my_booking() );
	}

}

// Instantiate Plugin Class
elementor_bookingpress_element::instance();
