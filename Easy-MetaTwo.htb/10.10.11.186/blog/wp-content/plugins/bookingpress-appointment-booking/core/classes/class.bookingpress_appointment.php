<?php
if ( ! class_exists( 'bookingpress_appointment' ) ) {
	class bookingpress_appointment {
		function __construct() {
			add_action( 'wp_ajax_bookingpress_get_appointments', array( $this, 'bookingpress_get_appointment' ) );
			add_action( 'wp_ajax_bookingpress_get_search_employess', array( $this, 'bookingpress_search_employess' ) );
			add_action( 'wp_ajax_bookingpress_delete_appointment', array( $this, 'bookingpress_delete_appointment' ) );
			add_action( 'wp_ajax_bookingpress_edit_appointment', array( $this, 'bookingpress_edit_appointment' ) );
			add_action( 'wp_ajax_bookingpress_bulk_appointment', array( $this, 'bookingpress_bulk_appointment' ) );
			add_action( 'wp_ajax_bookingpress_add_appointment_booking', array( $this, 'bookingpress_add_appointment_booking_func' ) );

			add_action( 'bookingpress_appointments_dynamic_vue_methods', array( $this, 'bookingpress_appointment_dynamic_vue_methods_func' ), 10 );
			add_action( 'bookingpress_appointments_dynamic_on_load_methods', array( $this, 'bookingpress_appointment_dynamic_on_load_methods_func' ), 10 );
			add_action( 'bookingpress_appointments_dynamic_data_fields', array( $this, 'bookingpress_appointment_dynamic_data_fields_func' ), 10 );
			add_action( 'bookingpress_appointments_dynamic_directives', array( $this, 'bookingpress_appointment_dynamic_directives' ), 10 );
			add_action( 'bookingpress_appointments_dynamic_helper_vars', array( $this, 'bookingpress_appointment_dynamic_helper_func' ), 10 );
			add_action( 'bookingpress_appointments_dynamic_view_load', array( $this, 'bookingpress_dynamic_load_appointment_view_func' ), 10 );
		}

		function bookingpress_dynamic_load_appointment_view_func() {
			$bookingpress_load_file_name = BOOKINGPRESS_VIEWS_DIR . '/appointment/manage_appointment.php';
			$bookingpress_load_file_name = apply_filters( 'bookingpress_modify_appointment_view_file_path', $bookingpress_load_file_name );

			require $bookingpress_load_file_name;
		}

		function bookingpress_appointment_dynamic_data_fields_func() {
			global $wpdb,$BookingPress,$bookingpress_appointment_vue_data_fields,$tbl_bookingpress_customers, $tbl_bookingpress_categories, $tbl_bookingpress_services;

			// Fetch customers details
			$bookingpress_customer_details           = $wpdb->get_results( 'SELECT bookingpress_customer_id, bookingpress_user_firstname, bookingpress_user_lastname, bookingpress_user_email FROM ' . $tbl_bookingpress_customers . ' WHERE bookingpress_user_type = 2 AND bookingpress_user_status = 1', ARRAY_A );
			$bookingpress_customer_selection_details = array();
			$bookingpress_customer_name              = '';
			foreach ( $bookingpress_customer_details as $bookingpress_customer_key => $bookingpress_customer_val ) {
				$bookingpress_customer_name = ( $bookingpress_customer_val['bookingpress_user_firstname'] == '' && $bookingpress_customer_val['bookingpress_user_lastname'] == '' ) ? $bookingpress_customer_val['bookingpress_user_email'] : $bookingpress_customer_val['bookingpress_user_firstname'] . ' ' . $bookingpress_customer_val['bookingpress_user_lastname'];

				$bookingpress_customer_selection_details[] = array(
					'text'  => $bookingpress_customer_name,
					'value' => $bookingpress_customer_val['bookingpress_customer_id'],
				);
			}
			$bookingpress_appointment_vue_data_fields['appointment_customers_list'] = $bookingpress_customer_selection_details;
			$bookingpress_appointment_vue_data_fields['search_customer_list']       = $bookingpress_customer_selection_details;

			// Fetch staff members details
			$bookingpress_staff_members_details          = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_customers . ' WHERE bookingpress_user_type = 1 AND bookingpress_user_status = 1', ARRAY_A );
			$bookingpress_staff_member_selection_details = array();
			foreach ( $bookingpress_staff_members_details as $bookingpress_staff_members_key => $bookingpress_staff_members_val ) {
				$bookingpress_staff_member_selection_details[] = array(
					'text'  => $bookingpress_staff_members_val['bookingpress_user_login'],
					'value' => $bookingpress_staff_members_val['bookingpress_customer_id'],
				);
			}
			$bookingpress_appointment_vue_data_fields['appointment_staff_members_list'] = $bookingpress_staff_member_selection_details;
			$bookingpress_appointment_vue_data_fields['service_employee']               = $bookingpress_staff_member_selection_details;

			// Fetch Services Details
			$bookingpress_services_details2   = array();
			$bookingpress_services_details2[] = array(
				'category_name'     => '',
				'category_services' => array(
					'0' => array(
						'service_id'    => 0,
						'service_name'  => __( 'Select service', 'bookingpress-appointment-booking' ),
						'service_price' => '',
					),
				),
			);
			$bookingpress_services_details    = $BookingPress->get_bookingpress_service_data_group_with_category();
			$bookingpress_services_details2   = array_merge( $bookingpress_services_details2, $bookingpress_services_details );
			$bookingpress_appointment_vue_data_fields['appointment_services_list'] = $bookingpress_services_details2;
			$bookingpress_appointment_vue_data_fields['appointment_services_data'] = $bookingpress_services_details;

			$bookingpress_default_status_option = $BookingPress->bookingpress_get_settings( 'appointment_status', 'general_setting' );
			$bookingpress_appointment_vue_data_fields['appointment_formdata']['appointment_status'] = ! empty( $bookingpress_default_status_option ) ? $bookingpress_default_status_option : 'Approved';

			// Pagination data
			$bookingpress_default_perpage_option                               = $BookingPress->bookingpress_get_settings( 'per_page_item', 'general_setting' );
			$bookingpress_appointment_vue_data_fields['perPage']               = ! empty( $bookingpress_default_perpage_option ) ? $bookingpress_default_perpage_option : '20';
			$bookingpress_appointment_vue_data_fields['pagination_length_val'] = ! empty( $bookingpress_default_perpage_option ) ? $bookingpress_default_perpage_option : '20';

			$default_daysoff_details = $BookingPress->bookingpress_get_default_dayoff_dates();
			if ( ! empty( $default_daysoff_details ) ) {
				$default_daysoff_details                                   = array_map(
					function( $date ) {
						return date( 'Y-m-d', strtotime( $date ) );
					},
					$default_daysoff_details
				);
				$bookingpress_appointment_vue_data_fields['disabledDates'] = $default_daysoff_details;
			} else {
				$bookingpress_appointment_vue_data_fields['disabledDates'] = '';
			}
			$bookingpress_appointment_vue_data_fields['appointment_formdata']['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );
			$bookingpress_appointment_vue_data_fields                                     = apply_filters( 'bookingpress_modify_appointment_data_fields', $bookingpress_appointment_vue_data_fields );
			echo json_encode( $bookingpress_appointment_vue_data_fields );
		}

		function bookingpress_appointment_dynamic_helper_func() {
			global $bookingpress_global_options;
			$bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_locale_lang = $bookingpress_options['locale'];
			?>
			var lang = ELEMENT.lang.<?php echo esc_html( $bookingpress_locale_lang ); ?>;
			ELEMENT.locale(lang)
			const createSortable = (el, options, vnode) => {
				return Sortable.create(el, {
					...options
				});
			};
			const sortable = {
				name: 'sortable',
				bind(el, binding, vnode) {
					const table = el;
					table._sortable = createSortable(table.querySelector("tbody"), binding.value, vnode);
				}
			};
			<?php
		}
		function bookingpress_appointment_dynamic_directives() {
			echo esc_html( 'sortable' );
		}
		function bookingpress_appointment_dynamic_on_load_methods_func() {
			?>
			this.loadAppointments().catch(error => {
				console.error(error)
			})
			<?php
			do_action( 'bookingpress_add_appointment_dynamic_on_load_methods' );
		}
		function bookingpress_appointment_dynamic_vue_methods_func() {
			global $BookingPress,$bookingpress_notification_duration;
			$bookingpress_default_status_option = $BookingPress->bookingpress_get_settings( 'appointment_status', 'general_setting' );
			$bookingpress_default_status_option = ! empty( $bookingpress_default_status_option ) ? $bookingpress_default_status_option : 'Approved';
			?>
			toggleBusy() {
				if(this.is_display_loader == '1'){
					this.is_display_loader = '0'
				}else{
					this.is_display_loader = '1'
				}
			},
			handleSelectionChange(val) {
				this.multipleSelection = val;
				this.bulk_action = 'bulk_action';
			},
			handleSizeChange(val) {
				this.perPage = val
				this.loadAppointments()
			},
			handleCurrentChange(val) {
				this.currentPage = val;
				this.loadAppointments()
			},
			changeCurrentPage(perPage) {
				var total_item = this.totalItems;
				var recored_perpage = perPage;
				var select_page =  this.currentPage;				
				var current_page = Math.ceil(total_item/recored_perpage);
				if(total_item <= recored_perpage ) {
					current_page = 1;
				} else if(select_page >= current_page ) {
					
				} else {
					current_page = select_page;
				}
				return current_page;
			},
			changePaginationSize(selectedPage) { 	
				var total_recored_perpage = selectedPage;
				var current_page = this.changeCurrentPage(total_recored_perpage);										
				this.perPage = selectedPage;					
				this.currentPage = current_page;	
				this.loadAppointments()
			},
			saveAppointmentBooking(bookingAppointment){
				const vm = new Vue()
				const vm2 = this
					this.$refs[bookingAppointment].validate((valid) => {
						if (valid) {
						vm2.is_disabled = true
						vm2.is_display_save_loader = '1'
						var postData = { action:'bookingpress_save_appointment_booking', appointment_data: vm2.appointment_formdata,_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' };
						axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
						.then(function(response){
							vm2.is_disabled = false
							vm2.is_display_save_loader = '0'
							vm2.closeAppointmentModal()
							vm2.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: response.data.variant,
								customClass: response.data.variant+'_notification',
								duration:<?php echo intval($bookingpress_notification_duration); ?>,
							});
							vm2.loadAppointments()
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
				});
			},
			async loadAppointments() {
				this.toggleBusy();
				const vm2 = this
				var bookingpress_search_data = { 'search_appointment':this.search_appointment,'selected_date_range': this.appointment_date_range, 'customer_name': this.search_customer_name,'service_name': this.search_service_name,'appointment_status': this.search_appointment_status }

				<?php do_action('bookingpress_appointment_add_post_data') ?>

				var postData = { action:'bookingpress_get_appointments', perpage:this.perPage, currentpage:this.currentPage, search_data: bookingpress_search_data,_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>'};
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {
					vm2.toggleBusy();
					vm2.items = response.data.items;
					vm2.totalItems = response.data.totalItems;
				}.bind(this) )
				.catch( function (error) {					
					vm2.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something Went wrong...', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
				});
			},
			bookingpress_get_staff_members(event){
				const vm = this
				var selected_service_id = event
				var postData = { action:'bookingpress_get_service_staff_members_data', selected_service: selected_service_id };
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {
					vm.appointment_formdata.appointment_selected_staff_member = ''
					vm.appointment_staff_members_list = response.data
				}.bind(this) )
				.catch( function (error) {
					console.log(error);
				});
			},
			open_add_appointment_modal() {				
				this.open_appointment_modal = true;
			},		
			bookingpress_loader_hide() {
				this.modal_loader = 0
			},			
			editAppointmentData(index,row) {
				const vm2 = this
				var edit_id = row.appointment_id;
				vm2.appointment_formdata.appointment_update_id = edit_id
				vm2.open_add_appointment_modal()				
				var postData = { action:'bookingpress_get_edit_appointment_data', payment_log_id: edit_id, appointment_id: edit_id,_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' };
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						if(response.data != undefined || response.data != [])
						{
							vm2.appointment_formdata.appointment_selected_customer = response.data.bookingpress_customer_id
							vm2.appointment_formdata.appointment_selected_service = response.data.bookingpress_service_id
							vm2.appointment_formdata.appointment_selected_staff_member = response.data.bookingpress_staff_member_id
							vm2.appointment_formdata.appointment_booked_date = response.data.bookingpress_appointment_date
							vm2.appointment_formdata.appointment_booked_time = response.data.bookingpress_appointment_time
							vm2.appointment_formdata.appointment_internal_note = response.data.bookingpress_appointment_internal_note			
							vm2.appointment_time_slot = response.data.appointment_time_slot	

							if(response.data.bookingpress_appointment_send_notification == '0'){
								vm2.appointment_formdata.appointment_send_notification = false
							}else{
								vm2.appointment_formdata.appointment_send_notification = true
							}
							vm2.appointment_formdata.appointment_status = response.data.bookingpress_appointment_status
						}
					}.bind(this) )
					.catch( function (error) {
						console.log(error);
					});
			},
			deleteAppointment(index, row) {
				const vm = new Vue()
				const vm2 = this
				var delete_id = row.appointment_id
				var appointment_delete_data = { action: 'bookingpress_delete_appointment', delete_id: delete_id,_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' }
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( appointment_delete_data ) )
				.then(function(response){
					vm2.$notify({
						title: response.data.title,
						message: response.data.msg,
						type: response.data.variant,
						customClass: 'error_notification',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
					vm2.loadAppointments()
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
			bulk_actions() {				
				const vm = new Vue()
				const vm2 = this
				if(vm2.bulk_action == "bulk_action")
				{
					vm2.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Please select any action...', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
				}
				else
				{
					if(this.multipleSelection.length > 0 && this.bulk_action == "delete")
					{
						var appointment_delete_data = {
							action:'bookingpress_bulk_appointment',
							app_delete_ids: this.multipleSelection,
							bulk_action: 'delete',
							_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>',
						}
						axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( appointment_delete_data ) )
						.then(function(response){
							vm2.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: response.data.variant,
								customClass: response.data.variant+'_notification',
								duration:<?php echo intval($bookingpress_notification_duration); ?>,
							});
							vm2.loadAppointments();
							vm2.multipleSelection = [];
							vm2.totalItems = vm2.items.length
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
					else
					{	
						if(this.multipleSelection.length == 0) {
							vm2.$notify({
								title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
								message: '<?php esc_html_e( 'Please select one or more records.', 'bookingpress-appointment-booking' ); ?>',
								type: 'error',
								customClass: 'error_notification',
								duration:<?php echo intval($bookingpress_notification_duration); ?>,
							});
						} else {
							<?php do_action( 'bookingpress_appointment_dynamic_bulk_action' ); ?>
						}
					}
				}
			},
			resetFilter(){
				const vm = this
				vm.search_appointment = '';
				vm.appointment_date_range = ''
				vm.search_service_employee = ''
				vm.search_customer_name = ''
				vm.search_service_name = ''
				vm.search_appointment_status = ''
				vm.loadAppointments()
			},
			resetForm() {
					const vm2 = this
					vm2.appointment_formdata.appointment_selected_customer = ''
					vm2.appointment_formdata.appointment_selected_staff_member= ''
					vm2.appointment_formdata.appointment_selected_service = ''
					vm2.appointment_formdata.appointment_booked_date = '<?php echo esc_html( date( 'Y-m-d', current_time( 'timestamp' ) ) ); ?>';
					vm2.appointment_formdata.appointment_booked_time = ''
					vm2.appointment_formdata.appointment_internal_note = ''
					vm2.appointment_formdata.appointment_send_notification = ''
					vm2.appointment_formdata.appointment_status = '<?php echo esc_html( $bookingpress_default_status_option ); ?>'
					vm2.appointment_formdata.appointment_update_id = 0				
			},
			closeAppointmentModal() {
				const vm2= this
				vm2.$refs['appointment_formdata'].resetFields()
				vm2.resetForm()
				vm2.open_appointment_modal = false
			},				
			closeBulkAction(){
				this.$refs.multipleTable.clearSelection();
				this.bulk_action = 'bulk_action';
			},
			select_date(selected_value) {
				this.appointment_formdata.appointment_booked_date = this.get_formatted_date(this.appointment_formdata.appointment_booked_date)
				this.bookingpress_set_time_slot()
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
					this.appointment_date_range[0] = this.get_formatted_date(this.appointment_date_range[0])				
					this.appointment_date_range[1] = this.get_formatted_date(this.appointment_date_range[1])
				}
			},			
			bookingpress_set_time_slot() {
				const vm = this
				var service_id = vm.appointment_formdata.appointment_selected_service;
				var selected_appointment_date = vm.appointment_formdata.appointment_booked_date;
				vm.appointment_formdata.appointment_booked_time = '' ;
				if(service_id != '' &&  selected_appointment_date != '') {
					var postData = { action:'bookingpress_set_appointment_time_slot', service_id: 
					service_id,selected_date:selected_appointment_date ,_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' };
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						if(response.data != undefined || response.data != [])
						{								
							vm.appointment_time_slot = response.data;							
						}
					}.bind(this) )
					.catch( function (error) {
						console.log(error);
					});					
				} else {
					if(service_id == '' || service_id == undefined || service_id == 'undefined'){
						vm.$notify({
							title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
							message: '<?php esc_html_e( 'Please select service to get available date and time slots.', 'bookingpress-appointment-booking' ); ?>',
							type: 'error',
							customClass: 'error_notification',
							duration:<?php echo intval($bookingpress_notification_duration); ?>,
						});
					}
					vm.appointment_time_slot = '';
				}
			},
			bookingpress_change_status(update_id, selectedValue){
				const vm2 = this
				var postData = { action:'bookingpress_change_upcoming_appointment_status', update_appointment_id: update_id, appointment_new_status: selectedValue };
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {
					if(response.data == "0" || response.data == 0){
						vm2.$notify({
							title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
							message: '<?php esc_html_e( 'Appointment already booked for this slot', 'bookingpress-appointment-booking' ); ?>',
							type: 'error',
							customClass: 'error_notification',
							duration:<?php echo intval($bookingpress_notification_duration); ?>,
						});
						return false;
					}else{
						vm2.$notify({
							title: '<?php esc_html_e( 'Success', 'bookingpress-appointment-booking' ); ?>',
							message: '<?php esc_html_e( 'Appointment Status Changed Successfully', 'bookingpress-appointment-booking' ); ?>',
							type: 'success',
							customClass: 'success_notification',
							duration:<?php echo intval($bookingpress_notification_duration); ?>,
						});
					}
				}.bind(this) )
				.catch( function (error) {
					vm2.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something Went wrong...', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,						
					});
				});
			},
			<?php
			do_action( 'bookingpress_appointment_add_dynamic_vue_methods' );
		}

		function bookingpress_get_appointment() {
			global $BookingPress,$wpdb, $tbl_bookingpress_services,$tbl_bookingpress_appointment_bookings,$tbl_bookingpress_payment_logs,$tbl_bookingpress_customers,$bookingpress_global_options;
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
			$perpage                         = isset( $_POST['perpage'] ) ? intval( $_POST['perpage'] ) : 10;
			$currentpage                     = isset( $_POST['currentpage'] ) ? intval( $_POST['currentpage'] ) : 1;
			$offset                          = ( ! empty( $currentpage ) && $currentpage > 1 ) ? ( ( $currentpage - 1 ) * $perpage ) : 0;
			$bookingpress_search_data        = ! empty( $_REQUEST['search_data'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['search_data'] ) : array();
			$bookingpress_search_query       = '';
			$bookingpress_search_query_where = 'WHERE 1=1 ';

			if ( ! empty( $bookingpress_search_data ) ) {
				if ( ! empty( $bookingpress_search_data['search_appointment'] ) ) {
					$bookingpress_search_string = $bookingpress_search_data['search_appointment'];
					$bookingpress_search_result = $wpdb->get_results( 'SELECT bookingpress_customer_id  FROM ' . $tbl_bookingpress_customers . " WHERE bookingpress_user_firstname LIKE '%{$bookingpress_search_string}%' OR bookingpress_user_lastname LIKE '%{$bookingpress_search_string}%' OR bookingpress_user_login LIKE '%{$bookingpress_search_string}%' AND (bookingpress_user_type = 1 OR bookingpress_user_type = 2)", ARRAY_A );
					if ( ! empty( $bookingpress_search_result ) ) {
						$bookingpress_customer_ids = array();
						foreach ( $bookingpress_search_result as $item ) {
							$bookingpress_customer_ids[] = $item['bookingpress_customer_id'];
						}
						$bookingpress_search_user_id      = implode( ',', $bookingpress_customer_ids );
						$bookingpress_search_query_where .= "AND (bookingpress_customer_id IN ({$bookingpress_search_user_id}) OR bookingpress_staff_member_id IN ({$bookingpress_search_user_id}))";
					} else {
						$bookingpress_search_query_where .= "AND (bookingpress_service_name LIKE '%{$bookingpress_search_string}%')";
					}
				}
				if ( ! empty( $bookingpress_search_data['selected_date_range'] ) ) {
					$bookingpress_search_date         = $bookingpress_search_data['selected_date_range'];
					$start_date                       = date( 'Y-m-d', strtotime( $bookingpress_search_date[0] ) );
					$end_date                         = date( 'Y-m-d', strtotime( $bookingpress_search_date[1] ) );
					$bookingpress_search_query_where .= "AND (bookingpress_appointment_date BETWEEN '{$start_date}' AND '{$end_date}')";
				}				
				if ( ! empty( $bookingpress_search_data['customer_name'] ) ) {
					$bookingpress_search_name         = $bookingpress_search_data['customer_name'];
					$bookingpress_search_customer_id  = implode( ',', $bookingpress_search_name );
					$bookingpress_search_query_where .= "AND (bookingpress_customer_id IN ({$bookingpress_search_customer_id}))";
				}
				if ( ! empty( $bookingpress_search_data['service_name'] ) ) {
					$bookingpress_search_name         = $bookingpress_search_data['service_name'];
					$bookingpress_search_service_id   = implode( ',', $bookingpress_search_name );
					$bookingpress_search_query_where .= "AND (bookingpress_service_id IN ({$bookingpress_search_service_id}))";
				}
				if ( ! empty( $bookingpress_search_data['appointment_status'] && $bookingpress_search_data['appointment_status'] != 'all' ) ) {
					$bookingpress_search_name         = $bookingpress_search_data['appointment_status'];
					$bookingpress_search_query_where .= "AND (bookingpress_appointment_status = '{$bookingpress_search_name}')";
				}
				$bookingpress_search_query_where = apply_filters( 'bookingpress_appointment_view_add_filter', $bookingpress_search_query_where,$bookingpress_search_data );
			}

			$get_total_appointments = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_appointment_bookings . " {$bookingpress_search_query}{$bookingpress_search_query_where} ", ARRAY_A );

			$total_appointments = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_appointment_bookings . " {$bookingpress_search_query}{$bookingpress_search_query_where} order by bookingpress_appointment_booking_id DESC LIMIT " . $offset . ',' . $perpage, ARRAY_A );
			$appointments       = array();
			if ( ! empty( $total_appointments ) ) {
				$counter                  = 1;

				$bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();
				$bookingpress_default_date_time_format = $bookingpress_global_options_arr['wp_default_date_format'].' '.$bookingpress_global_options_arr['wp_default_time_format'];

				foreach ( $total_appointments as $get_appointment ) {
					$appointment                     = array();
					$appointment['id']               = $counter;
					$appointment_id                  = intval( $get_appointment['bookingpress_appointment_booking_id'] );
					$appointment['appointment_id']   = $appointment_id;
					$staff_member_id                 = ! empty( $get_appointment['bookingpress_staff_member_id'] ) ? intval( $get_appointment['bookingpress_staff_member_id'] ) : 0;
					$payment_log                     = $wpdb->get_row( 'SELECT bookingpress_customer_firstname,bookingpress_customer_lastname,bookingpress_customer_email FROM ' . $tbl_bookingpress_payment_logs . ' WHERE bookingpress_appointment_booking_ref =' . $appointment_id, ARRAY_A );

					$appointment_date_time = $get_appointment['bookingpress_appointment_date'].' '.$get_appointment['bookingpress_appointment_time'];
					$appointment['appointment_date'] =  date( $bookingpress_default_date_time_format ,strtotime($appointment_date_time) );

					$customer_name = ! empty( $payment_log['bookingpress_customer_firstname'] ) ? esc_html( $payment_log['bookingpress_customer_firstname'] . ' ' . $payment_log['bookingpress_customer_lastname'] ) : esc_html( $payment_log['bookingpress_customer_email'] );

					$appointment['customer_name'] = $customer_name;
					$appointment['service_name']  = esc_html( $get_appointment['bookingpress_service_name'] );
					$service_duration             = esc_html( $get_appointment['bookingpress_service_duration_val'] );
					$service_duration_unit        = esc_html( $get_appointment['bookingpress_service_duration_unit'] );
					if ( $service_duration_unit == 'm' ) {
						$service_duration .= ' ' . esc_html__( 'Mins', 'bookingpress-appointment-booking' );
					} else {
						$service_duration .= ' ' . esc_html__( 'Hours', 'bookingpress-appointment-booking' );
					}
					$appointment['appointment_duration'] = $service_duration;
					$currency_name                       = $get_appointment['bookingpress_service_currency'];
					$currency_symbol                     = $BookingPress->bookingpress_get_currency_symbol( $currency_name );
					$payment_amount                      = $BookingPress->bookingpress_price_formatter_with_currency_symbol( $get_appointment['bookingpress_service_price'], $currency_symbol );
					$appointment['appointment_payment']  = $payment_amount;
					$appointment['appointment_status']   = esc_html( $get_appointment['bookingpress_appointment_status'] );

					$appointment = apply_filters( 'bookingpress_appointment_add_view_field', $appointment,$get_appointment);

					$appointments[]                      = $appointment;
					$counter++;
				}
			}
			$data['items']       = $appointments;
			$data ['totalItems'] = count( $get_total_appointments );
			wp_send_json( $data );

		}
		function bookingpress_delete_appointment( $appointment_id = '' ) {
			global $wpdb,$tbl_bookingpress_appointment_bookings,$tbl_bookingpress_payment_logs;
			$response              = array();
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( isset( $_POST['action'] ) && sanitize_text_field( $_POST['action'] ) == 'bookingpress_delete_appointment' ) {
				if ( ! $bpa_verify_nonce_flag ) {
					$response['variant'] = 'error';
					$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
					$response['msg']     = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
					wp_send_json( $response );
					die();
				}
			}
			$appointment_id      = isset( $_POST['delete_id'] ) ? intval( $_POST['delete_id'] ) : $appointment_id;
			$response['variant'] = 'error';
			$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']     = esc_html__( 'Something Went wrong...', 'bookingpress-appointment-booking' );
			$return              = false;
			if ( ! empty( $appointment_id ) ) {
				$wpdb->delete( $tbl_bookingpress_appointment_bookings, array( 'bookingpress_appointment_booking_id' => $appointment_id ), array( '%d' ) );
				$wpdb->delete( $tbl_bookingpress_payment_logs, array( 'bookingpress_appointment_booking_ref' => $appointment_id ), array( '%d' ) );
				$response['variant'] = 'success';
				$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Appointment has been deleted successfully.', 'bookingpress-appointment-booking' );
				$return              = true;
			}
			if ( isset( $_POST['action'] ) && sanitize_text_field( $_POST['action'] ) == 'bookingpress_delete_appointment' ) {
				wp_send_json( $response );
			}
			return $return;
		}
		function bookingpress_bulk_appointment() {
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
			$response['msg']     = esc_html__( 'Something Went wrong...', 'bookingpress-appointment-booking' );
			if ( ! empty( $_POST['bulk_action'] ) && sanitize_text_field( $_POST['bulk_action'] ) == 'delete' ) {
				$delete_ids = ! empty( $_POST['app_delete_ids'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['app_delete_ids'] ) : array();
				if ( ! empty( $delete_ids ) ) {
					foreach ( $delete_ids as $delete_key => $delete_val ) {
						if ( is_array( $delete_val ) ) {
							$delete_val = $delete_val['appointment_id'];
						}
						$return = $this->bookingpress_delete_appointment( $delete_val );
						if ( $return ) {
							$response['variant'] = 'success';
							$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
							$response['msg']     = esc_html__( 'Appointment has been deleted successfully.', 'bookingpress-appointment-booking' );
						}
					}
				}
			}
			wp_send_json( $response );
		}

	}
}

