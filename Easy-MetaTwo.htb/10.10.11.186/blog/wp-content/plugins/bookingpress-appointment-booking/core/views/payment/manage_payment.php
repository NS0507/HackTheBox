<?php
	global $bookingpress_ajaxurl,$bookingpress_common_date_format;
?>
<el-main class="bpa-main-listing-card-container bpa-default-card" id="all-page-main-container">
	<el-row type="flex" class="bpa-mlc-head-wrap">
		<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-mlc-left-heading">
			<h1 class="bpa-page-heading"><?php esc_html_e( 'Manage Payments', 'bookingpress-appointment-booking' ); ?></h1>
		</el-col>
		<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
			<div class="bpa-hw-right-btn-group">
				<el-button class="bpa-btn" @click="openNeedHelper('list_payments', 'payments', '<?php esc_html_e('Payments', 'bookingpress-appointment-booking'); ?>')">
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
		<div class="bpa-table-filter">
			<el-row type="flex" :gutter="32">				
				<el-col :xs="24" :sm="24" :md="24" :lg="6" :xl="6">
					<span class="bpa-form-label"><?php esc_html_e( 'Date', 'bookingpress-appointment-booking' ); ?></span>
					<el-date-picker class="bpa-form-control bpa-form-control--date-range-picker" format="<?php echo esc_html( $bookingpress_common_date_format ); ?>" v-model="search_data.search_range" type="daterange" 
					start-placeholder="Start date" end-placeholder="End date" @change="search_range_change($event)"
					:popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar"></el-date-picker>
				</el-col>				
				<el-col :xs="24" :sm="24" :md="24" :lg="6" :xl="6">
					<span class="bpa-form-label"><?php esc_html_e( 'Customer Name', 'bookingpress-appointment-booking' ); ?></span>
					<el-select class="bpa-form-control" v-model="search_data.search_customer" multiple filterable collapse-tags 
						placeholder="<?php esc_html_e( 'Select Customer', 'bookingpress-appointment-booking' ); ?>"
						:popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar">
						<el-option v-for="item_data in search_customer_data" :key="item_data.bookingpress_user_login" :label="item_data.bookingpress_user_firstname+' '+item_data.bookingpress_user_lastname" :value="item_data.bookingpress_customer_id"></el-option>
					</el-select>
				</el-col>
				<el-col :xs="24" :sm="24" :md="24" :lg="6" :xl="6">
					<span class="bpa-form-label"><?php esc_html_e( 'Service', 'bookingpress-appointment-booking' ); ?></span>
					<el-select class="bpa-form-control" v-model="search_data.search_service" multiple filterable collapse-tags 
						placeholder="<?php esc_html_e( 'Select Service', 'bookingpress-appointment-booking' ); ?>"
						:popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar">		
						<el-option-group v-for="item in search_services_data" :key="item.category_name" :label="item.category_name">
							<el-option v-for="cat_services in item.category_services" :key="cat_services.service_id" :label="cat_services.service_name" :value="cat_services.service_id"></el-option>
						</el-option-group>
					</el-select>
				</el-col>
				<el-col :xs="24" :sm="24" :md="24" :lg="6" :xl="6">
					<span class="bpa-form-label"><?php esc_html_e( 'Payment Status', 'bookingpress-appointment-booking' ); ?></span>
					<el-select class="bpa-form-control" v-model="search_data.search_status" filterable 
						placeholder="<?php esc_html_e( 'Select Status', 'bookingpress-appointment-booking' ); ?>"
						:popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar">		
						<el-option v-for="item_data in search_status_data" :key="item_data.text" :label="item_data.text" :value="item_data.value"></el-option>
					</el-select>
				</el-col>				
				<el-col :xs="24" :sm="24" :md="24" :lg="6" :xl="6">
					<div class="bpa-tf-btn-group">
						<el-button class="bpa-btn bpa-btn__medium bpa-btn--full-width" @click="resetFilter">
							<?php esc_html_e( 'Reset', 'bookingpress-appointment-booking' ); ?>
						</el-button>
						<el-button class="bpa-btn bpa-btn__medium bpa-btn--primary bpa-btn--full-width" @click="loadPayments">
							<?php esc_html_e( 'Apply', 'bookingpress-appointment-booking' ); ?>
						</el-button>
					</div>
				</el-col>
			</el-row>
		</div>
		<el-row type="flex" v-if="items.length == 0">
			<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
				<div class="bpa-data-empty-view">
					<div class="bpa-ev-left-vector">
						<picture>
							<source srcset="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.webp' ); ?>" type="image/webp">
							<img src="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.png' ); ?>">
						</picture>
					</div>				
					<div class="bpa-ev-right-content">					
						<h4><?php esc_html_e( 'No Record Found!', 'bookingpress-appointment-booking' ); ?></h4>
										
					</div>				
				</div>
			</el-col>
		</el-row>

		<el-row v-if="items.length > 0">
			<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
				<el-container class="bpa-table-container --is-payments-screen">
					<div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
						<div class="bpa-back-loader"></div>
					</div>
					<el-table ref="multipleTable" :data="items" @selection-change="handleSelectionChange">
						<el-table-column type="selection"></el-table-column>
						<el-table-column prop="payment_date" label="<?php esc_html_e( 'Payment Date', 'bookingpress-appointment-booking' ); ?>" sortable></el-table-column>
						<el-table-column prop="payment_customer" label="<?php esc_html_e( 'Customer', 'bookingpress-appointment-booking' ); ?>" sortable></el-table-column>
						<el-table-column prop="payment_service" label="<?php esc_html_e( 'Service', 'bookingpress-appointment-booking' ); ?>" sortable></el-table-column>
						<el-table-column prop="appointment_date" label="<?php esc_html_e( 'Appointment Date', 'bookingpress-appointment-booking' ); ?>" sortable sort-by="appointment_date"></el-table-column>
						<el-table-column prop="payment_gateway" label="<?php esc_html_e( 'Gateway', 'bookingpress-appointment-booking' ); ?>"></el-table-column>
						<el-table-column prop="payment_amount" label="<?php esc_html_e( 'Amount', 'bookingpress-appointment-booking' ); ?>" sortable sort-by="payment_numberic_amount"></el-table-column>
						<el-table-column prop="appointment_status" label="<?php esc_html_e( 'Appointment Status', 'bookingpress-appointment-booking' ); ?>">
							<template slot-scope="scope">
								<el-tag class="bpa-front-pill" :class="(scope.row.appointment_status == 'Pending' ? '--warning' : '') || (scope.row.appointment_status == 'Cancelled' ? '--info' : '') || (scope.row.appointment_status == 'Rejected' ? '--rejected' : '')">{{ scope.row.appointment_status }}</el-tag>
							</template>
						</el-table-column>
						<el-table-column prop="payment_status" label="<?php esc_html_e( 'Payment Status', 'bookingpress-appointment-booking' ); ?>">
							<template slot-scope="scope">
								<el-tag class="bpa-front-pill" :class="((scope.row.payment_status == 'pending' || scope.row.payment_status == 'Pending' ) ? '--warning' : '') || (scope.row.payment_status == 'Cancelled' ? '--info' : '')">{{ scope.row.payment_status }}</el-tag>
								<div class="bpa-table-actions-wrap">
									<div class="bpa-table-actions">
										<el-tooltip effect="dark" content="" placement="top" open-delay="300">
											<div slot="content">
												<span><?php esc_html_e( 'Approve', 'bookingpress-appointment-booking' ); ?></span>
											</div>
											<el-popconfirm 
												confirm-button-text='<?php esc_html_e( 'Approve', 'bookingpress-appointment-booking' ); ?>' 
												cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
												icon="false" 
												title="<?php esc_html_e( 'Are you sure you want to approve Appointment?', 'bookingpress-appointment-booking' ); ?>" 
												@confirm="bpa_approve_appointment(scope.row.payment_log_id)" 
												confirm-button-type="bpa-btn bpa-btn__small bpa-btn--primary" 
												cancel-button-type="bpa-btn bpa-btn__small"
												v-if="scope.row.appointment_status == 'Pending'">
												<el-button type="text" slot="reference" class="bpa-btn bpa-btn--icon-without-box __secondary">
													<span class="material-icons-round">done</span>
												</el-button>
											</el-popconfirm>
										</el-tooltip>
										<el-tooltip effect="dark" content="" placement="top" open-delay="300">
											<div slot="content">
												<span><?php esc_html_e( 'View', 'bookingpress-appointment-booking' ); ?></span>
											</div>
											<el-button class="bpa-btn bpa-btn--icon-without-box" @click="view_details(scope.row.payment_log_id)">
												<span class="material-icons-round">visibility</span>
											</el-button>
										</el-tooltip>
										<el-tooltip effect="dark" content="" placement="top" open-delay="300">
											<div slot="content">
												<span><?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?></span>
											</div>
											<el-popconfirm 
												confirm-button-text='<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>' 
												cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
												icon="false" 
												title="<?php esc_html_e( 'Are you sure you want to delete this payment transaction?', 'bookingpress-appointment-booking' ); ?>" 
												@confirm="deletePaymentLog(scope.row.payment_log_id)" 
												confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
												cancel-button-type="bpa-btn bpa-btn__small">
												<el-button type="text" slot="reference" class="bpa-btn bpa-btn--icon-without-box __danger">
													<span class="material-icons-round">delete</span>
												</el-button>
											</el-popconfirm>
										</el-tooltip>
									</div>
								</div>
							</template>
						</el-table-column>
					</el-table>				
				</el-container>
			</el-col>
		</el-row>	
		<el-row class="bpa-pagination" type="flex" v-if="items.length > 0"> <!-- Pagination -->
			<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" >
				<div class="bpa-pagination-left">
					<p><?php esc_html_e( 'Displaying', 'bookingpress-appointment-booking' ); ?> <strong><u>{{ items.length }}</u></strong><?php esc_html_e( 'out of', 'bookingpress-appointment-booking' ); ?><strong>{{ totalItems }}</strong></p>
					<div class="bpa-pagination-per-page">
						<p><?php esc_html_e( 'Displaying Per Page', 'bookingpress-appointment-booking' ); ?></p>
						<el-select v-model="pagination_length_val" placeholder="Select" @change="changePaginationSize($event)" class="bpa-form-control" popper-class="bpa-pagination-dropdown">
							<el-option v-for="item in pagination_val" :key="item.text" :label="item.text" :value="item.value"></el-option>
						</el-select>
					</div>
				</div>
			</el-col>
			<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" class="bpa-pagination-nav">
				<el-pagination ref="bpa_pagination" @size-change="handleSizeChange" @current-change="handleCurrentChange" :current-page.sync="currentPage" layout="prev, pager, next" :total="totalItems" :page-sizes="pagination_length" :page-size="perPage"></el-pagination>
			</el-col>

			<el-container v-if="multipleSelection.length > 0" class="bpa-default-card bpa-bulk-actions-card">
				<el-button class="bpa-btn bpa-btn--icon-without-box bpa-bac__close-icon" @click="closeBulkAction">
					<span class="material-icons-round">close</span>
				</el-button>
				<el-row type="flex" class="bpa-bac__wrapper">
					<el-col class="bpa-bac__left-area" :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
						<span class="material-icons-round">check_circle</span>
						<p>{{ multipleSelection.length }}<?php esc_html_e( ' Items Selected', 'bookingpress-appointment-booking' ); ?></p>
					</el-col>
					<el-col class="bpa-bac__right-area" :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
						<el-select class="bpa-form-control" v-model="bulk_action" placeholder="<?php esc_html_e( 'Select', 'bookingpress-appointment-booking' ); ?>" popper-class="bpa-dropdown--bulk-actions">
							<el-option v-for="item in bulk_options" :key="item.value" :label="item.label" :value="item.value"></el-option>
						</el-select>
						<el-button @click="bulk_actions" class="bpa-btn bpa-btn--primary bpa-btn__medium">
							<?php esc_html_e( 'Go', 'bookingpress-appointment-booking' ); ?>
						</el-button>
					</el-col>
				</el-row>
			</el-container>		
		</el-row>
	</div>
