<?php

if ( ! class_exists( 'bookingpress_dashboard' ) ) {
	class bookingpress_dashboard {
		function __construct() {
			add_action( 'bookingpress_dashboard_dynamic_view_load', array( $this, 'bookingpress_dynamic_load_dashboard_view_func' ) );
			add_action( 'bookingpress_dashboard_dynamic_data_fields', array( $this, 'bookingpress_dashboard_dynamic_data_fields_func' ) );
			add_action( 'bookingpress_dashboard_dynamic_helper_vars', array( $this, 'bookingpress_dashboard_dynamic_helper_vars_func' ) );
			add_action( 'bookingpress_dashboard_dynamic_on_load_methods', array( $this, 'bookingpress_dashboard_dynamic_on_load_methods_func' ) );
			add_action( 'bookingpress_dashboard_dynamic_vue_methods', array( $this, 'bookingpress_dashboard_dynamic_vue_methods_func' ) );

			add_action( 'wp_ajax_bookingpress_get_dashboard_upcoming_appointments', array( $this, 'bookingpress_dashboard_upcoming_appointments_func' ) );
			add_action( 'wp_ajax_bookingpress_get_dashboard_summary', array( $this, 'bookingpress_dashboard_summary_func' ), 10 );
			add_action( 'wp_ajax_bookingpress_get_charts_data', array( $this, 'get_chart_data' ) );
			add_action( 'wp_ajax_bookingpress_change_upcoming_appointment_status', array( $this, 'bookingpress_change_upcoming_appointment_status' ) );
		}

		function bookingpress_change_upcoming_appointment_status($update_appointment_id = '' ,$appointment_new_status = '' ) {
			global $wpdb, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs, $bookingpress_email_notifications;
			$appointment_update_id = !empty( $_REQUEST['update_appointment_id']) ? intval($_REQUEST['update_appointment_id']) : $update_appointment_id; 
			$appointment_new_status = ! empty( $_REQUEST['appointment_new_status']) ? sanitize_text_field( $_REQUEST['appointment_new_status']) : $appointment_new_status;

			if ( ! empty( $appointment_update_id ) && ! empty( $appointment_new_status ) ) {

 				$booked_appointment_details = $wpdb->get_row("SELECT * FROM ".$tbl_bookingpress_appointment_bookings." WHERE bookingpress_appointment_booking_id = {$appointment_update_id}", ARRAY_A);

 				$bookingpress_appointment_date = $booked_appointment_details['bookingpress_appointment_date'];
 				$bookingpress_appointment_start_time = $booked_appointment_details['bookingpress_appointment_time'];

 				$is_appointment_already_booked = 0;

 				if($appointment_new_status == "Approved" || $appointment_new_status == "Pending"){
 					$is_appointment_already_booked = $wpdb->get_var("SELECT * FROM ".$tbl_bookingpress_appointment_bookings." WHERE bookingpress_appointment_booking_id != {$appointment_update_id} AND (bookingpress_appointment_status = 'Pending' OR bookingpress_appointment_status = 'Approved') AND bookingpress_appointment_date = '{$bookingpress_appointment_date}' AND bookingpress_appointment_time = '{$bookingpress_appointment_start_time}'");
 				}

 				if($is_appointment_already_booked > 0){
 					echo 0;
 					exit();
 				}else{
					$appointment_update_data = array(
						'bookingpress_appointment_status' => $appointment_new_status,
					);

					$appointment_where_condition = array(
						'bookingpress_appointment_booking_id' => $appointment_update_id,
					);

					$wpdb->update( $tbl_bookingpress_appointment_bookings, $appointment_update_data, $appointment_where_condition );

					if ( $appointment_new_status == 'Approved' ) {
						$wpdb->update( $tbl_bookingpress_payment_logs, array( 'bookingpress_payment_status' => $appointment_new_status ), array( 'bookingpress_appointment_booking_ref' => $appointment_update_id ) );										
					}
					echo 1;
 					exit();
				}
			}
			if ( isset( $_POST['action'] ) && sanitize_text_field( $_POST['action'] ) != 'bookingpress_change_upcoming_appointment_status' ) {
				echo 0;
				exit();
			}
			echo 0;
			exit();
		}

		function bookingpress_dashboard_summary_func() {
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_customers;
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
			$return_data = array(
				'total_appointments'    => 0,
				'approved_appointments' => 0,
				'pending_appointments'  => 0,
				'total_revenue'         => 0,
				'total_customers'       => 0,
			);

			$appointments_search_query = '1=1';
			$selected_filter_val       = ! empty( $_POST['selected_filter'] ) ? sanitize_text_field( $_POST['selected_filter'] ) : 'week';
			if ( $selected_filter_val == 'today' ) {
				$selected_filter_val        = date( 'Y-m-d', current_time( 'timestamp' ) );
				$appointments_search_query .= " AND (bookingpress_appointment_date = '" . $selected_filter_val . "')";
			} elseif ( $selected_filter_val == 'yesterday' ) {
				$search_filter_val          = date( 'Y-m-d', strtotime( '-1 days', current_time( 'timestamp' ) ) );
				$appointments_search_query .= " AND (bookingpress_appointment_date = '" . $search_filter_val . "')";
			} elseif ( $selected_filter_val == 'tomorrow' ) {
				$search_filter_val          = date( 'Y-m-d', strtotime( '+1 days', current_time( 'timestamp' ) ) );
				$appointments_search_query .= " AND (bookingpress_appointment_date = '" . $search_filter_val . "')";
			} elseif ( $selected_filter_val == 'week' ) {
				$week_number  = date( 'W' );
				$current_year = date( 'Y' );
				$week_dates   = $BookingPress->get_weekstart_date_end_date( $week_number, $current_year );
				$week_start   = $week_dates['week_start'];
				$week_end     = $week_dates['week_end'];

				$appointments_search_query .= " AND (bookingpress_appointment_date BETWEEN '" . $week_start . "' AND '" . $week_end . "')";
			} elseif ( $selected_filter_val == 'last_week' ) {
				$week_number  = date( 'W' ) - 1;
				$current_year = date( 'Y' );
				$week_dates   = $BookingPress->get_weekstart_date_end_date( $week_number, $current_year );
				$week_start   = $week_dates['week_start'];
				$week_end     = $week_dates['week_end'];

				$appointments_search_query .= " AND (bookingpress_appointment_date BETWEEN '" . $week_start . "' AND '" . $week_end . "')";
			} elseif ( $selected_filter_val == 'monthly' ) {
				$monthly_dates = $BookingPress->get_monthstart_date_end_date();
				$month_start   = $monthly_dates['start_date'];
				$month_end     = $monthly_dates['end_date'];

				$appointments_search_query .= " AND (bookingpress_appointment_date BETWEEN '" . $month_start . "' AND '" . $month_end . "')";
			} elseif ( $selected_filter_val == 'yearly' ) {
				$year_start_date            = date( 'Y-m-d', strtotime( '01/01' ) );
				$year_end_date              = date( 'Y-m-d', strtotime( '12/31' ) );
				$appointments_search_query .= " AND (bookingpress_appointment_date BETWEEN '" . $year_start_date . "' AND '" . $year_end_date . "')";
			} elseif ( $selected_filter_val == 'custom' ) {
				$customer_filter_start_val  = ! empty( $_POST['custom_filter_val'][0] ) ? sanitize_text_field( $_POST['custom_filter_val'][0] ) : date( 'Y-m-d' );
				$customer_filter_end_val    = ! empty( $_POST['custom_filter_val'][1] ) ? sanitize_text_field( $_POST['custom_filter_val'][1] ) : date( 'Y-m-d' );
				$appointments_search_query .= " AND (bookingpress_appointment_date BETWEEN '" . $customer_filter_start_val . "' AND '" . $customer_filter_end_val . "')";
			}

			$total_appointments                = $wpdb->get_var( "SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE {$appointments_search_query} " );
			$return_data['total_appointments'] = $total_appointments;

			$approved_appointments                = $wpdb->get_var( "SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = 'Approved' AND {$appointments_search_query}" );
			$return_data['approved_appointments'] = $approved_appointments;

			$pending_appointments                = $wpdb->get_var( "SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = 'Pending' AND {$appointments_search_query}" );
			$return_data['pending_appointments'] = $pending_appointments;

			$total_revenue = $wpdb->get_var( "SELECT SUM(bookingpress_service_price) FROM {$tbl_bookingpress_appointment_bookings} WHERE {$appointments_search_query}" );
			if ( ! empty( $total_revenue ) ) {
				$return_data['total_revenue'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol( $total_revenue );
			}

			$total_customers                = $wpdb->get_var( "SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE {$appointments_search_query} " );
			$return_data['total_customers'] = $total_customers;

			$return_data = apply_filters('bookingpress_update_summary_data',$return_data);
			echo json_encode( $return_data );
			exit();
		}

		function bookingpress_dashboard_upcoming_appointments_func() {
			global $wpdb, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs, $BookingPress, $tbl_bookingpress_customers, $bookingpress_global_options;
			$return_data = array(
				'upcoming_appointments' => array(),
			);

			$bookingpress_global_details = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_date_format = $bookingpress_global_details['wp_default_date_format'] . '  ' . $bookingpress_global_details['wp_default_time_format'];

			$upcoming_appointments = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date > NOW() ORDER BY bookingpress_appointment_date ASC LIMIT 0, 10", ARRAY_A );

			$appointments = array();
			if ( ! empty( $upcoming_appointments ) ) {
				$counter = 1;
				foreach ( $upcoming_appointments as $get_appointment ) {
					$appointment                   = array();
					$appointment['id']             = $counter;
					$appointment_id                = intval( $get_appointment['bookingpress_appointment_booking_id'] );
					$appointment['appointment_id'] = intval( $get_appointment['bookingpress_appointment_booking_id'] );
					$payment_log                   = $wpdb->get_row( 'SELECT bookingpress_customer_firstname,bookingpress_customer_lastname, bookingpress_customer_email FROM ' . $tbl_bookingpress_payment_logs . ' WHERE bookingpress_appointment_booking_ref =' . $appointment_id, ARRAY_A );
					$booked_appointment_datetime   = esc_html( $get_appointment['bookingpress_appointment_date'] ) . ' ' . esc_html( $get_appointment['bookingpress_appointment_time'] );
					$appointment['payment_date']   = date( $bookingpress_date_format, strtotime( $booked_appointment_datetime ) );

					$customer_name                = ! empty( $payment_log['bookingpress_customer_firstname'] ) ? esc_html( $payment_log['bookingpress_customer_firstname'] . ' ' . $payment_log['bookingpress_customer_lastname'] ) : esc_html( $payment_log['bookingpress_customer_email'] );
					$appointment['customer_name'] = $customer_name;

					$appointment['service_name'] = esc_html( $get_appointment['bookingpress_service_name'] );
					$service_duration            = esc_html( $get_appointment['bookingpress_service_duration_val'] );
					$service_duration_unit       = esc_html( $get_appointment['bookingpress_service_duration_unit'] );
					if ( $service_duration_unit == 'm' ) {
						$service_duration .= ' ' . esc_html__( 'Mins', 'bookingpress-appointment-booking' );
					} else {
						$service_duration .= ' ' . esc_html__( 'Hours', 'bookingpress-appointment-booking' );
					}
					$appointment['appointment_duration'] = $service_duration;
					$currency_name                       = $get_appointment['bookingpress_service_currency'];
					$currency_symbol                     = $BookingPress->bookingpress_get_currency_symbol( $currency_name );
					$appointment['appointment_payment']  = $BookingPress->bookingpress_price_formatter_with_currency_symbol( $get_appointment['bookingpress_service_price'], $currency_symbol );

					$appointment['appointment_status'] = esc_html( $get_appointment['bookingpress_appointment_status'] );
					$appointments[]                    = $appointment;
					$counter++;
				}
			}

			$return_data['upcoming_appointments'] = $appointments;

			echo json_encode( $return_data );
			exit();
		}


		function bookingpress_dynamic_load_dashboard_view_func() {
			$bookingpress_load_file_name = BOOKINGPRESS_VIEWS_DIR . '/dashboard/manage_dashboard.php';
			$bookingpress_load_file_name = apply_filters( 'bookingpress_modify_dashboard_view_file_path', $bookingpress_load_file_name );

			require $bookingpress_load_file_name;
		}

		function get_chart_data() {
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_customers;
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
			$selected_filter_val       = isset( $_POST['selected_filter'] ) ? sanitize_text_field( $_POST['selected_filter'] ) : '';
			$custom_filter_val         = isset( $_POST['custom_filter_val'] ) ? (array) $_POST['custom_filter_val'] : array();
			$return_data               = array();
			$search_filter_dates       = array();
			$appointments_search_query = '1=1';
			$appointments_group_by     = 'bookingpress_appointment_date';
			if ( $selected_filter_val == 'today' ) {
				$selected_filter_val        = date( 'Y-m-d', current_time( 'timestamp' ) );
				$appointments_search_query .= " AND (bookingpress_appointment_date = '" . $selected_filter_val . "')";
				$appointments_group_by      = 'bookingpress_appointment_time';

				$start_time = strtotime( 'today' );
				$end_time   = strtotime( 'tomorrow', $start_time ) - 1;

				while ( $start_time <= $end_time ) {
					array_push( $search_filter_dates, date( 'H:i:s', $start_time ) );
					$start_time = strtotime( '+1 hour', $start_time );
				}
			} elseif ( $selected_filter_val == 'yesterday' ) {
				$search_filter_val          = date( 'Y-m-d', strtotime( '-1 days', current_time( 'timestamp' ) ) );
				$appointments_search_query .= " AND (bookingpress_appointment_date = '" . $search_filter_val . "')";
				$appointments_group_by      = 'bookingpress_appointment_time';

				$start_time = strtotime( 'today' );
				$end_time   = strtotime( 'tomorrow', $start_time ) - 1;

				while ( $start_time <= $end_time ) {
					array_push( $search_filter_dates, date( 'H:i:s', $start_time ) );
					$start_time = strtotime( '+1 hour', $start_time );
				}
			} elseif ( $selected_filter_val == 'tomorrow' ) {
				$search_filter_val          = date( 'Y-m-d', strtotime( '+1 days', current_time( 'timestamp' ) ) );
				$appointments_search_query .= " AND (bookingpress_appointment_date = '" . $search_filter_val . "')";
				$appointments_group_by      = 'bookingpress_appointment_time';

				$start_time = strtotime( 'today' );
				$end_time   = strtotime( 'tomorrow', $start_time ) - 1;

				while ( $start_time <= $end_time ) {
					array_push( $search_filter_dates, date( 'H:i:s', $start_time ) );
					$start_time = strtotime( '+1 hour', $start_time );
				}
			} elseif ( $selected_filter_val == 'week' ) {
				$week_number  = date( 'W' );
				$current_year = date( 'Y' );
				$week_dates   = $BookingPress->get_weekstart_date_end_date( $week_number, $current_year );
				$week_start   = $week_dates['week_start'];
				$week_end     = date( 'Y-m-d', strtotime( '+1 days ' . $week_dates['week_end'] ) );

				$appointments_search_query .= " AND (bookingpress_appointment_date BETWEEN '" . $week_start . "' AND '" . $week_end . "')";

				$bookingpress_get_all_dates = new DatePeriod(
					new DateTime( $week_start ),
					new DateInterval( 'P1D' ),
					new DateTime( $week_end )
				);

				foreach ( $bookingpress_get_all_dates as $date_key => $date_val ) {
					$search_date_val = $date_val->format( 'M d' );
					array_push( $search_filter_dates, $search_date_val );
				}
			} elseif ( $selected_filter_val == 'last_week' ) {
				$week_number  = date( 'W' ) - 1;
				$current_year = date( 'Y' );
				$week_dates   = $BookingPress->get_weekstart_date_end_date( $week_number, $current_year );
				$week_start   = $week_dates['week_start'];
				$week_end     = date( 'Y-m-d', strtotime( '+1 days ' . $week_dates['week_end'] ) );

				$appointments_search_query .= " AND (bookingpress_appointment_date BETWEEN '" . $week_start . "' AND '" . $week_end . "')";

				$bookingpress_get_all_dates = new DatePeriod(
					new DateTime( $week_start ),
					new DateInterval( 'P1D' ),
					new DateTime( $week_end )
				);

				foreach ( $bookingpress_get_all_dates as $date_key => $date_val ) {
					$search_date_val = $date_val->format( 'M d' );
					array_push( $search_filter_dates, $search_date_val );
				}
			} elseif ( $selected_filter_val == 'monthly' ) {
				$monthly_dates = $BookingPress->get_monthstart_date_end_date();
				$month_start   = $monthly_dates['start_date'];
				$month_end     = date( 'Y-m-d', strtotime( '+1 days ' . $monthly_dates['end_date'] ) );

				$appointments_search_query .= " AND (bookingpress_appointment_date BETWEEN '" . $month_start . "' AND '" . $month_end . "')";

				$bookingpress_get_all_dates = new DatePeriod(
					new DateTime( $month_start ),
					new DateInterval( 'P1D' ),
					new DateTime( $month_end )
				);

				foreach ( $bookingpress_get_all_dates as $date_key => $date_val ) {
					$search_date_val = $date_val->format( 'M d' );
					array_push( $search_filter_dates, $search_date_val );
				}
			} elseif ( $selected_filter_val == 'yearly' ) {
				$year_start_date            = date( 'Y-m-d', strtotime( '01/01' ) );
				$year_end_date              = date( 'Y-m-d', strtotime( '+1 days ' . date( 'Y-m-d', strtotime( '12/31' ) ) ) );
				$appointments_search_query .= " AND (bookingpress_appointment_date BETWEEN '" . $year_start_date . "' AND '" . $year_end_date . "')";

				$bookingpress_get_all_dates = new DatePeriod(
					new DateTime( $year_start_date ),
					new DateInterval( 'P1D' ),
					new DateTime( $year_end_date )
				);

				foreach ( $bookingpress_get_all_dates as $date_key => $date_val ) {
					$search_date_val = $date_val->format( 'M d' );
					array_push( $search_filter_dates, $search_date_val );
				}
			} elseif ( $selected_filter_val == 'custom' ) {
				$customer_filter_start_val  = ! empty( $custom_filter_val[0] ) ? sanitize_text_field( $custom_filter_val[0] ) : date( 'Y-m-d' );
				$customer_filter_end_val    = ! empty( $custom_filter_val[1] ) ? date( 'Y-m-d', strtotime( '+1 days ' . sanitize_text_field( $custom_filter_val[1] ) ) ) : date( 'Y-m-d' );
				$appointments_search_query .= " AND (bookingpress_appointment_date BETWEEN '" . $customer_filter_start_val . "' AND '" . $customer_filter_end_val . "')";

				$bookingpress_get_all_dates = new DatePeriod(
					new DateTime( $customer_filter_start_val ),
					new DateInterval( 'P1D' ),
					new DateTime( $customer_filter_end_val )
				);

				foreach ( $bookingpress_get_all_dates as $date_key => $date_val ) {
					$search_date_val = $date_val->format( 'M d' );
					array_push( $search_filter_dates, $search_date_val );
				}
			}

			$total_appointments = $wpdb->get_results( "SELECT COUNT(bookingpress_appointment_booking_id) as total, bookingpress_appointment_date, bookingpress_appointment_time FROM {$tbl_bookingpress_appointment_bookings} WHERE {$appointments_search_query} GROUP BY {$appointments_group_by}", ARRAY_A );

			$approved_appointments           = $wpdb->get_results( "SELECT COUNT(bookingpress_appointment_booking_id) as total, bookingpress_appointment_date, bookingpress_appointment_time FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = 'Approved' AND {$appointments_search_query} GROUP BY {$appointments_group_by}", ARRAY_A );
			$tmp_total_approved_appointments = array();
			foreach ( $approved_appointments as $appointment_key => $appointment_val ) {
				$total_appointments = (int) $appointment_val['total'];
				$appointment_date   = date( 'M d', strtotime( $appointment_val['bookingpress_appointment_date'] ) );
				if ( $appointments_group_by != 'bookingpress_appointment_date' ) {
					$appointment_date = date( 'H:i:s', strtotime( $appointment_val['bookingpress_appointment_time'] ) );
				}
				$tmp_total_approved_appointments[ $appointment_date ] = $total_appointments;
			}

			$pending_appointments           = $wpdb->get_results( "SELECT COUNT(bookingpress_appointment_booking_id) as total, bookingpress_appointment_date, bookingpress_appointment_time FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = 'Pending' AND {$appointments_search_query} GROUP BY {$appointments_group_by}", ARRAY_A );
			$tmp_total_pending_appointments = array();
			foreach ( $pending_appointments as $appointment_key => $appointment_val ) {
				$total_appointments = (int) $appointment_val['total'];
				$appointment_date   = date( 'M d', strtotime( $appointment_val['bookingpress_appointment_date'] ) );
				if ( $appointments_group_by != 'bookingpress_appointment_date' ) {
					$appointment_date = date( 'H:i:s', strtotime( $appointment_val['bookingpress_appointment_time'] ) );
				}
				$tmp_total_pending_appointments[ $appointment_date ] = $total_appointments;
			}

			$total_revenue     = $wpdb->get_results( "SELECT SUM(bookingpress_service_price) as total, bookingpress_appointment_date, bookingpress_appointment_time FROM {$tbl_bookingpress_appointment_bookings} WHERE {$appointments_search_query} GROUP BY {$appointments_group_by}", ARRAY_A );
			$tmp_total_revenue = array();
			foreach ( $total_revenue as $revenue_key => $revenue_val ) {
				$revenue_amount   = (float) $revenue_val['total'];
				$appointment_date = date( 'M d', strtotime( $revenue_val['bookingpress_appointment_date'] ) );
				if ( $appointments_group_by != 'bookingpress_appointment_date' ) {
					$appointment_date = date( 'H:i:s', strtotime( $revenue_val['bookingpress_appointment_time'] ) );
				}
				$tmp_total_revenue[ $appointment_date ] = $revenue_amount;
			}

			$total_customers     = $wpdb->get_results( "SELECT COUNT(bookingpress_appointment_booking_id) as total, bookingpress_appointment_date, bookingpress_appointment_time FROM {$tbl_bookingpress_appointment_bookings} WHERE {$appointments_search_query} GROUP BY {$appointments_group_by} ", ARRAY_A );
			$tmp_total_customers = array();
			foreach ( $total_customers as $customer_key => $customer_val ) {
				$total_customers  = (int) $customer_val['total'];
				$appointment_date = date( 'M d', strtotime( $customer_val['bookingpress_appointment_date'] ) );
				if ( $appointments_group_by != 'bookingpress_appointment_date' ) {
					$appointment_date = date( 'H:i:s', strtotime( $customer_val['bookingpress_appointment_time'] ) );
				}
				$tmp_total_customers[ $appointment_date ] = $total_customers;
			}

			$total_approved_appointments = array();
			$total_pending_appointments  = array();
			$total_revenue_data          = array();
			$total_customers_data        = array();
			foreach ( $search_filter_dates as $filter_key => $filter_val ) {
				$approved_appointment_vals = array_key_exists( $filter_val, $tmp_total_approved_appointments ) ? $tmp_total_approved_appointments[ $filter_val ] : 0;
				array_push( $total_approved_appointments, $approved_appointment_vals );

				$pending_appointment_vals = array_key_exists( $filter_val, $tmp_total_pending_appointments ) ? $tmp_total_pending_appointments[ $filter_val ] : 0;
				array_push( $total_pending_appointments, $pending_appointment_vals );

				$total_revenue_vals = array_key_exists( $filter_val, $tmp_total_revenue ) ? $tmp_total_revenue[ $filter_val ] : 0;
				array_push( $total_revenue_data, $total_revenue_vals );

				$total_customer_vals    = array_key_exists( $filter_val, $tmp_total_customers ) ? $tmp_total_customers[ $filter_val ] : 0;
				$total_customers_data[] = $total_customer_vals;
			}

			$return_data['total_appointments']    = $total_appointments;
			$return_data['approved_appointments'] = $total_approved_appointments;
			$return_data['pending_appointments']  = $total_pending_appointments;
			$return_data['total_revenue']         = $total_revenue_data;
			$return_data['total_customers']       = $total_customers_data;
			$return_data['chart_x_axis_vals']     = $search_filter_dates;

			echo json_encode( $return_data );
			exit();
		}

		function bookingpress_dashboard_dynamic_data_fields_func() {
			global $wpdb, $bookingpress_dashboard_vue_data_fields, $BookingPress, $tbl_bookingpress_customers, $bookingpress_global_options;

			$currency_name   = $BookingPress->bookingpress_get_settings( 'payment_default_currency', 'payment_setting' );
			$currency_name   = ! empty( $currency_name ) ? $currency_name : 'US Dollar';
			$currency_symbol = $BookingPress->bookingpress_get_currency_symbol( $currency_name );
			$bookingpress_dashboard_vue_data_fields['chart_currency_symbol'] = $currency_symbol;

			$bookingpress_customer_details           = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_customers . ' WHERE bookingpress_user_type = 2 AND bookingpress_user_status = 1', ARRAY_A );
			$bookingpress_customer_selection_details = array();
			foreach ( $bookingpress_customer_details as $bookingpress_customer_key => $bookingpress_customer_val ) {
				$bookingpress_customer_name = ( $bookingpress_customer_val['bookingpress_user_firstname'] == '' && $bookingpress_customer_val['bookingpress_user_lastname'] == '' ) ? $bookingpress_customer_val['bookingpress_user_email'] : $bookingpress_customer_val['bookingpress_user_firstname'] . ' ' . $bookingpress_customer_val['bookingpress_user_lastname'];

				$bookingpress_customer_selection_details[] = array(
					'text'  => $bookingpress_customer_name,
					'value' => $bookingpress_customer_val['bookingpress_customer_id'],
				);
			}
			$bookingpress_dashboard_vue_data_fields['appointment_customers_list'] = $bookingpress_customer_selection_details;

			$bookingpress_services_details2                                      = array();
			$bookingpress_services_details2[]                                    = array(
				'category_name'     => '',
				'category_services' => array(
					'0' => array(
						'service_id'    => 0,
						'service_name'  => __( 'Select service', 'bookingpress-appointment-booking' ),
						'service_price' => '',
					),
				),
			);
			$bookingpress_services_details                                       = $BookingPress->get_bookingpress_service_data_group_with_category();
			$bookingpress_services_details2                                      = array_merge( $bookingpress_services_details2, $bookingpress_services_details );
			$bookingpress_dashboard_vue_data_fields['appointment_services_list'] = $bookingpress_services_details2;

			$default_daysoff_details = $BookingPress->bookingpress_get_default_dayoff_dates();
			if ( ! empty( $default_daysoff_details ) ) {
				$default_daysoff_details                                 = array_map(
					function( $date ) {
						return date( 'Y-m-d', strtotime( $date ) );
					},
					$default_daysoff_details
				);
				$bookingpress_dashboard_vue_data_fields['disabledDates'] = $default_daysoff_details;
			} else {
				$bookingpress_dashboard_vue_data_fields['disabledDates'] = '';
			}
			$bookingpress_dashboard_vue_data_fields['appointment_formdata']['_wpnonce'] = wp_create_nonce( 'bpa_wp_nonce' );

			$bookingpress_dashboard_vue_data_fields = apply_filters('bookingpress_modify_dashboard_data_fields',$bookingpress_dashboard_vue_data_fields);

			echo json_encode( $bookingpress_dashboard_vue_data_fields );
		}

		function bookingpress_dashboard_dynamic_helper_vars_func() {
			global $bookingpress_global_options;
			$bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_locale_lang = $bookingpress_options['locale'];
			?>
				var lang = ELEMENT.lang.<?php echo esc_html( $bookingpress_locale_lang ); ?>;
				ELEMENT.locale(lang)

				var bookingpress_appointment_chart = ''
				var revenue_chart = ''
				var customer_chart = ''
			<?php
		}

		function bookingpress_dashboard_dynamic_on_load_methods_func() {
			?>
			const vm = this
			vm.loadSummary()
			vm.loadAppointments()	
			vm.loadCharts()
			<?php
		}

		function bookingpress_dashboard_dynamic_vue_methods_func() {
			global $bookingpress_notification_duration;
			?>
			loadCharts(){
				const vm = this

				var postData = { action:'bookingpress_get_charts_data', selected_filter: vm.currently_selected_filter, custom_filter_val: vm.custom_filter_val, _wpnonce: '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' };
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {
					vm.appointment_chart_x_axis_data = response.data.chart_x_axis_vals
					vm.revenue_chart_x_axis_data = response.data.chart_x_axis_vals
					vm.total_approved_appointments = response.data.approved_appointments
					vm.total_pending_appointments = response.data.pending_appointments
					vm.total_revenue = response.data.total_revenue
					vm.total_customers_data = response.data.total_customers

					if(bookingpress_appointment_chart != '' && bookingpress_appointment_chart != undefined){
						bookingpress_appointment_chart.destroy()
					}

					if(revenue_chart != '' && revenue_chart != undefined){
						revenue_chart.destroy()
					}

					if(customer_chart != '' && customer_chart != undefined){
						customer_chart.destroy()
					}

					const ctx = document.getElementById('appointments_charts').getContext('2d');
					bookingpress_appointment_chart = new Chart(ctx, {
						type: 'bar',
						data: {
							labels: vm.revenue_chart_x_axis_data,
							datasets: [{
								label: '<?php esc_html_e( 'Approved Appointment', 'bookingpress-appointment-booking' ); ?>',
								data: vm.total_approved_appointments,
								backgroundColor: [
									'rgba(18, 212, 136, 0.3)',
								],
								borderColor: [
									'rgba(18, 212, 136, 1)',
								],
								borderWidth: 1
							},
							{
								label: '<?php esc_html_e( 'Pending Appointment', 'bookingpress-appointment-booking' ); ?>',
								data: vm.total_pending_appointments,
								backgroundColor: [
									'rgba(245, 174, 65, 0.3)',
								],
								borderColor: [
									'rgba(245, 174, 65, 1)',
								],
								borderWidth: 1	
							}]
						},
						options: {
							scales: {
								y: {
									beginAtZero: false
								}
							},
							responsive: true,
							plugins:{
								title: {
									display: true,
									text: '<?php esc_html_e( 'Appointments', 'bookingpress-appointment-booking' ); ?>'
								},
								legend: {
									onClick: null
								},
							},
						}
					});

					const ctx2 = document.getElementById('revenue_charts').getContext('2d');
					revenue_chart = new Chart(ctx2, {
						type: 'line',
						data: {
							labels: vm.revenue_chart_x_axis_data,
							datasets: [{
								label: '<?php esc_html_e( 'Revenue', 'bookingpress-appointment-booking' ); ?>',
								data: vm.total_revenue,
								backgroundColor: [
									'rgba(18, 212, 136, 0.3)',
								],
								borderColor: [
									'rgba(18, 212, 136, 1)',
								],
								borderWidth: 1
							}]
						},
						options: {
							responsive: true,
							plugins: {
								legend: {
									position: 'top',
									onClick: null,
								},
								title: {
									display: true,
									text: '<?php esc_html_e( 'Revenue', 'bookingpress-appointment-booking' ); ?>'
								},
								tooltip: {
									callbacks: {
										label: function(context) {
											var label = context.dataset.label || '';
											if (label) {
												label += ': ';
											}
											label += vm.chart_currency_symbol + ((context.parsed.y).toFixed(2))
											return label;
										}
									}
								}
							}
						}
					});


					const ctx3 = document.getElementById('customer_charts').getContext('2d');
					customer_chart = new Chart(ctx3, {
						type: 'bar',
						data: {
							labels: vm.revenue_chart_x_axis_data,
							datasets: [{
								label: '<?php esc_html_e( 'Customers', 'bookingpress-appointment-booking' ); ?>',
								data: vm.total_customers_data,
								backgroundColor: [
									'rgba(33, 103, 241, 0.3)',
								],
								borderColor: [
									'rgba(33, 103, 241, 1)',
								],
								borderWidth: 1
							}]
						},
						options: {
							responsive: true,
							scales: {
								y: {
									beginAtZero: false
								}
							},
							plugins:{
								title: {
									display: true,
									text: '<?php esc_html_e( 'Customers', 'bookingpress-appointment-booking' ); ?>'
								},
								legend: {
									onClick: null
								},
							},
						}
					});
				}.bind(this) )
				.catch( function (error) {					
					vm.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something Went wrong...', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
				});	

				
			},
			async loadSummary(){
				const vm2 = this
				var postData = { action:'bookingpress_get_dashboard_summary', selected_filter: vm2.currently_selected_filter, custom_filter_val: vm2.custom_filter_val,_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' };
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {
					vm2.summary_data.total_appoint = response.data.total_appointments
					vm2.summary_data.approved_appoint = response.data.approved_appointments
					vm2.summary_data.pending_appoint = response.data.pending_appointments
					vm2.summary_data.total_revenue = response.data.total_revenue
					vm2.summary_data.total_customers = response.data.total_customers
					<?php 
					do_action('bookingpress_load_summary_dynamic_data');
					?>					

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
			async loadAppointments() {
				const vm2 = this
				var bookingpress_search_data = { }
				var postData = { action:'bookingpress_get_dashboard_upcoming_appointments', search_data: bookingpress_search_data };
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {
					this.items = response.data.upcoming_appointments;
					this.totalItems = response.data.totalItems;
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
			select_dashboard_filter(filter_val){
				const vm = this
				vm.currently_selected_filter = filter_val
				this.loadSummary()
				this.loadCharts()
			},
			select_dashboard_custom_date_filter(selected_value){
				this.custom_filter_val[0] = this.get_formatted_date(this.custom_filter_val[0])
				this.custom_filter_val[1] = this.get_formatted_date(this.custom_filter_val[1])
				this.loadSummary()
				this.loadCharts()
			},
			select_date(selected_value) {
				this.custom_filter_val[0] = this.get_formatted_date(this.custom_filter_val[0])
				this.custom_filter_val[1] = this.get_formatted_date(this.custom_filter_val[1])
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
			editAppointmentData(index,row) {
				const vm2 = this
				var edit_id = row.appointment_id;
				vm2.appointment_formdata.appointment_update_id = edit_id
				vm2.open_add_appointment_modal()				
				var postData = { action:'bookingpress_get_edit_appointment_data', payment_log_id: edit_id, appointment_id: edit_id,_wpnonce:'<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>' };
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
			closeAppointmentModal() {
				const vm2= this
				vm2.$refs['appointment_formdata'].resetFields()
				vm2.resetForm()
				vm2.open_appointment_modal = false
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
				vm2.appointment_formdata.appointment_status = 'Approved'
				vm2.appointment_formdata.appointment_update_id = 0
			},
			open_add_appointment_modal() {				
				this.open_appointment_modal = true;
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

global $bookingpress_dashboard, $bookingpress_dashboard_vue_data_fields;
$bookingpress_dashboard = new bookingpress_dashboard();

$bookingpress_dashboard_vue_data_fields = array(
	'bulk_action'                   => 'bulk_action',
	'items'                         => array(),
	'summary_data'                  => array(
		'total_appoint'    => 0,
		'approved_appoint' => 0,
		'pending_appoint'  => 0,
		'total_revenue'    => 0,
		'total_customers'  => 0,
	),
	'currently_selected_filter'     => 'week',
	'custom_filter_val'             => '',
	'appointment_chart_x_axis_data' => array(),
	'total_approved_appointments'   => array(),
	'total_pending_appointments'    => array(),
	'revenue_chart_x_axis_data'     => array(),
	'total_revenue'                 => array(),
	'chart_currency_symbol'         => '$',
	'total_customers_data'          => array(),
	'search_appointment_status'     => '',
	'appointment_time_slot'         => array(),
	'appointment_status'            => array(
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
	'rules'                         => array(
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
	'appointment_customers_list'    => array(),
	'appointment_services_list'     => array(),
	'appointment_formdata'          => array(
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
	'open_appointment_modal'        => false,
	'is_disabled'                   => false,
	'is_display_save_loader'        => '0',
);
