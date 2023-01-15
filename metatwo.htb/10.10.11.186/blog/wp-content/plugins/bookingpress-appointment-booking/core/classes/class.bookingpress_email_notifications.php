<?php
if ( ! class_exists( 'bookingpress_email_notifications' ) ) {
	class bookingpress_email_notifications {
		var $bookingpress_email_notification_type = '';
		var $bookingpress_email_sender_name       = '';
		var $bookingpress_email_sender_email      = '';
		var $bookingpress_admin_email             = '';
		var $bookingpress_smtp_username           = '';
		var $bookingpress_smtp_password           = '';
		var $bookingpress_smtp_host               = '';
		var $bookingpress_smtp_port               = '';
		var $bookingpress_smtp_secure             = '';

		function __construct() {
			add_filter( 'bookingpress_modify_email_notification_content', array( $this, 'bookingpress_modify_email_content_func' ), 10, 2 );
		}

		function bookingpress_init_emai_config() {
			global $BookingPress;
			$this->bookingpress_email_notification_type = esc_html( $BookingPress->bookingpress_get_settings( 'selected_mail_service', 'notification_setting' ) );
			$this->bookingpress_email_sender_name       = esc_html( $BookingPress->bookingpress_get_settings( 'sender_name', 'notification_setting' ) );
			$this->bookingpress_email_sender_email      = esc_html( $BookingPress->bookingpress_get_settings( 'sender_email', 'notification_setting' ) );

			if ( $this->bookingpress_email_notification_type == 'smtp' ) {
				$this->bookingpress_smtp_username = esc_html( $BookingPress->bookingpress_get_settings( 'smtp_username', 'notification_setting' ) );
				$this->bookingpress_smtp_password = $BookingPress->bookingpress_get_settings( 'smtp_password', 'notification_setting' );
				$this->bookingpress_smtp_host     = $BookingPress->bookingpress_get_settings( 'smtp_host', 'notification_setting' );
				$this->bookingpress_smtp_port     = esc_html( $BookingPress->bookingpress_get_settings( 'smtp_port', 'notification_setting' ) );
				$this->bookingpress_smtp_secure   = esc_html( $BookingPress->bookingpress_get_settings( 'smtp_secure', 'notification_setting' ) );
			}
		}

		function bookingpress_send_test_email_notification( $smtp_host, $smtp_port, $smtp_secure, $smtp_username, $smtp_password, $smtp_test_receiver_email, $smtp_test_msg, $smtp_sender_email, $smtp_sender_name ) {
			global $wpdb, $BookingPress, $wp_version;
			$is_mail_sent     = 0;
			$return_error_msg = __( 'SMTP Test Email cannot sent successfully', 'bookingpress-appointment-booking' );
			$return_error_log = '';

			if ( ! empty( $smtp_host ) && ! empty( $smtp_port ) && ! empty( $smtp_secure ) && ! empty( $smtp_username ) && ! empty( $smtp_password ) && ! empty( $smtp_test_receiver_email ) && ! empty( $smtp_test_msg ) && ! empty( $smtp_sender_email ) && ! empty( $smtp_sender_name ) ) {
				if ( version_compare( $wp_version, '5.5', '<' ) ) {
					require_once ABSPATH . WPINC . '/class-phpmailer.php';
					require_once ABSPATH . WPINC . '/class-smtp.php';
					$BookingPressMailer = new PHPMailer();
				} else {
					require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
					require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
					require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
					$BookingPressMailer = new PHPMailer\PHPMailer\PHPMailer();
				}

				$BookingPressMailer->CharSet   = 'UTF-8';
				$BookingPressMailer->SMTPDebug = 1; // change this value to 1 for debug
				ob_start();
				echo '<span class="bpa-smtp-notification-error-msg">';
				echo addslashes(esc_html__('The SMTP debugging output is shown below:', 'bookingpress-appointment-booking'));
				echo '</span><pre>';
				$BookingPressMailer->isSMTP();
				$BookingPressMailer->Host     = $smtp_host;
				$BookingPressMailer->SMTPAuth = true;
				$BookingPressMailer->Username = $smtp_username;
				$BookingPressMailer->Password = $smtp_password;
				if ( ! empty( $smtp_secure ) && $smtp_secure != 'Disabled' ) {
					$BookingPressMailer->SMTPSecure = strtolower( $smtp_secure );
				}
				if ( $smtp_secure == 'Disabled' ) {
					$BookingPressMailer->SMTPAutoTLS = false;
				}
				$BookingPressMailer->Port = $smtp_port;
				$BookingPressMailer->setFrom( $smtp_sender_email, $smtp_sender_name );
				$BookingPressMailer->addReplyTo( $smtp_sender_email, $smtp_sender_name );
				$BookingPressMailer->addAddress( $smtp_test_receiver_email );
				$BookingPressMailer->isHTML( true );
				$bookingpress_email_subject  = esc_html( 'BookingPress SMTP Test Email Notification', 'bookingpress-appointment-booking' );
				$BookingPressMailer->Subject = $bookingpress_email_subject;
				$BookingPressMailer->Body    = $smtp_test_msg;

				if(!$BookingPressMailer->send()) {									
					echo '</pre><span class="bpa-dialog--sns__body--error-title">';
					echo addslashes(esc_html__('The full debugging output is shown below:', 'bookingpress-appointment-booking'));
					echo '</span>';
					var_dump($BookingPressMailer);
					$smtp_debug_log = ob_get_clean();
					$return_error_log .='<pre>';
					$return_error_log .=$smtp_debug_log;
					$return_error_log .='</pre>';
					$return_error_msg = $BookingPressMailer->ErrorInfo;
				} else {
					$smtp_debug_log = ob_get_clean();
					$is_mail_sent = 1;					
					$return_error_msg = '';
				}
			}

			$return_msg = array(							
				'is_mail_sent' => $is_mail_sent,
				'error_msg'    => $return_error_msg,
				'error_log_msg' => $return_error_log,
			);			
			echo json_encode( $return_msg );
			exit;
		}


		function bookingpress_send_email_notification( $template_type, $notification_name, $appointment_id, $receiver_email_id ) {
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $wp_version;

			$this->bookingpress_init_emai_config();
			$is_mail_sent = 0;
			if ( ! empty( $this->bookingpress_email_notification_type ) && ! empty( $this->bookingpress_email_sender_name ) && ! empty( $this->bookingpress_email_sender_email ) && ! empty( $template_type ) && ! empty( $notification_name ) && ! empty( $receiver_email_id ) ) {
				$bookingpress_appointment_data = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = {$appointment_id}", ARRAY_A );

				$bookingpress_is_allowed_email_notification = ! empty( $bookingpress_appointment_data['bookingpress_appointment_send_notification'] ) ? 1 : 0;
				if ( $bookingpress_is_allowed_email_notification ) {
					$bookingpress_get_email_template_details = $this->bookingpress_get_email_template_details( $template_type, $notification_name, $bookingpress_appointment_data );
					$bookingpress_email_subject              = $bookingpress_email_content = '';
					if ( ! empty( $bookingpress_get_email_template_details ) ) {
						$bookingpress_email_subject = $bookingpress_get_email_template_details['notification_subject'];
						$bookingpress_email_content = $bookingpress_get_email_template_details['notification_message'];
					}

					switch ( $this->bookingpress_email_notification_type ) {
						case 'php_mail':
							$bookingpress_email_header_data = 'From: ' . $this->bookingpress_email_sender_name . '<' . $this->bookingpress_email_sender_email . "> \r\n";

							if ( mail( $receiver_email_id, $bookingpress_email_subject, $bookingpress_email_content, $bookingpress_email_header_data ) ) {
								$is_mail_sent = 1;
							}
							break;
						case 'wp_mail':
							if ( wp_mail( $receiver_email_id, $bookingpress_email_subject, $bookingpress_email_content, $bookingpress_email_header_data ) ) {
								$is_mail_sent = 1;
							}
							break;
						case 'smtp':
							if ( ! empty( $this->bookingpress_smtp_username ) && ! empty( $this->bookingpress_smtp_password ) && ! empty( $this->bookingpress_smtp_host ) && ! empty( $this->bookingpress_smtp_port ) && ! empty( $this->bookingpress_smtp_secure ) ) {
								if ( version_compare( $wp_version, '5.5', '<' ) ) {
									require_once ABSPATH . WPINC . '/class-phpmailer.php';
									require_once ABSPATH . WPINC . '/class-smtp.php';
									$BookingPressMailer = new PHPMailer();
								} else {
									require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
									require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
									require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
									$BookingPressMailer = new PHPMailer\PHPMailer\PHPMailer();
								}

								$BookingPressMailer->CharSet   = 'UTF-8';
								$BookingPressMailer->SMTPDebug = 0; // change this value to 1 for debug
								$BookingPressMailer->isSMTP();
								$BookingPressMailer->Host     = $this->bookingpress_smtp_host;
								$BookingPressMailer->SMTPAuth = true;
								$BookingPressMailer->Username = $this->bookingpress_smtp_username;
								$BookingPressMailer->Password = $this->bookingpress_smtp_password;
								if ( ! empty( $this->bookingpress_smtp_secure ) && $this->bookingpress_smtp_secure != 'Disabled' ) {
									$BookingPressMailer->SMTPSecure = strtolower( $this->bookingpress_smtp_secure );
								}
								if ( $this->bookingpress_smtp_secure == 'Disabled' ) {
									$BookingPressMailer->SMTPAutoTLS = false;
								}
								$BookingPressMailer->Port = $this->bookingpress_smtp_port;
								$BookingPressMailer->setFrom( $this->bookingpress_email_sender_email, $this->bookingpress_email_sender_name );
								$BookingPressMailer->addReplyTo( $this->bookingpress_email_sender_email, $this->bookingpress_email_sender_name );
								$BookingPressMailer->addAddress( $receiver_email_id );
								$BookingPressMailer->isHTML( true );
								$BookingPressMailer->Subject = $bookingpress_email_subject;
								$BookingPressMailer->Body    = $bookingpress_email_content;

								if ( $BookingPressMailer->send() ) {
									$is_mail_sent = 1;
								}
							}
							break;
						default:
							break;
					}
				}
			}

			return $is_mail_sent;
		}

		function bookingpress_get_email_template_details( $template_type, $notification_name, $bookingpress_appointment_data ) {
			global $wpdb, $tbl_bookingpress_notifications;
			$bookingpress_template_data = array();
			if ( ! empty( $template_type ) && ! empty( $notification_name ) ) {
				$bookingpress_email_data = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_name = '{$notification_name}' AND bookingpress_notification_receiver_type = '{$template_type}' AND bookingpress_notification_status = 1", ARRAY_A );
				if ( ! empty( $bookingpress_email_data ) ) {
					$bookingpress_template_data['notification_subject'] = esc_html( $bookingpress_email_data['bookingpress_notification_subject'] );

					$bookingpress_email_data['bookingpress_notification_message'] = apply_filters( 'bookingpress_modify_email_notification_content', $bookingpress_email_data['bookingpress_notification_message'], $bookingpress_appointment_data );
					$bookingpress_template_data['notification_message']           = $bookingpress_email_data['bookingpress_notification_message'];
				}
			}
			return $bookingpress_template_data;
		}

		function bookingpress_modify_email_content_func( $template_content, $bookingpress_appointment_data ) {
			global $wpdb, $BookingPress, $bookingpress_spam_protection, $tbl_bookingpress_customers;

			if ( ! empty( $bookingpress_appointment_data ) ) {
				$appointment_customer_id   = esc_html( $bookingpress_appointment_data['bookingpress_customer_id'] );
				$appointment_customer_data = $wpdb->get_row("SELECT * FROM {$tbl_bookingpress_customers} WHERE bookingpress_customer_id = {$appointment_customer_id}", ARRAY_A);
				if ( ! empty( $appointment_customer_data ) ) {
					$customer_email     = ! empty( $appointment_customer_data['bookingpress_user_email'] ) ? esc_html( $appointment_customer_data['bookingpress_user_email'] ) : '';
					$customer_firstname = ! empty( $appointment_customer_data['bookingpress_user_firstname'] ) ? esc_html( $appointment_customer_data['bookingpress_user_firstname'] ) : '';
					$customer_lastname  = ! empty( $appointment_customer_data['bookingpress_user_lastname'] ) ? esc_html( $appointment_customer_data['bookingpress_user_lastname'] ) : '';
					$customer_fullname  = $customer_firstname . ' ' . $customer_lastname;
					$customer_phone     = ! empty( $appointment_customer_data['bookingpress_user_phone'] ) ? esc_html( $appointment_customer_data['bookingpress_user_phone'] ) : '';
					$customer_note      = ! empty( $bookingpress_appointment_data['bookingpress_appointment_internal_note'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_appointment_internal_note'] ) : '';

					$template_content = str_replace( '%customer_email%', $customer_email, $template_content );
					$template_content = str_replace( '%customer_first_name%', $customer_firstname, $template_content );
					$template_content = str_replace( '%customer_full_name%', $customer_fullname, $template_content );
					$template_content = str_replace( '%customer_last_name%', $customer_lastname, $template_content );
					$template_content = str_replace( '%customer_note%', $customer_note, $template_content );

					$bpa_unique_id          = $bookingpress_spam_protection->bookingpress_generate_captcha_code( 10 );
					$bpa_customer_wpuser_id = intval( $appointment_customer_data['bookingpress_wpuser_id'] );
					if ( empty( get_user_meta( $bpa_customer_wpuser_id, 'bpa_cancel_id', true ) ) ) {
						update_user_meta( $bpa_customer_wpuser_id, 'bpa_cancel_id', $bpa_unique_id );
					}

					$bookingpress_cancel_appointment_link = BOOKINGPRESS_HOME_URL . '/?bpa_cancel=' . base64_encode( $bookingpress_appointment_data['bookingpress_appointment_booking_id'] ) . '&cancel_id=' . $bpa_unique_id;
					$template_content                     = str_replace( '%customer_cancel_appointment_link%', $bookingpress_cancel_appointment_link, $template_content );
				}

				$company_name    = esc_html( $BookingPress->bookingpress_get_settings( 'company_name', 'company_setting' ) );
				$company_address = esc_html( $BookingPress->bookingpress_get_settings( 'company_address', 'company_setting' ) );
				$company_phone   = esc_html( $BookingPress->bookingpress_get_settings( 'company_phone_number', 'company_setting' ) );
				$company_website = $BookingPress->bookingpress_get_settings( 'company_website', 'company_setting' );

				$template_content = str_replace( '%company_address%', $company_address, $template_content );
				$template_content = str_replace( '%company_name%', $company_name, $template_content );
				$template_content = str_replace( '%company_phone%', $company_phone, $template_content );
				$template_content = str_replace( '%company_website%', $company_website, $template_content );

				$bookingpress_service_name = !empty($bookingpress_appointment_data['bookingpress_service_name']) ? $bookingpress_appointment_data['bookingpress_service_name'] : '';

				$bookingpress_currency = !empty($bookingpress_appointment_data['bookingpress_service_currency']) ? $bookingpress_appointment_data['bookingpress_service_currency'] : '';
				$bookingpress_currency_symbol = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_currency);
				$bookingpress_service_price = !empty($bookingpress_appointment_data['bookingpress_service_price']) ? $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_appointment_data['bookingpress_service_price'], $bookingpress_currency_symbol) : 0;

				$bookingpress_service_duration_val = $bookingpress_appointment_data['bookingpress_service_duration_val'];
				$bookingpress_service_duration_unit = $bookingpress_appointment_data['bookingpress_service_duration_unit'];

				$template_content = str_replace( '%service_name%' , $bookingpress_service_name, $template_content );
				$template_content = str_replace( '%service_amount%' , $bookingpress_service_price, $template_content );
				$template_content = str_replace( '%service_duration%' , $bookingpress_service_duration_val." ".$bookingpress_service_duration_unit, $template_content );

				$template_content = apply_filters('bookingpress_modify_email_content_filter',$template_content,$bookingpress_appointment_data);

			}

			return $template_content;
		}

		function bookingpress_send_after_payment_log_entry_email_notification( $email_notification_type, $inserted_booking_id, $bookingpress_customer_email ) {
			global $wpdb, $BookingPress, $bookingpress_email_notifications;
			if ( ! empty( $email_notification_type ) ) {
				// Send customer email notification
				$is_email_sent = $bookingpress_email_notifications->bookingpress_send_email_notification( 'customer', $email_notification_type, $inserted_booking_id, $bookingpress_customer_email );

				// Send admin email notification
				$bookingpress_admin_emails = esc_html( $BookingPress->bookingpress_get_settings( 'admin_email', 'notification_setting' ) );
				
				if ( ! empty( $bookingpress_admin_emails ) ) {
					$bookingpress_admin_emails = explode( ',', $bookingpress_admin_emails );
					foreach ( $bookingpress_admin_emails as $admin_email_key => $admin_email_val ) {
						$bookingpress_email_notifications->bookingpress_send_email_notification( 'employee', $email_notification_type, $inserted_booking_id, $admin_email_val );
					}
				}
			}
		}

	}

	global $bookingpress_email_notifications;
	$bookingpress_email_notifications = new bookingpress_email_notifications();
}
