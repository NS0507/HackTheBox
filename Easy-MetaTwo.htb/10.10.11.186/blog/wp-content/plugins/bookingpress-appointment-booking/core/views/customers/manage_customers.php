<?php
	global $bookingpress_ajaxurl, $bookingpress_common_date_format;
?>
<el-main class="bpa-main-listing-card-container bpa-default-card" id="all-page-main-container">
	<el-row type="flex" class="bpa-mlc-head-wrap">
		<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-mlc-left-heading">
			<h1 class="bpa-page-heading"><?php esc_html_e( 'Manage Customers', 'bookingpress-appointment-booking' ); ?></h1>
		</el-col>
		<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
			<div class="bpa-hw-right-btn-group">
				<el-button class="bpa-btn bpa-btn--primary" @click="open_add_customer_modal()"> 
					<span class="material-icons-round">add</span> 
					<?php esc_html_e( 'Add New', 'bookingpress-appointment-booking' ); ?>
				</el-button>
				<el-button class="bpa-btn" @click="openNeedHelper('list_customers', 'customers', '<?php esc_html_e('Customers', 'bookingpress-appointment-booking'); ?>')">
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
				<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
					<span class="bpa-form-label"><?php esc_html_e( 'Customer Name', 'bookingpress-appointment-booking' ); ?></span>
					<el-input class="bpa-form-control" v-model="customerSearch" placeholder="<?php esc_html_e( 'Customer Name', 'bookingpress-appointment-booking' ); ?>"></el-input>
				</el-col>	
				<el-col :xs="24" :sm="24" :md="24" :lg="6" :xl="6">
					<div class="bpa-tf-btn-group">
						<el-button class="bpa-btn bpa-btn__medium bpa-btn--full-width" @click="resetFilter">
							<?php esc_html_e( 'Reset', 'bookingpress-appointment-booking' ); ?>
						</el-button>
						<el-button class="bpa-btn bpa-btn__medium bpa-btn--primary bpa-btn--full-width" @click="loadCustomers">
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
						
						<el-button class="bpa-btn bpa-btn--primary bpa-btn__medium" @click="open_add_customer_modal()"> 
							<span class="material-icons-round">add</span> 
							<?php esc_html_e( 'Add New', 'bookingpress-appointment-booking' ); ?>
						</el-button>
					</div>
				</div>
			</el-col>
		</el-row>
		<el-row v-if="items.length > 0">
			<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
				<el-container class="bpa-table-container">
					<div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
						<div class="bpa-back-loader"></div>
					</div>
					<el-table ref="multipleTable" :data="items" @selection-change="handleSelectionChange" style="width: 100%">
						<el-table-column  type="selection"></el-table-column>															
						<el-table-column  prop="customer_fullname" label="<?php esc_html_e( 'Full Name', 'bookingpress-appointment-booking' ); ?>" sortable>
							<template slot-scope="scope">														
								<el-image class="bpa-table-column-avatar" :src="scope.row.customer_avatar"></el-image>
								<label v-if="scope.row.customer_firstname != '' && scope.row.customer_lastname != ''">{{ scope.row.customer_firstname }} {{ scope.row.customer_lastname }}</label>
								<label v-else>{{ scope.row.customer_email }}</label>
							</template>
						</el-table-column>
						<el-table-column  prop="customer_email" label="<?php esc_html_e( 'Email', 'bookingpress-appointment-booking' ); ?>" sortable></el-table-column>
						<el-table-column  prop="customer_phone" label="<?php esc_html_e( 'Phone', 'bookingpress-appointment-booking' ); ?>"></el-table-column>
						<el-table-column  prop="customer_last_appointment" label="<?php esc_html_e( 'Last Appointment Booked', 'bookingpress-appointment-booking' ); ?>" sortable>	
						</el-table-column>
						<el-table-column align="center" prop="customer_total_appointment" label="<?php esc_html_e( 'Total Appointments', 'bookingpress-appointment-booking' ); ?>">
							<template slot-scope="scope">
								<label>{{ scope.row.customer_total_appointment }}</label>
								<div class="bpa-table-actions-wrap">
									<div class="bpa-table-actions">									
										<el-tooltip effect="dark" content="" placement="top" open-delay="300">
											<div slot="content">
												<span><?php esc_html_e( 'Edit', 'bookingpress-appointment-booking' ); ?></span>
											</div>
											<el-button class="bpa-btn bpa-btn--icon-without-box" @click.native.prevent="editCustomerDetails(scope.row.customer_id)">
												<span class="material-icons-round">mode_edit</span>
											</el-button>
										</el-tooltip>
										<el-tooltip effect="dark" content="" placement="top" open-delay="300">
											<div slot="content">
												<span><?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?></span>
											</div>
											<el-popconfirm 
												cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
												confirm-button-text='<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>' 
												icon="false" 
												title="<?php esc_html_e( 'Are you sure you want to delete this customer?', 'bookingpress-appointment-booking' ); ?>" 
												@confirm="deleteCustomer(scope.row.customer_id)" 
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
					<p><?php esc_html_e( 'Displaying', 'bookingpress-appointment-booking' ); ?> <strong><u>{{ items.length }}</u></strong><p><?php esc_html_e( 'out of', 'bookingpress-appointment-booking' ); ?></p><strong>{{ totalItems }}</strong></p>
					<div class="bpa-pagination-per-page">
						<p><?php esc_html_e( 'Displaying Per Page', 'bookingpress-appointment-booking' ); ?></p>
						<el-select v-model="pagination_length_val" placeholder="Select" @change="changePaginationSize($event)" class="bpa-form-control" popper-class="bpa-pagination-dropdown">
							<el-option v-for="item in pagination_val" :key="item.text" :label="item.text" :value="item.value"></el-option>
						</el-select>
					</div>
				</div>
			</el-col>
			<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" class="bpa-pagination-nav">
				<el-pagination @size-change="handleSizeChange" @current-change="handleCurrentChange" :current-page.sync="currentPage" layout="prev, pager, next" :total="totalItems" :page-sizes="pagination_length" :page-size="perPage"></el-pagination>
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
						<el-select class="bpa-form-control" v-model="bulk_action" placeholder="<?php esc_html_e( 'Select', 'bookingpress-appointment-booking' ); ?>"
						popper-class="bpa-dropdown--bulk-actions">
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
<!-- Customer Modal -->

