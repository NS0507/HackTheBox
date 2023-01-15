<?php

if ( ! class_exists( 'bookingpress_customize' ) ) {
	class bookingpress_customize {
		function __construct() {
			add_action( 'bookingpress_customize_dynamic_view_load', array( $this, 'bookingpress_load_customize_dynamic_view_func' ) );
			add_action( 'bookingpress_customize_dynamic_data_fields', array( $this, 'bookingpress_dynamic_data_fields_func' ) );
			add_action( 'bookingpress_customize_dynamic_computed_methods', array( $this, 'bookingpress_dynamic_computed_methods_func' ) );
			add_action( 'bookingpress_customize_dynamic_on_load_methods', array( $this, 'bookingpress_dynamic_onload_methods_func' ) );
			add_action( 'bookingpress_customize_dynamic_vue_methods', array( $this, 'bookingpress_dynamic_vue_methods_func' ) );
			add_action( 'bookingpress_customize_dynamic_components', array( $this, 'bookingpress_dynamic_components_func' ) );
			add_action( 'bookingpress_customize_dynamic_helper_vars', array( $this, 'bookingpress_customize_helper_vars_func' ) );

			add_action( 'wp_ajax_bookingpress_save_field_settings', array( $this, 'bookingpress_save_field_settings_data_func' ) );
			add_action( 'wp_ajax_bookingpress_save_my_booking_settings', array( $this, 'bookingpress_save_my_booking_settings_func' ) );
			add_action( 'wp_ajax_bookingpress_load_field_settings', array( $this, 'bookingpress_load_field_settings_func' ) );
			add_action( 'wp_ajax_bookingpress_update_field_position', array( $this, 'bookingpress_update_field_pos_func' ) );
			add_action( 'wp_ajax_bookingpress_save_form_settings', array( $this, 'bookingpress_save_form_settings_func' ) );
			add_action( 'wp_ajax_bookingpress_load_bookingform_data', array( $this, 'bookingpress_load_bookingform_data_func' ) );
			add_action( 'wp_ajax_bookingpress_set_date_format', array( $this, 'bookingpress_set_date_format_func' ) );
			add_action( 'wp_ajax_bookingpress_load_my_booking_data', array( $this, 'bookingpress_load_my_booking_data_func' ) );
		}

		function bookingpress_load_bookingform_data_func() {
			global $wpdb, $BookingPress, $tbl_bookingpress_customize_settings, $bookingpress_customize_vue_data_fields;
			$response              = array();
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['formdata'] = '';
				$response['variant']  = 'error';
				$response['title']    = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']      = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
				echo json_encode( $response );
				exit;
			}

			$tab_container_data = array(
				'is_edit_service'       => false,
				'is_edit_date_time'     => false,
				'is_edit_basic_details' => false,
				'is_edit_summary'       => false,
				'service_title'         => __( 'Services', 'bookingpress-appointment-booking' ),
				'datetime_title'        => __( 'Date & Time', 'bookingpress-appointment-booking' ),
				'basic_details_title'   => __( 'Basic Details', 'bookingpress-appointment-booking' ),
				'summary_title'         => __( 'Summary', 'bookingpress-appointment-booking' ),
			);

			$category_container_data = array(
				'editCategoryTitlePopup' => false,
				'is_edit_category_title' => false,
				'category_title'         => __( 'Select Category', 'bookingpress-appointment-booking' ),
			);

			$service_container_data = array(
				'editServiceTitlePopup' => false,
				'is_edit_service_title' => false,
				'service_heading_title' => __( 'Select Service', 'bookingpress-appointment-booking' ),
				'default_image_url'     => BOOKINGPRESS_URL . '/images/placeholder-img.jpg',
			);

			$timeslot_container_data = array(
				'is_edit_timeslot' => false,
				'timeslot_text'    => __( 'Time Slot', 'bookingpress-appointment-booking' ),
				'is_edit_morning' => false,
				'morning_text'    => __( 'Morning', 'bookingpress-appointment-booking' ),
				'is_edit_afternoon' => false,
				'afternoon_text'    => __( 'Afternoon', 'bookingpress-appointment-booking' ),
				'is_edit_evening' => false,
				'evening_text'    => __( 'Evening', 'bookingpress-appointment-booking' ),
				'is_edit_night' => false,
				'night_text'    => __( 'Night', 'bookingpress-appointment-booking' ),
			);

			$bookingpress_colorpicker_values = array(
				'background_color'         => '#fff',
				'footer_background_color'  => '#f4f7fb',
				'primary_color'            => '#12D488',
				'primary_background_color' => '#e2faf1',
				'label_title_color'        => '#202C45',
				'content_color'            => '#535D71',
				'price_button_text_color'  => '#fff',
				'custom_css'               => '',
			);

			$bookingpress_font_values = array(
				'title_font_size'     => '16',
				'title_font_family'   => 'Poppins',
				'content_font_size'   => '16',
				'content_font_family' => 'Poppins',
			);

			$booking_form_settings = array(
				'hide_category_service_selection' => false,
				'hide_next_previous_button'       => false,
				'hide_already_booked_slot'        => false,
				'display_service_description'     => false,
				'booking_form_tabs_position'      => 'left',
				'goback_button_text'              => __( 'Go Back', 'bookingpress-appointment-booking' ),
				'next_button_text'                => __( 'Next', 'bookingpress-appointment-booking' ),
				'book_appointment_btn_text'       => __( 'Book Appointment', 'bookingpress-appointment-booking' ),
				'default_date_format'             => get_option( 'date_format' ),
			);

			$summary_container_data = array(
				'is_edit_summary_content'       => false,
				'summary_content_text'          => __( 'Your appointment booking summary', 'bookingpress-appointment-booking' ),
				'is_edit_select_payment_method' => false,
				'payment_method_text'           => __( 'Select Payment Method', 'bookingpress-appointment-booking' ),
			);

			$bookingpress_bookingform_data = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_customize_settings} WHERE bookingpress_setting_type = 'booking_form'", ARRAY_A );
			foreach ( $bookingpress_bookingform_data as $bookingpress_formdata_key => $bookingpress_formdata_val ) {
				$bookingpress_setting_name  = $bookingpress_formdata_val['bookingpress_setting_name'];
				$bookingpress_setting_value = $bookingpress_formdata_val['bookingpress_setting_value'];

				if ( $bookingpress_setting_value == 'false' || $bookingpress_setting_value == 'true' ) {
					$bookingpress_setting_value = ( $bookingpress_setting_value == 'false' ) ? false : true;
				}

				if ( isset( $bookingpress_colorpicker_values[ $bookingpress_setting_name ] ) ) {
					$bookingpress_colorpicker_values[ $bookingpress_setting_name ] = $bookingpress_setting_value;
				} elseif ( isset( $bookingpress_font_values[ $bookingpress_setting_name ] ) ) {
					$bookingpress_font_values[ $bookingpress_setting_name ] = $bookingpress_setting_value;
				} elseif ( isset( $tab_container_data[ $bookingpress_setting_name ] ) ) {
					$tab_container_data[ $bookingpress_setting_name ] = $bookingpress_setting_value;
				} elseif ( isset( $category_container_data[ $bookingpress_setting_name ] ) ) {
					$category_container_data[ $bookingpress_setting_name ] = $bookingpress_setting_value;
				} elseif ( isset( $service_container_data[ $bookingpress_setting_name ] ) ) {
					$service_container_data[ $bookingpress_setting_name ] = $bookingpress_setting_value;
				} elseif ( isset( $timeslot_container_data[ $bookingpress_setting_name ] ) ) {
					$timeslot_container_data[ $bookingpress_setting_name ] = $bookingpress_setting_value;
				} elseif ( isset( $booking_form_settings[ $bookingpress_setting_name ] ) ) {
					$booking_form_settings[ $bookingpress_setting_name ] = $bookingpress_setting_value;
				} elseif ( isset( $summary_container_data[ $bookingpress_setting_name ] ) ) {
					$summary_container_data[ $bookingpress_setting_name ] = $bookingpress_setting_value;
				}
			}

			$bookingpress_return_data = array(
				'tab_container_data'      => $tab_container_data,
				'category_container_data' => $category_container_data,
				'service_container_data'  => $service_container_data,
				'timeslot_container_data' => $timeslot_container_data,
				'colorpicker_values'      => $bookingpress_colorpicker_values,
				'font_values'             => $bookingpress_font_values,
				'booking_form_settings'   => $booking_form_settings,
				'summary_container_data'  => $summary_container_data,
			);

			$response['variant']  = 'success';
			$response['title']    = esc_html__( 'Success', 'bookingpress-appointment-booking' );
			$response['msg']      = esc_html__( 'Field Settings Data Retrieved Successfully', 'bookingpress-appointment-booking' );
			$response['formdata'] = $bookingpress_return_data;

			echo json_encode( $response );
			exit();
		}

		function bookingpress_load_my_booking_data_func() {

			global $wpdb, $BookingPress, $tbl_bookingpress_customize_settings, $bookingpress_customize_vue_data_fields;
			$response              = array();
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['formdata'] = '';
				$response['variant']  = 'error';
				$response['title']    = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']      = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
				echo json_encode( $response );
				exit;
			}
			$bookingpress_colorpicker_values        = array(
				'background_color'     => '#fff',
				'row_background_color' => '#f4f7fb',
				'label_title_color'    => '#727E95',
				'content_color'        => '#727e95',
				'custom_css'           => '',
			);
			$bookingpress_font_values               = array(
				'title_font_size'     => '14',
				'title_font_family'   => 'Poppins',
				'content_font_size'   => '14',
				'content_font_family' => 'Poppins',
			);
			$bookingpress_my_booking_field_settings = array(
				'is_edit_mybooking_title'     => false,
				'mybooking_title_text'        => __( 'My Bookings', 'bookingpress-appointment-booking' ),
				'hide_customer_details'       => false,
				'hide_search_bar'             => false,
				'allow_to_cancel_appointment' => true,
				'Default_date_formate'        => get_option( 'date_format' ),
			);

			$bookingpress_bookingform_data = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_customize_settings} WHERE bookingpress_setting_type = 'booking_my_booking'", ARRAY_A );
			foreach ( $bookingpress_bookingform_data as $bookingpress_formdata_key => $bookingpress_formdata_val ) {
				$bookingpress_setting_name  = $bookingpress_formdata_val['bookingpress_setting_name'];
				$bookingpress_setting_value = $bookingpress_formdata_val['bookingpress_setting_value'];

				if ( $bookingpress_setting_value == 'false' || $bookingpress_setting_value == 'true' ) {
					$bookingpress_setting_value = ( $bookingpress_setting_value == 'false' ) ? false : true;
				}
				if ( isset( $bookingpress_colorpicker_values[ $bookingpress_setting_name ] ) ) {
					$bookingpress_colorpicker_values[ $bookingpress_setting_name ] = $bookingpress_setting_value;
				} elseif ( isset( $bookingpress_font_values[ $bookingpress_setting_name ] ) ) {
					$bookingpress_font_values[ $bookingpress_setting_name ] = $bookingpress_setting_value;
				} elseif ( isset( $bookingpress_my_booking_field_settings[ $bookingpress_setting_name ] ) ) {
					$bookingpress_my_booking_field_settings[ $bookingpress_setting_name ] = $bookingpress_setting_value;
				}
			}
			$bookingpress_return_data = array(
				'colorpicker_values'    => $bookingpress_colorpicker_values,
				'font_values'           => $bookingpress_font_values,
				'booking_form_settings' => $bookingpress_my_booking_field_settings,
			);

			$response['variant']  = 'success';
			$response['title']    = esc_html__( 'Success', 'bookingpress-appointment-booking' );
			$response['msg']      = esc_html__( 'Field Settings Data Retrieved Successfully', 'bookingpress-appointment-booking' );
			$response['formdata'] = $bookingpress_return_data;

			echo json_encode( $response );
			exit();
		}

		function bookingpress_save_form_settings_func() {
			global $wpdb, $BookingPress, $tbl_bookingpress_customize_settings;
			$response              = array();
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['field_settings'] = '';
				$response['variant']        = 'error';
				$response['title']          = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']            = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
				echo json_encode( $response );
				exit;
			}

			$response['variant'] = 'error';
			$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']     = esc_html__( 'Something went wrong...', 'bookingpress-appointment-booking' );

			$tab_container_data              = ! empty( $_POST['tab_container_data'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['tab_container_data'] ) : array();
			$category_container_data         = ! empty( $_POST['category_container_data'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['category_container_data'] ) : array();
			$service_container_data          = ! empty( $_POST['service_container_data'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['service_container_data'] ) : array();
			$timeslot_container_data         = ! empty( $_POST['timeslot_container_data'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['timeslot_container_data'] ) : array();
			$bookingpress_colorpicker_values = ! empty( $_POST['colorpicker_values'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['colorpicker_values'] ) : array();
			$bookingpress_font_values        = ! empty( $_POST['font_values'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['font_values'] ) : array();
			$booking_form_settings           = ! empty( $_POST['booking_form_settings'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['booking_form_settings'] ) : array();
			$summary_container_data          = ! empty( $_POST['summary_container_data'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['summary_container_data'] ) : array();

			$bookingpress_customize_settings_data = array_merge( $tab_container_data, $category_container_data, $service_container_data, $timeslot_container_data, $bookingpress_colorpicker_values, $bookingpress_font_values, $booking_form_settings, $summary_container_data );
			if ( ! empty( $bookingpress_customize_settings_data ) ) {
				foreach ( $bookingpress_customize_settings_data as $bookingpress_setting_key => $bookingpress_setting_val ) {
					$bookingpress_db_fields = array(
						'bookingpress_setting_name'  => $bookingpress_setting_key,
						'bookingpress_setting_value' => $bookingpress_setting_val,
						'bookingpress_setting_type'  => 'booking_form',
					);

					$is_setting_exists = $wpdb->get_var( "SELECT COUNT(bookingpress_setting_id) as total FROM {$tbl_bookingpress_customize_settings} WHERE bookingpress_setting_name = '{$bookingpress_setting_key}' AND bookingpress_setting_type = 'booking_form'" );
					if ( $is_setting_exists > 0 ) {
						$wpdb->update(
							$tbl_bookingpress_customize_settings,
							$bookingpress_db_fields,
							array(
								'bookingpress_setting_name' => $bookingpress_setting_key,
								'bookingpress_setting_type' => 'booking_form',
							)
						);
					} else {
						$wpdb->insert( $tbl_bookingpress_customize_settings, $bookingpress_db_fields );
					}
				}

				$this->bookingpress_generate_customize_css_func();

				$response['variant'] = 'success';
				$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Customize settings updated successfully.', 'bookingpress-appointment-booking' );
			}

			echo json_encode( $response );
			exit();
		}

		function bookingpress_update_field_pos_func() {
			global $wpdb, $BookingPress, $tbl_bookingpress_form_fields;
			$response              = array();
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['field_settings'] = '';
				$response['variant']        = 'error';
				$response['title']          = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']            = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
				echo json_encode( $response );
				exit;
			}

			$response['variant'] = 'error';
			$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']     = esc_html__( 'Something went wrong...', 'bookingpress-appointment-booking' );

			$bookingpress_old_index = isset( $_POST['old_index'] ) ? ( intval( $_POST['old_index'] ) + 1 ) : 0;
			$bookingpress_new_index = isset( $_POST['new_index'] ) ? ( intval( $_POST['new_index'] ) + 1 ) : 0;
			$bookingpress_update_id = ! empty( $_POST['update_id'] ) ? intval( $_POST['update_id'] ) : 0;

			if ( isset( $_POST['old_index'] ) && isset( $_POST['new_index'] ) ) {
				if ( $bookingpress_new_index > $bookingpress_old_index ) {
					$condition = 'BETWEEN ' . $bookingpress_old_index . ' AND ' . $bookingpress_new_index;
					$fields    = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_form_fields . ' WHERE bookingpress_field_position ' . $condition . ' order by bookingpress_field_position ASC', ARRAY_A );
					foreach ( $fields as $field ) {
						$position = $field['bookingpress_field_position'] - 1;
						$position = ( $field['bookingpress_field_position'] == $bookingpress_old_index ) ? $bookingpress_new_index : $position;
						$args     = array(
							'bookingpress_field_position' => $position,
						);
						$wpdb->update( $tbl_bookingpress_form_fields, $args, array( 'bookingpress_form_field_id' => $field['bookingpress_form_field_id'] ) );
					}
				} else {
					$fields = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_form_fields . ' WHERE bookingpress_field_position BETWEEN ' . $bookingpress_new_index . ' AND ' . $bookingpress_old_index . ' order by bookingpress_field_position ASC', ARRAY_A );
					foreach ( $fields as $field ) {
						$position = $field['bookingpress_field_position'] + 1;
						$position = ( $field['bookingpress_field_position'] == $bookingpress_old_index ) ? $bookingpress_new_index : $position;
						$args     = array(
							'bookingpress_field_position' => $position,
						);
						$wpdb->update( $tbl_bookingpress_form_fields, $args, array( 'bookingpress_form_field_id' => $field['bookingpress_form_field_id'] ) );
					}
				}
				$response['variant'] = 'success';
				$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Field position has been changed successfully.', 'bookingpress-appointment-booking' );
			}

			echo json_encode( $response );
			exit();
		}

		function bookingpress_load_field_settings_func() {
			global $wpdb, $BookingPress, $tbl_bookingpress_form_fields;
			$response              = array();
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['field_settings'] = '';
				$response['variant']        = 'error';
				$response['title']          = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']            = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
				echo json_encode( $response );
				exit;
			}

			$bookingpress_field_settings_data        = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_form_fields} ORDER BY bookingpress_field_position ASC", ARRAY_A );
			$bookingpress_field_settings_return_data = array();
			foreach ( $bookingpress_field_settings_data as $bookingpress_field_setting_key => $bookingpress_field_setting_val ) {
				$bookingpress_field_type = '';
				if ( $bookingpress_field_setting_val['bookingpress_form_field_name'] == 'fullname' ) {
					$bookingpress_field_type = 'Text';
				} elseif ( $bookingpress_field_setting_val['bookingpress_form_field_name'] == 'firstname' ) {
					$bookingpress_field_type = 'Text';
				} elseif ( $bookingpress_field_setting_val['bookingpress_form_field_name'] == 'lastname' ) {
					$bookingpress_field_type = 'Text';
				} elseif ( $bookingpress_field_setting_val['bookingpress_form_field_name'] == 'email_address' ) {
					$bookingpress_field_type = 'Email';
				} elseif ( $bookingpress_field_setting_val['bookingpress_form_field_name'] == 'phone_number' ) {
					$bookingpress_field_type = 'Dropdown';
				} elseif ( $bookingpress_field_setting_val['bookingpress_form_field_name'] == 'note' ) {
					$bookingpress_field_type = 'Textarea';
				}

				$bookingpress_draggable_field_setting_fields_tmp                   = array();
				$bookingpress_draggable_field_setting_fields_tmp['id']             = intval( $bookingpress_field_setting_val['bookingpress_form_field_id'] );
				$bookingpress_draggable_field_setting_fields_tmp['field_name']     = $bookingpress_field_setting_val['bookingpress_form_field_name'];
				$bookingpress_draggable_field_setting_fields_tmp['field_type']     = $bookingpress_field_type;
				$bookingpress_draggable_field_setting_fields_tmp['is_edit']        = false;
				$bookingpress_draggable_field_setting_fields_tmp['is_required']    = ( $bookingpress_field_setting_val['bookingpress_field_required'] == 0 ) ? false : true;
				$bookingpress_draggable_field_setting_fields_tmp['label']          = $bookingpress_field_setting_val['bookingpress_field_label'];
				$bookingpress_draggable_field_setting_fields_tmp['placeholder']    = $bookingpress_field_setting_val['bookingpress_field_placeholder'];
				$bookingpress_draggable_field_setting_fields_tmp['error_message']  = $bookingpress_field_setting_val['bookingpress_field_error_message'];
				$bookingpress_draggable_field_setting_fields_tmp['is_hide']        = ( $bookingpress_field_setting_val['bookingpress_field_is_hide'] == 0 ) ? false : true;
				$bookingpress_draggable_field_setting_fields_tmp['field_position'] = intval( $bookingpress_field_setting_val['bookingpress_field_position'] );

				array_push( $bookingpress_field_settings_return_data, $bookingpress_draggable_field_setting_fields_tmp );
			}

			$response['variant']        = 'success';
			$response['title']          = esc_html__( 'Success', 'bookingpress-appointment-booking' );
			$response['msg']            = esc_html__( 'Field Settings Data Retrieved Successfully', 'bookingpress-appointment-booking' );
			$response['field_settings'] = $bookingpress_field_settings_return_data;

			echo json_encode( $response );
			exit();
		}

		function bookingpress_save_field_settings_data_func() {
			global $wpdb, $BookingPress, $tbl_bookingpress_form_fields;
			$response              = array();
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
				echo json_encode( $response );
				exit;
			}

			$response['variant'] = 'error';
			$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']     = esc_html__( 'Something went wrong...', 'bookingpress-appointment-booking' );

			$bookingpress_field_settings_data = ! empty( $_POST['field_settings'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['field_settings'] ) : array();
			if ( ! empty( $bookingpress_field_settings_data ) ) {
				foreach ( $bookingpress_field_settings_data as $bookingpress_field_setting_key => $bookingpress_field_setting_val ) {
					$bookingpress_field_name = $bookingpress_field_setting_val['field_name'];

					$bookingpress_db_fields = array(
						'bookingpress_form_field_name'     => $bookingpress_field_name,
						'bookingpress_field_required'      => ( $bookingpress_field_setting_val['is_required'] == 'false' ) ? 0 : 1,
						'bookingpress_field_label'         => $bookingpress_field_setting_val['label'],
						'bookingpress_field_placeholder'   => $bookingpress_field_setting_val['placeholder'],
						'bookingpress_field_error_message' => $bookingpress_field_setting_val['error_message'],
						'bookingpress_field_is_hide'       => ( $bookingpress_field_setting_val['is_hide'] == 'false' ) ? 0 : 1,
						'bookingpress_field_position'      => $bookingpress_field_setting_val['field_position'],
					);

					$field_exist = $wpdb->get_var( "SELECT COUNT(bookingpress_form_field_id) as total FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_form_field_name = '{$bookingpress_field_name}'" );
					if ( $field_exist > 0 ) {
						$wpdb->update( $tbl_bookingpress_form_fields, $bookingpress_db_fields, array( 'bookingpress_form_field_name' => $bookingpress_field_name ) );
					} else {
						$wpdb->insert( $tbl_bookingpress_form_fields, $bookingpress_db_fields );
					}
				}

				$response['variant'] = 'success';
				$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Field Settings Data Saved Successfully', 'bookingpress-appointment-booking' );
			}

			echo json_encode( $response );
			exit();
		}

		function bookingpress_save_my_booking_settings_func() {
			global $wpdb, $BookingPress, $tbl_bookingpress_customize_settings;
			$response              = array();
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['field_settings'] = '';
				$response['variant']        = 'error';
				$response['title']          = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']            = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
				echo json_encode( $response );
				exit;
			}

			$response['variant'] = 'error';
			$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']     = esc_html__( 'Something went wrong...', 'bookingpress-appointment-booking' );

			$bookingpress_colorpicker_data = ! empty( $_POST['my_booking_selected_colorpicker_values'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['my_booking_selected_colorpicker_values'] ) : array();
			$bookingpress_font_values_data = ! empty( $_POST['my_booking_selected_font_values'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['my_booking_selected_font_values'] ) : array();
			$bookingpress_settings_data    = ! empty( $_POST['my_booking_field_settings'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['my_booking_field_settings'] ) : array();

			$bookingpress_customize_settings_data = array_merge( $bookingpress_colorpicker_data, $bookingpress_font_values_data, $bookingpress_settings_data );
			if ( ! empty( $bookingpress_customize_settings_data ) ) {
				foreach ( $bookingpress_customize_settings_data as $bookingpress_setting_key => $bookingpress_setting_val ) {
					$bookingpress_db_fields = array(
						'bookingpress_setting_name'  => $bookingpress_setting_key,
						'bookingpress_setting_value' => $bookingpress_setting_val,
						'bookingpress_setting_type'  => 'booking_my_booking',
					);

					$is_setting_exists = $wpdb->get_var( "SELECT COUNT(bookingpress_setting_id) as total FROM {$tbl_bookingpress_customize_settings} WHERE bookingpress_setting_name = '{$bookingpress_setting_key}' AND bookingpress_setting_type = 'booking_my_booking'" );
					if ( $is_setting_exists > 0 ) {
						$wpdb->update(
							$tbl_bookingpress_customize_settings,
							$bookingpress_db_fields,
							array(
								'bookingpress_setting_name' => $bookingpress_setting_key,
								'bookingpress_setting_type' => 'booking_my_booking',
							)
						);
					} else {
						$wpdb->insert( $tbl_bookingpress_customize_settings, $bookingpress_db_fields );
					}
				}

				$this->bookingpress_generate_customize_css_func();

				$response['variant'] = 'success';
				$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Customize settings updated successfully.', 'bookingpress-appointment-booking' );
			}

			echo json_encode( $response );
			exit();
		}

		function bookingpress_set_date_format_func() {
			$response              = array();
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
				echo json_encode( $response );
				exit;
			}
			$bookingpress_date_format = ! empty( $_POST['format'] ) ? sanitize_text_field( $_POST['format'] ) : get_option( 'date_format' );
			$bookingpess_data         = '';

			if ( ! empty( $bookingpress_date_format ) ) {
				$bookingpess_data = array(
					'bookingpress_date_format_1' => 'Thursday, ' . date( $bookingpress_date_format, strtotime( '2021-10-25' ) ),
					'bookingpress_date_format_2' => date( $bookingpress_date_format, strtotime( '2021-10-25' ) ),
					'bookingpress_date_format_3' => 'Friday, ' . date( $bookingpress_date_format, strtotime( '2021-10-26' ) ),
					'bookingpress_date_format_4' => date( $bookingpress_date_format, strtotime( '2021-10-26' ) ),
				);

			}
			echo json_encode( $bookingpess_data );
			exit;
		}

		function bookingpress_customize_helper_vars_func() {
			global $bookingpress_global_options;
			$bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_locale_lang = $bookingpress_options['locale'];
			?>
			var lang = ELEMENT.lang.<?php echo esc_html( $bookingpress_locale_lang ); ?>;
			ELEMENT.locale(lang)
			<?php
		}

		function bookingpress_dynamic_components_func() {
			?>
				'vue-cal': vuecal
			<?php
		}

		function bookingpress_dynamic_vue_methods_func() {
			global $bookingpress_notification_duration;
			?>
				bpa_select_category(selected_category){
					const vm = this
					vm.bookingpress_shortcode_form.selected_category = selected_category
				},
				bpa_select_service(selected_service){
					const vm = this
					vm.bookingpress_shortcode_form.selected_service = selected_service
				},
				bpa_select_time(selected_time){
					const vm = this
					vm.bookingpress_shortcode_form.selected_time = selected_time
				},
				bpa_select_primary_color(selected_color){
					var opacity_color = Math.round(Math.min(Math.max(0.12 || 1, 0), 1) * 255);
					var primary_background_color = selected_color+(opacity_color.toString(16).toUpperCase())
					this.selected_colorpicker_values.primary_background_color = primary_background_color
				},
				bpa_reset_bookingform(){
					const vm = this
					vm.selected_colorpicker_values.background_color = '#FFF'
					vm.selected_colorpicker_values.footer_background_color = '#f4f7fb'
					vm.selected_colorpicker_values.primary_color = '#12D488'
					vm.selected_colorpicker_values.primary_background_color = '#e2faf1'
					vm.selected_colorpicker_values.label_title_color = '#202C45'
					vm.selected_colorpicker_values.content_color = '#535D71'
					vm.selected_colorpicker_values.price_button_text_color = '#fff'
					vm.selected_font_values.title_font_size = '16'
					vm.selected_font_values.title_font_family = 'Poppins'
					vm.selected_font_values.content_font_size = '16',
					vm.selected_font_values.content_font_family = 'Poppins'
				},
				bpa_reset_formsettings(){
					const vm = this
					vm.booking_form_settings.hide_category_service_selection = false
					vm.booking_form_settings.hide_next_previous_button = false
					vm.booking_form_settings.hide_already_booked_slot = false
					vm.booking_form_settings.booking_form_tabs_position = 'left'
					vm.booking_form_settings.goback_button_text = '<?php esc_html_e( 'Go Back', 'bookingpress-appointment-booking' ); ?>'
					vm.booking_form_settings.next_button_text = '<?php esc_html_e( 'Next', 'bookingpress-appointment-booking' ); ?>'
					vm.booking_form_settings.book_appointment_btn_text = '<?php esc_html_e( 'Book Appointment', 'bookingpress-appointment-booking' ); ?>'
					vm.booking_form_settings.default_date_format = '<?php echo get_option( 'date_format' ); ?>'
				},
				bpa_save_booking_form_settings_data(){
					const vm2 = this
					var postData = []
					postData.action = 'bookingpress_save_form_settings'
					postData.tab_container_data = vm2.tab_container_data
					postData.category_container_data = vm2.category_container_data
					postData.service_container_data = vm2.service_container_data
					postData.timeslot_container_data = vm2.timeslot_container_data
					postData.colorpicker_values = vm2.selected_colorpicker_values
					postData.font_values = vm2.selected_font_values
					postData.booking_form_settings = vm2.booking_form_settings
					postData.summary_container_data = vm2.summary_container_data
					postData._wpnonce = '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>'
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						if(response.data.variant == 'error'){
							vm2.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: response.data.variant,
								customClass: response.data.variant+'_notification',
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
				bpa_save_field_settings_data(){
					const vm2 = this
					var postData = []
					postData.action = 'bookingpress_save_field_settings'
					postData.field_settings = vm2.field_settings_fields
					postData._wpnonce = '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>'
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						if(response.data.variant == 'error'){
							vm2.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: response.data.variant,
								customClass: response.data.variant+'_notification',								
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
				bpa_save_field_my_booking_data() {
					const vm2 = this
					var postData = []
					postData.action = 'bookingpress_save_my_booking_settings'
					postData.my_booking_field_settings = vm2.my_booking_field_settings
					postData.my_booking_selected_font_values = vm2.my_booking_selected_font_values
					postData.my_booking_selected_colorpicker_values = vm2.my_booking_selected_colorpicker_values					
					postData._wpnonce = '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>'
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						if(response.data.variant == 'error'){
							vm2.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: response.data.variant,
								customClass: response.data.variant+'_notification',
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
				bpa_save_customize_settings(){
					const vm = this
					vm.is_display_save_loader = '1'
					vm.is_disabled = 1
					vm.bpa_save_booking_form_settings_data()
					vm.bpa_save_field_settings_data()
					vm.bpa_save_field_my_booking_data()				

					setTimeout(function(){
						vm.is_display_save_loader = '0'
						vm.is_disabled = 0
						vm.$notify({
							title: '<?php esc_html_e( 'Success', 'bookingpress-appointment-booking' ); ?>',
							message: '<?php esc_html_e( 'Customization settings saved successfully', 'bookingpress-appointment-booking' ); ?>',
							type: 'success',
							customClass: 'success_notification',
							duration:<?php echo intval($bookingpress_notification_duration); ?>,
						});
					}, 3000);
				},
				bookingpress_load_booking_form_data(){
					const vm2 = this
					var postData = []
					postData.action = 'bookingpress_load_bookingform_data'
					postData._wpnonce = '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>'
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						if(response.data.variant == 'error'){
							vm2.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: response.data.variant,
								customClass: response.data.variant+'_notification',
								duration:<?php echo intval($bookingpress_notification_duration); ?>,
							});
						}else{
							vm2.tab_container_data = response.data.formdata.tab_container_data
							vm2.category_container_data = response.data.formdata.category_container_data
							vm2.service_container_data = response.data.formdata.service_container_data
							vm2.timeslot_container_data = response.data.formdata.timeslot_container_data
							vm2.selected_colorpicker_values = response.data.formdata.colorpicker_values
							vm2.selected_font_values = response.data.formdata.font_values
							vm2.booking_form_settings = response.data.formdata.booking_form_settings
							vm2.summary_container_data = response.data.formdata.summary_container_data
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
				bookingpress_load_field_settings_data(){
					const vm2 = this
					var postData = []
					postData.action = 'bookingpress_load_field_settings'
					postData._wpnonce = '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>'
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						if(response.data.variant == 'error'){
							vm2.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: response.data.variant,
								customClass: response.data.variant+'_notification',
								duration:<?php echo intval($bookingpress_notification_duration); ?>,
							});
						}else{
							vm2.field_settings_fields = response.data.field_settings
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
				bookingpress_load_my_booking_data(){
					const vm2 = this
					var postData = []
					postData.action = 'bookingpress_load_my_booking_data'
					postData._wpnonce = '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>'
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						if(response.data.variant == 'error'){
							vm2.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: response.data.variant,
								customClass: response.data.variant+'_notification',
								duration:<?php echo intval($bookingpress_notification_duration); ?>,
							});
						}else{
							vm2.my_booking_selected_colorpicker_values = response.data.formdata.colorpicker_values
							vm2.my_booking_selected_font_values = response.data.formdata.font_values
							vm2.my_booking_field_settings = response.data.formdata.booking_form_settings
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
				updateFieldPos(e){
					const vm2 = this
					var field_pos_update_id = e.draggedContext.element.id
					var old_index = e.draggedContext.index
					var new_index = e.draggedContext.futureIndex
					var postData = []
					postData.action = 'bookingpress_update_field_position'
					postData.old_index = old_index
					postData.new_index = new_index
					postData.update_id = field_pos_update_id
					postData._wpnonce = '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>'
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						if(response.data.variant == 'error'){
							vm2.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: response.data.variant,
								customClass: response.data.variant+'_notification',
								duration:<?php echo intval($bookingpress_notification_duration); ?>,
							});
						}
						vm2.bookingpress_load_field_settings_data()
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
				bpa_reset_mybookingform(){
					const vm = this
					vm.my_booking_selected_colorpicker_values.background_color = '#FFF'
					vm.my_booking_selected_colorpicker_values.row_background_color = '#f4f7fb'
					vm.my_booking_selected_colorpicker_values.label_title_color = '#202C45'
					vm.my_booking_selected_colorpicker_values.content_color = '#535D71'
					vm.my_booking_selected_font_values.title_font_size = '16'
					vm.my_booking_selected_font_values.title_font_family = 'Poppins'
					vm.my_booking_selected_font_values.content_font_size = '16'
					vm.my_booking_selected_font_values.content_font_family = 'Poppins'
				},
				bpa_reset_content_settings(){
					const vm = this
					vm.my_booking_field_settings.hide_customer_details = false
					vm.my_booking_field_settings.hide_search_bar = false
					vm.my_booking_field_settings.allow_to_cancel_appointment = true
					vm.my_booking_field_settings.Default_date_formate = '<?php echo get_option( 'date_format' ); ?>'
				},
				bookingpress_set_date_format(format) {					
					const vm = this
					var postData = []
					postData.action = 'bookingpress_set_date_format'
					postData.format = format
					postData._wpnonce = '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>'
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						vm.my_booking_date_text.bookingpress_date_format_1 = response.data.bookingpress_date_format_1
						vm.my_booking_date_text.bookingpress_date_format_2 = response.data.bookingpress_date_format_2
						vm.my_booking_date_text.bookingpress_date_format_3 = response.data.bookingpress_date_format_3
						vm.my_booking_date_text.bookingpress_date_format_4 = response.data.bookingpress_date_format_4
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
				closeFieldSettings(field_name){
					//this.field_settings_fields[field_name].is_edit = 0
					var field_settings = this.field_settings_fields
					field_settings.forEach(function(item, index, arr){
						item.is_edit = 0
					});
				},
			<?php
		}

		function bookingpress_dynamic_onload_methods_func() {
			?>
				const vm = this
				vm.bookingpress_load_booking_form_data()
				vm.bookingpress_load_field_settings_data()
				vm.bookingpress_load_my_booking_data()
			<?php
		}

		function bookingpress_dynamic_computed_methods_func() {
			?>
			<?php
		}

		function bookingpress_load_customize_dynamic_view_func() {
			$bookingpress_load_file_name = BOOKINGPRESS_VIEWS_DIR . '/customize/manage_customize.php';
			$bookingpress_load_file_name = apply_filters( 'bookingpress_modify_customize_view_file_path', $bookingpress_load_file_name );
			require $bookingpress_load_file_name;
		}

		function bookingpress_dynamic_data_fields_func() {
			global $bookingpress_customize_vue_data_fields, $BookingPress, $bookingpress_global_options, $tbl_bookingpress_form_fields, $tbl_bookingpress_customize_settings,$wpdb;
			$default_daysoff_details = $BookingPress->bookingpress_get_default_dayoff_dates();
			$disabled_date           = implode( ',', $default_daysoff_details );
			$bookingpress_customize_vue_data_fields['days_off_disabled_dates'] = $disabled_date;

			// Load fonts options
			$bookingpress_default_fonts_list = $bookingpress_global_options->bookingpress_get_default_fonts();
			$bookingpress_google_fonts_list  = $bookingpress_global_options->bookingpress_get_google_fonts();
			$bookingpress_fonts_list         = array(
				array(
					'label'   => __( 'Default Fonts', 'bookingpress-appointment-booking' ),
					'options' => $bookingpress_default_fonts_list,
				),
				array(
					'label'   => __( 'Google Fonts', 'bookingpress-appointment-booking' ),
					'options' => $bookingpress_google_fonts_list,
				),
			);

			$bookingpress_customize_vue_data_fields['fonts_list'] = $bookingpress_fonts_list;

			$bookingpress_date_format = $wpdb->get_results( "SELECT bookingpress_setting_value FROM {$tbl_bookingpress_customize_settings} WHERE bookingpress_setting_type = 'booking_my_booking' AND bookingpress_setting_name = 'Default_date_formate'", ARRAY_A );

			if ( ! empty( $bookingpress_date_format ) ) {
				$settings_value       = esc_html( $bookingpress_date_format[0]['bookingpress_setting_value'] );
				$my_booking_date_text = array(
					'bookingpress_date_format_1' => 'Thursday, ' . date( $settings_value, strtotime( '2021-10-25' ) ),
					'bookingpress_date_format_2' => date( $settings_value, strtotime( '2021-10-26' ) ),
					'bookingpress_date_format_3' => 'Friday, ' . date( $settings_value, strtotime( '2021-10-25' ) ),
					'bookingpress_date_format_4' => date( $settings_value, strtotime( '2021-10-26' ) ),
				);
				$bookingpress_customize_vue_data_fields['my_booking_date_text'] = $my_booking_date_text;
			}
			$bookingpress_customize_vue_data_fields = apply_filters( 'bookingpress_customize_add_dynamic_data_fields', $bookingpress_customize_vue_data_fields );
			echo json_encode( $bookingpress_customize_vue_data_fields );
		}

		function bookingpress_generate_customize_css_func() {
			$bookingpress_customize_css_content = '';
			$bookingpress_customize_css_key = get_option('bookingpress_custom_css_key', true);
			if ( $_POST['action'] == 'bookingpress_save_my_booking_settings' && ! empty( $_POST['my_booking_selected_colorpicker_values'] ) ) {

				$shortcode_background_color = sanitize_text_field( $_POST['my_booking_selected_colorpicker_values']['background_color'] );
				$raw_background_color       = sanitize_text_field( $_POST['my_booking_selected_colorpicker_values']['row_background_color'] );
				$label_title_color          = sanitize_text_field( $_POST['my_booking_selected_colorpicker_values']['label_title_color'] );
				$content_color              = sanitize_text_field( $_POST['my_booking_selected_colorpicker_values']['content_color'] );

				$title_font_size   = sanitize_text_field( $_POST['my_booking_selected_font_values']['title_font_size'] ) . 'px';
				$title_font_family = sanitize_text_field( $_POST['my_booking_selected_font_values']['title_font_family'] );

				$content_font_size   = sanitize_text_field( $_POST['my_booking_selected_font_values']['content_font_size'] ) . 'px';
				$content_font_family = sanitize_text_field( $_POST['my_booking_selected_font_values']['content_font_family'] );

				$bookingpress_customize_css_content .= "
					.bpa-front-default-card{
						background: " . $shortcode_background_color . ' !important;
					}
					.bpa-front-my-appointments-container .bpa-front-ma-list--item-row .bpa-front-ma-list--item__item-card{
						background: ' . $raw_background_color . '!important;
						border: 1px solid ' . $raw_background_color . ' !important;
					}';

				$bookingpress_customize_css_content .= '
					.bpa-front-my-appointments-container .bpa-front-module-heading, .bpa-front-my-appointments-container .bpa-front-ma-list--item-row .bpa-front-ma-list--item__heading h6{
						color: ' . $label_title_color . ' !important;
						font-size: ' . $title_font_size . ' !important;
					}';

				if ( $title_font_family != 'Inherit Fonts' ) {
					$bookingpress_customize_css_content .= '.bpa-front-my-appointments-container .bpa-front-module-heading, .bpa-front-my-appointments-container .bpa-front-ma-list--item-row .bpa-front-ma-list--item__heading h6{
						font-family: ' . $title_font_family . ' !important;
					}';
				}

				$bookingpress_customize_css_content .= '
					.bpa-front-my-appointments-container .bpa-front-ma-list--item-row .bpa-front-ma-list--item__item-card h4, .bpa-front-my-appointments-container .bpa-front-ma-list--item-row .bpa-front-ma-list--item__item-card p, .bpa-front-my-appointments-container .bpa-front-pill{
						color: ' . $content_color . ' !important;
						font-size: ' . $content_font_size . ' !important;
						
					}';

				$bookingpress_customize_css_content .= '
					.bpa-front-my-appointments-container .bpa-front-ma-header__profile-dropdown .bpa-front-ma-header__profile-dropdown--body h4, .bpa-front-my-appointments-container .bpa-front-ma-header__profile-dropdown .bpa-front-ma-header__profile-dropdown--body p{
						color: ' . $content_color . ' !important;
						font-size: ' . $content_font_size . ' !important;
					}';

				if ( $content_font_family != 'Inherit Fonts' ) {
					$bookingpress_customize_css_content .= '.bpa-front-my-appointments-container .bpa-front-ma-list--item-row .bpa-front-ma-list--item__item-card h4, .bpa-front-my-appointments-container .bpa-front-ma-list--item-row .bpa-front-ma-list--item__item-card p, .bpa-front-my-appointments-container .bpa-front-pill{
						font-family: ' . $content_font_family . ' !important;	
					}';

					$bookingpress_customize_css_content .= '.bpa-front-my-appointments-container .bpa-front-ma-header__profile-dropdown .bpa-front-ma-header__profile-dropdown--body h4, .bpa-front-my-appointments-container .bpa-front-ma-header__profile-dropdown .bpa-front-ma-header__profile-dropdown--body p{
						font-family: ' . $content_font_family . ' !important;
					}';

					$bookingpress_customize_css_content .= '.bpa-front-data-empty-view--my-bookings h4, .bpa-front-ma-lists .bpa-tf-btn-group .bpa-front-btn, .bpa-front-form-control--date-range-picker.el-date-editor, .el-date-range-picker{
						font-family: ' . $content_font_family . ' !important;
					}';
				}

				// Get custom CSS value
				$bookingpress_customize_css_content .= sanitize_textarea_field( $_POST['my_booking_selected_colorpicker_values']['custom_css'] );

				if ( ! function_exists( 'WP_Filesystem' ) ) {
					require_once ABSPATH . 'wp-admin/includes/file.php';
				}
				WP_Filesystem();
				global $wp_filesystem;
				$wp_upload_dir = wp_upload_dir();
				$target_path   = $wp_upload_dir['basedir'] . '/bookingpress/bookingpress_front_mybookings_custom_'.$bookingpress_customize_css_key.'.css';
				$result        = $wp_filesystem->put_contents( $target_path, $bookingpress_customize_css_content, 0777 );
			} else {
				$shortcode_background_color        = sanitize_text_field( $_POST['colorpicker_values']['background_color'] );
				$shortcode_footer_background_color = sanitize_text_field( $_POST['colorpicker_values']['footer_background_color'] );
				$primary_color                     = sanitize_text_field( $_POST['colorpicker_values']['primary_color'] );
				$primary_alpha_color               = sanitize_text_field( $_POST['colorpicker_values']['primary_background_color'] );

				$title_label_color = sanitize_text_field( $_POST['colorpicker_values']['label_title_color'] );
				$title_font_size   = sanitize_text_field( $_POST['font_values']['title_font_size'] ) . 'px';
				$title_font_family = sanitize_text_field( $_POST['font_values']['title_font_family'] );

				$content_color       = sanitize_text_field( $_POST['colorpicker_values']['content_color'] );
				$price_button_text_content_color = sanitize_text_field( $_POST['colorpicker_values']['price_button_text_color'] );
				$content_font_size   = sanitize_text_field( $_POST['font_values']['content_font_size'] ) . 'px';
				$content_font_family = sanitize_text_field( $_POST['font_values']['content_font_family'] );
				$hex = $primary_color;								
				list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");				
				$box_shadow_color = "0 4px 8px rgba($r,$g,$b,0.06), 0 8px 16px rgba($r,$g,$b,0.16)";								
				
				$bookingpress_customize_css_content .= '.bpa-front-tabs {
						--bpa-pt-main-green: ' . $primary_color . ' !important;
						--bpa-pt-main-green-darker: ' . $primary_color . ' !important;
						--bpa-pt-main-green-alpha-12: ' . $primary_alpha_color . ' !important;
					}
					.bpa-front-tabs .bpa-front-tab-menu, .bpa-front-tabs .bpa-front-module--bs-amount-details{
						background-color: ' . $shortcode_background_color . ' !important;
					}
					.bpa-front-tabs .bpa-front-default-card{
						background-color: ' . $shortcode_background_color . ' !important;
					}
					.bpa-front-tabs .bpa-front-tabs--foot{
						background-color: ' . $shortcode_footer_background_color . ' !important;
					}';

				$bookingpress_customize_css_content .= '.bpa-front-tabs .bpa-front-module-heading, .bpa-front-tabs .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si__card-body--heading, .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-summary-content .bpa-front-module--bs-summary-content-item span, .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-head h4, .bpa-front-tabs .bpa-front-module--payment-methods .bpa-front-module--pm-head .__content h4, .bpa-front-tabs .bpa-front--dt__time-slots .bpa-front--dt__ts-heading h4, .bpa-front-tabs .bpa-front--dt__time-slots .bpa-front--dt__ts-body .bpa-front--dt__ts-body--row h5{
						color: ' . $title_label_color . ' !important;
						font-size: ' . $title_font_size . ' !important;
					}';

				if ( $title_font_family != 'Inherit Fonts' ) {
					$bookingpress_customize_css_content .= '
						.bpa-front-tabs .bpa-front-module-heading, .bpa-front-tabs .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si__card-body--heading, .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-summary-content .bpa-front-module--bs-summary-content-item span, .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-head h4, .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-amount-details h4, .bpa-front-tabs .bpa-front-module--payment-methods .bpa-front-module--pm-head .__content h4, .bpa-front-tabs .bpa-front--dt__time-slots .bpa-front--dt__ts-heading h4, .bpa-front-tabs .bpa-front--dt__time-slots .bpa-front--dt__ts-body .bpa-front--dt__ts-body--row h5 {
							font-family: ' . $title_font_family . ' !important;
						}';
				}

				$bookingpress_customize_css_content .= '.bpa-front-tabs .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si-cb__specs .bpa-front-si-cb__specs-item p, .bpa-front-tabs .bpa-front--dt__calendar .vuecal.vuecal--month-view .vuecal__cell .vuecal__cell-date, .bpa-front-tabs .bpa-front--dt__calendar .vuecal.vuecal--month-view .vuecal__title-bar span, .bpa-front-tabs .bpa-front--dt__calendar .vuecal.vuecal--month-view .vuecal__weekdays-headings .weekday-label span, .bpa-front-tabs .bpa-front--dt__time-slots .bpa-front--dt__ts-body .bpa-front--dt__ts-body--row .bpa-front--dt__ts-body--items .bpa-front--dt__ts-body--item, .bpa-front-tabs .bpa-front-form-control input, .bpa-front-tabs .bpa-front-form-control .el-textarea__inner, .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-summary-content .bpa-front-module--bs-summary-content-item h4, .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-head p, .bpa-front-tabs .bpa-front-ci-item-title,.bpa-front-tabs .el-form-item__label, .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si-cb__specs .bpa-front-si-cb__specs-item p strong, .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .--bpa-is-desc {
						color: ' . $content_color . ' !important;
						font-size: ' . $content_font_size . ' !important;
					}

					.bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si-cb__specs .bpa-front-si-cb__specs-item p strong.--is-service-price{
						color: ' . $price_button_text_content_color . ' !important;
						font-size: ' . $content_font_size . ' !important;
					}

					.bpa-front-tabs .bpa-front-tabs--vertical-left .bpa-front-tab-menu .bpa-front-tab-menu--item:hover{
						color: ' . $content_color . ' !important;
						font-size: ' . $content_font_size . ' !important;
					}

				';

				if ( $content_font_family != 'Inherit Fonts' ) {
					$bookingpress_customize_css_content .= '
						.bpa-front-tabs .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si-cb__specs .bpa-front-si-cb__specs-item p, .bpa-front-tabs .bpa-front--dt__calendar .vuecal.vuecal--month-view .vuecal__cell .vuecal__cell-date, .bpa-front-tabs .bpa-front--dt__calendar .vuecal.vuecal--month-view .vuecal__title-bar span, .bpa-front-tabs .bpa-front--dt__calendar .vuecal.vuecal--month-view .vuecal__weekdays-headings .weekday-label span, .bpa-front-tabs .bpa-front--dt__time-slots .bpa-front--dt__ts-body .bpa-front--dt__ts-body--row .bpa-front--dt__ts-body--items .bpa-front--dt__ts-body--item, .bpa-front-tabs .bpa-front-form-control input, .bpa-front-tabs .bpa-front-form-control .el-textarea__inner, .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-summary-content .bpa-front-module--bs-summary-content-item h4, .bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-head p, .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .bpa-front-si-cb__specs .bpa-front-si-cb__specs-item p strong, .bpa-front-tabs .bpa-front-ci-item-title,.bpa-front-tabs .el-form-item__label, .bpa-front-module--service-item .bpa-front-si-card .bpa-front-si__card-body .--bpa-is-desc{
							font-family: ' . $content_font_family . ' !important;
						}

						.bpa-front-tabs .bpa-front-tab-menu--item, .bpa-front-tabs .bpa-front-tabs--vertical-left .bpa-front-tab-menu .bpa-front-tab-menu--item:hover{
							font-family: ' . $content_font_family . ' !important;	
						}

						.el-form-item__error{
							font-family: ' . $content_font_family . ' !important;
						}
					';
				}

				$bookingpress_customize_css_content .= '
					.bpa-front-tabs--foot .bpa-front-btn--primary span, .bpa-front-tabs--foot .bpa-front-btn--primary strong {
						color: ' . $price_button_text_content_color . ' !important;
					}
					.bpa-front--dt__ts-body--item.__bpa-is-selected {
						background-color: ' . $primary_alpha_color . ' !important;
					}
					.bpa-front-tabs .vuecal__cell--out-of-scope .vuecal__cell-date{
						color: var(--bpa-gt-gray-400) !important;
					}
					.bpa-front-tabs .vuecal.vuecal--month-view .vuecal__title-bar .vuecal__arrow{
						color: ' . $content_color . ' !important;
					}
				';
				$bookingpress_customize_css_content .= '
				.bpa-front-tabs .bpa-front-module--booking-summary .bpa-front-module--bs-amount-details .bpa-front-module--bs-ad--price {
					color: ' . $primary_color . ' !important;
				}';

				$bookingpress_customize_css_content .= '
				.bpa-front-tabs--vertical-left .bpa-front-tab-menu .bpa-front-tab-menu--item.__bpa-is-active span {					
					box-shadow: ' . $box_shadow_color . ' !important;
				}';

				// Get custom CSS value
				$bookingpress_customize_css_content .= sanitize_textarea_field( $_POST['colorpicker_values']['custom_css'] );

				if ( ! function_exists( 'WP_Filesystem' ) ) {
					require_once ABSPATH . 'wp-admin/includes/file.php';
				}
				WP_Filesystem();
				global $wp_filesystem;
				$wp_upload_dir = wp_upload_dir();
				$target_path   = $wp_upload_dir['basedir'] . '/bookingpress/bookingpress_front_custom_'.$bookingpress_customize_css_key.'.css';
				$result        = $wp_filesystem->put_contents( $target_path, $bookingpress_customize_css_content, 0777 );
			}
		}
	}

	global $bookingpress_customize, $bookingpress_customize_vue_data_fields;
	$bookingpress_customize = new bookingpress_customize();

	global $bookingpress_global_options;
	$bookingpress_options      = $bookingpress_global_options->bookingpress_global_options();
	$bookingpress_country_list = json_decode( $bookingpress_options['country_lists'] );

	$bookingpress_customize_vue_data_fields = array(
		'is_display_loader'                      => '0',
		'is_display_save_loader'                 => '0',
		'is_disabled'                            => 0,
		'dragging'                               => false,
		'activeTabName'                          => 'booking_form',
		'formActiveTab'                          => '1',
		'appointment_date_range'                 => array( date( 'Y-m-d', strtotime( '-3 Day' ) ), date( 'Y-m-d', strtotime( '+3 Day' ) ) ),
		'tab_container_data'                     => array(
			'is_edit_service'         => false,
			'is_edit_date_time'       => false,
			'is_edit_basic_details'   => false,
			'is_edit_summary'         => false,
			'is_edit_service_2'       => false,
			'is_edit_date_time_2'     => false,
			'is_edit_basic_details_2' => false,
			'is_edit_summary_2'       => false,
			'is_edit_service_3'       => false,
			'is_edit_date_time_3'     => false,
			'is_edit_basic_details_3' => false,
			'is_edit_summary_3'       => false,
			'is_edit_service_4'       => false,
			'is_edit_date_time_4'     => false,
			'is_edit_basic_details_4' => false,
			'is_edit_summary_4'       => false,
			'service_title'           => __( 'Services', 'bookingpress-appointment-booking' ),
			'datetime_title'          => __( 'Date & Time', 'bookingpress-appointment-booking' ),
			'basic_details_title'     => __( 'Basic Details', 'bookingpress-appointment-booking' ),
			'summary_title'           => __( 'Summary', 'bookingpress-appointment-booking' ),
		),
		'category_container_data'                => array(
			'editCategoryTitlePopup' => false,
			'is_edit_category_title' => false,
			'category_title'         => __( 'Select Category', 'bookingpress-appointment-booking' ),
		),
		'service_container_data'                 => array(
			'editServiceTitlePopup' => false,
			'is_edit_service_title' => false,
			'service_heading_title' => __( 'Select Service', 'bookingpress-appointment-booking' ),
			'default_image_url'     => BOOKINGPRESS_URL . '/images/placeholder-img.jpg',
		),
		'timeslot_container_data'                => array(
			'is_edit_timeslot' => false,
			'timeslot_text'    => __( 'Time Slot', 'bookingpress-appointment-booking' ),
		),
		'summary_container_data'                 => array(
			'is_edit_summary_content'       => false,
			'summary_content_text'          => __( 'Your appointment booking summary', 'bookingpress-appointment-booking' ),
			'is_edit_select_payment_method' => false,
			'payment_method_text'           => __( 'Select Payment Method', 'bookingpress-appointment-booking' ),
		),
		'bookingpress_shortcode_form'            => array(
			'selected_category'         => 'low_consultancy',
			'selected_service'          => 'chronic_disease_management_1',
			'selected_date'             => date( 'Y-m-d', current_time( 'timestamp' ) ),
			'selected_time'             => '17:00',
			'customer_name'             => '',
			'customer_selected_country' => 'us',
			'cusomter_phone'            => '',
			'customer_email'            => '',
			'customer_note'             => '',
			'phone_countries_details'   => $bookingpress_country_list,
		),
		'selected_colorpicker_values'            => array(
			'background_color'         => '#fff',
			'footer_background_color'  => '#f4f7fb',
			'primary_color'            => '#12D488',
			'primary_background_color' => '#e2faf1',
			'label_title_color'        => '#202C45',
			'content_color'            => '#535D71',
			'price_button_text_color'  => '#fff',
			'custom_css'               => '',
		),
		'selected_font_values'                   => array(
			'title_font_size'     => '16',
			'title_font_family'   => 'Poppins',
			'content_font_size'   => '16',
			'content_font_family' => 'Poppins',
		),
		'booking_form_settings'                  => array(
			'hide_category_service_selection' => false,
			'hide_next_previous_button'       => false,
			'hide_already_booked_slot'        => false,
			'display_service_description'     => false,
			'booking_form_tabs_position'      => 'left',
			'goback_button_text'              => __( 'Go Back', 'bookingpress-appointment-booking' ),
			'next_button_text'                => __( 'Next', 'bookingpress-appointment-booking' ),
			'book_appointment_btn_text'       => __( 'Book Appointment', 'bookingpress-appointment-booking' ),
			'default_date_format'             => 'F j, Y',
		),
		'draggable_field_setting_fields'         => array(),
		'field_settings_fields'                  => array(
			'fullname'      => array(
				'field_name'     => 'fullname',
				'field_type'     => 'Text',
				'is_edit'        => 0,
				'is_required'    => 0,
				'label'          => __( 'Fullname', 'bookingpress-appointment-booking' ),
				'placeholder'    => __( 'Enter your full name', 'bookingpress-appointment-booking' ),
				'error_message'  => __( 'Please enter your full name', 'bookingpress-appointment-booking' ),
				'is_hide'        => 0,
				'field_position' => 1,
			),
			'firstname'     => array(
				'field_name'     => 'firstname',
				'field_type'     => 'Text',
				'is_edit'        => 0,
				'is_required'    => 0,
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
				'is_required'    => 0,
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
				'is_required'    => 0,
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
		),
		'my_booking_selected_colorpicker_values' => array(
			'background_color'     => '#fff',
			'row_background_color' => '#f4f7fb',
			'label_title_color'    => '#727E95',
			'content_color'        => '#727e95',
			'custom_css'           => '',
		),
		'my_booking_selected_font_values'        => array(
			'title_font_size'     => '14',
			'title_font_family'   => 'Poppins',
			'content_font_size'   => '14',
			'content_font_family' => 'Poppins',
		),
		'my_booking_field_settings'              => array(
			'is_edit_mybooking_title'     => false,
			'mybooking_title_text'        => __( 'My Bookings', 'bookingpress-appointment-booking' ),
			'hide_customer_details'       => false,
			'hide_search_bar'             => false,
			'allow_to_cancel_appointment' => true,
			'Default_date_formate'        => 'F j, Y',
		),
		'my_booking_date_text'                   => array(
			'bookingpress_date_format_1' => 'Thursday, ' . date( 'F j, Y', strtotime( '2021-10-25' ) ),
			'bookingpress_date_format_2' => date( 'F j, Y', strtotime( '2021-10-26' ) ),
			'bookingpress_date_format_3' => 'Friday, ' . date( 'F j, Y', strtotime( '2021-10-25' ) ),
			'bookingpress_date_format_4' => date( 'F j, Y', strtotime( '2021-10-26' ) ),
		),
	);
}
