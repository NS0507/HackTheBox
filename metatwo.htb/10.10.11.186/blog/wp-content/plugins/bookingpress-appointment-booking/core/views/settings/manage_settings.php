<?php
	global $bookingpress_slugs;
	$setting_module = !empty($_REQUEST['setting_page']) ? $_REQUEST['setting_page'] : 'general';
	$bookingpress_setting_page_url = add_query_arg('page', $bookingpress_slugs->bookingpress_settings, esc_url( BOOKINGPRESS_MENU_URL ));

	$general_settings_url = $bookingpress_setting_page_url;
	$company_settings_url = add_query_arg('setting_page', 'company', $bookingpress_setting_page_url);
	$notification_settings_url = add_query_arg('setting_page', 'notifications', $bookingpress_setting_page_url);
	$workhours_settings_url = add_query_arg('setting_page', 'workhours', $bookingpress_setting_page_url);
	$daysoff_settings_url = add_query_arg('setting_page', 'daysoff', $bookingpress_setting_page_url);
	$payment_settings_url = add_query_arg('setting_page', 'payment', $bookingpress_setting_page_url);
	$messages_settings_url = add_query_arg('setting_page', 'messages', $bookingpress_setting_page_url);
	$debug_logs_settings_url = add_query_arg('setting_page', 'debug_logs', $bookingpress_setting_page_url);
?>
<el-main class="bpa-main-listing-card-container bpa-general-settings--main-container" id="all-page-main-container" >
	<div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
		<div class="bpa-back-loader"></div>
	</div>
	<div class="bpa-back-loader-container" id="bpa-page-loading-loader">
		<div class="bpa-back-loader"></div>
	</div>
	<div id="bpa-main-container">
		<el-tabs type="card" v-model="selected_tab" tab-position="left" class="bpa-tabs bpa-tabs--vertical__left-side" @tab-click="settings_tab_select($event)">
			<?php
				require BOOKINGPRESS_VIEWS_DIR . '/settings/general_setting_tab.php';
				require BOOKINGPRESS_VIEWS_DIR . '/settings/company_setting_tab.php';
				require BOOKINGPRESS_VIEWS_DIR . '/settings/notification_setting_tab.php';
				require BOOKINGPRESS_VIEWS_DIR . '/settings/customers_setting_tab.php';
				require BOOKINGPRESS_VIEWS_DIR . '/settings/workhours_setting_tab.php';
				require BOOKINGPRESS_VIEWS_DIR . '/settings/daysoff_setting_tab.php';
				require BOOKINGPRESS_VIEWS_DIR . '/settings/payment_setting_tab.php';
				require BOOKINGPRESS_VIEWS_DIR . '/settings/messages_setting_tab.php';
				require BOOKINGPRESS_VIEWS_DIR . '/settings/debug_log_settings.php';
			?>
		</el-tabs>
	</div>
</el-main>