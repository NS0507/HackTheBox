<?php
	global $wpdb, $bookingpress_ajaxurl, $BookingPress, $bookingpress_common_date_format, $tbl_bookingpress_customers;
	$requested_module                               = 'front_appointments';
	$bookingpress_get_customer_details              = $wpdb->get_row( "SELECT * FROM {$tbl_bookingpress_customers} WHERE bookingpress_wpuser_id = {$this->bookingpress_mybooking_login_user_id}", ARRAY_A );
	$this->bookingpress_mybooking_wpuser_id         = ! empty( $bookingpress_get_customer_details['bookingpress_customer_id'] ) ? intval( $bookingpress_get_customer_details['bookingpress_customer_id'] ) : 0;
	$this->bookingpress_mybooking_customer_username = $bpa_customer_username = ! empty( $bookingpress_get_customer_details['bookingpress_user_login'] ) ? esc_html( $bookingpress_get_customer_details['bookingpress_user_login'] ) : '';
	$this->bookingpress_mybooking_customer_email    = $bpa_customer_email = ! empty( $bookingpress_get_customer_details['bookingpress_user_email'] ) ? esc_html( $bookingpress_get_customer_details['bookingpress_user_email'] ) : '';
	$bpa_avatar_url                                 = get_avatar_url( $this->bookingpress_mybooking_wpuser_id );

	$bookingpress_get_existing_avatar_url = $BookingPress->get_bookingpress_customersmeta( $this->bookingpress_mybooking_wpuser_id, 'customer_avatar_details');
	$bookingpress_get_existing_avatar_url = !empty($bookingpress_get_existing_avatar_url) ? maybe_unserialize($bookingpress_get_existing_avatar_url) : array();

	if ( ! empty( $bookingpress_get_existing_avatar_url[0]['url'] ) ) {
		$bpa_avatar_url = $bookingpress_get_existing_avatar_url[0]['url'];
	} else {
		$bpa_avatar_url = BOOKINGPRESS_IMAGES_URL . '/default-avatar.jpg';
	}

	$confirmation_message_for_the_cancel_appointment = $BookingPress->bookingpress_get_settings( 'confirmation_message_for_the_cancel_appointment', 'message_setting' );

?>