</el-main>

<!-- View Payment Logs Modal -->
<el-dialog custom-class="bpa-dialog bpa-dialog--default bpa-dialog--manage-categories bpa-dialog--view-payment-info" title="" :visible.sync="view_payment_details_modal" :close-on-press-escape="close_modal_on_esc">	
	<div class="bpa-dialog-heading">
		<el-row type="flex" :gutter="24">
			<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
				<h1 class="bpa-page-heading"><?php esc_html_e( 'View Details', 'bookingpress-appointment-booking' ); ?></h1>
				<el-button class="bpa-btn bpa-btn--icon-without-box bpa-bac__close-icon" @click="ClosePaymentModal()">
					<span class="material-icons-round">close</span>
				</el-button>
			</el-col>
		</el-row>
	</div>
	<div class="bpa-back-loader-container" v-if="is_display_loader_view == '1'">
		<div class="bpa-back-loader"></div>
	</div>
	<div class="bpa-dialog-body">
		<div class="bpa-card bpa-card__body-row">
			<div class="bpa-dialog--vpi__body">
				<div class="bpa-dialog--vpi__body--head">
					<ul>
						<li :xs="12" :sm="12" :md="12" :lg="8" :xl="8">
							<span><?php esc_html_e( 'Customer', 'bookingpress-appointment-booking' ); ?></span>
							<p v-text="view_payment_data.customer_name"></p>
						</li>
						<li :xs="12" :sm="12" :md="12" :lg="8" :xl="8">
							<span><?php esc_html_e( 'Appointment Date', 'bookingpress-appointment-booking' ); ?></span>
							<p v-text="view_payment_data.bookingpress_appointment_date"></p>
						</li>
						<li :xs="12" :sm="12" :md="12" :lg="8" :xl="8">
							<span><?php esc_html_e( 'Payment Status', 'bookingpress-appointment-booking' ); ?></span>
							<p v-text="view_payment_data.bookingpress_payment_status"></p>
						</li>
					</el-row>
				</div>
				<div class="bpa-dialog--vpi__body--extra-fields">
					<div class="bpa-dialog--vpi__body--ef-row">
						<el-row type="flex">
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<span><?php esc_html_e( 'Service', 'bookingpress-appointment-booking' ); ?></span>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<p v-text="view_payment_data.bookingpress_service_name"></p>
							</el-col>
						</el-row>
					</div>
					<div class="bpa-dialog--vpi__body--ef-row">
						<el-row type="flex">
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<span><?php esc_html_e( 'Payment Gateway', 'bookingpress-appointment-booking' ); ?></span>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<p v-text="view_payment_data.bookingpress_payment_gateway"></p>
							</el-col>
						</el-row>
						</div>
					<div class="bpa-dialog--vpi__body--ef-row">
						<el-row type="flex">
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<span><?php esc_html_e( 'Paid Amount', 'bookingpress-appointment-booking' ); ?></span>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<p v-text="view_payment_data.bookingpress_payment_amount"></p>
							</el-col>
						</el-row>
					</div>
					<div class="bpa-dialog--vpi__body--ef-row">
						<el-row type="flex">
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<span><?php esc_html_e( 'Transaction ID', 'bookingpress-appointment-booking' ); ?></span>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<p v-text="view_payment_data.bookingpress_transaction_id"></p>
							</el-col>
						</el-row>
					</div>
					<div class="bpa-dialog--vpi__body--ef-row">
						<el-row type="flex">
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<span><?php esc_html_e( 'Payer Email', 'bookingpress-appointment-booking' ); ?></span>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<p v-text="view_payment_data.bookingpress_payer_email"></p>
							</el-col>
						</el-row>
					</div>
				</div>
			</div>
		</div>
	</div>
</el-dialog>
