<?php
if ( is_ssl() ) {
	define( 'BOOKINGPRESS_URL', str_replace( 'http://', 'https://', WP_PLUGIN_URL . '/' . BOOKINGPRESS_DIR_NAME ) );
	define( 'BOOKINGPRESS_HOME_URL', home_url( '', 'https' ) );
} else {
	define( 'BOOKINGPRESS_URL', WP_PLUGIN_URL . '/' . BOOKINGPRESS_DIR_NAME );
	define( 'BOOKINGPRESS_HOME_URL', home_url() );
}

define( 'BOOKINGPRESS_MENU_URL', admin_url() . 'admin.php?page=bookingpress' );

define( 'BOOKINGPRESS_CORE_DIR', BOOKINGPRESS_DIR . '/core' );

define( 'BOOKINGPRESS_CLASSES_DIR', BOOKINGPRESS_DIR . '/core/classes' );
define( 'BOOKINGPRESS_CLASSES_URL', BOOKINGPRESS_URL . '/core/classes' );

define( 'BOOKINGPRESS_WIDGET_DIR', BOOKINGPRESS_DIR . '/core/widgets' );
define( 'BOOKINGPRESS_WIDGET_URL', BOOKINGPRESS_URL . '/core/widgets' );

define( 'BOOKINGPRESS_IMAGES_DIR', BOOKINGPRESS_DIR . '/images' );
define( 'BOOKINGPRESS_IMAGES_URL', BOOKINGPRESS_URL . '/images' );

define( 'BOOKINGPRESS_LIBRARY_DIR', BOOKINGPRESS_DIR . '/lib' );
define( 'BOOKINGPRESS_LIBRARY_URL', BOOKINGPRESS_URL . '/lib' );

define( 'BOOKINGPRESS_INC_DIR', BOOKINGPRESS_DIR . '/inc' );

define( 'BOOKINGPRESS_VIEWS_DIR', BOOKINGPRESS_DIR . '/core/views' );
define( 'BOOKINGPRESS_VIEWS_URL', BOOKINGPRESS_URL . '/core/views' );


if ( ! defined( 'FS_METHOD' ) ) {
	@define( 'FS_METHOD', 'direct' );
}

$bookingpress_wpupload_dir = wp_upload_dir();
$bookingpress_upload_dir   = $bookingpress_wpupload_dir['basedir'] . '/bookingpress';
$bookingpress_upload_url   = $bookingpress_wpupload_dir['baseurl'] . '/bookingpress';

if ( is_ssl() ) {
	$bookingpress_upload_url = str_replace( 'http://', 'https://', $bookingpress_upload_url );
}

$bookingpress_tmp_images_dir = $bookingpress_upload_dir . '/tmp_images';
$bookingpress_tmp_images_url = $bookingpress_upload_url . '/tmp_images';
if ( ! is_dir( $bookingpress_upload_dir ) ) {
	wp_mkdir_p( $bookingpress_upload_dir );
}
if ( ! is_dir( $bookingpress_tmp_images_dir ) ) {
	wp_mkdir_p( $bookingpress_tmp_images_dir );
}
define( 'BOOKINGPRESS_UPLOAD_DIR', $bookingpress_upload_dir );
define( 'BOOKINGPRESS_UPLOAD_URL', $bookingpress_upload_url );
define( 'BOOKINGPRESS_TMP_IMAGES_DIR', $bookingpress_tmp_images_dir );
define( 'BOOKINGPRESS_TMP_IMAGES_URL', $bookingpress_tmp_images_url );

$bookingpress_upload_css_dir = $bookingpress_wpupload_dir['basedir'] . '/bookingpress/css';
$bookingpress_upload_css_url = $bookingpress_wpupload_dir['baseurl'] . '/bookingpress/css';
if ( ! is_dir( $bookingpress_upload_css_dir ) ) {
	wp_mkdir_p( $bookingpress_upload_css_dir );
}
define( 'BOOKINGPRESS_UPLOAD_CSS_DIR', $bookingpress_upload_css_dir );
define( 'BOOKINGPRESS_UPLOAD_CSS_URL', $bookingpress_upload_css_url );

