<?php
if ( ! class_exists( 'bookingpress_appointment_bookings' ) ) {
	class bookingpress_appointment_bookings {
		var $bookingpress_form_category;
		var $bookingpress_form_service;
		var $bookingpress_hide_category_service;
		var $bookingpress_hide_next_previous_buttons;
		var $bookingpress_default_date_format;
		var $bookingpress_default_time_format;
		var $bookingpress_form_fields_error_msg_arr;
		var $bookingpress_form_fields_new;
		var $bookingpress_is_service_load_from_url;

		var $bookingpress_mybooking_random_id;
		var $bookingpress_mybooking_hide_customer_details;
		var $bookingpress_mybooking_hide_searchbar;
		var $bookingpress_mybooking_allow_cancel_appointments;
		var $bookingpress_mybooking_default_date_format;
		var $bookingpress_mybooking_customer_username;
		var $bookingpress_mybooking_customer_email;
		var $bookingpress_mybooking_login_user_id;
		var $bookingpress_mybooking_wpuser_id;

		function __construct() {
			$this->bookingpress_form_category              = 0;
			$this->bookingpress_form_service               = 0;
			$this->bookingpress_hide_category_service      = 0;
			$this->bookingpress_hide_next_previous_buttons = 0;
			$this->bookingpress_default_date_format        = get_option( 'date_format' );
			$this->bookingpress_default_time_format        = get_option('time_format');
			$this->bookingpress_form_fields_error_msg_arr  = array();
			$this->bookingpress_form_fields_new            = array();
			$this->bookingpress_is_service_load_from_url   = 0;

			$this->bookingpress_mybooking_hide_customer_details     = 0;
			$this->bookingpress_mybooking_hide_searchbar            = 0;
			$this->bookingpress_mybooking_allow_cancel_appointments = 1;
			$this->bookingpress_mybooking_default_date_format       = get_option( 'date_format' );
			$this->bookingpress_mybooking_customer_username         = '';
			$this->bookingpress_mybooking_customer_email            = '';
			$this->bookingpress_mybooking_wpuser_id                 = 0;

			add_action( 'bookingpress_front_booking_dynamic_data_fields', array( $this, 'bookingpress_booking_dynamic_data_fields_func' ), 10, 2 );

			add_action( 'bookingpress_front_booking_dynamic_helper_vars', array( $this, 'bookingpress_booking_dynamic_helper_vars_func' ) );
			add_action( 'bookingpress_front_booking_dynamic_on_load_methods', array( $this, 'bookingpress_booking_dynamic_on_load_methods_func' ) );
			add_action( 'bookingpress_front_booking_dynamic_vue_methods', array( $this, 'bookingpress_booking_dynamic_vue_methods_func' ) );

			add_action( 'media_buttons', array( $this, 'bookingpress_insert_shortcode_button' ), 20 );

			add_shortcode( 'bookingpress_form', array( $this, 'bookingpress_front_booking_form' ) );
			add_shortcode( 'bookingpress_company_avatar', array( $this, 'bookingpress_company_avatar_func' ) );
			add_shortcode( 'bookingpress_company_name', array( $this, 'bookingpress_company_name_func' ) );
			add_shortcode( 'bookingpress_company_website', array( $this, 'bookingpress_company_website_func' ) );
			add_shortcode( 'bookingpress_company_address', array( $this, 'bookingpress_company_address_func' ) );
			add_shortcode( 'bookingpress_company_phone', array( $this, 'bookingpress_company_phone_func' ) );
			add_shortcode( 'bookingpress_appointment_service', array( $this, 'bookingpress_appointment_service_func' ) );
			add_shortcode( 'bookingpress_appointment_datetime', array( $this, 'bookingpress_appointment_datetime_func' ) );
			add_shortcode( 'bookingpress_appointment_customername', array( $this, 'bookingpress_appointment_customername_func' ) );
			add_shortcode( 'bookingpress_my_appointments', array( $this, 'bookingpress_my_appointments_func' ) );

			add_action( 'wp_ajax_bookingpress_front_get_category_services', array( $this, 'bookingpress_get_category_service_data' ), 10 );
			add_action( 'wp_ajax_nopriv_bookingpress_front_get_category_services', array( $this, 'bookingpress_get_category_service_data' ), 10 );

			add_action( 'wp_ajax_bookingpress_front_get_timings', array( $this, 'bookingpress_get_service_timings' ), 10 );
			add_action( 'wp_ajax_nopriv_bookingpress_front_get_timings', array( $this, 'bookingpress_get_service_timings' ), 10 );

			add_action( 'wp_ajax_bookingpress_front_save_appointment_booking', array( $this, 'bookingpress_save_appointment_booking_func' ), 10 );
			add_action( 'wp_ajax_nopriv_bookingpress_front_save_appointment_booking', array( $this, 'bookingpress_save_appointment_booking_func' ), 10 );

			add_action( 'wp_ajax_bookingpress_before_book_appointment', array( $this, 'bookingpress_before_book_appointment_func' ), 10 );
			add_action( 'wp_ajax_nopriv_bookingpress_before_book_appointment', array( $this, 'bookingpress_before_book_appointment_func' ), 10 );

			add_action( 'wp_ajax_bookingpress_cancel_appointment', array( $this, 'bookingpress_cancel_appointment' ), 10 );
			add_action( 'wp', array( $this, 'bookingpress_cancel_appointment_func' ), 10 );

			/* fornt-end mybooking */

			add_action( 'bookingpress_front_appointments_dynamic_data_fields', array( $this, 'bookingpress_front_appointments_dynamic_data_fields_func' ) );
			add_action( 'bookingpress_front_appointments_dynamic_helper_vars', array( $this, 'bookingpress_front_appointments_dynamic_helper_vars_func' ) );
			add_action( 'bookingpress_front_appointments_dynamic_on_load_methods', array( $this, 'bookingpress_front_appointments_dynamic_on_load_methods_func' ) );
			add_action( 'bookingpress_front_appointments_dynamic_vue_methods', array( $this, 'bookingpress_front_appointments_dynamic_vue_methods_func' ) );

			add_action( 'wp_ajax_bookingpress_get_customer_appointments', array( $this, 'bookingpress_get_customer_appointments_func' ), 10 );
		}
		function bookingpress_insert_shortcode_button( $content ) {
			global $bookingpress_global_options;
			$allowed_pages_for_media_button = array( 'post.php', 'post-new.php' );

			if ( ! in_array( basename( $_SERVER['PHP_SELF'] ), $allowed_pages_for_media_button ) ) {
				return;
			}
			if ( ! isset( $post_type ) ) {
				$post_type = '';
			}
			if ( basename( sanitize_text_field( $_SERVER['PHP_SELF'] ) ) == 'post.php' ) {
				$post_id   = sanitize_text_field( $_REQUEST['post'] );
				$post_type = get_post_type( $post_id );
			}
			if ( basename( sanitize_text_field( $_SERVER['PHP_SELF'] ) ) == 'post-new.php' ) {
				if ( isset( $_REQUEST['post_type'] ) ) {
					$post_type = sanitize_text_field( $_REQUEST['post_type'] );
				} else {
					$post_type = 'post';
				}
			}

			$allowed_post_types = array( 'post', 'page' );

			if ( ! in_array( $post_type, $allowed_post_types ) ) {
				return;
			}
			if ( ! wp_script_is( 'jquery', 'enqueued' ) ) {
				wp_enqueue_script( 'jquery' );
			}
			if ( ! wp_style_is( 'bookingpress_tinymce', 'enqueued' ) ) {
				wp_enqueue_style( 'bookingpress_tinymce', BOOKINGPRESS_URL . '/css/bookingpress_tinymce.css', array(), BOOKINGPRESS_VERSION );
			}
			wp_register_script( 'bookingpress_vue_js', BOOKINGPRESS_URL . '/js/bookingpress_vue.min.js', array(), BOOKINGPRESS_VERSION, 0 );
			wp_register_script( 'bookingpress_element_js', BOOKINGPRESS_URL . '/js/bookingpress_element.js', array( '' ), '2.51.5', 0 );
			wp_register_script( 'bookingpress_element_en_js', BOOKINGPRESS_URL . '/js/bookingpress_element_en.js', array( '' ), '2.51.5', 0 );
			wp_register_script( 'bookingpress_wordpress_vue_helper_js', BOOKINGPRESS_URL . '/js/bookingpress_wordpress_vue_qs_helper.js', array( '' ), '6.5.1', 0 );

			wp_enqueue_script( 'bookingpress_vue_js' );
			wp_enqueue_script( 'bookingpress_element_js' );
			wp_enqueue_script( 'bookingpress_element_en_js' );
			wp_enqueue_script( 'bookingpress_wordpress_vue_helper_js' );

			wp_register_style( 'bookingpress_element_css', BOOKINGPRESS_URL . '/css/bookingpress_element_theme.css', array(), BOOKINGPRESS_VERSION );
			wp_enqueue_style( 'bookingpress_element_css' );

			if ( wp_script_is( 'bookingpress_vue_js', 'enqueued' ) ) {
				$this->bookingpress_insert_shortcode_popup();
			}

			$bookingpress_site_current_language = $bookingpress_global_options->bookingpress_get_site_current_language();

			$bpa_inline_script_data = '         				        					        		
					var lang = ELEMENT.lang.'.$bookingpress_site_current_language.'
					ELEMENT.locale(lang)			
					var app = new Vue({						
						el: "#bookingpress_shortcode_form",
						data() {
							var bookingpress_return_data = {
								open_bookingpress_shortcode_modal: false,
								close_modal_on_esc: true,
								centerDialogVisible: false,
								selected_bookingpress_shortcode: "", 
								append_modal_to_body: true,
							};
							return bookingpress_return_data;			
						},
						mounted(){
						},
						methods: {							
							model_action() {
								const vm= this
								if(vm.open_bookingpress_shortcode_modal == true ) {
									vm.open_bookingpress_shortcode_modal = false;		
								} else {
									vm.open_bookingpress_shortcode_modal = true;
								}					
							},
							bookingpress_open_form_shortcode_popup(){
								this.model_action();
							},
							add_bookingpress_shortcode(){
								const vm = this
								if(vm.selected_bookingpress_shortcode != "") {
									if(tinyMCE.activeEditor != null){
										var editorContent = tinyMCE.activeEditor.getContent()
										editorContent += "["+vm.selected_bookingpress_shortcode+"]"
										tinyMCE.activeEditor.setContent(editorContent)
									}
									else{
										var textEditorContent = document.getElementById("content").innerHTML
										textEditorContent += "\n["+vm.selected_bookingpress_shortcode+"]"
										document.getElementById("content").innerHTML = textEditorContent
									}
									vm.model_action();
								}
							}
						},
					});';

			wp_add_inline_script( 'bookingpress_wordpress_vue_helper_js', $bpa_inline_script_data );
		}

		function bookingpress_insert_shortcode_popup() {
			if ( file_exists( BOOKINGPRESS_VIEWS_DIR . '/bookingpress_tinymce_options_shortcodes.php' ) ) {
				require BOOKINGPRESS_VIEWS_DIR . '/bookingpress_tinymce_options_shortcodes.php';
			}
			?>
			<?php
		}

		function bookingpress_before_book_appointment_func() {
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs,$tbl_bookingpress_customers;
			$response              = array();
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['variant']      = 'error';
				$response['title']        = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']          = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
				$response['redirect_url'] = '';
				wp_send_json( $response );
				die();
			}
			$response['variant']    = 'success';
			$response['title']      = '';
			$response['msg']        = '';
			$response['error_type'] = '';

			$no_service_selected_for_the_booking = $BookingPress->bookingpress_get_settings( 'no_service_selected_for_the_booking', 'message_setting' );

			$no_appointment_date_selected_for_the_booking = $BookingPress->bookingpress_get_settings( 'no_appointment_date_selected_for_the_booking', 'message_setting' );

			$no_appointment_time_selected_for_the_booking = $BookingPress->bookingpress_get_settings( 'no_appointment_time_selected_for_the_booking', 'message_setting' );

			$no_payment_method_is_selected_for_the_booking = $BookingPress->bookingpress_get_settings( 'no_payment_method_is_selected_for_the_booking', 'message_setting' );

			$duplicate_email_address_found = $BookingPress->bookingpress_get_settings( 'duplicate_email_address_found', 'message_setting' );

			$unsupported_currecy_selected_for_the_payment = $BookingPress->bookingpress_get_settings( 'unsupported_currecy_selected_for_the_payment', 'message_setting' );

			$duplidate_appointment_time_slot_found = $BookingPress->bookingpress_get_settings( 'duplidate_appointment_time_slot_found', 'message_setting' );


			$bookingpress_service_price = isset($_REQUEST['appointment_data']['service_price_without_currency']) ? floatval($_REQUEST['appointment_data']['service_price_without_currency']) : 0;

			if ( empty( $_POST['appointment_data']['selected_service'] ) ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( $no_service_selected_for_the_booking, 'bookingpress-appointment-booking' );
				echo json_encode( $response );
				exit();
			}

			if ( empty( $_POST['appointment_data']['selected_date'] ) ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( $no_appointment_date_selected_for_the_booking, 'bookingpress-appointment-booking' );
				echo json_encode( $response );
				exit();
			}

			if ( empty( $_POST['appointment_data']['selected_start_time'] ) || empty( $_POST['appointment_data']['selected_end_time'] ) ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( $no_appointment_time_selected_for_the_booking, 'bookingpress-appointment-booking' );
				echo json_encode( $response );
				exit();
			}

			if ( empty( $_POST['appointment_data']['selected_payment_method'] ) && $bookingpress_service_price > 0 ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( $no_payment_method_is_selected_for_the_booking, 'bookingpress-appointment-booking' );
				echo json_encode( $response );
				exit();
			}

			

			$bookingpress_fullname        = ! empty( $_POST['appointment_data']['customer_name'] ) ? trim( sanitize_text_field( $_POST['appointment_data']['customer_name'] ) ) : '';
			$bookingpress_firstname        = ! empty( $_POST['appointment_data']['customer_firstname'] ) ? trim( sanitize_text_field( $_POST['appointment_data']['customer_firstname'] ) ) : '';
			$bookingpress_lastname         = ! empty( $_POST['appointment_data']['customer_lastname'] ) ? trim( sanitize_text_field( $_POST['appointment_data']['customer_lastname'] ) ) : '';			
			$bookingpress_email            = ! empty( $_POST['appointment_data']['customer_email'] ) ? sanitize_email( $_POST['appointment_data']['customer_email'] ) : '';

			if ( strlen( $bookingpress_fullname ) > 255 ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg'] = esc_html__( 'Fullname is too long...', 'bookingpress-appointment-booking' );
				echo json_encode( $response );
				exit();
			}
			if ( strlen( $bookingpress_firstname ) > 255 ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg'] = esc_html__( 'Firstname is too long...', 'bookingpress-appointment-booking' );
				echo json_encode( $response );
				exit();
			}
			if ( strlen( $bookingpress_lastname ) > 255 ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg'] = esc_html__( 'Lastname is too long...', 'bookingpress-appointment-booking' );
				echo json_encode( $response );
				exit();
			}
			if ( strlen( $bookingpress_email ) > 255 ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg'] = esc_html__( 'Email address is too long...', 'bookingpress-appointment-booking' );
				echo json_encode( $response );
				exit();
			}
			$bookingpress_selected_payment_method = sanitize_text_field( $_POST['appointment_data']['selected_payment_method'] );
			$bookingpress_currency_name           = $BookingPress->bookingpress_get_settings( 'payment_default_currency', 'payment_setting' );
			if ( $bookingpress_selected_payment_method == 'paypal' && ( $bookingpress_currency_name == 'Tanzanian Shilling' || $bookingpress_currency_name == 'Uzbekistan Som' ) ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( $unsupported_currecy_selected_for_the_payment, 'bookingpress-appointment-booking' ) . '.';
				echo json_encode( $response );
				exit();
			}

			$appointment_service_id    = intval( $_POST['appointment_data']['selected_service'] );
			$appointment_selected_date = date( 'Y-m-d', strtotime( sanitize_text_field( $_POST['appointment_data']['selected_date'] ) ) );
			$appointment_start_time    = date( 'H:i:s', strtotime( sanitize_text_field( $_POST['appointment_data']['selected_start_time'] ) ) );

			$is_appointment_exists = $wpdb->get_var( "SELECT COUNT(bookingpress_appointment_booking_id) as total FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_service_id = {$appointment_service_id} AND bookingpress_appointment_date LIKE '{$appointment_selected_date}' AND bookingpress_appointment_time LIKE '{$appointment_start_time}' AND (bookingpress_appointment_status = 'Approved' OR bookingpress_appointment_status = 'Pending')" );
			if ( $is_appointment_exists > 0 ) {
				$booking_already_exists_error_msg = esc_html__( $duplidate_appointment_time_slot_found, 'bookingpress-appointment-booking' );
				$response['variant']              = 'error';
				$response['title']                = 'Error';
				$response['msg']                  = $booking_already_exists_error_msg;
				echo json_encode( $response );
				exit();
			}

			// If selected date is day off then display error.
			$bookingpress_search_query              = preg_quote( $appointment_selected_date, '~' );
			$bookingpress_get_default_daysoff_dates = $BookingPress->bookingpress_get_default_dayoff_dates();
			$bookingpress_search_date               = preg_grep( '~' . $bookingpress_search_query . '~', $bookingpress_get_default_daysoff_dates );
			if ( ! empty( $bookingpress_search_date ) ) {
				$booking_dayoff_msg     = esc_html__( 'Selected date is off day', 'bookingpress-appointment-booking' );
				$booking_dayoff_msg    .= '. ' . esc_html__( 'So please select new date', 'bookingpress-appointment-booking' ) . '.';
				$response['error_type'] = 'dayoff';
				$response['variant']    = 'error';
				$response['title']      = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']        = $booking_dayoff_msg;
				echo json_encode( $response );
				exit();
			}

			

			// If payment gateway is disable then return error
			if ( $bookingpress_selected_payment_method == 'on-site' && $bookingpress_service_price > 0 ) {
				$on_site_payment = $BookingPress->bookingpress_get_settings( 'on_site_payment', 'payment_setting' );
				if ( empty( $on_site_payment ) || ( $on_site_payment == 'false' ) ) {
					$response['variant'] = 'error';
					$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
					$response['msg']     = __( 'On-site payment gateway is not active', 'bookingpress-appointment-booking' ) . '.';
					echo json_encode( $response );
					exit();
				}
			} elseif ( $bookingpress_selected_payment_method == 'paypal' && $bookingpress_service_price > 0 ) {
				$paypal_payment = $BookingPress->bookingpress_get_settings( 'paypal_payment', 'payment_setting' );
				if ( empty( $paypal_payment ) || ( $paypal_payment == 'false' ) ) {
					$response['variant'] = 'error';
					$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
					$response['msg']     = __( 'PayPal payment gateway is not active', 'bookingpress-appointment-booking' ) . '.';
					echo json_encode( $response );
					exit();
				}
			}

			do_action('bookingpress_validate_booking_form', $_POST);

		}

		function bookingpress_cancel_appointment_func() {
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs, $bookingpress_email_notifications;
			$cancel_id    = ! empty( $_REQUEST['bpa_cancel'] ) ? intval( base64_decode( $_REQUEST['bpa_cancel'] ) ) : 0;
			$cancel_token = ! empty( $_REQUEST['cancel_id'] ) ? esc_html( $_REQUEST['cancel_id'] ) : '';
			if ( ! empty( $cancel_id ) && ! empty( $cancel_token ) ) {
				// Get payment log id and insert canceled appointment entry
				$payment_log_data = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_payment_logs} WHERE bookingpress_appointment_booking_ref = {$cancel_id}", ARRAY_A );
				if ( ! empty( $payment_log_data ) ) {
					$bookingpress_customer_data = $BookingPress->get_customer_details( $payment_log_data['bookingpress_customer_id'] );
					$bookingpress_wpuser_id     = $bookingpress_customer_data['bookingpress_wpuser_id'];

					$customer_cancel_token = get_user_meta( $bookingpress_wpuser_id, 'bpa_cancel_id', true );
					if ( ! empty( $customer_cancel_token ) && ( $customer_cancel_token == $cancel_token ) ) {
						$bookingress_customer_email = $payment_log_data['bookingpress_customer_email'];

						$bookingpress_after_canceled_payment_url = $BookingPress->bookingpress_get_settings( 'redirect_url_after_booking_canceled', 'general_setting' );
						$bookingpress_after_canceled_payment_url = ! empty( $bookingpress_after_canceled_payment_url ) ? $bookingpress_after_canceled_payment_url : BOOKINGPRESS_HOME_URL;

						$wpdb->update( $tbl_bookingpress_appointment_bookings, array( 'bookingpress_appointment_status' => 'Cancelled' ), array( 'bookingpress_appointment_booking_id' => $cancel_id ) );

						$bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification( 'Appointment Canceled', $cancel_id, $bookingress_customer_email );

						delete_user_meta( $bookingpress_wpuser_id, 'bpa_cancel_id' );

						wp_redirect( $bookingpress_after_canceled_payment_url );
					}
				}
			}
		}

		function bookingpress_cancel_appointment() {
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs, $bookingpress_email_notifications;
			$response              = array();
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['variant']      = 'error';
				$response['title']        = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']          = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
				$response['redirect_url'] = '';
				wp_send_json( $response );
				die();
			}

			$response['variant'] = 'error';
			$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']     = esc_html__( 'Something Went wrong...', 'bookingpress-appointment-booking' );

			$appointment_cancelled_successfully = $BookingPress->bookingpress_get_settings( 'appointment_cancelled_successfully', 'message_setting' );
			$cancel_id                          = ! empty( $_REQUEST['cancel_id'] ) ? intval( $_REQUEST['cancel_id'] ) : 0;
			if ( ! empty( $cancel_id ) ) {
				$bookingpress_after_canceled_payment_url = $BookingPress->bookingpress_get_settings( 'redirect_url_after_booking_canceled', 'general_setting' );
				$bookingpress_after_canceled_payment_url = ! empty( $bookingpress_after_canceled_payment_url ) ? $bookingpress_after_canceled_payment_url : BOOKINGPRESS_HOME_URL;

				$wpdb->update( $tbl_bookingpress_appointment_bookings, array( 'bookingpress_appointment_status' => 'Cancelled' ), array( 'bookingpress_appointment_booking_id' => $cancel_id ) );

				// Get payment log id and insert canceled appointment entry
				$payment_log_data = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_payment_logs} WHERE bookingpress_appointment_booking_ref = {$cancel_id}", ARRAY_A );
				if ( ! empty( $payment_log_data ) ) {
					$bookingress_customer_email = $payment_log_data['bookingpress_customer_email'];

					$bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification( 'Appointment Canceled', $cancel_id, $bookingress_customer_email );
				}

				$response['variant']      = 'success';
				$response['title']        = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']          = esc_html__( $appointment_cancelled_successfully, 'bookingpress-appointment-booking' );
				$response['redirect_url'] = $bookingpress_after_canceled_payment_url;
			}

			echo json_encode( $response );
			exit();
		}

		function bookingpress_my_appointments_func( $atts, $content, $tag ) {
			if ( ! is_user_logged_in() ) {
				$content .= '
				<div>
					<el-row type="flex">
						<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
							<div class="bpa-data-empty-view">
								<div class="bpa-ev-left-vector">
									<picture>
										<source srcset="' . esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.webp' ) . '" type="image/webp">
										<img src="' . esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.png' ) . '">
									</picture>
								</div>
								<div class="bpa-ev-right-content">
									<h4>' . esc_html__( 'Please login to your account to view bookings!', 'bookingpress-appointment-booking' ) . '</h4>
								</div>
							</div>
						</el-col>
					</el-row>
				</div>';
			} else {
				global $wpdb, $BookingPress;

				$this->bookingpress_mybooking_login_user_id = get_current_user_id();

				$bookingpress_mybooking_title_text = $BookingPress->bookingpress_get_customize_settings( 'mybooking_title_text', 'booking_my_booking' );

				$bookingpress_hide_customer_details                 = $BookingPress->bookingpress_get_customize_settings( 'hide_customer_details', 'booking_my_booking' );
				$this->bookingpress_mybooking_hide_customer_details = $bookingpress_hide_customer_details = ( $bookingpress_hide_customer_details == 'true' ) ? 1 : 0;

				$bookingpress_hide_search_bar                = $BookingPress->bookingpress_get_customize_settings( 'hide_search_bar', 'booking_my_booking' );
				$this->bookingpress_mybooking_hide_searchbar = $bookingpress_hide_search_bar = ( $bookingpress_hide_search_bar == 'true' ) ? 1 : 0;

				$bookingpress_allow_cancel_appointments                 = $BookingPress->bookingpress_get_customize_settings( 'allow_to_cancel_appointment', 'booking_my_booking' );
				$this->bookingpress_mybooking_allow_cancel_appointments = $bookingpress_allow_cancel_appointments = ( $bookingpress_allow_cancel_appointments == 'true' ) ? 1 : 0;

				$bookingpress_default_date_format = $BookingPress->bookingpress_get_customize_settings( 'Default_date_formate', 'booking_my_booking' );
				if ( $bookingpress_default_date_format == 'F j, Y' ) {
					$bookingpress_default_date_format = 'MMMM D, YYYY';
				} elseif ( $bookingpress_default_date_format == 'Y-m-d' ) {
					$bookingpress_default_date_format = 'YYYY-MM-DD';
				} elseif ( $bookingpress_default_date_format == 'm/d/Y' || $bookingpress_default_date_format == 'd/m/Y' ) {
					$bookingpress_default_date_format = 'MM/DD/YYYY';
				}

				$this->bookingpress_mybooking_default_date_format = $bookingpress_default_date_format;

				$BookingPress->set_front_css(1);
				$BookingPress->set_front_js(1);

				$bookingpress_uniq_id = uniqid();
				ob_start();
				require BOOKINGPRESS_VIEWS_DIR . '/frontend/appointment_my_appointments.php';
				$content .= ob_get_clean();

				add_action( 'wp_footer', function() use (&$bookingpress_uniq_id){
					global $bookingpress_global_options;
					$bookingpress_global_details = $bookingpress_global_options->bookingpress_global_options();
					$bookingpress_formatted_timeslot = $bookingpress_global_details['bpa_time_format_for_timeslot'];
					$requested_module = 'front_appointments';
					?>
						<script>
							var bpa_customer_username = '<?php echo $this->bookingpress_mybooking_customer_email; ?>';
							var bpa_customer_email = '<?php echo $this->bookingpress_mybooking_customer_email; ?>';
							var bpa_customer_id = '<?php echo $this->bookingpress_mybooking_wpuser_id; ?>';
							<?php do_action( 'bookingpress_' . $requested_module . '_dynamic_helper_vars' ); ?>
							var app = new Vue({
								el: '#bookingpress_booking_form_<?php echo $bookingpress_uniq_id; ?>',
								directives: { <?php do_action( 'bookingpress_' . $requested_module . '_dynamic_directives' ); ?> },
								components: { <?php do_action( 'bookingpress_' . $requested_module . '_dynamic_components' ); ?> },
								data() {
									var bookingpress_return_data = <?php do_action( 'bookingpress_' . $requested_module . '_dynamic_data_fields' ); ?>;
									bookingpress_return_data['is_display_loader'] = '0';
									bookingpress_return_data['hide_customer_details'] = '<?php echo $this->bookingpress_mybooking_hide_customer_details; ?>';
									bookingpress_return_data['hide_search_bar'] = '<?php echo $this->bookingpress_mybooking_hide_searchbar; ?>';
									bookingpress_return_data['allow_cancel_appointments'] = '<?php echo $this->bookingpress_mybooking_allow_cancel_appointments; ?>';
									return bookingpress_return_data;
								},
								filters:{
									bookingpress_format_date: function(value){
										var default_date_format = '<?php echo $this->bookingpress_mybooking_default_date_format; ?>'
										return moment(String(value)).format(default_date_format)
									},
									bookingpress_format_time: function(value){
										var default_time_format = '<?php echo $bookingpress_formatted_timeslot; ?>'
										return moment(String(value), "HH:mm:ss").format(default_time_format)
									}
								},
								mounted() {
									<?php do_action( 'bookingpress_' . $requested_module . '_dynamic_on_load_methods' ); ?>			
								},
								methods: {
									<?php do_action( 'bookingpress_' . $requested_module . '_dynamic_vue_methods' ); ?>
								},
							});
						</script>
					<?php
				},100);
			}
			return do_shortcode( $content );
		}

		function bookingpress_get_customer_appointments_func() {
			global $BookingPress,$wpdb,$tbl_bookingpress_appointment_bookings,$tbl_bookingpress_customers;
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
			$bpa_login_customer_id             = get_current_user_id();
			$bookingpress_get_customer_details = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_customers} WHERE bookingpress_wpuser_id = {$bpa_login_customer_id}", ARRAY_A );
			$bookingpress_current_user_id      = ! empty( $bookingpress_get_customer_details['bookingpress_customer_id'] ) ? $bookingpress_get_customer_details['bookingpress_customer_id'] : 0;
				$appointments_data             = array();
			if ( ! empty( $bookingpress_current_user_id ) ) {
				$bookingpress_search_data        = ! empty( $_REQUEST['search_data'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['search_data'] ) : array();
				$bookingpress_search_query       = '';
				$bookingpress_search_query_where = "WHERE 1=1 AND bookingpress_customer_id={$bookingpress_current_user_id} ";

				if ( ! empty( $bookingpress_search_data ) ) {
					if ( ! empty( $bookingpress_search_data['search_appointment'] ) ) {
						$bookingpress_search_string       = $bookingpress_search_data['search_appointment'];
						$bookingpress_search_query_where .= "AND (bookingpress_service_name LIKE '%{$bookingpress_search_string}%') ";
					}
					if ( ! empty( $bookingpress_search_data['selected_date_range'] ) ) {
						$bookingpress_search_date         = $bookingpress_search_data['selected_date_range'];
						$start_date                       = date( 'Y-m-d', strtotime( $bookingpress_search_date[0] ) );
						$end_date                         = date( 'Y-m-d', strtotime( $bookingpress_search_date[1] ) );
						$bookingpress_search_query_where .= "AND (bookingpress_appointment_date BETWEEN '{$start_date}' AND '{$end_date}')";
					}
				}
				$total_appointments_list = $wpdb->get_results( 'SELECT bookingpress_appointment_date  FROM ' . $tbl_bookingpress_appointment_bookings . " {$bookingpress_search_query}{$bookingpress_search_query_where} GROUP BY bookingpress_appointment_date order by bookingpress_appointment_date DESC", ARRAY_A );

				if ( ! empty( $total_appointments_list ) ) {
					$bookingpress_date_format = $BookingPress->bookingpress_get_customize_settings( 'Default_date_formate', 'booking_my_booking' );
					$appointment_date         = '';
					$bookingpress_time_format = get_option( 'time_format' );
					foreach ( $total_appointments_list as $get_appointment_date ) {
						$appointment_date = $get_appointment_date['bookingpress_appointment_date'];
						$counter          = 1;
						$appointments     = array();
						if ( ! empty( $appointment_date ) ) {
							$total_appointments = $wpdb->get_results( 'SELECT *  FROM ' . $tbl_bookingpress_appointment_bookings . " {$bookingpress_search_query}{$bookingpress_search_query_where} AND bookingpress_appointment_date LIKE '%{$appointment_date}%'  order by bookingpress_appointment_booking_id DESC", ARRAY_A );
							foreach ( $total_appointments as $get_appointment ) {
								$appointment                             = array();
								$appointment['id']                       = $counter;
								$appointment_id                          = intval( $get_appointment['bookingpress_appointment_booking_id'] );
								$appointment['appointment_id']           = $appointment_id;
								$appointment['appointment_date']         = date( $bookingpress_date_format, strtotime( $get_appointment['bookingpress_appointment_date'] ) );
								$bookingpress_appointment_time           = date( $bookingpress_time_format, strtotime( $get_appointment['bookingpress_appointment_time'] ) );
								$appointment['appointment_time']         = $bookingpress_appointment_time;
								$appointment['appointment_service_name'] = esc_html( $get_appointment['bookingpress_service_name'] );
								$service_duration                        = esc_html( $get_appointment['bookingpress_service_duration_val'] );
								$service_duration_unit                   = esc_html( $get_appointment['bookingpress_service_duration_unit'] );
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
								$appointments[]                      = $appointment;
								$counter++;
							}
						}
						$appointment_date    = date( 'l', strtotime( $appointment_date ) ) . ', ' . date( $bookingpress_date_format, strtotime( $appointment_date ) );
						$appointments_data[] = array(
							'date' => $appointment_date,
							'data' => $appointments,
						);
					}
				}
			}
			$data['items'] = $appointments_data;
			wp_send_json( $data );
		}
		function bookingpress_appointment_service_func( $atts, $content, $tag ) {
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_entries;
			$BookingPress->set_front_css(1);
			$BookingPress->set_front_js(1);
			$bookingpress_short_atts = array(
				'appointment_id' => 0,
			);

			$atts           = shortcode_atts( $bookingpress_short_atts, $atts, $tag );
			$appointment_id = $atts['appointment_id'];

			$appointment_data = array();
			if ( empty( $appointment_id ) && ! empty( $_GET['appointment_id'] ) ) {
				$appointment_id = intval( base64_decode( $_GET['appointment_id'] ) );

				$bookingpress_entry_details = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_entries} WHERE bookingpress_entry_id = {$appointment_id}", ARRAY_A );
				if ( ! empty( $bookingpress_entry_details ) ) {
					$bookingpress_service_id         = $bookingpress_entry_details['bookingpress_service_id'];
					$bookingpress_appointment_date   = $bookingpress_entry_details['bookingpress_appointment_date'];
					$bookingpress_appointment_time   = $bookingpress_entry_details['bookingpress_appointment_time'];
					$bookingpress_appointment_status = $bookingpress_entry_details['bookingpress_appointment_status'];

					$appointment_data = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_service_id = {$bookingpress_service_id} AND bookingpress_appointment_date = '{$bookingpress_appointment_date}' AND bookingpress_appointment_time = '{$bookingpress_appointment_time}' AND bookingpress_appointment_status = '{$bookingpress_appointment_status}'", ARRAY_A );
					if(empty($appointment_data)){
						//If no appointment data found then display data from entries table.
						$appointment_data = $bookingpress_entry_details;
					}
				}
			} else {
				$appointment_data = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = {$appointment_id}", ARRAY_A );
			}

			if ( ! empty( $appointment_data ) ) {
				$content .= "<div class='bookingpress_service_name_div'>";
				$content .= "<span class='bookingpress_service_name'>" . esc_html( $appointment_data['bookingpress_service_name'] ) . '</span>';
				$content .= '</div>';
			}

			return do_shortcode( $content );
		}

		function bookingpress_appointment_datetime_func( $atts, $content, $tag ) {
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_entries;
			$BookingPress->set_front_css(1);
			$BookingPress->set_front_js(1);

			$bookingpress_short_atts = array(
				'appointment_id' => 0,
			);

			$atts           = shortcode_atts( $bookingpress_short_atts, $atts, $tag );
			$appointment_id = $atts['appointment_id'];

			$appointment_data = array();
			if ( empty( $appointment_id ) && ! empty( $_GET['appointment_id'] ) ) {
				$appointment_id = intval( base64_decode( $_GET['appointment_id'] ) );

				$bookingpress_entry_details = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_entries} WHERE bookingpress_entry_id = {$appointment_id}", ARRAY_A );
				if ( ! empty( $bookingpress_entry_details ) ) {
					$bookingpress_service_id         = $bookingpress_entry_details['bookingpress_service_id'];
					$bookingpress_appointment_date   = $bookingpress_entry_details['bookingpress_appointment_date'];
					$bookingpress_appointment_time   = $bookingpress_entry_details['bookingpress_appointment_time'];
					$bookingpress_appointment_status = $bookingpress_entry_details['bookingpress_appointment_status'];

					$appointment_data = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_service_id = {$bookingpress_service_id} AND bookingpress_appointment_date = '{$bookingpress_appointment_date}' AND bookingpress_appointment_time = '{$bookingpress_appointment_time}' AND bookingpress_appointment_status = '{$bookingpress_appointment_status}'", ARRAY_A );
					if(empty($appointment_data)){
						//If no appointment data found then display data from entries table.
						$appointment_data = $bookingpress_entry_details;
					}
				}
			} else {
				$appointment_data = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = {$appointment_id}", ARRAY_A );
			}

			if ( ! empty( $appointment_data ) ) {
				$bookingpress_date_format    = get_option( 'date_format' ) . ' | ' . get_option( 'time_format' );
				$booked_appointment_datetime = esc_html( $appointment_data['bookingpress_appointment_date'] ) . ' ' . esc_html( $appointment_data['bookingpress_appointment_time'] );
				$booked_appointment_date     = date( $bookingpress_date_format, strtotime( $booked_appointment_datetime ) );

				$content .= "<div class='bookingpress_appointment_datetime_div'>";
				$content .= "<span class='bookingpress_appointment_datetime'>" . $booked_appointment_date . '</span>';
				$content .= '</div>';
			}

			return do_shortcode( $content );
		}

		function bookingpress_appointment_customername_func( $atts, $content, $tag ) {
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_entries;
			$BookingPress->set_front_css(1);
			$BookingPress->set_front_js(1);

			$bookingpress_short_atts = array(
				'appointment_id' => 0,
			);

			$atts           = shortcode_atts( $bookingpress_short_atts, $atts, $tag );
			$appointment_id = $atts['appointment_id'];

			$appointment_data  = array();
			$customer_fullname = '';
			if ( empty( $appointment_id ) && ! empty( $_GET['appointment_id'] ) ) {
				$appointment_id = intval( base64_decode( $_GET['appointment_id'] ) );

				$bookingpress_entry_details = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_entries} WHERE bookingpress_entry_id = {$appointment_id}", ARRAY_A );
				if ( ! empty( $bookingpress_entry_details ) ) {
					$bookingpress_service_id         = $bookingpress_entry_details['bookingpress_service_id'];
					$bookingpress_appointment_date   = $bookingpress_entry_details['bookingpress_appointment_date'];
					$bookingpress_appointment_time   = $bookingpress_entry_details['bookingpress_appointment_time'];
					$bookingpress_appointment_status = $bookingpress_entry_details['bookingpress_appointment_status'];

					$appointment_data = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_service_id = {$bookingpress_service_id} AND bookingpress_appointment_date = '{$bookingpress_appointment_date}' AND bookingpress_appointment_time = '{$bookingpress_appointment_time}' AND bookingpress_appointment_status = '{$bookingpress_appointment_status}'", ARRAY_A );
					if(empty($appointment_data)){
						//If no data found from appointments then display data from entries table.
						$appointment_data = $bookingpress_entry_details;
					}
				}
			} else {
				$appointment_data = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = {$appointment_id}", ARRAY_A );
			}

			if ( ! empty( $appointment_data ) ) {
				$bookingpress_customer_id      = $appointment_data['bookingpress_customer_id'];
				$bookingpress_customer_details = $BookingPress->get_customer_details( $bookingpress_customer_id );
					
				
				if(empty($bookingpress_customer_details)) 
				{
					$customer_firstname = ! empty( $appointment_data['bookingpress_customer_firstname'] ) ? $appointment_data['bookingpress_customer_firstname'] : '';
					$customer_lastname  = ! empty( $appointment_data['bookingpress_customer_lastname'] ) ? $appointment_data['bookingpress_customer_lastname'] : '';
					$customer_email  = ! empty( $appointment_data['bookingpress_customer_email'] ) ? $appointment_data['bookingpress_customer_email'] : '';
				} else {
					$customer_firstname = ! empty( $bookingpress_customer_details['bookingpress_user_firstname'] ) ? $bookingpress_customer_details['bookingpress_user_firstname'] : '';
					$customer_lastname  = ! empty( $bookingpress_customer_details['bookingpress_user_lastname'] ) ? $bookingpress_customer_details['bookingpress_user_lastname'] : '';
					$customer_email = ! empty( $bookingpress_customer_details['bookingpress_user_email'] ) ? $bookingpress_customer_details['bookingpress_user_email'] : '';
				}
				$customer_fullname  = $customer_firstname . ' ' . $customer_lastname;
				if ( empty( $customer_firstname ) && empty( $customer_lastname ) ) {
					$customer_fullname = $customer_email;
				}

				$content .= "<div class='bookingpress_appointment_customername_div'>";
				$content .= "<span class='bookingpress_appointment_customername'>" . $customer_fullname . '</span>';
				$content .= '</div>';
			}

			return do_shortcode( $content );
		}
		function bookingpress_company_avatar_func() {
			global $BookingPress;
			$BookingPress->set_front_css(1);
			$BookingPress->set_front_js(1);

			$content                         = '';
			$bookingpress_company_avatar_url = $BookingPress->bookingpress_get_settings( 'company_avatar_url', 'company_setting' );
			if ( $bookingpress_company_avatar_url != '' ) {
				$bookingpress_company_avatar_url = esc_url( $bookingpress_company_avatar_url );
				$content                         = '<img src=' . $bookingpress_company_avatar_url . ' width=100 height=100 >';
			} else {
				$content = esc_html_e( 'Company Avatar Not Found', 'bookingpress-appointment-booking' );
			}
			return do_shortcode( $content );
		}
		function bookingpress_company_name_func() {
			global $BookingPress;
			$BookingPress->set_front_css(1);
			$BookingPress->set_front_js(1);

			$content                   = '';
			$bookingpress_company_name = $BookingPress->bookingpress_get_settings( 'company_name', 'company_setting' );
			if ( $bookingpress_company_name != '' ) {
				$content = esc_html( $bookingpress_company_name );
			} else {
				$content = esc_html_e( 'Company Name Not Found', 'bookingpress-appointment-booking' );
			}
			return do_shortcode( $content );
		}
		function bookingpress_company_website_func() {
			global $BookingPress;
			$BookingPress->set_front_css(1);
			$BookingPress->set_front_js(1);

			$content                      = '';
			$bookingpress_company_website = $BookingPress->bookingpress_get_settings( 'company_website', 'company_setting' );
			if ( $bookingpress_company_website != '' ) {
				$content = esc_html( $bookingpress_company_website );
			} else {
				$content = esc_html_e( 'Company Website Name Not Found', 'bookingpress-appointment-booking' );
			}
			return do_shortcode( $content );
		}
		function bookingpress_company_address_func() {
			global $BookingPress;
			$BookingPress->set_front_css(1);
			$BookingPress->set_front_js(1);

			$content                      = '';
			$bookingpress_company_address = $BookingPress->bookingpress_get_settings( 'company_address', 'company_setting' );
			if ( $bookingpress_company_address != '' ) {
				$content = esc_html( $bookingpress_company_address );
			} else {
				$content = esc_html_e( 'Company Address Not Found', 'bookingpress-appointment-booking' );
			}
			return do_shortcode( $content );
		}
		function bookingpress_company_phone_func() {
			global $BookingPress;
			$BookingPress->set_front_css(1);
			$BookingPress->set_front_js(1);

			$content                      = '';
			$bookingpress_company_phone   = $BookingPress->bookingpress_get_settings( 'company_phone_number', 'company_setting' );
			$bookingpress_company_country = $BookingPress->bookingpress_get_settings( 'company_phone_country', 'company_setting' );

			if ( $bookingpress_company_phone != '' ) {
				$content = esc_html( $bookingpress_company_phone );
			} else {
				$content = esc_html_e( 'Company Phone Number Not Found', 'bookingpress-appointment-booking' );
			}
			return do_shortcode( $content );
		}
		function bookingpress_save_appointment_booking_func() {
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_services, $tbl_bookingpress_customer_bookings, $tbl_bookingpress_customers, $bookingpress_payment_gateways, $bookingpress_debug_payment_log_id;
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
			$response['variant']       = 'error';
			$response['title']         = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']           = esc_html__( 'Something Went wrong...', 'bookingpress-appointment-booking' );
			$response['is_redirect']   = 0;
			$response['redirect_data'] = '';
			$response['is_spam']       = 1;

			$response = apply_filters( 'bookingpress_validate_spam_protection', $response, array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['appointment_data'] ) );

			$appointment_booked_successfully = $BookingPress->bookingpress_get_settings( 'appointment_booked_successfully', 'message_setting' );

			if ( ! empty( $_REQUEST ) && ! empty( $_REQUEST['appointment_data'] ) ) {
				$bookingpress_appointment_data            = $_REQUEST['appointment_data'];
				$bookingpress_payment_gateway             = ! empty( $bookingpress_appointment_data['selected_payment_method'] ) ? sanitize_text_field( $bookingpress_appointment_data['selected_payment_method'] ) : '';
				$bookingpress_appointment_on_site_enabled = ( sanitize_text_field( $bookingpress_appointment_data['selected_payment_method'] ) == 'onsite' ) ? 1 : 0;
				$payment_gateway                          = ( $bookingpress_appointment_on_site_enabled ) ? 'on-site' : $bookingpress_payment_gateway;

				$bookingpress_service_price =  isset($bookingpress_appointment_data['service_price_without_currency']) ? floatval($bookingpress_appointment_data['service_price_without_currency']) : 0;
				if($bookingpress_service_price == 0){
					$payment_gateway = " - ";
				}

				$bookingpress_return_data                 = apply_filters( 'bookingpress_validate_submitted_form', $payment_gateway, $bookingpress_appointment_data );

				if ( $payment_gateway == 'on-site' && $bookingpress_service_price > 0 ) {
					$entry_id = ! empty( $bookingpress_return_data['entry_id'] ) ? $bookingpress_return_data['entry_id'] : 0;
					$bookingpress_payment_gateways->bookingpress_confirm_booking( $entry_id, array(), 'pending' );

					$bookingpress_redirect_url  = $bookingpress_return_data['pending_appointment_url'];
					$bookingpress_redirect_url .= '?appointment_id=' . base64_encode( $entry_id );
					if ( ! empty( $bookingpress_redirect_url ) ) {
						$response['variant']       = 'redirect_url';
						$response['title']         = '';
						$response['msg']           = '';
						$response['is_redirect']   = 1;
						$response['redirect_data'] = $bookingpress_redirect_url;
					} else {
						$response['variant'] = 'success';
						$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
						$response['msg']     = esc_html__( $appointment_booked_successfully, 'bookingpress-appointment-booking' );
					}
				} else if($bookingpress_service_price == 0) {
					$entry_id = ! empty( $bookingpress_return_data['entry_id'] ) ? $bookingpress_return_data['entry_id'] : 0;
					$bookingpress_payment_gateways->bookingpress_confirm_booking( $entry_id, array(), 'success' );

					$redirect_url                    = $bookingpress_return_data['approved_appointment_url'] . '?appointment_id=' . base64_encode( $entry_id );
					$bookingpress_appointment_status = $BookingPress->bookingpress_get_settings( 'appointment_status', 'general_setting' );
					if ( $bookingpress_appointment_status == 'Pending' ) {
						$redirect_url = $bookingpress_return_data['pending_appointment_url'] . '?appointment_id=' . base64_encode( $entry_id );
					}

					$bookingpress_redirect_url  = $redirect_url;
					if ( ! empty( $bookingpress_redirect_url ) ) {
						$response['variant']       = 'redirect_url';
						$response['title']         = '';
						$response['msg']           = '';
						$response['is_redirect']   = 1;
						$response['redirect_data'] = $bookingpress_redirect_url;
					} else {
						$response['variant'] = 'success';
						$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
						$response['msg']     = esc_html__( $appointment_booked_successfully, 'bookingpress-appointment-booking' );
					}
				} else {
					if($payment_gateway == "paypal")
					{
						$bookingpress_payment_mode    = $BookingPress->bookingpress_get_settings( 'paypal_payment_mode', 'payment_setting' );
						$bookingpress_is_sandbox_mode = ( $bookingpress_payment_mode != 'live' ) ? true : false;
						$bookingpress_gateway_status  = $BookingPress->bookingpress_get_settings( 'paypal_payment', 'payment_setting' );
						$bookingpress_merchant_email  = $BookingPress->bookingpress_get_settings( 'paypal_merchant_email', 'payment_setting' );
						$bookingpress_api_username    = $BookingPress->bookingpress_get_settings( 'paypal_api_username', 'payment_setting' );
						$bookingpress_api_password    = $BookingPress->bookingpress_get_settings( 'paypal_api_password', 'payment_setting' );
						$bookingpress_api_signature   = $BookingPress->bookingpress_get_settings( 'paypal_api_signature', 'payment_setting' );

						$bookingpress_paypal_error_msg = esc_html__('PayPal Configuration Error', 'bookingpress-appointment-booking');
						$bookingpress_paypal_error_msg .= ": ";
						if(empty($bookingpress_merchant_email)){	
							$bookingpress_paypal_error_msg .= esc_html__('Please configure merchant email address', 'bookingpress-appointment-booking');

							$response['variant']       = 'error';
							$response['title']         = esc_html__( 'Error', 'bookingpress-appointment-booking' );
							$response['msg']           = $bookingpress_paypal_error_msg;
							$response['is_redirect']   = 0;
							$response['redirect_data'] = '';
							$response['is_spam']       = 0;

							echo json_encode($response);
							exit;
						}

						if(empty($bookingpress_api_username)){
							$bookingpress_paypal_error_msg .= esc_html__('Please configure PayPal API Username', 'bookingpress-appointment-booking');

							$response['variant']       = 'error';
							$response['title']         = esc_html__( 'Error', 'bookingpress-appointment-booking' );
							$response['msg']           = $bookingpress_paypal_error_msg;
							$response['is_redirect']   = 0;
							$response['redirect_data'] = '';
							$response['is_spam']       = 0;

							echo json_encode($response);
							exit;
						}

						if(empty($bookingpress_api_password)){
							$bookingpress_paypal_error_msg .= esc_html__('Please configure PayPal API Password', 'bookingpress-appointment-booking');

							$response['variant']       = 'error';
							$response['title']         = esc_html__( 'Error', 'bookingpress-appointment-booking' );
							$response['msg']           = $bookingpress_paypal_error_msg;
							$response['is_redirect']   = 0;
							$response['redirect_data'] = '';
							$response['is_spam']       = 0;

							echo json_encode($response);
							exit;
						}

						if(empty($bookingpress_api_signature)){
							$bookingpress_paypal_error_msg .= esc_html__('Please configure PayPal API Signature', 'bookingpress-appointment-booking');

							$response['variant']       = 'error';
							$response['title']         = esc_html__( 'Error', 'bookingpress-appointment-booking' );
							$response['msg']           = $bookingpress_paypal_error_msg;
							$response['is_redirect']   = 0;
							$response['redirect_data'] = '';
							$response['is_spam']       = 0;

							echo json_encode($response);
							exit;
						}

						$entry_id                          = $bookingpress_return_data['entry_id'];
						$currency                          = $bookingpress_return_data['currency'];
						$currency_symbol                   = $BookingPress->bookingpress_get_currency_symbol( $currency );
						$bookingpress_final_payable_amount = isset( $bookingpress_return_data['payable_amount'] ) ? $bookingpress_return_data['payable_amount'] : 0;
						$customer_details                  = $bookingpress_return_data['customer_details'];
						$customer_email                    = ! empty( $customer_details['customer_email'] ) ? $customer_details['customer_email'] : '';

						$bookingpress_service_name = ! empty( $bookingpress_return_data['service_data']['bookingpress_service_name'] ) ? $bookingpress_return_data['service_data']['bookingpress_service_name'] : __( 'Appointment Booking', 'bookingpress-appointment-booking' );

						$custom_var = $entry_id;

						$sandbox = $bookingpress_is_sandbox_mode ? 'sandbox.' : '';

						$notify_url = $bookingpress_return_data['notify_url'];

						$redirect_url                    = $bookingpress_return_data['approved_appointment_url'] . '?appointment_id=' . base64_encode( $entry_id );
						$bookingpress_appointment_status = $BookingPress->bookingpress_get_settings( 'appointment_status', 'general_setting' );
						if ( $bookingpress_appointment_status == 'Pending' ) {
							$redirect_url = $bookingpress_return_data['pending_appointment_url'] . '?appointment_id=' . base64_encode( $entry_id );
						}

						$bookingpress_paypal_cancel_url = $BookingPress->bookingpress_get_settings( 'paypal_cancel_url', 'payment_setting' );				
						$cancel_url = !empty($bookingpress_paypal_cancel_url) ? $bookingpress_paypal_cancel_url : BOOKINGPRESS_HOME_URL ;				
						$cancel_url = add_query_arg( 'is_cancel', 1 , esc_url( $cancel_url ) );

						$cmd          = '_xclick';
						$paypal_form  = '<form name="_xclick" id="bookingpress_paypal_form" action="https://www.' . $sandbox . 'paypal.com/cgi-bin/webscr" method="post">';
						$paypal_form .= '<input type="hidden" name="cmd" value="' . $cmd . '" />';
						$paypal_form .= '<input type="hidden" name="amount" value="' . $bookingpress_final_payable_amount . '" />';
						$paypal_form .= '<input type="hidden" name="business" value="' . $bookingpress_merchant_email . '" />';
						$paypal_form .= '<input type="hidden" name="notify_url" value="' . $notify_url . '" />';
						$paypal_form .= '<input type="hidden" name="cancel_return" value="' . $cancel_url . '" />';
						$paypal_form .= '<input type="hidden" name="return" value="' . $redirect_url . '" />';
						$paypal_form .= '<input type="hidden" name="rm" value="2" />';
						$paypal_form .= '<input type="hidden" name="lc" value="en_US" />';
						$paypal_form .= '<input type="hidden" name="no_shipping" value="1" />';
						$paypal_form .= '<input type="hidden" name="custom" value="' . $custom_var . '" />';
						$paypal_form .= '<input type="hidden" name="on0" value="user_email" />';
						$paypal_form .= '<input type="hidden" name="os0" value="' . $customer_email . '" />';
						$paypal_form .= '<input type="hidden" name="page_style" value="primary" />';
						$paypal_form .= '<input type="hidden" name="charset" value="UTF-8" />';
						$paypal_form .= '<input type="hidden" name="item_name" value="' . $bookingpress_service_name . '" />';
						$paypal_form .= '<input type="hidden" name="item_number" value="1" />';
						$paypal_form .= '<input type="submit" value="Pay with PayPal!" />';
						$paypal_form .= '</form>';

						do_action( 'bookingpress_payment_log_entry', 'paypal', 'payment form redirected data', 'bookingpress', $paypal_form, $bookingpress_debug_payment_log_id );

						$paypal_form .= '<script type="text/javascript">document.getElementById("bookingpress_paypal_form").submit();</script>';

						$response['variant']       = 'redirect';
						$response['title']         = '';
						$response['msg']           = '';
						$response['is_redirect']   = 1;
						$response['redirect_data'] = $paypal_form;
						$response['entry_id'] = $entry_id;
					}
				}
			}

			echo json_encode( $response );
			exit();
		}

		function bookingpress_get_service_timings() {
			global $wpdb, $BookingPress, $tbl_bookingpress_services, $bookingpress_global_options;
			$response              = array();
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
				echo json_encode( $response );
				exit();
			}
			$selected_service_id = ! empty( $_POST['service_id'] ) ? intval( $_POST['service_id'] ) : 0;
			$selected_date       = ! empty( $_POST['selected_date'] ) ? date( 'Y-m-d', strtotime( sanitize_text_field( $_POST['selected_date'] ) ) ) : '';

			$service_timings = $BookingPress->bookingpress_get_service_available_time( $selected_service_id, $selected_date );

			$bookingpress_global_details = $bookingpress_global_options->bookingpress_global_options();
			$bpa_wp_default_time_format = $bookingpress_global_details['wp_default_time_format'];

			$morning_time = array();
			$afternoon_time = array();
			$evening_time = array();
			$night_time = array();
			if ( ! empty( $service_timings ) ) {
				foreach ( $service_timings as $service_time_key => $service_time_val ) {
					$service_start_time = date( 'H', strtotime( $service_time_val['start_time'] ) );
					$service_end_time = date( 'H', strtotime( $service_time_val['end_time'] ) );

					$service_formatted_start_time = date($bpa_wp_default_time_format, strtotime($service_time_val['start_time']));
					$service_formatted_end_time = date($bpa_wp_default_time_format, strtotime($service_time_val['end_time']));
					
					if ( $service_start_time >= 0 && $service_start_time < 12 ) {
						$morning_time[] = array(
							'start_time'       => $service_time_val['start_time'],
							'end_time'         => $service_time_val['end_time'],
							'formatted_start_time' => $service_formatted_start_time,
							'formatted_end_time' => $service_formatted_end_time,
							'break_start_time' => $service_time_val['break_start_time'],
							'break_end_time'   => $service_time_val['break_end_time'],
							'is_booked'        => $service_time_val['is_booked'],
							'class'            => ( $service_time_val['is_booked'] ) ? '__bpa-is-disabled' : '',
						);
					} else if($service_start_time >= 12 && $service_start_time < 16) {
						$afternoon_time[] = array(
							'start_time'       => $service_time_val['start_time'],
							'end_time'         => $service_time_val['end_time'],
							'formatted_start_time' => $service_formatted_start_time,
							'formatted_end_time' => $service_formatted_end_time,
							'break_start_time' => $service_time_val['break_start_time'],
							'break_end_time'   => $service_time_val['break_end_time'],
							'is_booked'        => $service_time_val['is_booked'],
							'class'            => ( $service_time_val['is_booked'] ) ? '__bpa-is-disabled' : '',
						);
					} else if($service_start_time >= 16 && $service_start_time < 20) {
						$evening_time[] = array(
							'start_time'       => $service_time_val['start_time'],
							'end_time'         => $service_time_val['end_time'],
							'formatted_start_time' => $service_formatted_start_time,
							'formatted_end_time' => $service_formatted_end_time,
							'break_start_time' => $service_time_val['break_start_time'],
							'break_end_time'   => $service_time_val['break_end_time'],
							'is_booked'        => $service_time_val['is_booked'],
							'class'            => ( $service_time_val['is_booked'] ) ? '__bpa-is-disabled' : '',
						);
					} else {
						$night_time[] = array(
							'start_time'       => $service_time_val['start_time'],
							'end_time'         => $service_time_val['end_time'],
							'formatted_start_time' => $service_formatted_start_time,
							'formatted_end_time' => $service_formatted_end_time,
							'break_start_time' => $service_time_val['break_start_time'],
							'break_end_time'   => $service_time_val['break_end_time'],
							'is_booked'        => $service_time_val['is_booked'],
							'class'            => ( $service_time_val['is_booked'] ) ? '__bpa-is-disabled' : '',
						);
					}
				}
			}

			$return_data = array(
				'morning_time' => $morning_time,
				'afternoon_time' => $afternoon_time,
				'evening_time' => $evening_time,
				'night_time' => $night_time,
			);

			echo json_encode( $return_data );
			exit();
		}

		function bookingpress_get_category_service_data() {
			 global $wpdb, $BookingPress, $tbl_bookingpress_services, $tbl_bookingpress_servicesmeta;
			$response              = array();
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
				echo json_encode( $response );
				exit();
			}
			if ( ! empty( $_POST['category_id'] ) ) {
				$selected_category_id        = intval( $_POST['category_id'] );
				$bookingpress_total_services = 0;
				if ( ! empty( $_POST['total_service'] ) ) {
					$bookingpress_total_services = sanitize_text_field( $_POST['total_service'] );
				}
				// Fetch services of selected categories
				$bookingpress_search_query_where = '';
				if ( ! empty( $bookingpress_total_services ) && $bookingpress_total_services != 0 ) {
					$bookingpress_search_query_where .= " AND bookingpress_service_id IN ({$bookingpress_total_services})";
				}
				$service_data = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_services} WHERE bookingpress_category_id = {$selected_category_id} {$bookingpress_search_query_where}", ARRAY_A );

				$bookingpress_display_service_description = $BookingPress->bookingpress_get_customize_settings('display_service_description', 'booking_form' );

				foreach ( $service_data as $service_key => $service_val ) {
					$service_data[ $service_key ]['bookingpress_service_price']                  = $BookingPress->bookingpress_price_formatter_with_currency_symbol( $service_val['bookingpress_service_price'] );
					$service_data[ $service_key ]['service_price_without_currency'] = (float) $service_val['bookingpress_service_price'];

					$service_id                              = $service_val['bookingpress_service_id'];
					$service_meta_details                    = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_servicesmeta} WHERE bookingpress_service_id = {$service_id} AND bookingpress_servicemeta_name = 'service_image_details'", ARRAY_A );
					$service_img_details                     = ! empty( $service_meta_details['bookingpress_servicemeta_value'] ) ? unserialize( $service_meta_details['bookingpress_servicemeta_value'] ) : array();
					$service_data[ $service_key ]['img_url'] = ! empty( $service_img_details[0]['url'] ) ? $service_img_details[0]['url'] : BOOKINGPRESS_URL . '/images/placeholder-img.jpg';					
					if($bookingpress_display_service_description == 'true') {
						$service_data[ $service_key ]['display_read_more_less'] = 1;						
						$default_service_description = stripslashes($service_data[$service_key]['bookingpress_service_description']);
						if(strlen($default_service_description) > 140 ){
							$service_data[ $service_key ]['bookingpress_service_description_with_excerpt'] = stripslashes(substr($default_service_description, 0, 140));
							$service_data[ $service_key ]['display_details_more'] = 0;
							$service_data[ $service_key ]['display_details_less'] = 1;
						} else {
							$service_data[ $service_key ]['display_read_more_less'] = 0 ;
						}
					}	
				}
				echo json_encode( $service_data );
				exit();
			}
		}

		function bookingpress_front_booking_form( $atts, $content, $tag ) {
			global $wpdb, $BookingPress, $bookingpress_common_date_format, $tbl_bookingpress_form_fields, $tbl_bookingpress_services, $tbl_bookingpress_customers;
			$defaults = array(
				'service'  => 0,
				'category' => 0,
			);
			$args     = shortcode_atts( $defaults, $atts, $tag );
			extract( $args );
			$Bookingpress_service  = 0;
			$Bookingpress_category = 0;
			if ( ! empty( $category ) && $category != 0 ) {
				$Bookingpress_category            = $category;
				$this->bookingpress_form_category = $category;
			}
			if ( ( ! empty( $service ) && $service != 0 ) || ( isset( $_GET['bpservice_id'] ) && ! empty( $_GET['bpservice_id'] ) ) ) {
				if ( empty( $service ) && ! empty( intval( $_GET['bpservice_id'] ) ) ) {
					$service = intval( $_GET['bpservice_id'] );

					// Get category id
					$bookingpress_service_details = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = {$service}", ARRAY_A );
					if ( ! empty( $bookingpress_service_details ) ) {
						$this->bookingpress_form_category = $bookingpress_service_details['bookingpress_category_id'];
					}

					$this->bookingpress_is_service_load_from_url = 1;
				}

				$Bookingpress_service            = $service;
				$this->bookingpress_form_service = $service;
			}
			$bookingpress_service_details = $BookingPress->get_bookingpress_service_data_group_with_category();

			// Get labels and tabs names generated from customize
			// -----------------------------------------------------

				$bookingpress_first_tab_name  = $BookingPress->bookingpress_get_customize_settings( 'service_title', 'booking_form' );
				$bookingpress_second_tab_name = $BookingPress->bookingpress_get_customize_settings( 'datetime_title', 'booking_form' );
				$bookingpress_third_tab_name  = $BookingPress->bookingpress_get_customize_settings( 'basic_details_title', 'booking_form' );
				$bookingpress_fourth_tab_name = $BookingPress->bookingpress_get_customize_settings( 'summary_title', 'booking_form' );

				$bookingpress_category_title       = $BookingPress->bookingpress_get_customize_settings( 'category_title', 'booking_form' );
				$bookingpress_services_title       = $BookingPress->bookingpress_get_customize_settings( 'service_heading_title', 'booking_form' );
				$bookingpress_timeslot_title       = $BookingPress->bookingpress_get_customize_settings( 'timeslot_text', 'booking_form' );
				$bookingpress_summary_content_text = $BookingPress->bookingpress_get_customize_settings( 'summary_content_text', 'booking_form' );
				$bookingpress_payment_method_text  = $BookingPress->bookingpress_get_customize_settings( 'payment_method_text', 'booking_form' );

				$bookingpress_morning_text = $BookingPress->bookingpress_get_customize_settings( 'morning_text', 'booking_form' );
				if(empty($bookingpress_morning_text)){
					$bookingpress_morning_text = esc_html__('Morning', 'bookingpress-appointment-booking');
				}
				$bookingpress_afternoon_text = $BookingPress->bookingpress_get_customize_settings( 'afternoon_text', 'booking_form' );
				if(empty($bookingpress_afternoon_text)){
					$bookingpress_afternoon_text = esc_html__('Afternoon', 'bookingpress-appointment-booking');
				}
				$bookingpress_evening_text = $BookingPress->bookingpress_get_customize_settings( 'evening_text', 'booking_form' );
				if(empty($bookingpress_evening_text)){
					$bookingpress_evening_text = esc_html__('Evening', 'bookingpress-appointment-booking');
				}
				$bookingpress_night_text = $BookingPress->bookingpress_get_customize_settings( 'night_text', 'booking_form' );
				if(empty($bookingpress_night_text)){
					$bookingpress_night_text = esc_html__('Night', 'bookingpress-appointment-booking');
				}

				$bookingpress_goback_btn_text           = $BookingPress->bookingpress_get_customize_settings( 'goback_button_text', 'booking_form' );
				$bookingpress_next_btn_text             = $BookingPress->bookingpress_get_customize_settings( 'next_button_text', 'booking_form' );
				$bookingpress_book_appointment_btn_text = $BookingPress->bookingpress_get_customize_settings( 'book_appointment_btn_text', 'booking_form' );
				$bookingpress_tabs_position             = $BookingPress->bookingpress_get_customize_settings( 'booking_form_tabs_position', 'booking_form' );

				$bookingpress_hide_category_service       = $BookingPress->bookingpress_get_customize_settings( 'hide_category_service_selection', 'booking_form' );
				$bookingpress_hide_category_service       = ( $bookingpress_hide_category_service == 'true' ) ? 1 : 0;
				$this->bookingpress_hide_category_service = $bookingpress_hide_category_service;

				$bookingpress_hide_next_previous_buttons       = $BookingPress->bookingpress_get_customize_settings( 'hide_next_previous_button', 'booking_form' );
				$bookingpress_hide_next_previous_buttons       = ( $bookingpress_hide_next_previous_buttons == 'true' ) ? 1 : 0;
				$this->bookingpress_hide_next_previous_buttons = $bookingpress_hide_next_previous_buttons;

				$bookingpress_default_date_format = $BookingPress->bookingpress_get_customize_settings( 'default_date_format', 'booking_form' );
			if ( $bookingpress_default_date_format == 'F j, Y' ) {
				$bookingpress_default_date_format = 'MMMM D, YYYY';
			} elseif ( $bookingpress_default_date_format == 'Y-m-d' ) {
				$bookingpress_default_date_format = 'YYYY-MM-DD';
			} elseif ( $bookingpress_default_date_format == 'm/d/Y' ) {
				$bookingpress_default_date_format = 'MM/DD/YYYY';
			} elseif ( $bookingpress_default_date_format == 'd/m/Y' ) {
				$bookingpress_default_date_format = 'DD/MM/YYYY';
			}
				$this->bookingpress_default_date_format = $bookingpress_default_date_format;

			// -----------------------------------------------------

			// Get form fields details
			// -----------------------------------------------------

				$bookingpress_form_fields               = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_form_fields} ORDER BY bookingpress_field_position ASC", ARRAY_A );
				$bookingpress_form_fields_error_msg_arr = $bookingpress_form_fields_new = array();
			foreach ( $bookingpress_form_fields as $bookingpress_form_field_key => $bookingpress_form_field_val ) {

				$bookingpress_v_model_value = '';
				if ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'fullname' ) {
					$bookingpress_v_model_value = 'customer_name';
				} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'firstname' ) {
					$bookingpress_v_model_value = 'customer_firstname';
				} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'lastname' ) {
					$bookingpress_v_model_value = 'customer_lastname';
				} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'email_address' ) {
					$bookingpress_v_model_value = 'customer_email';
				} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'phone_number' ) {
					$bookingpress_v_model_value = 'customer_phone';
				} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'note' ) {
					$bookingpress_v_model_value = 'appointment_note';
				}

				$bookingpress_form_fields_new[] = array(
					'field_name'     => $bookingpress_form_field_val['bookingpress_form_field_name'],
					'is_required'    => ( $bookingpress_form_field_val['bookingpress_field_required'] == 0 ) ? false : true,
					'label'          => $bookingpress_form_field_val['bookingpress_field_label'],
					'placeholder'    => $bookingpress_form_field_val['bookingpress_field_placeholder'],
					'error_message'  => $bookingpress_form_field_val['bookingpress_field_error_message'],
					'is_hide'        => ( $bookingpress_form_field_val['bookingpress_field_is_hide'] == 0 ) ? false : true,
					'field_position' => intval( $bookingpress_form_field_val['bookingpress_field_position'] ),
					'v_model_value'  => $bookingpress_v_model_value,
				);

				if ( $bookingpress_form_field_val['bookingpress_field_required'] == '1' ) {
					if ( $bookingpress_v_model_value == 'customer_email' ) {
						$bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ] = array(
							array(
								'required' => true,
								'message'  => $bookingpress_form_field_val['bookingpress_field_error_message'],
								'trigger'  => 'blur',
							),
							array(
								'type'    => 'email',
								'message' => esc_html__( 'Please enter valid email address', 'bookingpress-appointment-booking' ),
								'trigger' => 'blur',
							),
						);
					} else {
						$bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ] = array(
							'required' => true,
							'message'  => $bookingpress_form_field_val['bookingpress_field_error_message'],
							'trigger'  => 'blur',
						);
					}
				}
			}

				$this->bookingpress_form_fields_error_msg_arr = $bookingpress_form_fields_error_msg_arr;
				$this->bookingpress_form_fields_new           = $bookingpress_form_fields_new;

			// -----------------------------------------------------

			if ( is_user_logged_in() ) {
				$bookingpress_wp_user_id              = get_current_user_id();
				$bookingpress_check_user_exist_or_not = $wpdb->get_var( "SELECT COUNT(bookingpress_customer_id) as total FROM {$tbl_bookingpress_customers} WHERE bookingpress_wpuser_id = {$bookingpress_wp_user_id} AND bookingpress_user_status = 0 AND bookingpress_user_type = 0" );
				if ( $bookingpress_check_user_exist_or_not > 0 ) {
					$bookingpress_update_customer_data = array(
						'bookingpress_user_status' => 1,
						'bookingpress_user_type'   => 2,
					);

					$bookingpress_where_condition = array(
						'bookingpress_wpuser_id' => $bookingpress_wp_user_id,
					);

					$wpdb->update( $tbl_bookingpress_customers, $bookingpress_update_customer_data, $bookingpress_where_condition );
				}
			}

			$bookingpress_uniq_id = uniqid();

			$BookingPress->set_front_css(1);
			$BookingPress->set_front_js(1);

			ob_start();
			$bookingpress_shortcode_file_url = BOOKINGPRESS_VIEWS_DIR . '/frontend/appointment_booking_form.php';
			$bookingpress_shortcode_file_url = apply_filters('bookingpress_change_booking_shortcode_file_url', $bookingpress_shortcode_file_url);
			require $bookingpress_shortcode_file_url;
			$content .= ob_get_clean();

			add_action( 'wp_footer', function() use (&$bookingpress_uniq_id){
				global $BookingPress, $bookingpress_global_options;
				$bookingpress_global_details = $bookingpress_global_options->bookingpress_global_options();
				$bookingpress_formatted_timeslot = $bookingpress_global_details['bpa_time_format_for_timeslot'];


				$bookingpress_site_current_language = get_locale();
				if($bookingpress_site_current_language == "ru_RU"){
					$bookingpress_site_current_language = "ru";
				}else if($bookingpress_site_current_language == "ar"){
					$bookingpress_site_current_language = "ar"; //arabic
				}else if($bookingpress_site_current_language == "bg_BG"){
					$bookingpress_site_current_language = "bg"; //Bulgeria
				}else if($bookingpress_site_current_language == "en_CA"){
					$bookingpress_site_current_language = "ca"; //Canada
				}else if($bookingpress_site_current_language == "da_DK" || $bookingpress_site_current_language == "de_AT" || $bookingpress_site_current_language == "de_CH" || $bookingpress_site_current_language == "de_DE_formal"){
					$bookingpress_site_current_language = "da"; //Denmark
				}else if($bookingpress_site_current_language == "de_DE" || $bookingpress_site_current_language == "de_CH_informal"){
					$bookingpress_site_current_language = "de"; //Germany
				}else if($bookingpress_site_current_language == "el"){
					$bookingpress_site_current_language = "el"; //Greece
				}else if($bookingpress_site_current_language == "es_ES"){
					$bookingpress_site_current_language = "es"; //Spain
				}else if($bookingpress_site_current_language == "fr_FR"){
					$bookingpress_site_current_language = "fr"; //France
				}else if($bookingpress_site_current_language == "hr"){
					$bookingpress_site_current_language = "hr"; //Croatia
				}else if($bookingpress_site_current_language == "hu_HU"){
					$bookingpress_site_current_language = "hu"; //Hungary
				}else if($bookingpress_site_current_language == "id_ID"){
					$bookingpress_site_current_language = "id"; //Indonesia
				}else if($bookingpress_site_current_language == "is_IS"){
					$bookingpress_site_current_language = "is"; //Iceland
				}else if($bookingpress_site_current_language == "it_IT"){
					$bookingpress_site_current_language = "it"; //Italy
				}else if($bookingpress_site_current_language == "ja"){
					$bookingpress_site_current_language = "ja"; //Japan
				}else if($bookingpress_site_current_language == "ka_GE"){
					$bookingpress_site_current_language = "ka"; //Georgia
				}else if($bookingpress_site_current_language == "ko_KR"){
					$bookingpress_site_current_language = "ko"; //Korean
				}else if($bookingpress_site_current_language == "lt_LT"){
					$bookingpress_site_current_language = "lt"; //Lithunian
				}else if($bookingpress_site_current_language == "mn"){
					$bookingpress_site_current_language = "mn"; //Mongolia
				}else if($bookingpress_site_current_language == "nl_NL"){
					$bookingpress_site_current_language = "nl"; //Netherlands
				}else if($bookingpress_site_current_language == "nn_NO"){
					$bookingpress_site_current_language = "no"; //Norway
				}else if($bookingpress_site_current_language == "pl_PL"){
					$bookingpress_site_current_language = "pl"; //Poland
				}else if($bookingpress_site_current_language == "pt_BR"){
					$bookingpress_site_current_language = "pt-br"; //Portuguese
				}else if($bookingpress_site_current_language == "ro_RO"){
					$bookingpress_site_current_language = "ro"; //Romania
				}else if($bookingpress_site_current_language == "sk_SK"){
					$bookingpress_site_current_language = "sk"; //Slovakia
				}else if($bookingpress_site_current_language == "sl_SI"){
					$bookingpress_site_current_language = "sl"; //Slovenia
				}else if($bookingpress_site_current_language == "sq"){
					$bookingpress_site_current_language = "sq"; //Albanian
				}else if($bookingpress_site_current_language == "sr_RS"){
					$bookingpress_site_current_language = "sr"; //Suriname
				}else if($bookingpress_site_current_language == "sv_SE"){
					$bookingpress_site_current_language = "sv"; //El Salvador
				}else if($bookingpress_site_current_language == "tr_TR"){
					$bookingpress_site_current_language = "tr"; //Turkey
				}else if($bookingpress_site_current_language == "uk"){
					$bookingpress_site_current_language = "uk"; //Ukrain
				}else if($bookingpress_site_current_language == "vi"){
					$bookingpress_site_current_language = "vi"; //Virgin Islands (U.S.)
				}else if($bookingpress_site_current_language == "zh_CN"){
					$bookingpress_site_current_language = "zh-cn"; //Chinese
				}else{
					$bookingpress_site_current_language = "en";
				}

				$no_appointment_time_selected_for_the_booking = $BookingPress->bookingpress_get_settings( 'no_appointment_time_selected_for_the_booking', 'message_setting' );

				$no_service_selected_for_the_booking = $BookingPress->bookingpress_get_settings('no_service_selected_for_the_booking', 'message_setting');
				?>
					<script>
						<?php do_action( 'bookingpress_front_booking_dynamic_helper_vars' ); ?>
						var app = new Vue({
							el: '#bookingpress_booking_form_<?php echo $bookingpress_uniq_id; ?>',
							components: { 'vue-cal': vuecal },
							directives: { <?php do_action( 'bookingpress_front_booking_dynamic_directives' ); ?> },
							data() {
								var bookingpress_return_data = <?php do_action( 'bookingpress_front_booking_dynamic_data_fields', $this->bookingpress_form_category, $this->bookingpress_form_service ); ?>;
								bookingpress_return_data['jsCurrentDate'] = new Date();
								bookingpress_return_data['appointment_step_form_data']['stime'] = <?php echo( time() + 14921 ); ?>;
								bookingpress_return_data['appointment_step_form_data']['spam_captcha'] = '';
								bookingpress_return_data['hide_category_service'] = '<?php echo $this->bookingpress_hide_category_service; ?>';
								bookingpress_return_data['hide_next_previous_btns'] = '<?php echo $this->bookingpress_hide_next_previous_buttons; ?>';
								bookingpress_return_data['default_date_format'] = '<?php echo $this->bookingpress_default_date_format; ?>';

								bookingpress_return_data['customer_details_rule'] = <?php echo json_encode( $this->bookingpress_form_fields_error_msg_arr ); ?>;
								bookingpress_return_data['customer_form_fields'] = <?php echo json_encode( $this->bookingpress_form_fields_new ); ?>;
								bookingpress_return_data['is_error_msg'] = '';
								bookingpress_return_data['is_display_error'] = '0';
								bookingpress_return_data['is_service_loaded_from_url'] = '<?php echo $this->bookingpress_is_service_load_from_url; ?>';
								bookingpress_return_data['booking_cal_maxdate'] = new Date().addDays(730);

								bookingpress_return_data['site_locale'] = '<?php echo $bookingpress_site_current_language; ?>';

								return bookingpress_return_data
							},
							filters:{
								bookingpress_format_date: function(value){
									var default_date_format = '<?php echo $this->bookingpress_default_date_format; ?>'
									return moment(String(value)).format(default_date_format)
								},
								bookingpress_format_time: function(value){
									var default_time_format = '<?php echo $bookingpress_formatted_timeslot; ?>'
									return moment(String(value), "HH:mm:ss").format(default_time_format)
								}
							},
							mounted() {
								this.loadSpamProtection()
								<?php do_action( 'bookingpress_front_booking_dynamic_on_load_methods' ); ?>
								if(this.hide_category_service == '1' || this.is_service_loaded_from_url == '1'){
									this.current_selected_tab_id = '2'
									this.selectDate(this.appointment_step_form_data.selected_service, this.appointment_step_form_data.selected_service_name, this.appointment_step_form_data.selected_service_price, this.appointment_step_form_data.service_price_without_currency)
								}
								this.loadTabClick()
							},
							methods: {
								generateSpamCaptcha(){
									const vm = this;
									var postData = { action:'bookingpress_generate_spam_captcha', _wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' };
										axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
									.then( function (response) {
										if(response.variant != 'error' && (response.data.captcha_val != '' && response.data.captcha_val != undefined)){
											vm.appointment_step_form_data.spam_captcha = response.data.captcha_val
										}else{
											vm.$notify({
												title: response.data.title,
												message: response.data.msg,
												type: response.data.variant,
												customClass: 'error_notification',
											});
										}
									}.bind(this) )
									.catch( function (error) {
										console.log(error);
									});
								},
								loadSpamProtection(){
									const vm = this;
									vm.generateSpamCaptcha();
								},
								<?php do_action( 'bookingpress_front_booking_dynamic_vue_methods' ); ?>
								loadTabClick(){
									const vm = this
									var bpa_tab_click = document.querySelectorAll('.bpa-front-tab-menu--item');
									bpa_tab_click.forEach(el => el.addEventListener('click', event => {
										event.preventDefault();
										var currentElement = event.target;
										var dataset_id = currentElement.dataset.id

										var tab_bodys = document.querySelectorAll('.bpa-front-tabs--panel-body');
										tab_bodys.forEach(function(currentValue, index, arr){
											if(currentValue.dataset.id == dataset_id)
											{
												if(dataset_id > 1 && (vm.appointment_step_form_data.selected_service == '')){
													vm.bookingpress_set_error_msg('<?php esc_html_e( $no_service_selected_for_the_booking, 'bookingpress-appointment-booking' ); ?>')
													vm.current_selected_tab_id = 1
													return false;
												}

												if(dataset_id > 2 && (vm.appointment_step_form_data.selected_start_time == '')){
													vm.bookingpress_set_error_msg('<?php esc_html_e( $no_appointment_time_selected_for_the_booking, 'bookingpress-appointment-booking' ); ?>')
													vm.current_selected_tab_id = 2
													return false;
												}


												if(dataset_id > 3){
													var customer_form = 'appointment_step_form_data';
													vm.$refs[customer_form].validate((valid) => {
														if (valid) {
															vm.current_selected_tab_id = dataset_id		
														}
													});
												}else{
													vm.current_selected_tab_id = dataset_id
													currentElement.classList.add('__bpa-is-active');
												}
											}
										});
									}));
								},
							},
						});
					</script>
				<?php
			}, 100);

			return do_shortcode( $content );
		}

		function bookingpress_booking_dynamic_data_fields_func( $bookingpress_category, $bookingpress_service ) {
			global $wpdb, $BookingPress, $bookingpress_front_vue_data_fields, $tbl_bookingpress_customers, $tbl_bookingpress_categories, $tbl_bookingpress_services, $tbl_bookingpress_servicesmeta;
			// Get categories
			$bookingpress_search_query_where = 'WHERE 1=1 ';
			$bookingpress_search_query_join  = '';

			if ( ! empty( $bookingpress_category ) ) {
				$bookingpress_search_query_where .= " AND category.bookingpress_category_id IN ({$bookingpress_category})";
			}
			$bookingpress_search_query_join  .= "LEFT JOIN {$tbl_bookingpress_services} AS service ON category.bookingpress_category_id = service.bookingpress_category_id";
			$bookingpress_search_query_where .= " AND category.bookingpress_category_id = service.bookingpress_category_id";
			if ( ! empty( $bookingpress_service ) ) {
				$bookingpress_search_query_where .= " AND service.bookingpress_service_id IN ({$bookingpress_service})";
				$bookingpress_front_vue_data_fields['appointment_step_form_data']['total_services'] = $bookingpress_service;
			}
			$bookingpress_search_query_where .= " GROUP BY bookingpress_category_id";
			$bookingpress_service_categories = $wpdb->get_results( "SELECT category.* FROM {$tbl_bookingpress_categories} AS category {$bookingpress_search_query_join} {$bookingpress_search_query_where} ORDER BY bookingpress_category_position ASC", ARRAY_A );			

			$bookingpress_front_vue_data_fields['service_categories'] = $bookingpress_service_categories;
			$default_service_category                                 = ! empty( $bookingpress_service_categories[0]['bookingpress_category_id'] ) ? $bookingpress_service_categories[0]['bookingpress_category_id'] : 0;

			$bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_category'] = $default_service_category;
			$bookingpress_service_search_query_where = '';
			if ( ! empty( $bookingpress_service ) ) {
				$bookingpress_service_search_query_where .= " AND bookingpress_service_id IN ({$bookingpress_service})";
			}
			$service_data = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_services} WHERE bookingpress_category_id = {$default_service_category} {$bookingpress_service_search_query_where}", ARRAY_A );


			$bookingpress_display_service_description = $BookingPress->bookingpress_get_customize_settings('display_service_description', 'booking_form' );
			foreach ( $service_data as $service_key => $service_val ) {
				$service_data[ $service_key ]['service_price_without_currency'] = $service_val['bookingpress_service_price'];
				$service_data[ $service_key ]['bookingpress_service_price']     = $BookingPress->bookingpress_price_formatter_with_currency_symbol( $service_val['bookingpress_service_price'] );

				$service_id                              = $service_val['bookingpress_service_id'];
				$service_meta_details                    = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_servicesmeta} WHERE bookingpress_service_id = {$service_id} AND bookingpress_servicemeta_name = 'service_image_details'", ARRAY_A );
				$service_img_details                     = ! empty( $service_meta_details['bookingpress_servicemeta_value'] ) ? unserialize( $service_meta_details['bookingpress_servicemeta_value'] ) : array();
				$service_data[ $service_key ]['img_url'] = ! empty( $service_img_details[0]['url'] ) ? $service_img_details[0]['url'] : BOOKINGPRESS_URL . '/images/placeholder-img.jpg';

				$default_service_description = ! empty( $service_val['bookingpress_service_description'] ) ? $service_val['bookingpress_service_description'] : '';		

				if($bookingpress_display_service_description == 'true') {
					$service_data[ $service_key ]['display_read_more_less'] = 1;						
					$service_data[ $service_key ]['bookingpress_service_description'] = stripslashes($default_service_description);
					if(strlen($default_service_description) > 140 ){
						$service_data[ $service_key ]['bookingpress_service_description_with_excerpt'] = stripslashes(substr($default_service_description, 0, 140));
						$service_data[ $service_key ]['display_details_more'] = 0;
						$service_data[ $service_key ]['display_details_less'] = 1;
					} else {
						$service_data[ $service_key ]['display_read_more_less'] = 0 ;
					}

				}
			}
			if($bookingpress_display_service_description == 'true') {
				$bookingpress_front_vue_data_fields['display_service_description'] = '1';
			}			
			$bookingpress_front_vue_data_fields['services_data'] = $service_data;
			$default_service_id                                  = ! empty( $service_data[0]['bookingpress_service_id'] ) ? $service_data[0]['bookingpress_service_id'] : 0;
			$default_service_name                                = ! empty( $service_data[0]['bookingpress_service_name'] ) ? $service_data[0]['bookingpress_service_name'] : '';
			$default_price                                       = ! empty( $service_data[0]['bookingpress_service_price'] ) ? $service_data[0]['bookingpress_service_price'] : 0;
			$default_price_with_currency                         = ! empty( $service_data[0]['service_price_without_currency'] ) ? $service_data[0]['service_price_without_currency'] : 0;


			$bookingpress_is_hide_category_service_selection = $BookingPress->bookingpress_get_customize_settings('hide_category_service_selection', 'booking_form');

			if($bookingpress_is_hide_category_service_selection == "true"){
				$bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_service']               = $default_service_id;
				$bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_service_name']          = $default_service_name;
				$bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_service_price']         = $default_price;
				$bookingpress_front_vue_data_fields['appointment_step_form_data']['service_price_without_currency'] = $default_price_with_currency;
			}

			$on_site_payment = $BookingPress->bookingpress_get_settings( 'on_site_payment', 'payment_setting' );
			$paypal_payment  = $BookingPress->bookingpress_get_settings( 'paypal_payment', 'payment_setting' );

			$bookingpress_front_vue_data_fields['on_site_payment'] = $on_site_payment;
			$bookingpress_front_vue_data_fields['paypal_payment']  = $paypal_payment;

			if (! $on_site_payment ) {
				$bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_payment_method'] = 'on-site';
			}

			if ( $bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_payment_method'] == '' && ($paypal_payment == "true") ) {
				$bookingpress_front_vue_data_fields['paypal_payment'] = 'paypal';
			}

			if ( is_user_logged_in() ) {
				$current_user_id             = get_current_user_id();
				$bookingpress_current_user_obj = new WP_User($current_user_id);

				$bookingpress_check_customer = $wpdb->get_var( "SELECT COUNT(bookingpress_customer_id) as total_cnt FROM {$tbl_bookingpress_customers} WHERE bookingpress_wpuser_id = {$current_user_id} AND bookingpress_user_type = 2" );
				if ( $bookingpress_check_customer ) {
					$get_current_user_data = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_customers} WHERE bookingpress_wpuser_id = {$current_user_id} AND bookingpress_user_type = 2", ARRAY_A );
					if ( ! empty( $get_current_user_data ) ) {
						$bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_name']      = $get_current_user_data['bookingpress_user_login'];
						$bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_phone']     = $get_current_user_data['bookingpress_user_phone'];
						$bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_email']     = $get_current_user_data['bookingpress_user_email'];
						$bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_firstname'] = $get_current_user_data['bookingpress_user_firstname'];
						$bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_lastname']  = $get_current_user_data['bookingpress_user_lastname'];
					}
				}else if(!empty($current_user_id) && !empty($bookingpress_current_user_obj)){
					$bookingpress_customer_name = !empty($bookingpress_current_user_obj->data->user_login) ? $bookingpress_current_user_obj->data->user_login : '';
					$bookingpress_customer_email = !empty($bookingpress_current_user_obj->data->user_email) ? $bookingpress_current_user_obj->data->user_email : '';
					$bookingpress_firstname = get_user_meta($current_user_id, 'first_name', true);
					$bookingpress_lastname = get_user_meta($current_user_id, 'last_name', true);

					$bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_name']      = $bookingpress_customer_name;
					$bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_email']     = $bookingpress_customer_email;
					$bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_firstname'] = $bookingpress_firstname;
					$bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_lastname']  = $bookingpress_lastname;
				}
			}
			$bookingpress_phone_mandatory_option = $BookingPress->bookingpress_get_settings( 'phone_number_mandatory', 'general_setting' );
			if ( ! empty( $bookingpress_phone_mandatory_option ) && $bookingpress_phone_mandatory_option == 'true' ) {
				$mandatory_field_data = array(
					'required' => true,
					'message'  => __( 'Please enter customer phone number', 'bookingpress-appointment-booking' ),
					'trigger'  => 'blur',
				);
				$bookingpress_front_vue_data_fields['customer_details_rule']['customer_phone'] = $mandatory_field_data;
			}

			$bookingpress_phone_country_option = $BookingPress->bookingpress_get_settings( 'default_phone_country_code', 'general_setting' );
			$bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_phone_country'] = $bookingpress_phone_country_option;
			$bookingpress_front_vue_data_fields['bookingpress_tel_input_props'] = array(
				'defaultCountry' => $bookingpress_phone_country_option,
			);

			$default_daysoff_details = $BookingPress->bookingpress_get_default_dayoff_dates();
			$disabled_date           = implode( ',', $default_daysoff_details );
			$bookingpress_front_vue_data_fields['days_off_disabled_dates'] = $disabled_date;

			$bookingpress_selected_date = $BookingPress->bookingpress_select_date_before_load();															
			if(!empty($bookingpress_selected_date)){					
				$bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_date'] = $bookingpress_selected_date;
			}
			
			$bookingpress_front_vue_data_fields = apply_filters( 'bookingpress_frontend_apointment_form_add_dynamic_data', $bookingpress_front_vue_data_fields );
			echo wp_json_encode($bookingpress_front_vue_data_fields);
		}

		function bookingpress_booking_dynamic_helper_vars_func() {
			global $bookingpress_global_options;
			$bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_locale_lang = $bookingpress_options['locale'];
			?>
			var lang = ELEMENT.lang.<?php echo esc_html( $bookingpress_locale_lang ); ?>;
			ELEMENT.locale(lang)
			<?php
			do_action( 'bookingpress_add_calendar_dynamic_helper_vars' );
		}


		function bookingpress_booking_dynamic_on_load_methods_func() {
				do_action('bookingpress_add_appointment_booking_on_load_methods');
			?>
			<?php
		}

		function bookingpress_booking_dynamic_vue_methods_func() {
			global $BookingPress;

			$bookingpress_current_date                    = date( 'Y-m-d', current_time( 'timestamp' ) );
			$no_appointment_time_selected_for_the_booking = $BookingPress->bookingpress_get_settings( 'no_appointment_time_selected_for_the_booking', 'message_setting' );
			$no_service_selected_for_the_booking = $BookingPress->bookingpress_get_settings('no_service_selected_for_the_booking', 'message_setting');
			?>
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
				bookingpress_set_error_msg(error_msg){
					const vm = this
					vm.is_display_error = '1'
					vm.is_error_msg = error_msg
					window.scrollTo({
						top: 0,
						behavior: 'smooth',
					});
				},
				bookingpress_remove_error_msg(){
					const vm = this
					vm.is_display_error = '0'
					vm.is_error_msg = ''
				},
				checkBeforeBookAppointment(){
					const vm = this
					var postData = { action:'bookingpress_before_book_appointment', appointment_data: vm.appointment_step_form_data,_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' };
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						if(response.data.variant == 'error'){
							vm.bookingpress_set_error_msg(response.data.msg)
							if(response.data.error_type == 'dayoff'){
								vm.service_timing = []
							}
						}else{
							vm.bookingpress_remove_error_msg()
						}
					}.bind(this) )
					.catch( function (error) {
						vm.bookingpress_set_error_msg(error)
					});
				},
				book_appointment(){
					const vm2 = this
					vm2.isLoadBookingLoader = '1'
					vm2.isBookingDisabled = true
					vm2.checkBeforeBookAppointment()
					setTimeout(function(){
						if(vm2.is_display_error != '1'){
							var postData = { action:'bookingpress_front_save_appointment_booking', appointment_data: vm2.appointment_step_form_data,_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' };
							axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
							.then( function (response) {
								vm2.isLoadBookingLoader = '0'
								vm2.isBookingDisabled = false
								if(response.data.variant == 'redirect'){
									vm2.bookingpress_remove_error_msg()
									var bookingpress_created_element = document.createElement('div');
									bookingpress_created_element.innerHTML = response.data.redirect_data;
									bookingpress_created_element.className = 'bookingpress_runtime_script';
									document.body.appendChild(bookingpress_created_element);
									var scripts = document.getElementsByClassName("bookingpress_runtime_script");
									var text = scripts[scripts.length - 1].textContent;
									eval(text);
								}else if(response.data.variant == 'redirect_url'){
									vm2.bookingpress_remove_error_msg()
									window.location.href = response.data.redirect_data
								}else if(response.data.variant == 'error'){
									vm2.bookingpress_set_error_msg(response.data.msg)
								}else{
									vm2.bookingpress_remove_error_msg()
								}
							}.bind(this) )
							.catch( function (error) {
								vm2.bookingpress_set_error_msg(error)
							});
						}else{
							vm2.isLoadBookingLoader = '0'
							vm2.isBookingDisabled = false
						}
					}, 3000);
				},
				selectStepCategory(selected_cat_id, selected_cat_name,total_services){
					const vm = this
					vm.appointment_step_form_data.selected_category = selected_cat_id
					vm.appointment_step_form_data.selected_cat_name = selected_cat_name

					var postData = { action:'bookingpress_front_get_category_services',category_id: selected_cat_id,total_service: total_services
,_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' };
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						vm.services_data = response.data
					}.bind(this) )
					.catch( function (error) {
						console.log(error);
					});
				},
				selectDate(selected_service_id, service_name, service_price, service_price_without_currency){					
					const vm = this
					vm.service_timing = ''
					vm.automatic_next_page(2)
					vm.isLoadTimeLoader = '1'
					vm.appointment_step_form_data.selected_service = selected_service_id
					vm.appointment_step_form_data.selected_service_name = service_name
					vm.appointment_step_form_data.selected_service_price = service_price									
					vm.appointment_step_form_data.service_price_without_currency = service_price_without_currency										
					var selected_date = vm.appointment_step_form_data.selected_date
					var formatted_date = vm.get_formatted_date(selected_date)					
					var postData = { action:'bookingpress_front_get_timings', service_id: selected_service_id,selected_date: formatted_date,_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' };
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						setTimeout(function(){
							vm.service_timing = response.data
							vm.isLoadTimeLoader = '0'
							vm.displayResponsiveCalendar = '0'
						}, 1500);

						<?php do_action('bookingpress_after_selecting_booking_service'); ?>
					}.bind(this) )
					.catch( function (error) {
						vm.isLoadTimeLoader = '0'
						console.log(error);
					});
				},
				get_date_timings(current_selected_date = ''){
					const vm = this
					vm.service_timing = ''
					vm.isLoadTimeLoader = '1'
					if( current_selected_date == '') {
						current_selected_date =	vm.appointment_step_form_data.selected_date
					}
					vm.appointment_step_form_data.selected_date = current_selected_date
					var selected_date = vm.appointment_step_form_data.selected_date
					var formatted_date = vm.get_formatted_date(selected_date)					
					vm.appointment_step_form_data.selected_date = formatted_date					
					vm.appointment_step_form_data.selected_start_time = ''
					vm.appointment_step_form_data.selected_end_time = ''
					var selected_service_id = vm.appointment_step_form_data.selected_service
					var postData = { action:'bookingpress_front_get_timings', service_id: selected_service_id, selected_date: formatted_date,_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' };
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						setTimeout(function(){
							vm.service_timing = response.data
							vm.isLoadTimeLoader = '0'
							vm.displayResponsiveCalendar = '0'
						}, 1500);

						<?php do_action('bookingpress_after_selecting_booking_service'); ?>
					}.bind(this) )
					.catch( function (error) {
						console.log(error);
					});
				},
				selectTiming(selected_start_time, selected_end_time){
					const vm = this
					vm.appointment_step_form_data.selected_start_time = selected_start_time
					vm.appointment_step_form_data.selected_end_time = selected_end_time
					vm.automatic_next_page(3)
				},
				resetForm(){
					const vm2 = this
					vm2.appointment_formdata.appointment_selected_customer = '<?php echo get_current_user_id(); ?>'
					vm2.appointment_formdata.appointment_selected_service = ''
					vm2.appointment_formdata.appointment_booked_date = '<?php echo date( 'Y-m-d', current_time( 'timestamp' ) ); ?>';
					vm2.appointment_formdata.appointment_booked_time = ''
				},
				select_service(selected_service_id){
					const vm = this
					vm.appointment_step_form_data.selected_service = selected_service_id
				},
				automatic_next_page(next_tab_id){
					const vm = this
					vm.current_selected_tab_id = next_tab_id;
					vm.bookingpress_remove_error_msg()
				},
				next_page(customer_form = '', current_selected_element = ''){
					const vm = this
					var current_selected_tab = bpa_selected_tab = parseFloat(vm.current_selected_tab_id)
					vm.previous_selected_tab_id = current_selected_tab
					if(current_selected_element != undefined && current_selected_element != null){
						current_selected_tab = current_selected_element
					}
					if(current_selected_tab == 1){
						vm.is_display_error = '0'
						if(vm.appointment_step_form_data.selected_service == "" || vm.appointment_step_form_data.selected_service == undefined || vm.appointment_step_form_data.selected_service == 'undefined'){
							vm.bookingpress_set_error_msg('<?php esc_html_e( $no_service_selected_for_the_booking, 'bookingpress-appointment-booking' ); ?>')
							vm.current_selected_tab_id = 1
							return false;
						}else{
							current_selected_tab = current_selected_tab + 1;
							vm.current_selected_tab_id = current_selected_tab;
						}
					}else if(current_selected_tab == 2){
						if(current_selected_element != undefined && current_selected_element == 2  && vm.appointment_step_form_data.selected_start_time == '' && bpa_selected_tab == '2' ) {
							vm.bookingpress_set_error_msg('<?php esc_html_e( $no_appointment_time_selected_for_the_booking, 'bookingpress-appointment-booking' ); ?>')
							vm.current_selected_tab_id = 2
							return false;
						}
						if(vm.is_display_error != '1'){
							current_selected_tab = current_selected_tab + 1;
							vm.current_selected_tab_id = current_selected_tab;
							vm.bookingpress_remove_error_msg()
						}else{
							if(vm.is_error_msg == ''){
								vm.bookingpress_set_error_msg('<?php esc_html_e( 'Something went wrong', 'bookingpress-appointment-booking' ); ?>')
							}
						}
					}else if(current_selected_tab == 3){
						if(vm.appointment_step_form_data.selected_start_time == ''){
							vm.bookingpress_set_error_msg('<?php esc_html_e( $no_appointment_time_selected_for_the_booking, 'bookingpress-appointment-booking' ); ?>')
							vm.current_selected_tab_id = 2
							return false;
						}else{
							vm.$refs[customer_form].validate((valid) => {
								if (valid) {
									current_selected_tab = current_selected_tab + 1;
									vm.current_selected_tab_id = current_selected_tab;
								}
							});	
						}
					}else{
						vm.$refs[customer_form].validate((valid) => {
							if (valid) {
								current_selected_tab = current_selected_tab + 1;
								vm.current_selected_tab_id = current_selected_tab;
							}else{
								vm.current_selected_tab_id = 3
							}
						});
					}
					if(current_selected_tab == 2 && vm.appointment_step_form_data.selected_start_time == '' && vm.appointment_step_form_data.selected_date != '' ) {
						vm.get_date_timings()
					}
				},
				previous_page(){
					const vm = this
					var current_selected_tab = parseFloat(vm.current_selected_tab_id)
					vm.previous_selected_tab_id = current_selected_tab
					current_selected_tab = current_selected_tab - 1;
					vm.current_selected_tab_id = current_selected_tab;
				},
				select_payment_method(payment_method){
					const vm = this
					vm.appointment_step_form_data.selected_payment_method = payment_method
					<?php do_action('bookingpress_after_selecting_payment_method'); ?>
				},
				displayCalendar(){
					const vm = this
					vm.displayResponsiveCalendar = '1'
				},
				Change_front_appointment_description(service_id) {												
					const vm = this;
					vm.services_data.forEach(function(item, index, arr){					
						if(item.bookingpress_service_id == service_id ){						
							if(item.display_details_more == 0 && item.display_details_less == 1) {
								item.display_details_less = 0;
								item.display_details_more = 1;								
							} else {
								item.display_details_more = 0;
								item.display_details_less = 1;
							}
						}					
					});
				},
				bookingpress_phone_country_change_func(bookingpress_country_obj){
					const vm = this
					var bookingpress_selected_country = bookingpress_country_obj.iso2
					vm.appointment_step_form_data.customer_phone_country = bookingpress_selected_country
				},
				<?php				
				do_action('bookingpress_add_appointment_booking_vue_methods');
		}

		function bookingpress_front_appointments_dynamic_data_fields_func() {
			global $bookingpress_front_appointment_vue_data_fields;
			$bookingpress_front_appointment_vue_data_fields = apply_filters( 'bookingpress_front_appointment_add_dynamic_data', $bookingpress_front_appointment_vue_data_fields );
			echo json_encode( $bookingpress_front_appointment_vue_data_fields );
		}

		function bookingpress_front_appointments_dynamic_helper_vars_func() {
			global $bookingpress_global_options;
			$bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_locale_lang = $bookingpress_options['locale'];
			?>
			var lang = ELEMENT.lang.<?php echo esc_html( $bookingpress_locale_lang ); ?>;
			ELEMENT.locale(lang)
			<?php
			do_action( 'bookingpress_add_front_appointment_helper_vars' );
		}
		function bookingpress_front_appointments_dynamic_on_load_methods_func() {
			?>
			this.loadFrontAppointments();
			<?php
		}

		function bookingpress_front_appointments_dynamic_vue_methods_func() {
			?>

			toggleBusy() {
				if(this.is_display_loader == '1'){
					this.is_display_loader = '0'
				}else{
					this.is_display_loader = '1'
				}
			},	
			loadFrontAppointments() {					
				const vm = this
				this.toggleBusy()
				var bookingpress_search_data = { 'search_appointment':this.search_appointment,'selected_date_range': this.appointment_date_range}
				var postData = { action:'bookingpress_get_customer_appointments',search_data: bookingpress_search_data,_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>'};
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )				
				.then( function (response) {
					this.toggleBusy()
					this.items = response.data.items;
				}.bind(this) )
				.catch( function (error) {					
					vm.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something Went wrong...', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
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
					this.appointment_date_range[0] = this.get_formatted_date(this.appointment_date_range[0])				
					this.appointment_date_range[1] = this.get_formatted_date(this.appointment_date_range[1])
				}
			},
			cancelAppointment( appointment_id){				
				const vm = new Vue()
				const vm2 = this
				vm2.is_display_loader = '1'
				vm2.is_disabled = true
				var cancel_id = appointment_id
				var appointment_cancel_data = { action: 'bookingpress_cancel_appointment', cancel_id: cancel_id,_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' }
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( appointment_cancel_data ) )
				.then(function(response){
					vm2.is_display_loader = '0'
					vm2.is_disabled = false
					if(response.data.variant != 'error'){
						window.location.href = response.data.redirect_url;
					}else{
						vm2.$notify({
							title: response.data.title,
							message: response.data.msg,
							type: response.data.variant,
							customClass: response.data.variant+'_notification',
						});
						vm2.loadFrontAppointments()
					}
				}).catch(function(error){
					console.log(error);
					vm2.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something Went wrong...', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
					});
				});
			},
			resetFilter(){				
				const vm = this
				if(vm.search_appointment != '' || vm.appointment_date_range != '') {					
					vm.search_appointment = '';
					vm.appointment_date_range = ''
					vm.loadFrontAppointments()
				}
			}	
			<?php
		}
	}
}