<el-main class="bpa-front-default-card bpa-front-my-appointments-container" id="bookingpress_booking_form_<?php echo $bookingpress_uniq_id; ?>">
	<div class="bpa-front-ma-header">
		<div class="bpa-front-ma-header--left">
			<h2 class="bpa-front-module-heading"><?php echo esc_html( $bookingpress_mybooking_title_text ); ?></h2>
		</div>
		<div class="bpa-front-ma-header--right">
			<div class="bpa-front-ma-header__profile-dropdown" v-if="bpa_customer_username != '' && bpa_customer_email != '' && hide_customer_details != '1'">
				<div class="bpa-front-ma-header__profile-dropdown--img">
					<img src="<?php echo esc_url( $bpa_avatar_url ); ?>" alt="">
				</div>
				<div class="bpa-front-ma-header__profile-dropdown--body">
					<h4><?php echo esc_html( $bpa_customer_username ); ?></h4>
					<p><?php echo esc_html( $bpa_customer_email ); ?></p>
				</div>
			</div>
		</div>		
	</div>
	<div class="bpa-front-ma-lists">
		<div class="bpa-front-ma-list--filter-wrapper">
			<div class="bpa-front-ma--fw__row">				
				<div class="bpa-front-ma--fw__col">
					<el-date-picker class="bpa-front-form-control bpa-front-form-control--date-range-picker" v-model="appointment_date_range" format="<?php echo esc_html( $bookingpress_common_date_format ); ?>" type="daterange" 
					start-placeholder="<?php esc_html_e('Start date', 'bookingpress-appointment-booking'); ?>" end-placeholder="<?php esc_html_e('End date', 'bookingpress-appointment-booking'); ?>" @change="search_range_change($event)"
					popper-class="bpa-front--date-range-picker"> </el-date-picker>
				</div>	
				<div class="bpa-front-ma--fw__col">
					<el-input class="bpa-front-form-control" v-model="search_appointment" placeholder="<?php esc_html_e( 'Search appointments', 'bookingpress-appointment-booking' ); ?>" v-if="hide_search_bar != '1'"></el-input>
				</div>
				<div class="bpa-front-ma--fw__col">
					<div class="bpa-tf-btn-group">
						<el-button class="bpa-front-btn bpa-front-btn__medium bpa-front-btn--full-width" @click="resetFilter" >
							<?php esc_html_e( 'Reset', 'bookingpress-appointment-booking' ); ?>
						</el-button>
						<el-button class="bpa-front-btn bpa-front-btn__medium bpa-front-btn--primary bpa-front-btn--full-width" @click="loadFrontAppointments()">
							<?php esc_html_e( 'Apply', 'bookingpress-appointment-booking' ); ?>
						</el-button>
					</div>
				</div>
			</div>
		</div>
		<div class="bpa-front-ma-list--items">
			<div class="bpa-front-loader-container" v-if="is_display_loader == '1'">
				<div class="bpa-front-loader"></div>
			</div>
			<div class="bpa-front-ma-list--item__item-card">
				<el-row type="flex" v-if="items.length == 0">
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<div class="bpa-front-data-empty-view--my-bookings">
							<picture>
								<source srcset="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.webp' ); ?>" type="image/webp">
								<img src="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.png' ); ?>">
							</picture>
							<h4><?php esc_html_e( 'No Appointments found!', 'bookingpress-appointment-booking' ); ?></h4>
						</div>
					</el-col>
				</el-row>
			</div>
			<div class="bpa-front-ma-list--item-row" v-if="items.length != 0" v-for="items_data in items">
				<div class="bpa-front-ma-list--item__heading">
					<h6>{{items_data.date}}</h6>
				</div>					
				<div class="bpa-front-ma-list--item__item-card" v-for="items_list in items_data.data">
					<div class="bpa-front-ma-list--item__ic-row">
						<div class="bpa-front-ma-list--item__ic-col">
							<h4>{{items_list.appointment_service_name}}</h4>
						</div>
						<div class="bpa-front-ma-list--item__ic-col">
							<p>
								<span class="material-icons-round">access_time</span>
								{{items_list.appointment_duration}}
							</p>
						</div>
						<div class="bpa-front-ma-list--item__ic-col">
							<p>
								<span class="material-icons-round">calendar_today</span>
								{{items_list.appointment_date}}  {{ items_list.appointment_time }}
							</p>
						</div>
						<div class="bpa-front-ma-list--item__ic-col">
							<p>{{items_list.appointment_payment}}</p>
						</div>
						<div class="bpa-front-ma-list--item__ic-col --pills">
							<el-tag class="bpa-front-pill --warning" v-if="items_list.appointment_status =='Pending'">{{items_list.appointment_status}}</el-tag>
							<el-tag class="bpa-front-pill" v-if="items_list.appointment_status =='Approved'">{{items_list.appointment_status}}</el-tag>
							<el-tag class="bpa-front-pill --info" v-if="items_list.appointment_status == 'Cancelled'">{{items_list.appointment_status}}</el-tag>							
							<el-tag class="bpa-front-pill --rejected" v-if="items_list.appointment_status == 'Rejected'">{{items_list.appointment_status}}</el-tag>
						</div>
						<div class="bpa-front-ma-list--item__ic-col bpa-front-ma-list--item__item-card--actions">
							<el-popconfirm 
								confirm-button-text='<?php esc_html_e( 'Yes', 'bookingpress-appointment-booking' ); ?>' 
								cancel-button-text='<?php esc_html_e( 'No', 'bookingpress-appointment-booking' ); ?>' 
								icon="false" 
								title="<?php esc_html_e( $confirmation_message_for_the_cancel_appointment, 'bookingpress-appointment-booking' ); ?>"
								@confirm = "cancelAppointment(items_list.appointment_id)" 
								confirm-button-type="bpa-front-btn bpa-front-btn__small bpa-front-btn--danger" 
								cancel-button-type="bpa-front-btn bpa-front-btn__small"
								v-if="allow_cancel_appointments == '1'">
								<el-button type="text" slot="reference" class="bpa-btn bpa-front-btn--icon-without-box __danger" :disabled="is_disabled" v-if="items_list.appointment_status != 'Cancelled' && items_list.appointment_status != 'Rejected'">
									<span class="material-icons-round">event_busy</span>
								</el-button>
							</el-popconfirm>
						</div>						
					</el-row>
				</div>
			</div>
		</div>
	</div>
</el-main>
