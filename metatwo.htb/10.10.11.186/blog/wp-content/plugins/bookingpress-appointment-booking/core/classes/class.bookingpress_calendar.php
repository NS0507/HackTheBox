<?php
if ( ! class_exists( 'bookingpress_calendar' ) ) {
	class bookingpress_calendar {
		function __construct() {
			add_action( 'bookingpress_calendar_dynamic_view_load', array( $this, 'bookingpress_dynamic_load_calendar_view_func' ) );
			add_action( 'bookingpress_calendar_dynamic_data_fields', array( $this, 'bookingpress_calendar_dynamic_data_fields_func' ) );
			add_action( 'bookingpress_calendar_dynamic_helper_vars', array( $this, 'bookingpress_calendar_dynamic_helper_vars_func' ) );
			add_action( 'bookingpress_calendar_dynamic_on_load_methods', array( $this, 'bookingpress_calendar_dynamic_on_load_methods_func' ) );
			add_action( 'bookingpress_calendar_dynamic_vue_methods', array( $this, 'bookingpress_calendar_dynamic_vue_methods_func' ) );
			add_action( 'bookingpress_calendar_dynamic_components', array( $this, 'bookingpress_calendar_dynamic_components_func' ) );

			add_action( 'wp_ajax_bookingpress_save_appointment_booking', array( $this, 'bookingpress_save_appointment_booking_func' ), 10 );
			add_action( 'wp_ajax_bookingpress_get_bookings_details', array( $this, 'bookingpress_get_bookings_details_func' ) );
			add_action( 'wp_ajax_bookingpress_get_edit_appointment_data', array( $this, 'bookingpress_get_edit_appointment_data_func' ), 10 );

			add_action( 'wp_ajax_bookingpress_set_appointment_time_slot', array( $this, 'bookingpress_set_appointment_time_slot_func' ), 10 );
		}

		function bookingpress_calendar_dynamic_components_func() {
			?>
				'vue-cal': vuecal
			<?php
		}

		function bookingpress_get_edit_appointment_data_func() {
			global $wpdb,$BookingPress,$tbl_bookingpress_appointment_bookings;
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
			$appointment_data = array();
			if ( ! empty( $_POST['appointment_id'] ) ) {
				$appointment_id                = intval( $_POST['appointment_id'] );
				$appointment_data              = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = {$appointment_id}", ARRAY_A );
				$bookingpress_service_id       = ! empty( $appointment_data['bookingpress_service_id'] ) ? intval( $appointment_data['bookingpress_service_id'] ) : '';
				$bookingpress_appointment_date = ! empty( $appointment_data['bookingpress_appointment_date'] ) ? $appointment_data['bookingpress_appointment_date'] : '';

				if ( ! empty( $appointment_data['bookingpress_appointment_time'] ) ) {
					if ( ! empty( $appointment_data['bookingpress_service_duration_val'] ) && ! empty( $appointment_data['bookingpress_service_duration_unit'] ) ) {
						$service_time_duration      = esc_html( $appointment_data['bookingpress_service_duration_val'] );
						$service_time_duration_unit = esc_html( $appointment_data['bookingpress_service_duration_unit'] );
						if ( $service_time_duration_unit == 'h' ) {
							$service_time_duration = $service_time_duration * 60;
						}
						$service_step_duration_val = $service_time_duration;
						$service_current_time      = $bookingpress_appointment_start_time = date( 'H:i', strtotime( $appointment_data['bookingpress_appointment_time'] ) );
						$service_tmp_time_obj      = new DateTime( $service_current_time );
						$service_tmp_time_obj->add( new DateInterval( 'PT' . $service_step_duration_val . 'M' ) );
						$bookingpress_appointment_end_time                 = $service_tmp_time_obj->format( 'H:i' );
						$appointment_data['bookingpress_appointment_time'] = $bookingpress_appointment_start_time . ' to ' . $bookingpress_appointment_end_time;
					}
				}
				if ( ! empty( $bookingpress_service_id ) && ! empty( $bookingpress_appointment_date ) ) {
					$appointment_time_slot             = $BookingPress->bookingpress_get_service_available_time( $bookingpress_service_id, $bookingpress_appointment_date );
					
					$appointment_data['appointment_time_slot'] = $BookingPress->bookingpress_get_daily_timeslots($appointment_time_slot);
				}
			}
			echo json_encode( $appointment_data );
			exit();
		}

		function bookingpress_get_bookings_details_func() {
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings;
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
			$calendar_week_number = date( 'W', current_time( 'timestamp' ) );
			$calendar_year        = date( 'Y', current_time( 'timestamp' ) );
			$month_details        = $BookingPress->get_monthstart_date_end_date();
			$start_date           = $month_details['start_date'];
			$end_date             = $month_details['end_date'];

			$search_query = '';
			$search_data  = ! empty( $_REQUEST['search_data'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['search_data'] ) : array();
			if ( ! empty( $search_data ) ) {
				$search_selected_services = ! empty( $search_data['selected_services'] ) ? implode( ',', $search_data['selected_services'] ) : '';
				if ( isset( $search_data['selected_services'] ) && $search_selected_services != 0 ) {
					$search_query .= " AND (bookingpress_service_id IN({$search_selected_services}))";
				}

				$search_selected_customer = ! empty( $search_data['selected_customers'] ) ? implode( ',', $search_data['selected_customers'] ) : '';
				if ( ! empty( $search_selected_customer ) ) {
					$search_query .= " AND (bookingpress_customer_id IN ({$search_selected_customer}))";
				}

				$search_appointment_status = ! empty( $search_data['selected_status'] ) ? $search_data['selected_status'] : '';
				if ( ! empty( $search_appointment_status ) && $search_appointment_status != 'all' ) {
					$search_query .= " AND (bookingpress_appointment_status = '{$search_appointment_status}')";
				}
				$search_query = apply_filters('bookingpress_calendar_add_view_filter',$search_query,$search_data);
			}

			$calendar_bookings_data = array();

			// $bookings_data = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date BETWEEN '{$start_date}' AND '{$end_date}' {$search_query} ORDER BY bookingpress_appointment_date ASC, bookingpress_appointment_time ASC", ARRAY_A );
			$bookings_data = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date {$search_query} ORDER BY bookingpress_appointment_date ASC, bookingpress_appointment_time ASC", ARRAY_A );

			foreach ( $bookings_data as $bookings_key => $bookings_val ) {
				$bookingpress_booking_date = date( 'Y-m-d', strtotime( $bookings_val['bookingpress_appointment_date'] ) );

				$service_duration_vals = $BookingPress->bookingpress_get_service_end_time( $bookings_val['bookingpress_service_id'], $bookings_val['bookingpress_appointment_time'], $bookings_val['bookingpress_service_duration_val'], $bookings_val['bookingpress_service_duration_unit'] );
				$service_start_time    = ! empty( $service_duration_vals['service_start_time'] ) ? $service_duration_vals['service_start_time'] : $bookingpress_appointment_booked_time;
				$service_end_time      = ! empty( $service_duration_vals['service_end_time'] ) ? $service_duration_vals['service_end_time'] : '';

				$bookingpress_appointment_status = $bookings_val['bookingpress_appointment_status'];
				$bookingpress_appointment_class  = 'bpa-cal-event-card';
				if ( $bookingpress_appointment_status == 'Approved' ) {
					$bookingpress_appointment_class .= ' bpa-cal-event-card--approved';
				} elseif ( $bookingpress_appointment_status == 'Pending' ) {
					$bookingpress_appointment_class .= ' bpa-cal-event-card--pending';
				} elseif ( $bookingpress_appointment_status == 'Cancelled' ) {
					$bookingpress_appointment_class .= ' bpa-cal-event-card--cancelled';
				} elseif ( $bookingpress_appointment_status == 'Rejected' ) {
					$bookingpress_appointment_class .= ' bpa-cal-event-card--cancelled';
				}

				$calendar_bookings_data[] = array(
					'start'          => $bookingpress_booking_date . ' ' . $service_start_time,
					'end'            => $bookingpress_booking_date . ' ' . $service_end_time,
					'title'          => esc_html( $bookings_val['bookingpress_service_name'] ),
					'class'          => $bookingpress_appointment_class,
					'appointment_id' => intval( $bookings_val['bookingpress_appointment_booking_id'] ),
					'service_id'     => intval( $bookings_val['bookingpress_service_id'] ),
					'is_cancelled'   => ( $bookingpress_appointment_status == 'Cancelled' || $bookingpress_appointment_status == 'Rejected' ) ? 1 : 0,
				);
			}

			echo json_encode( $calendar_bookings_data );
			exit();
		}

		function bookingpress_save_appointment_booking_func() {
			global $wpdb, $BookingPress, $tbl_bookingpress_entries, $bookingpress_payment_gateways, $tbl_bookingpress_payment_logs, $tbl_bookingpress_appointment_bookings, $bookingpress_email_notifications,$bookingpress_debug_payment_log_id;
			$response              = array();
			$wpnonce               = isset( $_REQUEST['appointment_data']['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['appointment_data']['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response            = array();
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
				wp_send_json( $response );
				die();
			}
			$response['variant'] = 'error';
			$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']     = esc_html__( 'Something Went wrong...', 'bookingpress-appointment-booking' );

			if ( ! empty( $_REQUEST ) && ! empty( $_REQUEST['appointment_data'] ) ) {
				$bookingpress_appointment_data                 = $_REQUEST['appointment_data'];
				$bookingpress_appointment_selected_customer    = ! empty( $bookingpress_appointment_data['appointment_selected_customer'] ) ? sanitize_text_field( $bookingpress_appointment_data['appointment_selected_customer'] ) : '';
				$bookingpress_appointment_selected_services    = ! empty( $bookingpress_appointment_data['appointment_selected_service'] ) ? sanitize_text_field( $bookingpress_appointment_data['appointment_selected_service'] ) : '';
				$bookingpress_appointment_booked_date          = ! empty( $bookingpress_appointment_data['appointment_booked_date'] ) ? sanitize_text_field( $bookingpress_appointment_data['appointment_booked_date'] ) : '';
				$bookingpress_appointment_booked_time          = ! empty( $bookingpress_appointment_data['appointment_booked_time'] ) ? sanitize_text_field( $bookingpress_appointment_data['appointment_booked_time'] ) : '';
				$bookingpress_appointment_internal_note        = ! empty( $bookingpress_appointment_data['appointment_internal_note'] ) ? trim( sanitize_textarea_field( $bookingpress_appointment_data['appointment_internal_note'] ) ) : '';
				$bookingpress_appointment_is_send_notification = ( sanitize_text_field( $bookingpress_appointment_data['appointment_send_notification'] ) == 'true' ) ? 1 : 0;
				$bookingpress_appointment_status               = ! empty( $bookingpress_appointment_data['appointment_status'] ) ? sanitize_text_field( $bookingpress_appointment_data['appointment_status'] ) : 'Approved';

				$bookingpress_update_id = ! empty( $bookingpress_appointment_data['appointment_update_id'] ) ? $bookingpress_appointment_data['appointment_update_id'] : '';
				if ( ! empty( $bookingpress_appointment_selected_customer ) && ! empty( $bookingpress_appointment_selected_services ) && ! empty( $bookingpress_appointment_booked_date ) && ! empty( $bookingpress_appointment_booked_time ) ) {
					$customer_data     = $BookingPress->get_customer_details( $bookingpress_appointment_selected_customer );
					$customer_username = ! empty( $customer_data['bookingpress_user_login'] ) ? esc_html( $customer_data['bookingpress_user_login'] ) : '';
					$customer_phone    = ! empty( $customer_data['bookingpress_user_phone'] ) ? esc_html( $customer_data['bookingpress_user_phone'] ) : '';

					$customer_country = ! empty( $customer_data['bookingpress_user_country_phone'] ) ? esc_html( $customer_data['bookingpress_user_country_phone'] ) : '';
					$customer_email   = ! empty( $customer_data['bookingpress_user_email'] ) ? esc_html( $customer_data['bookingpress_user_email'] ) : '';

					$service_data               = $BookingPress->get_service_by_id( $bookingpress_appointment_selected_services );
					$service_name               = ! empty( $service_data['bookingpress_service_name'] ) ? esc_html( $service_data['bookingpress_service_name'] ) : '';
					$service_amount             = ! empty( $service_data['bookingpress_service_price'] ) ? (float) $service_data['bookingpress_service_price'] : 0;
					$service_duration_val       = ! empty( $service_data['bookingpress_service_duration_val'] ) ? esc_html( $service_data['bookingpress_service_duration_val'] ) : '';
					$service_duration_unit      = ! empty( $service_data['bookingpress_service_duration_unit'] ) ? esc_html( $service_data['bookingpress_service_duration_unit'] ) : '';
					$bookingpress_currency_name = $BookingPress->bookingpress_get_settings( 'payment_default_currency', 'payment_setting' );

					if ( ! empty( $bookingpress_update_id ) ) {

						if($bookingpress_appointment_status == "Approved" || $bookingpress_appointment_status == "Pending"){
							$bookingpress_appointment_start_time = explode('to', $bookingpress_appointment_booked_time);
							if(!empty($bookingpress_appointment_start_time[0])){
								$bookingpress_appointment_start_time = trim($bookingpress_appointment_start_time[0]).":00";
							}
							
							$is_appointment_already_booked = $wpdb->get_var("SELECT * FROM ".$tbl_bookingpress_appointment_bookings." WHERE bookingpress_appointment_booking_id != {$bookingpress_update_id} AND (bookingpress_appointment_status = 'Pending' OR bookingpress_appointment_status = 'Approved') AND bookingpress_appointment_date = '{$bookingpress_appointment_booked_date}' AND bookingpress_appointment_time = '{$bookingpress_appointment_start_time}'");

							if($is_appointment_already_booked > 0){
								$response['variant'] = 'error';
								$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
								$response['msg']     = esc_html__( 'Appointment already booked for this slot', 'bookingpress-appointment-booking' );
								echo json_encode($response);
								exit();
							}
						}

						// get existing appointment data
						$appointment_details = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = {$bookingpress_update_id}", ARRAY_A );
						if ( ! empty( $appointment_details ) ) {
							$appointment_details['bookingpress_customer_id']                   = $bookingpress_appointment_selected_customer;
							$appointment_details['bookingpress_service_id']                    = $bookingpress_appointment_selected_services;
							$appointment_details['bookingpress_service_name']                  = $service_name;
							$appointment_details['bookingpress_service_price']                 = $service_amount;
							$appointment_details['bookingpress_service_currency']              = $bookingpress_currency_name;
							$appointment_details['bookingpress_service_duration_val']          = $service_duration_val;
							$appointment_details['bookingpress_service_duration_unit']         = $service_duration_unit;
							$appointment_details['bookingpress_appointment_date']              = $bookingpress_appointment_booked_date;
							$appointment_details['bookingpress_appointment_time']              = $bookingpress_appointment_booked_time;
							$appointment_details['bookingpress_appointment_internal_note']     = $bookingpress_appointment_internal_note;
							$appointment_details['bookingpress_appointment_send_notification'] = $bookingpress_appointment_is_send_notification;
							$appointment_details['bookingpress_appointment_status']            = $bookingpress_appointment_status;

							$wpdb->update( $tbl_bookingpress_appointment_bookings, $appointment_details, array( 'bookingpress_appointment_booking_id' => $bookingpress_update_id ) );

							if ( $bookingpress_appointment_is_send_notification ) {
								if ( $bookingpress_appointment_status == 'Rejected' ) {
									$bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification( 'Appointment Rejected', $bookingpress_update_id, $customer_email );
								} else if($bookingpress_appointment_status == "Approved"){
									$bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification( 'Appointment Approved', $bookingpress_update_id, $customer_email );
								} else if($bookingpress_appointment_status == "Pending"){
									$bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification( 'Appointment Pending', $bookingpress_update_id, $customer_email );
								} else if($bookingpress_appointment_status == "Cancelled"){
									$bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification( 'Appointment Canceled', $bookingpress_update_id, $customer_email );
								}
							}

							$response['variant'] = 'success';
							$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
							$response['msg']     = esc_html__( 'Appointment has been updated successfully.', 'bookingpress-appointment-booking' );
						}
					} else {
						$bookingpress_entry_details = array(
							'bookingpress_customer_name'   => $customer_username,
							'bookingpress_customer_phone'  => $customer_phone,
							'bookingpress_customer_country' => $customer_country,
							'bookingpress_customer_email'  => $customer_email,
							'bookingpress_service_id'      => $bookingpress_appointment_selected_services,
							'bookingpress_service_name'    => $service_name,
							'bookingpress_service_price'   => $service_amount,
							'bookingpress_service_currency' => $bookingpress_currency_name,
							'bookingpress_service_duration_val' => $service_duration_val,
							'bookingpress_service_duration_unit' => $service_duration_unit,
							'bookingpress_payment_gateway' => 'on-site',
							'bookingpress_appointment_date' => $bookingpress_appointment_booked_date,
							'bookingpress_appointment_time' => $bookingpress_appointment_booked_time,
							'bookingpress_appointment_internal_note' => $bookingpress_appointment_internal_note,
							'bookingpress_appointment_send_notifications' => $bookingpress_appointment_is_send_notification,
							'bookingpress_appointment_status' => $bookingpress_appointment_status,
							'bookingpress_created_at'      => current_time( 'mysql' ),
						);

						do_action( 'bookingpress_payment_log_entry', 'on-site', 'submit appointment form backend', 'bookingpress', $bookingpress_entry_details, $bookingpress_debug_payment_log_id );

						$wpdb->insert( $tbl_bookingpress_entries, $bookingpress_entry_details );
						$entry_id       = $wpdb->insert_id;
						$payment_log_id = 0;
						if ( ! empty( $entry_id ) ) {
							$payment_log_id = $bookingpress_payment_gateways->bookingpress_confirm_booking( $entry_id, array(), 'success' );
						}

						if ( ! empty( $payment_log_id ) ) {
							$response['variant'] = 'success';
							$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
							$response['msg']     = esc_html__( 'Appointment has been booked successfully.', 'bookingpress-appointment-booking' );
						}
					}
				} else {
					$response['msg'] = esc_html__( 'Please fill all required values', 'bookingpress-appointment-booking' );
				}
			}

			echo json_encode( $response );
			exit();
		}
		function bookingpress_set_appointment_time_slot_func() {
			global $wpdb,$tbl_bookingpress_services, $BookingPress;
			if ( isset( $_REQUEST['action'] ) && sanitize_text_field( $_REQUEST['action'] ) == 'bookingpress_set_appointment_time_slot' ) {
				$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
				$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
				if ( ! $bpa_verify_nonce_flag ) {
					$response            = array();
					$response['variant'] = 'error';
					$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
					$response['msg']     = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
					wp_send_json( $response );
					die();
				}
			}
			$bookingpress_service_id    = isset( $_REQUEST['service_id'] ) ? intval( $_REQUEST['service_id'] ) : '';
			$bookingpress_selected_date = isset( $_REQUEST['selected_date'] ) ? sanitize_text_field( $_REQUEST['selected_date'] ) : '';

			if ( ! empty( $bookingpress_service_id ) && ! empty( $bookingpress_selected_date ) ) {

				$appointment_time_slot             = $BookingPress->bookingpress_get_service_available_time( $bookingpress_service_id, $bookingpress_selected_date );
				$bookingpress_service_slot_details = $BookingPress->bookingpress_get_daily_timeslots($appointment_time_slot);				
				echo json_encode( $bookingpress_service_slot_details );
				exit;
			}
		}

		function bookingpress_dynamic_load_calendar_view_func() {
			$bookingpress_load_file_name = BOOKINGPRESS_VIEWS_DIR . '/calendar/manage_calendar.php';
			$bookingpress_load_file_name = apply_filters( 'bookingpress_modify_calendar_view_file_path', $bookingpress_load_file_name );

			require $bookingpress_load_file_name;
		}

		function bookingpress_calendar_dynamic_data_fields_func() {
			global $wpdb, $BookingPress, $bookingpress_calendar_vue_data_fields, $tbl_bookingpress_customers, $tbl_bookingpress_categories, $tbl_bookingpress_services;

			// Fetch customers details
			$bookingpress_customer_details           = $wpdb->get_results( 'SELECT bookingpress_customer_id,bookingpress_user_firstname,bookingpress_user_lastname,bookingpress_user_email FROM ' . $tbl_bookingpress_customers . ' WHERE bookingpress_user_type = 2 AND bookingpress_user_status = 1', ARRAY_A );
			$bookingpress_customer_selection_details = array();
			foreach ( $bookingpress_customer_details as $bookingpress_customer_key => $bookingpress_customer_val ) {

				$bookingpress_customer_name = ( $bookingpress_customer_val['bookingpress_user_firstname'] == '' && $bookingpress_customer_val['bookingpress_user_lastname'] == '' ) ? $bookingpress_customer_val['bookingpress_user_email'] : $bookingpress_customer_val['bookingpress_user_firstname'] . ' ' . $bookingpress_customer_val['bookingpress_user_lastname'];

				$bookingpress_customer_selection_details[] = array(
					'text'  => $bookingpress_customer_name,
					'value' => $bookingpress_customer_val['bookingpress_customer_id'],
				);
			}
			$bookingpress_calendar_vue_data_fields['appointment_customers_list'] = $bookingpress_customer_selection_details;
			$bookingpress_calendar_vue_data_fields['search_customer_list']       = $bookingpress_customer_selection_details;

			// Fetch Services Details
			$bookingpress_services_details2                                     = array();
			$bookingpress_services_details2[]                                   = array(
				'category_name'     => '',
				'category_services' => array(
					'0' => array(
						'service_id'    => 0,
						'service_name'  => __( 'Select Services', 'bookingpress-appointment-booking' ),
						'service_price' => '',
					),
				),
			);
			$bookingpress_services_details                                      = $BookingPress->get_bookingpress_service_data_group_with_category();
			$bookingpress_calendar_vue_data_fields['appointment_services_data'] = $bookingpress_services_details;

			$bookingpress_services_details                                      = array_merge( $bookingpress_services_details2, $bookingpress_services_details );
			$bookingpress_calendar_vue_data_fields['appointment_services_list'] = $bookingpress_services_details;
			$bpa_nonce = wp_create_nonce( 'bpa_wp_nonce' );
			$bookingpress_calendar_vue_data_fields['appointment_formdata']['_wpnonce'] = $bpa_nonce;

			$bookingpress_default_status_option = $BookingPress->bookingpress_get_settings( 'appointment_status', 'general_setting' );
			$bookingpress_calendar_vue_data_fields['appointment_formdata']['appointment_status'] = ! empty( $bookingpress_default_status_option ) ? $bookingpress_default_status_option : 'Approved';
			$default_daysoff_details = $BookingPress->bookingpress_get_default_dayoff_dates();
			if ( ! empty( $default_daysoff_details ) ) {
				$default_daysoff_details                                = array_map(
					function( $date ) {
						return date( 'Y-m-d', strtotime( $date ) );
					},
					$default_daysoff_details
				);
				$bookingpress_calendar_vue_data_fields['disabledDates'] = $default_daysoff_details;
			} else {
				$bookingpress_calendar_vue_data_fields['disabledDates'] = '';
			}
			$bookingpress_calendar_vue_data_fields = apply_filters( 'bookingpress_modify_calendar_data_fields', $bookingpress_calendar_vue_data_fields );
			echo json_encode( $bookingpress_calendar_vue_data_fields );
		}

		function bookingpress_calendar_dynamic_helper_vars_func() {
			global $bookingpress_global_options;
			$bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_locale_lang = $bookingpress_options['locale'];
			?>
			var lang = ELEMENT.lang.<?php echo esc_html( $bookingpress_locale_lang ); ?>;
			ELEMENT.locale(lang)
			<?php
			do_action( 'bookingpress_add_calendar_dynamic_helper_vars' );
		}


		function bookingpress_calendar_dynamic_on_load_methods_func() {
			?>
			const vm = this;
			vm.loadCalendar()
			<?php
		}

		function bookingpress_calendar_dynamic_vue_methods_func() {
			global $BookingPress,$bookingpress_notification_duration;
			$bookingpress_current_date          = date( 'Y-m-d', current_time( 'timestamp' ) );
			$bookingpress_default_status_option = $BookingPress->bookingpress_get_settings( 'appointment_status', 'general_setting' );
			$bookingpress_default_status_option = ! empty( $bookingpress_default_status_option ) ? $bookingpress_default_status_option : 'Approved';
			?>
				loadCalendar(){
					const vm = this
					vm.resetForm()
					var events_details = []
					var postData = { action:'bookingpress_get_bookings_details', search_data: vm.search_data,_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' };
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						vm.calendar_events_data = response.data
					}.bind(this) )
					.catch( function (error) {
						console.log(error);
					});
				},
				openAppointmentBookingModal(){
					const vm = this
					vm.open_calendar_appointment_modal = true
				},
				closeAppointmentBookingModal(){
					const vm = this
					vm.open_calendar_appointment_modal = false
					vm.$refs['appointment_formdata'].resetFields()
					vm.resetForm()
				},
				saveAppointmentBooking(bookingAppointment){
					const vm = new Vue()
					const vm2 = this				
					this.$refs[bookingAppointment].validate((valid) => {
						if (valid) {									
							vm2.is_disabled = true
							vm2.is_display_save_loader = '1'
							var postData = { action:'bookingpress_save_appointment_booking', appointment_data: vm2.appointment_formdata };
							axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
							.then( function (response) {										
								vm2.is_disabled = false
								vm2.is_display_save_loader = '0'
								vm2.open_calendar_appointment_modal = false
								vm2.$notify({
									title: response.data.title,
									message: response.data.msg,
									type: response.data.variant,
									customClass: response.data.variant+'_notification',																		
									duration:<?php echo intval($bookingpress_notification_duration); ?>,
								});
								vm2.closeAppointmentBookingModal()
								vm2.loadCalendar()								
							}.bind(this) )
							.catch( function (error) {
								console.log(error);
							});
						}
					});
				},
				formatted_date(selected_date){
					const vm2 = this
					vm2.appointment_formdata.appointment_booked_date = vm2.get_formatted_date(selected_date)
					vm2.bookingpress_set_time_slot()
				},
				resetForm(){
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
				editEvent(event, e){
					const vm = this
					var appointment_id = event.appointment_id
					var service_id = event.service_id
					vm.appointment_formdata.appointment_update_id = appointment_id
					vm.openAppointmentBookingModal()
					var postData = { action:'bookingpress_get_edit_appointment_data', appointment_id: appointment_id,_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' };
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						if(response.data != undefined || response.data != [])
						{
							vm.appointment_formdata.appointment_selected_customer = response.data.bookingpress_customer_id
							vm.appointment_formdata.appointment_selected_service = response.data.bookingpress_service_id
							vm.appointment_formdata.appointment_booked_date = response.data.bookingpress_appointment_date
							vm.appointment_formdata.appointment_booked_time = response.data.bookingpress_appointment_time
							vm.appointment_formdata.appointment_internal_note = response.data.bookingpress_appointment_internal_note
							vm.appointment_time_slot = response.data.appointment_time_slot
							if(response.data.bookingpress_appointment_send_notification == '0'){
								vm.appointment_formdata.appointment_send_notification = false
							}else{
								vm.appointment_formdata.appointment_send_notification = true
							}
							vm.appointment_formdata.appointment_status = response.data.bookingpress_appointment_status
						}
					}.bind(this) )
					.catch( function (error) {
						console.log(error);
					});
				},
				resetFilter(){
					const vm = this
					vm.search_data.selected_services = []
					vm.search_data.selected_customers = []
					vm.search_data.selected_status = ''
					vm.loadCalendar()
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
			<?php
		}
	}
}

global $bookingpress_calendar, $bookingpress_calendar_vue_data_fields;
$bookingpress_calendar = new bookingpress_calendar();

$bookingpress_appointment_status_array = array(
	array(
		'text'  => 'All',
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

$bookingpress_calendar_vue_data_fields = array(
	'bulk_action'                     => 'bulk_action',
	'calendar_val'                    => '',
	'appointment_customers_list'      => array(),
	'appointment_staff_members_list'  => array(),
	'appointment_services_data'       => array(),
	'appointment_services_list'       => array(),
	'appointment_time_slot'           => array(),
	'appointment_status'              => array(
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
	'rules'                           => array(
		'appointment_selected_customer' => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please select customer', 'bookingpress-appointment-booking' ),
				'trigger'  => 'change',
			),
		),
		'appointment_selected_service'  => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please select service', 'bookingpress-appointment-booking' ),
				'trigger'  => 'change',
			),
		),
		'appointment_booked_date'       => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please select booking date', 'bookingpress-appointment-booking' ),
				'trigger'  => 'change',
			),
		),
		'appointment_booked_time'       => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please select booking time', 'bookingpress-appointment-booking' ),
				'trigger'  => 'change',
			),
		),
	),
	'appointment_formdata'            => array(
		'appointment_selected_customer'     => '',
		'appointment_selected_staff_member' => '',
		'appointment_selected_service'      => '',
		'appointment_booked_date'           => date( 'Y-m-d', current_time( 'timestamp' ) ),
		'appointment_booked_time'           => '',
		'appointment_internal_note'         => '',
		'appointment_send_notification'     => false,
		'appointment_status'                => 'Approved',
		'appointment_update_id'             => 0,
		'_wpnonce'                          => '',
	),
	'open_calendar_appointment_modal' => false,
	'calendar_events_data'            => array(),
	'calendar_current_date'           => date( 'Y-m-d', current_time( 'timestamp' ) ),
	'show_all_day_events'             => true,
	'search_customer_list'            => '',
	'search_status'                   => $bookingpress_appointment_status_array,
	'search_data'                     => array(
		'selected_services'  => array(),
		'selected_customers' => array(),
		'selected_status'    => '',
	),
	'activeView'                      => 'month',
	'minEventWidth'                   => 0,
	'is_display_save_loader'          => '0',
	'is_disabled'                     => false,
);