global $bookingpress_appointment, $bookingpress_appointment_vue_data_fields;
$bookingpress_appointment = new bookingpress_appointment();

global $bookingpress_global_options,$BookingPress,$wpdb, $tbl_bookingpress_customers,$bookingpress_appointment_status_array;
$bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
$bookingpress_locale_lang = $bookingpress_options['locale'];
$bookingpress_pagination  = $bookingpress_options['pagination'];

$bookingpress_pagination_arr      = json_decode( $bookingpress_pagination, true );
$bookingpress_pagination_selected = $bookingpress_pagination_arr[0];

$bookingpress_appointment_status_array    = array(
	array(
		'text'  => __('All', 'bookingpress-appointment-booking'),
		'value' => 'all',
	),
	array(
		'text'  => __('Approved', 'bookingpress-appointment-booking'),
		'value' => 'Approved',
	),
	array(
		'text'  => __('Pending', 'bookingpress-appointment-booking'),
		'value' => 'Pending',
	),
	array(
		'text'  => __('Cancelled', 'bookingpress-appointment-booking'),
		'value' => 'Cancelled',
	),
	array(
		'text'  => __('Rejected', 'bookingpress-appointment-booking'),
		'value' => 'Rejected',
	),
);
$bookingpress_appointment_vue_data_fields = array(
	'bulk_action'                    => 'bulk_action',
	'bulk_options'                   => array(
		array(
			'value' => 'bulk_action',
			'label' => __( 'Bulk Action', 'bookingpress-appointment-booking' ),
		),
		array(
			'value' => 'delete',
			'label' => __( 'Delete', 'bookingpress-appointment-booking' ),
		),
	),
	'loading'                        => false,
	'items'                          => array(),
	'multipleSelection'              => array(),
	'appointment_customers_list'     => array(),
	'appointment_services_list'      => array(),
	'perPage'                        => $bookingpress_pagination_selected,
	'totalItems'                     => 0,
	'pagination_selected_length'     => $bookingpress_pagination_selected,
	'pagination_length'              => $bookingpress_pagination,
	'currentPage'                    => 1,
	'search_appointment'             => '',
	'appointment_date_range'         => array( date( 'Y-m-d', strtotime( '-3 Day' ) ), date( 'Y-m-d', strtotime( '+3 Day' ) ) ),
	'search_customer_name'           => '',
	'search_service_name'            => '',
	'search_service_employee'        => '',
	'search_appointment_status'      => '',
	'search_customer_list'           => '',
	'search_status'                  => $bookingpress_appointment_status_array,
	'appointment_time_slot'          => array(),
	'appointment_status'             => array(
		array(
			'text'  => __('Approved', 'bookingpress-appointment-booking'),
			'value' => 'Approved',
		),
		array(
			'text'  => __('Pending', 'bookingpress-appointment-booking'),
			'value' => 'Pending',
		),
		array(
			'text'  => __('Cancelled', 'bookingpress-appointment-booking'),
			'value' => 'Cancelled',
		),
		array(
			'text'  => __('Rejected', 'bookingpress-appointment-booking'),
			'value' => 'Rejected',
		),
	),
	'service_employee'               => array(),
	'appointment_services_data'      => array(),
	'modal_loader'                   => 1,
	'rules'                          => array(
		'appointment_selected_customer' => array(
			array(
				'required' => true,
				'message'  => __( 'Please select customer', 'bookingpress-appointment-booking' ),
				'trigger'  => 'change',
			),
		),
		'appointment_selected_service'  => array(
			array(
				'required' => true,
				'message'  => __( 'Please select service', 'bookingpress-appointment-booking' ),
				'trigger'  => 'change',
			),
		),
		'appointment_booked_date'       => array(
			array(
				'required' => true,
				'message'  => __( 'Please select booking date', 'bookingpress-appointment-booking' ),
				'trigger'  => 'change',
			),
		),
		'appointment_booked_time'       => array(
			array(
				'required' => true,
				'message'  => __( 'Please select booking time', 'bookingpress-appointment-booking' ),
				'trigger'  => 'change',
			),
		),
	),
	'appointment_formdata'           => array(
		'appointment_selected_customer'     => '',
		'appointment_selected_staff_member' => '',
		'appointment_selected_service'      => '',
		'appointment_booked_date'           => date( 'Y-m-d', current_time( 'timestamp' ) ),
		'appointment_booked_time'           => '',
		'appointment_internal_note'         => '',
		'appointment_send_notification'     => false,
		'appointment_status'                => 'Approved',
		'appointment_update_id'             => 0,
	),
	'pagination_length_val'          => '10',
	'pagination_val'                 => array(
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
	'savebtnloading'                 => false,
	'open_appointment_modal'         => false,
	'is_display_loader'              => '0',
	'is_disabled'                    => false,
	'is_display_save_loader'         => '0',
);
