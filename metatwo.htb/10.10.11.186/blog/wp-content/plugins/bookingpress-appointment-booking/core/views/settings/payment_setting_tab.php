<el-tab-pane class="bpa-tabs--v_ls__tab--pane-body" label="payments" data-tab_name="payment_settings">
	<span slot="label">
		<i class="material-icons-round">account_balance_wallet</i>
		<?php esc_html_e( 'Payments', 'bookingpress-appointment-booking' ); ?>
	</span>
	<div class="bpa-default-card bpa-general-settings-tabs--pb__card bpa-payment-settings-tabs--pb__card">
		<el-row type="flex" class="bpa-mlc-head-wrap-settings bpa-gs-tabs--pb__heading">
			<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-gs-tabs--pb__heading--left">
				<h1 class="bpa-page-heading"><?php esc_html_e( 'Payments', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>
			<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
				<div class="bpa-hw-right-btn-group bpa-gs-tabs--pb__btn-group">	
					<el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveSettingsData('payment_setting_form','payment_setting')" :disabled="is_disabled" >					
					  <span class="bpa-btn__label"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></span>
					  <div class="bpa-btn--loader__circles">				    
						  <div></div>
						  <div></div>
						  <div></div>
					  </div>
					</el-button>
					<el-button class="bpa-btn" @click="openNeedHelper('list_payment_settings', 'payment_settings', '<?php esc_html_e( 'Payment Settings', 'bookingpress-appointment-booking' ); ?>')">
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
			<el-form :rules="rules_payment" ref="payment_setting_form" :model="payment_setting_form" @submit.native.prevent>
				<div class="bpa-pst-sub-module-wrapper">
					<h4><?php esc_html_e('Currency Settings', 'bookingpress-appointment-booking'); ?></h4>
					<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
						<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">					
							<h4> <?php esc_html_e( 'Currency', 'bookingpress-appointment-booking' ); ?>:</h4>						
						</el-col>
						<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">	
								<el-form-item prop="payment_default_currency">
									<el-select class="bpa-form-control" v-model="payment_setting_form.payment_default_currency"
										popper-class="bpa-el-select--is-with-navbar">
										<el-option  v-for="currency_data in currency_countries" :value="currency_data.name">
											<div class="bpa-fc__item--currency-custom-dropdown-item">
												<el-image :src="'<?php echo esc_url_raw( BOOKINGPRESS_IMAGES_URL ); ?>/country-flags/'+currency_data.iso+'.png'"></el-image>
												<div class="bpa-fc__item--currency-custom-dropdown-item__body">
													<p>{{ currency_data.name }}</p>
													<span>{{ currency_data.symbol }}</span>
												</div>
											</div>
										</el-option>
									</el-select>
								</el-form-item>
						</el-col>				
					</el-row>
					<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
						<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
							<h4><?php esc_html_e( 'Price Symbol Position', 'bookingpress-appointment-booking' ); ?>:</h4>					
						</el-col>
						<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
							<el-form-item prop="price_symbol_position">
								<el-select  class="bpa-form-control" v-model="payment_setting_form.price_symbol_position"
								popper-class="bpa-el-select--is-with-navbar">
									<el-option v-for="price_data in price_symbol_position_val" :value="price_data.value" :label="price_data.text">{{ price_data.text }} - <span class="bookingpress_payment_ex_position_styles">{{ price_data.position_ex }}</span></el-option>
								</el-select>		
							</el-form-item>
						</el-col>
					</el-row>							
					<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
						<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
							<h4><?php esc_html_e( 'Price Separator', 'bookingpress-appointment-booking' ); ?>:</h4>
						</el-col>
						<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
							<el-form-item prop="price_separator_vals">
								<el-select class="bpa-form-control" v-model="payment_setting_form.price_separator"
								popper-class="bpa-el-select--is-with-navbar">
									<el-option v-for="price_data in price_separator_vals" :value="price_data.value" :label="price_data.text">
										<span>{{ price_data.text }}</span>
										<span class="bookingpress_payment_ex_position_styles">{{ price_data.separator_ex }}</span>
									</el-option>
								</el-select>
							</el-form-item>
						</el-col>
					</el-row>
					<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
						<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
							<h4> <?php esc_html_e( 'Price Number Of Decimals:', 'bookingpress-appointment-booking' ); ?></h4>
						</el-col>
						<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
							<el-form-item prop="price_number_of_decimals">
								<el-input-number class="bpa-form-control bpa-form-control--number" :min="1" :max="5" v-model="payment_setting_form.price_number_of_decimals"></el-input-number>
							</el-form-item>	
						</el-col>
					</el-row>
				</div>
				<div class="bpa-pst-sub-module-wrapper">
					<h4><?php esc_html_e('Payment Method Settings', 'bookingpress-appointment-booking'); ?></h4>
					<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
						<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
							<h4> <?php esc_html_e( 'On-Site', 'bookingpress-appointment-booking' ); ?></h4>
						</el-col>
						<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
							<el-form-item prop="on_site_payment">
								<el-switch class="bpa-swtich-control" v-model="payment_setting_form.on_site_payment"></el-switch>
							</el-form-item>
						</el-col>
					</el-row>
					<div class="bpa-pst-is-single-payment-box">
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
							<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
								<h4> <?php esc_html_e( 'PayPal', 'bookingpress-appointment-booking' ); ?></h4>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
								<el-form-item prop="paypal_payment">
									<el-switch class="bpa-swtich-control" v-model="payment_setting_form.paypal_payment"></el-switch>
								</el-form-item>
							</el-col>
						</el-row>
						<div class="bpa-ns--sub-module__card" v-if="payment_setting_form.paypal_payment == true">
							<el-row type="flex" class="bpa-ns--sub-module__card--row">
								<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
									<h4> <?php esc_html_e( 'Payment Mode', 'bookingpress-appointment-booking' ); ?></h4>
								</el-col>
								<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">
									<el-radio v-model="payment_setting_form.paypal_payment_mode" label="sandbox">Sandbox</el-radio>
									<el-radio v-model="payment_setting_form.paypal_payment_mode" label="live">Live</el-radio>
								</el-col>
							</el-row>
							<el-row type="flex" class="bpa-ns--sub-module__card--row">
								<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
									<h4> <?php esc_html_e( 'Merchant Email', 'bookingpress-appointment-booking' ); ?></h4>
								</el-col>
								<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">
									<el-form-item prop="paypal_merchant_email">
										<el-input class="bpa-form-control" type="email" v-model="payment_setting_form.paypal_merchant_email" placeholder="<?php esc_html_e( 'Enter Email', 'bookingpress-appointment-booking' ); ?>"></el-input>
									</el-form-item>
								</el-col>
							</el-row>
							<el-row type="flex" class="bpa-ns--sub-module__card--row">
								<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
									<h4> <?php esc_html_e( 'API Username', 'bookingpress-appointment-booking' ); ?></h4>
								</el-col>
								<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
									<el-form-item prop="paypal_api_username">
										<el-input class="bpa-form-control" v-model="payment_setting_form.paypal_api_username" placeholder="<?php esc_html_e( 'Enter Username', 'bookingpress-appointment-booking' ); ?>"></el-input>
									</el-form-item>	
								</el-col>
							</el-row>
							<el-row type="flex" class="bpa-ns--sub-module__card--row">
								<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
									<h4> <?php esc_html_e( 'API Password', 'bookingpress-appointment-booking' ); ?></h4>
								</el-col>
								<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">															
									<el-form-item prop="paypal_api_password">
										<el-input class="bpa-form-control" v-model="payment_setting_form.paypal_api_password" placeholder="<?php esc_html_e( 'Enter Password', 'bookingpress-appointment-booking' ); ?>"></el-input>
									</el-form-item>
								</el-col>
							</el-row>
							<el-row type="flex" class="bpa-ns--sub-module__card--row">
								<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
									<h4> <?php esc_html_e( 'API Signature', 'bookingpress-appointment-booking' ); ?></h4>
								</el-col>
								<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
									<el-form-item prop="paypal_api_signature">
										<el-input class="bpa-form-control" v-model="payment_setting_form.paypal_api_signature" placeholder="<?php esc_html_e( 'Enter API Signature', 'bookingpress-appointment-booking' ); ?>"></el-input>
									</el-form-item>
								</el-col>
							</el-row>
							<el-row type="flex" class="bpa-ns--sub-module__card--row">
								<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
									<h4> <?php esc_html_e( 'Unsuccessful / Cancel Url', 'bookingpress-appointment-booking' ); ?></h4>
								</el-col>
								<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
									<el-form-item prop="paypal_cancel_url">
										<el-input class="bpa-form-control" v-model="payment_setting_form.paypal_cancel_url" placeholder="<?php esc_html_e( 'Enter Cancel URL', 'bookingpress-appointment-booking' ); ?>"></el-input>
									</el-form-item>
								</el-col>
							</el-row>
						</div>
					</div>

					<?php
						do_action('bookingpress_gateway_listing_field');
					?>

				</div>
			</el-form>
		</div>	
	</div>
</el-tab-pane>
