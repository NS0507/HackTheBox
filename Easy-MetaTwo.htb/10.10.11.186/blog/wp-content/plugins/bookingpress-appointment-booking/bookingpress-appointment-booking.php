<?php
/*
	Plugin Name: BookingPress Appointment Booking
	Description: Book appointments, create bookings, and pay online with bookingpress. Easily create appointments, manage time, and send out customized emails.
	Version: 1.0.10
	Plugin URI: https://www.bookingpressplugin.com/
	Author: Repute Infosystems
	Text Domain: bookingpress-appointment-booking
	Domain Path: /languages
	Author URI: https://www.bookingpressplugin.com/
 */

define( 'BOOKINGPRESS_DIR_NAME', dirname( plugin_basename( __FILE__ ) ) );
define( 'BOOKINGPRESS_DIR', WP_PLUGIN_DIR . '/' . BOOKINGPRESS_DIR_NAME );

require_once BOOKINGPRESS_DIR . '/autoload.php';
