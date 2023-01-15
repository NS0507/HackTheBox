<?php
if ( ! class_exists( 'bookingpress_fileupload_class' ) ) {
	class bookingpress_fileupload_class {
		var $file;
		var $check_cap;
		var $capabilities;
		var $check_nonce;
		var $nonce_data;
		var $nonce_action;
		var $check_only_image;
		var $check_specific_ext;
		var $allowed_ext;
		var $invalid_ext;
		var $compression_ext;
		var $error_message;
		var $default_error_msg;
		var $check_file_size;
		var $file_size;
		var $max_file_size;
		var $field_error_msg;
		var $field_size_error_msg;


		function __construct( $file ) {
			global $BookingPress;
			$file = ! empty( $file ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $file ) : array();
			if ( empty( $file ) && ! $import ) {
				$this->error_message = esc_html__( 'Please select a file to process', 'bookingpress-appointment-booking' );
				return false;
			}

			$this->file      = $file;
			$this->file_size = $file['size'];

			$this->invalid_ext = apply_filters( 'bookingpress_restricted_file_ext', array( 'php', 'php3', 'php4', 'php5', 'py', 'pl', 'jsp', 'asp', 'cgi', 'ext' ) );

			$this->compression_ext = apply_filters( 'bookingpress_exclude_file_check_ext', array( 'tar', 'zip', 'gz', 'gzip', 'rar', '7z' ) );

			$mimes = get_allowed_mime_types();

			$type_img = array();

			foreach ( $mimes as $ext => $type ) {
				if ( preg_match( '/(image\/)/', $type ) ) {
					if ( preg_match( '/(\|)/', $ext ) ) {
						$type_imgs = explode( '|', $ext );
						$type_img  = array_merge( $type_img, $type_imgs );
					} else {
						$type_img[] = $ext;
					}
				}
			}

			$this->image_exts = $type_img;

			$this->capabilities = array( 'bookingpress' );
		}


		function bookingpress_process_upload( $destination ) {
			if ( $this->check_cap ) {
				$capabilities = $this->capabilities;

				if ( ! empty( $capabilities ) ) {
					if ( is_array( $capabilities ) ) {
						$isFailed = false;
						foreach ( $capabilities as $caps ) {
							if ( ! current_user_can( $caps ) ) {
								$isFailed            = true;
								$this->error_message = esc_html__( "Sorry, you don't have permission to perform this action.", 'bookingpress-appointment-booking' );
								break;
							}
						}

						if ( $isFailed ) {
							return false;
						}
					} else {
						if ( ! current_user_can( $capabilities ) ) {
							$this->error_message = esc_html__( "Sorry, you don't have permission to perform this action.", 'bookingpress-appointment-booking' );
						}
					}
				} else {
					$this->error_message = esc_html__( "Sorry, you don't have permission to perform this action.", 'bookingpress-appointment-booking' );
					return false;
				}
			}

			if ( $this->check_nonce ) {
				if ( empty( $this->nonce_data ) || empty( $this->nonce_action ) ) {
					$this->error_message = esc_html__( 'Sorry, Your request could not be processed due to security reasons.', 'bookingpress-appointment-booking' );
					return false;
				}

				if ( ! wp_verify_nonce( $this->nonce_data, $this->nonce_action ) ) {
					$this->error_message = esc_html__( 'Sorry, Your request could not be processed due to security reasons.', 'bookingpress-appointment-booking' );
					return false;
				}
			}

			$ext_data = explode( '.', $this->file['name'] );
			$ext      = end( $ext_data );
			$ext      = strtolower( $ext );

			if ( in_array( $ext, $this->invalid_ext ) ) {
				$this->error_message = esc_html__( 'The file could not be uploaded due to security reasons.', 'bookingpress-appointment-booking' );
				return false;
			}

			if ( $this->check_only_image ) {
				if ( ! preg_match( '/(image\/)/', $this->file['type'] ) ) {
					$this->error_message = esc_html__( 'Please select image file only.', 'bookingpress-appointment-booking' );
					if ( ! empty( $this->default_error_msg ) ) {
						$this->error_message = $this->default_error_msg;
					}
					return false;
				}
			}

			if ( $this->check_specific_ext ) {
				if ( empty( $this->allowed_ext ) ) {
					$this->error_message = esc_html__( 'Please set extensions to validate file.', 'bookingpress-appointment-booking' );
					return false;
				}
				if ( ! in_array( $ext, $this->allowed_ext ) ) {
					$this->error_message = esc_html__( 'Invalid file extension. Please select valid file', 'bookingpress-appointment-booking' );
					if ( ! empty( $this->default_error_msg ) ) {
						$this->error_message = $this->default_error_msg;
					}

					if ( ! empty( $this->field_error_msg ) ) {
						$this->error_message = $this->field_error_msg;
					}

					return false;
				}
			}

			if ( $this->check_file_size ) {
				$size_in_bytes = $this->bookingpress_convert_to_bytes();
				if ( $size_in_bytes < $this->file_size ) {
					$this->error_message = esc_html__( 'Invalid File Size.', 'bookingpress-appointment-booking' );

					if ( ! empty( $this->field_size_error_msg ) ) {
						$this->error_message = $this->field_size_error_msg;
					}
					return false;
				}
			}

			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}

			WP_Filesystem();
			global $wp_filesystem;

			$file_content  = $wp_filesystem->get_contents( $this->file['tmp_name'] );
			$is_valid_file = $this->bookingpress_read_file( $file_content, $ext );

			if ( ! $is_valid_file ) {
				return false;
			}

			if ( '' == $file_content || ! $wp_filesystem->put_contents( $destination, $file_content, 0777 ) ) {
				$this->error_message = esc_html__( 'There is an issue while uploading a file. Please try again', 'bookingpress-appointment-booking' );
				return false;
			}

			return 1;
		}

		function bookingpress_read_file( $file_content, $ext ) {
			if ( '' == $file_content ) {
				return true;
			}

			if ( in_array( $ext, $this->compression_ext ) ) {
				return true;
			}

			$file_bytes = $this->file_size;

			$file_size = number_format( $file_bytes / 1048576, 2 );

			if ( $file_size > 10 ) {
				return true;
			}

			$bookingpress_valid_pattern = '/(\<\?(php))/';

			if ( preg_match( $bookingpress_valid_pattern, $file_content ) ) {
				$this->error_message = esc_html__( 'The file could not be uploaded due to security reason as it contains malicious code', 'bookingpress-appointment-booking' );
				return false;
			}

			return true;
		}

		function bookingpress_convert_to_bytes() {
			$units_arr = array(
				'B'  => 0,
				'K'  => 1,
				'KB' => 1,
				'M'  => 2,
				'MB' => 2,
				'G'  => 3,
				'GB' => 3,
				'T'  => 4,
				'TB' => 4,
				'P'  => 5,
				'PB' => 5,
			);

			$numbers = preg_replace( '/[^\d.]/', '', $this->max_file_size );
			$suffix  = preg_replace( '/[\d.]+/', '', $this->max_file_size );
			if ( is_numeric( substr( $suffix, 0, 1 ) ) ) {
				return preg_replace( '/[^\d.]/', '', $this->max_file_size );
			}
			$exponent = ! empty( $units_arr[ $suffix ] ) ? $units_arr[ $suffix ] : null;
			if ( null == $exponent ) {
				return null;
			}
			return $numbers * ( 1024 ** $exponent );
		}
	}
}
