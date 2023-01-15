<el-main class="bpa-frontend-main-container" id="bookingpress_booking_form_<?php echo $bookingpress_uniq_id; ?>">
	
	<div class="bpa-front-tabs bpa-front-tabs--vertical-left <?php echo ( $bookingpress_tabs_position == 'left' ) ? 'bpa-front-tabs--left' : '--top'; ?>" v-if="service_categories != ''">
		<div class="bpa-front-tab-menu">
			<a href="#" class="bpa-front-tab-menu--item" :class="(current_selected_tab_id == '1') ? ' __bpa-is-active' : ''" data-id="1" @click="next_page('', 1)" v-if="hide_category_service != '1'">
				<span class="material-icons-round">dns</span>
				<?php echo esc_html( $bookingpress_first_tab_name ); ?>
			</a>
			<a href="#" class="bpa-front-tab-menu--item" :class="(current_selected_tab_id == '2') ? ' __bpa-is-active' : ''" data-id="2" @click="next_page('', 2)">
				<span class="material-icons-round">date_range</span>
				<?php echo esc_html( $bookingpress_second_tab_name ); ?>
			</a>
			<a href="#" class="bpa-front-tab-menu--item" :class="(current_selected_tab_id == '3') ? ' __bpa-is-active' : ''" data-id="3" @click="next_page('appointment_step_form_data', 3)">
				<span class="material-icons-round">article</span>
				<?php echo esc_html( $bookingpress_third_tab_name ); ?>
			</a>
			<a href="#" class="bpa-front-tab-menu--item" :class="(current_selected_tab_id == '4') ? ' __bpa-is-active' : ''" data-id="4" @click="next_page('appointment_step_form_data', 4)">
				<span class="material-icons-round">assignment_turned_in</span>
				<?php echo esc_html( $bookingpress_fourth_tab_name ); ?>
			</a>
		</div><!--end of tab-menu-->

		<div class="bpa-front-tabs--panel-body" :class="[current_selected_tab_id == '1' ? ' __bpa-is-active' : '', current_selected_tab_id < previous_selected_tab_id ? ' __is-previous' : '']" data-id="1" v-if="hide_category_service != '1'">
			
			<div class="bpa-front-default-card">
				<div class="bpa-front-toast-notification --bpa-error" v-if="is_display_error == '1'">
					<div class="bpa-front-tn-body">
						<span class="material-icons-round">error_outline</span>
						<p>{{ is_error_msg }}</p>
						<!--<a href="#" class="close-icon"><span class="material-icons-round">close</span></a>-->
					</div>
				</div>
				<div class="bpa-front-dc--body">
					<el-row>
						<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
							<div class="bpa-front-module-container bpa-front-module--category">
								<el-row>
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<h2 class="bpa-front-module-heading"><?php echo esc_html( $bookingpress_category_title ); ?></h2>
									</el-col>
								</el-row>
								<el-row>
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<div class="bpa-front-cat-items-wrapper">
											<div class="bpa-front-cat-items">
													<el-tag class="bpa-front-ci-pill" :class="(appointment_step_form_data.selected_category == cat_data.bookingpress_category_id) ? '__bpa-is-active' : '' " v-for="cat_data in service_categories" @click="selectStepCategory(cat_data.bookingpress_category_id, cat_data.bookingpress_category_name,appointment_step_form_data.total_services)">
														<div class="bpa-front-ci-item-title">{{ cat_data.bookingpress_category_name }}</div>
														<i class="material-icons-round" v-if="appointment_step_form_data.selected_category == cat_data.bookingpress_category_id">check_circle</i>
													</el-tag>
											</div>
										</div>
									</el-col>
								</el-row>
							</div>
						</el-col>
					</el-row>
					<el-row>
						<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
							<div class="bpa-front-module-container bpa-front-module--service">
								<el-row>
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<h2 class="bpa-front-module-heading"><?php echo esc_html( $bookingpress_services_title ); ?></h2>
									</el-col>
								</el-row>
								<div class="bpa-front-module--service-items-row">									
									<div class="bpa-fm--si--col" v-for="service_details in services_data">
										<div class="bpa-front-module--service-item" :class="[(appointment_step_form_data.selected_service == service_details.bookingpress_service_id) ? '__bpa-is-selected' : '', (display_service_description == 1) ? '__bpa-is-description-enable' : '']" @click="selectDate(service_details.bookingpress_service_id, service_details.bookingpress_service_name, service_details.bookingpress_service_price, service_details.service_price_without_currency)">
											<div class="bpa-front-si-card">
												<div class="bpa-front-si-card--checkmark-icon" v-if="appointment_step_form_data.selected_service == service_details.bookingpress_service_id">
													<span class="material-icons-round">check_circle</span>
												</div>
												<div class="bpa-front-si-card__left">
													<img :src="service_details.img_url" alt="">
												</div>
												<div class="bpa-front-si__card-body">
													<h5 class="bpa-front-si__card-body--heading">{{ service_details.bookingpress_service_name }}</h5>
													<!-- <p class="--bpa-is-desc" v-if="service_details.display_details_less == 1 && display_service_description == 1 && service_details.display_read_more_less == 1">{{service_details.bookingpress_service_description_with_excerpt}} 
														<el-link class="bpa-front-read-more-link" @click="Change_front_appointment_description(service_details.bookingpress_service_id)" type="info" >read more</el-link>
													</p>
													<p class="--bpa-is-desc" v-if="service_details.display_details_more == 1 && display_service_description == 1 && service_details.display_read_more_less == 1"> {{service_details.bookingpress_service_description}} 							
														<el-link class="bpa-front-read-more-link" @click="Change_front_appointment_description(service_details.bookingpress_service_id)" type="info">read less</el-link>
													</p> -->
													<p class="--bpa-is-desc" v-if="display_service_description == 1"> {{service_details.bookingpress_service_description}} </p> 
													<div class="bpa-front-si-cb__specs">
														<div class="bpa-front-si-cb__specs-item">
															<p><?php esc_html_e( 'Duration', 'bookingpress-appointment-booking' ); ?>: <strong>{{ service_details.bookingpress_service_duration_val }}{{ service_details.bookingpress_service_duration_unit }}</strong></p>
														</div>
														<div class="bpa-front-si-cb__specs-item" v-if="service_details.service_price_without_currency != 0">
															<p><?php esc_html_e( 'Price', 'bookingpress-appointment-booking' ); ?>: <strong class="--is-service-price">{{ service_details.bookingpress_service_price }}</strong></p>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</el-col>
					</el-row>
				</div>
				<div class="bpa-front-dc--footer" v-if="hide_next_previous_btns != '1'">
					<el-row>
						<el-col>
							<div class="bpa-front-tabs--foot">
								<el-button class="bpa-front-btn bpa-front-btn--primary" @click="next_page('appointment_step_form_data', 1)">
									<?php echo esc_html( $bookingpress_next_btn_text ); ?>: <strong class=""><?php echo esc_html( $bookingpress_second_tab_name ); ?></strong>
									<span class="material-icons-round">east</span>
								</el-button>
							</div>
						</el-col>
					</el-row>
				</div>
			</div>
		</div><!--end of tab one-->

		<div class="bpa-front-tabs--panel-body" :class="[current_selected_tab_id == '2' ? ' __bpa-is-active' : '', current_selected_tab_id < previous_selected_tab_id ? ' __is-previous' : '']" data-id="2">			
			<div class="bpa-front-default-card">	
				<div class="bpa-front-toast-notification --bpa-error" v-if="is_display_error == '1'">
					<div class="bpa-front-tn-body">
						<p>{{ is_error_msg }}</p>
						<!--<a href="#" class="close-icon"><span class="material-icons-round">close</span></a>-->
					</div>
				</div>			
				<div class="bpa-front-dc--body">
					<el-row>
						<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
							<div class="bpa-front-module-container bpa-front-module--date-and-time">
								<el-row>
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<h2 class="bpa-front-module-heading"><?php echo esc_html( $bookingpress_second_tab_name ); ?></h2>
									</el-col>
								</el-row>
								<div class="bpa-front--dt__wrapper">
									<div class="bpa-front--dt__col">										
										<div class="bpa-front--dt__calendar">
											<vue-cal small v-model="appointment_step_form_data.selected_date" :disable-views="['years', 'year', 'week', 'day']" :time="false" :dblclick-to-navigate="false" active-view="month" @cell-click="get_date_timings($event)" :min-date="jsCurrentDate" :selected-date="appointment_step_form_data.selected_date" :disable-days="days_off_disabled_dates" :max-date="booking_cal_maxdate" :locale="site_locale"/>
										</div>
									</div>
									<div class="bpa-front--dt__col">										
										<div class="bpa-front--dt__time-slots">
											<div class="bpa-front-loader-container" v-if="isLoadTimeLoader == '1'">
												<div class="bpa-front-loader"></div>
											</div>
											<div class="bpa-front--dt__ts-heading">
												<h4><?php echo esc_html( $bookingpress_timeslot_title ); ?></h4>
											</div>
											<div class="bpa-front--dt__ts-body" v-show="isLoadTimeLoader == '0'">
												<div class="bpa-front--dt__ts-body--row" v-if="service_timing.morning_time != ''">
													<h5><?php echo $bookingpress_morning_text; ?></h5>
													<div class="bpa-front--dt__ts-body--items">
														<div class="bpa-front--dt__ts-body--item" :class="(time_details.is_booked ? '__bpa-is-disabled' : (appointment_step_form_data.selected_start_time == time_details.start_time) ? '__bpa-is-selected' : '')" v-for="time_details in service_timing.morning_time">
															<span @click="selectTiming(time_details.start_time, time_details.end_time)" v-if="!time_details.is_booked">
																{{ time_details.formatted_start_time }} to {{ time_details.formatted_end_time }}
															</span>
															<span v-else>
																{{ time_details.formatted_start_time }} to {{ time_details.formatted_end_time }}
															</span>
														</div>
													</div>
												</div>
												<div class="bpa-front--dt__ts-body--row" v-if="service_timing.afternoon_time != ''">
													<h5><?php echo $bookingpress_afternoon_text; ?></h5>
													<div class="bpa-front--dt__ts-body--items">
														<div class="bpa-front--dt__ts-body--item" :class="(time_details.is_booked ? '__bpa-is-disabled' : (appointment_step_form_data.selected_start_time == time_details.start_time) ? '__bpa-is-selected' : '')" v-for="time_details in service_timing.afternoon_time">
															<span @click="selectTiming(time_details.start_time, time_details.end_time)" v-if="!time_details.is_booked">
																{{ time_details.formatted_start_time }} to {{ time_details.formatted_end_time }}
															</span>
															<span v-else>
																{{ time_details.formatted_start_time }} to {{ time_details.formatted_end_time }}
															</span>
														</div>
													</div>
												</div>
												<div class="bpa-front--dt__ts-body--row" v-if="service_timing.evening_time != ''">
													<h5><?php echo $bookingpress_evening_text; ?></h5>
													<div class="bpa-front--dt__ts-body--items">
														<div class="bpa-front--dt__ts-body--item" :class="(time_details.is_booked ? '__bpa-is-disabled' : (appointment_step_form_data.selected_start_time == time_details.start_time) ? '__bpa-is-selected' : '')" v-for="time_details in service_timing.evening_time">
															<span @click="selectTiming(time_details.start_time, time_details.end_time)" v-if="!time_details.is_booked">
																{{ time_details.formatted_start_time }} to {{ time_details.formatted_end_time }}
															</span>
															<span v-else>
																{{ time_details.formatted_start_time }} to {{ time_details.formatted_end_time }}
															</span>
														</div>
													</div>
												</div>
												<div class="bpa-front--dt__ts-body--row" v-if="service_timing.night_time != ''">
													<h5><?php echo $bookingpress_night_text; ?></h5>
													<div class="bpa-front--dt__ts-body--items">
														<div class="bpa-front--dt__ts-body--item" :class="(time_details.is_booked ? '__bpa-is-disabled' : (appointment_step_form_data.selected_start_time == time_details.start_time) ? '__bpa-is-selected' : '')" v-for="time_details in service_timing.night_time">
															<span @click="selectTiming(time_details.start_time, time_details.end_time)" v-if="!time_details.is_booked">
																{{ time_details.formatted_start_time }} to {{ time_details.formatted_end_time }}
															</span>
															<span v-else>
																{{ time_details.formatted_start_time }} to {{ time_details.formatted_end_time }}
															</span>
														</div>
													</div>
												</div>
											</div>
										</div>									
									</div>	
								</div>
							</div>
						</el-col>
					</el-row>



					<?php /* Calendar Responsive Div */ ?>
					<el-row>
						<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
							<div class="bpa-front-module-container bpa-front-module--date-and-time __sm">
								<el-row>
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<h2 class="bpa-front-module-heading"><?php echo esc_html( $bookingpress_second_tab_name ); ?></h2>
									</el-col>
								</el-row>
								<el-row :gutter="40" type="flex">
									<el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12" v-if="displayResponsiveCalendar == '1'">
										<div class="bpa-front--dt__calendar">
											<vue-cal small v-model="appointment_step_form_data.selected_date" :disable-views="['years', 'year', 'week', 'day']" :time="false" :dblclick-to-navigate="false" active-view="month" @cell-click="get_date_timings($event)" :min-date="jsCurrentDate" :disable-days="days_off_disabled_dates" :locale="site_locale"/>
										</div>
									</el-col>
									<el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12" v-if="displayResponsiveCalendar == '0'">
										<div class="bpa-front--dt__time-slots">
											<div class="bpa-front-loader-container" v-if="isLoadTimeLoader == '1'">
												<div class="bpa-front-loader"></div>
											</div>
											<div class="bpa-front--dt__ts-sm-back-btn">
												<el-button class="bpa-front-btn" @click="displayCalendar">
													<span class="material-icons-round">west</span>
													<label> {{ appointment_step_form_data.selected_date }} </label>
												</el-button>
											</div>
											<div class="bpa-front--dt__ts-heading">												
												<h4><?php echo esc_html( $bookingpress_timeslot_title ); ?></h4>
											</div>
											<div class="bpa-front--dt__ts-body">
												<div class="bpa-front--dt__ts-body--row" v-if="service_timing.morning_time != ''">
													<h5><?php echo $bookingpress_morning_text; ?></h5>
													<div class="bpa-front--dt__ts-body--items">
														<div class="bpa-front--dt__ts-body--item" :class="(time_details.is_booked ? '__bpa-is-disabled' : (appointment_step_form_data.selected_start_time == time_details.start_time) ? '__bpa-is-selected' : '')" v-for="time_details in service_timing.morning_time" @click="selectTiming(time_details.start_time, time_details.end_time)">
															<span>{{ time_details.start_time }} to {{ time_details.end_time }}</span>
														</div>
													</div>
												</div>
												<div class="bpa-front--dt__ts-body--row" v-if="service_timing.afternoon_time != ''">
													<h5><?php echo $bookingpress_afternoon_text; ?></h5>
													<div class="bpa-front--dt__ts-body--items">
														<div class="bpa-front--dt__ts-body--item" :class="(appointment_step_form_data.selected_start_time == time_details.start_time) ? '__bpa-is-selected' : ''" v-for="time_details in service_timing.afternoon_time" @click="selectTiming(time_details.start_time, time_details.end_time)">
															<span>{{ time_details.start_time }} to {{ time_details.end_time }}</span>
														</div>
													</div>
												</div>
												<div class="bpa-front--dt__ts-body--row" v-if="service_timing.evening_time != ''">
													<h5><?php echo $bookingpress_evening_text; ?></h5>
													<div class="bpa-front--dt__ts-body--items">
														<div class="bpa-front--dt__ts-body--item" :class="(appointment_step_form_data.selected_start_time == time_details.start_time) ? '__bpa-is-selected' : ''" v-for="time_details in service_timing.evening_time" @click="selectTiming(time_details.start_time, time_details.end_time)">
															<span>{{ time_details.start_time }} to {{ time_details.end_time }}</span>
														</div>
													</div>
												</div>
												<div class="bpa-front--dt__ts-body--row" v-if="service_timing.night_time != ''">
													<h5><?php echo $bookingpress_night_text; ?></h5>
													<div class="bpa-front--dt__ts-body--items">
														<div class="bpa-front--dt__ts-body--item" :class="(appointment_step_form_data.selected_start_time == time_details.start_time) ? '__bpa-is-selected' : ''" v-for="time_details in service_timing.night_time" @click="selectTiming(time_details.start_time, time_details.end_time)">
															<span>{{ time_details.start_time }} to {{ time_details.end_time }}</span>
														</div>
													</div>
												</div>
											</div>
										</div>
									</el-col>
								</el-row>
							</div>
						</el-col>
					</el-row>
					
				</div>
				<div class="bpa-front-dc--footer" v-if="hide_next_previous_btns != '1'">
					<el-row>
						<el-col>
							<div class="bpa-front-tabs--foot">
								<el-button class="bpa-front-btn bpa-front-btn--borderless" @click="previous_page" v-if="hide_category_service != '1'">
									<span class="material-icons-round">west</span>
									<?php echo esc_html( $bookingpress_goback_btn_text ); ?>
								</el-button>
								<el-button class="bpa-front-btn bpa-front-btn--primary" @click="next_page('appointment_step_form_data', 2)">
									<?php echo esc_html( $bookingpress_next_btn_text ); ?>: <strong class=""><?php echo esc_html( $bookingpress_third_tab_name ); ?></strong>
									<span class="material-icons-round">east</span>
								</el-button>
							</div>
						</el-col>
					</el-row>
				</div>
			</div>
		</div><!--end of tab two--> 

		<div class="bpa-front-tabs--panel-body" :class="[current_selected_tab_id == '3' ? ' __bpa-is-active' : '', current_selected_tab_id < previous_selected_tab_id ? ' __is-previous' : '']" data-id="3">
			<div class="bpa-front-default-card">
				<div class="bpa-front-toast-notification --bpa-error" v-if="is_display_error == '1'">
					<div class="bpa-front-tn-body">
						<p>{{ is_error_msg }}</p>
						<!--<a href="#" class="bpa-close-icon"><span class="material-icons-round">close</span></a>-->
					</div>
				</div>
				<div class="bpa-front-dc--body">
					<el-row>
						<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
							<div class="bpa-front-module-container bpa-front-module--basic-details">								
								<el-row>
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<h2 class="bpa-front-module-heading"><?php echo esc_html( $bookingpress_third_tab_name ); ?></h2>
									</el-col>
								</el-row>
								<el-row>
									<el-form :model="appointment_step_form_data" :rules="customer_details_rule" ref="appointment_step_form_data">
										<el-col>
											<div class="bpa-front-module--bd-form">
												<el-row :gutter="24">
													<el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" v-for="customer_form_fields_data in customer_form_fields" v-if="customer_form_fields_data.field_name != 'note' && customer_form_fields_data.is_hide != '1'">
														<el-form-item :prop="customer_form_fields_data.v_model_value" :ref="customer_form_fields_data.v_model_value" v-if="customer_form_fields_data.field_name != 'phone_number' && customer_form_fields_data.is_hide != '1'">
															<template #label>
																<span class="bpa-front-form-label">{{ customer_form_fields_data.label }}</span>		
															</template>
															<el-input v-model="appointment_step_form_data[customer_form_fields_data['v_model_value']]" class="bpa-front-form-control" :placeholder="customer_form_fields_data.placeholder" v-if="customer_form_fields_data.field_name != 'email_address'"></el-input>

															<el-input type="email" v-model="appointment_step_form_data[customer_form_fields_data['v_model_value']]" class="bpa-front-form-control" :placeholder="customer_form_fields_data.placeholder" v-else></el-input>
														</el-form-item>
														<el-form-item prop="customer_phone" ref="customer_phone" v-if="customer_form_fields_data.field_name == 'phone_number' && customer_form_fields_data.is_hide != '1'">
															<template #label>
																<span class="bpa-front-form-label">{{ customer_form_fields_data.label }}</span>		
															</template>
															<vue-tel-input v-model="appointment_step_form_data.customer_phone" class="bpa-front-form-control --bpa-country-dropdown" @country-changed="bookingpress_phone_country_change_func($event)" v-bind="bookingpress_tel_input_props" ref="bpa_tel_input_field">
																<template v-slot:arrow-icon>
																	<span class="material-icons-round">keyboard_arrow_down</span>
																</template>
															</vue-tel-input>
															<?php /*
															<el-input v-model="appointment_step_form_data.customer_phone" class="bpa-front-form-control" :placeholder="customer_form_fields_data.placeholder">
															<el-select slot="prepend" class="bpa-form-control__country-dropdown" v-model="appointment_step_form_data.customer_phone_country" :popper-append-to-body="false" popper-class="bpa-front-dropdown--country-selection">
																<el-option v-for="countries in phone_countries_details" :value="countries.code" :label="countries.code"><span class="flag" :class="countries.code"></span> {{ countries.name }}</el-option>
																</el-select>
															</el-input>	 */ ?>
														</el-form-item>
													</el-col>
												</el-row>

												<el-row :gutter="24">
													<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-for="customer_form_fields_data in customer_form_fields" v-if="customer_form_fields_data.field_name == 'note' && customer_form_fields_data.is_hide != '1'">
														<el-form-item :prop="customer_form_fields_data.field_name" :ref="customer_form_fields_data.field_name">
															<template #label>
																<span class="bpa-front-form-label">{{ customer_form_fields_data.label }}</span>		
															</template>
															<el-input type="textarea" v-model="appointment_step_form_data[customer_form_fields_data['v_model_value']]" class="bpa-front-form-control" :placeholder="customer_form_fields_data.placeholder"></el-input>
														</el-form-item>
													</el-col>
												</el-row>
											</div>
										</el-col>
									</el-form>
								</el-row>
							</div>
						</el-col>
					</el-row>
				</div>
				<el-row>
					<el-col>
						<div class="bpa-front-tabs--foot" v-show="hide_next_previous_btns != '1'">
							<el-button class="bpa-front-btn bpa-front-btn--borderless" @click="previous_page">
								<span class="material-icons-round">west</span>
								<?php echo esc_html( $bookingpress_goback_btn_text ); ?>
							</el-button>

							<el-button class="bpa-front-btn bpa-front-btn--primary" @click="next_page('appointment_step_form_data', 3)" ref="validteBtn">
								<?php echo esc_html( $bookingpress_next_btn_text ); ?>: <strong class=""><?php echo esc_html( $bookingpress_fourth_tab_name ); ?></strong>
								<span class="material-icons-round">east</span>
							</el-button>
						</div>
					</el-col>
				</el-row>
			</div>
		</div>
		
		<div class="bpa-front-tabs--panel-body" :class="[current_selected_tab_id == '4' ? ' __bpa-is-active' : '', current_selected_tab_id < previous_selected_tab_id ? ' __is-previous' : '']" data-id="4">
			<div class="bpa-front-default-card">
				<div class="bpa-front-toast-notification --bpa-error" v-if="is_display_error == '1'">
					<div class="bpa-front-tn-body">
						<p>{{ is_error_msg }}</p>
						<!--<a href="#" class="close-icon"><span class="material-icons-round">close</span></a>-->
					</div>
				</div>
				<div class="bpa-front-dc--body">
					<el-row>
						<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
							<div class="bpa-front-module-container bpa-front-module--booking-summary">
								<div class="bpa-front-module--bs-head">
									<img src="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/front-summary-vector.svg' ); ?>" alt="">
									<h4><?php echo esc_html( $bookingpress_fourth_tab_name ); ?></h4>
									<p><?php echo esc_html( $bookingpress_summary_content_text ); ?></p>
								</div>
								<div class="bpa-front-module--bs-summary-content">
									<div class="bpa-front-module--bs-summary-content-item">
										<span><?php esc_html_e( 'Service', 'bookingpress-appointment-booking' ); ?>:</span>
										<h4>{{ appointment_step_form_data.selected_service_name }}</h4>
									</div>
									<div class="bpa-front-module--bs-summary-content-item">
										<span><?php esc_html_e( 'Date &amp; Time', 'bookingpress-appointment-booking' ); ?>:</span>
										<h4>{{ appointment_step_form_data.selected_date | bookingpress_format_date }}, {{ appointment_step_form_data.selected_start_time | bookingpress_format_time }} to {{ appointment_step_form_data.selected_end_time | bookingpress_format_time }}</h4>
									</div>
									<div class="bpa-front-module--bs-summary-content-item">
										<span><?php esc_html_e( 'Customer Name', 'bookingpress-appointment-booking' ); ?>:</span>
										<h4 v-if="appointment_step_form_data.customer_name != ''">{{ appointment_step_form_data.customer_name }}</h4>
										<h4 v-else-if="appointment_step_form_data.customer_firstname != '' || appointment_step_form_data.customer_lastname != ''">{{ appointment_step_form_data.customer_firstname }} {{ appointment_step_form_data.customer_lastname }}</h4>
										<h4 v-else>{{ appointment_step_form_data.customer_email }}</h4>
									</div>
								</div>
								<div class="bpa-front-module--bs-amount-details" v-if="appointment_step_form_data.service_price_without_currency != '0'">
									<div class="bpa-fm--bs-amount-item">
										<h4><?php esc_html_e( 'Total Amount Payable', 'bookingpress-appointment-booking' ); ?></h4>
										<h4 class="bpa-front-module--bs-ad--price">{{ appointment_step_form_data.selected_service_price }}</h4>
									</div>
								</div>
							</div>
						</el-col>
					</el-row>
					<el-row v-if="appointment_step_form_data.service_price_without_currency != '0'">
						<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
							<div class="bpa-front-module-container bpa-front-module--payment-methods">
								<div class="bpa-front-module--pm-head">
									<div class="__bpa-pm-icon">
										<span class="material-icons-round">payments</span>
									</div>
									<div class="__bpa-pm-content">
										<h4><?php echo esc_html( $bookingpress_payment_method_text ); ?></h4>
									</div>
								</div>
								<div class="bpa-front-module--pm-body">
									<div class="bpa-front-module--pm-body__item" :class="(appointment_step_form_data.selected_payment_method == 'on-site') ? '__bpa-is-selected' : ''" @click="select_payment_method('on-site')" v-if="on_site_payment != 'false' && on_site_payment != ''">
										<div class="bpa-front-si-card--checkmark-icon" v-if="appointment_step_form_data.selected_payment_method == 'on-site'">
											<span class="material-icons-round">check_circle</span>
										</div>
										<span class="material-icons-round">storefront</span>
										<p><?php esc_html_e( 'Pay Locally', 'bookingpress-appointment-booking' ); ?></p>
									</div>
									<div class="bpa-front-module--pm-body__item" :class="(appointment_step_form_data.selected_payment_method == 'paypal') ? '__bpa-is-selected' : ''" @click="select_payment_method('paypal')" v-if="paypal_payment != 'false' && paypal_payment != ''">
										<div class="bpa-front-si-card--checkmark-icon" v-if="appointment_step_form_data.selected_payment_method == 'paypal'">
											<span class="material-icons-round">check_circle</span>
										</div>
										<span><img src="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/front-paypal-symbol.svg' ); ?>" alt=""></span>
										<p><?php esc_html_e( 'PayPal', 'bookingpress-appointment-booking' ); ?></p>
									</div>
									<?php
										do_action('bpa_front_add_payment_gateway');
									?>
								</div>
							</div>
						</el-col>
					</el-row>
				</div>
				<div class="bpa-front-dc--footer">
					<el-row>
						<el-col>
							<div class="bpa-front-tabs--foot">
								<el-button class="bpa-front-btn bpa-front-btn--borderless" @click="previous_page" v-if="hide_next_previous_btns != '1'">
									<span class="material-icons-round">west</span>
									<?php echo esc_html( $bookingpress_goback_btn_text ); ?>
								</el-button>
								<el-button class="bpa-front-btn bpa-front-btn--primary" :class="(isLoadBookingLoader == '1') ? 'bpa-front-btn--is-loader' : ''" @click="book_appointment" :disabled="isBookingDisabled">									
									<span class="bpa-btn__label"><?php echo esc_html( $bookingpress_book_appointment_btn_text ); ?></span>									
								    <div class="bpa-front-btn--loader__circles">				    
									  <div></div>
									  <div></div>
									  <div></div>
								 	</div>									
								</el-button>
							</div>
						</el-col>
					</el-row>
				</div>
			</div>
		</div>
	</div>
	<el-row type="flex" v-if="service_categories == ''">
		<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
			<div class="bpa-data-empty-view">
				<div class="bpa-ev-left-vector">
					<picture>
						<source srcset="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.webp' ); ?>" type="image/webp">
						<img src="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.png' ); ?>">
					</picture>
				</div>
				<div class="bpa-ev-right-content">					
					<h4><?php esc_html_e( 'No categories and services added!', 'bookingpress-appointment-booking' ); ?></h4>
				</div>
			</div>
		</el-col>
	</el-row>
</el-main>
