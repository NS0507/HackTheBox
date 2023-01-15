<?php global $bookingpress_common_date_format; ?>
<el-main class="bpa-main-listing-card-container bpa-default-card" id="all-page-main-container">
	<el-row type="flex" class="bpa-mlc-head-wrap">
		<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-mlc-left-heading">
			<h1 class="bpa-page-heading"><?php esc_html_e( 'Calendar', 'bookingpress-appointment-booking' ); ?></h1>
		</el-col>
		<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
			<div class="bpa-hw-right-btn-group">
				<el-button class="bpa-btn bpa-btn--primary" @click="openAppointmentBookingModal"> 
					<span class="material-icons-round">add</span> 
					<?php esc_html_e( 'Add Appointment', 'bookingpress-appointment-booking' ); ?>
				</el-button>
				<el-button class="bpa-btn" @click="openNeedHelper('list_calendar', 'calendar', '<?php esc_html_e('Calendar', 'bookingpress-appointment-booking'); ?>')">
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
		<el-row>
			<div class="bpa-calendar-filter-div bpa-table-filter">
				<el-row type="flex" :gutter="32">
					<el-col :xs="24" :sm="24" :md="24" :lg="6" :xl="6">
						<span class="bpa-form-label"><?php esc_html_e( 'Service', 'bookingpress-appointment-booking' ); ?></span>
						<el-select v-model="search_data.selected_services" class="bpa-form-control" multiple filterable collapse-tags 
							placeholder="<?php esc_html_e( 'Select Service', 'bookingpress-appointment-booking' ); ?>"
							:popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar">		
							<el-option-group v-for="item in appointment_services_data" :key="item.category_name" :label="item.category_name">
								<el-option v-for="cat_services in item.category_services" :key="cat_services.service_id" :label="cat_services.service_name" :value="cat_services.service_id"></el-option>
							</el-option-group>
						</el-select>
					</el-col>	
					<el-col :xs="24" :sm="24" :md="24" :lg="6" :xl="6">
						<span class="bpa-form-label"><?php esc_html_e( 'Customers', 'bookingpress-appointment-booking' ); ?></span>
						<el-select v-model="search_data.selected_customers" class="bpa-form-control" multiple filterable collapse-tags 
							placeholder="<?php esc_html_e( 'Select Customers', 'bookingpress-appointment-booking' ); ?>"
							:popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar">		
							<el-option v-for="item in search_customer_list" :key="item.value" :label="item.text" :value="item.value"></el-option>
						</el-select>
					</el-col>
					<el-col :xs="24" :sm="24" :md="24" :lg="6" :xl="6">
						<span class="bpa-form-label"><?php esc_html_e( 'Appointment Status', 'bookingpress-appointment-booking' ); ?></span>
						<el-select class="bpa-form-control" v-model="search_data.selected_status" 
							placeholder="<?php esc_html_e( 'Select Status', 'bookingpress-appointment-booking' ); ?>"
							:popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar">
							<el-option v-for="item in search_status" :key="item.value" :label="item.text" :value="item.value"></el-option>
						</el-select>
					</el-col>
					<el-col :xs="24" :sm="24" :md="24" :lg="6" :xl="6">
						<div class="bpa-tf-btn-group">
							<el-button class="bpa-btn bpa-btn__medium bpa-btn--full-width" @click="resetFilter">
								<?php esc_html_e( 'Reset', 'bookingpress-appointment-booking' ); ?>
							</el-button>
							<el-button class="bpa-btn bpa-btn__medium bpa-btn--primary bpa-btn--full-width" @click="loadCalendar">
								<?php esc_html_e( 'Apply', 'bookingpress-appointment-booking' ); ?>
							</el-button>
						</div>
					</el-col>
				</el-row>
			</div>
			<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
				<div class="bpa-full-screen-calendar">
					<div class="bpa-fsc--custom-filter-header">
						<div class="bpa-cfh--wrapper">
							<div class="bpa-cfh__left">
								<el-button class="bpa-btn bpa-btn__medium" @click="$refs.bpavuecal.previous()">
									<span class="material-icons-round">arrow_back_ios</span>
								</el-button>
								<el-button class="bpa-btn bpa-btn__medium" @click="$refs.bpavuecal.next()">
									<span class="material-icons-round">arrow_forward_ios</span>
								</el-button>
							</div>
							<div class="bpa-cfh__right">
								<div class="bpa-cfh__legends">
									<div class="bpa-cfh__legends--item">
										<p><?php esc_html_e('Approved', 'bookingpress-appointment-booking'); ?></p>
									</div>
									<div class="bpa-cfh__legends--item">
										<p><?php esc_html_e('Pending', 'bookingpress-appointment-booking'); ?></p>
									</div>
									<div class="bpa-cfh__legends--item">
										<p><?php esc_html_e('Rejected', 'bookingpress-appointment-booking'); ?></p>
									</div>
								</div>
								<div class="bpa-cfh__btns">
									<div class="bpa-cfh__btns-wrapper">
									<el-button class="bpa-btn bpa-btn__medium" :class="activeView == 'month' ? 'bpa-btn--primary' : ''" @click="activeView = 'month'"><?php esc_html_e( 'Month','bookingpress-appointment-booking' ); ?></el-button>
									<el-button class="bpa-btn bpa-btn__medium" :class="activeView == 'week' ? 'bpa-btn--primary' : ''" @click="activeView = 'week'"><?php esc_html_e( 'Week','bookingpress-appointment-booking' ); ?></el-button>
									<el-button class="bpa-btn bpa-btn__medium" :class="activeView == 'day' ? 'bpa-btn--primary' : ''" @click="activeView = 'day'"><?php esc_html_e( 'Day','bookingpress-appointment-booking' ); ?></el-button>
									</div>
								</div>
							</div>
						</div>
					</div>
					<el-row>
						<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
							<vue-cal ref="bpavuecal" small :selected-date="calendar_current_date" :time-from="00 * 60" :time-to="25 * 60" :disable-views="['years', 'year']" :events="calendar_events_data" :on-event-click="editEvent" :showAllDayEvents="show_all_day_events" events-on-month-view="true" hide-view-selector :active-view.sync="activeView" :min-event-width="minEventWidth" :locale="site_locale">
								<template v-slot:title="{ title, view }">
									<span v-if="view.id === 'month'">{{ view.startDate.toLocaleString(site_locale, { month: "long" }) }} {{ view.startDate.format('YYYY') }}</span>
									<span v-if="view.id === 'week'"><?php esc_html_e( 'Week', 'bookingpress-appointment-booking' ); ?> {{ view.startDate.getWeek() }} ({{ view.startDate.toLocaleString(site_locale, { month: "long" }) }} {{ view.startDate.format('YYYY') }})</span>
									<span v-if="view.id === 'day'">{{ view.startDate.format('D') }} {{ view.startDate.toLocaleString(site_locale, { month: "long" }) }} {{ view.startDate.format('YYYY') }}</span>
								</template>

								<template v-slot:arrow-prev>
									<span></span>
								</template>

								<template v-slot:arrow-next>
									<span></span>
								</template>
							</vue-cal>
						</el-col>
					</el-row>
				</div>
			</el-col>
		</el-row>
	</div>