<el-dialog id="customer_add_modal" custom-class="bpa-dialog bpa-dialog--fullscreen" modal-append-to-body=false :visible.sync="open_customer_modal" :before-close="closeCustomerModal" fullscreen=true :close-on-press-escape="close_modal_on_esc">
	<div class="bpa-dialog-heading">
		<el-row type="flex">
			<el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
		<h1 class="bpa-page-heading" v-if="customer.update_id == 0"><?php esc_html_e( 'Add Customer', 'bookingpress-appointment-booking' ); ?></h1>
		<h1 class="bpa-page-heading" v-else><?php esc_html_e( 'Edit Customer', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>
			<el-col :xs="12" :sm="12" :md="7" :lg="7" :xl="7" class="bpa-dh__btn-group-col">
				<el-button class="bpa-btn bpa-btn--primary " :class="is_display_save_loader == '1' ? 'bpa-btn--is-loader' : ''" @click="saveCustomerDetails" :disabled="is_disabled" >
					<span class="bpa-btn__label"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></span>
					<div class="bpa-btn--loader__circles">
						<div></div>
						<div></div>
						<div></div>
					</div>
				</el-button> 
				<el-button class="bpa-btn" @click="closeCustomerModal()"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
			</el-col>
		</el-row>
	</div>
	
	<div class="bpa-dialog-body">
		<div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
			<div class="bpa-back-loader"></div>
		</div>
		<div class="bpa-form-row">
			<el-row>
				<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
					<div class="bpa-db-sec-heading">
						<el-row type="flex" align="middle">
							<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
								<div class="db-sec-left">
									<h2 class="bpa-page-heading"><?php esc_html_e('Basic Details', 'bookingpress-appointment-booking'); ?></h2>
								</div>
							</el-col>
							<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
								<div class="bpa-hw-right-btn-group">
									<el-button class="bpa-btn bpa-btn--icon-without-box __is-label" @click="openNeedHelper('list_customers', 'customers', '<?php esc_html_e('Customers', 'bookingpress-appointment-booking'); ?>')">
										<span class="material-icons-round">help</span>
										<?php esc_html_e( 'Need help?', 'bookingpress-appointment-booking' ); ?>
									</el-button>
								</div>
							</el-col>
						</el-row>
					</div>			
					<div class="bpa-default-card bpa-db-card">
						<el-form ref="customer" :rules="rules" :model="customer" label-position="top" @submit.native.prevent>
							<template>							
								<el-row :gutter="24">
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-form-group">
										<el-upload class="bpa-upload-component" ref="avatarRef" action="<?php echo wp_nonce_url( $bookingpress_ajaxurl . '?action=bookingpress_upload_customer_avatar', 'bookingpress_upload_customer_avatar' ); ?>" :on-success="bookingpress_upload_customer_avatar_func" :file-list="customer.avatar_list" multiple="false" :show-file-list="cusShowFileList" limit="1" :on-exceed="bookingpress_image_upload_limit" :on-error="bookingpress_image_upload_err" :on-remove="bookingpress_remove_customer_avatar" :before-upload="checkUploadedFile" drag>
											<span class="material-icons-round bpa-upload-component__icon">cloud_upload</span>
										   <div class="bpa-upload-component__text" v-if="customer.avatar_url == ''"><?php esc_html_e( 'jpg/png files with a size less than 500kb', 'bookingpress-appointment-booking' ); ?>									   	
										   </div>
										</el-upload>
										<div class="bpa-uploaded-avatar__preview"  v-if="customer.avatar_url != ''">
											<button class="bpa-avatar-close-icon" @click="bookingpress_remove_customer_avatar">
												<span class="material-icons-round">close</span>
											</button>
											<el-avatar shape="square" :src="customer.avatar_url" class="bpa-uploaded-avatar__picture"></el-avatar>
										</div>
									</el-col>
								</el-row>
								<div class="bpa-form-body-row">
									<el-row :gutter="32">
										<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
											<el-form-item prop="wp_user">
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'WordPress User', 'bookingpress-appointment-booking' ); ?></span>
												</template>
												<el-select class="bpa-form-control" v-model="customer.wp_user" filterable placeholder="<?php esc_html_e( 'Select User', 'bookingpress-appointment-booking' ); ?>" @change="bookingpress_get_existing_user_details($event)">
													<el-option v-for="item in wpUsersList" :key="item.wp_user" :label="item.label" :value="item.value"></el-option>
												</el-select>
											</el-form-item>
										</el-col>
											<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
											<el-form-item prop="firstname">
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'First Name', 'bookingpress-appointment-booking' ); ?></span>
												</template>
												<el-input class="bpa-form-control" v-model="customer.firstname" id="firstname" name="firstname" placeholder="<?php esc_html_e( 'Enter First Name', 'bookingpress-appointment-booking' ); ?>"></el-input>
											</el-form-item>
										</el-col>
										<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
											<el-form-item prop="lastname">
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Last Name', 'bookingpress-appointment-booking' ); ?></span>
												</template>
												<el-input class="bpa-form-control" v-model="customer.lastname" id="lastname" name="lastname" placeholder="<?php esc_html_e( 'Enter Last Name', 'bookingpress-appointment-booking' ); ?>"></el-input>
											</el-form-item>
										</el-col>											
										<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
											<el-form-item prop="email">
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Email', 'bookingpress-appointment-booking' ); ?></span>
												</template>
												<el-input class="bpa-form-control" v-model="customer.email" id="email" name="email" placeholder="<?php esc_html_e( 'Enter Email', 'bookingpress-appointment-booking' ); ?>"></el-input>
											</el-form-item>
										</el-col>
										<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
											<el-form-item prop="phone">
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Phone', 'bookingpress-appointment-booking' ); ?></span>
												</template>
												<vue-tel-input v-model="customer.phone" class="bpa-form-control --bpa-country-dropdown" @country-changed="bookingpress_phone_country_change_func($event)" v-bind="bookingpress_tel_input_props" ref="bpa_tel_input_field">
													<template v-slot:arrow-icon>
														<span class="material-icons-round">keyboard_arrow_down</span>
													</template>
												</vue-tel-input>
												<?php /*
												<el-input class="bpa-form-control" v-model="customer.phone" placeholder="" >
													<el-select slot="prepend" class="bpa-form-control__country-dropdown" placeholder="IN" v-model="customer.customer_phone_country">
														<el-option v-for="countries in phone_countries_details" :value="countries.code" :label="countries.code"><span class="flag" :class="countries.code"></span> {{ countries.name }}</el-option>
													</el-select>
												</el-input> */ ?>
											</el-form-item>
										</el-col>			
										<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
											<el-form-item prop="note">
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Note', 'bookingpress-appointment-booking' ); ?></span>
												</template>
												<el-input class="bpa-form-control" type="textarea" :rows="3" v-model="customer.note"></el-input>
											</el-form-item>
										</el-col> 
									</el-row>
								</div>
							</template>
						</el-form>
					</div>
				</el-col>
			</el-row>
		</div>
	</div>
</el-dialog>
