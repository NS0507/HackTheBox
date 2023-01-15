<?php
	global $BookingPress, $bookingpress_common_date_format;
	$bookingpress_price          = $BookingPress->bookingpress_price_formatter_with_currency_symbol( 1000 );
	$bookingpress_service_price1 = $BookingPress->bookingpress_price_formatter_with_currency_symbol( 350 );
	$bookingpress_service_price2 = $BookingPress->bookingpress_price_formatter_with_currency_symbol( 150 );
?>
<link rel="stylesheet" :href="'https://fonts.googleapis.com/css?family='+selected_font_values.title_font_family">
<link rel="stylesheet" :href="'https://fonts.googleapis.com/css?family='+selected_font_values.content_font_family">
<link rel="stylesheet" :href="'https://fonts.googleapis.com/css?family='+my_booking_selected_font_values.title_font_family">
<link rel="stylesheet" :href="'https://fonts.googleapis.com/css?family='+my_booking_selected_font_values.content_font_family">

<el-main class="bpa-main-listing-card-container bpa-default-card" id="all-page-main-container">
	<el-row type="flex" class="bpa-mlc-head-wrap">
		<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-mlc-left-heading">
			<h1 class="bpa-page-heading"><?php esc_html_e( 'Customize', 'bookingpress-appointment-booking' ); ?></h1>
		</el-col>
		<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
			<div class="bpa-hw-right-btn-group">
				<el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="bpa_save_customize_settings" :disabled="is_disabled" >					
				  <span class="bpa-btn__label"><?php esc_html_e( 'Save Changes', 'bookingpress-appointment-booking' ); ?></span>
				  <div class="bpa-btn--loader__circles">				    
					  <div></div>
					  <div></div>
					  <div></div>
				  </div>
				</el-button>
				<el-button class="bpa-btn" @click="openNeedHelper('list_customize', 'customize', '<?php esc_html_e('Customize', 'bookingpress-appointment-booking'); ?>')">
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
	<el-container class="bpa-customize-main-container" id="bpa-main-container">
		<div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
			<div class="bpa-back-loader"></div>
		</div>
		<div class="bpa-customize-body-wrapper">
			<el-tabs class="bpa-tabs bpa-tabs--customize-module" v-model="activeTabName">
				<el-tab-pane name="booking_form">
					<template #label>
						<span><?php esc_html_e( 'Booking Form', 'bookingpress-appointment-booking' ); ?></span>
					</template>
					<div class="bpa-customize-step-content-container __bpa-is-sidebar">
						<el-row type="flex">
							<el-col :xs="24" :sm="24" :md="24" :lg="4" :xl="4">
								<div class="bpa-customize-step-side-panel">
									<div class="bpa-cs-sp--heading">
										<h4><?php esc_html_e( 'Form Styling', 'bookingpress-appointment-booking' ); ?></h4>
										<el-popconfirm 
											confirm-button-text='<?php esc_html_e( 'Yes', 'bookingpress-appointment-booking' ); ?>' 
											cancel-button-text='<?php esc_html_e( 'No', 'bookingpress-appointment-booking' ); ?>' 
											icon="false"
											@confirm="bpa_reset_bookingform()" 
											title="<?php esc_html_e( 'Are you sure you want to reset the settings?', 'bookingpress-appointment-booking' ); ?>" @confirm="delete_breakhour(break_data.start_time, break_data.end_time, work_hours_day.day_name)" 
											confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
											cancel-button-type="bpa-btn bpa-btn__small">
												<el-button class="bpa-btn bpa-btn__small" slot="reference">
													<?php esc_html_e( 'Reset', 'bookingpress-appointment-booking' ); ?>
												</el-button>
										</el-popconfirm>
									</div>
									<div class="bpa-cs-sp-sub-module">
										<h5><?php esc_html_e( 'Color Settings', 'bookingpress-appointment-booking' ); ?></h5>
										<div class="bpa-sm--item --bpa-is-flexbox">
											<label class="bpa-form-label"><?php esc_html_e( 'Background Color', 'bookingpress-appointment-booking' ); ?></label>
											<el-color-picker v-model="selected_colorpicker_values.background_color"></el-color-picker>
										</div>
										<div class="bpa-sm--item --bpa-is-flexbox">
											<label class="bpa-form-label"><?php esc_html_e( 'Footer Background', 'bookingpress-appointment-booking' ); ?></label>
											<el-color-picker v-model="selected_colorpicker_values.footer_background_color"></el-color-picker>
										</div>
										<div class="bpa-sm--item --bpa-is-flexbox">
											<label class="bpa-form-label"><?php esc_html_e( 'Primary Color', 'bookingpress-appointment-booking' ); ?></label>
											<el-color-picker v-model="selected_colorpicker_values.primary_color" @change="bpa_select_primary_color"></el-color-picker>
										</div>
										<div class="bpa-sm--item --bpa-is-flexbox">
											<label class="bpa-form-label"><?php esc_html_e( 'Title Color', 'bookingpress-appointment-booking' ); ?></label>
											<el-color-picker v-model="selected_colorpicker_values.label_title_color"></el-color-picker>
										</div>
										<div class="bpa-sm--item --bpa-is-flexbox">
											<label class="bpa-form-label"><?php esc_html_e( 'Content Color', 'bookingpress-appointment-booking' ); ?></label>
											<el-color-picker v-model="selected_colorpicker_values.content_color"></el-color-picker>
										</div>
										<div class="bpa-sm--item --bpa-is-flexbox">
											<label class="bpa-form-label"><?php esc_html_e( 'Price &amp; Button text color', 'bookingpress-appointment-booking' ); ?></label>
											<el-color-picker v-model="selected_colorpicker_values.price_button_text_color"></el-color-picker>
										</div>
									</div>
									<div class="bpa-cs-sp-sub-module bpa-cs-sp--form-controls">
										<h5><?php esc_html_e( 'Font Settings', 'bookingpress-appointment-booking' ); ?></h5>
										<div class="bpa-sm--item">
											<label class="bpa-form-label"><?php esc_html_e( 'Title Size', 'bookingpress-appointment-booking' ); ?></label>
											<el-select v-model="selected_font_values.title_font_size" class="bpa-form-control" popper-class="bpa-el-select--is-with-navbar">
												<el-option label="<?php esc_html( '10' ); ?>" value="10"></el-option>
												<el-option label="<?php esc_html( '11' ); ?>" value="11"></el-option>
												<el-option label="<?php esc_html( '12' ); ?>" value="12"></el-option>
												<el-option label="<?php esc_html( '13' ); ?>" value="13"></el-option>
												<el-option label="<?php esc_html( '14' ); ?>" value="14"></el-option>
												<el-option label="<?php esc_html( '16' ); ?>" value="16"></el-option>
												<el-option label="<?php esc_html( '18' ); ?>" value="18"></el-option>
												<el-option label="<?php esc_html( '20' ); ?>" value="20"></el-option>
												<el-option label="<?php esc_html( '22' ); ?>" value="22"></el-option>
												<el-option label="<?php esc_html( '24' ); ?>" value="24"></el-option>
												<el-option label="<?php esc_html( '26' ); ?>" value="26"></el-option>
											</el-select>
										</div>
										<div class="bpa-sm--item">
											<label class="bpa-form-label"><?php esc_html_e( 'Title Font Family', 'bookingpress-appointment-booking' ); ?></label>
											<el-select v-model="selected_font_values.title_font_family" class="bpa-form-control" popper-class="bpa-el-select--is-with-navbar" filterable>
												<el-option-group v-for="item_data in fonts_list" :key="item_data.label" :label="item_data.label">
													<el-option v-for="item in item_data.options" :key="item" :label="item" :value="item"></el-option>
												</el-option-group>
											</el-select>
										</div>
										<div class="bpa-sm--item">
											<label class="bpa-form-label"><?php esc_html_e( 'Content Size', 'bookingpress-appointment-booking' ); ?></label>
											<el-select v-model="selected_font_values.content_font_size" class="bpa-form-control"
												popper-class="bpa-el-select--is-with-navbar">
												<el-option label="<?php esc_html( '10' ); ?>" value="10"></el-option>
												<el-option label="<?php esc_html( '11' ); ?>" value="11"></el-option>
												<el-option label="<?php esc_html( '12' ); ?>" value="12"></el-option>
												<el-option label="<?php esc_html( '13' ); ?>" value="13"></el-option>
												<el-option label="<?php esc_html( '14' ); ?>" value="14"></el-option>
												<el-option label="<?php esc_html( '16' ); ?>" value="16"></el-option>
												<el-option label="<?php esc_html( '18' ); ?>" value="18"></el-option>
												<el-option label="<?php esc_html( '20' ); ?>" value="20"></el-option>
												<el-option label="<?php esc_html( '22' ); ?>" value="22"></el-option>
												<el-option label="<?php esc_html( '24' ); ?>" value="24"></el-option>
												<el-option label="<?php esc_html( '26' ); ?>" value="26"></el-option>
											</el-select>
										</div>
										<div class="bpa-sm--item">
											<label class="bpa-form-label"><?php esc_html_e( 'Content Font Family', 'bookingpress-appointment-booking' ); ?></label>
											<el-select v-model="selected_font_values.content_font_family" class="bpa-form-control" popper-class="bpa-el-select--is-with-navbar" filterable>
												<el-option-group v-for="item_data in fonts_list" :key="item_data.label" :label="item_data.label">
													<el-option v-for="item in item_data.options" :key="item" :label="item" :value="item"></el-option>
												</el-option-group>
											</el-select>
										</div>
										<div class="bpa-sm--item">
											<label class="bpa-form-label"><?php esc_html_e( 'Custom CSS', 'bookingpress-appointment-booking' ); ?></label>
											<el-input type="textarea" :rows="4" class="bpa-form-control" v-model="selected_colorpicker_values.custom_css"/>
										</div>
									</div>
								</div>
							</el-col>
							<el-col :xs="24" :sm="24" :md="24" :lg="16" :xl="16">
								<div class="bpa-customize-booking-form-preview-container">
									<el-tabs class="bpa-tabs bpa-cbf--tabs" v-model="formActiveTab">
										<el-tab-pane name="1">
											<template #label>
												<span :class="formActiveTab == '1' ? 'bpa_center_container_tab_title' : ''" :style="[ formActiveTab == '1' ? { 'background': selected_colorpicker_values.primary_color } : '' ]"><?php esc_html_e( '01', 'bookingpress-appointment-booking' ); ?></span>
											</template>
											<div class="bpa-cbf--preview-step" :style="{ 'background': selected_colorpicker_values.background_color }">
												<div class="bpa-cbf--preview-step__body-content">
													<div class="bpa-cbf--preview-step--tab-menu">
														<div class="bpa-cbf--ps--tm__item">
															<span class="bpa-form-label" v-text="tab_container_data.service_title"></span>
															<el-popover placement="bottom" v-model="tab_container_data.is_edit_service">
																<el-container class="bpa-customize--edit-label-popover">
																	<div>
																		<el-input class="bpa-form-control" v-model="tab_container_data.service_title"></el-input>
																	</div>
																	<div class="bpa-customize--edit-label-popover--actions">
																		<el-button class="bpa-btn bpa-btn__small" @click="tab_container_data.is_edit_service = false"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
																		<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="tab_container_data.is_edit_service = false"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
																	</div>
																</el-container>
																<el-button class="bpa-btn bpa-btn__small bpa-btn--icon-without-box" slot="reference">
																	<i class="material-icons-round">mode_edit</i>
																</el-button>
															</el-popover>
														</div>
														<div class="bpa-cbf--ps--tm__item">
															<span class="bpa-form-label" v-text="tab_container_data.datetime_title"></span>
															<el-popover placement="bottom" v-model="tab_container_data.is_edit_date_time">
																<el-container class="bpa-customize--edit-label-popover">
																	<div>
																		<el-input class="bpa-form-control" v-model="tab_container_data.datetime_title"></el-input>
																	</div>
																	<div class="bpa-customize--edit-label-popover--actions">
																		<el-button class="bpa-btn bpa-btn__small" @click="tab_container_data.is_edit_date_time = false"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
																		<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="tab_container_data.is_edit_date_time = false"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
																	</div>
																</el-container>
																<el-button class="bpa-btn bpa-btn__small bpa-btn--icon-without-box" slot="reference">
																	<i class="material-icons-round">mode_edit</i>
																</el-button>
															</el-popover>
														</div>
														<div class="bpa-cbf--ps--tm__item">
															<span class="bpa-form-label" v-text="tab_container_data.basic_details_title"></span>
															<el-popover placement="bottom" v-model="tab_container_data.is_edit_basic_details">
																<el-container class="bpa-customize--edit-label-popover">
																	<div>
																		<el-input class="bpa-form-control" v-model="tab_container_data.basic_details_title"></el-input>
																	</div>
																	<div class="bpa-customize--edit-label-popover--actions">
																		<el-button class="bpa-btn bpa-btn__small" @click="tab_container_data.is_edit_basic_details = false"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
																		<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="tab_container_data.is_edit_basic_details = false"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
																	</div>
																</el-container>
																<el-button class="bpa-btn bpa-btn__small bpa-btn--icon-without-box" slot="reference">
																	<i class="material-icons-round">mode_edit</i>
																</el-button>
															</el-popover>
														</div>
														<div class="bpa-cbf--ps--tm__item">
															<span class="bpa-form-label" v-text="tab_container_data.summary_title"></span>
															<el-popover placement="bottom" v-model="tab_container_data.is_edit_summary">
																<el-container class="bpa-customize--edit-label-popover">
																	<div>
																		<el-input class="bpa-form-control" v-model="tab_container_data.summary_title"></el-input>
																	</div>
																	<div class="bpa-customize--edit-label-popover--actions">
																		<el-button class="bpa-btn bpa-btn__small" @click="tab_container_data.is_edit_summary = false"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
																		<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="tab_container_data.is_edit_summary = false"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
																	</div>
																</el-container>
																<el-button class="bpa-btn bpa-btn__small bpa-btn--icon-without-box" slot="reference">
																	<i class="material-icons-round">mode_edit</i>
																</el-button>
															</el-popover>
														</div>
													</div>
													<div class="bpa-cbf--preview--module-container __category-module">
														<el-row type="flex" class="bpa-cbf--preview__module-heading">
															<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
																<label class="bpa-form-label" v-text="category_container_data.category_title" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family }"></label>
																<el-popover placement="bottom" v-model="category_container_data.editCategoryTitlePopup">
																	<el-container class="bpa-customize--edit-label-popover">
																		<div>
																			<el-input class="bpa-form-control" v-model="category_container_data.category_title"></el-input>
																		</div>
																		<div class="bpa-customize--edit-label-popover--actions">
																			<el-button class="bpa-btn bpa-btn__small" @click="category_container_data.editCategoryTitlePopup = false"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
																			<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="category_container_data.editCategoryTitlePopup = false"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
																		</div>
																	</el-container>
																	<el-button class="bpa-btn bpa-btn__small bpa-btn--icon-without-box" slot="reference">
																		<i class="material-icons-round">mode_edit</i>
																	</el-button>
																</el-popover>
															</el-col>
														</el-row>
														<el-row>															
															<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
																<div class="bpa-front-cat-items-wrapper">
																	<div class="bpa-front-cat-items">
																		<el-tag class="bpa-front-ci-pill" :class="(bookingpress_shortcode_form.selected_category == 'low_consultancy') ? '__bpa-is-active' : ''" @click="bpa_select_category('low_consultancy')" :style="[bookingpress_shortcode_form.selected_category == 'low_consultancy' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : '']">
																			<label :style="[bookingpress_shortcode_form.selected_category == 'low_consultancy' ? { 'color': selected_colorpicker_values.primary_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family } : { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } ]"><?php esc_html_e( 'Cardio', 'bookingpress-appointment-booking' ); ?></label>
																			<i class="material-icons-round" :style="[bookingpress_shortcode_form.selected_category == 'low_consultancy' ? { 'color': selected_colorpicker_values.primary_color } : { 'color': selected_colorpicker_values.content_color }]" v-if="bookingpress_shortcode_form.selected_category == 'low_consultancy'">check_circle</i>
																		</el-tag>
																		<el-tag class="bpa-front-ci-pill" :class="(bookingpress_shortcode_form.selected_category == 'entertainment_2') ? '__bpa-is-active' : ''" @click="bpa_select_category('entertainment_2')" :style="[bookingpress_shortcode_form.selected_category == 'entertainment_2' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : '']">
																			<label :style="[bookingpress_shortcode_form.selected_category == 'entertainment_2' ? { 'color': selected_colorpicker_values.primary_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family } : { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } ]"><?php esc_html_e( 'Yoga', 'bookingpress-appointment-booking' ); ?></label>
																			<i class="material-icons-round" :style="[bookingpress_shortcode_form.selected_category == 'entertainment_2' ? { 'color': selected_colorpicker_values.primary_color } : { 'color': selected_colorpicker_values.content_color }]" v-if="bookingpress_shortcode_form.selected_category == 'entertainment_2'">check_circle</i>
																		</el-tag>
																		<el-tag class="bpa-front-ci-pill" :class="(bookingpress_shortcode_form.selected_category == 'real_estate_2') ? '__bpa-is-active' : ''" @click="bpa_select_category('real_estate_2')" :style="[bookingpress_shortcode_form.selected_category == 'real_estate_2' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : '']">
																			<label :style="[bookingpress_shortcode_form.selected_category == 'real_estate_2' ? { 'color': selected_colorpicker_values.primary_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family } : { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } ]"><?php esc_html_e( 'Combat Sports', 'bookingpress-appointment-booking' ); ?></label>
																			<i class="material-icons-round" :style="[bookingpress_shortcode_form.selected_category == 'real_estate_2' ? { 'color': selected_colorpicker_values.primary_color } : { 'color': selected_colorpicker_values.content_color }]" v-if="bookingpress_shortcode_form.selected_category == 'real_estate_2'">check_circle</i>
																		</el-tag>
																		<el-tag class="bpa-front-ci-pill" :class="(bookingpress_shortcode_form.selected_category == 'low_consultancy_2') ? '__bpa-is-active' : ''" @click="bpa_select_category('low_consultancy_2')" :style="[bookingpress_shortcode_form.selected_category == 'low_consultancy_2' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : '']">
																			<label :style="[bookingpress_shortcode_form.selected_category == 'low_consultancy_2' ? { 'color': selected_colorpicker_values.primary_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family } : { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } ]"><?php esc_html_e( 'Bodybuilding', 'bookingpress-appointment-booking' ); ?></label>
																			<i class="material-icons-round" :style="[bookingpress_shortcode_form.selected_category == 'low_consultancy_2' ? { 'color': selected_colorpicker_values.primary_color } : { 'color': selected_colorpicker_values.content_color }]" v-if="bookingpress_shortcode_form.selected_category == 'low_consultancy_2'">check_circle</i>
																		</el-tag>
																	</div>
																</div>
															</el-col>
														</el-row>
													</div>
													<div class="bpa-cbf--preview--module-container __service-module">
														<el-row type="flex" class="bpa-cbf--preview__module-heading">
															<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
																<label class="bpa-form-label" v-text="service_container_data.service_heading_title" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family }"></label>
																<el-popover placement="bottom" v-model="service_container_data.editServiceTitlePopup">
																	<el-container class="bpa-customize--edit-label-popover">
																		<div>
																			<el-input class="bpa-form-control" v-model="service_container_data.service_heading_title"></el-input>
																		</div>
																		<div class="bpa-customize--edit-label-popover--actions">
																			<el-button class="bpa-btn bpa-btn__small" @click="service_container_data.editServiceTitlePopup = false"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
																			<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="service_container_data.editServiceTitlePopup = false"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
																		</div>
																	</el-container>
																	<el-button class="bpa-btn bpa-btn__small bpa-btn--icon-without-box" slot="reference">
																		<i class="material-icons-round">mode_edit</i>
																	</el-button>
																</el-popover>
															</el-col>
														</el-row>
														<el-row :gutter="32">
															<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
																<div class="bpa-front-module--service-item" :class="(bookingpress_shortcode_form.selected_service == 'chronic_disease_management_1') ? ' __bpa-is-selected' : ''" @click="bpa_select_service('chronic_disease_management_1')">
																	<div class="bpa-front-si-card" :style="[bookingpress_shortcode_form.selected_service == 'chronic_disease_management_1' ? { 'border-color': selected_colorpicker_values.primary_color } : '']">
																		<div class="bpa-front-si-card--checkmark-icon" v-if="bookingpress_shortcode_form.selected_service == 'chronic_disease_management_1'">
																			<span class="material-icons-round" :style="[bookingpress_shortcode_form.selected_service == 'chronic_disease_management_1' ? { 'color': selected_colorpicker_values.primary_color } : { 'color': selected_colorpicker_values.content_color }]">check_circle</span>
																		</div>
																		<div class="bpa-front-si-card__left">
																			<img :src="service_container_data.default_image_url" alt="">
																		</div>
																		<div class="bpa-front-si__card-body">
																			<h5 class="bpa-front-si__card-body--heading" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family }"><?php esc_html_e( 'Chronic Disease Management', 'bookingpress-appointment-booking' ); ?></h5>
																			<div class="bpa-front-si-cb__specs" v-if="booking_form_settings.display_service_description == true">
																				<p :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }" >Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam varius viverra lectus</p>
																			</div><br>
																			<div class="bpa-front-si-cb__specs">
																				<div class="bpa-front-si-cb__specs-item">
																					<p :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }"><?php esc_html_e( 'Duration', 'bookingpress-appointment-booking' ); ?>: <strong :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }">4h</strong></p>
																				</div>
																				<div class="bpa-front-si-cb__specs-item">
																					<p :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }"><?php esc_html_e( 'Price', 'bookingpress-appointment-booking' ); ?>: <strong class="bpa-front-text-primary-color --is-service-price" :style="{ 'background-color': selected_colorpicker_values.primary_color, 'color': selected_colorpicker_values.price_button_text_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }"><?php echo $bookingpress_service_price1; ?></strong></p>
																				</div>
																			</div>
																		</div>
																	</div>
																</div>
															</el-col>
															<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
																<div class="bpa-front-module--service-item" :class="(bookingpress_shortcode_form.selected_service == 'chronic_disease_management_2') ? ' __bpa-is-selected' : ''" @click="bpa_select_service('chronic_disease_management_2')">
																	<div class="bpa-front-si-card" :style="[bookingpress_shortcode_form.selected_service == 'chronic_disease_management_2' ? { 'border-color': selected_colorpicker_values.primary_color } : '']">
																		<div class="bpa-front-si-card--checkmark-icon" v-if="bookingpress_shortcode_form.selected_service == 'chronic_disease_management_2'">
																			<span class="material-icons-round" :style="[bookingpress_shortcode_form.selected_service == 'chronic_disease_management_2' ? { 'color': selected_colorpicker_values.primary_color } : { 'color': selected_colorpicker_values.content_color }]">check_circle</span>
																		</div>
																		<div class="bpa-front-si-card__left">
																			<img :src="service_container_data.default_image_url" alt="">
																		</div>
																		<div class="bpa-front-si__card-body">
																			<h5 class="bpa-front-si__card-body--heading" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family }"><?php esc_html_e( 'Chronic Disease Management', 'bookingpress-appointment-booking' ); ?></h5>
																			<div class="bpa-front-si-cb__specs" v-if="booking_form_settings.display_service_description == true">
																				<p :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }" >Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam varius viverra lectus</p>
																			</div><br>
																			<div class="bpa-front-si-cb__specs">
																				<div class="bpa-front-si-cb__specs-item">
																					<p :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }"><?php esc_html_e( 'Duration', 'bookingpress-appointment-booking' ); ?>: <strong :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }">1h</strong></p>
																				</div>
																				<div class="bpa-front-si-cb__specs-item">
																					<p :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }"><?php esc_html_e( 'Price', 'bookingpress-appointment-booking' ); ?>: <strong class="bpa-front-text-primary-color --is-service-price" :style="{ 'background-color': selected_colorpicker_values.primary_color, 'color': selected_colorpicker_values.price_button_text_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }"><?php echo $bookingpress_service_price2; ?></strong></p>
																				</div>
																			</div>
																		</div>
																	</div>
																</div>
															</el-col>															
															<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
																<div class="bpa-front-module--service-item" :class="(bookingpress_shortcode_form.selected_service == 'chronic_disease_management_3') ? ' __bpa-is-selected' : ''" @click="bpa_select_service('chronic_disease_management_3')">
																	<div class="bpa-front-si-card" :style="[bookingpress_shortcode_form.selected_service == 'chronic_disease_management_3' ? { 'border-color': selected_colorpicker_values.primary_color } : '']">
																		<div class="bpa-front-si-card--checkmark-icon" v-if="bookingpress_shortcode_form.selected_service == 'chronic_disease_management_3'">
																			<span class="material-icons-round" :style="[bookingpress_shortcode_form.selected_service == 'chronic_disease_management_3' ? { 'color': selected_colorpicker_values.primary_color } : { 'color': selected_colorpicker_values.content_color }]">check_circle</span>
																		</div>
																		<div class="bpa-front-si-card__left">
																			<img :src="service_container_data.default_image_url" alt="">
																		</div>
																		<div class="bpa-front-si__card-body">
																			<h5 class="bpa-front-si__card-body--heading" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family }"><?php esc_html_e( 'Chronic Disease Management', 'bookingpress-appointment-booking' ); ?></h5>
																			<div class="bpa-front-si-cb__specs" v-if="booking_form_settings.display_service_description == true">
																				<p :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }" >Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam varius viverra lectus</p>
																			</div><br>
																			<div class="bpa-front-si-cb__specs">
																				<div class="bpa-front-si-cb__specs-item">
																					<p :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }"><?php esc_html_e( 'Duration', 'bookingpress-appointment-booking' ); ?>: <strong :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }">1h</strong></p>
																				</div>
																				<div class="bpa-front-si-cb__specs-item">
																					<p :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }"><?php esc_html_e( 'Price', 'bookingpress-appointment-booking' ); ?>: <strong class="bpa-front-text-primary-color --is-service-price" :style="{ 'background-color': selected_colorpicker_values.primary_color, 'color': selected_colorpicker_values.price_button_text_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }"><?php echo $bookingpress_service_price2; ?></strong></p>
																				</div>
																			</div>
																		</div>
																	</div>
																</div>
															</el-col>
															<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
																<div class="bpa-front-module--service-item" :class="(bookingpress_shortcode_form.selected_service == 'chronic_disease_management_4') ? ' __bpa-is-selected' : ''" @click="bpa_select_service('chronic_disease_management_4')">
																	<div class="bpa-front-si-card" :style="[bookingpress_shortcode_form.selected_service == 'chronic_disease_management_4' ? { 'border-color': selected_colorpicker_values.primary_color } : '']">
																		<div class="bpa-front-si-card--checkmark-icon" v-if="bookingpress_shortcode_form.selected_service == 'chronic_disease_management_4'">
																			<span class="material-icons-round" :style="[bookingpress_shortcode_form.selected_service == 'chronic_disease_management_4' ? { 'color': selected_colorpicker_values.primary_color } : { 'color': selected_colorpicker_values.content_color }]">check_circle</span>
																		</div>
																		<div class="bpa-front-si-card__left">
																			<img :src="service_container_data.default_image_url" alt="">
																		</div>
																		<div class="bpa-front-si__card-body">
																			<h5 class="bpa-front-si__card-body--heading" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family }"><?php esc_html_e( 'Chronic Disease Management', 'bookingpress-appointment-booking' ); ?></h5>
																			<div class="bpa-front-si-cb__specs" v-if="booking_form_settings.display_service_description == true">
																				<p :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }" >Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam varius viverra lectus </p>
																			</div></br>
																			<div class="bpa-front-si-cb__specs">
																				<div class="bpa-front-si-cb__specs-item">
																					<p :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }"><?php esc_html_e( 'Duration', 'bookingpress-appointment-booking' ); ?>: <strong :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }">1h</strong></p>
																				</div>
																				<div class="bpa-front-si-cb__specs-item">
																					<p :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }"><?php esc_html_e( 'Price', 'bookingpress-appointment-booking' ); ?>: <strong class="bpa-front-text-primary-color --is-service-price" :style="{ 'background-color': selected_colorpicker_values.primary_color, 'color': selected_colorpicker_values.price_button_text_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }"><?php echo $bookingpress_service_price2; ?></strong></p>
																				</div>
																			</div>
																		</div>
																	</div>
																</div>
															</el-col>
														</el-row>
													</div>
												</div>
												<div class="bpa-front-tabs--foot" :style="{ 'background': selected_colorpicker_values.footer_background_color }">
													<el-button class="bpa-btn bpa-btn--primary bpa-btn--front-preview" :style="{ 'background': selected_colorpicker_values.primary_color, 'border-color': selected_colorpicker_values.primary_color, color: selected_colorpicker_values.price_button_text_color }">
														<span class="bpa--text-ellipsis">{{ booking_form_settings.next_button_text}}: <strong>{{tab_container_data.datetime_title }}</strong></span>
														<span class="material-icons-round">east</span>
													</el-button>
												</div>
											</div>
										</el-tab-pane>
										<el-tab-pane name="2">
											<template #label>
												<span :class="formActiveTab == '2' ? 'bpa_center_container_tab_title' : ''" :style="[ formActiveTab == '2' ? { 'background': selected_colorpicker_values.primary_color } : '' ]"><?php esc_html_e( '02', 'bookingpress-appointment-booking' ); ?></span>
											</template>
											<div class="bpa-cbf--preview-step __is-calendar-step" :style="{ 'background': selected_colorpicker_values.background_color }">
												<div class="bpa-cbf--preview-step__body-content">
													<div class="bpa-cbf--preview-step--tab-menu">
														<div class="bpa-cbf--ps--tm__item">
															<span class="bpa-form-label" v-text="tab_container_data.service_title"></span>
															<el-popover placement="bottom" v-model="tab_container_data.is_edit_service_2">
																<el-container class="bpa-customize--edit-label-popover">
																	<div>
																		<el-input class="bpa-form-control" v-model="tab_container_data.service_title"></el-input>
																	</div>
																	<div class="bpa-customize--edit-label-popover--actions">
																		<el-button class="bpa-btn bpa-btn__small" @click="tab_container_data.is_edit_service_2 = false"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
																		<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="tab_container_data.is_edit_service_2 = false"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
																	</div>
																</el-container>
																<el-button class="bpa-btn bpa-btn__small bpa-btn--icon-without-box" slot="reference">
																	<i class="material-icons-round">mode_edit</i>
																</el-button>
															</el-popover>
														</div>
														<div class="bpa-cbf--ps--tm__item">
															<span class="bpa-form-label" v-text="tab_container_data.datetime_title"></span>
															<el-popover placement="bottom" v-model="tab_container_data.is_edit_date_time_2">
																<el-container class="bpa-customize--edit-label-popover">
																	<div>
																		<el-input class="bpa-form-control" v-model="tab_container_data.datetime_title"></el-input>
																	</div>
																	<div class="bpa-customize--edit-label-popover--actions">
																		<el-button class="bpa-btn bpa-btn__small" @click="tab_container_data.is_edit_date_time_2 = false"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
																		<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="tab_container_data.is_edit_date_time_2 = false"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
																	</div>
																</el-container>
																<el-button class="bpa-btn bpa-btn__small bpa-btn--icon-without-box" slot="reference">
																	<i class="material-icons-round">mode_edit</i>
																</el-button>
															</el-popover>
														</div>
														<div class="bpa-cbf--ps--tm__item">
															<span class="bpa-form-label" v-text="tab_container_data.basic_details_title"></span>
															<el-popover placement="bottom" v-model="tab_container_data.is_edit_basic_details_2">
																<el-container class="bpa-customize--edit-label-popover">
																	<div>
																		<el-input class="bpa-form-control" v-model="tab_container_data.basic_details_title"></el-input>
																	</div>
																	<div class="bpa-customize--edit-label-popover--actions">
																		<el-button class="bpa-btn bpa-btn__small" @click="tab_container_data.is_edit_basic_details_2 = false"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
																		<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="tab_container_data.is_edit_basic_details_2 = false"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
																	</div>
																</el-container>
																<el-button class="bpa-btn bpa-btn__small bpa-btn--icon-without-box" slot="reference">
																	<i class="material-icons-round">mode_edit</i>
																</el-button>
															</el-popover>
														</div>
														<div class="bpa-cbf--ps--tm__item">
															<span class="bpa-form-label" v-text="tab_container_data.summary_title"></span>
															<el-popover placement="bottom" v-model="tab_container_data.is_edit_summary_2">
																<el-container class="bpa-customize--edit-label-popover">
																	<div>
																		<el-input class="bpa-form-control" v-model="tab_container_data.summary_title"></el-input>
																	</div>
																	<div class="bpa-customize--edit-label-popover--actions">
																		<el-button class="bpa-btn bpa-btn__small" @click="tab_container_data.is_edit_summary_2 = false"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
																		<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="tab_container_data.is_edit_summary_2 = false"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
																	</div>
																</el-container>
																<el-button class="bpa-btn bpa-btn__small bpa-btn--icon-without-box" slot="reference">
																	<i class="material-icons-round">mode_edit</i>
																</el-button>
															</el-popover>
														</div>
													</div>
													<div class="bpa-cbf--preview--module-container __cal-and-time">
														<el-row class="bpa-cbf--preview__module-heading">
															<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
																<label class="bpa-form-label" v-text="tab_container_data.datetime_title" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family }"></label>
															</el-col>
														</el-row>
														<el-row :gutter="40" type="flex">
															<el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
																<div class="bpa-front--dt__calendar">
																	<vue-cal small :disable-views="['years', 'year', 'week', 'day']" :time="false" :dblclick-to-navigate="false" active-view="month" :min-date="jsCurrentDate" :disable-days="days_off_disabled_dates" :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }" :locale="site_locale" />
																</div>
															</el-col>
															<el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
																<div class="bpa-front--dt__time-slots" :style="{ 'background': selected_colorpicker_values.background_color }">
																	<el-row type="flex" class="bpa-cbf--preview__module-heading">
																		<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
																			<label class="bpa-form-label" v-text="timeslot_container_data.timeslot_text" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family }"></label>
																			<el-popover placement="bottom" v-model="timeslot_container_data.is_edit_timeslot">
																				<el-container class="bpa-customize--edit-label-popover">
																					<div>
																						<el-input class="bpa-form-control" v-model="timeslot_container_data.timeslot_text"></el-input>
																					</div>
																					<div class="bpa-customize--edit-label-popover--actions">
																						<el-button class="bpa-btn bpa-btn__small" @click="timeslot_container_data.is_edit_timeslot = false"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
																						<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="timeslot_container_data.is_edit_timeslot = false"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
																					</div>
																				</el-container>
																				<el-button class="bpa-btn bpa-btn__small bpa-btn--icon-without-box" slot="reference">
																					<i class="material-icons-round">mode_edit</i>
																				</el-button>
																			</el-popover>
																		</el-col>
																	</el-row>
																	<div class="bpa-front--dt__ts-body">
																		<div class="bpa-front--dt__ts-body--row">
																			<div class="bpa-timeslot-heading">
																				<h5 :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family }">{{ timeslot_container_data.morning_text }}</h5>
																				<el-popover placement="bottom" v-model="timeslot_container_data.is_edit_morning">
																					<el-container class="bpa-customize--edit-label-popover">
																						<div>
																							<el-input class="bpa-form-control" v-model="timeslot_container_data.morning_text"></el-input>
																						</div>
																						<div class="bpa-customize--edit-label-popover--actions">
																							<el-button class="bpa-btn bpa-btn__small" @click="timeslot_container_data.is_edit_morning = false"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
																							<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="timeslot_container_data.is_edit_morning = false"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
																						</div>
																					</el-container>
																					<el-button class="bpa-btn bpa-btn__small bpa-btn--icon-without-box" slot="reference">
																						<i class="material-icons-round">mode_edit</i>
																					</el-button>
																				</el-popover>
																			</div>
																			<div class="bpa-front--dt__ts-body--items">
																				<div class="bpa-front--dt__ts-body--item __bpa-is-disabled">
																					<span :style="[bookingpress_shortcode_form.selected_time == '09:00' ? { 'color': selected_colorpicker_values.primary_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } : { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } ]">
																						09:00 to 09:30
																					</span>
																				</div>
																				<div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '09:30') ? '__bpa-is-selected' : ''" @click="bpa_select_time('09:30')" :style="[bookingpress_shortcode_form.selected_time == '09:30' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : '']">
																					<span :style="[bookingpress_shortcode_form.selected_time == '09:30' ? { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } : { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } ]">
																						09:30 to 10:00
																					</span>
																				</div>
																				<div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '10:00') ? '__bpa-is-selected' : ''" @click="bpa_select_time('10:00')" :style="[bookingpress_shortcode_form.selected_time == '10:00' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : '']">
																					<span :style="[bookingpress_shortcode_form.selected_time == '10:00' ? { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } : { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } ]">
																						10:00 to 10:30
																					</span>
																				</div>
																				<div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '10:30') ? '__bpa-is-selected' : ''" @click="bpa_select_time('10:30')" :style="[bookingpress_shortcode_form.selected_time == '10:30' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : '']">
																					<span :style="[bookingpress_shortcode_form.selected_time == '10:30' ? { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } : { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } ]">
																						10:30 to 11:00
																					</span>
																				</div>
																				<div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '11:00') ? '__bpa-is-selected' : ''" @click="bpa_select_time('11:00')" :style="[bookingpress_shortcode_form.selected_time == '11:00' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : '']">
																					<span :style="[bookingpress_shortcode_form.selected_time == '11:00' ? { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } : { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } ]">
																						11:00 to 11:30
																					</span>
																				</div>
																			</div>
																		</div>
																		<div class="bpa-front--dt__ts-body--row">
																			<div class="bpa-timeslot-heading">
																				<h5 :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family }">{{ timeslot_container_data.afternoon_text }}</h5>
																				<el-popover placement="bottom" v-model="timeslot_container_data.is_edit_afternoon">
																					<el-container class="bpa-customize--edit-label-popover">
																						<div>
																							<el-input class="bpa-form-control" v-model="timeslot_container_data.afternoon_text"></el-input>
																						</div>
																						<div class="bpa-customize--edit-label-popover--actions">
																							<el-button class="bpa-btn bpa-btn__small" @click="timeslot_container_data.is_edit_afternoon = false"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
																							<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="timeslot_container_data.is_edit_afternoon = false"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
																						</div>
																					</el-container>
																					<el-button class="bpa-btn bpa-btn__small bpa-btn--icon-without-box" slot="reference">
																						<i class="material-icons-round">mode_edit</i>
																					</el-button>
																				</el-popover>
																			</div>
																			<div class="bpa-front--dt__ts-body--items">
																				<div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '12:00') ? '__bpa-is-selected' : ''" @click="bpa_select_time('12:00')" :style="[bookingpress_shortcode_form.selected_time == '12:00' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : '']">
																					<span :style="[bookingpress_shortcode_form.selected_time == '12:00' ? { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } : { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } ]">
																						12:00 to 12:30
																					</span>
																				</div>
																				<div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '12:30') ? '__bpa-is-selected' : ''" @click="bpa_select_time('12:30')" :style="[bookingpress_shortcode_form.selected_time == '12:30' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : '']">
																					<span :style="[bookingpress_shortcode_form.selected_time == '12:30' ? { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } : { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } ]">
																						12:30 to 13:00
																					</span>
																				</div>
																				<div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '13:00') ? '__bpa-is-selected' : ''" @click="bpa_select_time('13:00')" :style="[bookingpress_shortcode_form.selected_time == '13:00' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : '']">
																					<span :style="[bookingpress_shortcode_form.selected_time == '13:00' ? { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } : { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } ]">
																						13:00 to 13:30
																					</span>
																				</div>
																				<div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '13:30') ? '__bpa-is-selected' : ''" @click="bpa_select_time('13:30')" :style="[bookingpress_shortcode_form.selected_time == '13:30' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : '']">
																					<span :style="[bookingpress_shortcode_form.selected_time == '13:30' ? { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } : { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } ]">
																						13:30 to 14:00
																					</span>
																				</div>
																			</div>
																		</div>
																		<div class="bpa-front--dt__ts-body--row">
																			<div class="bpa-timeslot-heading">
																				<h5 :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family }">{{ timeslot_container_data.evening_text }}</h5>
																				<el-popover placement="bottom" v-model="timeslot_container_data.is_edit_evening">
																					<el-container class="bpa-customize--edit-label-popover">
																						<div>
																							<el-input class="bpa-form-control" v-model="timeslot_container_data.evening_text"></el-input>
																						</div>
																						<div class="bpa-customize--edit-label-popover--actions">
																							<el-button class="bpa-btn bpa-btn__small" @click="timeslot_container_data.is_edit_evening = false"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
																							<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="timeslot_container_data.is_edit_evening = false"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
																						</div>
																					</el-container>
																					<el-button class="bpa-btn bpa-btn__small bpa-btn--icon-without-box" slot="reference">
																						<i class="material-icons-round">mode_edit</i>
																					</el-button>
																				</el-popover>
																			</div>	
																			<div class="bpa-front--dt__ts-body--items">
																				<div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '17:00') ? '__bpa-is-selected' : ''" @click="bpa_select_time('17:00')" :style="[bookingpress_shortcode_form.selected_time == '17:00' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : '']">
																					<span :style="[bookingpress_shortcode_form.selected_time == '17:00' ? { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } : { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } ]">
																						17:00 to 17:30
																					</span>
																				</div>
																				<div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '17:30') ? '__bpa-is-selected' : ''" @click="bpa_select_time('17:30')" :style="[bookingpress_shortcode_form.selected_time == '17:30' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : '']">
																					<span :style="[bookingpress_shortcode_form.selected_time == '17:30' ? { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } : { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } ]">
																						17:30 to 18:00
																					</span>
																				</div>
																				<div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '18:00') ? '__bpa-is-selected' : ''" @click="bpa_select_time('18:00')" :style="[bookingpress_shortcode_form.selected_time == '18:00' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : '']">
																					<span :style="[bookingpress_shortcode_form.selected_time == '18:00' ? { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } : { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } ]">
																						18:00 to 18:30
																					</span>
																				</div>
																				<div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '18:30') ? '__bpa-is-selected' : ''" @click="bpa_select_time('18:30')" :style="[bookingpress_shortcode_form.selected_time == '18:30' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : '']">
																					<span :style="[bookingpress_shortcode_form.selected_time == '18:30' ? { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } : { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } ]">
																						18:30 to 19:00
																					</span>
																				</div>
																			</div>
																		</div>
																		<div class="bpa-front--dt__ts-body--row">
																			<div class="bpa-timeslot-heading">
																				<h5 :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family }">{{ timeslot_container_data.night_text }}</h5>
																				<el-popover placement="bottom" v-model="timeslot_container_data.is_edit_night">
																					<el-container class="bpa-customize--edit-label-popover">
																						<div>
																							<el-input class="bpa-form-control" v-model="timeslot_container_data.night_text"></el-input>
																						</div>
																						<div class="bpa-customize--edit-label-popover--actions">
																							<el-button class="bpa-btn bpa-btn__small" @click="timeslot_container_data.is_edit_night = false"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
																							<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="timeslot_container_data.is_edit_night = false"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
																						</div>
																					</el-container>
																					<el-button class="bpa-btn bpa-btn__small bpa-btn--icon-without-box" slot="reference">
																						<i class="material-icons-round">mode_edit</i>
																					</el-button>
																				</el-popover>
																			</div>
																			<div class="bpa-front--dt__ts-body--items">
																				<div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '20:00') ? '__bpa-is-selected' : ''" @click="bpa_select_time('20:00')" :style="[bookingpress_shortcode_form.selected_time == '20:00' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : '']">
																					<span :style="[bookingpress_shortcode_form.selected_time == '20:00' ? { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } : { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } ]">
																						20:00 to 20:30
																					</span>
																				</div>
																				<div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '20:30') ? '__bpa-is-selected' : ''" @click="bpa_select_time('20:30')" :style="[bookingpress_shortcode_form.selected_time == '20:30' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : '']">
																					<span :style="[bookingpress_shortcode_form.selected_time == '20:30' ? { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } : { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } ]">
																						20:30 to 21:00
																					</span>
																				</div>
																				<div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '21:00') ? '__bpa-is-selected' : ''" @click="bpa_select_time('21:00')" :style="[bookingpress_shortcode_form.selected_time == '21:00' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : '']">
																					<span :style="[bookingpress_shortcode_form.selected_time == '21:00' ? { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } : { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } ]">
																						21:00 to 21:30
																					</span>
																				</div>
																				<div class="bpa-front--dt__ts-body--item" :class="(bookingpress_shortcode_form.selected_time == '21:30') ? '__bpa-is-selected' : ''" @click="bpa_select_time('21:30')" :style="[bookingpress_shortcode_form.selected_time == '21:30' ? { 'border-color': selected_colorpicker_values.primary_color, 'background': selected_colorpicker_values.primary_background_color } : '']">
																					<span :style="[bookingpress_shortcode_form.selected_time == '21:30' ? { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } : { 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family } ]">
																						21:30 to 22:00
																					</span>
																				</div>
																			</div>
																		</div>
																	</div>
																</div>
															</el-col>
														</el-row>
													</div>
												</div>
												<div class="bpa-front-tabs--foot" :style="{ 'background': selected_colorpicker_values.footer_background_color }">
													<el-button class="bpa-btn bpa-btn--borderless">
														<span class="material-icons-round">west</span>
														<span class="bpa--text-ellipsis">{{ booking_form_settings.goback_button_text }}</span>
													</el-button>
													<el-button class="bpa-btn bpa-btn--primary bpa-btn--front-preview" :style="{ 'background': selected_colorpicker_values.primary_color, 'border-color': selected_colorpicker_values.primary_color, color: selected_colorpicker_values.price_button_text_color }">
														<span class="bpa--text-ellipsis">{{ booking_form_settings.next_button_text}}: <strong>{{ tab_container_data.basic_details_title }}</strong></span>
														<span class="material-icons-round">east</span>
													</el-button>
												</div>
											</div>
										</el-tab-pane>
										<el-tab-pane name="3">
											<template #label>
												<span :class="formActiveTab == '3' ? 'bpa_center_container_tab_title' : ''" :style="[ formActiveTab == '3' ? { 'background': selected_colorpicker_values.primary_color } : '' ]"><?php esc_html_e( '03', 'bookingpress-appointment-booking' ); ?></span>
											</template>
											<div class="bpa-cbf--preview-step __is-basic-details-step" :style="{ 'background': selected_colorpicker_values.background_color }">
												<div class="bpa-cbf--preview-step__body-content">
													<div class="bpa-cbf--preview-step--tab-menu">
														<div class="bpa-cbf--ps--tm__item">
															<span class="bpa-form-label" v-text="tab_container_data.service_title"></span>
															<el-popover placement="bottom" v-model="tab_container_data.is_edit_service_3">
																<el-container class="bpa-customize--edit-label-popover">
																	<div>
																		<el-input class="bpa-form-control" v-model="tab_container_data.service_title"></el-input>
																	</div>
																	<div class="bpa-customize--edit-label-popover--actions">
																		<el-button class="bpa-btn bpa-btn__small" @click="tab_container_data.is_edit_service_3 = false"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
																		<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="tab_container_data.is_edit_service_3 = false"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
																	</div>
																</el-container>
																<el-button class="bpa-btn bpa-btn__small bpa-btn--icon-without-box" slot="reference">
																	<i class="material-icons-round">mode_edit</i>
																</el-button>
															</el-popover>
														</div>
														<div class="bpa-cbf--ps--tm__item">
															<span class="bpa-form-label" v-text="tab_container_data.datetime_title"></span>
															<el-popover placement="bottom" v-model="tab_container_data.is_edit_date_time_3">
																<el-container class="bpa-customize--edit-label-popover">
																	<div>
																		<el-input class="bpa-form-control" v-model="tab_container_data.datetime_title"></el-input>
																	</div>
																	<div class="bpa-customize--edit-label-popover--actions">
																		<el-button class="bpa-btn bpa-btn__small" @click="tab_container_data.is_edit_date_time_3 = false"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
																		<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="tab_container_data.is_edit_date_time_3 = false"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
																	</div>
																</el-container>
																<el-button class="bpa-btn bpa-btn__small bpa-btn--icon-without-box" slot="reference">
																	<i class="material-icons-round">mode_edit</i>
																</el-button>
															</el-popover>
														</div>
														<div class="bpa-cbf--ps--tm__item">
															<span class="bpa-form-label" v-text="tab_container_data.basic_details_title"></span>
															<el-popover placement="bottom" v-model="tab_container_data.is_edit_basic_details_3">
																<el-container class="bpa-customize--edit-label-popover">
																	<div>
																		<el-input class="bpa-form-control" v-model="tab_container_data.basic_details_title"></el-input>
																	</div>
																	<div class="bpa-customize--edit-label-popover--actions">
																		<el-button class="bpa-btn bpa-btn__small" @click="tab_container_data.is_edit_basic_details_3 = false"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
																		<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="tab_container_data.is_edit_basic_details_3 = false"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
																	</div>
																</el-container>
																<el-button class="bpa-btn bpa-btn__small bpa-btn--icon-without-box" slot="reference">
																	<i class="material-icons-round">mode_edit</i>
																</el-button>
															</el-popover>
														</div>
														<div class="bpa-cbf--ps--tm__item">
															<span class="bpa-form-label" v-text="tab_container_data.summary_title"></span>
															<el-popover placement="bottom" v-model="tab_container_data.is_edit_summary_3">
																<el-container class="bpa-customize--edit-label-popover">
																	<div>
																		<el-input class="bpa-form-control" v-model="tab_container_data.summary_title"></el-input>
																	</div>
																	<div class="bpa-customize--edit-label-popover--actions">
																		<el-button class="bpa-btn bpa-btn__small" @click="tab_container_data.is_edit_summary_3 = false"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
																		<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="tab_container_data.is_edit_summary_3 = false"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
																	</div>
																</el-container>
																<el-button class="bpa-btn bpa-btn__small bpa-btn--icon-without-box" slot="reference">
																	<i class="material-icons-round">mode_edit</i>
																</el-button>
															</el-popover>
														</div>
													</div>
													<div class="bpa-cbf--preview--module-container">
														<el-row class="bpa-cbf--preview__module-heading">
															<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
																<label class="bpa-form-label" v-text="tab_container_data.basic_details_title" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family }"></label>
															</el-col>
														</el-row>
														<el-row>
															<el-form ref="appointment_step_form_data">
																<el-col>
																	<div class="bpa-front-module--bd-form">
																		<el-row :gutter="24">
																			<el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
																				<el-form-item prop="customer_name" ref="customer_name">
																					<template #label>
																						<span class="bpa-form-label" :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }"><?php esc_html_e( 'Name', 'bookingpress-appointment-booking' ); ?></span>		
																					</template>
																					<el-input v-model="bookingpress_shortcode_form.customer_name" class="bpa-form-control" placeholder="<?php esc_html_e( 'Please enter your name', 'bookingpress-appointment-booking' ); ?>" :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }"></el-input>
																				</el-form-item>
																			</el-col>
																			<el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
																				<el-form-item prop="customer_phone" ref="customer_phone">
																					<template #label>
																						<span class="bpa-form-label" :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }"><?php esc_html_e( 'Phone', 'bookingpress-appointment-booking' ); ?></span>
																					</template>
																					<vue-tel-input v-model="bookingpress_shortcode_form.cusomter_phone" class="bpa-form-control --bpa-country-dropdown">
																						<template v-slot:arrow-icon>
																							<span class="material-icons-round">keyboard_arrow_down</span>
																						</template>
																					</vue-tel-input>
																					<?php /*
																					<el-input v-model="bookingpress_shortcode_form.cusomter_phone" class="bpa-form-control" placeholder="<?php esc_html_e( 'Please enter your phone no', 'bookingpress-appointment-booking' ); ?>" :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }">
																						<el-select v-model="bookingpress_shortcode_form.customer_selected_country" slot="prepend" class="bpa-form-control__country-dropdown" popper-class="bpa-el-select--is-with-navbar">
																							<el-option v-for="countries in bookingpress_shortcode_form.phone_countries_details" :value="countries.code">
																								<span class="flag" :class="countries.code"></span> {{ countries.name }}
																							</el-option>
																						</el-select>
																					</el-input>	 */ ?>
																				</el-form-item>
																			</el-col>
																			<el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
																				<el-form-item prop="customer_email" ref="customer_email">
																					<template #label>
																						<span class="bpa-form-label" :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }"><?php esc_html_e( 'Email', 'bookingpress-appointment-booking' ); ?> </span>
																					</template>
																					<el-input v-model="bookingpress_shortcode_form.customer_email" type="email" class="bpa-form-control" placeholder="<?php esc_html_e( 'Please enter your email', 'bookingpress-appointment-booking' ); ?>" :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }"></el-input>
																				</el-form-item>
																			</el-col>
																		</el-row>
																		<el-row :gutter="24">
																			<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
																				<el-form-item prop="appointment_note" ref="appointment_note">
																					<template #label>
																						<span class="bpa-form-label" :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }"><?php esc_html_e( 'Note', 'bookingpress-appointment-booking' ); ?></span>
																					</template>
																					<el-input type="textarea" v-model="bookingpress_shortcode_form.customer_note" class="bpa-form-control" placeholder="<?php esc_html_e( 'Please enter your note', 'bookingpress-appointment-booking' ); ?>" :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }"></el-input>
																				</el-form-item>
																			</el-col>
																		</el-row>
																	</div>
																</el-col>
															</el-form>
														</el-row>
													</div>
												</div>
												<div class="bpa-front-tabs--foot" :style="{ 'background': selected_colorpicker_values.footer_background_color }">
													<el-button class="bpa-btn bpa-btn--borderless">
														<span class="material-icons-round">west</span>
														<span>{{ booking_form_settings.goback_button_text }}</span>
													</el-button>
													<el-button class="bpa-btn bpa-btn--primary" :style="{ 'background': selected_colorpicker_values.primary_color, 'border-color': selected_colorpicker_values.primary_color, color: selected_colorpicker_values.price_button_text_color}">
														<span>{{ booking_form_settings.next_button_text}}: <strong>{{ tab_container_data.summary_title }}</strong></span>
														<span class="material-icons-round">east</span>
													</el-button>
												</div>
											</div>
										</el-tab-pane>
										<el-tab-pane name="4">
											<template #label>
												<span :class="formActiveTab == '4' ? 'bpa_center_container_tab_title' : ''" :style="[ formActiveTab == '4' ? { 'background': selected_colorpicker_values.primary_color } : '' ]"><?php esc_html_e( '04', 'bookingpress-appointment-booking' ); ?></span>
											</template>
											<div class="bpa-cbf--preview-step __is-summary-step" :style="{ 'background': selected_colorpicker_values.background_color }">
												<div class="bpa-cbf--preview-step__body-content">
													<div class="bpa-cbf--preview-step--tab-menu">
														<div class="bpa-cbf--ps--tm__item">
															<span class="bpa-form-label" v-text="tab_container_data.service_title"></span>
															<el-popover placement="bottom" v-model="tab_container_data.is_edit_service_4">
																<el-container class="bpa-customize--edit-label-popover">
																	<div>
																		<el-input class="bpa-form-control" v-model="tab_container_data.service_title"></el-input>
																	</div>
																	<div class="bpa-customize--edit-label-popover--actions">
																		<el-button class="bpa-btn bpa-btn__small" @click="tab_container_data.is_edit_service_4 = false"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
																		<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="tab_container_data.is_edit_service_4 = false"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
																	</div>
																</el-container>
																<el-button class="bpa-btn bpa-btn__small bpa-btn--icon-without-box" slot="reference">
																	<i class="material-icons-round">mode_edit</i>
																</el-button>
															</el-popover>
														</div>
														<div class="bpa-cbf--ps--tm__item">
															<span class="bpa-form-label" v-text="tab_container_data.datetime_title"></span>
															<el-popover placement="bottom" v-model="tab_container_data.is_edit_date_time_4">
																<el-container class="bpa-customize--edit-label-popover">
																	<div>
																		<el-input class="bpa-form-control" v-model="tab_container_data.datetime_title"></el-input>
																	</div>
																	<div class="bpa-customize--edit-label-popover--actions">
																		<el-button class="bpa-btn bpa-btn__small" @click="tab_container_data.is_edit_date_time_4 = false"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
																		<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="tab_container_data.is_edit_date_time_4 = false"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
																	</div>
																</el-container>
																<el-button class="bpa-btn bpa-btn__small bpa-btn--icon-without-box" slot="reference">
																	<i class="material-icons-round">mode_edit</i>
																</el-button>
															</el-popover>
														</div>
														<div class="bpa-cbf--ps--tm__item">
															<span class="bpa-form-label" v-text="tab_container_data.basic_details_title"></span>
															<el-popover placement="bottom" v-model="tab_container_data.is_edit_basic_details_4">
																<el-container class="bpa-customize--edit-label-popover">
																	<div>
																		<el-input class="bpa-form-control" v-model="tab_container_data.basic_details_title"></el-input>
																	</div>
																	<div class="bpa-customize--edit-label-popover--actions">
																		<el-button class="bpa-btn bpa-btn__small" @click="tab_container_data.is_edit_basic_details_4 = false"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
																		<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="tab_container_data.is_edit_basic_details_4 = false"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
																	</div>
																</el-container>
																<el-button class="bpa-btn bpa-btn__small bpa-btn--icon-without-box" slot="reference">
																	<i class="material-icons-round">mode_edit</i>
																</el-button>
															</el-popover>
														</div>
														<div class="bpa-cbf--ps--tm__item">
															<span class="bpa-form-label" v-text="tab_container_data.summary_title"></span>
															<el-popover placement="bottom" v-model="tab_container_data.is_edit_summary_4">
																<el-container class="bpa-customize--edit-label-popover">
																	<div>
																		<el-input class="bpa-form-control" v-model="tab_container_data.summary_title"></el-input>
																	</div>
																	<div class="bpa-customize--edit-label-popover--actions">
																		<el-button class="bpa-btn bpa-btn__small" @click="tab_container_data.is_edit_summary_4 = false"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
																		<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="tab_container_data.is_edit_summary_4 = false"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
																	</div>
																</el-container>
																<el-button class="bpa-btn bpa-btn__small bpa-btn--icon-without-box" slot="reference">
																	<i class="material-icons-round">mode_edit</i>
																</el-button>
															</el-popover>
														</div>
													</div>
													<div class="bpa-cbf--preview--module-container">
														<el-row>
															<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
																<div class="bpa-front-module-container bpa-front-module--booking-summary">
																	<div class="bpa-front-module--bs-head">
																		<img src="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/front-summary-vector.svg' ); ?>" alt=""/>
																		<h4 :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family }">{{ tab_container_data.summary_title }}</h4>																				
																		<el-row class="bpa-cbf--preview__module-desc--is-edit">
																			<el-col>
																				<p :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }">{{ summary_container_data.summary_content_text }}</p>
																				<el-popover placement="bottom" v-model="summary_container_data.is_edit_summary_content">
																					<el-container class="bpa-customize--edit-label-popover">
																						<div>
																							<el-input class="bpa-form-control" v-model="summary_container_data.summary_content_text"></el-input>
																						</div>
																						<div class="bpa-customize--edit-label-popover--actions">
																							<el-button class="bpa-btn bpa-btn__small" @click="summary_container_data.is_edit_summary_content = false"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
																							<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="summary_container_data.is_edit_summary_content = false"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
																						</div>
																					</el-container>
																					<el-button class="bpa-btn bpa-btn__small bpa-btn--icon-without-box" slot="reference">
																						<span class="material-icons-round">mode_edit</span>
																					</el-button>
																				</el-popover>
																			</el-col>
																		</el-row>
																	</div>
																	<div class="bpa-front-module--bs-summary-content">
																		<div class="bpa-front-module--bs-summary-content-item">
																			<span :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family }"><?php esc_html_e( 'Service', 'bookingpress-appointment-booking' ); ?>:</span>
																			<h4 :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }"><?php esc_html_e( 'Chronic Disease Management', 'bookingpress-appointment-booking' ); ?></h4>
																		</div>
																		<div class="bpa-front-module--bs-summary-content-item">
																			<span :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family }"><?php esc_html_e( 'Date &amp; Time', 'bookingpress-appointment-booking' ); ?>:</span>
																			<h4 :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }" v-if="booking_form_settings.default_date_format == 'Y-m-d'"><?php echo date( 'Y-m-d', current_time( 'timestamp' ) ); ?>, 10:00 to 10:30</h4>
																			<h4 :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }" v-if="booking_form_settings.default_date_format == 'F j, Y'"><?php echo date( 'F j, Y', current_time( 'timestamp' ) ); ?>, 10:00 to 10:30</h4>
																			<h4 :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }" v-if="booking_form_settings.default_date_format == 'd/m/Y' "><?php echo date( 'd/m/Y', current_time( 'timestamp' ) ); ?>, 10:00 to 10:30</h4>															
																			<h4 :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }" v-if="booking_form_settings.default_date_format == 'm/d/Y'"><?php echo date( 'm/d/Y', current_time( 'timestamp' ) ); ?>, 10:00 to 10:30</h4>																
																		</div>
																		<div class="bpa-front-module--bs-summary-content-item">
																			<span :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family }"><?php esc_html_e( 'Customer Name', 'bookingpress-appointment-booking' ); ?>:</span>
																			<h4 :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }">Jerry G. Lugo</h4>
																		</div>
																	</div>
																	<div class="bpa-front-module--bs-amount-details" :style="{ 'background': selected_colorpicker_values.background_color }">
																		<h4 :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family }"><?php esc_html_e( 'Total Amount Payable', 'bookingpress-appointment-booking' ); ?></h4>
																		<h4 :style="{ 'color': selected_colorpicker_values.primary_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }" class="bpa-front-module--bs-ad--price"><?php echo $bookingpress_price; ?></h4>
																	</div>
																</div>
															</el-col>
														</el-row>
														<el-row>
															<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
																<div class="bpa-front-module-container bpa-front-module--payment-methods">
																	<div class="bpa-front-module--pm-head">
																		<div class="__bpa-pm-icon">
																			<span class="material-icons-round">payments</span>
																		</div>
																		<div class="__bpa-pm-content">
																			<el-row class="bpa-cbf--preview__module-heading">
																				<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
																					<h4 :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family }">{{ summary_container_data.payment_method_text }}</h4>
																					<el-popover placement="bottom" v-model="summary_container_data.is_edit_select_payment_method">
																						<el-container class="bpa-customize--edit-label-popover">
																							<div>
																								<el-input class="bpa-form-control" v-model="summary_container_data.payment_method_text"></el-input>
																							</div>
																							<div class="bpa-customize--edit-label-popover--actions">
																								<el-button class="bpa-btn bpa-btn__small" @click="summary_container_data.is_edit_select_payment_method = false"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
																								<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="summary_container_data.is_edit_select_payment_method = false"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
																							</div>
																						</el-container>
																						<el-button class="bpa-btn bpa-btn__small bpa-btn--icon-without-box" slot="reference">
																							<i class="material-icons-round">mode_edit</i>
																						</el-button>
																					</el-popover>
																				</el-col>
																			</el-row>
																		</div>
																	</div>
																	<div class="bpa-front-module--pm-body">
																		<div class="bpa-front-module--pm-body__item __bpa-is-selected" :style="{ 'border-color': selected_colorpicker_values.primary_color }">
																			<div class="bpa-front-si-card--checkmark-icon" >
																				<span class="material-icons-round" :style="{ 'color': selected_colorpicker_values.primary_color }">check_circle</span>
																			</div>
																			<span class="material-icons-round">storefront</span>
																			<p :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }"><?php esc_html_e( 'Pay Locally', 'bookingpress-appointment-booking' ); ?></p>
																		</div>
																		<div class="bpa-front-module--pm-body__item">
																			<span><img src="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/front-paypal-symbol.svg' ); ?>" alt=""></span>
																			<p :style="{ 'color': selected_colorpicker_values.content_color, 'font-size': selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }"><?php esc_html_e( 'PayPal', 'bookingpress-appointment-booking' ); ?></p>
																		</div>
																	</div>
																</div>
															</el-col>
														</el-row>
													</div>
												</div>
												<div class="bpa-front-tabs--foot">
													<el-button class="bpa-btn bpa-btn--borderless">
														<span class="material-icons-round">west</span>
														{{ booking_form_settings.goback_button_text }}
													</el-button>
													<el-button class="bpa-btn bpa-btn--primary" :style="{ 'background': selected_colorpicker_values.primary_color, 'border-color': selected_colorpicker_values.primary_color,color: selected_colorpicker_values.price_button_text_color }">
														{{ booking_form_settings.book_appointment_btn_text }}													
													</el-button>
												</div>
											</div>
										</el-tab-pane>
									</el-tabs>
								</div>
							</el-col>
							<el-col :xs="24" :sm="24" :md="24" :lg="4" :xl="4">
								<div class="bpa-customize-step-side-panel">
									<div class="bpa-cs-sp--heading">
										<h4><?php esc_html_e( 'Form Settings', 'bookingpress-appointment-booking' ); ?></h4>
										<!-- <el-button class="bpa-btn bpa-btn__small" @click="bpa_reset_formsettings">
											<?php esc_html_e( 'Reset', 'bookingpress-appointment-booking' ); ?>
										</el-button> -->
									</div>
									<div class="bpa-cs-sp-sub-module bpa-sm--swtich">
										<div class="bpa-sm--item --bpa-is-flexbox">
											<label class="bpa-form-label"><?php esc_html_e( 'Hide Category &amp; Service Selection Step', 'bookingpress-appointment-booking' ); ?></label>
											<el-switch v-model="booking_form_settings.hide_category_service_selection" class="bpa-swtich-control"></el-switch>
										</div>
										<div class="bpa-sm--item --bpa-is-flexbox">
											<label class="bpa-form-label"><?php esc_html_e( 'Hide next &amp; previous buttons', 'bookingpress-appointment-booking' ); ?></label>
											<el-switch v-model="booking_form_settings.hide_next_previous_button" class="bpa-swtich-control"></el-switch>
										</div>
										<div class="bpa-sm--item --bpa-is-flexbox">
											<label class="bpa-form-label"><?php esc_html_e( 'Hide already booked slot', 'bookingpress-appointment-booking' ); ?></label>
											<el-switch v-model="booking_form_settings.hide_already_booked_slot" class="bpa-swtich-control"></el-switch>
										</div>										
										<div class="bpa-sm--item --bpa-is-flexbox">
											<label class="bpa-form-label"><?php esc_html_e( 'Display service description', 'bookingpress-appointment-booking' ); ?></label>
											<el-switch v-model="booking_form_settings.display_service_description" class="bpa-swtich-control"></el-switch>
										</div>
									</div>
									<div class="bpa-cs-sp-sub-module bpa-cs-sp--form-controls">
										<div class="bpa-sm--item">
											<label class="bpa-form-label"><?php esc_html_e( 'Booking Wizard Tabs Position', 'bookingpress-appointment-booking' ); ?></label>
											<el-select v-model="booking_form_settings.booking_form_tabs_position" @change="bookingpress_set_date_format" class="bpa-form-control"
												:popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar">
												<el-option value="left"><?php esc_html_e( 'Left', 'bookingpress-appointment-booking' ); ?></el-option>
												<el-option value="top"><?php esc_html_e( 'Top', 'bookingpress-appointment-booking' ); ?></el-option>
											</el-select>
										</div>
										<div class="bpa-sm--item">
											<label class="bpa-form-label"><?php esc_html_e( 'Go back button label', 'bookingpress-appointment-booking' ); ?></label>
											<el-input v-model="booking_form_settings.goback_button_text" class="bpa-form-control"></el-input>
										</div>
										<div class="bpa-sm--item">
											<label class="bpa-form-label"><?php esc_html_e( 'Next button label', 'bookingpress-appointment-booking' ); ?></label>
											<el-input v-model="booking_form_settings.next_button_text" class="bpa-form-control"></el-input>		
										</div>
										<div class="bpa-sm--item">
											<label class="bpa-form-label"><?php esc_html_e( 'Book Appointment Button label', 'bookingpress-appointment-booking' ); ?></label>
											<el-input v-model="booking_form_settings.book_appointment_btn_text" class="bpa-form-control"></el-input>		
										</div>
										<div class="bpa-sm--item">
											<label class="bpa-form-label"><?php esc_html_e( 'Date Format', 'bookingpress-appointment-booking' ); ?></label>
											<el-select v-model="booking_form_settings.default_date_format" @change="bookingpress_set_date_format" class="bpa-form-control"
												popper-class="bpa-el-select--is-with-navbar">
												<el-option label="<?php esc_html( 'F j, Y' ); ?>" value="F j, Y"></el-option>
												<el-option label="<?php esc_html( 'Y-m-d' ); ?>" value="Y-m-d"></el-option>
												<el-option label="<?php esc_html( 'm/d/Y' ); ?>" value="m/d/Y"></el-option>
												<el-option label="<?php esc_html( 'd/m/Y' ); ?>" value="d/m/Y"></el-option>
											</el-select>
										</div>
									</div>
								</div>
							</el-col>
						</el-row>
					</div>
				</el-tab-pane>
				<el-tab-pane name="field_settings">
					<template #label>
						<span><?php esc_html_e( 'Field Settings', 'bookingpress-appointment-booking' ); ?></span>
					</template>
					<el-row type="flex">
						<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
							<div class="bpa-customize-field-settings-body-container">
								<el-row>
									<draggable :list="field_settings_fields" class="list-group" ghost-class="ghost" @start="dragging = true" @end="dragging = false" :move="updateFieldPos">
										<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-for="field_settings_data in field_settings_fields" v-if="field_settings_data.field_name != 'note'">
											<div class="bpa-cfs-item-card list-group-item">
												<div class="bpa-cfs-ic--head">
													<div class="bpa-cfs-ic--head__type-label">
														<span class="material-icons-round" v-show="field_settings_data.field_name != 'note'">drag_indicator</span>
														<p>{{ field_settings_data.field_type }}</p>
													</div>
													<div class="bpa-cfs-ic--head__field-controls">
														<div class="bpa-cfs-ic--head__fc-swtich">
															<el-switch v-model="field_settings_data.is_required" class="bpa-swtich-control" v-if="field_settings_data.field_name == 'email_address'" disabled></el-switch>
															<el-switch v-model="field_settings_data.is_required" class="bpa-swtich-control" v-else></el-switch>
															<label>Required</label>
														</div>
														<div class="bpa-cfs-ic--head__fc-actions">
															<el-popover placement="bottom" v-model="field_settings_data.is_edit">
																<el-container class="bpa-field-settings-edit-container">
																	<div class="bpa-fs-item-settings-form-control-item">
																		<label class="bpa-form-label"><?php esc_html_e( 'Label', 'bookingpress-appointment-booking' ); ?></label>
																		<el-input class="bpa-form-control" v-model="field_settings_data.label"></el-input>
																	</div>
																	<div class="bpa-fs-item-settings-form-control-item">
																		<label class="bpa-form-label"><?php esc_html_e( 'Placeholder', 'bookingpress-appointment-booking' ); ?></label>
																		<el-input class="bpa-form-control" v-model="field_settings_data.placeholder"></el-input>
																	</div>
																	<div class="bpa-fs-item-settings-form-control-item">
																		<label class="bpa-form-label"><?php esc_html_e( 'Error Message', 'bookingpress-appointment-booking' ); ?></label>
																		<el-input class="bpa-form-control" v-model="field_settings_data.error_message"></el-input>
																	</div>
																	<div class="bpa-fs-item-settings-form-control-item" v-if="field_settings_data.field_name != 'email_address'">
																		<label class="bpa-form-label"><?php esc_html_e( 'Hide field on frontend', 'bookingpress-appointment-booking' ); ?></label>
																		<el-switch v-model="field_settings_data.is_hide" class="bpa-swtich-control"></el-switch>
																	</div>
																	<div class="bpa-customize--edit-label-popover--actions">
																		<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="closeFieldSettings(field_settings_data.field_name)"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></el-button>
																	</div>
																</el-container>
																<el-button class="bpa-btn bpa-btn--icon-without-box" slot="reference">
																	<span class="material-icons-round">settings</span>
																</el-button>
															</el-popover>
														</div>
													</div>
												</div>
												<div class="bpa-cfs-ic--body">
													<div class="bpa-cfs-ic--body__field-preview">
														<span class="bpa-form-label" v-text="field_settings_data.label"></span>
														<el-input class="bpa-form-control" :placeholder="field_settings_data.placeholder"></el-input>
													</div>
												</div>
											</div>
										</el-col>
									</draggable>
								</el-row>
								<br/><br/>
								<el-row>
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-for="field_settings_data in field_settings_fields" v-if="field_settings_data.field_name == 'note'">
										<div class="bpa-cfs-item-card list-group-item">
											<div class="bpa-cfs-ic--head">
												<div class="bpa-cfs-ic--head__type-label">
													<p>{{ field_settings_data.field_type }}</p>
												</div>
												<div class="bpa-cfs-ic--head__field-controls">
													<div class="bpa-cfs-ic--head__fc-swtich">
														<el-switch v-model="field_settings_data.is_required" class="bpa-swtich-control" v-if="field_settings_data.field_name == 'email_address'" disabled></el-switch>
														<el-switch v-model="field_settings_data.is_required" class="bpa-swtich-control" v-else></el-switch>
														<label>Required</label>
													</div>
													<div class="bpa-cfs-ic--head__fc-actions">
														<el-popover placement="bottom" v-model="field_settings_data.is_edit">
															<el-container class="bpa-field-settings-edit-container">
																<div class="bpa-fs-item-settings-form-control-item">
																	<label class="bpa-form-label"><?php esc_html_e( 'Label', 'bookingpress-appointment-booking' ); ?></label>
																	<el-input class="bpa-form-control" v-model="field_settings_data.label"></el-input>
																</div>
																<div class="bpa-fs-item-settings-form-control-item">
																	<label class="bpa-form-label"><?php esc_html_e( 'Placeholder', 'bookingpress-appointment-booking' ); ?></label>
																	<el-input class="bpa-form-control" v-model="field_settings_data.placeholder"></el-input>
																</div>
																<div class="bpa-fs-item-settings-form-control-item">
																	<label class="bpa-form-label"><?php esc_html_e( 'Error Message', 'bookingpress-appointment-booking' ); ?></label>
																	<el-input class="bpa-form-control" v-model="field_settings_data.error_message"></el-input>
																</div>
																<div class="bpa-fs-item-settings-form-control-item" v-if="field_settings_data.field_name != 'email_address'">
																	<label class="bpa-form-label"><?php esc_html_e( 'Hide field on frontend', 'bookingpress-appointment-booking' ); ?></label>
																	<el-switch v-model="field_settings_data.is_hide" class="bpa-swtich-control"></el-switch>
																</div>
																<div class="bpa-customize--edit-label-popover--actions">
																	<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="closeFieldSettings(field_settings_data.field_name)"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></el-button>
																</div>
															</el-container>
															<el-button class="bpa-btn bpa-btn--icon-without-box" slot="reference">
																<span class="material-icons-round">settings</span>
															</el-button>
														</el-popover>
													</div>
												</div>
											</div>
											<div class="bpa-cfs-ic--body">
												<div class="bpa-cfs-ic--body__field-preview">
													<span class="bpa-form-label" v-text="field_settings_data.label"></span>
													<el-input class="bpa-form-control" :placeholder="field_settings_data.placeholder"></el-input>
												</div>
											</div>
										</div>
									</el-col>
								</el-row>
							</div>
						</el-col>
					</el-row>
				</el-tab-pane>
				<el-tab-pane name="my_bookings">
					<template #label>
						<span><?php esc_html_e( 'My Bookings', 'bookingpress-appointment-booking' ); ?></span>
					</template>
					<div class="bpa-customize-step-content-container __bpa-is-sidebar">
					<el-row type="flex">
							<el-col :xs="24" :sm="24" :md="24" :lg="4" :xl="4">
							<div class="bpa-customize-step-side-panel">
								<div class="bpa-cs-sp--heading">
									<h4><?php esc_html_e( 'Layout Styling', 'bookingpress-appointment-booking' ); ?></h4>
									<el-popconfirm 
										confirm-button-text='<?php esc_html_e( 'Yes', 'bookingpress-appointment-booking' ); ?>' 
										cancel-button-text='<?php esc_html_e( 'No', 'bookingpress-appointment-booking' ); ?>' 
										icon="false" 
										@confirm="bpa_reset_mybookingform()"
										title="<?php esc_html_e( 'Are you sure you want to reset the settings?', 'bookingpress-appointment-booking' ); ?>" @confirm="delete_breakhour(break_data.start_time, break_data.end_time, work_hours_day.day_name)" 
										confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
										cancel-button-type="bpa-btn bpa-btn__small">
											<el-button class="bpa-btn bpa-btn__small" slot="reference">
												<?php esc_html_e( 'Reset', 'bookingpress-appointment-booking' ); ?>
											</el-button>
									</el-popconfirm>
								</div>
								<div class="bpa-cs-sp-sub-module">
									<h5><?php esc_html_e( 'Color Settings', 'bookingpress-appointment-booking' ); ?></h5>
									<div class="bpa-sm--item --bpa-is-flexbox">
										<label class="bpa-form-label"><?php esc_html_e( 'Background Color', 'bookingpress-appointment-booking' ); ?></label>
										<el-color-picker v-model="my_booking_selected_colorpicker_values.background_color"></el-color-picker>
									</div>
									<div class="bpa-sm--item --bpa-is-flexbox">
										<label class="bpa-form-label"><?php esc_html_e( 'Row Background Color', 'bookingpress-appointment-booking' ); ?></label>
										<el-color-picker v-model="my_booking_selected_colorpicker_values.row_background_color"></el-color-picker>
									</div>
									<div class="bpa-sm--item --bpa-is-flexbox">
										<label class="bpa-form-label"><?php esc_html_e( 'Title Color', 'bookingpress-appointment-booking' ); ?></label>
										<el-color-picker v-model="my_booking_selected_colorpicker_values.label_title_color"></el-color-picker>
									</div>
									<div class="bpa-sm--item --bpa-is-flexbox">
										<label class="bpa-form-label"><?php esc_html_e( 'Content Color', 'bookingpress-appointment-booking' ); ?></label>
										<el-color-picker v-model="my_booking_selected_colorpicker_values.content_color"></el-color-picker>
									</div>
									<div class="bpa-cs-sp-sub-module bpa-cs-sp--form-controls">
										<h5><?php esc_html_e( 'Font Settings', 'bookingpress-appointment-booking' ); ?></h5>
										<div class="bpa-sm--item">
											<label class="bpa-form-label"><?php esc_html_e( 'Title Size', 'bookingpress-appointment-booking' ); ?></label>
												<el-select v-model="my_booking_selected_font_values.title_font_size" class="bpa-form-control" popper-class="bpa-el-select--is-with-navbar">
												<el-option label="<?php esc_html( '10' ); ?>" value="10"></el-option>
												<el-option label="<?php esc_html( '11' ); ?>" value="11"></el-option>
												<el-option label="<?php esc_html( '12' ); ?>" value="12"></el-option>
												<el-option label="<?php esc_html( '13' ); ?>" value="13"></el-option>
												<el-option label="<?php esc_html( '14' ); ?>" value="14"></el-option>
												<el-option label="<?php esc_html( '16' ); ?>" value="16"></el-option>
												<el-option label="<?php esc_html( '18' ); ?>" value="18"></el-option>
												<el-option label="<?php esc_html( '20' ); ?>" value="20"></el-option>
												<el-option label="<?php esc_html( '22' ); ?>" value="22"></el-option>
												<el-option label="<?php esc_html( '24' ); ?>" value="24"></el-option>
												<el-option label="<?php esc_html( '26' ); ?>" value="26"></el-option>
											</el-select>
										</div>
										<div class="bpa-sm--item">
											<label class="bpa-form-label"><?php esc_html_e( 'Title Font Family', 'bookingpress-appointment-booking' ); ?></label>
												<el-select v-model="my_booking_selected_font_values.title_font_family" class="bpa-form-control" popper-class="bpa-el-select--is-with-navbar" filterable>
												<el-option-group v-for="item_data in fonts_list" :key="item_data.label" :label="item_data.label">
													<el-option v-for="item in item_data.options" :key="item" :label="item" :value="item"></el-option>
												</el-option-group>
											</el-select>
										</div>
										<div class="bpa-sm--item">
											<label class="bpa-form-label"><?php esc_html_e( 'Content Size', 'bookingpress-appointment-booking' ); ?></label>
												<el-select v-model="my_booking_selected_font_values.content_font_size" class="bpa-form-control" popper-class="bpa-el-select--is-with-navbar">
												<el-option label="<?php esc_html( '10' ); ?>" value="10"></el-option>
												<el-option label="<?php esc_html( '11' ); ?>" value="11"></el-option>
												<el-option label="<?php esc_html( '12' ); ?>" value="12"></el-option>
												<el-option label="<?php esc_html( '13' ); ?>" value="13"></el-option>
												<el-option label="<?php esc_html( '14' ); ?>" value="14"></el-option>
												<el-option label="<?php esc_html( '16' ); ?>" value="16"></el-option>
												<el-option label="<?php esc_html( '18' ); ?>" value="18"></el-option>
												<el-option label="<?php esc_html( '20' ); ?>" value="20"></el-option>
												<el-option label="<?php esc_html( '22' ); ?>" value="22"></el-option>
												<el-option label="<?php esc_html( '24' ); ?>" value="24"></el-option>
												<el-option label="<?php esc_html( '26' ); ?>" value="26"></el-option>
											</el-select>
										</div>
										<div class="bpa-sm--item">
											<label class="bpa-form-label"><?php esc_html_e( 'Content Font Family', 'bookingpress-appointment-booking' ); ?></label>
												<el-select v-model="my_booking_selected_font_values.content_font_family" class="bpa-form-control" popper-class="bpa-el-select--is-with-navbar" filterable>
												<el-option-group v-for="item_data in fonts_list" :key="item_data.label" :label="item_data.label">
													<el-option v-for="item in item_data.options" :key="item" :label="item" :value="item"></el-option>
												</el-option-group>
											</el-select>
										</div>
										<div class="bpa-sm--item">
											<label class="bpa-form-label"><?php esc_html_e( 'Custom CSS', 'bookingpress-appointment-booking' ); ?></label>
											<el-input type="textarea" :rows="4" class="bpa-form-control" v-model="my_booking_selected_colorpicker_values.custom_css"/>
										</div>
									</div>
								</div>
							</div>
						</el-col>
							<el-col :xs="24" :sm="24" :md="24" :lg="16" :xl="16">
							<div class="bpa-customize-booking-form-preview-container --bpa-my-bookings">
								<div class="bpa-cmb-step-preview" :style="{ 'background': my_booking_selected_colorpicker_values.background_color }">
									<div class="bpa-front-ma-header">
										<div class="bpa-front-ma-header--left">
											<el-row type="flex" class="bpa-cbf--preview__module-heading">
												<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">											
													<h2 class="bpa-module-heading" :style="{'color': my_booking_selected_colorpicker_values.label_title_color, 'font-size': my_booking_selected_font_values.title_font_size+'px', 'font-family': my_booking_selected_font_values.title_font_family }"> {{ my_booking_field_settings.mybooking_title_text }} </h2>
													<el-popover placement="bottom" v-model="my_booking_field_settings.is_edit_mybooking_title">
														<el-container class="bpa-customize--edit-label-popover">
															<div>
																<el-input class="bpa-form-control" v-model="my_booking_field_settings.mybooking_title_text"></el-input>
															</div>
															<div class="bpa-customize--edit-label-popover-actions">
																<el-button class="bpa-btn bpa-btn__small" @click="my_booking_field_settings.is_edit_mybooking_title = false"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
																<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="my_booking_field_settings.is_edit_mybooking_title = false"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
															</div>
														</el-container>
														<el-button class="bpa-btn bpa-btn__small bpa-btn--icon-without-box" slot="reference">
															<span class="material-icons-round">mode_edit</span>
														</el-button>
													</el-popover>
												</el-col>
											</el-row>
										</div>
										<div class="bpa-front-ma-header--right" v-if="my_booking_field_settings.hide_customer_details == false">
											<div class="bpa-front-ma-header__profile-dropdown">
												<div class="bpa-front-ma-header__profile-dropdown--img">
													<img src="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/profile-img.jpg' ); ?>" alt="">
												</div>
												<div class="bpa-front-ma-header__profile-dropdown--body">
													<h4 :style="{'color': my_booking_selected_colorpicker_values.content_color, 'font-size': my_booking_selected_font_values.content_font_size+'px', 'font-family': my_booking_selected_font_values.content_font_family}" >Jerome Bell</h4>
													<p :style="{'color': my_booking_selected_colorpicker_values.content_color, 'font-size': my_booking_selected_font_values.content_font_size+'px', 'font-family': my_booking_selected_font_values.content_font_family }">Jerome.bell15@gmail.com</p>
												</div>
											</div>
										</div>		
									</div>
									<div class="bpa-front-ma-lists">
										<div class="bpa-front-ma-list--filter-wrapper">
											<el-row type="flex" :gutter="32">																			
												<el-col :xs="8" :sm="8" :md="8" :lg="10	" :xl="10">
														<el-date-picker class="bpa-form-control bpa-form-control--date-range-picker" format="<?php echo esc_html( $bookingpress_common_date_format ); ?>" 
														v-model="appointment_date_range" type="daterange" start-placeholder="Start date" end-placeholder="End date"
														:popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar"> </el-date-picker>
												</el-col>
												<el-col :xs="8" :sm="8" :md="8" :lg="9" :xl="9">
													<el-input class="bpa-form-control" placeholder="<?php esc_html_e( 'Search appointments', 'bookingpress-appointment-booking' ); ?>" v-if="my_booking_field_settings.hide_search_bar == false" ></el-input>														
												</el-col>
												<el-col :xs="8" :sm="8" :md="8" :lg="6" :xl="6">
													<div class="bpa-tf-btn-group">
														<el-button class="bpa-btn bpa-btn__medium bpa-btn--full-width">
															<?php esc_html_e( 'Reset', 'bookingpress-appointment-booking' ); ?>
														</el-button>
															<el-button class="bpa-btn bpa-btn__medium bpa-btn--full-width bpa-btn--primary">
															<?php esc_html_e( 'Apply', 'bookingpress-appointment-booking' ); ?>
														</el-button>
													</div>
												</el-col>
											</el-row>
										</div>
										<div class="bpa-front-ma-list--items">
											<div class="bpa-front-ma-list--item-row">
												<div class="bpa-front-ma-list--item__heading">
													<h6 :style="{'color': my_booking_selected_colorpicker_values.label_title_color, 'font-size': my_booking_selected_font_values.title_font_size+'px', 'font-family': my_booking_selected_font_values.title_font_family }" v-text="my_booking_date_text.bookingpress_date_format_1"></h6>
												</div>
												<div class="bpa-front-ma-list--item__item-card" :style="{ 'background': my_booking_selected_colorpicker_values.row_background_color }">
													<el-row type="flex" >
														<el-col	:xs="8" :sm="8" :md="4" :lg="6" :xl="6" >
															<h4 :style="{'color': my_booking_selected_colorpicker_values.content_color, 'font-size': my_booking_selected_font_values.content_font_size+'px', 'font-family': my_booking_selected_font_values.content_font_family }" >Nutrition Consulting</h4>
														</el-col>						
														<el-col	:xs="8" :sm="8" :md="4" :lg="4" :xl="4">
															<p :style="{'color': my_booking_selected_colorpicker_values.content_color, 'font-size': my_booking_selected_font_values.content_font_size+'px', 'font-family': my_booking_selected_font_values.content_font_family }" >
																<span class="material-icons-round">access_time</span>
																2.5 Hours
															</p>
														</el-col>						
														<el-col	:xs="8" :sm="8" :md="4" :lg="4" :xl="4">
															<p :style="{'color': my_booking_selected_colorpicker_values.content_color, 'font-size': my_booking_selected_font_values.content_font_size+'px', 'font-family': my_booking_selected_font_values.content_font_family }" >
																<span class="material-icons-round">calendar_today</span>																	
																<label v-text="my_booking_date_text.bookingpress_date_format_2"></label>
															</p>
														</el-col>						
														<el-col	:xs="8" :sm="8" :md="4" :lg="4" :xl="4">
															<p :style="{'color': my_booking_selected_colorpicker_values.content_color, 'font-size': my_booking_selected_font_values.content_font_size+'px', 'font-family': my_booking_selected_font_values.content_font_family }" >$149.00</p>
														</el-col>						
														<el-col	:xs="8" :sm="8" :md="4" :lg="4" :xl="4">
															<el-tag class="bpa-front-pill --warning" :style="{'color': my_booking_selected_colorpicker_values.content_color, 'font-size': my_booking_selected_font_values.content_font_size+'px', 'font-family': my_booking_selected_font_values.content_font_family }" >Pending</el-tag>
														</el-col>
														<el-col	class="bpa-front-ma-list--item__item-card--actions" :xs="8" :sm="8" :md="4" :lg="2" :xl="2">
															<el-popconfirm 
																confirm-button-text='<?php esc_html_e( 'Yes', 'bookingpress-appointment-booking' ); ?>' 
																cancel-button-text='<?php esc_html_e( 'No', 'bookingpress-appointment-booking' ); ?>' 
																icon="false" 
																title="<?php esc_html_e( 'Are you sure you want to cancel this appointment?', 'bookingpress-appointment-booking' ); ?>" 
																@confirm="" 
																	confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
																cancel-button-type="bpa-btn bpa-btn__small"
																v-if="my_booking_field_settings.allow_to_cancel_appointment == true">
																<el-button type="text" slot="reference" class="bpa-btn bpa-btn--icon-without-box __danger">
																	<span class="material-icons-round">event_busy</span>
																</el-button>
															</el-popconfirm>
														</el-col>						
													</el-row>
												</div>
											</div>
											<div class="bpa-front-ma-list--item-row">
												<div class="bpa-front-ma-list--item__heading">
													<h6 :style="{'color': my_booking_selected_colorpicker_values.label_title_color, 'font-size': my_booking_selected_font_values.title_font_size+'px', 'font-family': my_booking_selected_font_values.title_font_family }" v-text="my_booking_date_text.bookingpress_date_format_3"> </h6>
												</div>
												<div class="bpa-front-ma-list--item__item-card" :style="{ 'background': my_booking_selected_colorpicker_values.row_background_color }">
													<el-row type="flex">
														<el-col	:xs="8" :sm="8" :md="4" :lg="6" :xl="6">
															<h4 :style="{'color': my_booking_selected_colorpicker_values.content_color, 'font-size': my_booking_selected_font_values.content_font_size+'px', 'font-family': my_booking_selected_font_values.content_font_family }" >Nutrition Consulting</h4>
														</el-col>						
														<el-col	:xs="8" :sm="8" :md="4" :lg="4" :xl="4">
															<p :style="{'color': my_booking_selected_colorpicker_values.content_color,'font-size': my_booking_selected_font_values.content_font_size+'px', 'font-family': my_booking_selected_font_values.content_font_family }" >
																<span class="material-icons-round">access_time</span>
																2.5 Hours
															</p>
														</el-col>						
														<el-col	:xs="8" :sm="8" :md="4" :lg="4" :xl="4">
															<p :style="{'color': my_booking_selected_colorpicker_values.content_color,'font-size': my_booking_selected_font_values.content_font_size+'px', 'font-family': my_booking_selected_font_values.content_font_family }" >
																<span class="material-icons-round">calendar_today</span>
																<label v-text="my_booking_date_text.bookingpress_date_format_4"></label>
															</p>
														</el-col>						
														<el-col	:xs="8" :sm="8" :md="4" :lg="4" :xl="4">
															<p :style="{'color': my_booking_selected_colorpicker_values.content_color,'font-size': my_booking_selected_font_values.content_font_size+'px', 'font-family': selected_font_values.content_font_family }" >$149.00</p>
														</el-col>						
														<el-col	:xs="8" :sm="8" :md="4" :lg="4" :xl="4">
															<el-tag class="bpa-front-pill --info" :style="{'color': my_booking_selected_colorpicker_values.content_color,'font-size': my_booking_selected_font_values.content_font_size+'px', 'font-family': my_booking_selected_font_values.content_font_family }" >Cancelled</el-tag>
														</el-col>						
													</el-row>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</el-col>
							<el-col :xs="24" :sm="24" :md="24" :lg="4" :xl="4">
							<div class="bpa-customize-step-side-panel">
								<div class="bpa-cs-sp--heading">
									<h4><?php esc_html_e( 'Content Settings', 'bookingpress-appointment-booking' ); ?></h4>
									<!-- <el-button class="bpa-btn bpa-btn__small" @click="bpa_reset_content_settings">
										<?php esc_html_e( 'Reset', 'bookingpress-appointment-booking' ); ?>
									</el-button> -->
								</div>
								<div class="bpa-cs-sp-sub-module bpa-sm--swtich">
									<div class="bpa-sm--item --bpa-is-flexbox">
										<label class="bpa-form-label"><?php esc_html_e( 'Hide Customer Details', 'bookingpress-appointment-booking' ); ?></label>
										<el-switch v-model="my_booking_field_settings.hide_customer_details" class="bpa-swtich-control"></el-switch>
									</div>									
									<div class="bpa-sm--item --bpa-is-flexbox">
										<label class="bpa-form-label"><?php esc_html_e( 'Hide Search Bar', 'bookingpress-appointment-booking' ); ?></label>
										<el-switch v-model="my_booking_field_settings.hide_search_bar" class="bpa-swtich-control"></el-switch>
									</div>
									<div class="bpa-sm--item --bpa-is-flexbox">
										<label class="bpa-form-label"><?php esc_html_e( 'Allow customers to cancel their appointment', 'bookingpress-appointment-booking' ); ?></label>
										<el-switch v-model="my_booking_field_settings.allow_to_cancel_appointment" class="bpa-swtich-control"></el-switch>
									</div>									
								</div>
								<div class="bpa-cs-sp-sub-module bpa-cs-sp--form-controls">
									<div class="bpa-sm--item">
										<label class="bpa-form-label"><?php esc_html_e( 'Default Date Format', 'bookingpress-appointment-booking' ); ?></label>
										<el-select v-model="my_booking_field_settings.Default_date_formate" @change="bookingpress_set_date_format" class="bpa-form-control">						
											<el-option label="<?php esc_html( 'F j, Y' ); ?>" value="F j, Y"></el-option>
											<el-option label="<?php esc_html( 'Y-m-d' ); ?>" value="Y-m-d"></el-option>
											<el-option label="<?php esc_html( 'm/d/Y' ); ?>" value="m/d/Y"></el-option>
											<el-option label="<?php esc_html( 'd/m/Y' ); ?>" value="d/m/Y"></el-option>
										</el-select>
									</div>
								</div>
							</div>
						</el-col>
					</el-row>
					</div>
				</el-tab-pane>
			</el-tabs>
		</div>
	</el-container>	
</el-main>
