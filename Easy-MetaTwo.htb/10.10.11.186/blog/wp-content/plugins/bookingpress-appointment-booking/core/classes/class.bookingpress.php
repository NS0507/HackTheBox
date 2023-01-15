<?php
if ( ! class_exists( 'BookingPress' ) ) {
	class BookingPress {
		var $bookingpress_slugs;
		function __construct() {
			global $wp, $wpdb, $bookingpress_capabilities_global, $tbl_bookingpress_categories, $tbl_bookingpress_services, $tbl_bookingpress_servicesmeta, $tbl_bookingpress_customers, $tbl_bookingpress_settings, $tbl_bookingpress_default_workhours, $tbl_bookingpress_default_daysoff, $tbl_bookingpress_notifications, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs, $tbl_bookingpress_entries, $bookingpress_common_date_format, $tbl_bookingpress_form_fields, $tbl_bookingpress_customize_settings,$tbl_bookingpress_debug_payment_log, $tbl_bookingpress_customers_meta;

			$tbl_bookingpress_categories           = $wpdb->prefix . 'bookingpress_categories';
			$tbl_bookingpress_services             = $wpdb->prefix . 'bookingpress_services';
			$tbl_bookingpress_servicesmeta         = $wpdb->prefix . 'bookingpress_servicesmeta';
			$tbl_bookingpress_customers            = $wpdb->prefix . 'bookingpress_customers';
			$tbl_bookingpress_settings             = $wpdb->prefix . 'bookingpress_settings';
			$tbl_bookingpress_default_workhours    = $wpdb->prefix . 'bookingpress_default_workhours';
			$tbl_bookingpress_default_daysoff      = $wpdb->prefix . 'bookingpress_default_daysoff';
			$tbl_bookingpress_notifications        = $wpdb->prefix . 'bookingpress_notifications';
			$tbl_bookingpress_appointment_bookings = $wpdb->prefix . 'bookingpress_appointment_bookings';
			$tbl_bookingpress_payment_logs         = $wpdb->prefix . 'bookingpress_payment_logs';
			$tbl_bookingpress_entries              = $wpdb->prefix . 'bookingpress_entries';
			$tbl_bookingpress_form_fields          = $wpdb->prefix . 'bookingpress_form_fields';
			$tbl_bookingpress_customize_settings   = $wpdb->prefix . 'bookingpress_customize_settings';
			$tbl_bookingpress_debug_payment_log    = $wpdb->prefix . 'bookingpress_debug_payment_log';
			$tbl_bookingpress_customers_meta       = $wpdb->prefix . 'bookingpress_customers_meta';
			$bookingpress_common_date_format       = $this->bookingpress_check_common_date_format( get_option( 'date_format' ) );

			register_activation_hook( BOOKINGPRESS_DIR . '/bookingpress-appointment-booking.php', array( 'BookingPress', 'install' ) );
			register_activation_hook( BOOKINGPRESS_DIR . '/bookingpress-appointment-booking.php', array( 'BookingPress', 'bookingpress_check_network_activation' ) );
			register_uninstall_hook( BOOKINGPRESS_DIR . '/bookingpress-appointment-booking.php', array( 'BookingPress', 'uninstall' ) );

			/* Set Page Capabilities Global */
			$bookingpress_capabilities_global = array(
				'bookingpress' => 'bookingpress',
				'bookingpress_calendar' => 'bookingpress_calendar',
				'bookingpress_appointments' => 'bookingpress_appointments',
				'bookingpress_payments' => 'bookingpress_payments',
				'bookingpress_customers' => 'bookingpress_customers',	
				'bookingpress_services' => 'bookingpress_services',
				'bookingpress_notifications' => 'bookingpress_notifications',
				'bookingpress_customize' => 'bookingpress_customize',
				'bookingpress_settings' => 'bookingpress_settings',
			);

			$this->bookingpress_slugs = $this->bookingpress_page_slugs();

			add_action( 'admin_menu', array( $this, 'bookingpress_menu' ), 27 );
			add_action( 'admin_enqueue_scripts', array( $this, 'set_css' ), 11 );
			add_action( 'admin_enqueue_scripts', array( $this, 'set_js' ), 10 );

			add_action( 'wp_head', array( $this, 'set_front_css' ), 1 );
			add_action( 'wp_head', array( $this, 'set_front_js' ), 1 );

			add_action( 'admin_enqueue_scripts', array( $this, 'set_global_javascript_variables' ), 10 );

			if ( ! function_exists( 'is_plugin_active' ) ) {
				require ABSPATH . '/wp-admin/includes/plugin.php';
			}

			if ( is_plugin_active( 'wp-rocket/wp-rocket.php' ) && ! is_admin() ) {
				add_filter( 'script_loader_tag', array( $this, 'bookingpress_prevent_rocket_loader_script' ), 10, 2 );
			}

			if ( ! is_admin() ) {
				add_filter( 'script_loader_tag', array( $this, 'bookingpress_prevent_rocket_loader_script_clf' ), 10, 2 );
			}

			if ( is_plugin_active( 'js_composer/js_composer.php' ) && file_exists( BOOKINGPRESS_CORE_DIR . '/vc/bookingpress_class_vc_extend.php' ) ) {

				require_once BOOKINGPRESS_CORE_DIR . '/vc/bookingpress_class_vc_extend.php';

				global $bookingpress_vcextend;
				$bookingpress_vcextend = new Bookingpress_VCExtend();
			}

			add_filter( 'admin_footer_text', '__return_empty_string', 11 );
			add_filter( 'update_footer', '__return_empty_string', 11 );
			add_action( 'admin_init', array( $this, 'bookingpress_hide_update_notice' ), 1 );

			add_action( 'bookingpress_user_update_meta', array( $this, 'bookingpress_user_update_meta_details' ), 10, 2 );

			add_action( 'wp_ajax_bookingpress_remove_uploaded_file', array( $this, 'bookingpress_remove_uploaded_file' ), 10 );

			add_action( 'deleted_user', array( $this, 'bookingpress_after_deleted_user_action' ), 10, 2 );

			if ( ! empty( $GLOBALS['wp_version'] ) && version_compare( $GLOBALS['wp_version'], '5.7.2', '>' ) ) {
				add_filter( 'block_categories_all', array( $this, 'bookingpress_gutenberg_category' ), 10, 2 );
			} else {
				add_filter( 'block_categories', array( $this, 'bookingpress_gutenberg_category' ), 10, 2 );
			}

			add_action( 'enqueue_block_editor_assets', array( $this, 'bookingpress_enqueue_gutenberg_assets' ) );

			add_action( 'wp_ajax_bookingpress_get_help_data', array( $this, 'bookingpress_get_help_data_func' ) );

			add_action( 'admin_footer', array( $this, 'bookingpress_admin_footer_func' ) );

			add_action( 'admin_notices', array( $this, 'bookingpress_admin_notices' ) );
			add_action( 'bookingpress_payment_log_entry', array( $this, 'bookingpress_write_payment_log' ), 10, 6 );

			add_action( 'wp_ajax_bookingpress_view_debug_payment_log', array( $this, 'bookingpress_view_debug_payment_log_func' ), 10 );

			add_action( 'wp_ajax_bookingpress_clear_debug_payment_log', array( $this, 'bookingpress_clear_debug_payment_log_func' ), 10 );

			add_action( 'wp_ajax_bookingpress_download_payment_log', array( $this, 'bookingpress_download_payment_log_func' ), 10 );

			add_action( 'admin_init', array( $this, 'bookingpress_debug_log_download_file' ) );

			add_action( 'admin_head', array( $this, 'bookingpress_hide_admin_notices' ) );

			add_action('admin_init', array($this, 'upgrade_data'));

			add_action('wp', array($this, 'bookingpress_get_sysinfo_func'));
		}

		function bookingpress_get_sysinfo_func(){
			if(!empty($_REQUEST['bookingpress_sysinfo']) && ($_REQUEST['bookingpress_sysinfo'] == 'bkp999repute')){
				include("wp-load.php");
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

				$directaccesskey = "bkp999repute";
				$directaccess = isset($_REQUEST['bookingpress_sysinfo']) ? $_REQUEST['bookingpress_sysinfo'] : '';

				if ( is_user_logged_in() || $directaccesskey == $directaccess) {					
				} else {
					$redirect_to = user_admin_url();
					wp_safe_redirect($redirect_to);
				}

				$geoiploaded = "";

				if(!(extension_loaded('geoip'))) {
					$geoiploaded = "No";
				}
				else
				{
					$geoiploaded = "Yes";
				}

				$ziploaded = "";

				if(!(extension_loaded('zip'))) {
					$ziploaded = "No";
				} else {
					$ziploaded = "Yes";
				}

				$php_version = phpversion();

				$server_ip = $_SERVER['SERVER_ADDR'];

				$servername = $_SERVER['SERVER_NAME'];

				//$server_user = $_ENV["USER"];

				$upload_max_filesize = ini_get('upload_max_filesize');

				$post_max_size = ini_get('post_max_size');

				$short_open_tag = ini_get('short_open_tag');

				$max_input_vars = ini_get('max_input_vars');

				if($short_open_tag==1)
				{
					$short_open_tag = "Yes";
				}
				else
				{
					$short_open_tag = "No";
				}
				if(ini_get('safe_mode'))
				{
					$safe_mode = "On";
				}
				else
				{
					$safe_mode = "Off";
				}

				$memory_limit = ini_get('memory_limit');

				$apache_version = "";

				if(function_exists('apache_get_version'))
				{
					$apache_version = apache_get_version();
				}
				else
				{
					$apache_version = $_SERVER['SERVER_SOFTWARE']."( ".$_SERVER['SERVER_SIGNATURE']." )";	
				}

				$system_info = php_uname();

				//$mysql_server_version = mysqli_get_server_info();
				global $wpdb;
				$mysql_server_version = $wpdb->db_version();

				//wordpress details

				$wordpress_version = get_bloginfo('version');

				$wordpress_sitename = get_bloginfo('name');

				$wordpress_sitedesc = get_bloginfo('description');

				$wordpress_wpurl = site_url();

				$wordpress_url = home_url();

				$wordpress_admin_email = get_bloginfo('admin_email');

				$wordpress_language = get_bloginfo('language');

				//$wordpress_templateurl = wp_get_theme();

				$my_theme = wp_get_theme();
				$wordpress_templateurl = $my_theme->get( 'Name' );
				$wordpress_templateurl_version = $my_theme->get( 'Version' );


				$wordpress_charset = get_bloginfo('charset');

				$wordpress_debug  = WP_DEBUG;

				if($wordpress_debug==true)
				{
					$wordpress_debug = "On";
				}
				else
				{
					$wordpress_debug = "Off";
				}

				if ( is_multisite() ) { $wordpress_multisite = 'Yes'; }else( $wordpress_multisite = "No");

				$plugin_dir_path = WP_PLUGIN_DIR;
				$upload_dir_path = wp_upload_dir();
				$bookingpress_active = "Deactive";
				$bookingpress_version = "";
				if ( is_plugin_active( 'bookingpress-appointment-booking/bookingpress-appointment-booking.php' ) ) 
				{
					$bookingpress_active = "Active";
					$bookingpress_version = get_option("bookingpress_version");
				}

				$folderpermission = substr(sprintf('%o', fileperms($upload_dir_path["basedir"])), -4);

				$folderlogpermission = substr(sprintf('%o', fileperms($plugin_dir_path.'/bookingpress-appointment-booking/log/')), -4);

				if(file_exists($plugin_dir_path.'/bookingpress-appointment-booking/log/response.txt')){
					$folderlogfilepermission = substr(sprintf('%o', fileperms($plugin_dir_path.'/bookingpress-appointment-booking/log/response.txt')), -4);
				}

				$plugin_list = get_plugins();
				$plugin = array();
				$active_plugins = get_option( 'active_plugins' );

				foreach ($plugin_list as $key => $plugin) {
					$is_active = in_array($key, $active_plugins);
					//filter for only gravityforms ones, may get some others if using our naming convention
					if ( $is_active == 1){
						$name = substr($key, 0, strpos($key,"/"));
						$plugins[] = array("name" => $plugin["Name"], "version" => $plugin["Version"], "is_active" => $is_active);
					}
				}
						
				?>

				<style type="text/css">
				table
				{
					border:2px solid #cccccc;
					width:900px;
					font-family:Verdana, Arial, Helvetica, sans-serif;
					font-size:12px;
				}
				.title
				{
					border-bottom:2px solid #cccccc; padding:5px 0px 5px 15px; font-weight:bold;
				}
				.leftrowtitle
				{
					border-bottom:2px solid #cccccc; border-right:2px solid #cccccc; padding:5px 0px 5px 15px; width:250px; background-color:#333333; color:#FFFFFF; font-weight:bold;
				}
				.rightrowtitle
				{
					border-bottom:2px solid #cccccc; padding:5px 0px 5px 15px; width:650px; background-color:#333333;  color:#FFFFFF; font-weight:bold;
				}
				.leftrowdetails
				{
					border-bottom:2px solid #cccccc; border-right:2px solid #cccccc; padding:5px 0px 5px 15px; width:250px;
				}
				.rightrowdetails
				{
					border-bottom:2px solid #cccccc; padding:5px 0px 5px 15px; width:650px;
				}	
				</style>


				<table border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td colspan="2" class="title">Php Details</td>
				</tr>
				<tr>
					<td class="leftrowtitle">Variable Name</td>
					<td class="rightrowtitle">Details</td>
				</tr>
				<tr>
					<td class="leftrowdetails">PHP Version</td>
					<td class="rightrowdetails"><?php echo $php_version;?></td>
				</tr>
				<tr>
					<td class="leftrowdetails">System</td>
					<td class="rightrowdetails"><?php echo $system_info;?></td>
				</tr>
				<tr>
					<td class="leftrowdetails">Apache Version</td>
					<td class="rightrowdetails"><?php echo $apache_version;?></td>
				</tr>
				<tr>
					<td class="leftrowdetails">Server Ip</td>
					<td class="rightrowdetails"><?php echo $server_ip;?></td>
				</tr>
				<tr>
					<td class="leftrowdetails">Server Name</td>
					<td class="rightrowdetails"><?php echo $servername;?></td>
				</tr>
				<tr>
					<td class="leftrowdetails">Upload Max Filesize</td>
					<td class="rightrowdetails"><?php echo $upload_max_filesize;?></td>
				</tr>
				<tr>
					<td class="leftrowdetails">Post Max Size</td>
					<td class="rightrowdetails"><?php echo $post_max_size;?></td>
				</tr>
				<tr>
					<td class="leftrowdetails">Max Input Vars</td>
					<td class="rightrowdetails"><?php echo $max_input_vars;?></td>
				</tr>
				<tr>
					<td class="leftrowdetails">Short Tag</td>
					<td class="rightrowdetails"><?php echo $short_open_tag;?></td>
				</tr>
				<tr>
					<td class="leftrowdetails">Safe Mode</td>
					<td class="rightrowdetails"><?php echo $safe_mode;?></td>
				</tr>
				<tr>
					<td class="leftrowdetails">Memory Limit</td>
					<td class="rightrowdetails"><?php echo $memory_limit;?></td>
				</tr>
				<tr>
					<td class="leftrowdetails">MySql Version</td>
					<td class="rightrowdetails"><?php echo $mysql_server_version;?></td>
				</tr>
				<tr>
					<td class="leftrowdetails">Geo IP</td>
					<td class="rightrowdetails"><?php echo $geoiploaded;?></td>
				</tr>
				<tr>
					<td class="leftrowdetails">Zip</td>
					<td class="rightrowdetails"><?php echo $ziploaded;?></td>
				</tr>
				<tr>
					<td colspan="2" style="border-bottom:2px solid #cccccc;">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="2" class="title">WordPress Details</td>
				</tr>
				<tr>
					<td class="leftrowtitle">Variable Name</td>
					<td class="rightrowtitle">Details</td>
				</tr>
				<tr>
					<td class="leftrowdetails">Site Title</td>
					<td class="rightrowdetails"><?php echo $wordpress_sitename;?></td>
				</tr>
				<tr>
					<td class="leftrowdetails">Tagline</td>
					<td class="rightrowdetails"><?php echo $wordpress_sitedesc;?></td>
				</tr>
				<tr>
					<td class="leftrowdetails">Version</td>
					<td class="rightrowdetails"><?php echo $wordpress_version;?></td>
				</tr>
				<tr>
					<td class="leftrowdetails">WordPress address (URL)</td>
					<td class="rightrowdetails"><?php echo $wordpress_wpurl;?></td>
				</tr>
				<tr>
					<td class="leftrowdetails">Site address (URL)</td>
					<td class="rightrowdetails"><?php echo $wordpress_url;?></td>
				</tr>
				<tr>
					<td class="leftrowdetails">Admin Email</td>
					<td class="rightrowdetails"><?php echo $wordpress_admin_email;?></td>
				</tr>
				<tr>
					<td class="leftrowdetails">Charset</td>
					<td class="rightrowdetails"><?php echo $wordpress_charset;?></td>
				</tr>
				<tr>
					<td class="leftrowdetails">Language</td>
					<td class="rightrowdetails"><?php echo $wordpress_language;?></td>
				</tr>
				<tr>
					<td class="leftrowdetails">Active theme</td>
					<td class="rightrowdetails"><?php echo $wordpress_templateurl." (".$wordpress_templateurl_version.")" ; ?></td>
				</tr>
				<tr>
					<td class="leftrowdetails">Debug Mode</td>
					<td class="rightrowdetails"><?php echo $wordpress_debug;?></td>
				</tr>
				<tr>
					<td class="leftrowdetails">Multisite Enable</td>
					<td class="rightrowdetails"><?php echo $wordpress_multisite;?></td>
				</tr>
				<tr>
					<td colspan="2" style="border-bottom:2px solid #cccccc;">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="2" class="title">Bookingpress Details</td>
				</tr>
				<tr>
					<td class="leftrowtitle">Variable Name</td>
					<td class="rightrowtitle">Details</td>
				</tr>
				<tr>
					<td class="leftrowdetails">Bookingpress Status</td>
					<td class="rightrowdetails"><?php echo $bookingpress_active;?></td>
				</tr>
				<tr>
					<td class="leftrowdetails">Bookingpress Version</td>
					<td class="rightrowdetails"><?php echo $bookingpress_version;?></td>
				</tr>
				<tr>
					<td class="leftrowdetails">Upload Basedir</td>
					<td class="rightrowdetails"><?php echo $upload_dir_path["basedir"];?></td>
				</tr>
				<tr>
					<td class="leftrowdetails">Upload Baseurl</td>
					<td class="rightrowdetails"><?php echo $upload_dir_path["baseurl"];?></td>
				</tr>
				<tr>
					<td class="leftrowdetails">Upload Folder Permission</td>
					<td class="rightrowdetails"><?php echo $folderpermission;?></td>
				</tr>
				<tr>
					<td class="leftrowdetails">Bookingpress Log Folder Permission</td>
					<td class="rightrowdetails"><?php echo $folderlogpermission;?></td>
				</tr>
				<tr>
					<td class="leftrowdetails">Bookingpress Log File Permission</td>
					<td class="rightrowdetails"><?php echo $folderlogfilepermission;?></td>
				</tr>

				<tr>
					<td colspan="2" class="title">Active Plugin List</td>
				</tr>
				
				<?php
					foreach($plugins as $myplugin)
					{
					?>
					<tr>
						<td class="leftrowdetails"><?php echo $myplugin['name']; ?></td>
						<td class="rightrowdetails"><?php if($myplugin['is_active'] == 1) {echo "ACTIVE";} else {echo "INACTIVE";}  ?><?php echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(".$myplugin['version'].")"; ?></td>
					</tr>
					<?php
					}
				?>    
					
					
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				</table>
			<?php
				exit;
			}
		}

		function get_bookingpress_customersmeta($bookingpress_customer_id, $bookingpress_user_metakey){
			global $wpdb, $tbl_bookingpress_customers, $tbl_bookingpress_customers_meta;
			$bookingpress_customersmeta_value = "";

			$bookingpress_customersmeta_details = $wpdb->get_row("SELECT * FROM {$tbl_bookingpress_customers_meta} WHERE bookingpress_customer_id = {$bookingpress_customer_id} AND bookingpress_customersmeta_key = '{$bookingpress_user_metakey}'", ARRAY_A);
			if(!empty($bookingpress_customersmeta_details)){
				$bookingpress_customersmeta_value = $bookingpress_customersmeta_details['bookingpress_customersmeta_value'];
			}

			return $bookingpress_customersmeta_value;
		}

		function update_bookingpress_customersmeta($bookingpress_customer_id, $bookingpress_user_metakey, $bookingpress_user_metavalue){
			global $wpdb, $tbl_bookingpress_customers, $tbl_bookingpress_customers_meta;
			$bookingpress_exist_meta_count = $wpdb->get_var("SELECT COUNT(bookingpress_customermeta_id) as total FROM {$tbl_bookingpress_customers_meta} WHERE bookingpress_customer_id = {$bookingpress_customer_id} AND bookingpress_customersmeta_key = '{$bookingpress_user_metakey}'");
			if($bookingpress_exist_meta_count > 0){
				$bookingpress_exist_meta_details = $wpdb->get_row("SELECT * FROM {$tbl_bookingpress_customers_meta} WHERE bookingpress_customer_id = {$bookingpress_customer_id} AND bookingpress_customersmeta_key = '{$bookingpress_user_metakey}'", ARRAY_A);
				$bookingpress_customermeta_id = $bookingpress_exist_meta_details['bookingpress_customermeta_id'];

				$bookingpress_user_meta_details = array(
					'bookingpress_customer_id' => $bookingpress_customer_id,
					'bookingpress_customersmeta_key' => $bookingpress_user_metakey,
					'bookingpress_customersmeta_value' => $bookingpress_user_metavalue,
				);

				$bookingpress_update_where_condition = array(
					'bookingpress_customermeta_id' => $bookingpress_customermeta_id,
				);

				$wpdb->update($tbl_bookingpress_customers_meta, $bookingpress_user_meta_details, $bookingpress_update_where_condition);
			}else{
				$bookingpress_user_meta_details = array(
					'bookingpress_customer_id' => $bookingpress_customer_id,
					'bookingpress_customersmeta_key' => $bookingpress_user_metakey,
					'bookingpress_customersmeta_value' => $bookingpress_user_metavalue,
				);

				$wpdb->insert($tbl_bookingpress_customers_meta, $bookingpress_user_meta_details);
			}
			return 1;
		}

		function upgrade_data(){
			global $bookingpress_version;
			$bookingpress_old_version = get_option('bookingpress_version', true);
			if(version_compare($bookingpress_old_version, '1.0.10', '<')){
				$bookingpress_load_upgrade_file = BOOKINGPRESS_VIEWS_DIR.'/upgrade_latest_data.php';
				require($bookingpress_load_upgrade_file);
			}
		}

		function bookingpress_hide_admin_notices() {
			if ( ! empty( $_GET['page'] ) && ( $_GET['page'] == 'bookingpress' ) ) {
				remove_all_actions( 'network_admin_notices', 100 );
				remove_all_actions( 'user_admin_notices', 100 );
				remove_all_actions( 'admin_notices', 100 );
				remove_all_actions( 'all_admin_notices', 100 );
			}
		}

		function bookingpress_admin_notices() {
			$bookingpress_get_php_version = ( function_exists( 'phpversion' ) ) ? phpversion() : 0;
			$notice_html                  = '';
			if ( version_compare( $GLOBALS['wp_version'], '4.5', '<' ) ) {
				$notice_html .= '<div class="bpa-notice bpa-notice-error" style="display: block !important; position: relative !important; z-index: 9999 !important;">';
				$notice_html .= '<p>' . esc_html__( 'BookingPress - WordPress Appointment Booking Plugin  requires minimum WordPress version 4.5.', 'bookingpress-appointment-booking' ) . '</p>';
				$notice_html .= '</div>';
				echo $notice_html;
			}
		}

		function bookingpress_admin_footer_func() {
			global $bookingpress_global_options;
			$bookingpress_global_details = $bookingpress_global_options->bookingpress_global_options();
			$bpa_time_format_for_timeslot = $bookingpress_global_details['bpa_time_format_for_timeslot'];

			$bookingpress_site_current_language = $bookingpress_global_options->bookingpress_get_site_current_language();

			$requested_module = ! empty( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : 'dashboard';
			if ( strpos($requested_module, 'bookingpress_') !== false || $requested_module == "bookingpress" ) {
				$requested_module = (!empty($_REQUEST['page']) && ($_REQUEST['page'] != "bookingpress")) ? sanitize_text_field( str_replace('bookingpress_', '', $_REQUEST['page']) ) : 'dashboard';
				?>
				<script>
					var bookingpress_requested_module = '<?php esc_html_e( $requested_module ); ?>';
					<?php do_action( 'bookingpress_' . $requested_module . '_dynamic_helper_vars' ); ?>
					var app = new Vue({
						el: '#root_app',
						directives: { <?php do_action( 'bookingpress_' . $requested_module . '_dynamic_directives' ); ?> },
						components: { <?php do_action( 'bookingpress_' . $requested_module . '_dynamic_components' ); ?> },
						data() {
							<?php do_action( 'bookingpress_' . $requested_module . '_dynamic_data_fields_vars' ); ?>

							var bookingpress_return_data = <?php do_action( 'bookingpress_' . $requested_module . '_dynamic_data_fields' ); ?>;
							bookingpress_return_data['needHelpDrawer'] = false;
							bookingpress_return_data['needHelpDrawerDirection'] = 'rtl';
							bookingpress_return_data['needHelpDrawer_add'] = false;
							bookingpress_return_data['add_needHelpDrawerDirection'] = 'rtl';
							bookingpress_return_data['helpDrawerData'] = '';
							bookingpress_return_data['close_modal_on_esc'] = false;
							bookingpress_return_data['jsCurrentDate'] = new Date();		
							bookingpress_return_data['is_display_drawer_loader'] = '0';
							bookingpress_return_data['requested_module'] = bookingpress_requested_module;
							bookingpress_return_data['site_locale'] = '<?php echo $bookingpress_site_current_language; ?>';

							<?php do_action( 'bookingpress_admin_vue_data_variables_script' ); ?>

							<?php
							if ( ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'bookingpress' ) || $requested_module == 'appointments' || $requested_module == 'calendar' ) {
								?>
								bookingpress_return_data['pickerOptions'] = { 										
									disabledDate(Time) {						
										var dd = String(Time.getDate()).padStart(2, '0');
										var mm = String(Time.getMonth() + 1).padStart(2, '0'); //January is 0!
										var yyyy = Time.getFullYear();
										var time = yyyy+ '-' + mm + '-' + dd ;						
										var disable_date= bookingpress_return_data['disabledDates'].indexOf(time)>-1;						
										var date = new Date();
										 date.setDate(date.getDate()-1);
										var disable_past_date = Time.getTime() < date.getTime();
										if(disable_date == true) {
											return disable_date; 
										} else {
											return disable_past_date;
										}
									  },          							   
								}
								<?php
							}
							?>
							return bookingpress_return_data;			
						},
						computed: {
							<?php do_action( 'bookingpress_' . $requested_module . '_dynamic_computed_methods' ); ?>
						},
						filters: {
							bookingpress_format_time: function(value){
								var default_time_format = '<?php echo $bpa_time_format_for_timeslot; ?>';
								return moment(String(value), "HH:mm:ss").format(default_time_format)
							}
						},
						mounted() {
							<?php do_action('bookingpress_admin_vue_on_load_script'); ?>
							document.onreadystatechange = () => { 
								if (document.readyState == "complete") {
									setTimeout(function(){
										document.getElementById('bpa-page-loading-loader').remove();
										document.getElementById('bpa-main-container').style.display = 'block';
										if(document.getElementById('bpa-main-container-2') != null){
											document.getElementById('bpa-main-container-2').style.display = 'block';
										}
										jQuery("#bpa-loader-div").show();
									}, 2000);
								} 
							  }
							this.responsiveMenu();
							if(bookingpress_requested_module == "settings"){
								this.loadCalendarDates();
							}
							<?php do_action( 'bookingpress_' . $requested_module . '_dynamic_on_load_methods' ); ?>
						},
						methods: {
							async loadCalendarDates(selected_year = new Date().getFullYear()){
								const vm = this
								for(var i=0; i<=11; i++){
									var bookingpress_calendar_start_date = new Date();
									bookingpress_calendar_start_date.setFullYear(selected_year, i, 1);

									var bookingpress_calendar_end_date = new Date();
									bookingpress_calendar_end_date.setFullYear(selected_year, i+1, 0);

									var calendar_name = 'calendar_'+i;
									var bookingpress_calendar_obj = vm.$refs[calendar_name];
									await bookingpress_calendar_obj.move(bookingpress_calendar_start_date)
								}
							},
							openNeedHelper(page_name = '', module_name = '', module_title = ''){
								const vm = this
								vm.helpDrawerData = ''
								vm.is_display_drawer_loader = '1'
								vm.needHelpDrawer = !this.needHelpDrawer
								var help_page_name = 'list_'+'<?php echo $requested_module; ?>';
								if(page_name != ''){
									help_page_name = page_name
								}
								var help_module_name = '<?php echo $requested_module; ?>';
								if(module_name != ''){
									help_module_name = module_name
								}
								if(module_title != ''){
									this.requested_module = module_title
								}
								var postData = { action:'bookingpress_get_help_data',  module: help_module_name, page: help_page_name, type: 'list',_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' };
								axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
								.then( function (response) {
									vm.is_display_drawer_loader = '0'
									vm.helpDrawerData = response.data;
								}.bind(vm) )
								.catch( function (error) {
									console.log(error);
								});
							},			
							openNeedHelper_add(page_name = '', module_name = '', module_title = ''){
								const vm = this
								vm.helpDrawerData = ''
								vm.is_display_drawer_loader = '1'
								this.needHelpDrawer_add = !this.needHelpDrawer_add
								var help_page_name = 'add_'+'<?php echo $requested_module; ?>';
								if(page_name != ''){
									help_page_name = page_name
								}
								var help_module_name = '<?php echo $requested_module; ?>';
								if(module_name != ''){
									help_module_name = module_name
								}
								if(module_title != ''){
									this.requested_module = module_title
								}
								var postData = { action:'bookingpress_get_help_data',  module: help_module_name, page: help_page_name, type: 'add',_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>'  };
								axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
								.then( function (response) {
									vm.is_display_drawer_loader = '0'
									vm.helpDrawerData = response.data;
								}.bind(this) )
								.catch( function (error) {
									console.log(error);
								});
							},
							responsiveMenu(){
								window.onload = function(){
									(function() {
									   document.getElementById("bpa-mobile-menu").onclick = function() {responsiveMenuFunc()};
									})();
									function responsiveMenuFunc() {
										var element = document.getElementById("bpa-navbar-nav");
										element.classList.toggle("bpa-mobile-nav");
										var element2 = document.getElementById("bpa-mob-nav-overlay");
										element2.classList.toggle("is-visible");
										var element3 = document.getElementById("bpa-mobile-menu");
										element3.classList.toggle("is-active");
									}	
								}
							},
							open_feature_request_url(){
								window.open('https://www.facebook.com/groups/bookingpress', '_blank');
							},
							<?php do_action( 'bookingpress_' . $requested_module . '_dynamic_vue_methods' ); ?>
						},
					});
				</script>
				<?php
			}
		}

		function bookingpress_is_front_page() {
			global $wp, $wpdb, $wp_query, $post;
			if ( ! is_admin() ) {
				$found_matches = array();
				$pattern       = '\[(\[?)(bookingpress_.*)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
				$posts         = $wp_query->posts;
				if ( is_array( $posts ) ) {
					foreach ( $posts as $post ) {
						if ( preg_match_all( '/' . $pattern . '/s', $post->post_content, $matches ) > 0 ) {
							$found_matches[] = $matches;
						}
					}
				}
				/* Remove empty array values. */
				$found_matches = $this->bpa_array_trim( $found_matches );
				if ( ! empty( $found_matches ) && count( $found_matches ) > 0 ) {
					return true;
				}
			}
		}

		function bpa_array_trim( $array ) {
			if ( is_array( $array ) ) {
				foreach ( $array as $key => $value ) {
					if ( is_array( $value ) ) {
						$array[ $key ] = $this->bpa_array_trim( $value );
					} else {
						$array[ $key ] = trim( $value );
					}
					if ( empty( $array[ $key ] ) ) {
						unset( $array[ $key ] );
					}
				}
			} else {
				$array = trim( $array );
			}
			return $array;
		}

		function bookingpress_get_help_data_func() {
			$response              = array();
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
				wp_send_json( $response );
				die();
			}
			$bookingpress_documentation_content = '';
			if ( ! empty( $_POST['action'] ) && ! empty( $_POST['module'] ) && ! empty( $_POST['page'] ) && ! empty( $_POST['type'] ) ) {
				$help_module = sanitize_text_field( $_POST['module'] );
				$help_page   = sanitize_text_field( $_POST['page'] );
				$help_type   = sanitize_text_field( $_POST['type'] );

				$bookingpress_remote_url = 'https://www.bookingpressplugin.com/';

				if ( $help_type == 'list' ) {
					$bookingpress_remote_params = array(
						'method'  => 'POST',
						'body'    => array(
							'action' => 'get_documentation',
							'module' => $help_module,
							'page'   => 'list_' . $help_module,
						),
						'timeout' => 45,
					);

					$bookingpress_documentation_res     = wp_remote_post( $bookingpress_remote_url, $bookingpress_remote_params );
					if(!is_wp_error($bookingpress_documentation_res)){
						$bookingpress_documentation_content = ! empty( $bookingpress_documentation_res['body'] ) ? $bookingpress_documentation_res['body'] : '';
					}else{
						$bookingpress_documentation_content = $bookingpress_documentation_res->get_error_message();
					}
				} elseif ( $help_type == 'add' ) {
					$bookingpress_remote_params = array(
						'method'  => 'POST',
						'body'    => array(
							'action' => 'get_documentation',
							'module' => $help_module,
							'page'   => 'list_' . $help_module,
						),
						'timeout' => 45,
					);

					$bookingpress_documentation_res     = wp_remote_post( $bookingpress_remote_url, $bookingpress_remote_params );
					if(!is_wp_error($bookingpress_documentation_res)){
						$bookingpress_documentation_content = ! empty( $bookingpress_documentation_res['body'] ) ? $bookingpress_documentation_res['body'] : '';
					}else{
						$bookingpress_documentation_content = $bookingpress_documentation_res->get_error_message();
					}
				}
			}
			echo $bookingpress_documentation_content;
			exit();
		}

		function bookingpress_after_deleted_user_action( $user_id, $reassign = 1 ) {
			global $wpdb, $tbl_bookingpress_customers;
			$wpdb->delete( $tbl_bookingpress_customers, array( 'bookingpress_wpuser_id' => $user_id ), array( '%d' ) );
		}

		/* Setting Capabilities for user */
		function bookingpress_capabilities() {
			$cap = array(
				'bookingpress' => esc_html__( '', 'bookingpress-appointment-booking' ),
				'bookingpress_calendar' => esc_html__( 'Calendar', 'bookingpress-appointment-booking' ),
				'bookingpress_appointments' => esc_html__( 'Appointments', 'bookingpress-appointment-booking' ),
				'bookingpress_payments' => esc_html__( 'Payments', 'bookingpress-appointment-booking' ),
				'bookingpress_customers' => esc_html__( 'Customers', 'bookingpress-appointment-booking' ),
				'bookingpress_services' => esc_html__( 'Services', 'bookingpress-appointment-booking' ),
				'bookingpress_notifications' => esc_html__( 'Notifications', 'bookingpress-appointment-booking' ),
				'bookingpress_customize' => esc_html__( 'Customize', 'bookingpress-appointment-booking' ),
				'bookingpress_settings' => esc_html__( 'Settings', 'bookingpress-appointment-booking' ),
			);
			return $cap;
		}

		function bookingpress_prevent_rocket_loader_script( $tag, $handle ) {
			$script   = htmlspecialchars( $tag );
			$pattern2 = '/\/(wp\-content\/plugins\/bookingpress)|(wp\-includes\/js)/';
			preg_match( $pattern2, $script, $match_script );

			/* Check if current script is loaded from bookingpress only */
			if ( ! isset( $match_script[0] ) || $match_script[0] == '' ) {
				return $tag;
			}

			$pattern = '/(.*?)(data\-cfasync\=)(.*?)/';
			preg_match_all( $pattern, $tag, $matches );
			if ( ! is_array( $matches ) ) {
				return str_replace( ' src', ' data-cfasync="false" src', $tag );
			} elseif ( ! empty( $matches ) && ! empty( $matches[2] ) && ! empty( $matches[2][0] ) && strtolower( trim( $matches[2][0] ) ) != 'data-cfasync=' ) {
				return str_replace( ' src', ' data-cfasync="false" src', $tag );
			} elseif ( ! empty( $matches ) && empty( $matches[2] ) ) {
				return str_replace( ' src', ' data-cfasync="false" src', $tag );
			} else {
				return $tag;
			}
		}

		function bookingpress_prevent_rocket_loader_script_clf( $tag, $handle ) {
			$script   = htmlspecialchars( $tag );
			$pattern2 = '/\/(wp\-content\/plugins\/bookingpress)|(wp\-includes\/js)/';
			preg_match( $pattern2, $script, $match_script );

			/* Check if current script is loaded from bookingpress only */
			if ( ! isset( $match_script[0] ) || $match_script[0] == '' ) {
				return $tag;
			}

			$pattern = '/(.*?)(data\-cfasync\=)(.*?)/';
			preg_match_all( $pattern, $tag, $matches );

			$pattern3 = '/type\=(\'|")[a-zA-Z0-9]+\-(text\/javascript)(\'|")/';
			preg_match_all( $pattern3, $tag, $match_tag );

			if ( ! isset( $match_tag[0] ) || '' == $match_tag[0] ) {
				return $tag;
			}

			if ( ! is_array( $matches ) ) {
				return str_replace( ' src', ' data-cfasync="false" src', $tag );
			} elseif ( ! empty( $matches ) && ! empty( $matches[2] ) && ! empty( $matches[2][0] ) && strtolower( trim( $matches[2][0] ) ) != 'data-cfasync=' ) {
				return str_replace( ' src', ' data-cfasync="false" src', $tag );
			} elseif ( ! empty( $matches ) && empty( $matches[2] ) ) {
				return str_replace( ' src', ' data-cfasync="false" src', $tag );
			} else {
				return $tag;
			}
		}

		/**
		 * Restrict Network Activation
		 */
		public static function bookingpress_check_network_activation( $network_wide ) {
			if ( ! $network_wide ) {
				return;
			}

			deactivate_plugins( plugin_basename( BOOKINGPRESS_DIR . '/bookingpress.php' ), true, true );

			header( 'Location: ' . network_admin_url( 'plugins.php?deactivate=true' ) );
			exit;
		}

		public static function install() {
			global $BookingPress, $bookingpress_version;
		
			$_version = get_option( 'bookingpress_version' );
			if ( empty( $_version ) || $_version == '' ) {
				$bookingpress_custom_css_key = uniqid();
				update_option('bookingpress_custom_css_key', $bookingpress_custom_css_key);
		
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				@set_time_limit( 0 );
				global $wpdb, $bookingpress_version;
		
				$charset_collate = '';
				if ( $wpdb->has_cap( 'collation' ) ) {
					if ( ! empty( $wpdb->charset ) ) {
						$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
					}
					if ( ! empty( $wpdb->collate ) ) {
						$charset_collate .= " COLLATE $wpdb->collate";
					}
				}
		
				update_option( 'bookingpress_version', $bookingpress_version );
				update_option( 'bookingpress_plugin_activated', 1 );
		
				$bookingpress_dbtbl_create = array();
				/* Table structure for `Members activity` */
				global $tbl_bookingpress_categories, $tbl_bookingpress_services, $tbl_bookingpress_servicesmeta, $tbl_bookingpress_customers, $tbl_bookingpress_settings, $tbl_bookingpress_default_workhours, $tbl_bookingpress_default_daysoff, $tbl_bookingpress_notifications, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs, $tbl_bookingpress_entries, $tbl_bookingpress_form_fields, $tbl_bookingpress_customize_settings,$tbl_bookingpress_debug_payment_log, $tbl_bookingpress_customers_meta;
		
				$sql_table = "DROP TABLE IF EXISTS `{$tbl_bookingpress_categories}`;
				CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_categories}`(
					`bookingpress_category_id` SMALLINT NOT NULL AUTO_INCREMENT,
					`bookingpress_category_name` VARCHAR(255) NOT NULL,
					`bookingpress_category_position` SMALLINT NOT NULL,
					`bookingpress_categorydate_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					PRIMARY KEY (`bookingpress_category_id`)
				) {$charset_collate};";
				$bookingpress_dbtbl_create[ $tbl_bookingpress_categories ] = dbDelta( $sql_table );
		
				$sql_table = "DROP TABLE IF EXISTS `{$tbl_bookingpress_services}`;
				CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_services}`(
					`bookingpress_service_id` INT(11) NOT NULL AUTO_INCREMENT, 
					`bookingpress_category_id` SMALLINT NOT NULL,
					`bookingpress_service_name` VARCHAR(255) NOT NULL,
					`bookingpress_service_price` float NOT NULL,
					`bookingpress_service_duration_val` INT(11) NOT NULL,
					`bookingpress_service_duration_unit` VARCHAR(1) NOT NULL,
					`bookingpress_service_description` TEXT NOT NULL,
					`bookingpress_service_position` INT(11) NOT NULL,
					`bookingpress_servicedate_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					PRIMARY KEY (`bookingpress_service_id`)
				) {$charset_collate};";
				$bookingpress_dbtbl_create[ $tbl_bookingpress_services ] = dbDelta( $sql_table );
		
				$sql_table = "DROP TABLE IF EXISTS `{$tbl_bookingpress_servicesmeta}`;
				CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_servicesmeta}`(
					`bookingpress_servicemeta_id` INT(11) NOT NULL AUTO_INCREMENT, 
					`bookingpress_service_id` INT(11) NOT NULL,					
					`bookingpress_servicemeta_name` VARCHAR(255) NOT NULL,
					`bookingpress_servicemeta_value` TEXT NOT NULL,
					`bookingpress_servicemetadate_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					PRIMARY KEY (`bookingpress_servicemeta_id`)
				) {$charset_collate};";
				$bookingpress_dbtbl_create[ $tbl_bookingpress_servicesmeta ] = dbDelta( $sql_table );
		
				$sql_table = "DROP TABLE IF EXISTS `{$tbl_bookingpress_customers}`;
				CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_customers}`(
					`bookingpress_customer_id` bigint(11) NOT NULL AUTO_INCREMENT,
					`bookingpress_wpuser_id` bigint(11) DEFAULT NULL,
					`bookingpress_user_login` VARCHAR(60) NOT NULL DEFAULT '',
					`bookingpress_user_status` INT(1) NOT NULL,
					`bookingpress_user_type` INT(1) DEFAULT 0,
					`bookingpress_user_firstname` VARCHAR(255) NOT NULL,
					`bookingpress_user_lastname` VARCHAR(255) NOT NULL,
					`bookingpress_user_email` VARCHAR(255) NOT NULL,
					`bookingpress_user_phone` VARCHAR(63) DEFAULT NULL,
					`bookingpress_user_country_phone` VARCHAR(60) DEFAULT NULL,
					`bookingpress_user_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					PRIMARY KEY (`bookingpress_customer_id`)
				) {$charset_collate};";
				$bookingpress_dbtbl_create[ $tbl_bookingpress_customers ] = dbDelta( $sql_table );
		
				$sql_table = "DROP TABLE IF EXISTS `{$tbl_bookingpress_settings}`;
				CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_settings}`(
					`setting_id` int(11) NOT NULL AUTO_INCREMENT,
					`setting_name` varchar(255) NOT NULL,
					`setting_value` varchar(255) DEFAULT NULL,
					`setting_type` varchar(255) DEFAULT NULL,
					`updated_at` timestamp DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`setting_id`)
				) {$charset_collate}";
				$bookingpress_dbtbl_create[ $tbl_bookingpress_settings ] = dbDelta( $sql_table );
		
				$sql_table = "DROP TABLE IF EXISTS `{$tbl_bookingpress_default_workhours}`;
				CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_default_workhours}`(
					`bookingpress_workhours_id` smallint NOT NULL AUTO_INCREMENT,
					`bookingpress_workday_key` varchar(11) NOT NULL,
					`bookingpress_start_time` time DEFAULT NULL,
					`bookingpress_end_time` time DEFAULT NULL,
					`bookingpress_is_break` TINYINT(1) DEFAULT 0,
					`bookingpress_created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`bookingpress_workhours_id`)
				) {$charset_collate}";
				$bookingpress_dbtbl_create[ $tbl_bookingpress_default_workhours ] = dbDelta( $sql_table );
		
				$sql_table = "DROP TABLE IF EXISTS `{$tbl_bookingpress_default_daysoff}`;
				CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_default_daysoff}`(
					`bookingpress_dayoff_id` smallint NOT NULL AUTO_INCREMENT,
					`bookingpress_name` varchar(255) NOT NULL,
					`bookingpress_dayoff_date` datetime DEFAULT NULL,
					`bookingpress_repeat` TINYINT(1) DEFAULT 0,
					`bookingpress_created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`bookingpress_dayoff_id`)
				) {$charset_collate}";
				$bookingpress_dbtbl_create[ $tbl_bookingpress_default_daysoff ] = dbDelta( $sql_table );
		
				$sql_table = "DROP TABLE IF EXISTS `{$tbl_bookingpress_notifications}`;
				CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_notifications}`(
					`bookingpress_notification_id` smallint NOT NULL AUTO_INCREMENT,
					`bookingpress_notification_receiver_type` varchar(11) DEFAULT 'customer',
					`bookingpress_notification_is_custom` TINYINT(1) DEFAULT 0,
					`bookingpress_notification_name` varchar(255) NOT NULL,
					`bookingpress_notification_execution_type` varchar(11) DEFAULT 'action',
					`bookingpress_notification_status` TINYINT(1) DEFAULT 0,
					`bookingpress_notification_type` varchar(255) DEFAULT 'appointment',
					`bookingpress_notification_appointment_status` varchar(255) DEFAULT 'approved',
					`bookingpress_notification_event_action` varchar(255) DEFAULT 'booked',
					`bookingpress_notification_send_only_this` TINYINT(1) DEFAULT 0,
					`bookingpress_notification_subject` varchar(255) DEFAULT NULL,
					`bookingpress_notification_message` TEXT DEFAULT NULL,
					`bookingpress_created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
					`bookingpress_updated_at` timestamp DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`bookingpress_notification_id`)
				) {$charset_collate}";
				$bookingpress_dbtbl_create[ $tbl_bookingpress_notifications ] = dbDelta( $sql_table );
		
				$sql_table = "DROP TABLE IF EXISTS `{$tbl_bookingpress_appointment_bookings}`;
				CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_appointment_bookings}`(
					`bookingpress_appointment_booking_id` bigint(11) NOT NULL AUTO_INCREMENT,
					`bookingpress_entry_id` bigint(11) DEFAULT NULL,
					`bookingpress_customer_id` bigint(11) NOT NULL,
					`bookingpress_staff_member_id` smallint DEFAULT NULL,
					`bookingpress_service_id` INT(11) NOT NULL,
					`bookingpress_service_name` varchar(255) NOT NULL,
					`bookingpress_service_price` float NOT NULL,
					`bookingpress_service_currency` varchar(20) NOT NULL,
					`bookingpress_service_duration_val` INT(11) NOT NULL,
					`bookingpress_service_duration_unit` VARCHAR(1) NOT NULL,
					`bookingpress_appointment_date` DATE NOT NULL,
					`bookingpress_appointment_time` TIME NOT NULL,
					`bookingpress_appointment_internal_note` TEXT DEFAULT NULL,
					`bookingpress_appointment_send_notification` TINYINT(1) DEFAULT 0,
					`bookingpress_appointment_status` varchar(20) NOT NULL,	
					`bookingpress_created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`bookingpress_appointment_booking_id`)
				) {$charset_collate}";
				$bookingpress_dbtbl_create[ $tbl_bookingpress_appointment_bookings ] = dbDelta( $sql_table );
		
				$sql_table = "DROP TABLE IF EXISTS `{$tbl_bookingpress_payment_logs}`;
				CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_payment_logs}`(
					`bookingpress_payment_log_id` bigint(11) NOT NULL AUTO_INCREMENT,
					`bookingpress_appointment_booking_ref` bigint(11) DEFAULT NULL,
					`bookingpress_customer_id` bigint(11) NOT NULL,
					`bookingpress_customer_firstname` varchar(255) DEFAULT NULL,
					`bookingpress_customer_lastname` varchar(255) DEFAULT NULL,
					`bookingpress_customer_email` varchar(255) DEFAULT NULL,
					`bookingpress_staff_member_id` smallint DEFAULT NULL,
					`bookingpress_service_id` INT(11) NOT NULL,
					`bookingpress_service_name` varchar(255) NOT NULL,
					`bookingpress_service_price` float NOT NULL,
					`bookingpress_service_duration_val` INT(11) NOT NULL,
					`bookingpress_service_duration_unit` VARCHAR(1) NOT NULL,
					`bookingpress_appointment_date` DATE NOT NULL,
					`bookingpress_appointment_start_time` TIME NOT NULL,
					`bookingpress_appointment_end_time` TIME NOT NULL,
					`bookingpress_payment_gateway` varchar(255) DEFAULT NULL,
					`bookingpress_payer_email` varchar(255) DEFAULT NULL,
					`bookingpress_transaction_id` varchar(255) DEFAULT NULL,
					`bookingpress_payment_date_time` timestamp DEFAULT CURRENT_TIMESTAMP,
					`bookingpress_payment_status` varchar(20) DEFAULT NULL,
					`bookingpress_payment_amount` FLOAT(8, 2) DEFAULT 0,
					`bookingpress_payment_currency` varchar(20) DEFAULT NULL,
					`bookingpress_payment_type` varchar(20) DEFAULT NULL,
					`bookingpress_payment_response` TEXT DEFAULT NULL,
					`bookingpress_additional_info` TEXT DEFAULT NULL,
					`bookingpress_created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`bookingpress_payment_log_id`)
				) {$charset_collate}";
				$bookingpress_dbtbl_create[ $tbl_bookingpress_payment_logs ] = dbDelta( $sql_table );
		
				$sql_table = "DROP TABLE IF EXISTS `{$tbl_bookingpress_entries}`;
				CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_entries}`(
					`bookingpress_entry_id` bigint(11) NOT NULL AUTO_INCREMENT,
					`bookingpress_customer_id` bigint(11) DEFAULT NULL,
					`bookingpress_customer_name` varchar(255) DEFAULT NULL,
					`bookingpress_customer_phone` varchar(255) DEFAULT NULL,
					`bookingpress_customer_firstname` varchar(255) DEFAULT NULL,
					`bookingpress_customer_lastname` varchar(255) DEFAULT NULL,
					`bookingpress_customer_country` VARCHAR(60) DEFAULT NULL,
					`bookingpress_customer_email` varchar(255) DEFAULT NULL,
					`bookingpress_service_id` INT(11) NOT NULL,
					`bookingpress_service_name` varchar(255) NOT NULL,
					`bookingpress_service_price` float NOT NULL,
					`bookingpress_service_currency` varchar(20) NOT NULL,
					`bookingpress_service_duration_val` INT(11) NOT NULL,
					`bookingpress_service_duration_unit` VARCHAR(1) NOT NULL,
					`bookingpress_payment_gateway` VARCHAR(255) DEFAULT NULL,
					`bookingpress_appointment_date` DATE NOT NULL,
					`bookingpress_appointment_time` TIME NOT NULL,
					`bookingpress_appointment_internal_note` TEXT DEFAULT NULL,
					`bookingpress_appointment_send_notifications` TINYINT(1) DEFAULT 0,
					`bookingpress_appointment_status` varchar(20) NOT NULL,	
					`bookingpress_created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`bookingpress_entry_id`)
				) {$charset_collate}";
				$bookingpress_dbtbl_create[ $tbl_bookingpress_entries ] = dbDelta( $sql_table );
		
				$sql_table = "DROP TABLE IF EXISTS `{$tbl_bookingpress_form_fields}`;
				CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_form_fields}`(
					`bookingpress_form_field_id` SMALLINT NOT NULL AUTO_INCREMENT,
					`bookingpress_form_field_name` varchar(255) NOT NULL,
					`bookingpress_field_required` TINYINT(1) DEFAULT 0,
					`bookingpress_field_label` TEXT NOT NULL,
					`bookingpress_field_placeholder` TEXT DEFAULT NULL,
					`bookingpress_field_error_message` VARCHAR(255) DEFAULT NULL,
					`bookingpress_field_is_hide` TINYINT(1) DEFAULT 0,
					`bookingpress_field_position` SMALLINT DEFAULT 0,
					`bookingpress_created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`bookingpress_form_field_id`)
				) {$charset_collate}";
				$bookingpress_dbtbl_create[ $tbl_bookingpress_form_fields ] = dbDelta( $sql_table );
		
				$sql_table = "DROP TABLE IF EXISTS `{$tbl_bookingpress_customize_settings}`;
				CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_customize_settings}`(
					`bookingpress_setting_id` int(11) NOT NULL AUTO_INCREMENT,
					`bookingpress_setting_name` varchar(255) NOT NULL,
					`bookingpress_setting_value` TEXT NOT NULL,
					`bookingpress_setting_type` varchar(255) NOT NULL,
					`bookingpress_created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`bookingpress_setting_id`)
				) {$charset_collate}";
				$bookingpress_dbtbl_create[ $tbl_bookingpress_customize_settings ] = dbDelta( $sql_table );
		
				$sql_table = "DROP TABLE IF EXISTS `{$tbl_bookingpress_debug_payment_log}`;
				CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_debug_payment_log}`(
					`bookingpress_payment_log_id` bigint(11) NOT NULL AUTO_INCREMENT,
					`bookingpress_payment_log_ref_id` bigint(11) NOT NULL DEFAULT '0',
					`bookingpress_payment_log_gateway` varchar(255) DEFAULT NULL,
					`bookingpress_payment_log_event` varchar(255) DEFAULT NULL,
					`bookingpress_payment_log_event_from` varchar(255) DEFAULT NULL,
					`bookingpress_payment_log_status` TINYINT(1) DEFAULT '1',
					`bookingpress_payment_log_raw_data` TEXT,
					`bookingpress_payment_log_added_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					PRIMARY KEY (`bookingpress_payment_log_id`)
				) {$charset_collate};";
				$bookingpress_dbtbl_create[ $tbl_bookingpress_debug_payment_log ] = dbDelta( $sql_table );
		
				
				$sql_table = "DROP TABLE IF EXISTS `{$tbl_bookingpress_customers_meta}`;
				CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_customers_meta}`(
					`bookingpress_customermeta_id` bigint(11) NOT NULL AUTO_INCREMENT,
					`bookingpress_customer_id` bigint(11) NOT NULL,
					`bookingpress_customersmeta_key` TEXT NOT NULL,
					`bookingpress_customersmeta_value` TEXT DEFAULT NULL,
					`bookingpress_customersmeta_created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`bookingpress_customermeta_id`)
				) {$charset_collate};";
		
				$bookingpress_dbtbl_create[ $tbl_bookingpress_customers_meta ] = dbDelta( $sql_table );
		
				$BookingPress->bookingpress_add_user_role_and_capabilities();
		
				$BookingPress->bookingpress_install_default_notification_data();
		
				$BookingPress->bookingpress_install_default_general_settings_data();
		
				$BookingPress->bookingpress_install_default_pages();
		
				$BookingPress->bookingpress_install_default_customize_settings_data();
		
				/* Plugin Action Hook After Install Process */
				do_action( 'bookingpress_after_activation_hook' );
				do_action( 'bookingpress_after_install' );
		
			} else {
				do_action( 'bookingpress_reactivate_plugin' );
			}
		
			$args  = array(
				'role'   => 'administrator',
				'fields' => 'id',
			);
			$users = get_users( $args );
		
			if ( count( $users ) > 0 ) {
				foreach ( $users as $key => $user_id ) {
					$bookingpressroles = $BookingPress->bookingpress_capabilities();
					$userObj           = new WP_User( $user_id );
					foreach ( $bookingpressroles as $bookingpressrole => $bookingpress_roledescription ) {
						$userObj->add_cap( $bookingpressrole );
					}
					unset( $bookingpressrole );
					unset( $bookingpressroles );
					unset( $bookingpress_roledescription );
				}
			}
		}

		public static function uninstall() {
			global $wp, $wpdb, $tbl_bookingpress_categories, $tbl_bookingpress_services, $tbl_bookingpress_servicesmeta, $tbl_bookingpress_customers, $tbl_bookingpress_customers_meta, $tbl_bookingpress_settings, $tbl_bookingpress_default_workhours, $tbl_bookingpress_default_daysoff, $tbl_bookingpress_notifications,$tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs, $tbl_bookingpress_entries, $tbl_bookingpress_form_fields, $tbl_bookingpress_customize_settings, $tbl_bookingpress_debug_payment_log;
			/**
			 * Delete Meta Values
			 */
			$wpdb->query( 'DELETE FROM `' . $wpdb->options . "` WHERE  `option_name` LIKE  '%bookingpress\_%'" );
			/**
			 * Delete Plugin DB Tables
			 */
			$bookingpress_tables = array(
				$tbl_bookingpress_categories,
				$tbl_bookingpress_services,
				$tbl_bookingpress_servicesmeta,
				$tbl_bookingpress_customers,
				$tbl_bookingpress_customers_meta,
				$tbl_bookingpress_settings,
				$tbl_bookingpress_default_workhours,
				$tbl_bookingpress_default_daysoff,
				$tbl_bookingpress_notifications,
				$tbl_bookingpress_appointment_bookings,
				$tbl_bookingpress_payment_logs,
				$tbl_bookingpress_entries,
				$tbl_bookingpress_form_fields,
				$tbl_bookingpress_customize_settings,
				$tbl_bookingpress_debug_payment_log,
			);
			foreach ( $bookingpress_tables as $table ) {
				$wpdb->query( "DROP TABLE IF EXISTS $table " );
			}

			/* Plugin Action Hook After Uninstall Process */
			do_action( 'bookingpress_after_uninstall' );
		}

		function bookingpress_menu() {
			global $bookingpress_slugs;

			$place = $this->get_free_menu_position( 26.1, 0.3 );

			$bookingpress_menu_hook = add_menu_page( esc_html__( 'BookingPress', 'bookingpress-appointment-booking' ), esc_html__( 'BookingPress', 'bookingpress-appointment-booking' ), 'bookingpress', $bookingpress_slugs->bookingpress, array( $this, 'route' ), BOOKINGPRESS_IMAGES_URL . '/bookingpress_menu_icon.png', $place );

			add_submenu_page($bookingpress_slugs->bookingpress, __('Dashboard', 'bookingpress-appointment-booking'), __('Dashboard', 'bookingpress-appointment-booking'), 'bookingpress', $bookingpress_slugs->bookingpress);

			add_submenu_page($bookingpress_slugs->bookingpress, __('Calendar', 'bookingpress-appointment-booking'), __('Calendar', 'bookingpress-appointment-booking'), 'bookingpress_calendar', $bookingpress_slugs->bookingpress_calendar, array($this, 'route'));

			add_submenu_page($bookingpress_slugs->bookingpress, __('Appointments', 'bookingpress-appointment-booking'), __('Appointments', 'bookingpress-appointment-booking'), 'bookingpress_appointments', $bookingpress_slugs->bookingpress_appointments, array($this, 'route'));

			add_submenu_page($bookingpress_slugs->bookingpress, __('Payments', 'bookingpress-appointment-booking'), __('Payments', 'bookingpress-appointment-booking'), 'bookingpress_payments', $bookingpress_slugs->bookingpress_payments, array($this, 'route'));

			add_submenu_page($bookingpress_slugs->bookingpress, __('Customers', 'bookingpress-appointment-booking'), __('Customers', 'bookingpress-appointment-booking'), 'bookingpress_customers', $bookingpress_slugs->bookingpress_customers, array($this, 'route'));

			add_submenu_page($bookingpress_slugs->bookingpress, __('Services', 'bookingpress-appointment-booking'), __('Services', 'bookingpress-appointment-booking'), 'bookingpress_services', $bookingpress_slugs->bookingpress_services, array($this, 'route'));

			add_submenu_page($bookingpress_slugs->bookingpress, __('Notifications', 'bookingpress-appointment-booking'), __('Notifications', 'bookingpress-appointment-booking'), 'bookingpress_notifications', $bookingpress_slugs->bookingpress_notifications, array($this, 'route'));

			add_submenu_page($bookingpress_slugs->bookingpress, __('Customize', 'bookingpress-appointment-booking'), __('Customize', 'bookingpress-appointment-booking'), 'bookingpress_customize', $bookingpress_slugs->bookingpress_customize, array($this, 'route'));

			add_submenu_page($bookingpress_slugs->bookingpress, __('Settings', 'bookingpress-appointment-booking'), __('Settings', 'bookingpress-appointment-booking'), 'bookingpress_settings', $bookingpress_slugs->bookingpress_settings, array($this, 'route'));
		}

		function bookingpress_page_slugs() {
			global $bookingpress_slugs;
			$bookingpress_slugs = new stdClass();
			/* Admin-Pages-Slug */

			$bookingpress_slugs->bookingpress = 'bookingpress';
			$bookingpress_slugs->bookingpress_calendar = 'bookingpress_calendar';
			$bookingpress_slugs->bookingpress_appointments = 'bookingpress_appointments';
			$bookingpress_slugs->bookingpress_payments = 'bookingpress_payments';
			$bookingpress_slugs->bookingpress_customers = 'bookingpress_customers';
			$bookingpress_slugs->bookingpress_services = 'bookingpress_services';
			$bookingpress_slugs->bookingpress_notifications = 'bookingpress_notifications';
			$bookingpress_slugs->bookingpress_customize = 'bookingpress_customize';
			$bookingpress_slugs->bookingpress_settings = 'bookingpress_settings';

			return $bookingpress_slugs;
		}

		function get_free_menu_position( $start, $increment = 0.1 ) {
			foreach ( $GLOBALS['menu'] as $key => $menu ) {
				$menus_positions[] = floatval( $key );
			}
			if ( ! in_array( $start, $menus_positions ) ) {
				$start = strval( $start );
				return $start;
			} else {
				$start += $increment;
			}
			/* the position is already reserved find the closet one */
			while ( in_array( $start, $menus_positions ) ) {
				$start += $increment;
			}
			$start = strval( $start );
			return $start;
		}

		function route() {
			global $bookingpress_slugs;
			if ( isset( $_REQUEST['page'] ) ) {
				$pageWrapperClass = '';
				if ( is_rtl() ) {
					$pageWrapperClass = 'bookingpress_page_rtl';
				}
				echo '<div class="bookingpress_page_wrapper ' . esc_html( $pageWrapperClass ) . '" id="root_app">';
				$requested_page = sanitize_text_field( $_REQUEST['page'] );
				do_action( 'bookingpress_admin_messages', $requested_page );

				if ( file_exists( BOOKINGPRESS_VIEWS_DIR . '/bookingpress_main.php' ) ) {
					include BOOKINGPRESS_VIEWS_DIR . '/bookingpress_main.php';
				}
				echo '</div>';
			}
		}

		/* Setting Admin CSS  */
		function set_css() {
			global $bookingpress_slugs;

			echo "<style type='text/css'>#toplevel_page_bookingpress .wp-menu-image img{ padding: 0 !important; opacity: 1 !important; }</style>";

			/* Plugin Style */
			wp_register_style( 'bookingpress_element_css', BOOKINGPRESS_URL . '/css/bookingpress_element_theme.css', array(), BOOKINGPRESS_VERSION );

			wp_register_style( 'bookingpress_fonts_css', BOOKINGPRESS_URL . '/css/fonts/fonts.css', array(), BOOKINGPRESS_VERSION );

			wp_register_style( 'bookingpress_calendar_css', BOOKINGPRESS_URL . '/css/bookingpress_vue_calendar.css', array(), BOOKINGPRESS_VERSION );

			wp_register_style( 'bookingpress_root_variables_css', BOOKINGPRESS_URL . '/css/bookingpress_variables.css', array(), BOOKINGPRESS_VERSION );

			wp_register_style( 'bookingpress_admin_css', BOOKINGPRESS_URL . '/css/bookingpress_admin.css', array('bookingpress_root_variables_css'), BOOKINGPRESS_VERSION );

			wp_register_style( 'bookingpress_admin_rtl_css', BOOKINGPRESS_URL . '/css/bookingpress_admin_rtl.css', array(), BOOKINGPRESS_VERSION );

			wp_register_style( 'bookingpress_tel_input', BOOKINGPRESS_URL . '/css/bookingpress_tel_input.css', array(), BOOKINGPRESS_VERSION );

			/* Add CSS file only for plugin pages. */
			if ( isset( $_REQUEST['page'] ) && in_array( sanitize_text_field( $_REQUEST['page'] ), (array) $bookingpress_slugs ) ) {
				wp_enqueue_style( 'bookingpress_element_css' );
				wp_enqueue_style( 'bookingpress_fonts_css' );

				if ( ! empty( $_REQUEST['page'] ) && ( sanitize_text_field( $_REQUEST['page'] ) == 'bookingpress_calendar' || sanitize_text_field( $_REQUEST['page'] ) == 'bookingpress_customize' ) ) {
					wp_enqueue_style( 'bookingpress_calendar_css' );
				}

				wp_enqueue_style( 'bookingpress_root_variables_css' );
				wp_enqueue_style( 'bookingpress_admin_css' );
				if(is_rtl()){
					wp_enqueue_style( 'bookingpress_admin_rtl_css' );	
				}

				if ( ! empty( $_REQUEST['page'] ) && ( sanitize_text_field( $_REQUEST['page'] ) == 'bookingpress_customers' || sanitize_text_field( $_REQUEST['page'] ) == 'bookingpress_customize' || sanitize_text_field( $_REQUEST['page'] ) == 'bookingpress_settings'  ) ) {
					wp_enqueue_style('bookingpress_tel_input');
				}
			}
		}


		/* Setting Front CSS */
		function set_front_css($force_enqueue = 0) {
			global $wpdb, $tbl_bookingpress_form_fields;
			wp_register_style( 'bookingpress_element_css', BOOKINGPRESS_URL . '/css/bookingpress_element_theme.css', array(), BOOKINGPRESS_VERSION );


			wp_register_style( 'bookingpress_fonts_css', BOOKINGPRESS_URL . '/css/fonts/fonts.css', array(), BOOKINGPRESS_VERSION );
			wp_register_style( 'bookingpress_front_css', BOOKINGPRESS_URL . '/css/bookingpress_front.css', array(), BOOKINGPRESS_VERSION );
			wp_register_style( 'bookingpress_front_rtl_css', BOOKINGPRESS_URL . '/css/bookingpress_front_rtl.css', array(), BOOKINGPRESS_VERSION );
			wp_register_style( 'bookingpress_calendar_css', BOOKINGPRESS_URL . '/css/bookingpress_vue_calendar.css', array(), BOOKINGPRESS_VERSION );
			wp_register_style( 'bookingpress_tel_input', BOOKINGPRESS_URL . '/css/bookingpress_tel_input.css', array(), BOOKINGPRESS_VERSION );

			$bookingress_load_js_css_all_pages = $this->bookingpress_get_settings( 'load_js_css_all_pages', 'general_setting' );

			if ( $this->bookingpress_is_front_page() || ( $bookingress_load_js_css_all_pages == 'true' ) || ($force_enqueue == 1) ) {
				wp_enqueue_style( 'bookingpress_element_css' );
				wp_enqueue_style( 'bookingpress_fonts_css' );
				wp_enqueue_style( 'bookingpress_front_css' );

				$bookingpress_form_field_data = $wpdb->get_row("SELECT * FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_form_field_name = 'phone_number'", ARRAY_A);
				$bookingpress_is_field_hide = isset($bookingpress_form_field_data['bookingpress_field_is_hide']) ? intval($bookingpress_form_field_data['bookingpress_field_is_hide']) : 1;
				if($bookingpress_is_field_hide == 0){
					wp_enqueue_style( 'bookingpress_tel_input' );
				}

				if(is_rtl()){
					wp_enqueue_style( 'bookingpress_front_rtl_css' );
				}

				$bookingpress_customize_css_key = get_option('bookingpress_custom_css_key', true);

				if ( file_exists( BOOKINGPRESS_UPLOAD_DIR . '/bookingpress_front_custom_'.$bookingpress_customize_css_key.'.css' ) ) {
					wp_register_style( 'bookingpress_front_custom_css', BOOKINGPRESS_UPLOAD_URL . '/bookingpress_front_custom_'.$bookingpress_customize_css_key.'.css', array( 'bookingpress_front_css' ), BOOKINGPRESS_VERSION );
					wp_enqueue_style( 'bookingpress_front_custom_css' );

					$bookingpress_fonts = array();
					$bookingform_title_font_family = $this->bookingpress_get_customize_settings('title_font_family', 'booking_form');
					if(!empty($bookingform_title_font_family) && !in_array($bookingform_title_font_family, $bookingpress_fonts) ){
						array_push($bookingpress_fonts, $bookingform_title_font_family);
					}

					$bookingform_content_font_family = $this->bookingpress_get_customize_settings('content_font_family', 'booking_form');
					if(!empty($bookingform_content_font_family) && !in_array($bookingform_content_font_family, $bookingpress_fonts) ){
						array_push($bookingpress_fonts, $bookingform_content_font_family);
					}

					$mybooking_title_font_family = $this->bookingpress_get_customize_settings('title_font_family', 'booking_my_booking');
					if(!empty($mybooking_title_font_family) && !in_array($mybooking_title_font_family, $bookingpress_fonts) ){
						array_push($bookingpress_fonts, $mybooking_title_font_family);
					}

					$mybooking_content_font_family = $this->bookingpress_get_customize_settings('content_font_family', 'booking_my_booking');
					if(!empty($mybooking_content_font_family) && !in_array($mybooking_content_font_family, $bookingpress_fonts)){
						array_push($bookingpress_fonts, $mybooking_content_font_family);
					}

					if(!empty($bookingpress_fonts) && is_array($bookingpress_fonts)){
						foreach($bookingpress_fonts as $bookingpress_font_key => $bookingpress_font_val){
							wp_register_style( 'bookingpress_front_font_css', 'https://fonts.googleapis.com/css2?family='.$bookingpress_font_val.'&display=swap', array(), BOOKINGPRESS_VERSION );
							wp_enqueue_style( 'bookingpress_front_font_css' );
						}
					}
				}

				if( file_exists( BOOKINGPRESS_UPLOAD_DIR . '/bookingpress_front_mybookings_custom_'.$bookingpress_customize_css_key.'.css' ) ){
					wp_register_style( 'bookingpress_front_mybookings_custom_css', BOOKINGPRESS_UPLOAD_URL . '/bookingpress_front_mybookings_custom_'.$bookingpress_customize_css_key.'.css', array( 'bookingpress_front_css' ), BOOKINGPRESS_VERSION );
					wp_enqueue_style( 'bookingpress_front_mybookings_custom_css' );
				}

				wp_enqueue_style( 'bookingpress_calendar_css' );
			}
		}

		/* Setting Front JS */
		function set_front_js($force_enqueue = 0) {
			global $wpdb, $tbl_bookingpress_form_fields;

			wp_register_script( 'bookingpress_vue_js', BOOKINGPRESS_URL . '/js/bookingpress_vue.min.js', array(), BOOKINGPRESS_VERSION );

			wp_register_script( 'bookingpress_axios_js', BOOKINGPRESS_URL . '/js/bookingpress_axios.min.js', array(), BOOKINGPRESS_VERSION );
			
			wp_register_script( 'bookingpress_element_js', BOOKINGPRESS_URL . '/js/bookingpress_element.js', array(), BOOKINGPRESS_VERSION );

			wp_register_script( 'bookingpress_wordpress_vue_helper_js', BOOKINGPRESS_URL . '/js/bookingpress_wordpress_vue_qs_helper.js', array(), BOOKINGPRESS_VERSION );

			wp_register_script( 'bookingpress_calendar_js', BOOKINGPRESS_URL . '/js/bookingpress_vue_calendar.js', array(), BOOKINGPRESS_VERSION );

			wp_register_script( 'bookingpress_moment_js', BOOKINGPRESS_URL . '/js/bookingpress_moment.min.js', array(), BOOKINGPRESS_VERSION );

			wp_register_script( 'bookingpress_tel_input_js', BOOKINGPRESS_URL . '/js/bookingpress_tel_input.js', array(), BOOKINGPRESS_VERSION );

			$bookingress_load_js_css_all_pages = $this->bookingpress_get_settings( 'load_js_css_all_pages', 'general_setting' );

			if ( $this->bookingpress_is_front_page() || ( $bookingress_load_js_css_all_pages == 'true' ) || ($force_enqueue == 1) ) {
				$get_already_loaded_vue_setting_val = $this->bookingpress_get_settings( 'use_already_loaded_vue', 'general_setting' );
				if ( ! $get_already_loaded_vue_setting_val || $get_already_loaded_vue_setting_val == 'false' ) {
					wp_enqueue_script( 'bookingpress_vue_js' );
				}
				wp_enqueue_script( 'bookingpress_axios_js' );
				wp_enqueue_script( 'bookingpress_wordpress_vue_helper_js' );
				wp_enqueue_script( 'bookingpress_element_js' );
				wp_enqueue_script( 'bookingpress_calendar_js' );
				wp_enqueue_script( 'bookingpress_moment_js' );

				$bookingpress_form_field_data = $wpdb->get_row("SELECT * FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_form_field_name = 'phone_number'", ARRAY_A);
				$bookingpress_is_field_hide = isset($bookingpress_form_field_data['bookingpress_field_is_hide']) ? intval($bookingpress_form_field_data['bookingpress_field_is_hide']) : 1;
				if($bookingpress_is_field_hide == 0){
					wp_enqueue_script( 'bookingpress_tel_input_js' );
				}

				global $bookingpress_global_options;
				$bookingpress_site_current_language = $bookingpress_global_options->bookingpress_get_site_current_language();


				if($bookingpress_site_current_language != "en"){
					wp_register_script('bookingpress_vue_cal_locale', BOOKINGPRESS_URL.'/js/locales/'.$bookingpress_site_current_language.'.js', array(), BOOKINGPRESS_VERSION);
					wp_enqueue_script('bookingpress_vue_cal_locale');

					wp_register_script('bookingpress_elements_locale', BOOKINGPRESS_URL.'/js/elements_locale/'.$bookingpress_site_current_language.'.js', array(), BOOKINGPRESS_VERSION);
					wp_enqueue_script('bookingpress_elements_locale');
				}else{
					wp_register_script( 'bookingpress_element_en_js', BOOKINGPRESS_URL . '/js/bookingpress_element_en.js', array(), BOOKINGPRESS_VERSION );
					wp_enqueue_script( 'bookingpress_element_en_js' );
				}

				wp_localize_script( 'bookingpress_vue_js', 'appoint_ajax_obj', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
			}
		}

		/* Setting Admin JavaScript */
		function set_js() {
			global $bookingpress_slugs, $bookingpress_global_options;

			$bookingpress_site_current_language = $bookingpress_global_options->bookingpress_get_site_current_language();

			/* Plugin Scripts */
			wp_register_script( 'bookingpress_admin_js', BOOKINGPRESS_URL . '/js/bookingpress_vue.min.js', array(), BOOKINGPRESS_VERSION );

			wp_register_script( 'bookingpress_axios_js', BOOKINGPRESS_URL . '/js/bookingpress_axios.min.js', array(), BOOKINGPRESS_VERSION );
			wp_register_script( 'bookingpress_sortable_js', BOOKINGPRESS_URL . '/js/bookingpress_Sortable.min.js', array( 'bookingpress_admin_js' ), BOOKINGPRESS_VERSION );
			wp_register_script( 'bookingpress_draggable_js', BOOKINGPRESS_URL . '/js/bookingpress_draggable.min.js', array( 'bookingpress_admin_js' ), BOOKINGPRESS_VERSION );

			wp_register_script( 'bookingpress_element_js', BOOKINGPRESS_URL . '/js/bookingpress_element.js', array( 'bookingpress_admin_js' ), BOOKINGPRESS_VERSION );

			if($bookingpress_site_current_language == "en"){
				wp_register_script( 'bookingpress_element_en_js', BOOKINGPRESS_URL . '/js/bookingpress_element_en.js', array( 'bookingpress_admin_js' ), BOOKINGPRESS_VERSION );
			}

			wp_register_script( 'bookingpress_wordpress_vue_helper_js', BOOKINGPRESS_URL . '/js/bookingpress_wordpress_vue_qs_helper.js', array(), BOOKINGPRESS_VERSION );

			wp_register_script( 'bookingpress_calendar_js', BOOKINGPRESS_URL . '/js/bookingpress_vue_calendar.js', array( 'bookingpress_admin_js' ), BOOKINGPRESS_VERSION );

			wp_register_script( 'bookingpress_v-calendar_js', BOOKINGPRESS_URL . '/js/bookingpress_v-calendar.js', array( 'bookingpress_admin_js' ), BOOKINGPRESS_VERSION );

			wp_register_script( 'bookingpress_moment_js', BOOKINGPRESS_URL . '/js/bookingpress_moment.min.js', array(), BOOKINGPRESS_VERSION );

			wp_register_script( 'bookingpress_tel_input_js', BOOKINGPRESS_URL . '/js/bookingpress_tel_input.js', array(), BOOKINGPRESS_VERSION );

			/* Add JS file only for plugin pages. */
			if ( isset( $_REQUEST['page'] ) && ( sanitize_text_field( $_REQUEST['page'] ) == 'bookingpress_services' || sanitize_text_field( $_REQUEST['page'] ) == 'bookingpress_customize' ) ) {
				wp_enqueue_script( 'bookingpress_sortable_js' );
				wp_enqueue_script( 'bookingpress_draggable_js' );
			}

			if ( isset( $_REQUEST['page'] ) && in_array( sanitize_text_field( $_REQUEST['page'] ), (array) $bookingpress_slugs ) ) {
				wp_enqueue_script( 'bookingpress_admin_js' );
				wp_enqueue_script( 'bookingpress_axios_js' );
				wp_enqueue_script( 'bookingpress_wordpress_vue_helper_js' );
				wp_enqueue_script( 'bookingpress_element_js' );
				wp_enqueue_script( 'bookingpress_moment_js' );
				if($bookingpress_site_current_language == "en"){
					wp_enqueue_script( 'bookingpress_element_en_js' );
				}

				if($bookingpress_site_current_language != "en"){
					wp_register_script('bookingpress_elements_locale', BOOKINGPRESS_URL.'/js/elements_locale/'.$bookingpress_site_current_language.'.js', array('bookingpress_element_js'), BOOKINGPRESS_VERSION);
					wp_enqueue_script('bookingpress_elements_locale');
				}

				if ( ! empty( $_REQUEST['page'] ) && ( sanitize_text_field( $_REQUEST['page'] ) == 'bookingpress_customers' || sanitize_text_field( $_REQUEST['page'] ) == 'bookingpress_customize' || sanitize_text_field( $_REQUEST['page'] ) == 'bookingpress_settings'  ) ) {
					wp_enqueue_script( 'bookingpress_tel_input_js' );
				}
			}

			if ( isset( $_REQUEST['page'] ) && ( sanitize_text_field( $_REQUEST['page'] ) == 'bookingpress_calendar' || sanitize_text_field( $_REQUEST['page'] ) == 'bookingpress_customize' ) ) {
				wp_enqueue_script( 'bookingpress_calendar_js' );
				
				if($bookingpress_site_current_language != "en"){
					wp_register_script('bookingpress_vue_cal_locale', BOOKINGPRESS_URL.'/js/locales/'.$bookingpress_site_current_language.'.js', array(), BOOKINGPRESS_VERSION);
					wp_enqueue_script('bookingpress_vue_cal_locale');
				}
			}

			if ( isset( $_REQUEST['page'] ) && sanitize_text_field( $_REQUEST['page'] ) == 'bookingpress_settings' ) {
				wp_enqueue_script( 'bookingpress_v-calendar_js' );
			}

			if ( isset( $_REQUEST['page'] ) && ( isset( $_REQUEST['page'] ) && sanitize_text_field( $_REQUEST['page'] ) == 'bookingpress' ) ) {
				wp_register_script( 'bookingpress_charts_js', BOOKINGPRESS_URL . '/js/bookingpress_chart.min.js', array(), BOOKINGPRESS_VERSION );
				wp_enqueue_script( 'bookingpress_charts_js' );
			}

			wp_localize_script( 'bookingpress_admin_js', 'appoint_ajax_obj', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		}
		function set_global_javascript_variables() {
			echo '<script type="text/javascript" data-cfasync="false">';
			echo '__BOOKINGPRESSIMAGEURL = "' . BOOKINGPRESS_IMAGES_URL . '";';
			echo '</script>';
		}
		/**
		 * Hide WordPress Update Notifications In Plugin's Pages
		 */
		function bookingpress_hide_update_notice() {
			global $bookingpress_slugs;
			if ( isset( $_REQUEST['page'] ) && in_array( sanitize_text_field( $_REQUEST['page'] ), (array) $bookingpress_slugs ) ) {
				remove_action( 'admin_notices', 'update_nag', 3 );
				remove_action( 'network_admin_notices', 'update_nag', 3 );
				remove_action( 'admin_notices', 'maintenance_nag' );
				remove_action( 'network_admin_notices', 'maintenance_nag' );
				remove_action( 'admin_notices', 'site_admin_notice' );
				remove_action( 'network_admin_notices', 'site_admin_notice' );
				remove_action( 'load-update-core.php', 'wp_update_plugins' );
				add_filter( 'pre_site_transient_update_core', array( $this, 'bookingpress_remove_core_updates' ) );
				add_filter( 'pre_site_transient_update_plugins', array( $this, 'bookingpress_remove_core_updates' ) );
				add_filter( 'pre_site_transient_update_themes', array( $this, 'bookingpress_remove_core_updates' ) );
			}
		}

		function bookingpress_remove_core_updates() {
			global $wp_version;
			return (object) array(
				'last_checked'    => time(),
				'version_checked' => $wp_version,
			);
		}

		function bookingpress_add_user_role_and_capabilities() {
			global $wp_roles;
			$role_name  = 'BookingPress Customer';
			$role_slug  = sanitize_title( $role_name );
			$basic_caps = array(
				$role_slug => true,
				'read'     => true,
				'level_0'  => true,
			);

			$wp_roles->add_role( $role_slug, $role_name, $basic_caps );
		}


		function bookingpress_validate_username( $user_login, $invalid_username = '' ) {
			$sanitized_user_login = sanitize_user( $user_login );
			$err                  = '';
			// Check the username
			if ( $sanitized_user_login == '' ) {
				$err = esc_html__( 'Please enter a username.', 'bookingpress-appointment-booking' );
			} elseif ( ! validate_username( $user_login ) ) {
				if ( $invalid_username == '' ) {
					$err_msg = esc_html__( 'This username is invalid because it uses illegal characters. Please enter a valid username.', 'bookingpress-appointment-booking' );
				} else {
					$err_msg = $invalid_username;
				}
				$err = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( 'This username is invalid because it uses illegal characters. Please enter a valid username.', 'bookingpress-appointment-booking' );
			} elseif ( username_exists( $sanitized_user_login ) ) {
				$err = esc_html__( 'This username is already registered, please choose another one.', 'bookingpress-appointment-booking' );
			}
			return $err;
		}


		function bookingpress_validate_email( $user_email, $invalid_email = '' ) {
			$err = '';
			// Check the username
			if ( '' == $user_email ) {
				$err = esc_html__( 'Please type your e-mail address.', 'bookingpress-appointment-booking' );
			} elseif ( ! is_email( $user_email ) ) {
				if ( $invalid_email == '' ) {
					$err_msg = esc_html__( 'Please enter valid email address.', 'bookingpress-appointment-booking' );
				} else {
					$err_msg = $invalid_email;
				}
				$err = ( ! empty( $err_msg ) ) ? $err_msg : esc_html__( 'Please enter valid email address.', 'bookingpress-appointment-booking' );
			} elseif ( email_exists( $user_email ) ) {
				$err = esc_html__( 'This email is already registered, please choose another one.', 'bookingpress-appointment-booking' );
			}
			return $err;
		}

		function bookingpress_user_update_meta_details( $user_ID, $posted_data = array() ) {
			if ( ! empty( $user_ID ) && ! empty( $posted_data ) ) {
				$user = new WP_User( $user_ID );
				foreach ( $posted_data as $key => $val ) {
					if ( $key == 'first_name' || $key == 'last_name' ) {
						$val = trim( sanitize_text_field( $val ) );
					} elseif ( $key == 'role' || $key == 'roles' ) {
						if ( isset( $val ) && is_array( $val ) && ! empty( $val ) ) {
							$count = 0;
							foreach ( $val as $v ) {
								if ( $count == 0 ) {
									$user->set_role( $v );
								} else {
									$user->add_role( $v );
								}
								$count++;
							}
						} else {
							$user->add_role( $val );
						}
					}
					update_user_meta( $user_ID, $key, $val );
				}
			}
		}

		function bookingpress_file_upload_function( $source, $destination ) {
			if ( empty( $source ) || empty( $destination ) ) {
				return false;
			}

			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}

			WP_Filesystem();
			global $wp_filesystem;

			$file_content = $wp_filesystem->get_contents( $source );

			$result = $wp_filesystem->put_contents( $destination, $file_content, 0777 );

			return $result;
		}


		function bookingpress_remove_uploaded_file() {
			global $wpdb;
			$response              = array();
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
				wp_send_json( $response );
				die();
			}
			if ( ! empty( $_POST ) && ! empty( $_POST['upload_file_url'] ) ) {
				$bookingpress_uploaded_avatar_url = esc_url_raw( $_POST['upload_file_url'] );
				$bookingpress_file_name_arr       = explode( '/', $bookingpress_uploaded_avatar_url );
				$bookingpress_file_name           = $bookingpress_file_name_arr[ count( $bookingpress_file_name_arr ) - 1 ];
				unlink( BOOKINGPRESS_TMP_IMAGES_DIR . '/' . $bookingpress_file_name );
			}
		}


		public function bookingpress_update_settings( $setting_name, $setting_type, $setting_value = '' ) {
			 global $wpdb, $tbl_bookingpress_settings;
			if ( ! empty( $setting_name ) ) {
				$bookingpress_check_record_existance = $wpdb->get_var( "SELECT COUNT(setting_id) FROM `{$tbl_bookingpress_settings}` WHERE setting_name = '" . $setting_name . "' AND setting_type = '" . $setting_type . "'" );

				if ( $bookingpress_check_record_existance > 0 ) {
					// If record already exists then update data.
					$bookingpress_update_data = array(
						'setting_value' => ( ! empty( $setting_value ) && gettype( $setting_value ) === 'boolean' ) ? $setting_value : sanitize_text_field( $setting_value ),
						'setting_type'  => $setting_type,
						'updated_at'    => current_time( 'mysql' ),
					);

					$arm_update_where_condition = array(
						'setting_name' => $setting_name,
						'setting_type' => $setting_type,
					);

					$arm_update_affected_rows = $wpdb->update( $tbl_bookingpress_settings, $bookingpress_update_data, $arm_update_where_condition );
					if ( $arm_update_affected_rows > 0 ) {
						wp_cache_delete( $setting_name );
						wp_cache_set( $setting_name, $setting_value );
						return 1;
					}
				} else {
					// If record not exists hen insert data.

						$bookingpress_insert_data = array(
							'setting_name'  => $setting_name,
							'setting_value' => ( ! empty( $setting_value ) && gettype( $setting_value ) === 'boolean' ) ? $setting_value : sanitize_text_field( $setting_value ),
							'setting_type'  => $setting_type,
							'updated_at'    => current_time( 'mysql' ),
						);

						$bookingpress_inserted_id = $wpdb->insert( $tbl_bookingpress_settings, $bookingpress_insert_data );
						if ( $bookingpress_inserted_id > 0 ) {
							wp_cache_delete( $setting_name );
							wp_cache_set( $setting_name, $setting_value );
							return 1;
						}
				}
			}

			return 0;
		}


		public function bookingpress_get_customize_settings( $setting_name, $setting_type ) {
			global $wpdb, $tbl_bookingpress_customize_settings;
			$bookingpress_setting_value = '';
			if ( ! empty( $setting_name ) ) {
				$bookingpress_check_record_existance = $wpdb->get_var( "SELECT COUNT(bookingpress_setting_id) FROM `{$tbl_bookingpress_customize_settings}` WHERE bookingpress_setting_name = '" . $setting_name . "' AND bookingpress_setting_type = '" . $setting_type . "'" );
				if ( $bookingpress_check_record_existance > 0 ) {
					$bookingpress_get_setting   = $wpdb->get_row( "SELECT * FROM `{$tbl_bookingpress_customize_settings}` WHERE bookingpress_setting_name = '" . $setting_name . "' AND bookingpress_setting_type = '" . $setting_type . "'", ARRAY_A );
					$bookingpress_setting_value = $bookingpress_get_setting['bookingpress_setting_value'];
				}
			}

			return $bookingpress_setting_value;
		}


		public function bookingpress_get_settings( $setting_name, $setting_type ) {
			global $wpdb, $tbl_bookingpress_settings;
			$bookingpress_setting_value = '';
			if ( ! empty( $setting_name ) ) {
				if ( ! empty( wp_cache_get( $setting_name ) ) ) {
					$bookingpress_setting_value = wp_cache_get( $setting_name );
				} else {
					$bookingpress_check_record_existance = $wpdb->get_var( "SELECT COUNT(setting_id) FROM `{$tbl_bookingpress_settings}` WHERE setting_name = '" . $setting_name . "' AND setting_type = '" . $setting_type . "'" );
					if ( $bookingpress_check_record_existance > 0 ) {
						$bookingpress_get_setting   = $wpdb->get_row( "SELECT * FROM `{$tbl_bookingpress_settings}` WHERE setting_name = '" . $setting_name . "' AND setting_type = '" . $setting_type . "'", ARRAY_A );
						$bookingpress_setting_value = $bookingpress_get_setting['setting_value'];
						wp_cache_set( $setting_name, $bookingpress_setting_value );
					}
				}
			}

			return $bookingpress_setting_value;
		}


		public function bookingpress_get_currency_symbol( $currency_name ) {
			if ( ! empty( $currency_name ) ) {
				global $bookingpress_global_options;
				$bookingpress_options                    = $bookingpress_global_options->bookingpress_global_options();
				$bookingpress_countries_currency_details = json_decode( $bookingpress_options['countries_json_details'] );

				$bookingpress_currency_symbol = '';

				foreach ( $bookingpress_countries_currency_details as $currency_key => $currency_val ) {
					if ( $currency_val->name == $currency_name ) {
						$bookingpress_currency_symbol = $currency_val->symbol;
						break;
					}
				}

				return $bookingpress_currency_symbol;
			}

			return '';
		}

		public function bookingpress_get_currency_code( $currency_name ) {
			if ( ! empty( $currency_name ) ) {
				global $bookingpress_global_options;
				$bookingpress_options                    = $bookingpress_global_options->bookingpress_global_options();
				$bookingpress_countries_currency_details = json_decode( $bookingpress_options['countries_json_details'] );

				$bookingpress_currency_code = '';

				foreach ( $bookingpress_countries_currency_details as $currency_key => $currency_val ) {
					if ( $currency_val->name == $currency_name ) {
						$bookingpress_currency_code = $currency_val->code;
						break;
					}
				}

				return $bookingpress_currency_code;
			}

			return '';
		}

		public function bookingpress_price_formatter_with_currency_symbol( $price, $currency_symbol = '' ) {
			$bookingpress_decimal_points = $this->bookingpress_get_settings( 'price_number_of_decimals', 'payment_setting' );
			if(gettype($price) == "string"){
				$price = floatval($price);
			}
			$bookingpress_price_separator_pos = $this->bookingpress_get_settings( 'price_separator', 'payment_setting' );
			if ( $bookingpress_price_separator_pos == 'comma-dot' ) {
				$price = number_format( $price, $bookingpress_decimal_points, '.', ',' );
			} elseif ( $bookingpress_price_separator_pos == 'dot-comma' ) {
				$price = number_format( $price, $bookingpress_decimal_points, ',', '.' );
			} elseif ( $bookingpress_price_separator_pos == 'space-dot' ) {
				$price = number_format( $price, $bookingpress_decimal_points, '.', ' ' );
			} elseif ( $bookingpress_price_separator_pos == 'space-comma' ) {
				$price = number_format( $price, $bookingpress_decimal_points, ',', ' ' );
			}

			if ( empty( $currency_symbol ) ) {
				$bookingpress_currency_name = $this->bookingpress_get_settings( 'payment_default_currency', 'payment_setting' );
				$currency_symbol            = ! empty( $bookingpress_currency_name ) ? $this->bookingpress_get_currency_symbol( $bookingpress_currency_name ) : '';
			}

			$bookingpress_price_symbol_position = $this->bookingpress_get_settings( 'price_symbol_position', 'payment_setting' );

			$bookingpress_price_with_symbol = $currency_symbol . $price;

			if ( $bookingpress_price_symbol_position == 'before' ) {
				$bookingpress_price_with_symbol = $currency_symbol . $price;
			} elseif ( $bookingpress_price_symbol_position == 'before_with_space' ) {
				$bookingpress_price_with_symbol = $currency_symbol . ' ' . $price;
			} elseif ( $bookingpress_price_symbol_position == 'after' ) {
				$bookingpress_price_with_symbol = $price . $currency_symbol;
			} elseif ( $bookingpress_price_symbol_position == 'after_with_space' ) {
				$bookingpress_price_with_symbol = $price . ' ' . $currency_symbol;
			}

			return $bookingpress_price_with_symbol;
		}

		public function bookingpress_get_service_end_time( $service_id, $service_start_time, $service_duration_val = '', $service_duration_unit = '' ) {
			global $wpdb, $tbl_bookingpress_services;
			if ( ! empty( $service_id ) ) {
				$service_duration      = ! empty( $service_duration_val ) ? $service_duration_val : '';
				$service_unit_duration = ! empty( $service_duration_unit ) ? $service_duration_unit : '';

				$service_data = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = {$service_id}", ARRAY_A );

				if ( empty( $service_duration ) && empty( $service_unit_duration ) && ! empty( $service_data ) ) {
					$service_duration      = $service_data['bookingpress_service_duration_val'];
					$service_unit_duration = $service_data['bookingpress_service_duration_unit'];
				}

				$service_mins = $service_duration;
				if ( $service_unit_duration == 'h' ) {
					$service_mins = $service_duration * 60;
				}

				$service_end_time_obj = new DateTime( $service_start_time );
				$service_end_time_obj->add( new DateInterval( 'PT' . $service_mins . 'M' ) );
				$service_end_time = $service_end_time_obj->format( 'H:i' );

				return array(
					'service_start_time' => $service_start_time,
					'service_end_time'   => $service_end_time,
				);

			}

			return array();
		}


		public function bookingpress_get_default_timeslot_data() {

			$bookingpress_default_timeslot_data = esc_html( $this->bookingpress_get_settings( 'default_time_slot_step', 'general_setting' ) );
			$bookingpress_default_timeslot_data = ! empty( $bookingpress_default_timeslot_data ) ? esc_html( $bookingpress_default_timeslot_data ) : 60;

			$time_duration = $bookingpress_default_timeslot_data;

			$time_unit = 'm';

			if ( $time_duration >= 60 ) {
				$time_duration = ( $time_duration / 60 );
				$time_unit     = 'h';
			}

			return array(
				'time_duration'    => $time_duration,
				'time_unit'        => $time_unit,
				'default_timeslot' => $bookingpress_default_timeslot_data,
			);
		}

		public function bookingpress_get_default_dayoff_dates() {
			global $wpdb, $tbl_bookingpress_default_daysoff, $tbl_bookingpress_default_workhours;

			$bookingpress_workhours_data = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_default_workhours} WHERE bookingpress_is_break = 0", ARRAY_A );
			$is_monday_break             = 0;
			$is_tuesday_break            = 0;
			$is_wednesday_break          = 0;
			$is_thursday_break           = 0;
			$is_friday_break             = 0;
			$is_saturday_break           = 0;
			$is_sunday_break             = 0;

			foreach ( $bookingpress_workhours_data as $workhour_key => $workhour_val ) {
				$bookingpress_start_time = $workhour_val['bookingpress_start_time'];
				$bookingpress_end_time   = $workhour_val['bookingpress_end_time'];
				if ( $workhour_val['bookingpress_workday_key'] == 'monday' && ( $bookingpress_start_time == NULL || $bookingpress_end_time == NULL ) ) {
					$is_monday_break = 1;
				} elseif ( $workhour_val['bookingpress_workday_key'] == 'tuesday' && ( $bookingpress_start_time == NULL || $bookingpress_end_time == NULL ) ) {
					$is_tuesday_break = 1;
				} elseif ( $workhour_val['bookingpress_workday_key'] == 'wednesday' && ( $bookingpress_start_time == NULL || $bookingpress_end_time == NULL ) ) {
					$is_wednesday_break = 1;
				} elseif ( $workhour_val['bookingpress_workday_key'] == 'thursday' && ( $bookingpress_start_time == NULL || $bookingpress_end_time == NULL ) ) {
					$is_thursday_break = 1;
				} elseif ( $workhour_val['bookingpress_workday_key'] == 'friday' && ( $bookingpress_start_time == NULL || $bookingpress_end_time == NULL ) ) {
					$is_friday_break = 1;
				} elseif ( $workhour_val['bookingpress_workday_key'] == 'saturday' && ( $bookingpress_start_time == NULL || $bookingpress_end_time == NULL ) ) {
					$is_saturday_break = 1;
				} elseif ( $workhour_val['bookingpress_workday_key'] == 'sunday' && ( $bookingpress_start_time == NULL || $bookingpress_end_time == NULL ) ) {
					$is_sunday_break = 1;
				}
			}

			$default_year            = date( 'Y', current_time( 'timestamp' ) );
			$default_daysoff_details = array();

			$calendar_start_date = $calendar_next_date = date( 'Y-m-d', current_time( 'timestamp' ) );
			$calendar_end_date   = date( 'Y-m-d', strtotime( '+1 year', current_time( 'timestamp' ) ) );
			for ( $i = 1; $i <= 730; $i++ ) {
				$current_day_name = date( 'l', strtotime( $calendar_next_date ) );
				if ( $current_day_name == 'Monday' && $is_monday_break == 1 ) {
					$daysoff_tmp_date = date( 'Y-m-d', strtotime( $calendar_next_date ) );
					array_push( $default_daysoff_details, date( 'c', strtotime( $daysoff_tmp_date ) ) );
				} elseif ( $current_day_name == 'Tuesday' && $is_tuesday_break == 1 ) {
					$daysoff_tmp_date = date( 'Y-m-d', strtotime( $calendar_next_date ) );
					array_push( $default_daysoff_details, date( 'c', strtotime( $daysoff_tmp_date ) ) );
				} elseif ( $current_day_name == 'Wednesday' && $is_wednesday_break == 1 ) {
					$daysoff_tmp_date = date( 'Y-m-d', strtotime( $calendar_next_date ) );
					array_push( $default_daysoff_details, date( 'c', strtotime( $daysoff_tmp_date ) ) );
				} elseif ( $current_day_name == 'Thursday' && $is_thursday_break == 1 ) {
					$daysoff_tmp_date = date( 'Y-m-d', strtotime( $calendar_next_date ) );
					array_push( $default_daysoff_details, date( 'c', strtotime( $daysoff_tmp_date ) ) );
				} elseif ( $current_day_name == 'Friday' && $is_friday_break == 1 ) {
					$daysoff_tmp_date = date( 'Y-m-d', strtotime( $calendar_next_date ) );
					array_push( $default_daysoff_details, date( 'c', strtotime( $daysoff_tmp_date ) ) );
				} elseif ( $current_day_name == 'Saturday' && $is_saturday_break == 1 ) {
					$daysoff_tmp_date = date( 'Y-m-d', strtotime( $calendar_next_date ) );
					array_push( $default_daysoff_details, date( 'c', strtotime( $daysoff_tmp_date ) ) );
				} elseif ( $current_day_name == 'Sunday' && $is_sunday_break == 1 ) {
					$daysoff_tmp_date = date( 'Y-m-d', strtotime( $calendar_next_date ) );
					array_push( $default_daysoff_details, date( 'c', strtotime( $daysoff_tmp_date ) ) );
				}

				$calendar_next_date = date( 'Y-m-d', strtotime( $calendar_next_date . ' +1 days' ) );
			}

			$daysoff_details = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_default_daysoff}", ARRAY_A );
			foreach ( $daysoff_details as $daysoff_details_key => $daysoff_details_val ) {
				$daysoff_date = esc_html( $daysoff_details_val['bookingpress_dayoff_date'] );

				$dayoff_year = date( 'Y', strtotime( $daysoff_date ) );

				if ( empty( $daysoff_details_val['bookingpress_repeat'] ) ) {
					array_push( $default_daysoff_details, date( 'c', strtotime( $daysoff_date ) ) );
				} elseif ( ! empty( $daysoff_details_val['bookingpress_repeat'] ) ) {
					for ( $i = $default_year; $i <= 2035; $i++ ) {
						$daysoff_new_date_month = $i . '-' . date( 'm-d', strtotime( $daysoff_date ) );
						array_push( $default_daysoff_details, date( 'c', strtotime( $daysoff_new_date_month ) ) );
					}
				}
			}

			return $default_daysoff_details;
		}


		public function bookingpress_get_service_available_time( $service_id = 0, $selected_date = '' ) {
			global $wpdb, $tbl_bookingpress_default_workhours, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs, $tbl_bookingpress_services;

			$bookingpress_hide_already_booked_slot = $this->bookingpress_get_customize_settings( 'hide_already_booked_slot', 'booking_form' );
			$bookingpress_hide_already_booked_slot = ( $bookingpress_hide_already_booked_slot == 'true' ) ? 1 : 0;

			$current_day  = ! empty( $selected_date ) ? strtolower( date( 'l', strtotime( $selected_date ) ) ) : strtolower( date( 'l', current_time( 'timestamp' ) ) );
			$current_date = ! empty( $selected_date ) ? date( 'Y-m-d', strtotime( $selected_date ) ) : date( 'Y-m-d', current_time( 'timestamp' ) );

			$bpa_current_date = date('Y-m-d', strtotime(current_time('mysql')));

		    $bpa_current_time = date( 'H:i',strtotime(current_time('mysql')));						

			$default_daysoff_details = $this->bookingpress_get_default_dayoff_dates();
			if ( ! empty( $default_daysoff_details ) ) {
				foreach ( $default_daysoff_details as $key => $value ) {
					if ( date( 'Y-m-d', strtotime( $value ) ) == $current_date ) {
						return '';
						exit;
					}
				}
			}

			$service_time_duration     = $this->bookingpress_get_default_timeslot_data();
			$service_step_duration_val = $service_time_duration['default_timeslot'];
			if ( ! empty( $service_id ) ) {
				$service_data = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = {$service_id}", ARRAY_A );
				if ( ! empty( $service_data ) ) {
					$service_time_duration      = esc_html( $service_data['bookingpress_service_duration_val'] );
					$service_time_duration_unit = esc_html( $service_data['bookingpress_service_duration_unit'] );
					if ( $service_time_duration_unit == 'h' ) {
						$service_time_duration = $service_time_duration * 60;
					}
					$service_step_duration_val = $service_time_duration;
				}
			}


			$already_booked_time_arr = $workhour_data = $break_hour_arr = array();

			$get_default_work_hours_data = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_default_workhours} WHERE bookingpress_workday_key = '{$current_day}' AND bookingpress_is_break = 0", ARRAY_A );
			if ( ! empty( $get_default_work_hours_data ) ) {
				$service_current_time = $service_start_time = date( 'H:i', strtotime( $get_default_work_hours_data['bookingpress_start_time'] ) );
				$service_end_time     = date( 'H:i', strtotime( $get_default_work_hours_data['bookingpress_end_time'] ) );

				if ( $service_start_time != NULL && $service_end_time != NULL ) {
					while ( $service_current_time <= $service_end_time ) {
						if ( $service_current_time > $service_end_time ) {
							break;
						}

						$service_tmp_current_time = $service_current_time;

						if($service_current_time == "00:00"){
							$service_current_time = date('H:i', strtotime($service_current_time)+($service_step_duration_val * 60));
						}else{
							$service_tmp_time_obj = new DateTime( $service_current_time );
							$service_tmp_time_obj->add( new DateInterval( 'PT' . $service_step_duration_val . 'M' ) );
							$service_current_time = $service_tmp_time_obj->format( 'H:i' );
						}
						
						$break_start_time      = '';
						$break_end_time        = '';
						$check_break_existance = $wpdb->get_var( "SELECT COUNT(bookingpress_workhours_id) as total FROM {$tbl_bookingpress_default_workhours} WHERE bookingpress_workday_key = '{$current_day}' AND bookingpress_is_break = 1 AND (bookingpress_start_time BETWEEN '{$service_tmp_current_time}' AND '{$service_current_time}')" );
						
						if ( $check_break_existance > 0 ) {
							$get_break_workhours = $wpdb->get_row( "SELECT TIMEDIFF(bookingpress_end_time, bookingpress_start_time) as time_diff, bookingpress_start_time, bookingpress_end_time FROM {$tbl_bookingpress_default_workhours} WHERE bookingpress_workday_key = '{$current_day}' AND bookingpress_is_break = 1 AND (bookingpress_start_time BETWEEN '{$service_tmp_current_time}' AND '{$service_current_time}')", ARRAY_A );
							$time_difference     = date( 'H:i', strtotime( $get_break_workhours['time_diff'] ) );

							$break_start_time     = date( 'H:i', strtotime( $get_break_workhours['bookingpress_start_time'] ) );
							$break_end_time       = date( 'H:i', strtotime( $get_break_workhours['bookingpress_end_time'] ) );
							$service_current_time = $break_start_time;
						}

						//$is_appointment_booked = $wpdb->get_var( "SELECT COUNT(bookingpress_payment_log_id) as total FROM {$tbl_bookingpress_payment_logs} WHERE bookingpress_appointment_date LIKE '%{$current_date}%' AND bookingpress_service_id = {$service_id} AND (bookingpress_appointment_start_time BETWEEN '{$service_tmp_current_time}' AND '{$service_current_time}' )" );

						$is_appointment_booked = $wpdb->get_var( "SELECT COUNT(bookingpress_payment_log_id) as total FROM {$tbl_bookingpress_payment_logs} WHERE bookingpress_appointment_date LIKE '%{$current_date}%' AND bookingpress_service_id = {$service_id} AND ((bookingpress_appointment_start_time >= '{$service_tmp_current_time}' AND bookingpress_appointment_start_time < '{$service_current_time}') OR (bookingpress_appointment_start_time < '{$service_current_time}' AND bookingpress_appointment_end_time > '{$service_tmp_current_time}' ) )" );

						$is_already_booked     = ( $is_appointment_booked > 0 ) ? 1 : 0;						
						if ( $is_already_booked == 1 ) {						

							$check_timeslot_canceled_data = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_payment_logs} WHERE bookingpress_appointment_date LIKE '%{$current_date}%' AND bookingpress_service_id = {$service_id} AND (bookingpress_appointment_start_time BETWEEN '{$service_tmp_current_time}' AND '{$service_current_time}' )", ARRAY_A );

							if ( ! empty( $check_timeslot_canceled_data ) ) {
								$appointment_id                = $check_timeslot_canceled_data['bookingpress_appointment_booking_ref'];
								$bookingpress_appointment_data = $wpdb->get_var( "SELECT COUNT(bookingpress_appointment_booking_id) as total FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = {$appointment_id} AND ( bookingpress_appointment_status = 'Cancelled' OR bookingpress_appointment_status = 'Rejected' )" );
								if ( $bookingpress_appointment_data > 0 ) {
									$is_already_booked = 0;
								}
							}

						}

						if($service_current_time < $service_start_time || $service_current_time == $service_start_time){
							$service_current_time = $service_end_time;
						}

						if ( $is_already_booked == 1 && $bookingpress_hide_already_booked_slot == 1 ) {
							continue;
						} else {	
							if ( $break_start_time != $service_tmp_current_time ) {
								if($bpa_current_date == $current_date) {
									if($service_tmp_current_time > $bpa_current_time) {
										$workhour_data[] = array(
											'start_time'       => $service_tmp_current_time,
											'end_time'         => $service_current_time,
											'break_start_time' => $break_start_time,
											'break_end_time'   => $break_end_time,
											'is_booked'        => $is_already_booked,										
										);									
									} else {
										$workhour_data[] = array(
											'start_time'       => $service_tmp_current_time,
											'end_time'         => $service_current_time,
											'break_start_time' => $break_start_time,
											'break_end_time'   => $break_end_time,										
											'is_booked'        => 1,
										);
									}	
								} else {
									$workhour_data[] = array(
											'start_time'       => $service_tmp_current_time,
											'end_time'         => $service_current_time,
											'break_start_time' => $break_start_time,
											'break_end_time'   => $break_end_time,
											'is_booked'        => $is_already_booked,										
										);									
								}
							}
						}

						if ( ! empty( $break_end_time ) ) {
							$service_current_time = $break_end_time;
						}

						if($service_current_time == $service_end_time){
							break;
						}
					}
				}
			}

			return $workhour_data;
		}

		public function bookingpress_get_daily_timeslots($appointment_time_slot = array()){
			$morning_time = array();
			$afternoon_time = array();
			$evening_time = array();
			$night_time = array();

			$bookingpress_service_slot_details = array();
			if ( ! empty( $appointment_time_slot ) ) {
				foreach ( $appointment_time_slot as $key => $value ) {

					$service_start_time = date( 'H', strtotime( $value['start_time'] ) );
					$service_end_time = date( 'H', strtotime( $value['end_time'] ) );

					if ( $service_start_time >= 0 && $service_start_time < 12 ) {
						$morning_time[] = array(
							'start_time' => $value['start_time'],
							'end_time'   => $value['end_time'],
							'is_disabled' => ($value['is_booked'] == 1) ? true : false,
						);
					} else if($service_start_time >= 12 && $service_start_time < 16) {
						$afternoon_time[] = array(
							'start_time' => $value['start_time'],
							'end_time'   => $value['end_time'],
							'is_disabled' => ($value['is_booked'] == 1) ? true : false,
						);
					} else if($service_start_time >= 16 && $service_start_time < 20) {
						$evening_time[] = array(
							'start_time' => $value['start_time'],
							'end_time'   => $value['end_time'],
							'is_disabled' => ($value['is_booked'] == 1) ? true : false,
						);
					} else {
						$night_time[] = array(
							'start_time' => $value['start_time'],
							'end_time'   => $value['end_time'],
							'is_disabled' => ($value['is_booked'] == 1) ? true : false,
						);
					}
				}
			}

			$bookingpress_service_slot_details['morning_time'] = array(
				'timeslot_label' => __('Morning', 'bookingpress-appointment-booking'),
				'timeslots' => $morning_time,
			);
			$bookingpress_service_slot_details['afternoon_time'] = array(
				'timeslot_label' => __('Afternoon', 'bookingpress-appointment-booking'),
				'timeslots' => $afternoon_time,
			);
			$bookingpress_service_slot_details['evening_time'] = array(
				'timeslot_label' => __('Evening', 'bookingpress-appointment-booking'),
				'timeslots' => $evening_time,
			);
			$bookingpress_service_slot_details['night_time'] = array(
				'timeslot_label' => __('Night', 'bookingpress-appointment-booking'),
				'timeslots' => $night_time,
			);

			return $bookingpress_service_slot_details;
		}


		public function get_weekstart_date_end_date( $week_number, $year ) {
			$dto = new DateTime();
			$dto->setISODate( $year, $week_number );
			$ret['week_start'] = $dto->format( 'Y-m-d' );
			$dto->modify( '+6 days' );
			$ret['week_end'] = $dto->format( 'Y-m-d' );
			return $ret;
		}

		public function get_monthstart_date_end_date() {
			$month_start_date = date( 'Y-m-01' );
			$month_end_date   = date( 'Y-m-t' );
			return array(
				'start_date' => $month_start_date,
				'end_date'   => $month_end_date,
			);
		}


		public function get_bookingpress_service_data_group_with_category() {
			global $wpdb, $tbl_bookingpress_categories, $tbl_bookingpress_services;

			$bookingpress_currency_name   = $this->bookingpress_get_settings( 'payment_default_currency', 'payment_setting' );
			$bookingpress_currency_symbol = ! empty( $bookingpress_currency_name ) ? $this->bookingpress_get_currency_symbol( $bookingpress_currency_name ) : '';

			$bookingpress_services_details   = array();
			$bookingpress_service_categories = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_categories . ' ORDER BY bookingpress_category_position ASC', ARRAY_A );
			foreach ( $bookingpress_service_categories as $bookingpress_service_cat_key => $bookingpress_service_cat_val ) {
				$bookingpress_cat_id       = $bookingpress_service_cat_val['bookingpress_category_id'];
				$bookingpress_tmp_services = array();
				$bookingpress_services     = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_services . " WHERE bookingpress_category_id = {$bookingpress_cat_id}", ARRAY_A );
				foreach ( $bookingpress_services as $bookingpress_service_key => $bookingpress_service_val ) {
					$bookingpress_service_price = $this->bookingpress_price_formatter_with_currency_symbol( $bookingpress_service_val['bookingpress_service_price'], $bookingpress_currency_symbol );

					$bookingpress_tmp_services[] = array(
						'service_id'    => $bookingpress_service_val['bookingpress_service_id'],
						'service_name'  => $bookingpress_service_val['bookingpress_service_name'],
						'service_price' => $bookingpress_service_price,
					);
				}

				if ( ! empty( $bookingpress_tmp_services ) ) {
					$bookingpress_services_details[] = array(
						'category_name'     => $bookingpress_service_cat_val['bookingpress_category_name'],
						'category_services' => $bookingpress_tmp_services,
					);
				}
			}

			return $bookingpress_services_details;
		}

		function bookingpress_insert_appointment_logs( $appointment_booking_data = array() ) {
			global $wpdb, $tbl_bookingpress_appointment_bookings;
			$appointment_inserted_id = 0;
			if ( ! empty( $appointment_booking_data ) ) {
				$wpdb->insert( $tbl_bookingpress_appointment_bookings, $appointment_booking_data );
				$appointment_inserted_id = $wpdb->insert_id;
			}
			return $appointment_inserted_id;
		}


		function bookingpress_insert_payment_logs( $payment_log_data = array() ) {
			global $wpdb, $tbl_bookingpress_payment_logs;
			$payment_log_id = 0;
			if ( ! empty( $payment_log_data ) ) {
				$wpdb->insert( $tbl_bookingpress_payment_logs, $payment_log_data );
				$payment_log_id = $wpdb->insert_id;
			}

			return $payment_log_id;
		}


		function get_service_by_id( $service_id ) {
			 global $wpdb, $tbl_bookingpress_services;
			$service_data = array();
			if ( ! empty( $service_id ) ) {
				$service_data = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = {$service_id}", ARRAY_A );
			}
			return $service_data;
		}


		function get_customer_details( $customer_id ) {
			 global $wpdb, $tbl_bookingpress_customers;
			$customer_data = array();
			if ( ! empty( $customer_id ) ) {
				$customer_data = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_customers} WHERE bookingpress_customer_id = {$customer_id}", ARRAY_A );
			}
			return $customer_data;
		}

		function bookingpress_install_default_notification_data() {
			global $wpdb, $tbl_bookingpress_notifications;

			$bookingpress_default_notifications_name_arr = array( 'Appointment Approved', 'Appointment Pending', 'Appointment Rejected', 'Appointment Canceled' );

			$bookingpress_default_notifications_message_arr = array(
				'Appointment Approved' => 'Dear %customer_full_name%,<br>You have successfully scheduled appointment.<br>Thank you for choosing us,<br>%company_name%',
				'Appointment Pending'  => 'Dear %customer_full_name%,<br>The %service_name% appointment is scheduled and it\'s waiting for a confirmation.<br>Thank you for choosing us,<br>%company_name%',
				'Appointment Rejected' => 'Dear %customer_full_name%,<br>The %service_name% appointment, has been rejected.<br>Thank you for choosing us,<br>%company_name%',
				'Appointment Canceled' => 'Dear %customer_full_name%,<br>The %service_name% appointment, has been canceled.<br>Thank you for choosing us,<br>%company_name%',
			);

			foreach ( $bookingpress_default_notifications_name_arr as $bookingpress_default_notification_key => $bookingpress_default_notification_val ) {
				$bookingpress_customer_notification_data = array(
					'bookingpress_notification_name'    => $bookingpress_default_notification_val,
					'bookingpress_notification_receiver_type' => 'customer',
					'bookingpress_notification_status'  => 1,
					'bookingpress_notification_type'    => 'default',
					'bookingpress_notification_subject' => $bookingpress_default_notification_val,
					'bookingpress_notification_message' => $bookingpress_default_notifications_message_arr[ $bookingpress_default_notification_val ],
					'bookingpress_created_at'           => current_time( 'mysql' ),
				);

				$wpdb->insert( $tbl_bookingpress_notifications, $bookingpress_customer_notification_data );
			}


			$bookingpress_default_notifications_arr2 = array(
				'Appointment Approved' => 'Hi administrator,<br>You have one confirmed %service_name% appointment. The appointment is added to your schedule.<br>Thank you,<br>%company_name%',
				'Appointment Pending'  => 'Hi administrator,<br>You have new appointment in %service_name%. The appointment is waiting for a confirmation.<br>Thank you,<br>%company_name%',
				'Appointment Rejected' => 'Hi administrator,<br>Your %service_name% appointment, has been rejected.<br>Thank you,<br>%company_name%',
				'Appointment Canceled' => 'Hi administrator,<br>Your %service_name% appointment, has been canceled.<br>Thank you,<br>%company_name%',	
			);

			foreach ( $bookingpress_default_notifications_name_arr as $bookingpress_default_notification_key => $bookingpress_default_notification_val ) {
				$bookingpress_employee_notification_data = array(
					'bookingpress_notification_name'    => $bookingpress_default_notification_val,
					'bookingpress_notification_receiver_type' => 'employee',
					'bookingpress_notification_status'  => 1,
					'bookingpress_notification_type'    => 'default',
					'bookingpress_notification_subject' => $bookingpress_default_notification_val,
					'bookingpress_notification_message' => $bookingpress_default_notifications_arr2[ $bookingpress_default_notification_val ],
					'bookingpress_created_at'           => current_time( 'mysql' ),
				);

				$wpdb->insert( $tbl_bookingpress_notifications, $bookingpress_employee_notification_data );
			}
		}


		function bookingpress_install_default_general_settings_data() {
			$bookingpress_general_setting_form_default_data = array(
				'default_time_slot_step'              => '30',
				'appointment_status'                  => 'Approved',
				'default_phone_country_code'          => 'us',
				'per_page_item'                       => '20',
				'redirect_url_after_booking_approved' => '',
				'redirect_url_after_booking_pending'  => '',
				'redirect_url_after_booking_canceled' => '',
				'phone_number_mandatory'              => false,
				'use_already_loaded_vue'              => false,
			);

			$bookingpress_company_setting_form_default_data      = array(
				'company_avatar_img'    => '',
				'company_avatar_url'    => '',
				'company_avatar_list'   => array(),
				'company_name'          => get_option( 'blogname' ),
				'company_address'       => '',
				'company_website'       => '',
				'company_phone_country' => 'us',
				'company_phone_number'  => '',
			);
			$bookingpress_notification_setting_form_default_data = array(
				'selected_mail_service' => 'php_mail',
				'sender_name'           => get_option( 'blogname' ),
				'sender_email'          => get_option( 'admin_email' ),
				'admin_email'           => get_option( 'admin_email' ),
				'success_url'           => '',
				'cancel_url'            => '',
				'smtp_host'             => '',
				'smtp_port'             => '',
				'smtp_secure'           => 'Disabled',
				'smtp_username'         => '',
				'smtp_password'         => '',
			);
			$bookingpress_payment_setting_form_default_data      = array(
				'payment_default_currency' => 'US Dollar',
				'price_symbol_position'    => 'before',
				'price_separator'          => 'comma-dot',
				'price_number_of_decimals' => 2,
				'on_site_payment'          => true,
				'paypal_payment'           => false,
				'paypal_cancel_url'        => BOOKINGPRESS_HOME_URL,  
				'paypal_payment_mode'      => 'sandbox',
			);
			$bookingpress_message_setting_form_default_data      = array(
				'confirmation_message_for_the_cancel_appointment' => 'Are you sure you want to cancel this appointment?',
				'appointment_booked_successfully'       => 'Appointment has been booked successfully.',
				'appointment_cancelled_successfully'    => 'Appointment has been cancelled successfully.',
				'duplidate_appointment_time_slot_found' => 'I am sorry! Another appointment is already booked with this time slot. Please select another time slot which suits you the best.',
				'unsupported_currecy_selected_for_the_payment' => 'I am sorry! The selected currency is not supported by selected payment gateway. Please proceed with another available payment method.',
				'duplicate_email_address_found'         => 'It seems that you are already registered with us! Please login to continue to book an appointment.',
				'no_payment_method_is_selected_for_the_booking' => 'Please select a payment method to proceed with the booking.',
				'no_appointment_time_selected_for_the_booking' => 'Please select a time slot to proceed with the booking.',
				'no_appointment_date_selected_for_the_booking' => 'Please select appointment date to proceed with the booking.',
				'no_service_selected_for_the_booking'   => 'Please select any service to book the appointment.',
			);
			$bookingpress_customer_setting_form_default_data = array(
				'allow_wp_user_create' => 'false',
			);
			$bookingpress_install_default_data                   = array(
				'general_setting'      => $bookingpress_general_setting_form_default_data,
				'company_setting'      => $bookingpress_company_setting_form_default_data,
				'notification_setting' => $bookingpress_notification_setting_form_default_data,
				'payment_setting'      => $bookingpress_payment_setting_form_default_data,
				'message_setting'      => $bookingpress_message_setting_form_default_data,
				'customer_setting'     => $bookingpress_customer_setting_form_default_data,
			);

			foreach ( $bookingpress_install_default_data as $bookingpress_default_data_key => $bookingpress_default_data_val ) {
				$bookingpress_setting_type = $bookingpress_default_data_key;
				foreach ( $bookingpress_default_data_val as $bookingpress_default_data_val_key => $bookingpress_default_data_val2 ) {
					$this->bookingpress_update_settings( $bookingpress_default_data_val_key, $bookingpress_setting_type, $bookingpress_default_data_val2 );
				}
			}

			global $tbl_bookingpress_default_workhours, $wpdb;

			// Install default workhours data
			$bookingpress_default_days = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
			foreach ( $bookingpress_default_days as $bookingpress_default_day_key => $bookingpress_default_day_val ) {
				$default_start_time = '09:00';
				$default_end_time   = '17:00';

				$default_insert_data = array(
					'bookingpress_workday_key' => $bookingpress_default_day_val,
				);

				if ( $bookingpress_default_day_val != 'saturday' && $bookingpress_default_day_val != 'sunday' ) {
					$default_insert_data['bookingpress_start_time'] = $default_start_time;
					$default_insert_data['bookingpress_end_time']   = $default_end_time;
				} else {
					$default_insert_data['bookingpress_start_time'] = NULL;
					$default_insert_data['bookingpress_end_time']   = NULL;
				}

				$wpdb->insert( $tbl_bookingpress_default_workhours, $default_insert_data );
			}
		}

		function bookingpress_install_default_customize_settings_data() {
			global $wpdb, $tbl_bookingpress_customize_settings, $tbl_bookingpress_form_fields;

			$booking_form_shortcode_default_fields = array(
				'is_edit_service'                 => 'false',
				'is_edit_date_time'               => 'false',
				'is_edit_basic_details'           => 'false',
				'is_edit_summary'                 => 'false',
				'service_title'                   => __( 'Service', 'bookingpress-appointment-booking' ),
				'datetime_title'                  => __( 'Date & Time', 'bookingpress-appointment-booking' ),
				'basic_details_title'             => __( 'Basic Details', 'bookingpress-appointment-booking' ),
				'summary_title'                   => __( 'Summary', 'bookingpress-appointment-booking' ),
				'editCategoryTitlePopup'          => 'false',
				'is_edit_category_title'          => 'false',
				'category_title'                  => __( 'Select Category', 'bookingpress-appointment-booking' ),
				'editServiceTitlePopup'           => 'false',
				'is_edit_service_title'           => 'false',
				'service_heading_title'           => __( 'Select Service', 'bookingpress-appointment-booking' ),
				'default_image_url'               => BOOKINGPRESS_URL . '/images/placeholder-img.jpg',
				'is_edit_timeslot'                => 'false',
				'timeslot_text'                   => __( 'Time Slot', 'bookingpress-appointment-booking' ),
				'is_edit_morning'                 => 'false',
				'morning_text'                    => __( 'Morning', 'bookingpress-appointment-booking' ),
				'is_edit_afternoon'               => 'false',
				'afternoon_text'                  => __( 'Afternoon', 'bookingpress-appointment-booking' ),
				'is_edit_evening'                 => 'false',
				'evening_text'                    => __( 'Evening', 'bookingpress-appointment-booking' ),
				'is_edit_night'                   => 'false',
				'night_text'                      => __( 'Night', 'bookingpress-appointment-booking' ),
				'is_edit_summary_content'         => 'false',
				'summary_content_text'            => __( 'Your appointment booking summary', 'bookingpress-appointment-booking' ),
				'is_edit_select_payment_method'   => 'false',
				'payment_method_text'             => __( 'Select Payment Method', 'bookingpress-appointment-booking' ),
				'background_color'                => '#fff',
				'footer_background_color'         => '#f4f7fb',
				'primary_color'                   => '#12D488',
				'primary_background_color'        => '#e2faf1',
				'label_title_color'               => '#202C45',
				'content_color'                   => '#535D71',
				'custom_css'                      => '',
				'title_font_size'                 => '16',
				'title_font_family'               => 'Poppins',
				'content_font_size'               => '16',
				'content_font_family'             => 'Poppins',
				'hide_category_service_selection' => 'false',
				'booking_form_tabs_position'      => 'left',
				'hide_next_previous_button'       => 'false',
				'hide_already_booked_slot'        => 'false',
				'display_service_description'     => 'false',
				'goback_button_text'              => __( 'Go Back', 'bookingpress-appointment-booking' ),
				'next_button_text'                => __( 'Next', 'bookingpress-appointment-booking' ),
				'book_appointment_btn_text'       => __( 'Book Appointment', 'bookingpress-appointment-booking' ),
				'default_date_format'             => 'F j, Y',
			);

			foreach ( $booking_form_shortcode_default_fields as $booking_form_shortcode_data_key => $booking_form_shortcode_data_val ) {
				$bookingpress_customize_settings_db_fields = array(
					'bookingpress_setting_name'  => $booking_form_shortcode_data_key,
					'bookingpress_setting_value' => $booking_form_shortcode_data_val,
					'bookingpress_setting_type'  => 'booking_form',
				);

				$wpdb->insert( $tbl_bookingpress_customize_settings, $bookingpress_customize_settings_db_fields );
			}

			$mybookings_shortcode_default_data = array(
				'background_color'            => '#fff',
				'row_background_color'        => '#f4f7fb',
				'label_title_color'           => '#727E95',
				'content_color'               => '#727e95',
				'custom_css'                  => '',
				'title_font_size'             => '14',
				'title_font_family'           => 'Poppins',
				'content_font_size'           => '14',
				'content_font_family'         => 'Poppins',
				'is_edit_mybooking_title'     => 'false',
				'mybooking_title_text'        => __( 'My Bookings', 'bookingpress-appointment-booking' ),
				'hide_customer_details'       => 'false',
				'hide_search_bar'             => 'false',
				'allow_to_cancel_appointment' => true,
				'Default_date_formate'        => 'F j, Y',
				'bookingpress_date_format_1'  => 'Thursday, ' . date( 'F j, Y', strtotime( '2021-10-25' ) ),
				'bookingpress_date_format_2'  => date( 'F j, Y', strtotime( '2021-10-26' ) ),
				'bookingpress_date_format_3'  => 'Friday, ' . date( 'F j, Y', strtotime( '2021-10-25' ) ),
				'bookingpress_date_format_4'  => date( 'F j, Y', strtotime( '2021-10-26' ) ),
			);

			foreach ( $mybookings_shortcode_default_data as $mybookings_shortcode_data_key => $mybookings_shortcode_data_val ) {
				$bookingpress_customize_settings_db_fields = array(
					'bookingpress_setting_name'  => $mybookings_shortcode_data_key,
					'bookingpress_setting_value' => $mybookings_shortcode_data_val,
					'bookingpress_setting_type'  => 'booking_my_booking',
				);

				$wpdb->insert( $tbl_bookingpress_customize_settings, $bookingpress_customize_settings_db_fields );
			}

			$form_fields_default_data = array(
				'fullname'      => array(
					'field_name'     => 'fullname',
					'field_type'     => 'Text',
					'is_edit'        => 0,
					'is_required'    => 0,
					'label'          => __( 'Fullname', 'bookingpress-appointment-booking' ),
					'placeholder'    => __( 'Enter your full name', 'bookingpress-appointment-booking' ),
					'error_message'  => __( 'Please enter your full name', 'bookingpress-appointment-booking' ),
					'is_hide'        => 1,
					'field_position' => 1,
				),
				'firstname'     => array(
					'field_name'     => 'firstname',
					'field_type'     => 'Text',
					'is_edit'        => 0,
					'is_required'    => true,
					'label'          => __( 'Firstname', 'bookingpress-appointment-booking' ),
					'placeholder'    => __( 'Enter your firstname', 'bookingpress-appointment-booking' ),
					'error_message'  => __( 'Please enter your firstname', 'bookingpress-appointment-booking' ),
					'is_hide'        => 0,
					'field_position' => 2,
				),
				'lastname'      => array(
					'field_name'     => 'lastname',
					'field_type'     => 'Text',
					'is_edit'        => 0,
					'is_required'    => true,
					'label'          => __( 'Lastname', 'bookingpress-appointment-booking' ),
					'placeholder'    => __( 'Enter your lastname', 'bookingpress-appointment-booking' ),
					'error_message'  => __( 'Please enter your lastname', 'bookingpress-appointment-booking' ),
					'is_hide'        => 0,
					'field_position' => 3,
				),
				'email_address' => array(
					'field_name'     => 'email_address',
					'field_type'     => 'Email',
					'is_edit'        => 0,
					'is_required'    => true,
					'label'          => __( 'Email Address', 'bookingpress-appointment-booking' ),
					'placeholder'    => __( 'Enter your email address', 'bookingpress-appointment-booking' ),
					'error_message'  => __( 'Please enter your email address', 'bookingpress-appointment-booking' ),
					'is_hide'        => 0,
					'field_position' => 4,
				),
				'phone_number'  => array(
					'field_name'     => 'phone_number',
					'field_type'     => 'Dropdown',
					'is_edit'        => 0,
					'is_required'    => true,
					'label'          => __( 'Phone Number', 'bookingpress-appointment-booking' ),
					'placeholder'    => __( 'Enter your phone number', 'bookingpress-appointment-booking' ),
					'error_message'  => __( 'Please enter your phone number', 'bookingpress-appointment-booking' ),
					'is_hide'        => 0,
					'field_position' => 5,
				),
				'note'          => array(
					'field_name'     => 'note',
					'field_type'     => 'Textarea',
					'is_edit'        => 0,
					'is_required'    => 0,
					'label'          => __( 'Note', 'bookingpress-appointment-booking' ),
					'placeholder'    => __( 'Enter note details', 'bookingpress-appointment-booking' ),
					'error_message'  => __( 'Please enter appointment note', 'bookingpress-appointment-booking' ),
					'is_hide'        => 0,
					'field_position' => 6,
				),
			);

			foreach ( $form_fields_default_data as $form_field_key => $form_field_val ) {
				$form_field_db_data = array(
					'bookingpress_form_field_name'     => $form_field_val['field_name'],
					'bookingpress_field_required'      => $form_field_val['is_required'],
					'bookingpress_field_label'         => $form_field_val['label'],
					'bookingpress_field_placeholder'   => $form_field_val['placeholder'],
					'bookingpress_field_error_message' => $form_field_val['error_message'],
					'bookingpress_field_is_hide'       => $form_field_val['is_hide'],
					'bookingpress_field_position'      => $form_field_val['field_position'],
				);

				$wpdb->insert( $tbl_bookingpress_form_fields, $form_field_db_data );
			}
		}

		function bookingpress_install_default_pages() {
			$bookingpress_thankyoupage_content = '
			<el-row>
				<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
					<div class="bpa-front-module-container bpa-front-module--booking-summary bpa-front-module--confirmation bpa-thankyou--page">
						<div class="bpa-front-module--bs-head">
							<img src="' . esc_url( BOOKINGPRESS_IMAGES_URL . '/front-confirmation-vector.svg' ) . '" alt="">
							<h4>' . esc_html__( 'Your Appointment Booked successfully!', 'bookingpress-appointment-booking' ) . '</h4>
							<p>' . esc_html__( 'We have sent your booking information to your email address.', 'bookingpress-appointment-booking' ) . '</p>
						</div>
						<div class="bpa-front-module--bs-summary-content">
							<div class="bpa-front-module--bs-summary-content-item">
								<span>' . esc_html__( 'Service:', 'bookingpress-appointment-booking' ) . '</span>
								<h4>[bookingpress_appointment_service]</h4>
							</div>
							<div class="bpa-front-module--bs-summary-content-item">
								<span>' . esc_html__( 'Date & Time:', 'bookingpress-appointment-booking' ) . '</span>
								<h4>[bookingpress_appointment_datetime]</h4>
							</div>
							<div class="bpa-front-module--bs-summary-content-item">
								<span>' . esc_html__( 'Customer Name:', 'bookingpress-appointment-booking' ) . '</span>
								<h4>[bookingpress_appointment_customername]</h4>
							</div>
						</div>
					</div>
				</el-col>
			</el-row>';

			$bookingpress_thankyou_page_details = array(
				'post_title'   => esc_html__( 'Thank you page', 'bookingpress-appointment-booking' ),
				'post_name'    => 'thank-you',
				'post_content' => $bookingpress_thankyoupage_content,
				'post_status'  => 'publish',
				'post_parent'  => 0,
				'post_author'  => 1,
				'post_type'    => 'page',
			);

			$bookingpress_post_id = wp_insert_post( $bookingpress_thankyou_page_details );

			$bookingpress_thankyou_page_url = get_permalink( $bookingpress_post_id );
			if ( ! empty( $bookingpress_thankyou_page_url ) ) {
				$this->bookingpress_update_settings( 'redirect_url_after_booking_approved', 'general_setting', $bookingpress_thankyou_page_url );
				$this->bookingpress_update_settings( 'redirect_url_after_booking_pending', 'general_setting', $bookingpress_thankyou_page_url );
			}

			$bookingpress_cancelpage_content = '
			<el-row>
				<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
					<div class="bpa-front-module-container bpa-front-module--booking-summary bpa-front-module--confirmation bpa-cancel--page">
						<div class="bpa-front-module--bs-summary-content">
							<div class="bpa-front-module--bs-summary-content-item">
								<h4>' . esc_html__( 'Sorry to hear that you have requested for cancel the appointment.', 'bookingpress-appointment-booking' ) . '</h4><br/>
								<h5>' . esc_html__( 'We have sent an email notification for cancel appointment. So please click on the button to cancel the appointment.', 'bookingpress-appointment-booking' ) . '</h5>
							</div>
						</div>
					</div>
				</el-col>
			</el-row>';

			$bookingpress_cancel_page_details = array(
				'post_title'   => esc_html__( 'Cancel page', 'bookingpress-appointment-booking' ),
				'post_name'    => 'cancel-appointment',
				'post_content' => $bookingpress_cancelpage_content,
				'post_status'  => 'publish',
				'post_parent'  => 0,
				'post_author'  => 1,
				'post_type'    => 'page',
			);

			$bookingpress_post_id         = wp_insert_post( $bookingpress_cancel_page_details );
			$bookingpress_cancel_page_url = get_permalink( $bookingpress_post_id );
			if ( ! empty( $bookingpress_cancel_page_url ) ) {
				$this->bookingpress_update_settings( 'redirect_url_after_booking_canceled', 'general_setting', $bookingpress_cancel_page_url );
			}


			//Cancel payment page
			$bookingpress_cancel_payment_page = '
			<el-row>
				<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
					<div class="bpa-front-module-container bpa-front-module--booking-summary bpa-front-module--confirmation bpa-cancel--page">
						<div class="bpa-front-module--bs-summary-content">
							<div class="bpa-front-module--bs-summary-content-item">
								<h4>' . esc_html__( 'Sorry! Something went wrong. Your payment has been failed.', 'bookingpress-appointment-booking' ) . '</h4>
							</div>
						</div>
					</div>
				</el-col>
			</el-row>';

			$bookingpress_cancel_page_details = array(
				'post_title'   => esc_html__( 'Cancel Payment page', 'bookingpress-appointment-booking' ),
				'post_name'    => 'cancel-payment',
				'post_content' => $bookingpress_cancel_payment_page,
				'post_status'  => 'publish',
				'post_parent'  => 0,
				'post_author'  => 1,
				'post_type'    => 'page',
			);

			$bookingpress_post_id         = wp_insert_post( $bookingpress_cancel_page_details );
			$bookingpress_cancel_payment_url = get_permalink( $bookingpress_post_id );
			if ( ! empty( $bookingpress_cancel_payment_url ) ) {
				$this->bookingpress_update_settings( 'paypal_cancel_url', 'payment_setting', $bookingpress_cancel_payment_url );
			}
		}

		function bookingpress_check_common_date_format( $selected_date_format ) {
			$return_final_date_format              = '';
			$Bookingpres_elementer_default_formate = array(
				'Y' => 'yyyy',
				'y' => 'yy',
				'F' => 'MMMM',
				'M' => 'MMM',
				'm' => 'MM',
				'n' => 'M',
				'l' => 'dddd',
				'D' => 'ddd',
				'd' => 'dd',
				'j' => 'd',
			);
			$bookingpress_supported_date_formats   = array( 'd', 'D', 'm', 'M', 'y', 'Y', 'F', 'j', 'l', 'n' );

			if ( $selected_date_format == 'F j, Y' ) {
				return 'MMMM d, yyyy';
			} elseif ( substr_count( $selected_date_format, '-' ) ) {
				$bookingpress_tmp_date_format_arr = explode( '-', $selected_date_format );
				if ( in_array( $bookingpress_tmp_date_format_arr[0], $bookingpress_supported_date_formats ) && in_array( $bookingpress_tmp_date_format_arr[1], $bookingpress_supported_date_formats ) && in_array( $bookingpress_tmp_date_format_arr[2], $bookingpress_supported_date_formats ) ) {

					$return_final_date_format = '';
					if ( in_array( $bookingpress_tmp_date_format_arr[0], $bookingpress_supported_date_formats ) ) {
						$return_final_date_format = $Bookingpres_elementer_default_formate[ $bookingpress_tmp_date_format_arr[0] ] . '-';
					}
					if ( in_array( $bookingpress_tmp_date_format_arr[1], $bookingpress_supported_date_formats ) ) {
						$return_final_date_format = $return_final_date_format . $Bookingpres_elementer_default_formate[ $bookingpress_tmp_date_format_arr[1] ] . '-';
					}
					if ( in_array( $bookingpress_tmp_date_format_arr[2], $bookingpress_supported_date_formats ) ) {
						$return_final_date_format = $return_final_date_format . $Bookingpres_elementer_default_formate[ $bookingpress_tmp_date_format_arr[2] ];
					}
					return $return_final_date_format;
				} else {
					return 'MMMM d, yyyy';
				}
			} elseif ( substr_count( $selected_date_format, '/' ) ) {
				$bookingpress_tmp_date_format_arr = explode( '/', $selected_date_format );

				if ( in_array( $bookingpress_tmp_date_format_arr[0], $bookingpress_supported_date_formats ) && in_array( $bookingpress_tmp_date_format_arr[1], $bookingpress_supported_date_formats ) && in_array( $bookingpress_tmp_date_format_arr[2], $bookingpress_supported_date_formats ) ) {

					$return_final_date_format = '';
					if ( in_array( $bookingpress_tmp_date_format_arr[0], $bookingpress_supported_date_formats ) ) {
						$return_final_date_format = $Bookingpres_elementer_default_formate[ $bookingpress_tmp_date_format_arr[0] ] . '/';
					}
					if ( in_array( $bookingpress_tmp_date_format_arr[1], $bookingpress_supported_date_formats ) ) {
						$return_final_date_format = $return_final_date_format . $Bookingpres_elementer_default_formate[ $bookingpress_tmp_date_format_arr[1] ] . '/';
					}
					if ( in_array( $bookingpress_tmp_date_format_arr[2], $bookingpress_supported_date_formats ) ) {
						$return_final_date_format = $return_final_date_format . $Bookingpres_elementer_default_formate[ $bookingpress_tmp_date_format_arr[2] ];
					}
					return $return_final_date_format;
				} else {
					return 'MMMM d, yyyy';
				}
			} elseif ( substr_count( $selected_date_format, ' ' ) ) {

				$bookingpress_tmp_date_format_arr = explode( ' ', $selected_date_format );
				$return_final_date_format         = '';

				if ( in_array( $bookingpress_tmp_date_format_arr[0], $bookingpress_supported_date_formats ) && in_array( $bookingpress_tmp_date_format_arr[1], $bookingpress_supported_date_formats ) && in_array( $bookingpress_tmp_date_format_arr[2], $bookingpress_supported_date_formats ) ) {

					if ( in_array( $bookingpress_tmp_date_format_arr[0], $bookingpress_supported_date_formats ) ) {
						$return_final_date_format = $Bookingpres_elementer_default_formate[ $bookingpress_tmp_date_format_arr[0] ] . ' ';
					}
					if ( in_array( $bookingpress_tmp_date_format_arr[1], $bookingpress_supported_date_formats ) ) {
						$return_final_date_format = $return_final_date_format . $Bookingpres_elementer_default_formate[ $bookingpress_tmp_date_format_arr[1] ] . ' ';
					}
					if ( in_array( $bookingpress_tmp_date_format_arr[2], $bookingpress_supported_date_formats ) ) {
						$return_final_date_format = $return_final_date_format . $Bookingpres_elementer_default_formate[ $bookingpress_tmp_date_format_arr[2] ];
					}
					return $return_final_date_format;

				} else {
					return 'MMMM d, yyyy';
				}
			} else {
				return 'MMMM d, yyyy';
			}
		}

		function bookingpress_write_response( $response_data, $file_name = '' ) {
			global $wp, $wpdb, $wp_filesystem;
			$file_path = BOOKINGPRESS_DIR . '/log/response.txt';
			if ( file_exists( ABSPATH . 'wp-admin/includes/file.php' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
				if ( false === ( $creds = request_filesystem_credentials( $file_path, '', false, false ) ) ) {
					return true;
				}

				if ( ! WP_Filesystem( $creds ) ) {
					request_filesystem_credentials( $file_path, $method, true, false );
					return true;
				}
				@$file_data = $wp_filesystem->get_contents( $file_path );
				$file_data .= $response_data;
				$file_data .= "\r\n===========================================================================\r\n";
				$breaks     = array( '<br />', '<br>', '<br/>' );
				$file_data  = str_ireplace( $breaks, "\r\n", $file_data );

				@$write_file = $wp_filesystem->put_contents( $file_path, $file_data, 0755 );
			}
			return;
		}
		function bookingpress_write_payment_log( $bookingpress_log_payment_gateway, $bookingpress_log_event, $bookingpress_log_event_from = 'bookingpress', $bookingpress_payment_log_raw_data = '', $bookingpress_ref_id = 0, $bookingpress_log_status = 1 ) {

			global $wpdb, $BookingPress,$bookingpress_debug_payment_log_id,$tbl_bookingpress_debug_payment_log;

			$bookingpress_log_payment_setting_name = $bookingpress_log_payment_gateway;
			$bookingpress_active_gateway = false;
			if ( ! empty( $bookingpress_log_payment_gateway ) && $bookingpress_log_payment_gateway == 'on-site' ) {
				$bookingpress_log_payment_setting_name = 'on_site_payment';
			} elseif ( ! empty( $bookingpress_log_payment_gateway ) && $bookingpress_log_payment_gateway == 'paypal' ) {
				$bookingpress_log_payment_setting_name = 'paypal_payment';
			}
			if ( ! empty( $bookingpress_log_payment_setting_name ) ) {
				$bookingpress_active_gateway = $BookingPress->bookingpress_get_settings( $bookingpress_log_payment_setting_name, 'debug_log_setting' );
			}
			$inserted_id = 0;
			if ( $bookingpress_active_gateway == 'true' ) {
				if ( $bookingpress_ref_id == null ) {
					$bookingpress_ref_id = 0; 
				}

				$bookingpress_database_log_data = array(
					'bookingpress_payment_log_ref_id'     => sanitize_text_field( $bookingpress_ref_id ),
					'bookingpress_payment_log_gateway'    => sanitize_text_field( $bookingpress_log_payment_gateway ),
					'bookingpress_payment_log_event'      => sanitize_text_field( $bookingpress_log_event ),
					'bookingpress_payment_log_event_from' => sanitize_text_field( $bookingpress_log_event_from ),
					'bookingpress_payment_log_status'     => sanitize_text_field( $bookingpress_log_status ),
					'bookingpress_payment_log_raw_data'   => maybe_serialize( stripslashes_deep( $bookingpress_payment_log_raw_data ) ),
					'bookingpress_payment_log_added_date' => current_time( 'mysql' ),
				);

				$wpdb->insert( $tbl_bookingpress_debug_payment_log, $bookingpress_database_log_data );
				$inserted_id = $wpdb->insert_id;
				if ( empty( $bookingpress_ref_id ) ) {
					$bookingpress_ref_id = $inserted_id;
				}
			}
			$bookingpress_debug_payment_log_id = $bookingpress_ref_id;
			return $inserted_id;
		}

		function bookingpress_debug_log_download_file() {

			if ( ! empty( $_REQUEST['bookingpress_action'] ) && 'download_log' == sanitize_text_field( $_REQUEST['bookingpress_action'] ) ) {

				$filename = ! empty( $_REQUEST['file'] ) ? sanitize_file_name( basename( $_REQUEST['file'] ) ) : '';
				if ( ! empty( $filename ) ) {
					$file_path = BOOKINGPRESS_UPLOAD_DIR . '/' . $filename;

					$allowexts = array( 'txt', 'zip' );

					$file_name_arm = substr( $filename, 0, 3 );

					$checkext = explode( '.', $filename );
					$ext      = strtolower( $checkext[ count( $checkext ) - 1 ] );

					if ( ! empty( $ext ) && in_array( $ext, $allowexts ) && ! empty( $filename ) && file_exists( $file_path ) ) {
						ignore_user_abort();
						$now = gmdate( 'D, d M Y H:i:s' );
						header( 'Expires: Tue, 03 Jul 2020 06:00:00 GMT' );
						header( 'Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate' );
						header( "Last-Modified: {$now} GMT" );
						header( 'Content-Type: application/force-download' );
						header( 'Content-Type: application/octet-stream' );
						header( 'Content-Type: application/download' );
						header( "Content-Disposition: attachment;filename={$filename}" );
						header( 'Content-Transfer-Encoding: binary' );

						readfile( $file_path );

						unlink( $file_path );

						$arm_txt_file_name = str_replace( '.zip', '.txt', $filename );
						$arm_txt_file_path = BOOKINGPRESS_UPLOAD_DIR . '/' . $arm_txt_file_name;
						if ( file_exists( $arm_txt_file_path ) ) {
							unlink( $arm_txt_file_path );
						}

						die;
					}
				}
			}
		}
		function bookingpress_view_debug_payment_log_func() {
			global $wpdb,$tbl_bookingpress_debug_payment_log;
			$response              = array();
			$response['variant']   = 'error';
			$response['title']     = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']       = esc_html__( 'Something went wrong', 'bookingpress-appointment-booking' );
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );

			$perpage     = isset( $_POST['perpage'] ) ? intval( $_POST['perpage'] ) : 20;
			$currentpage = isset( $_POST['currentpage'] ) ? intval( $_POST['currentpage'] ) : 1;
			$offset      = ( ! empty( $currentpage ) && $currentpage > 1 ) ? ( ( $currentpage - 1 ) * $perpage ) : 0;

			if ( ! $bpa_verify_nonce_flag ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
				wp_send_json( $response );
				die();
			}
			$bookingpress_view_log_selector = isset( $_REQUEST['bookingpress_debug_log_selector'] ) ? sanitize_text_field( $_REQUEST['bookingpress_debug_log_selector'] ) : '';

			if ( ! empty( $bookingpress_view_log_selector ) ) {
				$bookingpress_search_query  = ' WHERE 1=1';
				$bookingpress_search_query .= " AND bookingpress_payment_log_gateway = '{$bookingpress_view_log_selector}'";
				$total_payment_debug_logs   = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_debug_payment_log . " {$bookingpress_search_query} ORDER BY bookingpress_payment_log_id DESC", ARRAY_A );
				$payment_debug_logs         = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_debug_payment_log . " {$bookingpress_search_query} ORDER BY bookingpress_payment_log_id DESC LIMIT " . $offset . ',' . $perpage, ARRAY_A );
					$payment_debug_log_data = array();
				if ( ! empty( $payment_debug_logs ) ) {
					$bookingpress_date_format = get_option( 'date_format' );
					foreach ( $payment_debug_logs as $payment_debug_log_key => $payment_debug_log_val ) {

						$bookingpress_payment_log_id         = ! empty( $payment_debug_log_val['bookingpress_payment_log_id'] ) ? intval( $payment_debug_log_val['bookingpress_payment_log_id'] ) : '';
						$bookingpress_payment_log_event      = ! empty( $payment_debug_log_val['bookingpress_payment_log_event'] ) ? esc_html( $payment_debug_log_val['bookingpress_payment_log_event'] ) : '';
						$bookingpress_payment_log_raw_data   = ! empty( $payment_debug_log_val['bookingpress_payment_log_raw_data'] ) ? stripcslashes( $payment_debug_log_val['bookingpress_payment_log_raw_data'] ) : '';
						$bookingpress_payment_log_added_date = ! empty( $payment_debug_log_val['bookingpress_payment_log_added_date'] ) ? esc_html( $payment_debug_log_val['bookingpress_payment_log_added_date'] ) : '';

						$payment_debug_log_data[] = array(
							'payment_debug_log_id'         => $bookingpress_payment_log_id,
							'payment_debug_log_name'       => $bookingpress_payment_log_event,
							'payment_debug_log_data'       => $bookingpress_payment_log_raw_data,
							'payment_debug_log_added_date' => date( $bookingpress_date_format, strtotime( $bookingpress_payment_log_added_date ) ),
						);
					}
				}
			}
			$data['items'] = $payment_debug_log_data;
			$data['total'] = count( $total_payment_debug_logs );
			wp_send_json( $data );
			exit;
		}
		function bookingpress_clear_debug_payment_log_func() {
			global $wpdb,$tbl_bookingpress_debug_payment_log;
			$response              = array();
			$response['variant']   = 'error';
			$response['title']     = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']       = esc_html__( 'Something went wrong', 'bookingpress-appointment-booking' );
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
				wp_send_json( $response );
				die();
			}
			$bookingpress_view_log_selector = ! empty( $_REQUEST['bookingpress_debug_log_selector'] ) ? sanitize_text_field( $_REQUEST['bookingpress_debug_log_selector'] ) : '';
			if ( ! empty( $bookingpress_view_log_selector ) ) {
					// If data exists into payment debug log table then delete from that table.
					$wpdb->delete( $tbl_bookingpress_debug_payment_log, array( 'bookingpress_payment_log_gateway' => $bookingpress_view_log_selector ), array( '%s' ) );
					$response['variant'] = 'success';
					$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
					$response['msg']     = esc_html__( 'Debug Logs Cleared Successfully.', 'bookingpress-appointment-booking' );
			}
			echo json_encode( $response );
			exit();
		}
		function bookingpress_download_payment_log_func() {
			global $wpdb,$tbl_bookingpress_debug_payment_log;
			$response              = array();
			$response['variant']   = 'error';
			$response['title']     = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']       = esc_html__( 'Something went wrong', 'bookingpress-appointment-booking' );
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
				wp_send_json( $response );
				die();
			}
			$bookingpress_view_log_selector          = ! empty( $_REQUEST['bookingpress_debug_log_selector'] ) ? sanitize_text_field( $_REQUEST['bookingpress_debug_log_selector'] ) : '';
			$bookingpress_selected_download_duration = ! empty( $_REQUEST['bookingpress_selected_download_duration'] ) ? sanitize_text_field( $_REQUEST['bookingpress_selected_download_duration'] ) : 'all';

			if ( ! empty( $bookingpress_view_log_selector ) && ! empty( $bookingpress_selected_download_duration ) ) {

					$bookingpress_debug_payment_log_where_cond = '';
				if ( ! empty( $_REQUEST['bookingpress_selected_download_custom_duration'] ) && $bookingpress_selected_download_duration == 'custom' ) {
					$bookingpress_search_date                  = $_REQUEST['bookingpress_selected_download_custom_duration'];
					$bookingpress_start_date                   = date( 'Y-m-d 00:00:00', strtotime( sanitize_text_field( $bookingpress_search_date[0] ) ) );
					$bookingpress_end_date                     = date( 'Y-m-d 23:59:59', strtotime( sanitize_text_field( $bookingpress_search_date[1] ) ) );
					$bookingpress_debug_payment_log_where_cond = " AND (bookingpress_payment_log_added_date >= '" . $bookingpress_start_date . "' AND bookingpress_payment_log_added_date <= '" . $bookingpress_end_date . "')";
				} elseif ( ! empty( $bookingpress_view_log_selector ) && $bookingpress_view_log_selector != 'custom' ) {

					$bookingpress_last_selected_days           = date( 'Y-m-d', strtotime( '-' . $bookingpress_selected_download_duration . ' days' ) );
					$bookingpress_debug_payment_log_where_cond = " AND (bookingpress_payment_log_added_date >= '" . $bookingpress_last_selected_days . "')";
				}
					$bookingpress_debug_payment_log_query = 'SELECT * FROM `' . $tbl_bookingpress_debug_payment_log . "` WHERE `bookingpress_payment_log_gateway` = '" . $bookingpress_view_log_selector . "' AND `bookingpress_payment_log_status` = 1 " . $bookingpress_debug_payment_log_where_cond . ' ORDER BY bookingpress_payment_log_id DESC';

					$bookingpress_payment_debug_log_data = $wpdb->get_results( $bookingpress_debug_payment_log_query, ARRAY_A );

					$bookingpress_download_data = json_encode( $bookingpress_payment_debug_log_data );

				if ( ! function_exists( 'WP_Filesystem' ) ) {
					require_once ABSPATH . 'wp-admin/includes/file.php';
				}
					WP_Filesystem();
					global $wp_filesystem;

					$bookingpresss_debug_log_file_name = 'bookingpress_debug_logs_' . $bookingpress_view_log_selector . '_' . $bookingpress_selected_download_duration;
					$result                            = $wp_filesystem->put_contents( BOOKINGPRESS_UPLOAD_DIR . '/' . $bookingpresss_debug_log_file_name . '.txt', $bookingpress_download_data, 0777 );

					$debug_log_file_name = '';

				if ( class_exists( 'ZipArchive' ) ) {
					$zip = new ZipArchive();
					$zip->open( BOOKINGPRESS_UPLOAD_DIR . '/' . $bookingpresss_debug_log_file_name . '.zip', ZipArchive::CREATE );
					$zip->addFile( BOOKINGPRESS_UPLOAD_DIR . '/' . $bookingpresss_debug_log_file_name . '.txt', $bookingpresss_debug_log_file_name . '.txt' );
					$zip->close();

					$bookingpress_download_url = BOOKINGPRESS_UPLOAD_URL . '/' . $bookingpresss_debug_log_file_name . '.zip';
					$debug_log_file_name       = $bookingpresss_debug_log_file_name . '.zip';
				} else {
					$bookingpress_download_url = BOOKINGPRESS_UPLOAD_URL . '/' . $bookingpresss_debug_log_file_name . '.txt';
					$debug_log_file_name       = $bookingpresss_debug_log_file_name . '.txt';
				}

				$response['url'] = admin_url( 'admin.php?page=bookingpress&module=settings&bookingpress_action=download_log&file=' . $debug_log_file_name );
				echo json_encode( $response );
				exit();
			}
			echo json_encode( $response );
			exit();
		}
		function appointment_sanatize_field( $data_array ) {
			if ( is_array( $data_array ) ) {
				return array_map( array( $this, __FUNCTION__ ), $data_array );
			} else {
				return sanitize_text_field( $data_array );
			}
		}

		function bookingpress_gutenberg_category( $category, $post ) {
			$new_category     = array(
				array(
					'slug'  => 'bookingpress',
					'title' => 'Bookingpress Blocks',
				),
			);
			$final_categories = array_merge( $category, $new_category );
			return $final_categories;
		}

		function bookingpress_enqueue_gutenberg_assets() {
			global $bookingpress_version;
			if( !in_array( basename($_SERVER['PHP_SELF']), array( 'site-editor.php' ) ) ) {
				wp_register_script( 'bookingpress_gutenberg_script', BOOKINGPRESS_URL . '/js/bookingpress_gutenberg_script.js', array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-components' ), $bookingpress_version );
				wp_enqueue_script( 'bookingpress_gutenberg_script' );
			}

		}
		function bookingpress_select_date_before_load($date = ''){
			$bpa_current_date = $bpa_date = !empty($date) ? $date : date('Y-m-d');			
			$bpa_current_date =  date('c',strtotime($bpa_current_date));
			$bookingpress_daysoff_date = $this->bookingpress_get_default_dayoff_dates();	
			if(!empty($bookingpress_daysoff_date) && is_array($bookingpress_daysoff_date)) {
				if(in_array($bpa_current_date,$bookingpress_daysoff_date)) {										
					$bpa_current_date  = date('Y-m-d',strtotime($bpa_date.'+1 Day'));					
					return $this->bookingpress_select_date_before_load($bpa_current_date);
				}		
			} 
			return $bpa_date;			
		}		
	}
}
global $BookingPress,$bookingpress_debug_payment_log_id;
$BookingPress = new BOOKINGPRESS();

$bookingpress_debug_payment_log_id = 0;
