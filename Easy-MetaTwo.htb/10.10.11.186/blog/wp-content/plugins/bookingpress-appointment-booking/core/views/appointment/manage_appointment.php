<?php
	global $wpdb, $bookingpress_ajaxurl, $BookingPress,$bookingpress_common_date_format, $tbl_bookingpress_appointment_bookings;

	$bookingpress_count_record = $wpdb->get_var( "SELECT COUNT(bookingpress_appointment_booking_id) as total FROM {$tbl_bookingpress_appointment_bookings}" );

?>
<el-main class="bpa-main-listing-card-container bpa-default-card" id="all-page-main-container">
	<el-row type="flex" class="bpa-mlc-head-wrap">
		<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-mlc-left-heading">
			<h1 class="bpa-page-heading"><?php esc_html_e( 'Manage Appointments', 'bookingpress-appointment-booking' ); ?></h1>
		</el-col>		
		<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
			<div class="bpa-hw-right-btn-group">				
				<el-button class="bpa-btn bpa-btn--primary" @click="open_add_appointment_modal()"> 
					<span class="material-icons-round">add</span> 
					<?php esc_html_e( 'Add New', 'bookingpress-appointment-booking' ); ?>
				</el-button>
				<el-button class="bpa-btn" @click="openNeedHelper('list_appointments', 'appointments', '<?php esc_html_e('Appointments', 'bookingpress-appointment-booking'); ?>')">
					<span class="material-icons-round">help</span>
					<?php esc_html_e( 'Need help?', 'bookingpress-appointment-booking' ); ?>
				</el-button>				
				<el-button class="bpa-btn" @click="open_feature_request_url">
					<span class="material-icons-round">lightbulb</span>
					<?php esc_html_e( 'Feature Requests', 'bookingpress-appointment-booking' ); ?>
				</el-button>
			</div>
		</el-col>
	</el-row>
	<div class="bpa-back-loader-container" id="bpa-page-loading-loader">
		<div class="bpa-back-loader"></div>
	</div>
	<div id="bpa-main-container">
		<div class="bpa-table-filter">				
			<el-row type="flex" :gutter="32">			
				<el-col :xs="24" :sm="24" :md="24" :lg="09" :xl="8">
					<span class="bpa-form-label"><?php esc_html_e( 'Date', 'bookingpress-appointment-booking' ); ?></span>
					<el-date-picker class="bpa-form-control bpa-form-control--date-range-picker" format="<?php echo esc_html( $bookingpress_common_date_format ); ?>" v-model="appointment_date_range" type="daterange" 
					start-placeholder="Start date" end-placeholder="End date" @change="search_range_change($event)"
					:popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar"> </el-date-picker>
				</el-col>			
				<el-col :xs="24" :sm="24" :md="24" :lg="9" :xl="8">
					<span class="bpa-form-label"><?php esc_html_e( 'Customer Name', 'bookingpress-appointment-booking' ); ?></span>	
					<el-select class="bpa-form-control" v-model="search_customer_name" multiple filterable collapse-tags 
					placeholder="<?php esc_html_e( 'Select Customer', 'bookingpress-appointment-booking' ); ?>"
					:popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar">
						<el-option v-for="item in search_customer_list" :key="item.value" :label="item.text" :value="item.value">	
						</el-option>
					</el-select>
				</el-col>
				<el-col :xs="24" :sm="24" :md="24" :lg="9" :xl="8">
					<span class="bpa-form-label"><?php esc_html_e( 'Service', 'bookingpress-appointment-booking' ); ?></span>
					<el-select class="bpa-form-control" v-model="search_service_name" multiple filterable collapse-tags 
						placeholder="<?php esc_html_e( 'Select Service', 'bookingpress-appointment-booking' ); ?>"
						:popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar">
					   <el-option-group v-for="service_cat_data in appointment_services_data" :key="service_cat_data.category_name" :label="service_cat_data.category_name">
							<el-option v-for="service_data in service_cat_data.category_services" :key="service_data.service_id" :label="service_data.service_name" :value="service_data.service_id"></el-option>
						</el-option-group>
					</el-select>
				</el-col>
				<el-col :xs="24" :sm="24" :md="24" :lg="9" :xl="8">
					<span class="bpa-form-label"><?php esc_html_e( 'Status', 'bookingpress-appointment-booking' ); ?></span>		
					<el-select class="bpa-form-control" v-model="search_appointment_status" 
						placeholder="<?php esc_html_e( 'Select Status', 'bookingpress-appointment-booking' ); ?>"
						:popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar">
						<el-option v-for="item in search_status" :key="item.value" :label="item.text" :value="item.value"></el-option>
					</el-select>
				</el-col>			
			</el-row><br>
			<el-row type="flex" :gutter="32">
				<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
					<el-input class="bpa-form-control" v-model="search_appointment" placeholder="<?php esc_html_e( 'Search for Customers, Services...', 'bookingpress-appointment-booking' ); ?>" >	
					</el-input>
				</el-col>
				<el-col :xs="24" :sm="24" :md="24" :lg="6" :xl="6">
					<div class="bpa-tf-btn-group">
						<el-button class="bpa-btn bpa-btn__medium bpa-btn--full-width" @click="resetFilter">
							<?php esc_html_e( 'Reset', 'bookingpress-appointment-booking' ); ?>
						</el-button>
						<el-button class="bpa-btn bpa-btn__medium bpa-btn--primary bpa-btn--full-width" @click="loadAppointments()">
							<?php esc_html_e( 'Apply', 'bookingpress-appointment-booking' ); ?>
						</el-button>
					</div>
				</el-col>
			</el-row><br>
		</div>
		<div id="bpa-loader-div">
			<el-row type="flex" v-show="items.length == 0">
				<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
					<div class="bpa-data-empty-view">
						<div class="bpa-ev-left-vector">
							<picture>
								<source srcset="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.webp' ); ?>" type="image/webp">
								<img src="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.png' ); ?>">
							</picture>
						</div>
						<div class="bpa-ev-right-content">
							<h4><?php esc_html_e( 'No Record Found!', 'bookingpress-appointment-booking' ); ?></h4>
							
							<el-button class="bpa-btn bpa-btn--primary bpa-btn__medium" @click="open_add_appointment_modal()"> 						
								<span class="material-icons-round">add</span> 
								<?php esc_html_e( 'Add New', 'bookingpress-appointment-booking' ); ?>
							</el-button>
						</div>
					</div>
				</el-col>
			</el-row>
		</div>
		<el-row v-if="items.length > 0">
			<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
				<el-container class="bpa-table-container">
					<div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
						<div class="bpa-back-loader"></div>
					</div>
					<el-table ref="multipleTable" :data="items" @selection-change="handleSelectionChange" style="width: 100%" fit="false">
						<el-table-column type="selection"></el-table-column>					
						<el-table-column prop="customer_name" label="<?php esc_html_e( 'Customer Name', 'bookingpress-appointment-booking' ); ?>" sortable></el-table-column>
						<el-table-column prop="service_name" label="<?php esc_html_e( 'Service', 'bookingpress-appointment-booking' ); ?>" sortable></el-table-column>
						<el-table-column prop="appointment_duration" label="<?php esc_html_e( 'Duration', 'bookingpress-appointment-booking' ); ?>" sortable></el-table-column>
						<el-table-column prop="appointment_date" label="<?php esc_html_e( 'Appointment Date', 'bookingpress-appointment-booking' ); ?>" sortable></el-table-column>
						<el-table-column prop="appointment_payment" label="<?php esc_html_e( 'Payment', 'bookingpress-appointment-booking' ); ?>" sortable></el-table-column>
						<el-table-column prop="appointment_status" label="<?php esc_html_e( 'Status', 'bookingpress-appointment-booking' ); ?>">
							<template slot-scope="scope">
								<el-select class="bpa-form-control" :class="(scope.row.appointment_status == 'Pending' ? 'bpa-appointment-status--warning' : '') || (scope.row.appointment_status == 'Cancelled' ? 'bpa-appointment-status--cancelled' : '') || (scope.row.appointment_status == 'Approved' ? 'bpa-appointment-status--approved' : '') || (scope.row.appointment_status == 'Rejected' ? 'bpa-appointment-status--rejected' : '')" v-model="scope.row.appointment_status" placeholder="<?php esc_html_e( 'Select Status', 'bookingpress-appointment-booking' ); ?>" @change="bookingpress_change_status(scope.row.appointment_id, $event)" popper-class="bpa-appointment-status-dropdown-popper">
									<el-option-group label="<?php esc_html_e( 'Change status', 'bookingpress-appointment-booking' ); ?>">
										<el-option v-for="item in appointment_status" :key="item.value" :label="item.text" :value="item.value"></el-option>
									</el-option-group>
								</el-select>
								<!-- <el-tag class="bpa-front-pill " :class="(scope.row.appointment_status == 'Pending' ? '--warning' : '') || (scope.row.appointment_status == 'Cancelled' ? '--info' : '')">{{ scope.row.appointment_status }}</el-tag> -->
								<div class="bpa-table-actions-wrap">
									<div class="bpa-table-actions">
										<el-tooltip effect="dark" content="" placement="top" open-delay="300">
											<div slot="content">
												<span><?php esc_html_e( 'Edit', 'bookingpress-appointment-booking' ); ?></span>
											</div>
											<el-button class="bpa-btn bpa-btn--icon-without-box" @click.native.prevent="editAppointmentData(scope.$index, scope.row)">
												<span class="material-icons-round">mode_edit</span>
											</el-button>
										</el-tooltip>
										<el-tooltip effect="dark" content="" placement="top" open-delay="300">
											<div slot="content">
												<span><?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?></span>
											</div>
											<el-popconfirm 
												cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
												confirm-button-text='<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>' 
												icon="false" 
												title="<?php esc_html_e( 'Are you sure you want to delete this appointment?', 'bookingpress-appointment-booking' ); ?>" 
												@confirm="deleteAppointment(scope.$index, scope.row)" 
												confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
												cancel-button-type="bpa-btn bpa-btn__small">
												<el-button type="text" slot="reference" class="bpa-btn bpa-btn--icon-without-box __danger">
													<span class="material-icons-round">delete</span>
												</el-button>
											</el-popconfirm>
										</el-tooltip>
									</div>
								</div>
							</template>
						</el-table-column>
					</el-table>				
				</el-container>
			</el-col>
		</el-row>
		<el-row class="bpa-pagination" type="flex" v-if="items.length > 0"> <!-- Pagination -->
			<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" >
				<div class="bpa-pagination-left">
					<p><?php esc_html_e( 'Displaying', 'bookingpress-appointment-booking' ); ?> <strong><u>{{ items.length }}</u></strong><?php esc_html_e( 'out of', 'bookingpress-appointment-booking' ); ?><strong>{{ totalItems }}</strong></p>
					<div class="bpa-pagination-per-page">
						<p><?php esc_html_e( 'Displaying Per Page', 'bookingpress-appointment-booking' ); ?></p>
						<el-select v-model="pagination_length_val" placeholder="Select" @change="changePaginationSize($event)" class="bpa-form-control" popper-class="bpa-pagination-dropdown">
							<el-option v-for="item in pagination_val" :key="item.text" :label="item.text" :value="item.value"></el-option>
						</el-select>
					</div>
				</div>
			</el-col>
			<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" class="bpa-pagination-nav">
				<el-pagination @size-change="handleSizeChange" @current-change="handleCurrentChange" :current-page.sync="currentPage" layout="prev, pager, next" :total="totalItems" :page-sizes="pagination_length" :page-size="perPage"></el-pagination>
			</el-col>
			<el-container v-if="multipleSelection.length > 0" class="bpa-default-card bpa-bulk-actions-card" >
				<el-button class="bpa-btn bpa-btn--icon-without-box bpa-bac__close-icon" @click="closeBulkAction">
					<span class="material-icons-round">close</span>
				</el-button>
				<el-row type="flex" class="bpa-bac__wrapper">
					<el-col class="bpa-bac__left-area" :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
						<span class="material-icons-round">check_circle</span>
						<p>{{ multipleSelection.length }}<?php esc_html_e( ' Items Selected', 'bookingpress-appointment-booking' ); ?></p>
					</el-col>
					<el-col class="bpa-bac__right-area" :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
						<el-select class="bpa-form-control" v-model="bulk_action" placeholder="<?php esc_html_e( 'Select', 'bookingpress-appointment-booking' ); ?>"
						popper-class="bpa-dropdown--bulk-actions">
							<el-option v-for="item in bulk_options" :key="item.value" :label="item.label" :value="item.value"></el-option>
						</el-select>
						<el-button @click="bulk_actions()" class="bpa-btn bpa-btn--primary bpa-btn__medium">
							<?php esc_html_e( 'Go', 'bookingpress-appointment-booking' ); ?>
						</el-button>
					</el-col>
				</el-row>
			</el-container>		
		</el-row>
	</div>
