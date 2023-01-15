<?php
	$requested_module = (!empty($_REQUEST['page']) && ($_REQUEST['page'] != "bookingpress")) ? sanitize_text_field( str_replace('bookingpress_', '', $_REQUEST['page']) ) : 'dashboard';

	$bookingpress_load_file_name = BOOKINGPRESS_VIEWS_DIR . '/bookingpress_header.php';
	$bookingpress_load_file_name = apply_filters( 'bookingpress_modify_header_content', $bookingpress_load_file_name );
	require $bookingpress_load_file_name;

	$module_arr_data = array(
		'dashboard'     => array(
			'title'        => __( 'Dashboard', 'bookingpress-appointment-booking' ),
			'document_url' => 'https://www.bookingpressplugin.com/documents/dashboard/',
			'read_more'    => 'https://www.bookingpressplugin.com/documents/dashboard/',
		),
		'services'      => array(
			'title'        => __( 'Services', 'bookingpress-appointment-booking' ),
			'document_url' => 'https://www.bookingpressplugin.com/documents/services/',
			'read_more'    => 'https://www.bookingpressplugin.com/documents/services/',
		),
		'customers'     => array(
			'title'        => __( 'Customers', 'bookingpress-appointment-booking' ),
			'document_url' => 'https://www.bookingpressplugin.com/documents/customers/',
			'read_more'    => 'https://www.bookingpressplugin.com/documents/customers/',
		),
		'calendar'      => array(
			'title'        => __( 'Calendar', 'bookingpress-appointment-booking' ),
			'document_url' => 'https://www.bookingpressplugin.com/documents/admin-calendar-view/',
			'read_more'    => 'https://www.bookingpressplugin.com/documents/admin-calendar-view/',
		),
		'appointments'  => array(
			'title'        => __( 'Appointments', 'bookingpress-appointment-booking' ),
			'document_url' => 'https://www.bookingpressplugin.com/documents/appointments/',
			'read_more'    => 'https://www.bookingpressplugin.com/documents/appointments/',
		),
		'notifications' => array(
			'title'        => __( 'Notifications', 'bookingpress-appointment-booking' ),
			'document_url' => 'https://www.bookingpressplugin.com/documents/email-notifications/',
			'read_more'    => 'https://www.bookingpressplugin.com/documents/email-notifications/',
		),
		'payments'      => array(
			'title'        => __( 'Payments', 'bookingpress-appointment-booking' ),
			'document_url' => 'https://www.bookingpressplugin.com/documents/payments/',
			'read_more'    => 'https://www.bookingpressplugin.com/documents/payments/',
		),
		'settings'      => array(
			'title'        => __( 'Settings', 'bookingpress-appointment-booking' ),
			'document_url' => 'https://www.bookingpressplugin.com/documents/general-settings/',
			'read_more'    => 'https://www.bookingpressplugin.com/documents/general-settings/',
		),
		'customize'     => array(
			'title'        => __( 'Customize', 'bookingpress-appointment-booking' ),
			'document_url' => 'https://www.bookingpressplugin.com/documents/frontend-customization/',
			'read_more'    => 'https://www.bookingpressplugin.com/documents/frontend-customization/',
		),
	);

	$module_arr_data = apply_filters('bookingpress_modify_module_arr_data', $module_arr_data);

	do_action( 'bookingpress_' . $requested_module . '_dynamic_view_load' );
	?>

<el-drawer custom-class="bpa-help-drawer" :visible.sync="needHelpDrawer" :direction="needHelpDrawerDirection">
	<el-container>
		<div class="bpa-back-loader-container" v-if="is_display_drawer_loader == '1'">
			<div class="bpa-back-loader"></div>
		</div>
		<div class="bpa-hd-header">
			<h1 class="bpa-page-heading">{{ requested_module }}</h1>
			<el-link href="<?php echo $module_arr_data[ $requested_module ]['read_more']; ?>" :underline="false" target="_blank" class="bpa-btn bpa-btn__small"><?php esc_html_e( 'Read more', 'bookingpress-appointment-booking' ); ?></el-link>
		</div>
		<div class="bpa-hd-body" v-html="helpDrawerData"></div>
	</el-container>	
</el-drawer>
<el-drawer custom-class="bpa-help-drawer" :visible.sync="needHelpDrawer_add" :direction="add_needHelpDrawerDirection">
	<el-container>
		<div class="bpa-back-loader-container" v-if="is_display_drawer_loader == '1'">
			<div class="bpa-back-loader"></div>
		</div>
		<div class="bpa-hd-header">
			<h1 class="bpa-page-heading"><?php echo esc_html( $module_arr_data[ $requested_module ]['title'] ); ?></h1>
			<el-link href="<?php echo $module_arr_data[ $requested_module ]['read_more']; ?>" :underline="false" target="_blank" class="bpa-btn bpa-btn__small"><?php esc_html_e( 'Read more', 'bookingpress-appointment-booking' ); ?></el-link>
		</div>
		<div class="bpa-hd-body" v-html="helpDrawerData"></div>
	</el-container>	
</el-drawer>
