<el-tab-pane class="bpa-tabs--v_ls__tab--pane-body" label="DaysOff" data-tab_name="daysoff_settings">
	<span slot="label">
		<i class="material-icons-round">event</i>
		<?php esc_html_e( 'DaysOff', 'bookingpress-appointment-booking' ); ?>
	</span>
	<div class="bpa-default-card bpa-general-settings-tabs--pb__card bpa-daysoff-tabs--pb__card">
		<el-row type="flex" class="bpa-mlc-head-wrap-settings bpa-gs-tabs--pb__heading">
			<el-col :xs="8" :sm="8" :md="8" :lg="8" :xl="8" class="bpa-gs-tabs--pb__heading--left">
				<h1 class="bpa-page-heading"><?php esc_html_e( 'DaysOff', 'bookingpress-appointment-booking' ); ?></h1>
				
			</el-col>
			<el-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16">
				<div class="bpa-hw-right-btn-group bpa-gs-tabs--pb__btn-group">
					<div class="bpa-daysoff-highlight-types-row">
						<div class="bpa-daysoff-htr--item __bpa-is-yearly">				
							<p><?php esc_html_e( 'Repeat Yearly', 'bookingpress-appointment-booking' ); ?></p>
						</div>
						<div class="bpa-daysoff-htr--item">				
							<p><?php esc_html_e( 'Once Off', 'bookingpress-appointment-booking' ); ?></p>
						</div>
					</div>
					<el-button class="bpa-btn" @click="openNeedHelper('list_daysoff_settings', 'daysoff_settings', '<?php esc_html_e( 'Days Off Settings', 'bookingpress-appointment-booking' ); ?>')">
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
			<el-row type="flex" :gutter="32" class="bpa-gs--tabs-pb__cb-item-row">
				<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
					 <el-date-picker class="bpa-form-control bpa-form-control--date-picker bpa-form-control--date-picker__yearly" 
					 v-model="daysoff_default_year" type="year" @change="bookingpress_daysoff_selected_year($event)"
					 :popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar"></el-date-picker>
				</el-col>
			</el-row>
			<el-row type="flex" :gutter="32" class="bpa-gs--tabs-pb__cb-item-row">
				<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
					<div class="bpa-daysoff-calendar-col">
						<v-calendar class="bpa-daysoff-calendar-col--item" nav-visibility="hidden" ref="calendar_0" :attributes="attributes" @dayclick="onDayClick" :locale="site_locale" />
					</div>
				</el-col>

				<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
					<div class="bpa-daysoff-calendar-col">
						<v-calendar class="bpa-daysoff-calendar-col--item" nav-visibility="hidden" ref="calendar_1" :attributes="attributes" @dayclick="onDayClick" :locale="site_locale" />
					</div>
				</el-col>

				<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
					<div class="bpa-daysoff-calendar-col">
						<v-calendar class="bpa-daysoff-calendar-col--item" nav-visibility="hidden" ref="calendar_2" :attributes="attributes" @dayclick="onDayClick" :locale="site_locale" />
					</div>
				</el-col>
			</el-row>
			<el-row type="flex" :gutter="32" class="bpa-gs--tabs-pb__cb-item-row">
				<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
					<div class="bpa-daysoff-calendar-col">
						<v-calendar class="bpa-daysoff-calendar-col--item" nav-visibility="hidden" ref="calendar_3" :attributes="attributes" @dayclick="onDayClick" :locale="site_locale" />
					</div>
				</el-col>
				<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
					<div class="bpa-daysoff-calendar-col">
						<v-calendar class="bpa-daysoff-calendar-col--item" nav-visibility="hidden" ref="calendar_4" :attributes="attributes" @dayclick="onDayClick" :locale="site_locale" />
					</div>
				</el-col>
				<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
					<div class="bpa-daysoff-calendar-col">
						<v-calendar class="bpa-daysoff-calendar-col--item" nav-visibility="hidden" ref="calendar_5" :attributes="attributes" @dayclick="onDayClick" :locale="site_locale" />
					</div>
				</el-col>
			</el-row>
			<el-row type="flex" :gutter="32" class="bpa-gs--tabs-pb__cb-item-row">
				<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
					<div class="bpa-daysoff-calendar-col">
						<v-calendar class="bpa-daysoff-calendar-col--item" nav-visibility="hidden" ref="calendar_6" :attributes="attributes" @dayclick="onDayClick" :locale="site_locale" />
					</div>
				</el-col>

				<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
					<div class="bpa-daysoff-calendar-col">
						<v-calendar class="bpa-daysoff-calendar-col--item" nav-visibility="hidden" ref="calendar_7" :attributes="attributes" @dayclick="onDayClick" :locale="site_locale" />
					</div>
				</el-col>

				<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
					<div class="bpa-daysoff-calendar-col">
						<v-calendar class="bpa-daysoff-calendar-col--item" nav-visibility="hidden" ref="calendar_8" :attributes="attributes" @dayclick="onDayClick" :locale="site_locale" />
					</div>
				</el-col>
			</el-row>
			<el-row type="flex" :gutter="32" class="bpa-gs--tabs-pb__cb-item-row">
				<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
					<div class="bpa-daysoff-calendar-col">
						<v-calendar class="bpa-daysoff-calendar-col--item" nav-visibility="hidden" ref="calendar_9" :attributes="attributes" @dayclick="onDayClick" :locale="site_locale" />
					</div>
				</el-col>

				<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
					<div class="bpa-daysoff-calendar-col">
						<v-calendar class="bpa-daysoff-calendar-col--item" nav-visibility="hidden" ref="calendar_10" :attributes="attributes" @dayclick="onDayClick" :locale="site_locale" />
					</div>
				</el-col>

				<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
					<div class="bpa-daysoff-calendar-col">
						<v-calendar class="bpa-daysoff-calendar-col--item" nav-visibility="hidden" ref="calendar_11" :attributes="attributes" @dayclick="onDayClick" :locale="site_locale" />
					</div>
				</el-col>
			</el-row>
		</div>	
	</div>