</el-main>

<el-dialog custom-class="bpa-dialog bpa-dialog--fullscreen" modal-append-to-body=false :visible.sync="open_appointment_modal" :before-close="closeAppointmentModal" fullscreen=true :close-on-press-escape="close_modal_on_esc">
	<div class="bpa-dialog-heading">
		<el-row type="flex">
			<el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
				<h1 class="bpa-page-heading" v-if="appointment_formdata.appointment_update_id == 0"><?php esc_html_e( 'Add Appointment', 'bookingpress-appointment-booking' ); ?></h1>
				<h1 class="bpa-page-heading" v-else><?php esc_html_e( 'Edit Appointment', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>
			<el-col :xs="12" :sm="12" :md="7" :lg="7" :xl="7" class="bpa-dh__btn-group-col">
				<el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveAppointmentBooking('appointment_formdata')" :disabled="is_disabled" >					
				  <span class="bpa-btn__label"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></span>
				  <div class="bpa-btn--loader__circles">				    
					  <div></div>
					  <div></div>
					  <div></div>
				  </div>
				</el-button>
				<el-button class="bpa-btn" @click="closeAppointmentModal()"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
			</el-col>
		</el-row>
	</div>
	<div class="bpa-dialog-body">
		<div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
			<div class="bpa-back-loader"></div>
		</div>
		<div class="bpa-form-row">
			<el-row>
				<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
					<div class="bpa-db-sec-heading">
						<el-row type="flex" align="middle">
							<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
								<div class="db-sec-left">
									<h2 class="bpa-page-heading"><?php esc_html_e( 'Basic Details', 'bookingpress-appointment-booking' ); ?></h2>
								</div>
							</el-col>							
							<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
								<div class="bpa-hw-right-btn-group">
									<el-button class="bpa-btn bpa-btn--icon-without-box __is-label" @click="openNeedHelper('list_appointments', 'appointments', '<?php esc_html_e('Appointments', 'bookingpress-appointment-booking'); ?>')">
										<span class="material-icons-round">help</span>
										<?php esc_html_e( 'Need help?', 'bookingpress-appointment-booking' ); ?>
									</el-button>
								</div>
							</el-col>
						</el-row>
					</div>
					<div class="bpa-default-card bpa-db-card">
						<el-form ref="appointment_formdata" :rules="rules" :model="appointment_formdata" label-position="top" @submit.native.prevent>
							<template>								
								<div class="bpa-form-body-row">
									<el-row :gutter="32">
										<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">											
											<el-form-item prop="appointment_selected_customer">
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Select Customer', 'bookingpress-appointment-booking' ); ?></span>
												</template>
												<el-select class="bpa-form-control" v-model="appointment_formdata.appointment_selected_customer" name="appointment_selected_customer" filterable 
												placeholder="<?php esc_html_e( 'Select Customer', 'bookingpress-appointment-booking' ); ?>"
												popper-class="bpa-el-select--is-with-modal">
													<el-option v-for="customer_data in appointment_customers_list" :key="customer_data.value" :label="customer_data.text" :value="customer_data.value">
														<i class="el-icon-plus" v-if="customer_data.value == 'add_new'"></i>
														<span>{{ customer_data.text }}</span>
													</el-option>
												</el-select>
											</el-form-item>
										</el-col>													
										<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
											<el-form-item prop="appointment_selected_service">
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Select Service', 'bookingpress-appointment-booking' ); ?></span>
												</template>
												<el-select class="bpa-form-control" @Change="bookingpress_set_time_slot()" v-model="appointment_formdata.appointment_selected_service" name="appointment_selected_service" filterable 
												placeholder="<?php esc_html_e( 'Select Service', 'bookingpress-appointment-booking' ); ?>"
												popper-class="bpa-el-select--is-with-modal">
													<el-option-group v-for="service_cat_data in appointment_services_list" :key="service_cat_data.category_name" :label="service_cat_data.category_name">
														<template v-if="service_data.service_id == 0" v-for="service_data in service_cat_data.category_services">
															<el-option :key="service_data.service_id" :label="service_data.service_name" :value="''" ></el-option>
														</template>
														<template v-else>
															<el-option :key="service_data.service_id" :label="service_data.service_name+' ('+service_data.service_price+' )'" :value="service_data.service_id"></el-option>
														</template>
													</el-option-group>
												</el-select>
											</el-form-item>
										</el-col>
										<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
											<el-form-item prop="appointment_booked_date">
											<template #label>
												<span class="bpa-form-label"><?php esc_html_e( 'Appointment Date', 'bookingpress-appointment-booking' ); ?></span>
											</template>
											<el-date-picker class="bpa-form-control bpa-form-control--date-picker" format="<?php echo esc_html( $bookingpress_common_date_format ); ?>" v-model="appointment_formdata.appointment_booked_date" name="appointment_booked_date" type="date" :clearable="false" :picker-options="pickerOptions" @change="select_date($event)" ></el-date-picker>
										</el-form-item>
										</el-col>
										<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
											<el-form-item prop="appointment_booked_time">
											<template #label>
												<span class="bpa-form-label"><?php esc_html_e( 'Appointment Time', 'bookingpress-appointment-booking' ); ?></span>
											</template>
											<el-select class="bpa-form-control" Placeholder="<?php esc_html_e( 'Select Time', 'bookingpress-appointment-booking' ); ?>" v-model="appointment_formdata.appointment_booked_time" filterable popper-class="bpa-el-select--is-with-modal">
												<el-option-group v-for="appointment_time_slot_data in appointment_time_slot" :key="appointment_time_slot_data.timeslot_label" :label="appointment_time_slot_data.timeslot_label">
													<el-option v-for="appointment_time in appointment_time_slot_data.timeslots" :key="appointment_time.start_time" :label="appointment_time.start_time+' to '+appointment_time.end_time" :value="appointment_time.start_time" :disabled="appointment_time.is_disabled">
														<span>{{ appointment_time.start_time | bookingpress_format_time }} to {{appointment_time.end_time | bookingpress_format_time}}</span>
													</el-option>	
												</el-option-group>
											</el-select>
										</el-form-item>
										</el-col>
										<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
											<el-form-item>
											<template #label>
												<span class="bpa-form-label"><?php esc_html_e( 'Select Status', 'bookingpress-appointment-booking' ); ?></span>
											</template>
											<el-select class="bpa-form-control" v-model="appointment_formdata.appointment_status">
												<el-option v-for="status_data in appointment_status" :key="status_data.value" :label="status_data.text" :value="status_data.value">
													<span>{{ status_data.text }}</span>
												</el-option>
											</el-select>
											</el-form-item>
										</el-col>
									</el-row>
								</div>
								<div class="bpa-form-body-row">
									<el-row :gutter="24">
										<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
											<el-form-item>
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Internal note', 'bookingpress-appointment-booking' ); ?></span>
												</template>
												<el-input class="bpa-form-control" type="textarea" :rows="5" v-model="appointment_formdata.appointment_internal_note"></el-input>
											</el-form-item>
										</el-col>
									</el-row>
								</div>								
								<div class="bpa-form-body-row">
									<el-row :gutter="24">
										<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
											<el-form-item>
												<label class="bpa-form-label bpa-custom-checkbox--is-label"> <el-checkbox v-model="appointment_formdata.appointment_send_notification"></el-checkbox> <?php esc_html_e( 'Send Notifications', 'bookingpress-appointment-booking' ); ?></label>
											</el-form-item>
										</el-col> 										
									</el-row>
								</div>	
							</template>
						</el-form>
					</div>
				</el-col>
			</el-row>			
		</div>
	</div>
</el-dialog>
