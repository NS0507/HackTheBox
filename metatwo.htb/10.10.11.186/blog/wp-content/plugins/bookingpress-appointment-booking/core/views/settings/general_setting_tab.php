<el-tab-pane class="bpa-tabs--v_ls__tab-item--pane-body" data-tab_name="general_settings">
	<span slot="label">
		<i class="material-icons-round">settings</i>
		<?php esc_html_e( 'General Settings', 'bookingpress-appointment-booking' ); ?>
	</span>
	<div class="bpa-default-card bpa-general-settings-tabs--pb__card">
		<el-row type="flex" class="bpa-mlc-head-wrap-settings bpa-gs-tabs--pb__heading">
			<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-gs-tabs--pb__heading--left">
				<h1 class="bpa-page-heading"><?php esc_html_e( 'General Settings', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>
			<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
				<div class="bpa-hw-right-btn-group bpa-gs-tabs--pb__btn-group">		
					<el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveSettingsData('general_setting_form','general_setting')" :disabled="is_disabled" >					
					  <span class="bpa-btn__label"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></span>
					  <div class="bpa-btn--loader__circles">				    
						  <div></div>
						  <div></div>
						  <div></div>
					  </div>
					</el-button>
					<el-button class="bpa-btn" @click="openNeedHelper('list_general_settings', 'general_settings', '<?php esc_html_e( 'General Settings', 'bookingpress-appointment-booking' ); ?>')">
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
			<el-form :rules="rules_general" ref="general_setting_form" :model="general_setting_form" @submit.native.prevent >
				<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
					<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
						<h4><?php esc_html_e( 'Default Time Slot Step', 'bookingpress-appointment-booking' ); ?></h4>
					</el-col>
					<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">				
						<el-form-item prop="default_time_slot_step">
							<el-select class="bpa-form-control" v-model="general_setting_form.default_time_slot_step" 
								placeholder="<?php esc_html_e( 'Minutes', 'bookingpress-appointment-booking' ); ?>"
								popper-class="bpa-el-select--is-with-navbar">
								<el-option v-for="item in default_timeslot_options" :key="item.text" :label="item.text" :value="item.value"></el-option>	
							</el-select>						
						</el-form-item>
					</el-col>
				</el-row>			
				<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
					<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
						<h4><?php esc_html_e( 'Default Appointment Status', 'bookingpress-appointment-booking' ); ?></h4>
					</el-col>
					<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
						<el-row :gutter="24">
							<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
								<el-form-item prop="appointment_status">	
									<el-select class="bpa-form-control" v-model="general_setting_form.appointment_status"
										popper-class="bpa-el-select--is-with-navbar">
										<el-option v-for="item in default_appointment_staus" :key="item.text" :value="item.value"></el-option>
									</el-select>
								</el-form-item>
							</el-col>
						</el-row>
					</el-col>
				</el-row>		
				<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
					<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
						<h4><?php esc_html_e( 'Default Phone Country Code', 'bookingpress-appointment-booking' ); ?></h4>
					</el-col>
					<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
						<el-row :gutter="24">
							<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
							<el-form-item prop="default_phone_country_code">		
								<el-select class="bpa-form-control" filterable v-model="general_setting_form.default_phone_country_code"
									popper-class="bpa-el-select--is-with-navbar">
									<el-option v-for="countries in phone_countries_details" :value="countries.code" :label="countries.name">
										<span class="flag" :class="countries.code"></span> {{ countries.name }}
									</el-option>
								</el-select>
							</el-form-item>
							</el-col>
						</el-row>
					</el-col>
				</el-row>
				<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
					<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
						<h4><?php esc_html_e( 'Default items per page', 'bookingpress-appointment-booking' ); ?></h4>
					</el-col>
					<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
						<el-row :gutter="24">
							<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
								<el-form-item prop="per_page_item">
									<el-select class="bpa-form-control" v-model="general_setting_form.per_page_item"
										popper-class="bpa-el-select--is-with-navbar">
										<el-option v-for="item in default_pagination" :key="item.text" :value="item.value"></el-option>
									</el-select>
								</el-form-item>	
							</el-col>
						</el-row>
					</el-col>
				</el-row>			
				<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
					<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
						<h4><?php esc_html_e( 'Redirect URL After Booking Approved', 'bookingpress-appointment-booking' ); ?></h4>
					</el-col>
					<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
						<el-row :gutter="24">
							<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
								<el-form-item prop="redirect_url_after_booking_approved">									
									<el-input class="bpa-form-control" v-model="general_setting_form.redirect_url_after_booking_approved"></el-input>
								</el-form-item>
							</el-col>
						</el-row>
					</el-col>
				</el-row>	
					<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
					<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
						<h4><?php esc_html_e( 'Redirect URL After Booking Pending', 'bookingpress-appointment-booking' ); ?></h4>
					</el-col>
					<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
						<el-row :gutter="24">
							<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
								<el-form-item prop="redirect_url_after_booking_pending">
									<el-input class="bpa-form-control" v-model="general_setting_form.redirect_url_after_booking_pending"></el-input>
								</el-form-item>	
							</el-col>
						</el-row>
					</el-col>
				</el-row>	
					<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
					<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
						<h4><?php esc_html_e( 'Redirect URL After Booking Canceled', 'bookingpress-appointment-booking' ); ?></h4>						
					</el-col>
					<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
						<el-row :gutter="24">
							<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
								<el-form-item prop="redirect_url_after_booking_canceled">
									<el-input class="bpa-form-control" v-model="general_setting_form.redirect_url_after_booking_canceled"></el-input>
								</el-form-item>	
							</el-col>
						</el-row>
					</el-col>
				</el-row>
				<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
					<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
						<h4><?php esc_html_e( 'Use already loaded Vue', 'bookingpress-appointment-booking' ); ?></h4>
					</el-col>
					<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
							<el-form-item prop="use_already_loaded_vue">
								<el-switch class="bpa-swtich-control" v-model="general_setting_form.use_already_loaded_vue">
								</el-switch>	
							</el-form-item>
					</el-col>
				</el-row>
				<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
					<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
						<h4><?php esc_html_e( 'Load JS &amp; CSS in all pages', 'bookingpress-appointment-booking' ); ?></h4>
					</el-col>
					<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
						<el-form-item>
							<el-switch class="bpa-swtich-control" v-model="general_setting_form.load_js_css_all_pages">
							</el-switch>	
						</el-form-item>
					</el-col>
				</el-row>
			</el-form>	
		</div>
	</div>
</el-tab-pane>


