<?php
if ( ! class_exists( 'bookingpress_settings' ) ) {
	class bookingpress_settings {

		function __construct() {
			add_action( 'wp_ajax_bookingpress_save_settings_data', array( $this, 'bookingpress_save_settings_details' ) );
			add_action( 'wp_ajax_bookingpress_get_settings_details', array( $this, 'bookingpress_get_settings_details' ) );
			add_action( 'wp_ajax_bookingpress_save_default_work_hours', array( $this, 'bookingpress_save_default_work_hours' ) );
			add_action( 'wp_ajax_bookingpress_get_default_work_hours_details', array( $this, 'bookingpress_get_default_work_hours' ) );
			add_action( 'wp_ajax_bookingpress_save_default_daysoff', array( $this, 'bookingpress_save_default_daysoff_details' ) );
			add_action( 'wp_ajax_bookingpress_get_default_daysoff_details', array( $this, 'bookingpress_get_default_daysoff_details' ) );

			add_action( 'bookingpress_settings_dynamic_vue_methods', array( $this, 'bookingpress_setting_dynamic_vue_methods_func' ), 10 );
			add_action( 'bookingpress_settings_dynamic_data_fields', array( $this, 'bookingpress_setting_dynamic_data_fields_func' ), 10 );
			add_action( 'bookingpress_settings_dynamic_helper_vars', array( $this, 'bookingpress_setting_dynamic_helper_vars_func' ), 10 );
			add_action( 'bookingpress_settings_dynamic_view_load', array( $this, 'bookingpress_dynamic_load_setting_content_func' ), 10 );
			add_action( 'bookingpress_settings_dynamic_on_load_methods', array( $this, 'bookingpress_settings_dynamic_on_load_methods_func' ), 10 );
			add_action( 'bookingpress_settings_dynamic_computed_methods', array( $this, 'bookingpress_settings_dynamic_computed_methods_func' ), 10 );
			add_action( 'bookingpress_settings_dynamic_data_fields_vars', array( $this, 'bookingpress_settings_dynamic_data_fields_vars_func' ), 10 );

			add_action( 'wp_ajax_bookingpress_upload_company_avatar', array( $this, 'bookingpress_upload_company_avatar_func' ), 10 );
			add_action( 'wp_ajax_bookingpress_send_test_email', array( $this, 'bookingpress_send_test_email_func' ) );
			add_action( 'wp_ajax_bookingpress_save_default_daysoff_details', array( $this, 'bookingpress_save_default_daysoff_details_func' ), 10 );
			add_action( 'wp_ajax_bookingpress_get_daysoff_details', array( $this, 'bookingpress_get_daysoff_details_func' ), 10 );
			add_action( 'wp_ajax_bookingpress_delete_daysoff_details', array( $this, 'bookingpress_delete_daysoff_details_func' ), 10 );
		}

		function bookingpress_delete_daysoff_details_func() {
			global $wpdb, $tbl_bookingpress_default_daysoff;

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

			$daysoff_date = ! empty( $_REQUEST['days_off_form']['selected_date'] ) ? sanitize_text_field( $_REQUEST['days_off_form']['selected_date'] ) : '';
			if ( ! empty( $daysoff_date ) ) {
				$daysoff_date = date( 'Y-m-d', strtotime( $daysoff_date ) );

				$wpdb->query( "DELETE FROM {$tbl_bookingpress_default_daysoff} WHERE bookingpress_dayoff_date LIKE '%{$daysoff_date}%'" );
			}

			$response['variant'] = 'success';
			$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
			$response['msg']     = esc_html__( 'DaysOff deleted successfully', 'bookingpress-appointment-booking' );

			wp_send_json( $response );
			exit();
		}

		function bookingpress_get_daysoff_details_func() {
			global $wpdb, $tbl_bookingpress_default_daysoff;
			$response                 = array();
			$response['variant']      = 'error';
			$response['title']        = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']          = esc_html__( 'Something went wrong', 'bookingpress-appointment-booking' );
			$response['daysoff_data'] = '';
			$wpnonce                  = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag    = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
				wp_send_json( $response );
				die();
			}

			$daysoff_selected_year = ! empty( $_POST['selected_year'] ) ? sanitize_text_field( $_POST['selected_year'] ) : date( 'Y', current_time( 'timestamp' ) );

			$default_daysoff_details = array();
			$daysoff_details         = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_default_daysoff}", ARRAY_A );
			foreach ( $daysoff_details as $daysoff_details_key => $daysoff_details_val ) {
				$daysoff_date        = esc_html( $daysoff_details_val['bookingpress_dayoff_date'] );
				$yearly_repeat_class = ! empty( $daysoff_details_val['bookingpress_repeat'] ) ? 'bpa-daysoff-calendar-col--item__highlight--yearly bpa_selected_daysoff' : 'bpa-daysoff-calendar-col--item__highlight--single-dayoff bpa_selected_daysoff';

				$dayoff_year = date( 'Y', strtotime( $daysoff_date ) );

				if ( empty( $daysoff_details_val['bookingpress_repeat'] ) && ( $dayoff_year == $daysoff_selected_year ) ) {
					$default_daysoff_details[] = array(
						'id'       => date( 'Y-m-d', strtotime( $daysoff_date ) ),
						'date'     => date( 'c', strtotime( $daysoff_date ) ),
						'class'    => $yearly_repeat_class,
						'off_name' => esc_html( $daysoff_details_val['bookingpress_name'] ),
					);
				} elseif ( ! empty( $daysoff_details_val['bookingpress_repeat'] ) && ( $daysoff_selected_year >= $dayoff_year ) ) {
					$daysoff_new_date_month    = $daysoff_selected_year . '-' . date( 'm-d', strtotime( $daysoff_date ) );
					$default_daysoff_details[] = array(
						'id'       => $daysoff_new_date_month,
						'date'     => date( 'c', strtotime( $daysoff_new_date_month ) ),
						'class'    => $yearly_repeat_class,
						'off_name' => esc_html( $daysoff_details_val['bookingpress_name'] ),
					);
				}
			}

			$response['variant']      = 'success';
			$response['title']        = esc_html__( 'Success', 'bookingpress-appointment-booking' );
			$response['msg']          = esc_html__( 'DaysOff data retrieved successfully', 'bookingpress-appointment-booking' );
			$response['daysoff_data'] = $default_daysoff_details;

			echo json_encode( $response );
			exit();
		}

		function bookingpress_save_default_daysoff_details_func() {
			global $wpdb, $tbl_bookingpress_default_daysoff;
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

			if ( ! empty( $_REQUEST['daysoff_details'] ) ) {

				$daysoff_title     = ! empty( $_REQUEST['daysoff_details']['daysoff_title'] ) ? sanitize_text_field( $_REQUEST['daysoff_details']['daysoff_title'] ) : '';
				$is_repeat_daysoff = ! empty( $_REQUEST['daysoff_details']['is_repeat_days_off'] ) ? sanitize_text_field( $_REQUEST['daysoff_details']['is_repeat_days_off'] ) : '';
				$is_repeat_daysoff = ( $is_repeat_daysoff == 'true' ) ? 1 : 0;
				$daysoff_date      = ! empty( $_REQUEST['daysoff_details']['selected_date'] ) ? sanitize_text_field( $_REQUEST['daysoff_details']['selected_date'] ) : '';
				if ( ! empty( $daysoff_date ) ) {
					$daysoff_date = date( 'Y-m-d', strtotime( $daysoff_date ) );
				}

				if ( ! empty( $daysoff_title ) ) {
					$daysoff_database_data = array(
						'bookingpress_name'        => $daysoff_title,
						'bookingpress_dayoff_date' => $daysoff_date,
						'bookingpress_repeat'      => $is_repeat_daysoff,
					);

					$dayoff_exist_or_not = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_default_daysoff} WHERE bookingpress_dayoff_date LIKE '%{$daysoff_date}%'", ARRAY_A );
					if ( ! empty( $dayoff_exist_or_not ) ) {
						$dayoff_where_condition = array(
							'bookingpress_dayoff_id' => $dayoff_exist_or_not['bookingpress_dayoff_id'],
						);
						$wpdb->update( $tbl_bookingpress_default_daysoff, $daysoff_database_data, $dayoff_where_condition );
					} else {
						$wpdb->insert( $tbl_bookingpress_default_daysoff, $daysoff_database_data );
					}

					$response['variant'] = 'success';
					$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
					$response['msg']     = esc_html__( 'Days off has been saved successfully.', 'bookingpress-appointment-booking' );
				} else {
					$response['msg'] = esc_html__( 'Please fill Break Title', 'bookingpress-appointment-booking' );
				}
			}

			echo json_encode( $response );
			exit();
		}

		function bookingpress_send_test_email_func() {
			global $bookingpress_email_notifications;
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

			if ( ! empty( $_REQUEST['notification_formdata'] ) ) {
				$smtp_host         = ! empty( $_REQUEST['notification_formdata']['smtp_host'] ) ? esc_html( $_REQUEST['notification_formdata']['smtp_host'] ) : '';
				$smtp_port         = ! empty( $_REQUEST['notification_formdata']['smtp_port'] ) ? esc_html( $_REQUEST['notification_formdata']['smtp_port'] ) : '';
				$smtp_secure       = ! empty( $_REQUEST['notification_formdata']['smtp_secure'] ) ? esc_html( $_REQUEST['notification_formdata']['smtp_secure'] ) : 'Disabled';
				$smtp_username     = ! empty( $_REQUEST['notification_formdata']['smtp_username'] ) ? esc_html( $_REQUEST['notification_formdata']['smtp_username'] ) : '';
				$smtp_password     = ! empty( $_REQUEST['notification_formdata']['smtp_password'] ) ? $_REQUEST['notification_formdata']['smtp_password'] : '';
				$smtp_sender_name  = ! empty( $_REQUEST['notification_formdata']['sender_name'] ) ? esc_html( $_REQUEST['notification_formdata']['sender_name'] ) : '';
				$smtp_sender_email = ! empty( $_REQUEST['notification_formdata']['sender_email'] ) ? esc_html( $_REQUEST['notification_formdata']['sender_email'] ) : '';
				$smtp_test_receiver_email = ! empty( $_REQUEST['notification_test_mail_formdata']['smtp_test_receiver_email'] ) ? esc_html( $_REQUEST['notification_test_mail_formdata']['smtp_test_receiver_email'] ) : '';
				$smtp_test_msg            = ! empty( $_REQUEST['notification_test_mail_formdata']['smtp_test_msg'] ) ? esc_html( $_REQUEST['notification_test_mail_formdata']['smtp_test_msg'] ) : '';

				$bookingpress_email_res = $bookingpress_email_notifications->bookingpress_send_test_email_notification( $smtp_host, $smtp_port, $smtp_secure, $smtp_username, $smtp_password, $smtp_test_receiver_email, $smtp_test_msg, $smtp_sender_email, $smtp_sender_name );
				$bookingpress_email_res = json_decode( $bookingpress_email_res, true );

				$response = array(
					'is_mail_sent' => $bookingpress_email_res['is_mail_sent'],
					'error_msg'    => $bookingpress_email_res['error_msg'],
				);
			}

			echo json_encode( $response );
			exit();
		}

		function bookingpress_upload_company_avatar_func() {
			$return_data = array(
				'error'            => 0,
				'msg'              => '',
				'upload_url'       => '',
				'upload_file_name' => '',
			);

			$bookingpress_fileupload_obj = new bookingpress_fileupload_class( $_FILES['file'] );

			if ( ! $bookingpress_fileupload_obj ) {
				$return_data['error'] = 1;
				$return_data['msg']   = $bookingpress_fileupload_obj->error_message;
			}

			$bookingpress_fileupload_obj->check_cap          = true;
			$bookingpress_fileupload_obj->check_nonce        = true;
			$bookingpress_fileupload_obj->nonce_data         = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bookingpress_fileupload_obj->nonce_action       = isset( $_REQUEST['action'] ) ? sanitize_text_field( $_REQUEST['action'] ) : '';
			$bookingpress_fileupload_obj->check_only_image   = true;
			$bookingpress_fileupload_obj->check_specific_ext = false;
			$bookingpress_fileupload_obj->allowed_ext        = array();

			$file_name                = current_time( 'timestamp' ) . '_' . sanitize_file_name( $_FILES['file']['name'] );
			$upload_dir               = BOOKINGPRESS_TMP_IMAGES_DIR . '/';
			$upload_url               = BOOKINGPRESS_TMP_IMAGES_URL . '/';
			$bookingpress_destination = $upload_dir . $file_name;

			$upload_file = $bookingpress_fileupload_obj->bookingpress_process_upload( $bookingpress_destination );
			if ( $upload_file == false ) {
				$return_data['error'] = 1;
				$return_data['msg']   = ! empty( $upload_file->error_message ) ? $upload_file->error_message : esc_html__( 'Something went wrong while updating the file', 'bookingpress-appointment-booking' );
			} else {
				$return_data['error']            = 0;
				$return_data['msg']              = '';
				$return_data['upload_url']       = $upload_url . $file_name;
				$return_data['upload_file_name'] = sanitize_file_name( $_FILES['file']['name'] );
			}

			echo json_encode( $return_data );
			exit();
		}

		function bookingpress_settings_dynamic_data_fields_vars_func() {
			?>
			<?php
		}


		function bookingpress_settings_dynamic_computed_methods_func() {
			?>
				dates() {
					  return this.days.map(day => (
						  {
							  selected_date: day.date,
							  selected_class: day.class
						  }
					  ));
				},
				attributes() {
					return this.dates.map(date => (
						{
							highlight: {
								class: date.selected_class
							},
							dates: date.selected_date,
						}	
					));
				}
			<?php
		}


		function bookingpress_settings_dynamic_on_load_methods_func() {
			global $bookingpress_notification_duration;
			?>
				const vm = this
				if(vm.selected_tab == "0"){
					var selected_tab_index = localStorage.getItem("selected_tab")
					selected_tab_index = (selected_tab_index == '' || selected_tab_index == 'null' || selected_tab_index == null || selected_tab_index == 'undefined') ? '0' : selected_tab_index
					vm.selected_tab = selected_tab_index
				}

				if(vm.selected_tab == "0"){
					vm.getSettingsData('general_setting', 'general_setting_form');
				}else if(vm.selected_tab == "1"){
					vm.getSettingsData('company_setting','company_setting_form')
				}else if(vm.selected_tab == "2"){
					vm.getSettingsData('notification_setting','notification_setting_form')
				}else if(vm.selected_tab == "3"){
					vm.getSettingsData('customer_setting','customer_setting_form')
				}else if(vm.selected_tab == "4"){
					var postdata = [];
					postdata.action = 'bookingpress_get_default_work_hours_details';
					postdata._wpnonce = '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>';
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify(postdata))
					.then(function(response){
						vm.is_disabled = false
						vm.is_display_loader = '0'
						vm.work_hours_days_arr = response.data.data
						response.data.data.forEach(function(currentValue, index, arr){
							vm.selected_break_timings[currentValue.day_name] = currentValue.break_times
						});
						vm.workhours_timings = response.data.selected_workhours
						vm.default_break_timings = response.data.default_break_times
					}).catch(function(error){
						console.log(error);
						vm.$notify({
							title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
							message: '<?php esc_html_e( 'Something Went wrong...', 'bookingpress-appointment-booking' ); ?>',
							type: 'error',
							customClass: 'error_notification',
							duration:<?php echo intval($bookingpress_notification_duration); ?>,
						});
					});
				}else if(vm.selected_tab == "5"){
					var postdata = [];
					postdata.action = 'bookingpress_get_default_daysoff_details';
					postdata._wpnonce= '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>'
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify(postdata))
					.then(function(response){
						vm.employee_dayoff_arr = response.data
					}).catch(function(error){
						console.log(error);
						vm2.$notify({
							title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
							message: '<?php esc_html_e( 'Something Went wrong...', 'bookingpress-appointment-booking' ); ?>',
							type: 'error',
							customClass: 'error_notification',
							duration:<?php echo intval($bookingpress_notification_duration); ?>,
						});
					});

					this.loadAttributes();
					this.handleWrapperEvent();
				}else if(vm.selected_tab == "6"){
					vm.getSettingsData('payment_setting', 'payment_setting_form')
				}else if(vm.selected_tab == "7"){
					vm.getSettingsData('message_setting', 'message_setting_form')
				}else if(vm.selected_tab == "8"){
					vm.getSettingsData('debug_log_setting', 'debug_log_setting_form')
				}	
			<?php
			do_action( 'bookingpress_settings_add_dynamic_on_load_method' );
		}



		function bookingpress_setting_dynamic_vue_methods_func() {
			global $bookingpress_notification_duration;
			?>
			handleSizeChange(val) {				
				const vm = this
				var log_type =vm.open_view_model_gateway
				this.perPage = val
				this.bookingpess_view_log(log_type)
			},		
			handleCurrentChange(val) {
				const vm = this
				var log_type = vm.open_view_model_gateway
				this.currentPage = val;				
				this.bookingpess_view_log(log_type,'pagination');
			},
			handleWrapperEvent(){
				document.addEventListener( 'click', function(e){			

					if( e.target == null || !e.target.classList.contains('el-dialog__wrapper') ){
						return false;
					}
					let all_highlighted_el = document.querySelectorAll('.vc-highlights.vc-day-layer');

					if( all_highlighted_el.length > 0 ){
						for( let i = 0; i < all_highlighted_el.length; i++ ){
							let current_el = all_highlighted_el[i];
							if( current_el.querySelector('.bpa_selected_daysoff') != null ){
								continue;
							}
							current_el.parentNode.removeChild( current_el );
						}
					}
				});
			},
			loadAttributes() {
				const vm = this
				var loadAttrsData = []
				loadAttrsData.action = 'bookingpress_get_daysoff_details'
				loadAttrsData._wpnonce = '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>'
				loadAttrsData.selected_year = vm.daysoff_selected_year
				axios.post(appoint_ajax_obj.ajax_url, Qs.stringify(loadAttrsData))
				.then(function(response){
					if(response.data.variant == 'error'){
						vm.$notify({
							title: response.data.title,
							message: response.data.msg,
							type: response.data.variant,
							customClass: response.data.variant+'_notification',
							duration:<?php echo intval($bookingpress_notification_duration); ?>,
						});
					}else{
						vm.days = response.data.daysoff_data
					}
				}).catch(function(error){
					console.log(error)
				});
			},
			bookingpress_daysoff_selected_year(selectedValue){
				const vm = this
				var bookingpress_selected_date_obj = new Date(selectedValue)
				var bookingpress_selected_year = bookingpress_selected_date_obj.getFullYear();
				vm.daysoff_selected_year = bookingpress_selected_year
				this.loadCalendarDates(bookingpress_selected_year)
				this.loadAttributes()
			},
			delete_dayoff(){
				const vm = this
				var deleteAttrData = []
				deleteAttrData.action = 'bookingpress_delete_daysoff_details'
				deleteAttrData._wpnonce = '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>'
				deleteAttrData.days_off_form = vm.days_off_form
				axios.post(appoint_ajax_obj.ajax_url, Qs.stringify(deleteAttrData))
				.then(function(response){
					if(response.data.variant == 'error'){
						vm.$notify({
							title: response.data.title,
							message: response.data.msg,
							type: response.data.variant,
							customClass: response.data.variant+'_notification',
							duration:<?php echo intval($bookingpress_notification_duration); ?>,
						});
					}
					vm.open_add_daysoff_details = false
					vm.loadAttributes()
				}).catch(function(error){
					console.log(error)
				});	
			},
			onDayClick(day) {
				const vm = this
				var is_edit = 0;
				var selected_date = day.id
				vm.days_off_form.selected_date = selected_date
				vm.days_off_form.daysoff_title = ''
				vm.days_off_form.is_repeat_days_off = false

				vm.days.forEach(function(item, index, arr){
					if(item.id == selected_date){
						is_edit = 1
						vm.days_off_form.daysoff_title = item.off_name
						if(item.class == 'bpa-daysoff-calendar-col--item__highlight--yearly bpa_selected_daysoff'){
							vm.days_off_form.is_repeat_days_off = true
						}
					}
				})

				vm.days_off_form.is_edit = is_edit
				
				vm.open_add_daysoff_details = true
				
				var dialog_pos_x = day.el.getBoundingClientRect().left - 253;
				var dialog_pos_y = day.el.getBoundingClientRect().top + 40;
				
				vm.$el.querySelector('.el-dialog__wrapper:not(#breaks_add_modal) .el-dialog.bpa-add-dayoff-dialog').style.position = 'absolute';
				vm.$el.querySelector('.el-dialog__wrapper:not(#breaks_add_modal) .el-dialog.bpa-add-dayoff-dialog').style.marginTop = '0px';
				vm.$el.querySelector('.el-dialog__wrapper:not(#breaks_add_modal) .el-dialog.bpa-add-dayoff-dialog').style.top = dialog_pos_y + 'px';
				vm.$el.querySelector('.el-dialog__wrapper:not(#breaks_add_modal) .el-dialog.bpa-add-dayoff-dialog').style.left = dialog_pos_x + 'px';
				
				
				if(is_edit != 1){
					const idx = vm.days.findIndex(d => d.id === day.id);
					if (idx >= 0) {
						this.days.splice(idx, 1);
					} else {
						this.days.push({
							id: day.id,
							date: day.date,
							class: 'bpa-daysoff-calendar-col--item__highlight--single-dayoff'
						});
					}
				}
			},
			save_daysoff_details(form_name){
				const vm = this
				vm.$refs[form_name].validate((valid) => {
					if(valid) {
						var saveFormData = []								
						vm.is_disabled = true
						vm.is_display_save_loader = '1'
						saveFormData._wpnonce = '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>'
						saveFormData.action = 'bookingpress_save_default_daysoff_details'
						saveFormData.daysoff_details = vm.days_off_form 
						axios.post(appoint_ajax_obj.ajax_url, Qs.stringify(saveFormData))
						.then(function(response){								
							vm.is_disabled = false
							vm.is_display_save_loader = '0'
							vm.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: response.data.variant,
								customClass: response.data.variant+'_notification',
								duration:<?php echo intval($bookingpress_notification_duration); ?>,
							});
							vm.open_add_daysoff_details = false
							vm.loadAttributes()
						}).catch(function(error){
							console.log(error)
						});
					}	
				})
			},
			add_employee_dayoff_form() {
				this.employee_dayoff_form.dayoff_name = ''
				this.employee_dayoff_form.dayoff_date = ''
				this.employee_dayoff_form.dayoff_repeat = false
				this.edit_employee_dayoff = ''
				this.add_employee_dayoff = 1
			},
			addEmployeeDayoff(employee_dayoff_form) {
				var vm = this
				var ilength = parseInt(vm.employee_dayoff_arr.length) + 1;
				let empDaysOffData = {};
				Object.assign(empDaysOffData, {id: 'dayoff_'+ilength})
				Object.assign(empDaysOffData, {dayoff_date: vm.employee_dayoff_form.dayoff_date})
				Object.assign(empDaysOffData, {dayoff_name: vm.employee_dayoff_form.dayoff_name})
				Object.assign(empDaysOffData, {dayoff_repeat: vm.employee_dayoff_form.dayoff_repeat})
				vm.employee_dayoff_arr.push(empDaysOffData)
				vm.add_employee_dayoff = 0
			},
			show_edit_dayoff_div(day_off_id) {
				var vm = this
				vm.add_employee_dayoff = 0
				vm.employee_dayoff_arr.forEach(function(item, index, arr)
				{
					if (item.id == day_off_id) {
						vm.edit_dayoff_name = item.dayoff_name
						vm.edit_dayoff_date = item.dayoff_date
						vm.edit_dayoff_repeat = item.dayoff_repeat
					}
				})
				vm.edit_employee_dayoff = day_off_id
			},
			delete_dayoff_div(day_off_id) {
				var vm = this
				vm.add_employee_dayoff = 0
				vm.employee_dayoff_arr.forEach(function(item, index, arr)
				{
					if (item.id == day_off_id) {
						vm.employee_dayoff_arr.splice(index, 1);
					}
				})
			},
			editEmployeeDayoff() {
				var vm = this
				var dayoff_id = vm.edit_employee_dayoff
				var dayoff_name = vm.edit_dayoff_name
				var dayoff_date = vm.edit_dayoff_date
				var dayoff_repeat = vm.edit_dayoff_repeat
				vm.employee_dayoff_arr.forEach(function(item, index, arr)
				{
					if(item.id == dayoff_id)
					{
						item.dayoff_name = dayoff_name
						item.dayoff_date = dayoff_date
						item.dayoff_repeat = dayoff_repeat
					}
				})
				vm.edit_employee_dayoff = 0
			},
			closeEmployeeDayoff() {
				this.add_employee_dayoff = 0
				this.edit_employee_dayoff = ''
			},
			saveEmployeeDayoff(employee_dayoff) {
				event.preventDefault();
				const vm = new Vue()
				const vm2 = this
				var postdata = [];
				postdata.action = 'bookingpress_save_default_daysoff';
				postdata.daysoff = vm2.employee_dayoff_arr;
				postdata._wpnonce = '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>';	
				axios.post(appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
				.then(function(response){
					vm2.$notify({
						title: response.data.title,
						message: response.data.msg,
						type: response.data.variant,
						customClass: response.data.variant+'_notification',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
				}).catch(function(error){
					console.log(error);
					vm2.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something Went wrong...', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
				});
			},
			show_add_work_hour_div(day_key) {
				this.work_type_modal = day_key+'_work_hours'
				this.add_work_hours_display = day_key
			},
			save_work_hour_data() {
				var vm = this
				var selected_day = vm.add_work_hours_display

				let employeeWorkHoursData = document.getElementById('employee_work_hours');
				let employeeWorkHoursFormData = new FormData(employeeWorkHoursData)
				let empWorkHoursData = {};
				for(let [key, val] of employeeWorkHoursFormData.entries())
				{
					Object.assign(empWorkHoursData, {[key]: val})
				}
				empWorkHoursData.selected_day = selected_day

				var work_hour_type = empWorkHoursData.work_type;
				work_hour_type = work_hour_type.replace(selected_day+'_', "");

				vm.work_hours_days_arr.forEach(function(item, index, arr)
				{
					if(item.day_key == selected_day)
					{
						var is_break = 1

						if(work_hour_type == "work_hours")
						{
							is_break = 0;
						}
						var ilength = parseInt(item.day_services_data.length) + 1;
						var new_added_work_hour_data = {
							id: selected_day+'_'+ilength,
							start_time: empWorkHoursData.start_time,
							end_time: empWorkHoursData.end_time,
							is_break: is_break,
						};

						item.day_services_data.push(new_added_work_hour_data)
						empWorkHoursData.is_break = is_break
						empWorkHoursData.update_id = 0
						vm.final_work_hours_data.push(empWorkHoursData)
					}
				})
				this.add_work_hours_display = ''
				this.work_start_time = ''
				this.work_end_time = ''
			},
			hide_work_hour_div() {
				this.add_work_hours_display = ''
			},
			apply_to_all_days(day_key) {
				var vm = this
				var monday_services_data = []
				vm.work_hours_days_arr.forEach(function(item, index, arr)
				{
					if(item.day_key == day_key)
					{
						monday_services_data = item.day_services_data
					}
				})
				vm.work_hours_days_arr.forEach(function(item, index, arr)
				{
					if(item.day_key != day_key)
					{
						item.day_services_data = []
						monday_services_data.forEach(function(mitem, mindex, marr)
						{
							var ilength = parseInt(item.day_services_data.length) + 1;
							var new_added_work_hour_data = {
								id: item.day_key+'_'+ilength,
								start_time: mitem.start_time,
								end_time: mitem.end_time,
								is_break: mitem.is_break,
							};
							item.day_services_data.push(new_added_work_hour_data)
						})
					}
				})
			},
			hide_edit_work_hour_div() {
				this.edit_work_start_time = ''
				this.edit_work_end_time = ''
			},
			delete_work_hour_div(day_key, hour_id) {
				var vm = this
				vm.work_hours_days_arr.forEach(function(item, index, arr)
				{
					if(item.day_key == day_key)
					{
						item.day_services_data.forEach(function(ditem, dindex, darr)
						{
							if (ditem.id == hour_id) {
								item.day_services_data.splice(dindex, 1);
							}
						})
					}
				})
			},
			saveEmployeeWorkhours() {
				event.preventDefault();
				const vm = new Vue()
				const vm2 = this
				vm2.is_disabled = true
				vm2.is_display_save_loader = '1'
				var postdata = []
				postdata.workhours_timings = vm2.workhours_timings;
				postdata.action = 'bookingpress_save_default_work_hours';
				postdata.break_data = vm2.selected_break_timings;
				postdata._wpnonce = '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>';				
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify(postdata))
				.then(function(response){
					vm2.is_disabled = false
					vm2.is_display_save_loader = '0'
					vm2.$notify({
						title: response.data.title,
						message: response.data.msg,
						type: response.data.variant,
						customClass: response.data.variant+'_notification',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
					vm2.resetForm()
				}).catch(function(error){
					console.log(error);
					vm2.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something Went wrong...', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
				});

				//vm2.saveEmployeeDayoff('employee_dayoff')
			},
			resetForm(){
				const vm = this
				vm.break_timings.start_time = ''
				vm.break_timings.end_time = ''
				vm.break_timings.old_start_time = ''
				vm.break_timings.old_end_time = ''
			},
			toggleBusy() {
				this.modal_loading = !this.modal_loading
			},
			close_modal(modal_name){
				this.modals[modal_name+'_modal'] = false
			},
			saveSettingsData(form_name,setting_type){
				const vm = this
				if(form_name == "customer_setting_form"){
					vm.is_disabled = true
					vm.is_display_save_loader = '1'
					let saveFormData = vm[form_name]
					saveFormData.action = 'bookingpress_save_settings_data'
					saveFormData.settingType = setting_type
					saveFormData._wpnonce = '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>'
					axios.post(appoint_ajax_obj.ajax_url, Qs.stringify(saveFormData))
					.then(function(response){
						vm.is_disabled = false
						vm.is_display_save_loader = '0'
						vm.$notify({						
							title: response.data.title,
							message: response.data.msg,
							type: response.data.variant,
							customClass: response.data.variant+'_notification',
							duration:<?php echo intval($bookingpress_notification_duration); ?>,
						});
						vm.isloading = false;
						vm.toggleBusy()
					}).catch(function(error){
						console.log(error)
					});
				}else{							
					vm.$refs[form_name].validate((valid) => {						
						if(valid) {
							vm.is_disabled = true
							vm.is_display_save_loader = '1'
							let saveFormData = vm[form_name]
							saveFormData.action = 'bookingpress_save_settings_data'
							saveFormData.settingType = setting_type
							saveFormData._wpnonce = '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>'
							axios.post(appoint_ajax_obj.ajax_url, Qs.stringify(saveFormData))
							.then(function(response){
								vm.is_disabled = false
								vm.is_display_save_loader = '0'
								vm.$notify({						
									title: response.data.title,
									message: response.data.msg,
									type: response.data.variant,
									customClass: response.data.variant+'_notification',
									duration:<?php echo intval($bookingpress_notification_duration); ?>,
								});
								vm.isloading = false;
								vm.toggleBusy()
							}).catch(function(error){
								console.log(error)
							});
						}
					})
				}
			},
			getSettingsData(settingType, form_name){
				const vm = this
				//vm.is_disabled = true
				//vm.is_display_loader = '1'
				let getSettingsDetails = {
					'action': 'bookingpress_get_settings_details',
					'setting_type': settingType,
					'_wpnonce': '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>',
				}
				axios.post(appoint_ajax_obj.ajax_url, Qs.stringify(getSettingsDetails))
				.then(function(response){
					vm.is_disabled = false
					vm.is_display_loader = '0'
					if(response.data.data != '' || response.data.data != []){
						vm[form_name] = response.data.data
						if(settingType == 'company_setting'){
							vm.$refs.avatarRef.clearFiles()
							if(response.data.data.company_phone_country != 'undefined' || response.data.data.company_phone_country != undefined){
								vm.bookingpress_tel_input_props.defaultCountry = response.data.data.company_phone_country
								vm.$refs.bpa_tel_input_field._data.activeCountryCode = response.data.data.company_phone_country;
								vm.company_setting_form.company_phone_country = response.data.data.company_phone_country
							}
							if(response.data.data.company_avatar_url != undefined && response.data.data.company_avatar_url != ''){
								vm.company_setting_form.company_avatar_url = response.data.data.company_avatar_url
								vm.company_setting_form.company_avatar_img = response.data.data.company_avatar_img
							}	
						}
					}
				}).catch(function(error){
					console.log(error)
				});
			},
			bookingpress_upload_company_avatar_func(response, file, fileList){
				const vm2 = this
				if(response != ''){
					vm2.company_setting_form.company_avatar_url = response.upload_url
					vm2.company_setting_form.company_avatar_img = response.upload_file_name
				}
			},
			bookingpress_company_avatar_upload_limit(files, fileList){
				const vm2 = this
				if(files.length >= 1){
					vm2.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Multiple files not allowed', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
				}
			},
			checkUploadedFile(file){
				const vm2 = this
				if(file.type != 'image/jpeg' && file.type != 'image/png'){
					vm2.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Please upload jpg/png file only', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
					return false
				}
			},
			bookingpress_company_avatar_upload_err(err, file, fileList){
				const vm2 = this
				var bookingpress_err_msg = '<?php esc_html_e( 'Something went wrong', 'bookingpress-appointment-booking' ); ?>';
				if(err != '' || err != undefined){
					bookingpress_err_msg = err
				}
				vm2.$notify({
					title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
					message: bookingpress_err_msg,
					type: 'error',
					customClass: 'error_notification',
					duration:<?php echo intval($bookingpress_notification_duration); ?>,
				});
			},
			bookingpress_remove_company_avatar(){
				const vm = this
				var upload_url = vm.company_setting_form.company_avatar_url 					
				var upload_filename = vm.company_setting_form.company_avatar_img 

				var postData = { action:'bookingpress_remove_uploaded_file',upload_file_url: upload_url,_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' };
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {					
					vm.company_setting_form.company_avatar_url = ''
					vm.company_setting_form.company_avatar_img = ''
					vm.$refs.avatarRef.clearFiles()
				}.bind(vm) )
				.catch( function (error) {
					console.log(error);
				});
			},
			settings_tab_select(selected_tab){				
				const vm = this
				localStorage.setItem("selected_tab", selected_tab.index)
				vm.selected_tab = selected_tab.index
				vm.open_add_break_modal = false;
				var current_tabname = selected_tab.$el.dataset.tab_name;			

				if(current_tabname == "general_settings"){
					vm.getSettingsData('general_setting', 'general_setting_form')
				} else if (current_tabname == "company_settings") {
					vm.getSettingsData('company_setting','company_setting_form')
				} else if (current_tabname == "labels_settings") {
					vm.getSettingsData('label_setting', 'label_setting_form')	
				} else if (current_tabname == "notification_settings") {
					vm.getSettingsData('notification_setting','notification_setting_form')
				} else if (current_tabname == "payment_settings") {
					vm.getSettingsData('payment_setting', 'payment_setting_form')
				}else if (current_tabname == "debug_log_settings") {
					vm.getSettingsData('debug_log_setting', 'debug_log_setting_form')
				} else if (current_tabname == "message_settings") {
					vm.getSettingsData('message_setting', 'message_setting_form')
				} else if (current_tabname == "customer_settings") {
					vm.getSettingsData('customer_setting', 'customer_setting_form')
				} else if (current_tabname == "workhours_settings") {
					//vm.is_disabled = true
					//vm.is_display_loader = '1'
					//Get already added default workhours
					var postdata = [];
					postdata.action = 'bookingpress_get_default_work_hours_details';
					postdata._wpnonce = '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>';
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify(postdata))
					.then(function(response){
						vm.is_disabled = false
						vm.is_display_loader = '0'
						vm.work_hours_days_arr = response.data.data
						response.data.data.forEach(function(currentValue, index, arr){
							vm.selected_break_timings[currentValue.day_name] = currentValue.break_times
						});
						vm.workhours_timings = response.data.selected_workhours
						vm.default_break_timings = response.data.default_break_times
					}).catch(function(error){
						console.log(error);
						vm.$notify({
							title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
							message: '<?php esc_html_e( 'Something Went wrong...', 'bookingpress-appointment-booking' ); ?>',
							type: 'error',
							customClass: 'error_notification',
							duration:<?php echo intval($bookingpress_notification_duration); ?>,
						});
					});
				}else if( current_tabname == "daysoff_settings" ){
					this.loadAttributes();
					this.handleWrapperEvent();
				}
				<?php
				do_action('bookingpress_dynamic_get_settings_data');
				?>
			},
			open_add_break_modal_func(currentElement, breakSelectedDay){
				var dialog_pos = currentElement.target.getBoundingClientRect();
				this.break_modal_pos = (dialog_pos.top - 90)+'px'
				this.break_modal_pos_right = (dialog_pos.right + 38)+'px';
				this.open_add_break_modal = true
				this.break_selected_day = breakSelectedDay
			},
			savebreakdata(){
				const vm = this
				var is_edit = 0;
				if(vm.break_timings.start_time > vm.break_timings.end_time) {
					vm.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Start time not greater than to end time', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
				}else if(vm.break_timings.start_time == vm.break_timings.end_time) {					
					vm.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Start time and end time are not same', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});				
				} else {
					vm.$refs['break_timings'].validate((valid) => {
						if(valid) {	
							var update = 0;							
							if(vm.selected_break_timings[vm.break_selected_day] != '' ) { 							
								vm.selected_break_timings[vm.break_selected_day].forEach(function(currentValue, index, arr) {						
									if(is_edit == 0) {
										if(currentValue['start_time'] == vm.break_timings.start_time && currentValue['end_time'] == 
											vm.break_timings.end_time ) {
											currentValue.start_time = vm.break_timings.start_time
											currentValue.end_time = vm.break_timings.end_time
											is_edit = 1;
											vm.$notify({
												title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
												message: '<?php esc_html_e( 'Break time already added', 'bookingpress-appointment-booking' ); ?>',
												type: 'error',
												customClass: 'error_notification',
												duration:<?php echo intval($bookingpress_notification_duration); ?>,
											});
										}else if(currentValue['start_time'] <= vm.break_timings.start_time && currentValue['end_time'] >=  
										vm.break_timings.end_time ) {																	
											is_edit = 1;
											vm.$notify({
												title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
												message: '<?php esc_html_e( 'Break time already added', 'bookingpress-appointment-booking' ); ?>',
												type: 'error',
												customClass: 'error_notification',
												duration:<?php echo intval($bookingpress_notification_duration); ?>,
											});
										}else if(vm.workhours_timings[vm.break_selected_day].start_time > vm.break_timings.start_time || vm.workhours_timings[vm.break_selected_day].end_time < vm.break_timings.end_time) {	
											is_edit = 1;
											vm.$notify({
												title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
												message: '<?php esc_html_e( 'Please input valid time for break', 'bookingpress-appointment-booking' ); ?>',
												type: 'error',
												customClass: 'error_notification',
												duration:<?php echo intval($bookingpress_notification_duration); ?>,
											});				
										} else if(currentValue['start_time'] == vm.break_timings.start_time  && currentValue['end_time'] < 
										vm.break_timings.end_time) {
											currentValue['end_time'] = vm.break_timings.end_time;																
											is_edit = 1;
											vm.close_add_break_model()											
										} 
									}	
								});	
								if( is_edit == 0 ) {									
									vm.selected_break_timings[vm.break_selected_day].push({ start_time: vm.break_timings.start_time, end_time: vm.break_timings.end_time });
									vm.close_add_break_model()
								}
							} else {
								if(vm.workhours_timings[vm.break_selected_day].start_time > vm.break_timings.start_time || vm.workhours_timings[vm.break_selected_day].end_time < vm.break_timings.end_time) {
									vm.$notify({
										title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
										message: '<?php esc_html_e( 'Please input valid time for break', 'bookingpress-appointment-booking' ); ?>',
										type: 'error',
										customClass: 'error_notification',
										duration:<?php echo intval($bookingpress_notification_duration); ?>,
									});				
								}else{
									vm.selected_break_timings[vm.break_selected_day].push({ start_time: vm.break_timings.start_time, end_time: vm.break_timings.end_time });																					
									vm.close_add_break_model();
								}
							}
						}
					})
				}						
			},
			delete_breakhour(start_time, end_time, selected_day){
				const vm = this
				vm.selected_break_timings[selected_day].forEach(function(currentValue, index, arr){
					if(currentValue.start_time == start_time && currentValue.end_time == end_time)
					{
						vm.selected_break_timings[selected_day].splice(index, 1);
					}
				});
			},
			close_add_break_model() {
				const vm = this
				vm.$refs['break_timings'].resetFields()
				vm.resetForm()				
				vm.open_add_break_modal = false;
			},
			edit_workhour_data(break_start_time, break_end_time, day_name){
				const vm = this
				vm.break_timings.start_time = break_start_time
				vm.break_timings.end_time = break_end_time
				vm.break_timings.old_start_time = break_start_time
				vm.break_timings.old_end_time = break_end_time
				 vm.open_add_break_modal = true			
				vm.break_selected_day = day_name
			},
			bookingpress_send_test_email(){
				const vm = this
				vm.$refs['notification_smtp_test_mail_form'].validate((valid) => {						
					if(valid) {
						vm.is_disabled = true
						vm.is_display_send_test_mail_loader = '1'
						vm.is_disable_send_test_email_btn = true
						var postdata = []
						postdata.action = 'bookingpress_send_test_email'
						postdata.notification_formdata = vm.notification_setting_form
						postdata.notification_test_mail_formdata = vm.notification_smtp_test_mail_form
						postdata._wpnonce = '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>';
						axios.post( appoint_ajax_obj.ajax_url, Qs.stringify(postdata))
						.then(function(response){
							vm.is_disabled = false
							vm.is_display_send_test_mail_loader = '0'	
							vm.is_disable_send_test_email_btn = false
							if(response.data.is_mail_sent == 1){
								vm.succesfully_send_test_email = 1
								vm.error_send_test_email = 0;
								vm.smtp_mail_error_text = '';
								vm.error_text_of_test_email = '';
							}else{
								vm.succesfully_send_test_email = 0								
								vm.error_send_test_email = 1
								vm.error_text_of_test_email = response.data.error_msg
								vm.smtp_mail_error_text = response.data.error_log_msg
							}
						}).catch(function(error){
							console.log(error);
							vm2.$notify({
								title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
								message: '<?php esc_html_e( 'Something Went wrong...', 'bookingpress-appointment-booking' ); ?>',
								type: 'error',
								customClass: 'error_notification',
								duration:<?php echo intval($bookingpress_notification_duration); ?>,
							});
						});
					}
				})	
			},
			bookingpress_trim_value(input_value){
				input_value = input_value.trim()
				this.days_off_form['daysoff_title'] = input_value
			},
			bookingpess_view_log(log_type,request_from='') {								
				const vm = this
				var postdata = []
				vm.is_display_loader_view = '1'
				if( request_from != 'pagination') {						
					vm.items = '';
				}
				vm.open_view_model_gateway = log_type;
				postdata.action = 'bookingpress_view_debug_payment_log';
				postdata.bookingpress_debug_log_selector = log_type;
				postdata.perpage=this.perPage,
				postdata.currentpage=this.currentPage,
				postdata._wpnonce = '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>';
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify(postdata))
				.then(function(response){
					vm.is_display_loader_view = '0',
					vm.items = response.data.items;
					vm.totalItems = response.data.total;
					vm.open_display_log_modal  = true										
				}).catch(function(error){
					console.log(error);
					vm2.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something Went wrong...', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
				});
			},
			bookingpess_clear_bebug_log(log_type){
				const vm = this
				vm.is_display_loader = '1'
				var postdata = []
				postdata.action = 'bookingpress_clear_debug_payment_log';
				postdata.bookingpress_debug_log_selector = log_type;
				postdata._wpnonce = '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>';
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify(postdata))
				.then(function(response) {					
					vm.is_display_loader = '0'
					vm.$notify({
						title: response.data.title,
						message: response.data.msg,
						type: response.data.variant,
						customClass: response.data.variant+'_notification',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});					
				}).catch(function(error){
					vm.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something Went wrong...', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
				});
			},
			bookingpress_download_log(log_type,selected_download_duration,download_log_daterange) {
				const vm = this
				vm.is_display_download_save_loader = '1';
				vm.is_disabled= true;
				var postdata = []
				postdata.action = 'bookingpress_download_payment_log';
				postdata.bookingpress_debug_log_selector = log_type;
				postdata.bookingpress_selected_download_duration = selected_download_duration;
				if(selected_download_duration == 'custom') {					
					postdata.bookingpress_selected_download_custom_duration = download_log_daterange;
				}
				postdata._wpnonce = '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>';
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify(postdata))
				.then(function(response) {								
					   window.location.href = response.data.url;                         		 
					vm.is_display_download_save_loader = '0';                 		 
					vm.is_disabled= false;
				}).catch(function(error){
					console.log(error);
					vm.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something Went wrong...', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
				});

			},	
			get_formatted_date(iso_date){
				var __date = new Date(iso_date);
				var __year = __date.getFullYear();
				var __month = __date.getMonth()+1;
				var __day = __date.getDate();
				if (__day < 10) {
					__day = '0' + __day;
				}
				if (__month < 10) {
					__month = '0' + __month;
				}
				var formatted_date = __year+'-'+__month+'-'+__day;
				return formatted_date;
			},		
			search_range_change(selected_value) {				
				if(selected_value != null) {
					this.download_log_daterange[0] = this.get_formatted_date(this.download_log_daterange[0])				
					this.download_log_daterange[1] = this.get_formatted_date(this.download_log_daterange[1])
				}
			},			
			bookingpress_check_workhour_value(workhour_time,work_hour_day) {	
				if(workhour_time == 'Off') {
					const vm = this
					vm.workhours_timings[work_hour_day].start_time = 'Off';
				}
			},
			bookingpress_set_workhour_value(work_hour_day) {
				const vm = this				
				if(vm.workhours_timings[work_hour_day].end_time == 'Off') {					
					vm.work_hours_days_arr.forEach(function(currentValue, index, arr){
						if(currentValue.day_name == work_hour_day) {
							var start_time = vm.workhours_timings[work_hour_day].start_time;								
							currentValue.worktimes.forEach(function(currentValue2, index2, arr2){													
								if(currentValue2.start_time == start_time) {
									vm.workhours_timings[work_hour_day].end_time = arr2[index2]['end_time'] ;
								}
							});
						}
					});				
				}	
			},			
			open_smtp_error_modal() {				
				const vm= this;
				vm.smtp_error_modal = true;
			},
			close_smtp_error_modal(){
				const vm= this;
				vm.smtp_error_modal = false;	
			},
			bookingpress_phone_country_change_func(bookingpress_country_obj){
				const vm = this
				var bookingpress_selected_country = bookingpress_country_obj.iso2
				vm.company_setting_form.company_phone_country = bookingpress_selected_country
			},
			<?php
			do_action( 'bookingpress_add_setting_dynamic_vue_methods' );
		}


		function bookingpress_setting_dynamic_data_fields_func() {
			global $bookingpress_dynamic_setting_data_fields,$BookingPress;

			$bookingpress_default_perpage_option                               = $BookingPress->bookingpress_get_settings( 'per_page_item', 'general_setting' );
			$bookingpress_dynamic_setting_data_fields['perPage']               = ! empty( $bookingpress_default_perpage_option ) ? $bookingpress_default_perpage_option : '10';
			$bookingpress_dynamic_setting_data_fields['pagination_length_val'] = ! empty( $bookingpress_default_perpage_option ) ? $bookingpress_default_perpage_option : '10';


			$selected_tab_value = '0';
			$selected_tab_name = !empty($_REQUEST['setting_page']) ? $_REQUEST['setting_page'] : 'general';
			if($selected_tab_name == "general"){
				$selected_tab_value = '0';
			}else if($selected_tab_name == "company"){
				$selected_tab_value = '1';
			}else if($selected_tab_name == "notifications"){
				$selected_tab_value = '2';
			}else if($selected_tab_name == "working_hours"){
				$selected_tab_value = '3';
			}else if($selected_tab_name == "daysoff"){
				$selected_tab_value = '4';
			}else if($selected_tab_name == "payments"){
				$selected_tab_value = '5';
			}else if($selected_tab_name == "messages"){
				$selected_tab_value = '6';
			}else if($selected_tab_name == "debug_logs"){
				$selected_tab_value = '7';
			}

			$bookingpress_dynamic_setting_data_fields['selected_tab'] = $selected_tab_value;

			$bookingpress_company_phone_country = $BookingPress->bookingpress_get_settings( 'company_phone_country', 'company_setting' );
			$bookingpress_dynamic_setting_data_fields['bookingpress_tel_input_props'] = array(
				'defaultCountry' => $bookingpress_company_phone_country,				
			);

			$bookingpress_dynamic_setting_data_fields = apply_filters( 'bookingpress_add_setting_dynamic_data_fields', $bookingpress_dynamic_setting_data_fields );

			echo json_encode( $bookingpress_dynamic_setting_data_fields );
		}

		function bookingpress_setting_dynamic_helper_vars_func() {
			global $bookingpress_global_options;
			$bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_locale_lang = $bookingpress_options['locale'];

			?>
				var lang = ELEMENT.lang.<?php echo esc_html( $bookingpress_locale_lang ); ?>;
				ELEMENT.locale(lang)

			<?php
			do_action( 'bookingpress_dynamic_add_setting_helpers_vars' );
		}

		function bookingpress_dynamic_load_setting_content_func() {
			$bookingpress_load_file_name = BOOKINGPRESS_VIEWS_DIR . '/settings/manage_settings.php';
			$bookingpress_load_file_name = apply_filters( 'bookingpress_modify_settings_view_file_path', $bookingpress_load_file_name );

			require $bookingpress_load_file_name;
		}


		public function bookingpress_get_settings_data_by_setting_type( $setting_type ) {
			global $wpdb, $tbl_bookingpress_settings;

			if ( ! empty( $setting_type ) ) {
				$bookingpress_fetch_settings_details = $wpdb->get_results( "SELECT * FROM `{$tbl_bookingpress_settings}` WHERE setting_type = '" . $setting_type . "'", ARRAY_A );
				foreach ( $bookingpress_fetch_settings_details as $key => $value ) {
					if ( $setting_type == 'general_setting' ) {
						if ( $bookingpress_fetch_settings_details[ $key ]['setting_name'] == 'use_already_loaded_vue' && $bookingpress_fetch_settings_details[ $key ]['setting_value'] == '' ) {
							$bookingpress_fetch_settings_details[ $key ]['setting_value'] = 'false';
						} elseif ( $bookingpress_fetch_settings_details[ $key ]['setting_name'] == 'phone_number_mandatory' && $bookingpress_fetch_settings_details[ $key ]['setting_value'] == '' ) {
							$bookingpress_fetch_settings_details[ $key ]['setting_value'] = 'false';
						}
					}
					if ( $setting_type == 'payment_setting' ) {
						if ( $bookingpress_fetch_settings_details[ $key ]['setting_name'] == 'paypal_payment' && $bookingpress_fetch_settings_details[ $key ]['setting_value'] == '' ) {
							$bookingpress_fetch_settings_details[ $key ]['setting_value'] = 'false';
						} elseif ( $bookingpress_fetch_settings_details[ $key ]['setting_name'] == 'on_site_payment' && $bookingpress_fetch_settings_details[ $key ]['setting_value'] == 1 ) {
							$bookingpress_fetch_settings_details[ $key ]['setting_value'] = 'true';
						}
					}
				}
				if ( ! empty( $bookingpress_fetch_settings_details ) ) {
					return $bookingpress_fetch_settings_details;
				}
			}

			return array();
		}

		public function bookingpress_get_settings_details() {
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
			$response['variant'] = 'error';
			$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']     = esc_html__( 'Something Went wrong while fetch settings...', 'bookingpress-appointment-booking' );
			$response['data']    = array();

			if ( ! empty( $_POST['setting_type'] ) ) {
				$setting_type               = sanitize_text_field( $_POST['setting_type'] );
				$bookingpress_settings_data = $this->bookingpress_get_settings_data_by_setting_type( $setting_type );

				if ( ! empty( $bookingpress_settings_data ) ) {
					$bookingpress_setting_return_data = array();

					foreach ( $bookingpress_settings_data as $bookingpress_setting_key => $bookingpress_setting_val ) {
						$bookingpress_tmp_setting_val = $bookingpress_setting_val['setting_value'];
						if ( $bookingpress_tmp_setting_val == 'true' ) {
							$bookingpress_tmp_setting_val = true;
						} elseif ( $bookingpress_tmp_setting_val == 'false' ) {
							$bookingpress_tmp_setting_val = false;
						}
						if ( gettype( $bookingpress_tmp_setting_val ) == 'boolean' ) {
							$bookingpress_setting_return_data[ $bookingpress_setting_val['setting_name'] ] = $bookingpress_tmp_setting_val;
						} else {
							if ( $bookingpress_setting_val['setting_name'] == 'smtp_password' ) {
								$bookingpress_setting_return_data[ $bookingpress_setting_val['setting_name'] ] = $bookingpress_tmp_setting_val;
							} else {
								$bookingpress_setting_return_data[ $bookingpress_setting_val['setting_name'] ] = esc_html( $bookingpress_tmp_setting_val );
							}
						}
					}

					$response['variant'] = 'success';
					$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
					$response['msg']     = esc_html__( 'Settings has been fetched successfully', 'bookingpress-appointment-booking' );
					$response['data']    = $bookingpress_setting_return_data;
				}
			}

			echo json_encode( $response );
			exit();
		}

		public function bookingpress_save_settings_details() {
			global $BookingPress;
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
			$response['variant'] = 'error';
			$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']     = esc_html__( 'Something Went wrong while updating settings...', 'bookingpress-appointment-booking' );

			if ( ! empty( $_POST ) && ! empty( $_POST['action'] ) && ( sanitize_text_field( $_POST['action'] ) == 'bookingpress_save_settings_data' ) && ! empty( $_POST['settingType'] ) ) {
				$bookingpress_save_settings_data = (array) $_POST;
				$bookingpress_setting_type       = sanitize_text_field( $_POST['settingType'] );
				$bookingpress_setting_action     = sanitize_text_field( $_POST['action'] );
				unset( $bookingpress_save_settings_data['settingType'] );
				unset( $bookingpress_save_settings_data['action'] );
				unset( $bookingpress_save_settings_data['_wpnonce'] );

				if ( $bookingpress_setting_type == 'notification_setting' && isset( $bookingpress_save_settings_data['smtp_test_receiver_email'] ) && isset( $bookingpress_save_settings_data['smtp_test_msg'] ) ) {
					unset( $bookingpress_save_settings_data['smtp_test_receiver_email'] );
					unset( $bookingpress_save_settings_data['smtp_test_msg'] );
				}

				$bookingpress_response_arr = array();
				foreach ( $bookingpress_save_settings_data as $bookingpress_setting_key => $bookingpress_setting_val ) {
					if ( $bookingpress_setting_key == 'company_avatar_url' && ! empty( $bookingpress_setting_val ) ) {
						$bookingpress_avatar_url        = $bookingpress_setting_val;
						$bookingpress_upload_image_name = sanitize_file_name( $_POST['company_avatar_img'] );

						$upload_dir                 = BOOKINGPRESS_UPLOAD_DIR . '/';
						$bookingpress_new_file_name = current_time( 'timestamp' ) . '_' . $bookingpress_upload_image_name;
						$upload_path                = $upload_dir . $bookingpress_new_file_name;
						$bookingpress_upload_res    = $BookingPress->bookingpress_file_upload_function( $bookingpress_avatar_url, $upload_path );

						$bookingpress_setting_val = BOOKINGPRESS_UPLOAD_URL . '/' . $bookingpress_new_file_name;

						$bookingpress_file_name_arr = explode( '/', $bookingpress_avatar_url );
						$bookingpress_file_name     = $bookingpress_file_name_arr[ count( $bookingpress_file_name_arr ) - 1 ];
						unlink( BOOKINGPRESS_TMP_IMAGES_DIR . '/' . $bookingpress_file_name );
					}

					$bookingpress_res = $BookingPress->bookingpress_update_settings( $bookingpress_setting_key, $bookingpress_setting_type, $bookingpress_setting_val );

					array_push( $bookingpress_response_arr, $bookingpress_res );
				}

				do_action('boookingpress_after_save_settings_data',$_POST);

				if ( ! in_array( '0', $bookingpress_response_arr ) ) {
					$response['variant'] = 'success';
					$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
					$response['msg']     = esc_html__( 'Settings has been updated successfully.', 'bookingpress-appointment-booking' );
				}
			}

			echo json_encode( $response );
			exit();
		}

		public function bookingpress_save_default_work_hours() {
			global $wpdb, $tbl_bookingpress_default_workhours;
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
			$response['variant'] = 'error';
			$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']     = esc_html__( 'Something Went wrong while updating settings...', 'bookingpress-appointment-booking' );

			$delete_breakhours_condition = array(
				'bookingpress_is_break' => 1,
			);

			$wpdb->delete( $tbl_bookingpress_default_workhours, $delete_breakhours_condition );

			if ( ! empty( $_REQUEST['break_data'] ) ) {
				$break_data = $_REQUEST['break_data'];
				foreach ( $break_data as $break_key => $break_val ) {
					$dayname = strtolower( $break_key );
					foreach ( $break_val as $days_break_keys => $days_break_vals ) {
						$break_start_time = date( 'H:i:s', strtotime( sanitize_text_field( $days_break_vals['start_time'] ) ) );
						$break_end_time   = date( 'H:i:s', strtotime( sanitize_text_field( $days_break_vals['end_time'] ) ) );

						$bookingpress_insert_breakhours_data = array(
							'bookingpress_workday_key' => $dayname,
							'bookingpress_start_time' => $break_start_time,
							'bookingpress_end_time'   => $break_end_time,
							'bookingpress_is_break'   => 1,
							'bookingpress_created_at' => current_time( 'mysql' ),
						);

						$wpdb->insert( $tbl_bookingpress_default_workhours, $bookingpress_insert_breakhours_data );
					}
				}
			}
			if ( ! empty( $_REQUEST['workhours_timings'] ) ) {
				$workhour_timings = $_REQUEST['workhours_timings'];
				foreach ( $workhour_timings as $timing_key => $timing_val ) {
					$dayname           = strtolower( $timing_key );
					$start_time        = ( sanitize_text_field( $timing_val['start_time'] ) != 'Off' ) ? date( 'H:i:s', strtotime( sanitize_text_field( $timing_val['start_time'] ) ) ) : NULL;
					$end_time          = ( sanitize_text_field( $timing_val['start_time'] ) != 'Off' ) ? date( 'H:i:s', strtotime( sanitize_text_field( $timing_val['end_time'] ) ) ) : NULL;
					$workhours_counter = $wpdb->get_var( "SELECT COUNT(bookingpress_workhours_id) as total FROM {$tbl_bookingpress_default_workhours} WHERE bookingpress_workday_key = '{$dayname}' AND bookingpress_is_break = 0" );

					if ( $workhours_counter > 0 ) {
						$bookingpress_update_data = array(
							'bookingpress_start_time' => $start_time,
							'bookingpress_end_time'   => $end_time,
						);

						$bookingpress_where_condition = array(
							'bookingpress_workday_key' => $dayname,
							'bookingpress_is_break' => 0,
						);

						$wpdb->update( $tbl_bookingpress_default_workhours, $bookingpress_update_data, $bookingpress_where_condition );
					} else {
						$bookingpress_insertdata = array(
							'bookingpress_workday_key' => $dayname,
							'bookingpress_start_time' => $start_time,
							'bookingpress_end_time'   => $end_time,
							'bookingpress_is_break'   => 0,
							'bookingpress_created_at' => current_time( 'mysql' ),
						);

						$wpdb->insert( $tbl_bookingpress_default_workhours, $bookingpress_insertdata );
					}
				}

				$response['variant'] = 'success';
				$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Settings has been updated successfully.', 'bookingpress-appointment-booking' );
			}

			echo json_encode( $response );
			exit();
		}

		public function bookingpress_get_default_work_hours() {
			global $wpdb, $tbl_bookingpress_default_workhours, $tbl_bookingpress_services, $bookingpress_global_options, $BookingPress;
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
			$bookingpress_workhours_data = $bookingpress_selected_work_times = array();

			$response['data']    = $bookingpress_workhours_data;
			$response['msg']     = esc_html__( 'Something went wrong.', 'bookingpress-appointment-booking' );
			$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['variant'] = 'error';

			$bookingpress_days_arr = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );

			$default_start_time = '00:00:00';
			$default_end_time   = '23:00:00';
			$step_duration_val  = 30;

			foreach ( $bookingpress_days_arr as $days_key => $days_val ) {

				$bookingpress_breaks_arr = $bookingpress_times_arr = array();
				$curr_time               = $tmp_start_time = date( 'H:i:s', strtotime( $default_start_time ) );
				$tmp_end_time            = date( 'H:i:s', strtotime( $default_end_time ) );

				do {
					$tmp_time_obj = new DateTime( $curr_time );
					$tmp_time_obj->add( new DateInterval( 'PT' . $step_duration_val . 'M' ) );
					$end_time = $tmp_time_obj->format( 'H:i:s' );

					$bookingpress_check_break_time_exist = $wpdb->get_var( 'SELECT COUNT(bookingpress_workhours_id) FROM ' . $tbl_bookingpress_default_workhours . " WHERE bookingpress_workday_key = '" . $days_val . "' AND (bookingpress_start_time <= '{$curr_time}' AND bookingpress_end_time >= '{$end_time}') AND bookingpress_is_break = 1" );

					if ( $bookingpress_check_break_time_exist ) {
						$bookingpress_breaks_arr[] = array(
							'start_time' => $curr_time,
							'end_time'   => $end_time,
						);
					} else {
						$bookingpress_times_arr[] = array(
							'start_time' => $curr_time,
							'end_time'   => $end_time,
						);
					}

					$tmp_time_obj = new DateTime( $curr_time );
					$tmp_time_obj->add( new DateInterval( 'PT' . $step_duration_val . 'M' ) );
					$curr_time = $tmp_time_obj->format( 'H:i:s' );
				} while ( $curr_time <= $default_end_time );

				$bookingpress_times_arr[] = array(
					'start_time' => esc_html( 'Off', 'bookingpress-appointment-booking' ),
				);

				$bookingpress_workhours_data[] = array(
					'day_name'    => ucfirst( $days_val ),
					'break_times' => $bookingpress_breaks_arr,
					'worktimes'   => $bookingpress_times_arr,
				);

				$selected_timing_data = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_default_workhours} WHERE bookingpress_workday_key = '" . $days_val . "' AND bookingpress_is_break = 0", ARRAY_A );
				$selected_start_time  = $selected_timing_data['bookingpress_start_time'];
				$selected_end_time    = $selected_timing_data['bookingpress_end_time'];
				if ( $selected_start_time == NULL ) {
					$selected_start_time = 'Off';
				}

				if ( $selected_end_time == NULL ) {
					$selected_end_time = 'Off';
				}

				$bookingpress_selected_work_times[ ucfirst( $days_val ) ] = array(
					'start_time' => $selected_start_time,
					'end_time'   => $selected_end_time,
				);
			}

			$default_break_timings = array();
			$curr_time             = $tmp_start_time = date( 'H:i:s', strtotime( $default_start_time ) );
			$tmp_end_time          = date( 'H:i:s', strtotime( $default_end_time ) );

			do {
				$tmp_time_obj = new DateTime( $curr_time );
				$tmp_time_obj->add( new DateInterval( 'PT' . $step_duration_val . 'M' ) );
				$end_time = $tmp_time_obj->format( 'H:i:s' );

				$bookingpress_check_break_time_exist = $wpdb->get_var( 'SELECT COUNT(bookingpress_workhours_id) FROM ' . $tbl_bookingpress_default_workhours . " WHERE bookingpress_workday_key = '" . $days_val . "' AND (bookingpress_start_time <= '{$curr_time}' AND bookingpress_end_time >= '{$end_time}') AND bookingpress_is_break = 1" );

				if ( ! $bookingpress_check_break_time_exist ) {
					$default_break_timings[] = array(
						'start_time' => $curr_time,
						'end_time'   => $end_time,
					);
				}

				$tmp_time_obj = new DateTime( $curr_time );
				$tmp_time_obj->add( new DateInterval( 'PT' . $step_duration_val . 'M' ) );
				$curr_time = $tmp_time_obj->format( 'H:i:s' );
			} while ( $curr_time <= $default_end_time );			

			$response['data']                = $bookingpress_workhours_data;
			$response['selected_workhours']  = $bookingpress_selected_work_times;
			$response['default_break_times'] = $default_break_timings;
			$response['msg']                 = esc_html__( 'Workhours Data.', 'bookingpress-appointment-booking' );
			$response['title']               = esc_html__( 'Success', 'bookingpress-appointment-booking' );
			$response['variant']             = 'success';

			echo json_encode( $response );
			exit();
		}

		public function bookingpress_save_default_daysoff_details() {
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
			$response['variant'] = 'error';
			$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']     = esc_html__( 'Something Went wrong...', 'bookingpress-appointment-booking' );

			if ( ! empty( $_REQUEST['daysoff'] ) && ! empty( $_REQUEST['action'] ) && ( sanitize_text_field( $_REQUEST['action'] ) == 'bookingpress_save_default_daysoff' ) ) {
				global $wpdb, $tbl_bookingpress_default_daysoff;

				// $wpdb->delete($tbl_bookingpress_default_daysoff);
				$wpdb->query( "DELETE FROM {$tbl_bookingpress_default_daysoff}" );

				if ( ! empty( $_REQUEST['daysoff'] ) ) {
					foreach ( $_REQUEST['daysoff'] as $daysoff ) {
						$start_date    = ! empty( $daysoff['dayoff_date'][0] ) ? sanitize_text_field( $daysoff['dayoff_date'][0] ) : '';
						$start_date    = date_format( date_create( $start_date ), 'Y-m-d H:i:s' );
						$end_date      = ! empty( $daysoff['dayoff_date'][1] ) ? sanitize_text_field( $daysoff['dayoff_date'][1] ) : '';
						$end_date      = date_format( date_create( $end_date ), 'Y-m-d H:i:s' );
						$dayoff_name   = ! empty( $daysoff['dayoff_name'] ) ? sanitize_text_field( $daysoff['dayoff_name'] ) : '';
						$dayoff_repeat = ( ! empty( $daysoff['dayoff_repeat'] ) && sanitize_text_field( $daysoff['dayoff_repeat'] ) == 'true' ) ? 1 : 0;
						$args          = array(
							'bookingpress_name'       => $dayoff_name,
							'bookingpress_start_date' => $start_date,
							'bookingpress_end_date'   => $end_date,
							'bookingpress_repeat'     => $dayoff_repeat,
							'bookingpress_created_at' => current_time( 'mysql' ),
						);
						$wpdb->insert( $tbl_bookingpress_default_daysoff, $args );
					}
				}
				$response['variant'] = 'success';
				$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Days Off settings updated successfully.', 'bookingpress-appointment-booking' );
			}

			wp_send_json( $response );
		}

		public function bookingpress_get_default_daysoff_details() {
			global $wpdb, $tbl_bookingpress_default_daysoff;
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
			$days_off_arr = array();
			$days_off     = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_default_daysoff, ARRAY_A );
			if ( ! empty( $days_off ) ) {
				foreach ( $days_off as $day_off ) {
					$day_off_arr                  = array();
					$day_off_arr['id']            = $day_off['bookingpress_dayoff_id'];
					$day_off_arr['dayoff_name']   = $day_off['bookingpress_name'];
					$start_date                   = ! empty( $day_off['bookingpress_start_date'] ) ? date_format( date_create( $day_off['bookingpress_start_date'] ), 'F d, Y' ) : array();
					$end_date                     = ! empty( $day_off['bookingpress_end_date'] ) ? date_format( date_create( $day_off['bookingpress_end_date'] ), 'F d, Y' ) : array();
					$day_off_date                 = array( $start_date, $end_date );
					$day_off_arr['dayoff_date']   = $day_off_date;
					$day_off_arr['dayoff_repeat'] = ! empty( $day_off['bookingpress_repeat'] ) ? true : false;
					$days_off_arr[]               = $day_off_arr;
				}
			}

			echo json_encode( $days_off_arr );
			exit();
		}
	}
}
global $bookingpress_settings, $bookingpress_dynamic_setting_data_fields;
$bookingpress_settings = new bookingpress_settings();