</el-tab-pane>
<el-dialog custom-class="bpa-dialog bpa-dailog__small bpa-add-dayoff-dialog" title="" :visible.sync="open_add_daysoff_details" :visible.sync="centerDialogVisible" :style="'top: '+days_off_top_pos+'; left: '+days_off_left_pos+';'" :close-on-press-escape="close_modal_on_esc" :modal="is_mask_display">
	<div class="bpa-dialog-heading">
		<el-row type="flex">
			<el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
				<h1 class="bpa-page-heading"><?php esc_html_e( 'DayOff', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>
		</el-row>
	</div>
	<div class="bpa-dialog-body">
		<el-container class="bpa-grid-list-container bpa-add-categpry-container bpa-add-dayoff-container">
			<div class="bpa-form-row">
				<el-row>
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<el-form id="days_off_form" :rules="days_off_rules" ref="days_off_form" :model="days_off_form" label-position="top" @submit.native.prevent>
							<div class="bpa-form-body-row">
								<el-row>
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<el-form-item prop="daysoff_title">
											<template #label>
												<span class="bpa-form-label"><?php esc_html_e( 'DayOff Name', 'bookingpress-appointment-booking' ); ?></span>
											</template>
											<el-input v-model="days_off_form.daysoff_title" class="bpa-form-control" placeholder="<?php esc_html_e( 'Holiday Reason', 'bookingpress-appointment-booking' ); ?>" @input="bookingpress_trim_value($event)"></el-input>
										</el-form-item>
									</el-col>
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-add-dayoff-col--is-repeat-yearly">
										<el-form-item>
											<label class="bpa-form-label bpa-custom-checkbox--is-label"> 
												<el-checkbox v-model="days_off_form.is_repeat_days_off" class="bpa-custom-checkbox--sm"></el-checkbox> 
												<?php esc_html_e( 'Repeat Every Year', 'bookingpress-appointment-booking' ); ?>
											</label>
										</el-form-item>
									</el-col>
								</el-row>
							</div>
						</el-form>
					</el-col>
				</el-row>
			</div>
		</el-container>
	</div>
	<div class="bpa-dialog-footer">
		<div class="bpa-hw-right-btn-group">
			<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="save_daysoff_details('days_off_form')" :disabled="is_disabled" >					
			  <span class="bpa-btn__label"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></span>
			  <div class="bpa-btn--loader__circles">				    
				  <div></div>
				  <div></div>
				  <div></div>
			  </div>
			</el-button>
			<el-button class="bpa-btn bpa-btn__small" @click="delete_dayoff" v-if="days_off_form.is_edit == ''"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
			<el-popconfirm 
				confirm-button-text='<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>' 
				cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
				icon="false" 
				title="<?php esc_html_e( 'Are you sure you want to delete this dayoff?', 'bookingpress-appointment-booking' ); ?>" 
				@confirm="delete_dayoff" 
				confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
				cancel-button-type="bpa-btn bpa-btn__small"
				v-if="days_off_form.is_edit == '1'">
				<el-button type="text" slot="reference" class="bpa-btn bpa-btn__small">
					<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>
				</el-button>
			</el-popconfirm>
		</div>
	</div>
</el-dialog>
