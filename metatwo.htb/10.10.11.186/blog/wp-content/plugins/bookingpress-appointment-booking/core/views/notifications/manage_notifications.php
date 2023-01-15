<el-main class="bpa-email-notifications-container" id="all-page-main-container">
	<div class="bpa-back-loader-container" id="bpa-page-loading-loader">
		<div class="bpa-back-loader"></div>
	</div>
	<div id="bpa-main-container">	
		<el-row type="flex" :gutter="64">
			<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="6">
				<el-container class="bpa-default-card bpa-en-left">
					<div class="bpa-en-left__item">
						<div class="bpa-en-left__item-head">
							<h4 class="bpa-page-heading"><?php esc_html_e( 'System Notifications', 'bookingpress-appointment-booking' ); ?></h4>
							<el-button class="bpa-btn bpa-btn__small" @click="openNeedHelper('list_notifications', 'notifications', '<?php esc_html_e('Notifications', 'bookingpress-appointment-booking'); ?>')">
								<span class="material-icons-round">help</span>
								<?php esc_html_e( 'Need help?', 'bookingpress-appointment-booking' ); ?>
							</el-button>
						</div>
						<div class="bpa-en-left__item-body">
							<div class="bpa-en-left_item-body--list">
								<div class="bpa-en-left_item-body--list__item" :class="bookingpress_active_email_notification == 'appointment_approved' ? '__bpa-is-active' : ''" ref="appointmentApproved" @click="bookingpress_select_email_notification('Appointment Approved', 'appointment_approved')">
									<p><?php esc_html_e( 'Appointment Approved', 'bookingpress-appointment-booking' ); ?></p>
									<span class="material-icons-round --bpa-item-status is-enabled" v-if="default_notification_status['customer']['appointment_approved'] == true || default_notification_status['employee']['appointment_approved'] == true " >circle</span>
									<span class="material-icons-round --bpa-item-status" v-else>circle</span>
								</div>
								<div class="bpa-en-left_item-body--list__item" :class="bookingpress_active_email_notification == 'appointment_pending' ? '__bpa-is-active' : ''" ref="appointmentPending" @click="bookingpress_select_email_notification('Appointment Pending', 'appointment_pending')">
									<p><?php esc_html_e( 'Appointment Pending', 'bookingpress-appointment-booking' ); ?></p>
									<span class="material-icons-round --bpa-item-status is-enabled" v-if="default_notification_status['customer']['appointment_pending'] == true || default_notification_status['employee']['appointment_pending'] == true" >circle</span>
									<span class="material-icons-round --bpa-item-status" v-else>circle</span>
								</div>
								<div class="bpa-en-left_item-body--list__item" :class="bookingpress_active_email_notification == 'appointment_rejected' ? '__bpa-is-active' : ''" ref="appointmentRejected" @click="bookingpress_select_email_notification('Appointment Rejected', 'appointment_rejected')">
									<p><?php esc_html_e( 'Appointment Rejected', 'bookingpress-appointment-booking' ); ?></p>
									<span class="material-icons-round --bpa-item-status is-enabled" v-if="default_notification_status['customer']['appointment_rejected'] == true || default_notification_status['employee']['appointment_rejected'] == true">circle</span>
									<span class="material-icons-round --bpa-item-status" v-else>circle</span>
								</div>
								<div class="bpa-en-left_item-body--list__item" :class="bookingpress_active_email_notification == 'appointment_canceled' ? '__bpa-is-active' : ''" ref="appointmentCanceled" @click="bookingpress_select_email_notification('Appointment Canceled', 'appointment_canceled')">
									<p><?php esc_html_e( 'Appointment Canceled', 'bookingpress-appointment-booking' ); ?></p>
									<span class="material-icons-round --bpa-item-status is-enabled" v-if="default_notification_status['customer']['appointment_canceled'] == true || default_notification_status['employee']['appointment_canceled'] == true">circle</span>
									<span class="material-icons-round --bpa-item-status" v-else>circle</span>
								</div>
							</div>
						</div>
					</div>
				</el-container>
			</el-col>
			<el-col :xs="24" :sm="24" :md="24" :lg="16" :xl="18">
				<div class="bpa-default-card bpa-en-body-card">
					<div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
						<div class="bpa-back-loader"></div>
					</div>
					<el-row type="flex" class="bpa-mlc-head-wrap">
						<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" class="bpa-gs-tabs--pb__heading--left">
							<h1 class="bpa-page-heading" v-text="bookingpress_email_notification_edit_text"></h1>
						</el-col>					
					</el-row>
					<el-row type="flex" class="bpa-en-body-card__content">
						<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
							<el-row type="flex">
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<el-tabs class="bpa-tabs" v-model="activeTabName" @tab-click="bookingpress_change_tab">
										<el-tab-pane name="customer">
											<template #label>
												<span><?php esc_html_e( 'To Customer', 'bookingpress-appointment-booking' ); ?></span>
											</template>
										</el-tab-pane>
										<el-tab-pane name="employee">
											<template #label>
												<span><?php esc_html_e( 'To Admin', 'bookingpress-appointment-booking' ); ?></span>
											</template>
										</el-tab-pane>
									</el-tabs>
								</el-col>
							</el-row>
							<el-row type="flex">
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<el-form class="bpa-en-body-card__content--form" id="email_notification_form" ref="email_notification_form" @submit.native.prevent>
										<el-row>
											<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
												<div class="bpa-en-status--swtich-row" v-if="activeTabName == 'customer'">
													<label class="bpa-form-label"><?php esc_html_e( 'Change Notification Status', 'bookingpress-appointment-booking' ); ?></label>
													<el-switch class="bpa-swtich-control" v-model="default_notification_status[activeTabName][bookingpress_active_email_notification]"></el-switch>
												</div>
												<div class="bpa-en-status--swtich-row" v-if="activeTabName == 'employee'">
													<label class="bpa-form-label"><?php esc_html_e( 'Change Notification Status', 'bookingpress-appointment-booking' ); ?></label>
													<el-switch class="bpa-swtich-control" v-model="default_notification_status[activeTabName][bookingpress_active_email_notification]"></el-switch>
												</div>
											</el-col>
											<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
												<el-form-item>
													<template #label>
														<span class="bpa-form-label"><?php esc_html_e( 'Subject', 'bookingpress-appointment-booking' ); ?></span>
													</template>
													<el-input class="bpa-form-control" v-model="bookingpress_email_notification_subject" placeholder="<?php esc_html_e( 'Enter Subject', 'bookingpress-appointment-booking' ); ?>"></el-input>
												</el-form-item>
											</el-col>
											<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
												<el-form-item>
													<template #label>
														<span class="bpa-form-label"><?php esc_html_e( 'Message', 'bookingpress-appointment-booking' ); ?></span>
													</template>
													<?php
														$bookingpress_message_content_editor = array(
															'textarea_name' => 'bookingpress_email_notification_subject_message',
															'media_buttons' => false,
															'textarea_rows' => 10,
															'default_editor' => 'html',
															'editor_css' => '',
															'tinymce' => true,
														);
														wp_editor( '', 'bookingpress_email_notification_subject_message', $bookingpress_message_content_editor );
														?>
												</el-form-item>
											</el-col>
											<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-notification-error-msg">
												<span>
													<?php esc_html_e('Note', 'bookingpress-appointment-booking'); ?>: <?php esc_html_e('Please add <br /> in the email message to add a new line', 'bookingpress-appointment-booking'); ?>. <?php esc_html_e('Enter key will not be considered as new line', 'bookingpress-appointment-booking'); ?>.
												</span>
											</el-col>
											<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
												<el-form-item>
													<template #label>
														<span class="bpa-form-label"><?php esc_html_e( 'Insert email placeholders', 'bookingpress-appointment-booking' ); ?></span>
													</template>													
												</el-form-item>
												<el-row type="flex" :gutter="12">
													<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
														<el-select class="bpa-form-control" placeholder="<?php esc_html_e( 'Customer', 'bookingpress-appointment-booking' ); ?>" 
															@change="bookingpress_insert_placeholder($event)"
															popper-class="bpa-el-select--is-with-navbar">
															<el-option v-for="item in bookingpress_customer_placeholders" :key="item.value" :label="item.name" :value="item.value">
															</el-option>
														</el-select>
													</el-col>
													<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
														<el-select class="bpa-form-control" placeholder="<?php esc_html_e( 'Service', 'bookingpress-appointment-booking' ); ?>" 
															@change="bookingpress_insert_placeholder($event)"
															popper-class="bpa-el-select--is-with-navbar">
															<el-option v-for="item in bookingpress_service_placeholders" :key="item.value" :label="item.name" :value="item.value">
															</el-option>
														</el-select>
													</el-col>
													<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
														<el-select class="bpa-form-control" placeholder="<?php esc_html_e( 'Company', 'bookingpress-appointment-booking' ); ?>" 
															@change="bookingpress_insert_placeholder($event)"
															popper-class="bpa-el-select--is-with-navbar">
															<el-option v-for="item in bookingpress_company_placeholders" :key="item.value" :label="item.name" :value="item.value">
															</el-option>
														</el-select>
													</el-col>
												</el-row>
											</el-col>
										</el-row>
									</el-form>
								</el-col>
							</el-row>
						</el-col>
					</el-row>
					<el-row>
						<el-col>
							<div class="bpa-dialog-footer bpa-en-body-card__footer">
								<div class="bpa-df-btn-group">
									<el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="bookingpress_save_email_notification_data" :disabled="is_disabled" >					
									  <span class="bpa-btn__label"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></span>
									  <div class="bpa-btn--loader__circles">				    
										  <div></div>
										  <div></div>
										  <div></div>
									  </div>
									</el-button>
								</div>

							</div>
						</el-col>
					</el-row>
				</div>
			</el-col>
		</el-row>
	</div>
</el-main>
