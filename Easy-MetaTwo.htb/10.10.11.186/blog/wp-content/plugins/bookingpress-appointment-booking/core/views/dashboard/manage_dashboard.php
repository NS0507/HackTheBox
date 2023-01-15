<?php
	global $BookingPress,$bookingpress_common_date_format;
?>

<el-main class="bpa-main-listing-card-container bpa-dashboard-container" id="all-page-main-container">
	<div class="bpa-back-loader-container" id="bpa-page-loading-loader">
		<div class="bpa-back-loader"></div>
	</div>
	
	<div class="bpa-default-card bpa-dashboard--summary">
		<el-row type="flex" class="bpa-mlc-head-wrap">
			<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-mlc-left-heading bpa-mlc-left-heading--is-visible-help">
				<h1 class="bpa-page-heading"><?php esc_html_e( 'Overview', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>
		</el-row>
		<div id="bpa-main-container">
			<div class="bpa-dashboard--summary-body">
				<el-row class="bpa-dashboard-summary-filter">
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<el-button class="bpa-btn " :class="currently_selected_filter == 'today' ? 'bpa-btn--primary' : 'bpa-btn--default'" @click="select_dashboard_filter('today')">
							<?php esc_html_e( 'Today', 'bookingpress-appointment-booking' ); ?>
						</el-button>
						<el-button class="bpa-btn " :class="currently_selected_filter == 'yesterday' ? 'bpa-btn--primary' : 'bpa-btn--default'" @click="select_dashboard_filter('yesterday')">
							<?php esc_html_e( 'Yesterday', 'bookingpress-appointment-booking' ); ?>
						</el-button>
						<el-button class="bpa-btn " :class="currently_selected_filter == 'tomorrow' ? 'bpa-btn--primary' : 'bpa-btn--default'" @click="select_dashboard_filter('tomorrow')">
							<?php esc_html_e( 'Tomorrow', 'bookingpress-appointment-booking' ); ?>
						</el-button>
						<el-button class="bpa-btn " :class="currently_selected_filter == 'week' ? 'bpa-btn--primary' : 'bpa-btn--default'" @click="select_dashboard_filter('week')">
							<?php esc_html_e( 'This week', 'bookingpress-appointment-booking' ); ?>
						</el-button>
						<el-button class="bpa-btn " :class="currently_selected_filter == 'last_week' ? 'bpa-btn--primary' : 'bpa-btn--default'" @click="select_dashboard_filter('last_week')">
							<?php esc_html_e( 'Last week', 'bookingpress-appointment-booking' ); ?>
						</el-button>
						<el-button class="bpa-btn " :class="currently_selected_filter == 'monthly' ? 'bpa-btn--primary' : 'bpa-btn--default'" @click="select_dashboard_filter('monthly')">
							<?php esc_html_e( 'This month', 'bookingpress-appointment-booking' ); ?>
						</el-button>
						<el-button class="bpa-btn " :class="currently_selected_filter == 'yearly' ? 'bpa-btn--primary' : 'bpa-btn--default'" @click="select_dashboard_filter('yearly')">
							<?php esc_html_e( 'This year', 'bookingpress-appointment-booking' ); ?>
						</el-button>
						<el-button class="bpa-btn " :class="currently_selected_filter == 'custom' ? 'bpa-btn--primary' : 'bpa-btn--default'" @click="select_dashboard_filter('custom')">
							<?php esc_html_e( 'Custom', 'bookingpress-appointment-booking' ); ?>
						</el-button>
					</el-col>
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="6" class="bpa-dsf-col--custom-date-picker" v-if="currently_selected_filter == 'custom'">
					<el-date-picker class="bpa-form-control bpa-form-control--date-range-picker" v-model="custom_filter_val" 
					type="daterange" start-placeholder="Start date" end-placeholder="End date" @change="select_dashboard_custom_date_filter($event)"
					:popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar"></el-date-picker>
					</el-col>
				</el-row>
				<div class="bpa-dashboard-summary">
					<div class="bpa-dash-summary-item">
						<h1 v-text="summary_data.total_appoint"></h1>
						<p><?php esc_html_e( 'Appointments', 'bookingpress-appointment-booking' ); ?></p>
					</div>
					<div class="bpa-dash-summary-item bpa-dash-summary-item__primary">
						<h1 v-text="summary_data.approved_appoint"></h1>
						<p><?php esc_html_e( 'Approved Appointments', 'bookingpress-appointment-booking' ); ?></p>
					</div>
					<div class="bpa-dash-summary-item bpa-dash-summary-item__secondary">
						<h1 v-text="summary_data.pending_appoint"></h1>
						<p><?php esc_html_e( 'Pending Appointments', 'bookingpress-appointment-booking' ); ?></p>
					</div>
					<div class="bpa-dash-summary-item bpa-dash-summary-item__royal-blue">
						<h1 v-text="summary_data.total_revenue"></h1>
						<p><?php esc_html_e( 'Revenue', 'bookingpress-appointment-booking' ); ?></p>
					</div>
					<div class="bpa-dash-summary-item bpa-dash-summary-item__purple">
						<h1 v-text="summary_data.total_customers"></h1>
						<p><?php esc_html_e( 'Customers', 'bookingpress-appointment-booking' ); ?></p>
					</div>
				</div>
			</div>
			<div class="bpa-dashboard--technical-analysis">
				<el-row type="flex" class="bpa-mlc-head-wrap">
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-mlc-left-heading bpa-mlc-left-heading--is-visible-help">
						<h1 class="bpa-page-heading"><?php esc_html_e( 'Technical Analysis', 'bookingpress-appointment-booking' ); ?></h1>
					</el-col>
				</el-row>
				<div class="bpa-dashboard--technical-analysis-body">
					<el-row :gutter="24">
						<el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
							<canvas id="appointments_charts" width="400" height="400"></canvas>
						</el-col>
						<el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
							<canvas id="revenue_charts" width="400" height="400"></canvas>
						</el-col>
						<el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
							<canvas id="customer_charts" width="400" height="400"></canvas>
						</el-col>
					</el-row>
				</div>
			</div>
		</div>
	</div>
	
	<div id="bpa-main-container-2">
		<el-row class="bpa-default-card bpa-dashboard--upcoming-appointments">
			<el-row type="flex" class="bpa-mlc-head-wrap">
				<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
					<h1 class="bpa-page-heading"><?php esc_html_e( 'Upcoming Appointments', 'bookingpress-appointment-booking' ); ?></h1>
				</el-col>
			</el-row>
		
			<el-row type="flex" v-if="items.length == 0">
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
						</div>
					</div>
				</el-col>
			</el-row>
			<el-row v-else>
				<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
					<el-container class="bpa-table-container bpa-table-container--is-not-checkbox-control">
						<el-table ref="multipleTable" :data="items" fit="false">
							<el-table-column prop="customer_name" label="<?php esc_html_e( 'Customer Name', 'bookingpress-appointment-booking' ); ?>"></el-table-column>
							<el-table-column prop="service_name" label="<?php esc_html_e( 'Service', 'bookingpress-appointment-booking' ); ?>"></el-table-column>
							<el-table-column prop="payment_date" label="<?php esc_html_e( 'Appointment Date', 'bookingpress-appointment-booking' ); ?>"></el-table-column>
							<el-table-column prop="appointment_duration" label="<?php esc_html_e( 'Duration', 'bookingpress-appointment-booking' ); ?>"></el-table-column>
							<el-table-column prop="appointment_payment" label="<?php esc_html_e( 'Payment', 'bookingpress-appointment-booking' ); ?>"></el-table-column>
							<el-table-column prop="appointment_status" label="<?php esc_html_e( 'Status', 'bookingpress-appointment-booking' ); ?>">
								<template slot-scope="scope">
									<el-select class="bpa-form-control" :class="(scope.row.appointment_status == 'Pending' ? 'bpa-appointment-status--warning' : '') || (scope.row.appointment_status == 'Cancelled' ? 'bpa-appointment-status--cancelled' : '') || (scope.row.appointment_status == 'Approved' ? 'bpa-appointment-status--approved' : '') || (scope.row.appointment_status == 'Rejected' ? 'bpa-appointment-status--rejected' : '')" v-model="scope.row.appointment_status" placeholder="<?php esc_html_e( 'Select Status', 'bookingpress-appointment-booking' ); ?>" @change="bookingpress_change_status(scope.row.appointment_id, $event)" popper-class="bpa-appointment-status-dropdown-popper">
										<el-option-group label="<?php esc_html_e( 'Change status', 'bookingpress-appointment-booking' ); ?>">
											<el-option v-for="item in appointment_status" :key="item.value" :label="item.text" :value="item.value"></el-option>
										</el-option-group>
									</el-select>
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
										</div>
									</div>
								</template>
							</el-table-column>
						</el-table>				
					</el-container>
				</el-col>
			</el-row>
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
				<el-button class="bpa-btn bpa-btn--primary " :class="is_display_save_loader == '1' ? 'bpa-btn--is-loader' : ''" @click="saveAppointmentBooking('appointment_formdata')" :disabled="is_disabled" >
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
												<el-select class="bpa-form-control" v-model="appointment_formdata.appointment_selected_customer" name="appointment_selected_customer" filterable placeholder="<?php esc_html_e( 'Select Customer', 'bookingpress-appointment-booking' ); ?>">
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
												<el-select class="bpa-form-control" @Change="bookingpress_set_time_slot()" v-model="appointment_formdata.appointment_selected_service" name="appointment_selected_service" filterable placeholder="<?php esc_html_e( 'Select Service', 'bookingpress-appointment-booking' ); ?>" >
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
											<el-date-picker class="bpa-form-control bpa-form-control--date-picker" format="<?php echo esc_html( $bookingpress_common_date_format ); ?>"v-model="appointment_formdata.appointment_booked_date" name="appointment_booked_date" :clearable="false" @change="select_date($event)" :picker-options="pickerOptions"></el-date-picker>
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
