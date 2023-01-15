
<el-tab-pane class="bpa-tabs--v_ls__tab--pane-body" label="customers" data-tab_name="customer_settings">
	<span slot="label">
		<i class="material-icons-round">supervisor_account</i>
		<?php esc_html_e( 'Customers', 'bookingpress-appointment-booking' ); ?>
	</span>
	<div class="bpa-default-card bpa-general-settings-tabs--pb__card bpa-payment-settings-tabs--pb__card">
		<el-row type="flex" class="bpa-mlc-head-wrap-settings bpa-gs-tabs--pb__heading">
			<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-gs-tabs--pb__heading--left">
				<h1 class="bpa-page-heading"><?php esc_html_e( 'Customers Settings', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>
			<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
				<div class="bpa-hw-right-btn-group bpa-gs-tabs--pb__btn-group">	
					<el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveSettingsData('customer_setting_form','customer_setting')" :disabled="is_disabled" >					
					  <span class="bpa-btn__label"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></span>
					  <div class="bpa-btn--loader__circles">				    
						  <div></div>
						  <div></div>
						  <div></div>
					  </div>
					</el-button>
					<el-button class="bpa-btn" @click="openNeedHelper('list_customer_settings', 'customer_settings', '<?php esc_html_e( 'Customer Settings', 'bookingpress-appointment-booking' ); ?>')">
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
			<el-form id="customer_setting_form" ref="customer_setting_form" @submit.native.prevent>
				<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
					<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
						<h4> <?php esc_html_e( 'Create new WordPress user upon appointment booking?', 'bookingpress-appointment-booking' ); ?></h4>
					</el-col>
					<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >				
						<el-form-item>
							<el-switch class="bpa-swtich-control" v-model="customer_setting_form.allow_wp_user_create"></el-switch>
						</el-form-item>
					</el-col>
				</el-row>
			<el-form>
		</div>
	</div>
</el-tab-pane>