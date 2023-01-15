<?php

namespace ElementorBookingpress\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'bookingpress_form_shortcode' ) ) {

	class bookingpress_form_shortcode extends Widget_Base {

		public function get_name() {
			return 'BookingPress step-by-step wizard form';
		}
		public function get_title() {
			return __( 'BookingPress step-by-step wizard form', 'bookingpress-appointment-booking' ) . '<style>
        .bookingpress_element_icon{
            display: inline-block;
            width: 35px;
            height: 24px;
            background-image: url(' . BOOKINGPRESS_IMAGES_URL . '/bookingpress_menu_icon.png);
            background-repeat: no-repeat;
            background-position: bottom;
        }
        </style>
        ';
		}
		public function get_icon() {
			return 'bookingpress_element_icon';
		}
		public function get_categories() {
			return array( 'general' );
		}
		protected function _register_controls() {

			$this->end_controls_section();

		}
		protected function render() {

			echo '[bookingpress_form]';

		}

	}
}
if ( ! class_exists( 'bookingpress_my_booking' ) ) {

	class bookingpress_my_booking extends Widget_Base {

		public function get_name() {
			return 'Customer panel - My Appointments';
		}
		public function get_title() {
			return __( 'Customer panel - My Appointments', 'bookingpress-appointment-booking' ) . '<style>
        .bookingpress_element_icon{
            display: inline-block;
            width: 35px;
            height: 24px;
            background-image: url(' . BOOKINGPRESS_IMAGES_URL . '/bookingpress_menu_icon.png);
            background-repeat: no-repeat;
            background-position: bottom;
        }
        </style>
        ';
		}
		public function get_icon() {
			return 'bookingpress_element_icon';
		}
		public function get_categories() {
			return array( 'general' );
		}
		protected function _register_controls() {
			$this->end_controls_section();
		}
		protected function render() {
			echo '[bookingpress_my_appointments]';
		}

	}
}