global $bookingpress_global_options;
$bookingpress_options                    = $bookingpress_global_options->bookingpress_global_options();
$bookingpress_country_list               = $bookingpress_options['country_lists'];
$bookingpress_countries_currency_details = json_decode( $bookingpress_options['countries_json_details'] );
$timepicker_options                      = $bookingpress_options['timepicker_options'];
$bookingpress_pagination                 = $bookingpress_options['pagination'];
$bookingpress_pagination_arr             = json_decode( $bookingpress_pagination, true );
$bookingpress_pagination_selected        = $bookingpress_pagination_arr[0];

$bookingpress_dynamic_setting_data_fields = array(
	'modal_loading'                   => 'false',
	'flags_img_url'                   => BOOKINGPRESS_IMAGES_URL,
	'modals'                          => array(
		'general_setting_modal'      => false,
		'company_setting_modal'      => false,
		'notification_setting_modal' => false,
		'workhours_setting_modal'    => false,
		'appointment_setting_modal'  => false,
		'label_setting_modal'        => false,
		'payment_setting_modal'      => false,
	),
	'default_appointment_staus'       => array(
		array(
			'text'  => 'Approved',
			'value' => 'Approved',
		),
		array(
			'text'  => 'Pending',
			'value' => 'Pending',
		),
	),
	'default_appointment_staus'       => array(
		array(
			'text'  => 'Approved',
			'value' => 'Approved',
		),
		array(
			'text'  => 'Pending',
			'value' => 'Pending',
		),
	),
	'default_pagination'              => array(
		array(
			'text'  => '10',
			'value' => '10',
		),
		array(
			'text'  => '20',
			'value' => '20',
		),
		array(
			'text'  => '50',
			'value' => '50',
		),
		array(
			'text'  => '100',
			'value' => '100',
		),
		array(
			'text'  => '200',
			'value' => '200',
		),
		array(
			'text'  => '300',
			'value' => '300',
		),
		array(
			'text'  => '400',
			'value' => '400',
		),
		array(
			'text'  => '500',
			'value' => '500',
		),
	),
	'phone_countries_details'         => json_decode( $bookingpress_country_list ),
	'default_smtp_secure_options'     => array(
		array(
			'text'  => __( 'SSL', 'bookingpress-appointment-booking' ),
			'value' => 'SSL',
		),
		array(
			'text'  => __( 'TLS', 'bookingpress-appointment-booking' ),
			'value' => 'TLS',
		),
		array(
			'text'  => __( 'Disabled', 'bookingpress-appointment-booking' ),
			'value' => 'Disabled',
		),
	),
	'default_timeslot_options'        => array(
		array(
			'text'  => __( '5 min','bookingpress-appointment-booking' ),
			'value' => '5',
		),
		array(
			'text'  => __( '10 min','bookingpress-appointment-booking' ),
			'value' => '10',
		),
		array(
			'text'  => __( '12 min','bookingpress-appointment-booking' ),
			'value' => '12',
		),
		array(
			'text'  => __( '15 min','bookingpress-appointment-booking' ),
			'value' => '15',
		),
		array(
			'text'  => __( '20 min','bookingpress-appointment-booking' ),
			'value' => '20',
		),
		array(
			'text'  => __( '30 min','bookingpress-appointment-booking' ),
			'value' => '30',
		),
		array(
			'text'  => __( '45 min','bookingpress-appointment-booking' ),
			'value' => '45',
		),
		array(
			'text'  => __( '1 h','bookingpress-appointment-booking' ),
			'value' => '60',
		),
		array(
			'text'  => __( '1 h 30 min','bookingpress-appointment-booking' ),
			'value' => '90',
		),
		array(
			'text'  => __( '2 h','bookingpress-appointment-booking' ),
			'value' => '120',
		),
		array(
			'text'  => __( '3 h','bookingpress-appointment-booking' ),
			'value' => '180',
		),
		array(
			'text'  => __( '6 h','bookingpress-appointment-booking' ),
			'value' => '360',
		),
	),
	'price_symbol_position_val'       => array(
		array(
			'text'        => __( 'Before the value', 'bookingpress-appointment-booking' ),
			'value'       => 'before',
			'position_ex' => '$100',
		),
		array(
			'text'        => __( 'Before the value', 'bookingpress-appointment-booking' ) . ', ' . __( 'separated with space', 'bookingpress-appointment-booking' ),
			'value'       => 'before_with_space',
			'position_ex' => '$ 100',
		),
		array(
			'text'        => __( 'After the value', 'bookingpress-appointment-booking' ),
			'value'       => 'after',
			'position_ex' => '100$',
		),
		array(
			'text'        => __( 'After the value', 'bookingpress-appointment-booking' ) . ', ' . __( 'separated with space', 'bookingpress-appointment-booking' ),
			'value'       => 'after_with_space',
			'position_ex' => '100 $',
		),
	),
	'price_separator_vals'            => array(
		array(
			'text'         => 'Comma-Dot ',
			'value'        => 'comma-dot',
			'separator_ex' => '15,000.00',
		),
		array(
			'text'         => 'Dot-Comma ',
			'value'        => 'dot-comma',
			'separator_ex' => '15.000,00',
		),
		array(
			'text'         => 'Space-Dot ',
			'value'        => 'space-dot',
			'separator_ex' => '15 000.00',
		),
		array(
			'text'         => 'Space-Comma ',
			'value'        => 'space-comma',
			'separator_ex' => '15 000,00',
		),
	),
	'default_payment_method'          => array(
		array(
			'text'  => __( 'On-site', 'bookingpress-appointment-booking' ),
			'value' => 'on_site',
		),
		array(
			'text'  => __( 'PayPal', 'bookingpress-appointment-booking' ),
			'value' => 'paypal',
		),
	),
	'currency_countries'              => $bookingpress_countries_currency_details,
	'general_setting_form'            => array(
		'default_time_slot_step'              => '30',
		'appointment_status'                  => 'Approved',
		'default_phone_country_code'          => 'us',
		'per_page_item'                       => '20',
		'redirect_url_after_booking_approved' => '',
		'redirect_url_after_booking_pending'  => '',
		'redirect_url_after_booking_canceled' => '',
		'phone_number_mandatory'              => false,
		'use_already_loaded_vue'              => false,
		'load_js_css_all_pages'               => false,
	),
	'company_setting_form'            => array(
		'company_avatar_img'    => '',
		'company_avatar_url'    => '',
		'company_avatar_list'   => array(),
		'company_name'          => '',
		'company_address'       => '',
		'company_website'       => '',
		'company_phone_country' => 'us',
		'company_phone_number'  => '',
	),
	'notification_setting_form'       => array(
		'selected_mail_service'    => 'php_mail',
		'sender_name'              => get_option( 'blogname' ),
		'sender_email'             => get_option( 'admin_email' ),
		'admin_email'              => get_option( 'admin_email' ),
		'success_url'              => '',
		'cancel_url'               => '',
		'smtp_host'                => '',
		'smtp_port'                => '',
		'smtp_secure'              => 'Disabled',
		'smtp_username'            => '',
		'smtp_password'            => '',
	),
	'notification_smtp_test_mail_form' => array(	
		'smtp_test_receiver_email' => '',
		'smtp_test_msg'            => '',
	),
	'payment_setting_form'            => array(
		'payment_default_currency' => 'US Dollar',
		'price_symbol_position'    => 'before',
		'price_separator'          => 'comma-dot',
		'price_number_of_decimals' => 2,
		'on_site_payment'          => true,
		'paypal_payment'           => false,
		'paypal_payment_mode'      => 'sandbox',
		'paypal_merchant_email'    => '',
		'paypal_api_username'      => '',
		'paypal_api_password'      => '',
		'paypal_api_signature'     => '',
	),
	'message_setting_form'            => array(
		'confirmation_message_for_the_cancel_appointment' => 'Are you sure you want to cancel this appointment.',
		'appointment_booked_successfully'                 => 'Appointment has been booked successfully.',
		'appointment_cancelled_successfully'              => 'Appointment has been cancelled successfully.',
		'duplidate_appointment_time_slot_found'           => 'I am sorry! Another appointment is already booked with this time slot. Please select another time slot which suits you the best.',
		'unsupported_currecy_selected_for_the_payment'    => 'I am sorry! The selected currency is not supported by PayPal payment gateway. Please proceed with another available payment method.',
		'duplicate_email_address_found'                   => 'I am sorry! This email address is already exists. Please enter another email address.',
		'no_payment_method_is_selected_for_the_booking'   => 'Please select a payment method to proceed with the booking.',
		'no_appointment_time_selected_for_the_booking'    => 'Please select a time slot to proceed with the booking.',
		'no_appointment_date_selected_for_the_booking'    => 'Please select appointment date to proceed with the booking.',
		'no_service_selected_for_the_booking'             => 'Please select any service to book the appointment',
	),
	'debug_log_setting_form'          => array(
		'on_site_payment' => false,
		'paypal_payment'  => false,
	),
	'customer_setting_form'        => array(
		'allow_wp_user_create' => false,
	),
	'succesfully_send_test_email'     => 0,
	'error_send_test_email'           => 0,
	'error_text_of_test_email'        => '',
	'is_disable_send_test_email_btn'  => false,
	'is_display_send_test_mail_loader' => '0',
	'imageUrl'                        => '',
	'monday'                          => 'monday',
	'add_work_hours_display'          => '',
	'work_type_modal'                 => 'monday_work_hours',
	'work_hours_days_arr'             => array(),
	'timepicker_options'              => json_decode( $timepicker_options ),
	'work_start_time'                 => '',
	'work_end_time'                   => '',
	'final_work_hours_data'           => array(),
	'employee_dayoff'                 => array(),
	'days_off_year_filter'            => date( 'Y' ),
	'employee_dayoff_form'            => array(
		'dayoff_name'   => '',
		'dayoff_date'   => '',
		'dayoff_repeat' => false,
	),
	'add_employee_dayoff'             => 0,
	'rules_dayoff'                    => array(
		'dayoff_name' => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter name', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'dayoff_date' => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please select date', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
	),
	'rules_general'                   => array(
		'redirect_url_after_booking_approved' => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter approved booking redirection URL', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'redirect_url_after_booking_pending'  => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter pending booking redirection URL', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'redirect_url_after_booking_canceled' => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter canceled booking redirection URL', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
	),
	'rules_company'                   => array(
		'company_name'         => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter company name', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'company_address'      => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter company address', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'company_website'      => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter company website', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'company_phone_number' => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter phone number', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
	),
	'rules_notification'              => array(
		'sender_name'   => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter sender name', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'sender_email'  => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter sender email', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'sender_url'    => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter sender url', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'admin_email'   => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter admin email', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'success_url'   => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter Successfull Redirection URL', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'cancel_url'    => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter Cancel Redirection URL', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'smtp_port'     => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter smtp port', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'smtp_host'     => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter smtp host', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'smtp_secure'   => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter smtp secure', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'smtp_username' => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter smtp username', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'smtp_password' => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter phone password', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),

	),
	'rules_smtp_test_mail' 	=> array(
		'smtp_test_receiver_email' => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter Email', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'smtp_test_msg' => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter Message', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
	),
	'rules_payment'                   => array(
		'paypal_merchant_email' => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter merchant email', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'paypal_api_username'   => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter api username', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'paypal_api_password'   => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter api password', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'paypal_api_signature'  => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter api signature', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
	),
	'days_off_rules'                  => array(
		'daysoff_title' => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter holiday reason', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
	),
	'rules_message'                   => array(
		'confirmation_message_for_the_cancel_appointment' => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter confirmation message for the cancel appointment', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'appointment_booked_successfully'                 => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter appointment booked successfully message', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'appointment_cancelled_successfully'              => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter appointment cancelled successfully message', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'duplidate_appointment_time_slot_found'           => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter duplidate appointment time slot found message', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'unsupported_currecy_selected_for_the_payment'    => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter unsupported currency selected for the payment message', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'duplicate_email_address_found'                   => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter duplicate email address found message', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'no_payment_method_is_selected_for_the_booking'   => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter no payment method is selected for the booking message', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'no_appointment_time_selected_for_the_booking'    => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter no appointment time selected for the booking message', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'no_appointment_date_selected_for_the_booking'    => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter no appointment date selected for the booking message', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'no_service_selected_for_the_booking'             => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter no service selected for the booking message', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
	),
	'employee_dayoff_arr'             => array(),
	'edit_employee_dayoff'            => '',
	'edit_dayoff_name'                => '',
	'edit_dayoff_date'                => '',
	'edit_dayoff_repeat'              => false,
	'needHelpDrawer'                  => false,
	'needHelpDrawerDirection'         => 'rtl',
	'comShowFileList'                 => false,
	'workhours_timings'               => array(
		'Monday'    => array(
			'start_time' => '09:00:00',
			'end_time'   => '17:00:00',
		),
		'Tuesday'   => array(
			'start_time' => '09:00:00',
			'end_time'   => '17:00:00',
		),
		'Wednesday' => array(
			'start_time' => '09:00:00',
			'end_time'   => '17:00:00',
		),
		'Thursday'  => array(
			'start_time' => '09:00:00',
			'end_time'   => '17:00:00',
		),
		'Friday'    => array(
			'start_time' => '09:00:00',
			'end_time'   => '17:00:00',
		),
		'Saturday'  => array(
			'start_time' => 'Off',
			'end_time'   => 'Off',
		),
		'Sunday'    => array(
			'start_time' => 'Off',
			'end_time'   => 'Off',
		),
	),
	'isloading'                       => false,
	'open_add_break_modal'            => false,
	'break_modal_pos'                 => '254px',
	'break_modal_pos_right'           => '',
	'default_break_timings'           => array(),
	'break_selected_day'              => 'Monday',
	'break_timings'                   => array(
		'start_time'     => '',
		'end_time'       => '',
		'old_start_time' => '',
		'old_end_time'   => '',
	),
	'rules_add_break'                 => array(
		'start_time' => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter start time', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'end_time'   => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter end time', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
	),
	'selected_break_timings'  => array(
		'Monday'    => array(),
		'Tuesday'   => array(),
		'Wednesday' => array(),
		'Thursday'  => array(),
		'Friday'    => array(),
		'Saturday'  => array(),
		'Sunday'    => array(),
	),
	'days'                            => array(),
	'open_add_daysoff_details'        => false,
	'days_off_top_pos'                => '0',
	'days_off_left_pos'               => '0',
	'days_off_form'                   => array(
		'daysoff_title'      => '',
		'is_repeat_days_off' => false,
		'selected_date'      => '',
		'is_edit'            => '',
	),
	'daysoff_default_year'            => date( 'Y' ),
	'daysoff_selected_year'           => date( 'Y' ),
	'is_display_loader'               => '0',
	'is_disabled'                     => false,
	'is_display_save_loader'          => '0',
	'is_mask_display'                 => false,
	'open_display_log_modal'          => false,
	'items'                           => array(),
	'multipleSelection'               => array(),
	'perPage'                         => $bookingpress_pagination_selected,
	'totalItems'                      => 0,
	'pagination_selected_length'      => $bookingpress_pagination_selected,
	'pagination_length'               => $bookingpress_pagination,
	'currentPage'                     => 1,
	'pagination_length_val'           => '10',
	'open_view_model_gateway'         => '',
	'is_display_loader_view'          => '0',
	'select_download_log'             => '7',
	'log_download_default_option'     => array(
		array(
			'key'   => __( 'Last 1 Day', 'bookingpress-appointment-booking' ),
			'value' => '1',
		),
		array(
			'key'   => __( 'Last 3 Days', 'bookingpress-appointment-booking' ),
			'value' => '3',
		),
		array(
			'key'   => __( 'Last 1 Week', 'bookingpress-appointment-booking' ),
			'value' => '7',
		),
		array(
			'key'   => __( 'Last 2 Weeks', 'bookingpress-appointment-booking' ),
			'value' => '14',
		),
		array(
			'key'   => __( 'Last Month', 'bookingpress-appointment-booking' ),
			'value' => '30',
		),
		array(
			'key'   => __( 'All', 'bookingpress-appointment-booking' ),
			'value' => 'all',
		),
		array(
			'key'   => __( 'Custom', 'bookingpress-appointment-booking' ),
			'value' => 'custom',
		),
	),
	'download_log_daterange'          => array( date( 'Y-m-d', strtotime( '-3 Day' ) ), date( 'Y-m-d', strtotime( '+3 Day' ) ) ),
	'is_display_download_save_loader' => '0',
	'proper_body_class'               => false,
	'smtp_mail_error_text'			  => '',
	'smtp_error_modal'				  => false,
);
