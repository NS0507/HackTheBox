<?php
if ( ! class_exists( 'bookingpress_payment' ) ) {
	class bookingpress_payment {
		function __construct() {
			add_action( 'bookingpress_payments_dynamic_view_load', array( $this, 'bookingpress_payments_dynamic_view_load_func' ) );
			add_action( 'bookingpress_payments_dynamic_data_fields', array( $this, 'bookingpress_payments_dynamic_data_fields_func' ) );
			add_action( 'bookingpress_payments_dynamic_vue_methods', array( $this, 'bookingpress_payments_dynamic_vue_methods_func' ), 10 );
			add_action( 'bookingpress_payments_dynamic_on_load_methods', array( $this, 'bookingpress_payments_dynamic_on_load_methods_func' ), 10 );
			add_action( 'bookingpress_payments_dynamic_helper_vars', array( $this, 'bookingpress_service_dynamic_helper_func' ), 10 );

			add_action( 'wp_ajax_bookingpress_get_payments_data', array( $this, 'bookingpress_get_payments_details' ), 10 );
			add_action( 'wp_ajax_bookingpress_delete_payment_log', array( $this, 'bookingpress_delete_payment_log_func' ), 10 );
			add_action( 'wp_ajax_bookingpress_get_search_data', array( $this, 'bookingpress_get_search_data' ), 10 );
			add_action( 'wp_ajax_bookingpress_bulk_payment_logs_action', array( $this, 'bookingpress_payment_log_bulk_action' ), 10 );
			add_action( 'wp_ajax_bookingpress_fetch_payment_log', array( $this, 'bookingpress_fetch_payment_log_data' ), 10 );

			add_action( 'wp_ajax_bookingpress_approve_appointment', array( $this, 'bookingpress_approve_appointment' ), 10 );
		}

		function bookingpress_approve_appointment() {
			global $wpdb, $tbl_bookingpress_payment_logs, $tbl_bookingpress_appointment_bookings, $bookingpress_email_notifications;
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			$response              = array();
			if ( ! $bpa_verify_nonce_flag ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
				echo json_encode( $response );
				exit;
			}

			$response['variant'] = 'error';
			$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']     = esc_html__( 'Something Went wrong...', 'bookingpress-appointment-booking' );

			$approve_payment_log_id      = ! empty( $_REQUEST['approve_id'] ) ? intval( $_REQUEST['approve_id'] ) : 0;
			$payment_data                = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_payment_logs} WHERE bookingpress_payment_log_id = {$approve_payment_log_id}", ARRAY_A );
			$appointment_ref_id          = ! empty( $payment_data['bookingpress_appointment_booking_ref'] ) ? $payment_data['bookingpress_appointment_booking_ref'] : 0;
			$bookingpress_customer_email = ! empty( $payment_data['bookingpress_customer_email'] ) ? $payment_data['bookingpress_customer_email'] : '';
			if ( ! empty( $appointment_ref_id ) ) {
				$wpdb->update( $tbl_bookingpress_appointment_bookings, array( 'bookingpress_appointment_status' => 'Approved' ), array( 'bookingpress_appointment_booking_id' => $appointment_ref_id ) );

				$wpdb->update( $tbl_bookingpress_payment_logs, array( 'bookingpress_payment_status' => 'success' ), array( 'bookingpress_appointment_booking_ref' => $appointment_ref_id ) );

				$bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification( 'Appointment Approved', $appointment_ref_id, $bookingpress_customer_email );

				$response['variant'] = 'success';
				$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Appointment Approved Successfully', 'bookingpress-appointment-booking' );
			}

			echo json_encode( $response );
			exit();
		}

		function bookingpress_service_dynamic_helper_func() {
			global $bookingpress_global_options;
			$bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_locale_lang = $bookingpress_options['locale'];
			?>
				var lang = ELEMENT.lang.<?php echo esc_html( $bookingpress_locale_lang ); ?>;
				ELEMENT.locale(lang)
			<?php
		}

		function bookingpress_payments_dynamic_view_load_func() {
			$bookingpress_load_file_name = BOOKINGPRESS_VIEWS_DIR . '/payment/manage_payment.php';
			$bookingpress_load_file_name = apply_filters( 'bookingpress_modify_payment_view_file_path', $bookingpress_load_file_name );

			require $bookingpress_load_file_name;
		}

		function bookingpress_payments_dynamic_data_fields_func() {
			global $bookingpress_payment_vue_data_fields,$BookingPress;

			// pagination data
			$bookingpress_default_perpage_option                           = $BookingPress->bookingpress_get_settings( 'per_page_item', 'general_setting' );
			$bookingpress_payment_vue_data_fields['perPage']               = ! empty( $bookingpress_default_perpage_option ) ? $bookingpress_default_perpage_option : '10';
			$bookingpress_payment_vue_data_fields['pagination_length_val'] = ! empty( $bookingpress_default_perpage_option ) ? $bookingpress_default_perpage_option : '10';

			$bookingpress_payment_vue_data_fields = apply_filters( 'bookingpress_modify_payment_data_fields', $bookingpress_payment_vue_data_fields );
			echo json_encode( $bookingpress_payment_vue_data_fields );
		}

		function bookingpress_payments_dynamic_on_load_methods_func() {
			?>
				this.loadPayments()
				this.fetchSearchData()
			<?php
		}

		function bookingpress_payments_dynamic_vue_methods_func() {
			global $bookingpress_notification_duration;
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
					this.loadPayments()
				},
				handleCurrentChange(val) {
					this.currentPage = val;
					this.loadPayments()
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
					this.loadPayments()
				},
				async loadPayments() {
					const vm = this
					vm.toggleBusy()
					var postData = { action:'bookingpress_get_payments_data', perpage:this.perPage, currentpage:this.currentPage, search_data:vm.search_data,_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' };

					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						vm.toggleBusy()
						this.items = response.data.items;
						this.totalItems = response.data.total;
					}.bind(this) )
					.catch( function (error) {
						console.log(error);
					});
				},
				async fetchSearchData(){
					const vm = this
					var postData = { action:'bookingpress_get_search_data',_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>'};
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						vm.search_customer_data = response.data.customers
						vm.search_staffmember_data = response.data.staff_members
						vm.search_services_data = response.data.services
					}.bind(this) )
					.catch( function (error) {
						console.log(error);
					});
				},
				closeBulkAction(){
					this.$refs.multipleTable.clearSelection();
					this.bulk_action = 'bulk_action';
				},
				deletePaymentLog(delete_id){
					const vm2 = this
					var payment_log_delete_data = { action:'bookingpress_delete_payment_log', delete_id: delete_id,_wpnonce: '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>'}
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( payment_log_delete_data ) )
					.then(function(response){
						vm2.$notify({
							title: response.data.title,
							message: response.data.msg,
							type: response.data.variant,
							customClass: response.data.variant+'_notification',
							duration:<?php echo intval($bookingpress_notification_duration); ?>,
						});
						vm2.loadPayments()
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
				bulk_actions(){
					const vm = new Vue()
					const vm2 = this
					if(this.bulk_action == "bulk_action")
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
							var delete_payment_logs = {
								action:'bookingpress_bulk_payment_logs_action',
								delete_ids: this.multipleSelection,
								bulk_action: 'delete',
								_wpnonce: '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>'
							}
							axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( delete_payment_logs ) )
							.then(function(response){
								vm2.$notify({
									title: response.data.title,
									message: response.data.msg,
									type: response.data.variant,
									customClass: response.data.variant+'_notification',
									duration:<?php echo intval($bookingpress_notification_duration); ?>,
								});
								vm2.loadPayments()
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
							if(this.multipleSelection.length == 0){
								vm2.$notify({
									title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
									message: '<?php esc_html_e( 'Please select one or more records.', 'bookingpress-appointment-booking' ); ?>',
									type: 'error',
									customClass: 'error_notification',
									duration:<?php echo intval($bookingpress_notification_duration); ?>,
								});
							}else{
								<?php do_action( 'bookingpress_payment_dynamic_bulk_action' ); ?>
							}
						}
					}
				},
				resetFilter(){
					const vm = this
					vm.search_data.search_range = ''
					vm.search_data.search_customer = ''
					vm.search_data.search_emp = ''
					vm.search_data.search_service = ''
					vm.search_data.search_status = ''

					vm.loadPayments()
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
				search_range_change(selected_value)
				{
					this.search_data.search_range[0] = this.get_formatted_date(this.search_data.search_range[0])
					this.search_data.search_range[1] = this.get_formatted_date(this.search_data.search_range[1])
				},
				view_details(view_log_id)
				{
					const vm = this
					vm.view_payment_details_modal = true
					vm.is_display_loader_view = '1'
					vm.view_payment_data = {}
					var fetch_payment_log_details = { action:'bookingpress_fetch_payment_log', log_id: view_log_id,_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' }
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( fetch_payment_log_details, ) )
					.then(function(response){
						vm.is_display_loader_view = '0'
						vm.view_payment_data = response.data
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
				ClosePaymentModal() {
					const vm = this
					vm.view_payment_details_modal = false
				},
				bpa_approve_appointment(payment_log_id){
					const vm = this
					vm.is_display_loader = '1'
					var bpa_post_data = []
					bpa_post_data.action = 'bookingpress_approve_appointment'
					bpa_post_data.approve_id = payment_log_id
					bpa_post_data._wpnonce = '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>'
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( bpa_post_data, ) )
					.then(function(response){
						vm.is_display_loader = '0'
						vm.$notify({
							title: response.data.title,
							message: response.data.msg,
							type: response.data.variant,
							customClass: response.data.variant+'_notification',
							duration:<?php echo intval($bookingpress_notification_duration); ?>,
						});
						vm.loadPayments()
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
			<?php			
			do_action( 'bookingpress_payment_add_dynamic_vue_methods' );
		}


		function bookingpress_get_payments_details() {
			global $wpdb, $tbl_bookingpress_payment_logs, $BookingPress, $tbl_bookingpress_customers, $tbl_bookingpress_appointment_bookings;
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response            = array();
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
				echo json_encode( $response );
				exit;
			}

			$perpage     = isset( $_POST['perpage'] ) ? intval( $_POST['perpage'] ) : 20;
			$currentpage = isset( $_POST['currentpage'] ) ? intval( $_POST['currentpage'] ) : 1;
			$offset      = ( ! empty( $currentpage ) && $currentpage > 1 ) ? ( ( $currentpage - 1 ) * $perpage ) : 0;

			$bookingpress_search_query = ' WHERE 1=1';
			if ( ! empty( $_POST['search_data'] ) ) {
				$search_data = array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['search_data'] );
				if ( ! empty( $search_data['search_range'] ) ) {
					$range_start_date           = date( 'Y-m-d', strtotime( $search_data['search_range'][0] ) ) . ' 00:00:00';
					$range_end_date             = date( 'Y-m-d', strtotime( $search_data['search_range'][1] ) ) . ' 23:59:59';
					$bookingpress_search_query .= " AND (bookingpress_payment_date_time BETWEEN '{$range_start_date}' AND '{$range_end_date}')";
				}

				if ( ! empty( $search_data['search_customer'] ) ) {
					$customer_id                = $search_data['search_customer'];
					$customer_id                = implode( ',', $customer_id );
					$bookingpress_search_query .= " AND (bookingpress_customer_id IN ({$customer_id}))";
				}
				if ( ! empty( $search_data['search_service'] ) ) {
					$service_id                 = $search_data['search_service'];
					$service_id                 = implode( ',', $service_id );
					$bookingpress_search_query .= " AND (bookingpress_service_id IN ({$service_id}))";
				}

				if ( ! empty( $search_data['search_status'] ) && $search_data['search_status'] != 'all' ) {
					$search_status              = $search_data['search_status'];
					$bookingpress_search_query .= " AND (bookingpress_payment_status = '{$search_status}')";
				}

				$bookingpress_search_query = apply_filters( 'bookingpress_payment_add_filter', $bookingpress_search_query,$search_data );				
			}

			$total_payment_logs = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_payment_logs . " {$bookingpress_search_query} ORDER BY bookingpress_payment_log_id DESC", ARRAY_A );
			$get_payment_logs   = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_payment_logs . " {$bookingpress_search_query} ORDER BY bookingpress_payment_log_id DESC LIMIT " . $offset . ',' . $perpage, ARRAY_A );

			$payment_logs_data = array();
			if ( ! empty( $get_payment_logs ) ) {
				$bookingpress_date_format = get_option( 'date_format' );
				foreach ( $get_payment_logs as $payment_log_key => $payment_log_val ) {
					$bookingpress_customer = ! empty( $payment_log_val['bookingpress_customer_firstname'] ) ? esc_html( $payment_log_val['bookingpress_customer_firstname'] . ' ' . $payment_log_val['bookingpress_customer_lastname'] ) : esc_html( $payment_log_val['bookingpress_customer_email'] );

					$service_name = $payment_log_val['bookingpress_service_name'];

					$appointment_date = $payment_log_val['bookingpress_appointment_date'];
					$payment_date     = $payment_log_val['bookingpress_payment_date_time'];
					$payment_gateway  = $payment_log_val['bookingpress_payment_gateway'];

					$currency_name   = $payment_log_val['bookingpress_payment_currency'];
					$currency_symbol = $BookingPress->bookingpress_get_currency_symbol( $currency_name );
					$payment_amount  = $BookingPress->bookingpress_price_formatter_with_currency_symbol( $payment_log_val['bookingpress_payment_amount'], $currency_symbol );

					// get appointment status
					$appointment_ref_id = $payment_log_val['bookingpress_appointment_booking_ref'];
					$appointmentData    = $wpdb->get_row( "SELECT bookingpress_appointment_status FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = {$appointment_ref_id}", ARRAY_A );

					$payment = array(
						'payment_log_id'          => $payment_log_val['bookingpress_payment_log_id'],
						'payment_date'            => date( $bookingpress_date_format, strtotime( $payment_date ) ),
						'payment_customer'        => esc_html( $bookingpress_customer ),						
						'payment_service'         => esc_html( $service_name ),
						'appointment_date'        => date( $bookingpress_date_format, strtotime( $appointment_date ) ),
						'payment_gateway'         => esc_html( $payment_gateway ),
						'payment_numberic_amount' => floatval( $payment_log_val['bookingpress_payment_amount'] ),
						'payment_amount'          => $payment_amount,
						'appointment_status'      => $appointmentData['bookingpress_appointment_status'],
						'payment_status'          => esc_html( $payment_log_val['bookingpress_payment_status'] ),
					);
					$payment = apply_filters( 'bookingpress_payment_add_view_field', $payment,$payment_log_val);
					$payment_logs_data[] = $payment;
				}
			}

			$data['items'] = $payment_logs_data;
			$data['total'] = count( $total_payment_logs );
			wp_send_json( $data );
		}

		function bookingpress_delete_payment_log_func( $delete_id = '' ) {
			global $wpdb, $tbl_bookingpress_payment_logs;
			$response              = array();
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( isset( $_POST['action'] ) && sanitize_text_field( $_POST['action'] ) == 'bookingpress_delete_payment_log' ) {
				if ( ! $bpa_verify_nonce_flag ) {
					$response['variant'] = 'error';
					$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
					$response['msg']     = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
					wp_send_json( $response );
					die();
				}
			}
			$delete_id           = ! empty( $_POST['delete_id'] ) ? intval( $_POST['delete_id'] ) : intval( $delete_id );
			$response['variant'] = 'error';
			$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']     = esc_html__( 'Something Went wrong...', 'bookingpress-appointment-booking' );
			$return              = false;
			if ( ! empty( $delete_id ) ) {
				$wpdb->delete( $tbl_bookingpress_payment_logs, array( 'bookingpress_payment_log_id' => $delete_id ) );
				$response['variant'] = 'success';
				$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Payment Transaction has been deleted successfully.', 'bookingpress-appointment-booking' );
				$return              = true;
			}
			if ( isset( $_POST['action'] ) && sanitize_text_field( $_POST['action'] ) == 'bookingpress_delete_payment_log' ) {
				wp_send_json( $response );
				die();
			}
			return $return;
		}

		function bookingpress_get_search_data() {
			global $wpdb, $tbl_bookingpress_customers, $BookingPress;
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response            = array();
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
				echo json_encode( $response );
				exit();
			}
			$customers_data     = array();
			$staff_members_data = array();
			$services_data      = array();

			$bookingopress_users_data = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_customers} WHERE bookingpress_user_status = 1 ORDER BY bookingpress_customer_id DESC", ARRAY_A );
			foreach ( $bookingopress_users_data as $users_key => $users_val ) {
				if ( $users_val['bookingpress_user_type'] == 1 ) {
					$staff_members_data[] = $users_val;
				} elseif ( $users_val['bookingpress_user_type'] == 2 ) {
					$customers_data[] = $users_val;
				}
			}

			$services_data = $BookingPress->get_bookingpress_service_data_group_with_category();

			$return_data = array(
				'customers'     => $customers_data,
				'staff_members' => $staff_members_data,
				'services'      => $services_data,
			);

			echo json_encode( $return_data );
			exit();
		}

		function bookingpress_payment_log_bulk_action() {
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
				$delete_ids = ! empty( $_POST['delete_ids'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['delete_ids'] ) : array();
				if ( ! empty( $delete_ids ) ) {
					foreach ( $delete_ids as $delete_key => $delete_val ) {
						if ( is_array( $delete_val ) ) {
							$delete_val = $delete_val['payment_log_id'];
						}
						$return = $this->bookingpress_delete_payment_log_func( $delete_val );
						if ( $return ) {
							$response['variant'] = 'success';
							$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
							$response['msg']     = esc_html__( 'Payment Transaction has been deleted successfully.', 'bookingpress-appointment-booking' );
						}
					}
				}
			}
			wp_send_json( $response );
		}

		function bookingpress_fetch_payment_log_data() {
			global $wpdb, $tbl_bookingpress_payment_logs, $tbl_bookingpress_customers, $BookingPress;

			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response            = array();
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
				echo json_encode( $response );
				exit();
			}
			$payment_log_data = array();
			if ( ! empty( $_POST['log_id'] ) ) {
				$log_id           = intval( $_POST['log_id'] );
				$payment_log_data = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_payment_logs} WHERE bookingpress_payment_log_id = {$log_id}", ARRAY_A );

				$payment_log_data['customer_name']     = '';
				$payment_log_data['staff_member_name'] = '';
				if ( ! empty( $payment_log_data ) ) {
					$currency_name                                     = $payment_log_data['bookingpress_payment_currency'];
					$currency_symbol                                   = $BookingPress->bookingpress_get_currency_symbol( $currency_name );
					$payment_amount                                    = $BookingPress->bookingpress_price_formatter_with_currency_symbol( $payment_log_data['bookingpress_payment_amount'], $currency_symbol );
					$payment_log_data['bookingpress_payment_gateway']  = esc_html( $payment_log_data['bookingpress_payment_gateway'] );
					$payment_log_data['bookingpress_payer_email']      = esc_html( $payment_log_data['bookingpress_payer_email'] );
					$payment_log_data['bookingpress_payment_amount']   = $payment_amount;
					$bookingpress_date_format                          = get_option( 'date_format' );
					$payment_log_data['bookingpress_appointment_date'] = date( $bookingpress_date_format, strtotime( $payment_log_data['bookingpress_appointment_date'] ) );

					$customer_id     = $payment_log_data['bookingpress_customer_id'];
					$staff_member_id = ! empty( $payment_log_data['bookingpress_staff_member_id'] ) ? $payment_log_data['bookingpress_staff_member_id'] : 0;

					$customer_data = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_customers} WHERE bookingpress_customer_id = {$customer_id}", ARRAY_A );
					if ( ! empty( $customer_data ) ) {
						$customer_name                     = ! empty( $customer_data['bookingpress_user_firstname'] ) ? esc_html( $customer_data['bookingpress_user_firstname'] . ' ' . $customer_data['bookingpress_user_lastname'] ) : esc_html( $customer_data['bookingpress_user_email'] );
						$payment_log_data['customer_name'] = $customer_name;
					}

					$payment_log_data['staff_member_name'] = '';
					if ( ! empty( $staff_member_id ) ) {
						$staff_member_data = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_customers} WHERE bookingpress_customer_id = {$staff_member_id}", ARRAY_A );
						if ( ! empty( $staff_member_data ) ) {
							$payment_log_data['staff_member_name'] = $staff_member_data['bookingpress_user_firstname'] . ' ' . $staff_member_data['bookingpress_user_lastname'];
						}
					}
				}
			}

			$payment_log_data = apply_filters('bookingpress_modify_modal_payment_log_details', $payment_log_data);

			echo json_encode( $payment_log_data );
			exit();
		}
	}
}