global $bookingpress_user_status, $bookingpress_user_type;
$bookingpress_user_status = array(
	'1' => esc_html__( 'Active', 'bookingpress-appointment-booking' ),
	'2' => esc_html__( 'Inactive', 'bookingpress-appointment-booking' ),
	'3' => esc_html__( 'Pending', 'bookingpress-appointment-booking' ),
	'4' => esc_html__( 'Terminated', 'bookingpress-appointment-booking' ),
);

$bookingpress_user_type = array(
	'1' => esc_html__( 'Employee', 'bookingpress-appointment-booking' ),
	'2' => esc_html__( 'Customer', 'bookingpress-appointment-booking' ),
);

global $bookingpress_version;
$bookingpress_version = '1.0.10';
define( 'BOOKINGPRESS_VERSION', $bookingpress_version );

global $bookingpress_ajaxurl;
$bookingpress_ajaxurl = admin_url( 'admin-ajax.php' );

/**
 * Plugin Main Class
 */
if ( file_exists( BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress.php' ) ) {
	require_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress.php';
}

if ( file_exists( BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_fileupload_class.php' ) ) {
	require_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_fileupload_class.php';
}

if ( file_exists( BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_global_options.php' ) ) {
	require_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_global_options.php';
}

if ( file_exists( BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_services.php' ) ) {
	require_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_services.php';
}

if ( file_exists( BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_service_categories.php' ) ) {
	require_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_service_categories.php';
}

if ( file_exists( BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_settings.php' ) ) {
	require_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_settings.php';
}

if ( file_exists( BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_notifications.php' ) ) {
	require_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_notifications.php';
}

if ( file_exists( BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_customers.php' ) ) {
	require_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_customers.php';
}

if ( file_exists( BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_payment.php' ) ) {
	require_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_payment.php';
}

if ( file_exists( BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_appointment.php' ) ) {
	require_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_appointment.php';
}

if ( file_exists( BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_calendar.php' ) ) {
	require_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_calendar.php';
}

if ( file_exists( BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_dashboard.php' ) ) {
	require_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_dashboard.php';
}

if ( file_exists( BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_payment_gateways.php' ) ) {
	require_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_payment_gateways.php';
}

if ( file_exists( BOOKINGPRESS_CLASSES_DIR . '/payment_gateways/class.bookingpress_paypal.php' ) ) {
	require_once BOOKINGPRESS_CLASSES_DIR . '/payment_gateways/class.bookingpress_paypal.php';
}

if ( file_exists( BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_email_notifications.php' ) ) {
	require_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_email_notifications.php';
}

if ( file_exists( BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_customize.php' ) ) {
	require_once BOOKINGPRESS_CLASSES_DIR . '/class.bookingpress_customize.php';
}



// Frontend files
if ( file_exists( BOOKINGPRESS_CLASSES_DIR . '/frontend/class.bookingpress_appointment_bookings.php' ) ) {
	require_once BOOKINGPRESS_CLASSES_DIR . '/frontend/class.bookingpress_appointment_bookings.php';
}

if ( file_exists( BOOKINGPRESS_CLASSES_DIR . '/frontend/class.bookingpress_spam_protection.php' ) ) {
	require_once BOOKINGPRESS_CLASSES_DIR . '/frontend/class.bookingpress_spam_protection.php';
}


// widget Files

if ( file_exists( BOOKINGPRESS_WIDGET_DIR . '/class.bookingpress_frontwidget.php' ) ) {
	require_once BOOKINGPRESS_WIDGET_DIR . '/class.bookingpress_frontwidget.php';
}

// Elementer Files

if ( require_once BOOKINGPRESS_WIDGET_DIR . '/bookingpress_elementer.php' ) {
	require_once BOOKINGPRESS_WIDGET_DIR . '/bookingpress_elementer.php';
}

add_action( 'plugins_loaded', 'bookingpress_load_textdomain' );
/**
 * Loading plugin text domain
 */
function bookingpress_load_textdomain() {
	load_plugin_textdomain( 'bookingpress-appointment-booking', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
