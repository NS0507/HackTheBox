<?php
if ( ! class_exists( 'bookingpress_service_categories' ) ) {
	class bookingpress_service_categories {
		function __construct() {
			add_action( 'wp_ajax_bookingpress_get_categories', array( $this, 'bookingpress_get_categories' ) );
			add_action( 'wp_ajax_bookingpress_add_categories', array( $this, 'bookingpress_add_categories' ) );
			add_action( 'wp_ajax_bookingpress_edit_category', array( $this, 'bookingpress_edit_category' ) );
			add_action( 'wp_ajax_bookingpress_delete_category', array( $this, 'bookingpress_delete_category' ) );
			add_action( 'wp_ajax_bookingpress_bulk_category', array( $this, 'bookingpress_bulk_category' ) );
			add_action( 'wp_ajax_bookingpress_position_categories', array( $this, 'bookingpress_position_categories' ) );

			add_action( 'bookingpress_categories_dynamic_onload_method', array( $this, 'bookingpress_dynamic_on_load_method' ) );
			add_action( 'bookingpress_categories_dynamic_vue_methods', array( $this, 'bookingpress_dynamic_method_function' ) );
			add_action( 'bookingpress_categories_dynamic_data_variables', array( $this, 'bookingpress_dynamic_data_variable_function' ) );
			add_action( 'bookingpress_categories_dynamic_helper_variables', array( $this, 'bookingpress_dynamic_helper_variables_function' ) );
			add_action( 'bookingpress_categories_dynamic_directives', array( $this, 'bookingpress_dynamic_directive_function' ) );
		}

		function bookingpress_dynamic_directive_function() {
			 echo esc_html( 'sortable' );
		}

		function bookingpress_dynamic_helper_variables_function() {
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
			do_action( 'bookingpress_categories_add_dynamic_helper_vars' );
		}

		function bookingpress_dynamic_data_variable_function() {
			global $bookingpress_category_vue_data;
			echo json_encode( $bookingpress_category_vue_data );
		}

		function bookingpress_dynamic_on_load_method() {
			?>
			this.loadServiceCategory();
			<?php
			do_action( 'bookingpress_categories_add_dynamic_on_load_method' );
		}

		function bookingpress_dynamic_method_function() {
			global $bookingpress_notification_duration;
			?>
			toggleBusy() {
				this.loading = !this.loading
			},
			handleSelectionChange(val) {
				this.multipleSelection = val;
			},
			handleSizeChange(val) {
				this.perPage = val
				this.loadServiceCategory()
			},
			handleCurrentChange(val) {
				this.currentPage = val;
				this.loadServiceCategory()
			},
			async loadServiceCategory() {
				this.toggleBusy();
				var postData = { action:'bookingpress_get_categories', perpage:this.perPage, currentpage:this.currentPage,_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' };
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {
					this.toggleBusy();
					this.items = response.data.items;
					this.totalItems = response.data.total;
				}.bind(this) )
				.catch( function (error) {
					console.log(error);
				});
			},
			open_add_service_modal() {
				this.resetForm()
				this.open_category_modal = true
				this.bookingpress_loader_hide()
			},
			bookingpress_loader_hide() {
				this.modal_loader = 0
			},
			saveCategoryDetails: function(service_category) {
				this.$refs[service_category].validate((valid) => {
					if (valid) {
						const vm = new Vue()
						const vm2 = this
						vm2.savebtnloading = true
						var postdata = this.service_category;
						postdata.action = 'bookingpress_add_categories';
						postdata._wpnonce = '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>';
						axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
						.then(function(response){
							vm2.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: response.data.variant,
								customClass: response.data.variant+'_notification',
								duration:<?php echo intval($bookingpress_notification_duration); ?>,
							});
							vm2.savebtnloading = false
							if (response.data.variant == 'success') {
								vm2.loadServiceCategory()
								vm2.open_category_modal = false
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
					} else {
						return false;
					}
				});
			},
			editServiceCategoryData(index, row) {
				const vm = new Vue()
				const vm2 = this
				vm2.open_category_modal = true
				var edit_id = row.category_id;
				var service_category_edit_data = { action:'bookingpress_edit_category', edit_id: edit_id,'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' }
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( service_category_edit_data ) )
				.then(function(response){
					vm2.service_category.service_category_update_id = response.data.category_id
					vm2.service_category.service_category_name = response.data.category_name
					vm2.bookingpress_loader_hide()
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
			deleteServiceCategory(index, row) {
				const vm = new Vue()
				const vm2 = this
				var delete_id = row.category_id;
				var service_category_delete_data = { action:'bookingpress_delete_category', delete_id: delete_id,_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' }
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( service_category_delete_data ) )
				.then(function(response){
					vm2.$notify({
						title: response.data.title,
						message: response.data.msg,
						type: response.data.variant,
						customClass: response.data.variant+'_notification',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
					vm2.loadServiceCategory()
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
			delete_bulk_categories() {
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
						var service_category_delete_data = {
							action:'bookingpress_bulk_category',
							delete_ids: this.multipleSelection,
							bulk_action: 'delete',
							_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>'
						}
						axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( service_category_delete_data ) )
						.then(function(response){
							vm2.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: response.data.variant,
								customClass: response.data.variant+'_notification',
								duration:<?php echo intval($bookingpress_notification_duration); ?>,
							});
							vm2.loadServiceCategory();
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
						vm2.$notify({
							title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
							message: '<?php esc_html_e( 'Please select one or more records.', 'bookingpress-appointment-booking' ); ?>',
							type: 'error',
							customClass: 'error_notification',
							duration:<?php echo intval($bookingpress_notification_duration); ?>,
						});
					}
				}
			},
			resetForm() {
				this.service_category.service_category_name = ''
				this.service_category.service_category_update_id = 0
			},
			closeServiceCategoryModal() {
				const vm2 = this
				vm2.$refs['service_category'].resetFields()
				vm2.open_category_modal = false
			},
			<?php
			do_action( 'bookingpress_categories_add_dynamic_vue_methods' );
		}

		function bookingpress_get_categories() {
			global $wpdb, $tbl_bookingpress_services, $tbl_bookingpress_categories;
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
			$perpage            = isset( $_POST['perpage'] ) ? intval( $_POST['perpage'] ) : 10;
			$currentpage        = isset( $_POST['currentpage'] ) ? intval( $_POST['currentpage'] ) : 1;
			$offset             = ( ! empty( $currentpage ) && $currentpage > 1 ) ? ( ( $currentpage - 1 ) * $perpage ) : 0;
			$total_categories   = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_categories . ' order by bookingpress_category_position ASC', ARRAY_A );
			$categories         = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_categories . ' order by bookingpress_category_position ASC', ARRAY_A );
			$service_categories = array();
			$service_categories = apply_filters( 'bookingpress_add_new_category_option', $service_categories );
			if ( ! empty( $categories ) ) {
				$counter = 1;
				foreach ( $categories as $category ) {
					$service_category                  = array();
					$service_category['id']            = $counter;
					$service_category['category_id']   = $category['bookingpress_category_id'];
					$service_category['category_name'] = esc_html( $category['bookingpress_category_name'] );
					$total_services                    = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_services . ' WHERE bookingpress_category_id = ' . $category['bookingpress_category_id'], ARRAY_A );

					$service_category['total_services'] = count( $total_services );
					$service_categories[]               = $service_category;
					$counter++;
				}
			}
			$data['items'] = $service_categories;
			$data['total'] = count( $total_categories );
			wp_send_json( $data );
		}
		function bookingpress_add_categories() {
			global $wpdb, $tbl_bookingpress_categories;
			$response              = array();
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
			$category_id         = isset( $_POST['service_category_update_id'] ) ? intval( $_POST['service_category_update_id'] ) : '';
			$category_name       = isset( $_POST['service_category_name'] ) ? trim( sanitize_text_field( $_POST['service_category_name'] ) ) : '';
			$response['variant'] = 'error';
			$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']     = esc_html__( 'Something Went wrong...', 'bookingpress-appointment-booking' );
			if ( ! empty( $category_name ) ) {
				if ( strlen( $category_name ) > 255 ) {
					$response['msg'] = esc_html__( 'Category name is too long...', 'bookingpress-appointment-booking' );
				} else {
					if ( ! empty( $category_id ) ) {
						$args = array(
							'bookingpress_category_name' => sanitize_text_field( $category_name ),
						);
						$wpdb->update( $tbl_bookingpress_categories, $args, array( 'bookingpress_category_id' => $category_id ) );
						$response['variant'] = 'success';
						$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
						$response['msg']     = esc_html__( 'Category has been updated successfully.', 'bookingpress-appointment-booking' );
					} else {
						$category_position = 0;
						$category          = $wpdb->get_row( 'SELECT * FROM ' . $tbl_bookingpress_categories . ' ORDER BY bookingpress_category_position DESC LIMIT 1', ARRAY_A );
						if ( ! empty( $category ) ) {
							$category_position = $category['bookingpress_category_position'] + 1;
						}
						$date = current_time( 'mysql' );
						$args = array(
							'bookingpress_category_name' => sanitize_text_field( $category_name ),
							'bookingpress_category_position' => $category_position,
							'bookingpress_categorydate_created' => $date,
						);
						$wpdb->insert( $tbl_bookingpress_categories, $args, array( '%s', '%d', '%s' ) );
						$category_id         = $wpdb->insert_id;
						$response['category_id'] = strval($category_id);
						$response['variant'] = 'success';
						$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
						$response['msg']     = esc_html__( 'Category has been created successfully.', 'bookingpress-appointment-booking' );
					}
				}
			} elseif ( empty( $category_name ) ) {
				$response['msg'] = esc_html__( 'Please add valid data for category name', 'bookingpress-appointment-booking' ) . '.';
			}
			wp_send_json( $response );
		}
		function bookingpress_edit_category() {
			global $wpdb, $tbl_bookingpress_categories;
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
			$category_id         = isset( $_POST['edit_id'] ) ? intval( $_POST['edit_id'] ) : '';
			$response['variant'] = 'error';
			$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']     = esc_html__( 'Something Went wrong...', 'bookingpress-appointment-booking' );
			if ( ! empty( $category_id ) ) {
				$category = $wpdb->get_row( 'SELECT * FROM ' . $tbl_bookingpress_categories . ' WHERE bookingpress_category_id = ' . $category_id, ARRAY_A );
				if ( ! empty( $category ) ) {
					$response['category_id']   = intval( $category['bookingpress_category_id'] );
					$response['category_name'] = esc_html( $category['bookingpress_category_name'] );
					$response['variant']       = 'success';
					$response['title']         = esc_html__( 'Success', 'bookingpress-appointment-booking' );
					$response['msg']           = esc_html__( 'Category Data.', 'bookingpress-appointment-booking' );
				}
			}
			wp_send_json( $response );
		}
		function bookingpress_delete_category( $category_id = '' ) {
			global $wpdb, $tbl_bookingpress_categories,$tbl_bookingpress_services,$tbl_bookingpress_appointment_bookings;
			$response              = array();
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( isset( $_POST['action'] ) && sanitize_text_field( $_POST['action'] ) == 'bookingpress_delete_category' ) {
				if ( ! $bpa_verify_nonce_flag ) {
					$response['variant'] = 'error';
					$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
					$response['msg']     = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
					wp_send_json( $response );
					die();
				}
			}
			$category_id               = isset( $_POST['delete_id'] ) ? intval( $_POST['delete_id'] ) : $category_id;
			$response['variant']       = 'error';
			$response['title']         = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']           = esc_html__( 'Something Went wrong...', 'bookingpress-appointment-booking' );
			$return                    = false;
			$total_categories_services = $wpdb->get_results( 'SELECT `bookingpress_service_id` FROM ' . $tbl_bookingpress_services . ' WHERE bookingpress_category_id =' . $category_id, ARRAY_A );
			if ( ! empty( $total_categories_services ) ) {
				$response['variant'] = 'warning';
				$response['title']   = esc_html__( 'warning', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'I am sorry', 'bookingpress-appointment-booking' ) . '! ' . esc_html__( 'This category can not be deleted because it has one or more services associated with it', 'bookingpress-appointment-booking' ) . '.';
				wp_send_json( $response );
				exit;
			}
			if ( ! empty( $category_id ) ) {
				$total_categories = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_categories . ' order by bookingpress_category_position ASC', ARRAY_A );
				$new_position     = count( $total_categories ) - 1;
				$category         = $wpdb->get_row( 'SELECT * FROM ' . $tbl_bookingpress_categories . ' WHERE bookingpress_category_id = ' . $category_id, ARRAY_A );
				if ( $category['bookingpress_category_position'] != $new_position ) {
					$this->bookingpress_position_categories( $category['bookingpress_category_position'], $new_position );
				}
				$wpdb->delete( $tbl_bookingpress_categories, array( 'bookingpress_category_id' => $category_id ), array( '%d' ) );
				$response['variant'] = 'success';
				$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Category has been deleted successfully', 'bookingpress-appointment-booking' );
				$return              = true;
			}
			if ( isset( $_POST['action'] ) && sanitize_text_field( $_POST['action'] ) == 'bookingpress_delete_category' ) {
				wp_send_json( $response );
			}
			return $return;
		}
		function bookingpress_bulk_category() {
			global $wpdb, $tbl_bookingpress_categories,$BookingPress;
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
			$response['variant'] = 'danger';
			$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']     = esc_html__( 'Something Went wrong...', 'bookingpress-appointment-booking' );
			if ( ! empty( $_POST['bulk_action'] ) && sanitize_text_field( $_POST['bulk_action'] ) == 'delete' ) {
				$delete_ids = ! empty( $_POST['delete_ids'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['delete_ids'] ) : array();
				if ( ! empty( $delete_ids ) ) {
					foreach ( $delete_ids as $delete_key => $delete_val ) {
						if ( is_array( $delete_val ) ) {
							$delete_val = $delete_val['category_id'];
						}
						$this->bookingpress_delete_category( $delete_val );
						if ( $return ) {
							$response['variant'] = 'success';
							$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
							$response['msg']     = esc_html__( 'Categories has been deleted successfully', 'bookingpress-appointment-booking' );
						}
					}
				}
			}
			wp_send_json( $response );
		}
		function bookingpress_position_categories( $old_position = '', $new_position = '' ) {
			global $wpdb, $tbl_bookingpress_categories;
			$response              = array();
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( isset( $_POST['action'] ) && sanitize_text_field( $_POST['action'] ) == 'bookingpress_position_categories' ) {
				if ( ! $bpa_verify_nonce_flag ) {
					$response['variant'] = 'error';
					$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
					$response['msg']     = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
					wp_send_json( $response );
					die();
				}
			}
			$old_position        = isset( $_POST['old_position'] ) ? intval( $_POST['old_position'] ) : $old_position;
			$new_position        = isset( $_POST['new_position'] ) ? intval( $_POST['new_position'] ) : $new_position;
			$response['variant'] = 'danger';
			$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']     = esc_html__( 'Something Went wrong...', 'bookingpress-appointment-booking' );
			if ( $old_position != '' && $new_position != '' ) {
				if ( $new_position > $old_position ) {
					$condition  = 'BETWEEN ' . $old_position . ' AND ' . $new_position;
					$categories = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_categories . ' WHERE bookingpress_category_position ' . $condition . ' order by bookingpress_category_position ASC', ARRAY_A );
					foreach ( $categories as $category ) {
						$position = $category['bookingpress_category_position'] - 1;
						$position = ( $category['bookingpress_category_position'] == $old_position ) ? $new_position : $position;
						$args     = array(
							'bookingpress_category_position' => $position,
						);
						$wpdb->update( $tbl_bookingpress_categories, $args, array( 'bookingpress_category_id' => $category['bookingpress_category_id'] ) );
					}
				} else {
					$categories = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_categories . ' WHERE bookingpress_category_position BETWEEN ' . $new_position . ' AND ' . $old_position . ' order by bookingpress_category_position ASC', ARRAY_A );
					foreach ( $categories as $category ) {
						$position = $category['bookingpress_category_position'] + 1;
						$position = ( $category['bookingpress_category_position'] == $old_position ) ? $new_position : $position;
						$args     = array(
							'bookingpress_category_position' => $position,
						);
						$wpdb->update( $tbl_bookingpress_categories, $args, array( 'bookingpress_category_id' => $category['bookingpress_category_id'] ) );
					}
				}
				$response['variant'] = 'success';
				$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Category position has been changed successfully.', 'bookingpress-appointment-booking' );
			}
			if ( isset( $_POST['action'] ) && sanitize_text_field( $_POST['action'] ) == 'bookingpress_position_categories' ) {
				wp_send_json( $response );
			}
			return;
		}
	}
}
global $bookingpress_service_categories, $bookingpress_category_vue_data;
$bookingpress_service_categories = new bookingpress_service_categories();

global $bookingpress_global_options;
$bookingpress_options             = $bookingpress_global_options->bookingpress_global_options();
$bookingpress_locale_lang         = $bookingpress_options['locale'];
$bookingpress_pagination          = $bookingpress_options['pagination'];
$bookingpress_pagination_arr      = json_decode( $bookingpress_pagination, true );
$bookingpress_pagination_selected = $bookingpress_pagination_arr[0];

$bookingpress_category_vue_data = array(
	'bulk_action'                => 'bulk_action',
	'bulk_options'               => array(
		array(
			'value' => 'bulk_action',
			'label' => __( 'Bulk Action', ' bookingpress-appointment-booking' ),
		),
		array(
			'value' => 'delete',
			'label' => __( 'Delete', ' bookingpress-appointment-booking' ),
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
	'service_category'           => array(
		'service_category_name'      => '',
		'service_category_update_id' => 0,
	),
	'open_category_modal'        => false,
	'modal_loader'               => 1,
	'rules'                      => array(
		'service_category_name' => array(
			array(
				'required' => true,
				'message'  => __( 'Please enter category name', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
	),
	'savebtnloading'             => false,
);
