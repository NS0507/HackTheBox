<el-tab-pane class="bpa-tabs--v_ls__tab--pane-body" label="notifications" data-tab_name="notification_settings">
	<span slot="label">
		<i class="material-icons-round">notifications_active</i>
		<?php esc_html_e( 'Notifications', 'bookingpress-appointment-booking' ); ?>
	</span>
	<div class="bpa-default-card bpa-general-settings-tabs--pb__card bpa-notification-settings-tabs--pb__card">
		<el-row type="flex" class="bpa-mlc-head-wrap-settings bpa-gs-tabs--pb__heading">
			<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-gs-tabs--pb__heading--left">
				<h1 class="bpa-page-heading"><?php esc_html_e( 'Notifications', 'bookingpress-appointment-booking' ); ?></h1>				
			</el-col>
			<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
				<div class="bpa-hw-right-btn-group bpa-gs-tabs--pb__btn-group">					
					<el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveSettingsData('notification_setting_form','notification_setting')" :disabled="is_disabled" >					
					  <span class="bpa-btn__label"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></span>
					  <div class="bpa-btn--loader__circles">				    
						  <div></div>
						  <div></div>
						  <div></div>
					  </div>
					</el-button>
					<el-button class="bpa-btn" @click="openNeedHelper('list_notification_settings', 'notification_settings', '<?php esc_html_e( 'Notification Settings', 'bookingpress-appointment-booking' ); ?>')">
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
		<div class="bpa-gs--tabs-pb__content-body">			
			<el-form :rules="rules_notification" ref="notification_setting_form" :model="notification_setting_form" @submit.native.prevent >
				<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
					<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
						<h4><?php esc_html_e( 'Mail Service', 'bookingpress-appointment-booking' ); ?></h4>										
					</el-col>
					<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">														
						<el-radio v-model="notification_setting_form.selected_mail_service" label="php_mail">PHP Mail</el-radio>
						<el-radio v-model="notification_setting_form.selected_mail_service" label="wp_mail">WP Mail</el-radio>
						<el-radio v-model="notification_setting_form.selected_mail_service" label="smtp">SMTP</el-radio>							
					</el-col>
				</el-row>
				<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
					<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
						<h4><?php esc_html_e( 'Sender Name', 'bookingpress-appointment-booking' ); ?></h4>					
					</el-col>
					<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >
						<el-form-item prop="sender_name">
							<el-input class="bpa-form-control" v-model="notification_setting_form.sender_name" placeholder="<?php esc_html_e( 'Enter sender name', 'bookingpress-appointment-booking' ); ?>"></el-input>		
						</el-form-item>
					</el-col>
				</el-row>
				<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
					<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
						<h4><?php esc_html_e( 'Sender Email', 'bookingpress-appointment-booking' ); ?></h4>		
					</el-col>
					<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">							
						<el-form-item prop="sender_email">	
							<el-input class="bpa-form-control" type="email" v-model="notification_setting_form.sender_email" placeholder="<?php esc_html_e( 'example@example.com', 'bookingpress-appointment-booking' ); ?>"></el-input>		
						</el-form-item>	
					</el-col>
				</el-row>
				<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
					<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
						<h4><?php esc_html_e( 'Admin Email', 'bookingpress-appointment-booking' ); ?></h4>		
					</el-col>
					<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">							
						<el-form-item prop="admin_email">	
							<el-input class="bpa-form-control" type="email" v-model="notification_setting_form.admin_email" placeholder="<?php esc_html_e( 'Enter admin email', 'bookingpress-appointment-booking' ); ?>"></el-input>		
						</el-form-item>	
					</el-col>
				</el-row>				
				<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" v-if="notification_setting_form.selected_mail_service == 'smtp'">
					<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
						<h4> <?php esc_html_e( 'SMTP Host', 'bookingpress-appointment-booking' ); ?></h4>					
					</el-col>
					<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">
						<el-row :gutter="24">
							<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">							
								<el-form-item prop="smtp_host">	
									<el-input class="bpa-form-control" v-model="notification_setting_form.smtp_host" placeholder="<?php esc_html_e( 'SMTP Host', 'bookingpress-appointment-booking' ); ?>"></el-input>		
								</el-form-item>
							</el-col>
						</el-row>
					</el-col>
				</el-row>
				<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" v-if="notification_setting_form.selected_mail_service == 'smtp'">
					<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
						<h4> <?php esc_html_e( 'SMTP Port', 'bookingpress-appointment-booking' ); ?></h4>					
					</el-col>
					<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">
						<el-row :gutter="24">
							<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">							
								<el-form-item prop="smtp_port">	
									<el-input class="bpa-form-control" v-model="notification_setting_form.smtp_port" placeholder="<?php esc_html_e( 'SMTP Port', 'bookingpress-appointment-booking' ); ?>"></el-input>		
								</el-form-item>	
							</el-col>
						</el-row>
					</el-col>
				</el-row>
				<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" v-if="notification_setting_form.selected_mail_service == 'smtp'">
					<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
						<h4> <?php esc_html_e( 'SMTP Secure', 'bookingpress-appointment-booking' ); ?></h4>					
					</el-col>
					<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">
						<el-row :gutter="24">
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">							
								<el-form-item prop="smtp_secure">	
									<el-select class="bpa-form-control" placeholder="<?php esc_html_e( 'Select Secure', 'bookingpress-appointment-booking' ); ?>" v-model="notification_setting_form.smtp_secure">
										<el-option v-for="item in default_smtp_secure_options" :key="item.text" :label="item.text" :value="item.value"></el-option>
									</el-select>								
								</el-form-item>
							</el-col>
						</el-row>
					</el-col>
				</el-row>	
				<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" v-if="notification_setting_form.selected_mail_service == 'smtp'">
					<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
						<h4> <?php esc_html_e( 'SMTP Username', 'bookingpress-appointment-booking' ); ?></h4>					
					</el-col>
					<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">
						<el-row :gutter="24">
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">							
								<el-form-item prop="smtp_username">	
									<el-input class="bpa-form-control" v-model="notification_setting_form.smtp_username" placeholder="<?php esc_html_e( 'SMTP Username', 'bookingpress-appointment-booking' ); ?>"></el-input>		
								</el-form-item>	
							</el-col>
						</el-row>
					</el-col>
				</el-row>
				<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" v-if="notification_setting_form.selected_mail_service == 'smtp'">	
					<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
						<h4> <?php esc_html_e( 'SMTP Password', 'bookingpress-appointment-booking' ); ?></h4>				
					</el-col>
					<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">
						<el-row :gutter="24">
							<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">							
								<el-form-item prop="smtp_password">	
									<el-input class="bpa-form-control" type="password" v-model="notification_setting_form.smtp_password" placeholder="<?php esc_html_e( 'SMTP Password', 'bookingpress-appointment-booking' ); ?>"></el-input>	
								</el-form-item>
							</el-col>
						</el-row>
					</el-col>
				</el-row>
			</el-form></br></br>
			<div class="bpa-ns--sub-module__card" v-if="notification_setting_form.selected_mail_service == 'smtp'">
				<el-form :rules="rules_smtp_test_mail" ref="notification_smtp_test_mail_form" :model="notification_smtp_test_mail_form" @submit.native.prevent>					
					<h4><?php esc_html_e( 'Send Test Email Notification', 'bookingpress-appointment-booking' ); ?></h4>
					<el-row type="flex" class="bpa-ns--sub-module__card--row">	
						<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
							<h4> <?php esc_html_e( 'To', 'bookingpress-appointment-booking' ); ?></h4>
						</el-col>
						<el-col :xs="12" :sm="12" :md="12" :lg="24" :xl="16">							
							<el-form-item prop="smtp_test_receiver_email">
								<el-input class="bpa-form-control" type="email" v-model="notification_smtp_test_mail_form.smtp_test_receiver_email"></el-input>	
							</el-form-item>
						</el-col>
					</el-row></br>
					<el-row type="flex" class="bpa-ns--sub-module__card--row">	
						<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
							<h4><?php esc_html_e( 'Message', 'bookingpress-appointment-booking' ); ?></h4>
						</el-col>
						<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">
							<el-form-item prop="smtp_test_msg">	
								<el-input class="bpa-form-control" type="textarea" v-model="notification_smtp_test_mail_form.smtp_test_msg"></el-input>	
							</el-form-item>
						</el-col>
					</el-row>
					<el-row type="flex">
						<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
							<div class="bpa-ns--sub-module__card--row --is-button">
								<el-button class="bpa-btn bpa-btn--primary bpa-btn__medium" :class="(is_display_send_test_mail_loader == '1') ? 'bpa-btn--is-loader' : ''" :disabled="is_disable_send_test_email_btn" @click="bookingpress_send_test_email" >					
								  <span class="bpa-btn__label"><?php esc_html_e( 'Send Test Email', 'bookingpress-appointment-booking' ); ?></span>
								  <div class="bpa-btn--loader__circles">				    
									  <div></div>
									  <div></div>
									  <div></div>
								  </div>
								</el-button>	
							</div>
						</el-col>
					</el-row>					
					<el-row type="flex" v-if="notification_setting_form.selected_mail_service == 'smtp'">							
						<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
							<div class="bpa-toast-notification --error" :class="succesfully_send_test_email == 1 ? '--success' : ''" v-if="succesfully_send_test_email == 1 || error_send_test_email == 1">
								<label class="bpa-text--primary-color" v-if="succesfully_send_test_email == 1">
									<?php esc_html_e( 'Test Email Sent Successfully', 'bookingpress-appointment-booking' ); ?>
								</label>
								<label class="bpa-text--danger-color" v-if="error_send_test_email == 1" > {{error_text_of_test_email}}
								</label>
								<el-link @click="open_smtp_error_modal()" v-if="error_send_test_email == 1 && smtp_mail_error_text != ''"><?php esc_html_e( 'Click here to see the full log', 'bookingpress-appointment-booking' ); ?></el-link>
							</div>							
						</el-col>
					</el-row>									
				</el-form>
			</div>	
		</div>	
	</div>
</el-tab-pane>

<el-dialog custom-class="bpa-dialog bpa-dialog--smtp-notification-settings" title="" :visible.sync="smtp_error_modal" :visible.sync="centerDialogVisible" close-on-press-escape="close_modal_on_esc" :modal="smtp_error_modal">
	<div class="bpa-dialog-heading">
		<el-row type="flex">
			<el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
				<h1 class="bpa-page-heading"><?php esc_html_e( 'SMTP Test Full Log', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>
			<el-col :xs="12" :sm="12" :md="8" :lg="8" :xl="8">
				<div class="bpa-hw-right-btn-group">
					<el-button class="bpa-btn bpa-btn__medium" @click="close_smtp_error_modal()">
						<span>close</span>
					</el-button>
				</div>
			</el-col>
		</el-row>
	</div>
	<div class="bpa-dialog-body">
		<div class="bpa-dialog--sns__body" v-html="smtp_mail_error_text">
		</div>
	</div>	
</el-dialog>