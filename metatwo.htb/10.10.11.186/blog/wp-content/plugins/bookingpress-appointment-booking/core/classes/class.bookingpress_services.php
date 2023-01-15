<?php
if ( ! class_exists( 'bookingpress_services' ) ) {
	class bookingpress_services {
		function __construct() {
			add_action( 'wp_ajax_bookingpress_get_services', array( $this, 'bookingpress_get_services' ) );
			add_action( 'wp_ajax_bookingpress_add_service', array( $this, 'bookingpress_add_service' ) );
			add_action( 'wp_ajax_bookingpress_edit_service', array( $this, 'bookingpress_edit_service' ) );
			add_action( 'wp_ajax_bookingpress_delete_service', array( $this, 'bookingpress_delete_service' ) );
			add_action( 'wp_ajax_bookingpress_bulk_service', array( $this, 'bookingpress_bulk_service' ) );
			add_action( 'wp_ajax_bookingpress_position_services', array( $this, 'bookingpress_position_services' ) );
			add_action( 'wp_ajax_bookingpress_duplicate_service', array( $this, 'bookingpress_duplicate_service' ) );
			add_action( 'wp_ajax_bookingpress_get_search_categories', array( $this, 'bookingpress_search_categories' ) );

			add_action( 'bookingpress_services_dynamic_vue_methods', array( $this, 'bookingpress_service_dynamic_vue_methods_func' ), 10 );
			add_action( 'bookingpress_services_dynamic_on_load_methods', array( $this, 'bookingpress_service_dynamic_on_load_methods_func' ), 10 );
			add_action( 'bookingpress_services_dynamic_data_fields', array( $this, 'bookingpress_service_dynamic_data_fields_func' ), 10 );
			add_action( 'bookingpress_services_dynamic_directives', array( $this, 'bookingpress_service_dynamic_directives_func' ), 10 );
			add_action( 'bookingpress_services_dynamic_helper_vars', array( $this, 'bookingpress_service_dynamic_helper_func' ), 10 );

			add_action( 'bookingpress_services_dynamic_view_load', array( $this, 'bookingpress_service_dynamic_view_load_func' ), 10 );

			add_action( 'wp_ajax_bookingpress_upload_service', array( $this, 'bookingpress_upload_service_func' ), 10 );
		}

		function bookingpress_upload_service_func() {
			global $BookingPress;

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

		function bookingpress_service_dynamic_view_load_func() {
			$bookingpress_load_file_name = BOOKINGPRESS_VIEWS_DIR . '/services/manage_services.php';
			$bookingpress_load_file_name = apply_filters( 'bookingpress_modify_service_view_file_path', $bookingpress_load_file_name );

			require $bookingpress_load_file_name;
		}

		function bookingpress_service_dynamic_helper_func() {
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

		function bookingpress_service_dynamic_directives_func() {
			echo esc_html( 'sortable' );
		}

		function bookingpress_service_dynamic_data_fields_func() {
			global $BookingPress, $bookingpress_services_vue_data_fields,$wpdb,$tbl_bookingpress_categories;

			// Get Per Page Records
			$per_page_records                                 = $BookingPress->bookingpress_get_settings( 'per_page_item', 'general_setting' );
			$bookingpress_services_vue_data_fields['perPage'] = ! empty( $per_page_records ) ? $per_page_records : 10;

			$categories                             = $wpdb->get_results( 'SELECT bookingpress_category_id,bookingpress_category_name FROM ' . $tbl_bookingpress_categories . ' order by bookingpress_category_position ASC', ARRAY_A );
			$bookingpress_service_categories_item   = array();
			$bookingpress_service_categories_item[] = array(
				'value' => '',
				'label' => __( 'Select Category', 'bookingpress-appointment-booking' ),
			);
			foreach ( $categories as $key => $value ) {
				$bookingpress_service_categories_item[] = array(
					'value' => $value['bookingpress_category_id'],
					'label' => $value['bookingpress_category_name'],
				);
			}
			$bookingpress_services_vue_data_fields['serviceCatOptions'] = $bookingpress_service_categories_item;
			$bookingpress_default_time_duration_data                    = $BookingPress->bookingpress_get_default_timeslot_data();

			$bookingpress_services_vue_data_fields['service']['service_duration_val']  = ! empty( $bookingpress_default_time_duration_data['time_duration'] ) ? $bookingpress_default_time_duration_data['time_duration'] : 30;
			$bookingpress_services_vue_data_fields['service']['service_duration_unit'] = ! empty( $bookingpress_default_time_duration_data['time_unit'] ) ? $bookingpress_default_time_duration_data['time_unit'] : 'm';

			$bookingpress_services_vue_data_fields = apply_filters( 'bookingpress_modify_service_data_fields', $bookingpress_services_vue_data_fields );

			echo json_encode( $bookingpress_services_vue_data_fields );
		}

		function bookingpress_service_dynamic_on_load_methods_func() {
			?>
			this.loadServices().catch(error => {
				console.error(error)
			})
			this.loadSearchCategories()
			this.loadServiceCategory()
			<?php
			do_action( 'bookingpress_add_service_dynamic_on_load_methods' );
		}

		function bookingpress_service_dynamic_vue_methods_func() {
			global $BookingPress,$bookingpress_notification_duration;
			$bookingpress_default_time_duration_data = $BookingPress->bookingpress_get_default_timeslot_data();
			$bookingpress_default_time_duration      = ! empty( $bookingpress_default_time_duration_data['time_duration'] ) ? $bookingpress_default_time_duration_data['time_duration'] : 30;
			$bookingpress_default_time_unit          = ! empty( $bookingpress_default_time_duration_data['time_unit'] ) ? $bookingpress_default_time_duration_data['time_unit'] : 'm';
			?>
			searchCategoryData(category_id){
				const vm = this
				vm.search_service_category = category_id
				vm.loadServices()
				vm.open_manage_category_modal = false
			},
			clearBulkAction(){
				const vm = this
				vm.bulk_action = 'bulk_action';
				vm.multipleSelection = []
				vm.items.forEach(function(selectedVal, index, arr) {			
					selectedVal.selected = false;
				})
				vm.is_multiple_checked = false;
			},
			selectAllServices(isChecked){
				const vm = this				
				var selected_service_parent = '';
				if(isChecked)
				{	
					vm.items.forEach(function(selectedVal, index, arr) {																
						if( selectedVal.service_bulk_action == false) {									
							vm.multipleSelection.push(selectedVal.service_id);																	
							selectedVal.selected = true;														
						}
					})							
				}
				else
				{
					vm.clearBulkAction()
				}
			},
			handleSelectionChange(e, isChecked, service_id) {				
				const vm = this								
				vm.bulk_action = 'bulk_action';
				if(isChecked){
					vm.multipleSelection.push(service_id);
				}else{
					var removeIndex = vm.multipleSelection.indexOf(service_id);
					if(removeIndex > -1){
						vm.multipleSelection.splice(removeIndex, 1);
					}
				}
			},
			handleSizeChange(val) {
				this.perPage = val
				this.loadServices()
			},
			handleCurrentChange(val) {
				this.currentPage = val;
				this.loadServices()
			},
			async loadServiceCategory() {
				var postData = { action:'bookingpress_get_categories', perpage:this.perPage, currentpage:this.currentPage,_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' };
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {
					this.category_items = response.data.items;
				}.bind(this) )
				.catch( function (error) {
					console.log(error);
				});
			},
			open_add_category_modal_func(currentElement){
				this.resetCategoryForm()
				this.open_add_category_modal = true
			},
			resetCategoryForm() {
				this.service_category.service_category_name = ''
				this.service_category.service_category_update_id = 0
				this.category_modal_pos = '80px'
			},
			resetFilter(){
				const vm = this
				vm.search_service_name = ''
				vm.search_service_category = []
				vm.loadServices()
				vm.is_multiple_checked = false;
				vm.multipleSelection = [];
			},
			saveCategoryDetails: function(service_category) {
				this.$refs[service_category].validate((valid) => {
					if (valid) {
						const vm = new Vue()
						const vm2 = this
						vm2.is_disabled = true
						vm2.is_display_save_loader = '1'
						vm2.savebtnloading = true
						var postdata = this.service_category;
						postdata.action = 'bookingpress_add_categories';						
						postdata._wpnonce = '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>';
						axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
						.then(function(response){
							vm2.is_disabled = false
							vm2.is_display_save_loader = '0'
							vm2.open_category_modal = false
							vm2.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: response.data.variant,
								customClass: response.data.variant+'_notification',
								duration:<?php echo intval($bookingpress_notification_duration); ?>,
							});
							vm2.savebtnloading = false
							vm2.loadSearchCategories()
							if (response.data.variant == 'success') {
								vm2.loadServiceCategory()
								vm2.open_add_category_modal = false
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
			editServiceCategoryData(edit_id, currentElement) {
				const vm = new Vue()
				const vm2 = this
				vm2.resetCategoryForm()
				var dialog_pos = currentElement.target.getBoundingClientRect();
				vm2.category_modal_pos = (dialog_pos.top - 96)+'px'
				vm2.service_category.service_category_update_id = edit_id;
				vm2.open_add_category_modal = true
				var service_category_edit_data = { action:'bookingpress_edit_category', edit_id: edit_id,_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' }
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( service_category_edit_data ) )
				.then(function(response){
					vm2.service_category.service_category_name = response.data.category_name
					vm2.bookingpress_loader_hide()
					vm2.$refs.serviceCatName.$el.children[0].focus()
					vm2.$refs.serviceCatName.$el.children[0].blur()
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
			onUpdateCategoryPos: function(event) {
				const vm = new Vue()
				const vm2 = this
				var postData = { action: 'bookingpress_position_categories', old_position: event.oldIndex, new_position: event.newIndex, currentPage : this.currentPage, perPage: this.perPage,_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>'};
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then(function(response){
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
			deleteServiceCategory(delete_id) {
				const vm = new Vue()
				const vm2 = this
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
			saveServiceData(){
				const vm = new Vue()
				const vm2 = this
				
				vm2.$refs["service"].validate((valid) => {
					if (valid) {
						vm2.is_disabled = true
						vm2.is_display_save_loader = '1'
						vm2.savebtnloading = true
						var postdata = vm2.service;
						postdata.action = 'bookingpress_add_service';
						postdata._wpnonce = '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>';
						axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
						.then(function(response){
							vm2.is_disabled = false
							vm2.is_display_save_loader = '0'
							if(response.data.variant != 'error'){
								vm2.open_service_modal = false;
							}
							vm2.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: response.data.variant,
								customClass: response.data.variant+'_notification',
								duration:<?php echo intval($bookingpress_notification_duration); ?>,
							});
							vm2.savebtnloading = false
							if (response.data.variant == 'success') {
								vm2.service.service_update_id = response.data.service_id
								vm2.loadServices()
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
						vm2.activeTabName = 'details';
						return false;
					}
				});
			},
			async loadServices() {
				const vm = this
				vm.is_display_loader = '1'
				var bookingpress_search_data = { 'selected_category_id': this.search_service_category, 'service_name': this.search_service_name }
				var postData = { action:'bookingpress_get_services', perpage:this.perPage, currentpage:this.currentPage, search_data: bookingpress_search_data,_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' };
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {
					vm.is_display_loader = '0'
					this.items = response.data.items;
					this.totalItems = response.data.total;
				}.bind(this) )
				.catch( function (error) {
					vm2.is_display_loader = '0'
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
			async loadSearchCategories() {
				var postData = { action:'bookingpress_get_search_categories' };
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {
					this.search_categories = response.data;
				}.bind(this) )
				.catch( function (error) {
					console.log(error);
				});
			},
			open_add_service_modal() {
				this.resetForm()
				this.open_service_modal = true;
				this.get_categories()
				this.bookingpress_loader_hide()
			},
			get_categories() {
				const vm = new Vue()
				const vm2 = this
				var service = { action: 'bookingpress_get_categories',_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' }
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( service ) )
				.then(function(response){
					var serviceCatData = [];
					categoriesitems = response.data.items;
					categoriesitems.map(function(value, key) {
						serviceCatData.push({value: value.category_id, label: value.category_name});
					})
					vm2.serviceCatOptions = serviceCatData
				}).catch(function(error){
					console.log(error);
				});
			},
			bookingpress_loader_hide() {
				this.modal_loader = 0
			},
			openEditService(edit_id){
				const vm = new Vue()
				const vm2 = this
				vm2.service.service_update_id = edit_id
				vm2.open_service_modal = true;
				vm2.get_categories()
				var service_edit_data = { action: 'bookingpress_edit_service', edit_id: edit_id,_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' }
				axios.post(appoint_ajax_obj.ajax_url, Qs.stringify( service_edit_data ) )
				.then(function(response){						
						vm2.service.service_name = response.data.service_name
						vm2.service.service_category = response.data.category_id
						vm2.service.service_duration_val = response.data.service_duration
						vm2.service.service_duration_unit = response.data.service_duration_unit
						vm2.service.service_price = response.data.service_price
						vm2.service.service_description = response.data.service_description
						if(response.data.service_image_details != undefined && response.data.service_image_details != ''){
							vm2.service.service_image = response.data.service_image_details[0].url
						}
						if (response.data.extra_data != '') {
							vm2.service_extra_inputs = response.data.extra_data;
						}
						<?php do_action( 'bookingpress_edit_service_more_vue_data' ); ?>

				}.bind(this) )
				.catch(function(error){
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
			editServiceData(edit_id) {
				const vm2 = this
				vm2.openEditService(edit_id)
			},
			deleteService(delete_id) {
				const vm = new Vue()
				const vm2 = this
				var service_delete_data = { action: 'bookingpress_delete_service', delete_id: delete_id,_wpnonce:'<?Php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' }
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( service_delete_data ) )
				.then(function(response){
					vm2.$notify({
						title: response.data.title,
						message: response.data.msg,
						type: response.data.variant,
						customClass: response.data.variant+'_notification',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
					vm2.loadServices()
					vm2.clearBulkAction()					
					vm2.is_multiple_checked = false;
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
			delete_bulk_services() {
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
							action:'bookingpress_bulk_service',
							cat_delete_ids: this.multipleSelection,
							bulk_action: 'delete',
							_wpnonce:'<?Php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>',
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
							vm2.loadServices();							
							vm2.is_multiple_checked = false;
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
					{	if(this.multipleSelection.length == 0){						
							vm2.$notify({
								title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
								message: '<?php esc_html_e( 'Please select one or more records.', 'bookingpress-appointment-booking' ); ?>',
								type: 'error',
								customClass: 'error_notification',
								duration:<?php echo intval($bookingpress_notification_duration); ?>,
							});						
						}else{
							<?php do_action( 'bookingpress_service_dynamic_bulk_action' ); ?>
						}
					}
				}
			},
			bookingpress_duplicate_service(service_id){
				const vm = new Vue()
				const vm2 = this
				var bookingpress_dup_service_data = [];
				bookingpress_dup_service_data.action = "bookingpress_duplicate_service"
				bookingpress_dup_service_data.service_id = service_id,
				bookingpress_dup_service_data._wpnonce = '<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>'

				axios.post(appoint_ajax_obj.ajax_url, Qs.stringify(bookingpress_dup_service_data))
				.then(function(response){
					vm2.$notify({
						title: response.data.title,
						message: response.data.msg,
						type: response.data.variant,
						customClass: response.data.variant+'_notification',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
					vm2.loadServices();
					vm2.multipleSelection = [];
					vm2.totalItems = vm2.items.length
					if(response.data.duplicate_serv_id != '' || response.data.duplicate_serv_id != undefined)
					{
						vm2.openEditService(response.data.duplicate_serv_id)
					}
				}).catch(function(error){
					console.log(error)
					vm2.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something Went wrong...', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
				});
			},
			resetForm() {
				this.service.service_update_id = 0
				this.service.service_name = ''
				this.service.service_price = ''
				this.service.service_category = null
				this.service.service_duration_val = '<?php echo esc_html( $bookingpress_default_time_duration ); ?>'
				this.service.service_duration_unit = '<?php echo esc_html( $bookingpress_default_time_unit ); ?>'
				this.service.service_description = ''
				this.service_extra_inputs = []
				this.service.service_images_list = [],
				this.service.service_image = '',
				this.service.service_image_name = '',
				this.activeTabName = 'details'
			},
			closeServiceModal() {
				const vm2 = this
				vm2.$refs['service'].resetFields()
				vm2.resetForm()
				vm2.open_service_modal = false
			},
			bookingpress_upload_service_func(response, file, fileList){
				const vm2 = this
				if(response != ''){
					vm2.service.service_image = response.upload_url
					vm2.service.service_image_name = response.upload_file_name
				}
			},
			bookingpress_image_upload_limit(files, fileList){
				const vm2 = this
				if(vm2.service.service_image != ''){
					vm2.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Multiple files not allowed', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
				}
			},
			bookingpress_image_upload_err(err, file, fileList){
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
			bookingpress_remove_service_img(){
				const vm2 = this
				var upload_url = vm2.service.service_image
				var upload_filename = vm2.service.service_image_name

				var postData = { action:'bookingpress_remove_uploaded_file', upload_file_url: upload_url,_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' };
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {
					vm2.service.service_image = ''
					vm2.service.service_image_name = ''
					vm2.$refs.avatarRef.clearFiles()
				}.bind(vm2) )
				.catch( function (error) {
					console.log(error);
				});
			},
			checkUploadedFile(file){
				const vm2 = this
				if(file.type != 'image/jpeg' && file.type != 'image/png'){
					vm2.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Please upload jpg/png file only', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});					
					return false
				}
			},
			updateServicePos: function(e){
				var service_id = e.draggedContext.element.service_id
				var old_index = e.draggedContext.index
				var new_index = e.draggedContext.futureIndex
				const vm = new Vue()
				const vm2 = this
				var postData = { action: 'bookingpress_position_services', old_position: old_index, new_position: new_index, currentPage : this.currentPage, perPage: this.perPage,_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>' };
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then(function(response){
					
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
			updateCategoryPos: function(e){
				const vm = new Vue()
				const vm2 = this
				var old_index = e.draggedContext.index
				var new_index = e.draggedContext.futureIndex
				var postData = { action: 'bookingpress_position_categories', old_position: old_index, new_position: new_index, currentPage : this.currentPage, perPage: this.perPage,_wpnonce:'<?php echo wp_create_nonce( 'bpa_wp_nonce' ); ?>'};
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then(function(response){
					
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
			isNumberValidate(evt, control_name) {
				const regex = /^(?!.*(,,|,\.|\.,|\.\.))[\d.,]+$/gm;
				let m;
				if((m = regex.exec(evt)) == null ) {
					this.service[control_name] = '';
				}
			},
			<?php
			do_action( 'bookingpress_add_service_dynamic_vue_methods' );
		}

		function bookingpress_search_categories() {
			 global $wpdb, $tbl_bookingpress_categories;
			$bookingpress_search_category_details   = array();
			$bookingpress_search_category_details[] = array(
				'bookingpress_category_name' => __( 'All', 'bookingpress-appointment-booking' ),
				'bookingpress_category_id'   => 'all',
			);
			$bookingpress_search_categories         = $wpdb->get_results( 'SELECT bookingpress_category_id, bookingpress_category_name FROM ' . $tbl_bookingpress_categories, ARRAY_A );
			foreach ( $bookingpress_search_categories as $bookingpress_category_key => $bookingpress_category_val ) {
				$bookingpress_search_category_details[] = array(
					'bookingpress_category_name' => $bookingpress_category_val['bookingpress_category_name'],
					'bookingpress_category_id'   => $bookingpress_category_val['bookingpress_category_id'],
				);
			}
			echo json_encode( $bookingpress_search_category_details );
			exit();
		}

		function bookingpress_get_services() {
			global $wpdb, $tbl_bookingpress_services, $tbl_bookingpress_categories, $BookingPress, $tbl_bookingpress_servicesmeta,$tbl_bookingpress_appointment_bookings;
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
			$perpage                   = isset( $_POST['perpage'] ) ? intval( $_POST['perpage'] ) : 10;
			$currentpage               = isset( $_POST['currentpage'] ) ? intval( $_POST['currentpage'] ) : 1;
			$offset                    = ( ! empty( $currentpage ) && $currentpage > 1 ) ? ( ( $currentpage - 1 ) * $perpage ) : 0;
			$bookingpress_search_data  = ! empty( $_REQUEST['search_data'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['search_data'] ) : array();
			$bookingpress_search_query = '';
			if ( ! empty( $bookingpress_search_data ) ) {
				if ( ! empty( $bookingpress_search_data['selected_category_id'] ) && $bookingpress_search_data['selected_category_id'] != 'all' ) {
					$bookingpress_search_query .= " WHERE bookingpress_category_id = {$bookingpress_search_data['selected_category_id']}";
				}

				if ( ! empty( $bookingpress_search_data['service_name'] ) ) {
					$bookingpress_search_name   = $bookingpress_search_data['service_name'];
					$bookingpress_search_query .= ! empty( $bookingpress_search_query ) ? ' AND ' : ' WHERE ';
					$bookingpress_search_query .= "bookingpress_service_name LIKE '%{$bookingpress_search_name}%'";
				}
			}

			$get_total_services = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_services . " {$bookingpress_search_query}", ARRAY_A );
			$total_services     = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_services . " {$bookingpress_search_query} order by bookingpress_service_position ASC", ARRAY_A );
			$services           = array();
			if ( ! empty( $total_services ) ) {
				$counter      = 1;
				$current_date = date( 'Y-m-d', current_time( 'timestamp' ) );
				foreach ( $total_services as $get_service ) {
					$bookingpress_service_id     = intval( $get_service['bookingpress_service_id'] );
					$service                     = array();
					$service['id']               = $counter;
					$service['service_id']       = $bookingpress_service_id;
					$service['service_name']     = esc_html( $get_service['bookingpress_service_name'] );
					$category_id                 = $get_service['bookingpress_category_id'];
					$category                    = $wpdb->get_row( 'SELECT * FROM ' . $tbl_bookingpress_categories . ' WHERE bookingpress_category_id = ' . $category_id, ARRAY_A );
					$service['category_id']      = $category_id;
					$service['service_category'] = esc_html( $category['bookingpress_category_name'] );
					$service_duration            = esc_html( $get_service['bookingpress_service_duration_val'] );
					$service_duration_unit       = esc_html( $get_service['bookingpress_service_duration_unit'] );
					if ( $service_duration_unit == 'm' ) {
						$service_duration .= ' ' . esc_html__( 'Mins', 'bookingpress-appointment-booking' );
					} else {
						$service_duration .= ' ' . esc_html__( 'Hours', 'bookingpress-appointment-booking' );
					}
					$service['service_duration'] = $service_duration;
					$service['service_price']    = $BookingPress->bookingpress_price_formatter_with_currency_symbol( $get_service['bookingpress_service_price'] );

					// Get service image
					$service_img_details            = $wpdb->get_row( "SELECT bookingpress_servicemeta_value FROM {$tbl_bookingpress_servicesmeta} WHERE bookingpress_service_id = {$bookingpress_service_id} AND bookingpress_servicemeta_name = 'service_image_details'", ARRAY_A );
					$service_img_details            = ! empty( $service_img_details['bookingpress_servicemeta_value'] ) ? maybe_unserialize( $service_img_details['bookingpress_servicemeta_value'] ) : array();
					$service_img_url                = ! empty( $service_img_details[0]['url'] ) ? $service_img_details[0]['url'] : '';
					$service['service_img_details'] = $service_img_url;

					$bookingperss_appointments_data = '';
					$bookingperss_appointments_data = $wpdb->get_results( 'SELECT bookingpress_appointment_booking_id  FROM ' . $tbl_bookingpress_appointment_bookings . ' WHERE bookingpress_service_id = ' . $bookingpress_service_id . " AND bookingpress_appointment_date >='" . $current_date . "' AND (bookingpress_appointment_status != 'Cancelled' AND bookingpress_appointment_status != 'Rejected')", ARRAY_A );
					$service['service_bulk_action'] = false;
					if ( ! empty( $bookingperss_appointments_data ) ) {
						$service['service_bulk_action'] = true;
					}
					$service['selected'] = false;
					$services[]          = $service;
					$counter++;
				}
			}
			$data['items'] = $services;
			$data['total'] = count( $get_total_services );
			wp_send_json( $data );
		}


		function bookingpress_add_service() {
			global $wpdb, $tbl_bookingpress_categories, $tbl_bookingpress_services;
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
			$service_id   = isset( $_POST['service_update_id'] ) ? intval( $_POST['service_update_id'] ) : '';
			$service_name = ! empty( $_POST['service_name'] ) ? trim( sanitize_text_field( $_POST['service_name'] ) ) : '';
			if ( strlen( $service_name ) > 255 ) {
				$response            = array();
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Service name is too long...', 'bookingpress-appointment-booking' );
				wp_send_json( $response );
				die();
			}			
			$service_duration_val = $bpa_service_duration = isset( $_POST['service_duration_val'] ) ? intval( $_POST['service_duration_val'] ) : 0;
			$service_duration_unit = isset( $_POST['service_duration_unit'] ) ? sanitize_text_field( $_POST['service_duration_unit'] ) : 'm';

			if($service_duration_unit == 'h') {
				$bpa_service_duration = $bpa_service_duration * 60;
			}
			if ( $bpa_service_duration  > 1440 ) {
				$response            = array();
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Service time duration cannot be greater than 24 hours', 'bookingpress-appointment-booking' ).'.';
				wp_send_json( $response );
				die();
			}
			$service_price         = isset( $_POST['service_price'] ) ? floatval( $_POST['service_price'] ) : 0;
			$service_category      = isset( $_POST['service_category'] ) ? intval( $_POST['service_category'] ) : 0;
			$service_description   = ! empty( $_POST['service_description'] ) ? trim( sanitize_text_field( $_POST['service_description'] ) ) : '';
			$response              = array();
			$response['variant']   = 'error';
			$response['title']     = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']       = esc_html__( 'Something Went wrong...', 'bookingpress-appointment-booking' );

			if ( ! empty( $service_category ) ) {
				$is_service_exist = $wpdb->get_var( "SELECT (bookingpress_category_id) as total from {$tbl_bookingpress_categories} WHERE bookingpress_category_id = {$service_category}" );
				if ( $is_service_exist == 0 ) {
					$response['variant'] = 'error';
					$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
					$response['msg']     = esc_html__( 'Category not found', 'bookingpress-appointment-booking' );

					wp_send_json( $response );
					die();
				}
			}

			if ( ! empty( $service_name ) ) {
				$args = array(
					'bookingpress_category_id'           => $service_category,
					'bookingpress_service_name'          => $service_name,
					'bookingpress_service_price'         => $service_price,
					'bookingpress_service_duration_val'  => $service_duration_val,
					'bookingpress_service_duration_unit' => $service_duration_unit,
					'bookingpress_service_description'   => $service_description,
				);
				if ( ! empty( $service_id ) ) {
					if ( empty( $_POST['service_image'] ) ) {
						$this->bookingpress_add_service_meta( $service_id, 'service_image_details', array() );
					} else {
						if ( ! empty( $_POST['service_image'] ) && ! empty( $_POST['service_image_name'] ) ) {
							$service_img_url  = esc_url_raw( $_POST['service_image'] );
							$service_img_name = sanitize_file_name( $_POST['service_image_name'] );

							$service_image_details[] = array(
								'name' => $service_img_url,
								'url'  => $service_img_name,
							);

							$this->bookingpress_add_service_meta( $service_id, 'service_image_details', maybe_serialize( $service_image_details ) );
						}
					}

					$wpdb->update( $tbl_bookingpress_services, $args, array( 'bookingpress_service_id' => $service_id ) );
					$response['service_id'] = $service_id;
					$response['variant']    = 'success';
					$response['title']      = esc_html__( 'Success', 'bookingpress-appointment-booking' );
					$response['msg']        = esc_html__( 'Service has been updated successfully.', 'bookingpress-appointment-booking' );
				} else {
					$service_position = 0;
					$service          = $wpdb->get_row( 'SELECT * FROM ' . $tbl_bookingpress_services . ' ORDER BY bookingpress_service_position DESC LIMIT 1', ARRAY_A );
					if ( ! empty( $service ) ) {
						$service_position = $service['bookingpress_service_position'] + 1;
					}
					$date                                     = current_time( 'mysql' );
					$args['bookingpress_service_position']    = $service_position;
					$args['bookingpress_servicedate_created'] = $date;
					$wpdb->insert( $tbl_bookingpress_services, $args );
					$service_id             = $wpdb->insert_id;
					$response['service_id'] = $service_id;
					$response['variant']    = 'success';
					$response['title']      = esc_html__( 'Success', 'bookingpress-appointment-booking' );
					$response['msg']        = esc_html__( 'Service has been added successfully.', 'bookingpress-appointment-booking' );
				}

				$response = apply_filters( 'bookingpress_after_add_update_service', $response, $service_id, $_POST );

				if ( ! empty( $service_id ) ) {
					$service_image_url      = ! empty( $_POST['service_image'] ) ? esc_url_raw( $_POST['service_image'] ) : '';
					$service_image_new_name = ! empty( $_POST['service_image_name'] ) ? sanitize_file_name( $_POST['service_image_name'] ) : '';
					if ( ! empty( $service_image_url ) && ! empty( $service_image_new_name ) ) {
						global $BookingPress;
						$upload_dir                 = BOOKINGPRESS_UPLOAD_DIR . '/';
						$bookingpress_new_file_name = current_time( 'timestamp' ) . '_' . $service_image_new_name;
						$upload_path                = $upload_dir . $bookingpress_new_file_name;
						$bookingpress_upload_res    = $BookingPress->bookingpress_file_upload_function( $service_image_url, $upload_path );

						$service_image_new_url   = BOOKINGPRESS_UPLOAD_URL . '/' . $bookingpress_new_file_name;
						$service_image_details   = array();
						$service_image_details[] = array(
							'name' => $bookingpress_new_file_name,
							'url'  => $service_image_new_url,
						);
						$this->bookingpress_add_service_meta( $service_id, 'service_image_details', maybe_serialize( $service_image_details ) );

						$bookingpress_file_name_arr = explode( '/', $service_image_url );
						$bookingpress_file_name     = $bookingpress_file_name_arr[ count( $bookingpress_file_name_arr ) - 1 ];
						unlink( BOOKINGPRESS_TMP_IMAGES_DIR . '/' . $bookingpress_file_name );
					}
				}
			} elseif ( empty( $service_name ) ) {
				$response['msg'] = esc_html__( 'Please add valid data for add service', 'bookingpress-appointment-booking' ) . '.';
			}
			wp_send_json( $response );
		}
		function bookingpress_add_service_meta( $service_id, $meta_key, $meta_value ) {
			 global $wpdb, $tbl_bookingpress_servicesmeta;
			$service_meta = $wpdb->get_row( 'SELECT * FROM ' . $tbl_bookingpress_servicesmeta . ' WHERE bookingpress_service_id = ' . $service_id . " AND bookingpress_servicemeta_name = '" . $meta_key . "'", ARRAY_A );
			if ( ! empty( $service_meta ) ) {
				$servicemeta_id = $service_meta['bookingpress_servicemeta_id'];
				$args           = array(
					'bookingpress_servicemeta_value' => $meta_value,
				);
				$wpdb->update( $tbl_bookingpress_servicesmeta, $args, array( 'bookingpress_servicemeta_id' => $servicemeta_id ) );
			} else {
				$date = current_time( 'mysql' );
				$args = array(
					'bookingpress_service_id'              => $service_id,
					'bookingpress_servicemeta_name'        => $meta_key,
					'bookingpress_servicemeta_value'       => $meta_value,
					'bookingpress_servicemetadate_created' => $date,
				);
				$wpdb->insert( $tbl_bookingpress_servicesmeta, $args );
				$servicemeta_id = $wpdb->insert_id;
			}
			return $servicemeta_id;
		}


		function bookingpress_edit_service() {
			global $wpdb, $tbl_bookingpress_services, $tbl_bookingpress_servicesmeta;
			$service_id            = isset( $_POST['edit_id'] ) ? intval( $_POST['edit_id'] ) : '';
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
			if ( ! empty( $service_id ) ) {
				$service = $wpdb->get_row( 'SELECT * FROM ' . $tbl_bookingpress_services . ' WHERE bookingpress_service_id = ' . $service_id, ARRAY_A );
				if ( ! empty( $service ) ) {
					$response['service_id']            = $service['bookingpress_service_id'];
					$response['category_id']           = $service['bookingpress_category_id'];
					$response['service_name']          = esc_html( $service['bookingpress_service_name'] );
					$response['service_price']         = $service['bookingpress_service_price'];
					$response['service_duration']      = esc_html( $service['bookingpress_service_duration_val'] );
					$response['service_duration_unit'] = esc_html( $service['bookingpress_service_duration_unit'] );
					$response['service_description']   = esc_html( $service['bookingpress_service_description'] );
					$servicemetas                      = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_servicesmeta . ' WHERE bookingpress_service_id = ' . $service_id, ARRAY_A );
					$response['extra_data']            = '';

					if ( ! empty( $servicemetas ) ) {
						foreach ( $servicemetas as $key => $servicemeta ) {
							$response[ $servicemeta['bookingpress_servicemeta_name'] ] = maybe_unserialize( $servicemeta['bookingpress_servicemeta_value'] );
						}
					}
					$response['variant'] = 'success';
					$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
					$response['msg']     = esc_html__( 'Service Data.', 'bookingpress-appointment-booking' );
				}
			}
			wp_send_json( $response );
		}


		function bookingpress_delete_service( $service_id = '' ) {
			global $wpdb, $tbl_bookingpress_services, $tbl_bookingpress_servicesmeta,$tbl_bookingpress_appointment_bookings;
			$response              = array();
			$return                = false;
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( isset( $_POST['action'] ) && sanitize_text_field( $_POST['action'] ) == 'bookingpress_delete_service' ) {
				if ( ! $bpa_verify_nonce_flag ) {
					$response['variant'] = 'error';
					$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
					$response['msg']     = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
					wp_send_json( $response );
					die();
				}
			}
			$service_id          = isset( $_POST['delete_id'] ) ? intval( $_POST['delete_id'] ) : $service_id;
			$response['variant'] = 'error';
			$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']     = esc_html__( 'Something Went wrong...', 'bookingpress-appointment-booking' );

			if ( ! empty( $service_id ) ) {
				$current_date                   = date( 'Y-m-d', current_time( 'timestamp' ) );
				$bookingperss_appointments_data = $wpdb->get_results( 'SELECT bookingpress_appointment_booking_id  FROM ' . $tbl_bookingpress_appointment_bookings . ' WHERE bookingpress_service_id = ' . $service_id . " AND bookingpress_appointment_date >='" . $current_date . "' AND (bookingpress_appointment_status != 'Cancelled' AND bookingpress_appointment_status != 'Rejected')", ARRAY_A );

				if ( count( $bookingperss_appointments_data ) == 0 ) {
					$total_services = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_services . ' order by bookingpress_service_position DESC', ARRAY_A );
					$new_position   = count( $total_services ) - 1;
					$service        = $wpdb->get_row( 'SELECT * FROM ' . $tbl_bookingpress_services . ' WHERE bookingpress_service_id = ' . $service_id, ARRAY_A );
					if ( $service['bookingpress_service_position'] != $new_position ) {
						$this->bookingpress_position_services( $service['bookingpress_service_position'], $new_position );
					}
					$wpdb->delete( $tbl_bookingpress_services, array( 'bookingpress_service_id' => $service_id ), array( '%d' ) );
					$wpdb->delete( $tbl_bookingpress_servicesmeta, array( 'bookingpress_service_id' => $service_id ), array( '%d' ) );
					$response['variant'] = 'success';
					$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
					$response['msg']     = esc_html__( 'Services has been deleted successfully.', 'bookingpress-appointment-booking' );
					$return              = true;
					if ( isset( $_POST['action'] ) && sanitize_text_field( $_POST['action'] ) == 'bookingpress_delete_service' ) {
						wp_send_json( $response );
					}
					return $return;
				} else {
					$bookingpress_error_msg = esc_html__( ' I am sorry', 'bookingpress-appointment-booking' ) . '! ' . esc_html__( 'This service can not be deleted because it has one or more appointments associated with it', 'bookingpress-appointment-booking' ) . '.';

					$response['variant'] = 'warning';
					$response['title']   = esc_html__( 'warning', 'bookingpress-appointment-booking' );
					$response['msg']     = $bookingpress_error_msg;
					$return              = false;
					if ( isset( $_POST['action'] ) && sanitize_text_field( $_POST['action'] ) == 'bookingpress_delete_service' ) {
						wp_send_json( $response );
					}
					return $return;
				}
			}
		}


		function bookingpress_bulk_service() {
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
				$delete_ids = ! empty( $_POST['cat_delete_ids'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['cat_delete_ids'] ) : array();
				if ( ! empty( $delete_ids ) ) {
					foreach ( $delete_ids as $delete_key => $delete_val ) {
						if ( is_array( $delete_val ) ) {
							$delete_val = $delete_val['service_id'];
						}
						$return = $this->bookingpress_delete_service( $delete_val );
						if ( $return ) {
							$response['variant'] = 'success';
							$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
							$response['msg']     = esc_html__( 'Services has been deleted successfully.', 'bookingpress-appointment-booking' );
						} else {
							$response['variant'] = 'warning';
							$response['title']   = esc_html__( 'Warning', 'bookingpress-appointment-booking' );
							$response['msg']     = esc_html__( 'Could not delete service. This service has a appointment in the future.', 'bookingpress-appointment-booking' );
							wp_send_json( $response );
							exit;
						}
					}
				}
			}
			wp_send_json( $response );
		}


		function bookingpress_position_services( $old_position = '', $new_position = '' ) {
			 global $wpdb, $tbl_bookingpress_services;
			$response = array();
			if ( isset( $_POST['action'] ) && sanitize_text_field( $_POST['action'] ) == 'bookingpress_position_services' ) {
				$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
				$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
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
					$condition = 'BETWEEN ' . $old_position . ' AND ' . $new_position;
					$services  = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_services . ' WHERE bookingpress_service_position ' . $condition . ' order by bookingpress_service_position ASC', ARRAY_A );
					foreach ( $services as $service ) {
						$position = $service['bookingpress_service_position'] - 1;
						$position = ( $service['bookingpress_service_position'] == $old_position ) ? $new_position : $position;
						$args     = array(
							'bookingpress_service_position' => $position,
						);
						$wpdb->update( $tbl_bookingpress_services, $args, array( 'bookingpress_service_id' => $service['bookingpress_service_id'] ) );
					}
				} else {
					$services = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_services . ' WHERE bookingpress_service_position BETWEEN ' . $new_position . ' AND ' . $old_position . ' order by bookingpress_service_position ASC', ARRAY_A );
					foreach ( $services as $service ) {
						$position = $service['bookingpress_service_position'] + 1;
						$position = ( $service['bookingpress_service_position'] == $old_position ) ? $new_position : $position;
						$args     = array(
							'bookingpress_service_position' => $position,
						);
						$wpdb->update( $tbl_bookingpress_services, $args, array( 'bookingpress_service_id' => $service['bookingpress_service_id'] ) );
					}
				}
				$response['variant'] = 'success';
				$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Service position has been changed successfully.', 'bookingpress-appointment-booking' );
			}
			if ( isset( $_POST['action'] ) && sanitize_text_field( $_POST['action'] ) == 'bookingpress_position_services' ) {
				wp_send_json( $response );
			}
			return;
		}


		function bookingpress_get_all_services() {
			global $wpdb, $tbl_bookingpress_services;

			$bookingpress_return_data = array();

			$bookingpress_all_service_data = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_services} ORDER BY bookingpress_service_id", ARRAY_A );
			if ( ! empty( $bookingpress_all_service_data ) ) {
				$bookingpress_return_data = $bookingpress_all_service_data;
			}

			return json_encode( $bookingpress_return_data );
		}


		function bookingpress_duplicate_service() {
			 global $wpdb, $tbl_bookingpress_services, $tbl_bookingpress_servicesmeta, $BookingPress;
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
			$response['variant']               = 'error';
			$response['title']                 = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['duplicate_serv_id']     = '';
			$response['msg']                   = esc_html__( 'Something Went wrong...', 'bookingpress-appointment-booking' );
			$bookingpress_duplicate_service_id = ! empty( $_REQUEST['service_id'] ) ? intval( $_REQUEST['service_id'] ) : 0;

			if ( ! empty( $bookingpress_duplicate_service_id ) ) {
				// Fetch duplicate data records from service and service meta
				$bookingpress_duplicate_service = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = {$bookingpress_duplicate_service_id}", ARRAY_A );
				if ( ! empty( $bookingpress_duplicate_service ) ) {
					// Find max position of service
					$bookingpress_find_last_pos = $wpdb->get_row( "SELECT MAX(bookingpress_service_position) as bookingpress_last_pos FROM {$tbl_bookingpress_services}", ARRAY_A );
					$bookingpress_new_pos       = $bookingpress_find_last_pos['bookingpress_last_pos'] + 1;

					$bookingpress_duplicate_service_data = $bookingpress_duplicate_service;
					unset( $bookingpress_duplicate_service_data['bookingpress_service_id'] );
					$bookingpress_duplicate_service_data['bookingpress_service_name']        = __( 'Copy', 'bookingpress-appointment-booking' ) . ' ' . $bookingpress_duplicate_service_data['bookingpress_service_name'];
					$bookingpress_duplicate_service_data['bookingpress_service_position']    = $bookingpress_new_pos;
					$bookingpress_duplicate_service_data['bookingpress_servicedate_created'] = current_time( 'mysql' );

					$wpdb->insert( $tbl_bookingpress_services, $bookingpress_duplicate_service_data );
					$bookingpress_inserted_service_id = $wpdb->insert_id;

					$bookingpress_duplicate_service_meta = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_servicesmeta} WHERE bookingpress_service_id = {$bookingpress_duplicate_service_id}", ARRAY_A );
					if ( ! empty( $bookingpress_duplicate_service_meta ) ) {
						foreach ( $bookingpress_duplicate_service_meta as $bookingpress_duplicate_service_meta_key => $bookingpress_duplicate_service_meta_val ) {
							$bookingpress_service_meta_data = $bookingpress_duplicate_service_meta_val;

							unset( $bookingpress_duplicate_service_meta_val['bookingpress_servicemeta_id'] );
							$bookingpress_duplicate_service_meta_val['bookingpress_service_id']              = $bookingpress_inserted_service_id;
							$bookingpress_duplicate_service_meta_val['bookingpress_servicemetadate_created'] = current_time( 'mysql' );

							if ( $bookingpress_duplicate_service_meta_val['bookingpress_servicemeta_name'] == 'service_image_details' ) {
								// If image exists then copy image
								$bookingpress_service_image_details = maybe_unserialize( $bookingpress_duplicate_service_meta_val['bookingpress_servicemeta_value'] );

								$bookingpress_service_image_url  = ! empty( $bookingpress_service_image_details[0]['url'] ) ? $bookingpress_service_image_details[0]['url'] : '';
								$bookingpress_service_image_name = ! empty( $bookingpress_service_image_details[0]['name'] ) ? $bookingpress_service_image_details[0]['name'] : '';
								if ( ! empty( $bookingpress_service_image_url ) && ! empty( $bookingpress_service_image_name ) ) {
									$bookingpress_service_new_image_name = __( 'copy', 'bookingpress-appointment-booking' ) . '_' . $bookingpress_service_image_name;
									$bookingpress_upload_img_path        = BOOKINGPRESS_UPLOAD_DIR . '/' . $bookingpress_service_new_image_name;
									$BookingPress->bookingpress_file_upload_function( $bookingpress_service_image_url, $bookingpress_upload_img_path );

									$service_image_new_url = BOOKINGPRESS_UPLOAD_URL . '/' . $bookingpress_service_new_image_name;

									$bookingpress_service_image_details[0]['name'] = $bookingpress_service_new_image_name;
									$bookingpress_service_image_details[0]['url']  = $service_image_new_url;
								}

								$bookingpress_duplicate_service_meta_val['bookingpress_servicemeta_value'] = maybe_serialize( $bookingpress_service_image_details );
							}

							$bookingpress_service_meta_data = $bookingpress_duplicate_service_meta_val;

							$wpdb->insert( $tbl_bookingpress_servicesmeta, $bookingpress_service_meta_data );
						}
					}

					$response['variant']           = 'success';
					$response['title']             = esc_html__( 'Success', 'bookingpress-appointment-booking' );
					$response['msg']               = esc_html__( 'Service duplicate successfully', 'bookingpress-appointment-booking' );
					$response['duplicate_serv_id'] = $bookingpress_inserted_service_id;
				} else {
					$response['variant'] = 'error';
					$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
					$response['msg']     = esc_html__( 'No service found...', 'bookingpress-appointment-booking' );
				}
			}

			echo json_encode( $response );
			exit();
		}

	}
}
global $bookingpress_services, $bookingpress_services_vue_data_fields;
$bookingpress_services = new bookingpress_services();


global $bookingpress_global_options;
$bookingpress_options             = $bookingpress_global_options->bookingpress_global_options();
$bookingpress_pagination          = $bookingpress_options['pagination'];
$bookingpress_pagination_arr      = json_decode( $bookingpress_pagination, true );
$bookingpress_pagination_selected = $bookingpress_pagination_arr[0];

$bookingpress_services_vue_data_fields = array(
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
	'category_items'             => array(),
	'multipleSelection'          => array(),
	'perPage'                    => $bookingpress_pagination_selected,
	'totalItems'                 => 0,
	'pagination_selected_length' => $bookingpress_pagination_selected,
	'pagination_length'          => $bookingpress_pagination,
	'currentPage'                => 1,
	'search_service_name'        => '',
	'search_service_category'    => ! empty( $_REQUEST['bookingpress_cat_id'] ) ? intval( $_REQUEST['bookingpress_cat_id'] ) : '',
	'search_categories'          => array(),
	'service'                    => array(
		'service_image'         => '',
		'service_image_name'    => '',
		'service_images_list'   => array(),
		'service_name'          => '',
		'service_category'      => null,
		'service_duration_val'  => 30,
		'service_duration_unit' => 'm',
		'service_price'         => '',
		'service_description'   => '',
		'service_update_id'     => 0,
	),
	'open_service_modal'         => false,
	'open_manage_category_modal' => false,
	'modal_loader'               => 1,
	'activeTabName'              => 'details',
	'serviceCatOptions'          => array(),
	'rules'                      => array(
		'service_name'         => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter service name', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'service_category'     => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please select category', 'bookingpress-appointment-booking' ),
				'trigger'  => 'change',
			),
		),
		'service_duration_val' => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter duration', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
		'service_price'        => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter price', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
	),
	'categoryRules'              => array(
		'service_category_name' => array(
			array(
				'required' => true,
				'message'  => esc_html__( 'Please enter category name', 'bookingpress-appointment-booking' ),
				'trigger'  => 'blur',
			),
		),
	),
	'savebtnloading'             => false,
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
	'service_category'           => array(
		'service_category_name'      => '',
		'service_category_update_id' => 0,
	),
	'open_category_modal'        => false,
	'open_add_category_modal'    => false,
	'serviceShowFileList'        => false,
	'addCategoryModal'           => false,
	'editCategoryModal'          => false,
	'dragging'                   => false,
	'enabled'                    => true,
	'category_modal_pos'         => '80px',
	'is_display_loader'          => '0',
	'is_disabled'                => false,
	'is_display_save_loader'     => '0',
	'is_multiple_checked'        => false,
);