</el-main>

<el-dialog id="calendar_appointment_modal" custom-class="bpa-dialog bpa-dialog--fullscreen" title="" :visible.sync="open_calendar_appointment_modal" top="32px" fullscreen="true" :close-on-press-escape="close_modal_on_esc">
	<div class="bpa-dialog-heading">
		<el-row type="flex">
			<el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
				<h1 class="bpa-page-heading" v-if="appointment_formdata.appointment_update_id == '0'"><?php esc_html_e( 'Add Appointment', 'bookingpress-appointment-booking' ); ?></h1>
				<h1 class="bpa-page-heading" v-else><?php esc_html_e( 'Edit Appointment', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>
			<el-col :xs="12" :sm="12" :md="7" :lg="7" :xl="7" class="bpa-dh__btn-group-col">
				<el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveAppointmentBooking('appointment_formdata')" >					
				  <span class="bpa-btn__label"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></span>
				  <div class="bpa-btn--loader__circles">				    
					  <div></div>
					  <div></div>
					  <div></div>
				  </div>
				</el-button>
				<el-button class="bpa-btn" @click="closeAppointmentBookingModal"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
			</el-col>
		</el-row>
	</div>
	<div class="bpa-dialog-body">
		<div class="bpa-form-row">
			<el-row>
				<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
					<div class="bpa-default-card bpa-db-card">
						<el-form ref="appointment_formdata" :rules="rules" :model="appointment_formdata" label-position="top" @submit.native.prevent>
							<div class="bpa-form-body-row">
								<el-row :gutter="24">
									<el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
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
									<el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
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
									<el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
										<el-form-item prop="appointment_booked_date">
											<template #label>
												<span class="bpa-form-label"><?php esc_html_e( 'Appointment Date', 'bookingpress-appointment-booking' ); ?></span>
											</template>
											<el-date-picker class="bpa-form-control bpa-form-control--date-picker" format="<?php echo esc_html( $bookingpress_common_date_format ); ?>" v-model="appointment_formdata.appointment_booked_date" 
											name="appointment_booked_date" :clearable="false" :picker-options="pickerOptions" @change="formatted_date($event)"
											popper-class="bpa-el-select--is-with-modal"></el-date-picker>
										</el-form-item>
									</el-col>
									<el-col :xs="24" :sm="24" :md="8" :lg="12" :xl="12">
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
									<el-col :xs="24" :sm="24" :md="8" :lg="12" :xl="12">
										<el-form-item>
											<template #label>
												<span class="bpa-form-label"><?php esc_html_e( 'Select Status', 'bookingpress-appointment-booking' ); ?></span>
											</template>
											<el-select class="bpa-form-control" v-model="appointment_formdata.appointment_status" popper-class="bpa-el-select--is-with-modal">
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
						</el-form>
					</div>
				</el-col>
			</el-row>
		</div>
	</div>
</el-dialog>