global $bookingpress_appointment_bookings, $bookingpress_front_vue_data_fields,$bookingpress_front_appointment_vue_data_fields;
$bookingpress_appointment_bookings = new bookingpress_appointment_bookings();
$bookingpress_options              = $bookingpress_global_options->bookingpress_global_options();
$bookingpress_country_list         = $bookingpress_options['country_lists'];


$bookingpress_front_vue_data_fields             = array(
	'appointment_services_list'  => array(),
	'appointment_formdata'       => array(
		'appointment_selected_customer' => get_current_user_id(),
		'appointment_selected_service'  => '',
		'appointment_booked_date'       => date( 'Y-m-d', current_time( 'timestamp' ) ),
		'appointment_booked_time'       => '',
		'appointment_on_site_enabled'   => false,
	),
	'phone_countries_details'    => json_decode( $bookingpress_country_list ),
	'final_payable_amount'       => '',
	'activeStepNumber'           => 0,
	'service_categories'         => array(),
	'services_data'              => array(),
	'service_timing'             => array(),
	'on_site_payment'            => false,
	'paypal_payment'             => false,
	'appointment_step_form_data' => array(
		'selected_category'              => '',
		'selected_cat_name'              => '',
		'selected_service'               => '',
		'selected_service_name'          => '',
		'selected_service_price'         => '',
		'service_price_without_currency' => 0,
		'selected_date'                  => date( 'Y-m-d'),
		'selected_start_time'            => '',
		'selected_end_time'              => '',
		'customer_name'                  => '',
		'customer_firstname'             => '',
		'customer_lastname'              => '',
		'customer_phone'                 => '',
		'customer_email'                 => '',
		'appointment_note'               => '',
		'selected_payment_method'        => '',
		'customer_phone_country'         => 'us',
		'total_services'                 => '',
	),
	'customer_details_rule'      => array(
		'customer_name'  => array(
			'required' => true,
			'message'  => __( 'Please enter customer name', 'bookingpress-appointment-booking' ),
			'trigger'  => 'blur',
		),
		'customer_email' => array(
			'required' => true,
			'message'  => __( 'Please enter customer email', 'bookingpress-appointment-booking' ),
			'trigger'  => 'blur',
		),
	),
	'current_selected_tab_id'    => '1',
	'previous_selected_tab_id'   => '1',
	'isLoadTimeLoader'           => '0',
	'isLoadBookingLoader'        => '0',
	'isBookingDisabled'          => false,
	'displayResponsiveCalendar'  => '0',
	'display_service_description' => '0',
);
$bookingpress_front_appointment_vue_data_fields = array(
	'items'                    => array(),
	'search_appointment'       => '',
	'appointment_date_range'   => array( date( 'Y-m-d', strtotime( '-3 Day' ) ), date( 'Y-m-d', strtotime( '+3 Day' ) ) ),
	'appointment_service_name' => '',
	'appointment_date'         => '',
	'appointment_duration'     => '',
	'appointment_status'       => '',
	'appointment_payment'      => '',
	'is_disabled'              => false,
);