global $bookingpress_payment, $bookingpress_payment_vue_data_fields;
$bookingpress_payment = new bookingpress_payment();

global $bookingpress_global_options;
$bookingpress_options             = $bookingpress_global_options->bookingpress_global_options();
$bookingpress_pagination          = $bookingpress_options['pagination'];
$bookingpress_pagination_arr      = json_decode( $bookingpress_pagination, true );
$bookingpress_pagination_selected = $bookingpress_pagination_arr[0];


$bookingpress_payment_vue_data_fields = array(
	'bulk_action'                => 'bulk_action',
	'bulk_options'               => array(
		array(
			'value' => 'bulk_action',
			'label' => __( 'Bulk Action', 'bookingpress-appointment-booking' ),
		),
		array(
			'value' => 'delete',
			'label' => __( 'Delete', 'bookingpress-appointment-booking' ),
		),
	),
	'loading'                    => false,
	'items'                      => array(),
	'multipleSelection'          => array(),
	'perPage'                    => $bookingpress_pagination_selected,
	'totalItems'                 => 0,
	'pagination_selected_length' => $bookingpress_pagination_selected,
	'pagination_length'          => $bookingpress_pagination,
	'currentPage'                => 1,
	'pagination_length_val'      => '10',
	'pagination_val'             => array(
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
	'search_customer_data'       => array(),
	'search_staffmember_data'    => array(),
	'search_services_data'       => array(),
	'search_status_data'         => array(
		array(
			'text'  => __( 'All', 'bookingpress-appointment-booking' ),
			'value' => 'all',
		),
		array(
			'text'  => __( 'Success', 'bookingpress-appointment-booking' ),
			'value' => 'success',
		),
		array(
			'text'  => __( 'Pending', 'bookingpress-appointment-booking' ),
			'value' => 'pending',
		),
	),
	'search_data'                => array(
		'search_range'    => array( date( 'Y-m-d', strtotime( '-2 Month' ) ), date( 'Y-m-d' ) ),
		'search_customer' => '',
		'search_service'  => '',
		'search_status'   => '',
	),
	'view_payment_details_modal' => false,
	'view_payment_data'          => array(),
	'is_display_loader'          => '0',
	'is_display_loader_view'     => '0',
);
