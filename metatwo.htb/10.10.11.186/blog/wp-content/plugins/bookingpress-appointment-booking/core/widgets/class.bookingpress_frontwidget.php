<?php

if ( ! class_exists( 'BookingpressFormWidget' ) ) {
	class BookingpressFormWidget extends WP_Widget {

		function __construct() {
			parent::__construct(
				'bookingpress_frontend_widget',
				__( 'BookingPress step-by-step wizard form', 'bookingpress-appointment-booking' ),
				array( 'description' => __( 'Display frontend booking form for book appointments', 'bookingpress-appointment-booking' ) )
			);
		}

		public function widget( $args, $instance ) {
			if(!is_admin()){
				global $BookingPress;
				$BookingPress->set_front_css(1);
				$BookingPress->set_front_js(1);
			}
			echo $args['before_widget'];
			echo "<div class='bpa-frontend-main-container--widget'>";
			echo do_shortcode( '[bookingpress_form]' );
			echo '</div>';
			echo $args['after_widget'];
		}
		public function form( $instance ) {
		}
	}
	if ( class_exists( 'WP_Widget' ) ) {
		function Bookingpress_register_form_widgets() {
			 register_widget( 'BookingpressFormWidget' );
		}
		add_action( 'widgets_init', 'Bookingpress_register_form_widgets' );
	}
}
if ( ! class_exists( 'BookingpressMybookingWidget' ) ) {
	class BookingpressMybookingWidget extends WP_Widget {

		function __construct() {
			parent::__construct(
				'bookingpress_my_booking_widget',
				__( 'Customer panel - My Appointments', 'bookingpress-appointment-booking' ),
				array( 'description' => __( 'Display booked appointments', 'bookingpress-appointment-booking' ) )
			);
		}
		public function widget( $args, $instance ) {
			if(!is_admin()){
				global $BookingPress;
				$BookingPress->set_front_css(1);
				$BookingPress->set_front_js(1);
			}
			echo $args['before_widget'];
			echo "<div class='bpa-frontend-main-container--widget'>";
			echo do_shortcode( '[bookingpress_my_appointments]' );
			echo '</div>';
			echo $args['after_widget'];
		}
		public function form( $instance ) {

		}
	}
	if ( class_exists( 'WP_Widget' ) ) {
		function Bookingpress_register_my_booking_widgets() {
			register_widget( 'BookingpressMybookingWidget' );
		}
		add_action( 'widgets_init', 'Bookingpress_register_my_booking_widgets' );
	}
}
