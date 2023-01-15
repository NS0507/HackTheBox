<?php

global $BookingPress, $wpdb, $bookingpress_version;

$bookingpress_old_version = get_option('bookingpress_version', true);

if(version_compare($bookingpress_old_version, '1.0.2', '<')){
	$tbl_bookingpress_default_workhours    = $wpdb->prefix . 'bookingpress_default_workhours';
	$wpdb->query("UPDATE `{$tbl_bookingpress_default_workhours}` SET `bookingpress_start_time` = NULL, bookingpress_end_time = NULL WHERE bookingpress_start_time = '00:00:00' AND bookingpress_end_time = '00:00:00'");
}


if(version_compare($bookingpress_old_version, '1.0.3', '<')){
	$args  = array(
		'role'   => 'administrator',
		'fields' => 'id',
	);
	$users = get_users( $args );

	if ( count( $users ) > 0 ) {
		foreach ( $users as $key => $user_id ) {
			$bookingpressroles = $BookingPress->bookingpress_capabilities();
			$userObj           = new WP_User( $user_id );
			foreach ( $bookingpressroles as $bookingpressrole => $bookingpress_roledescription ) {
				$userObj->add_cap( $bookingpressrole );
			}
			unset( $bookingpressrole );
			unset( $bookingpressroles );
			unset( $bookingpress_roledescription );
		}
	}	
}

if(version_compare($bookingpress_old_version, '1.0.6', '<')){
	$tbl_bookingpress_entries = $wpdb->prefix . 'bookingpress_entries';
	$wpdb->query("ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_customer_id INT(11) DEFAULT NULL AFTER bookingpress_entry_id");

	$tbl_bookingpress_users = $wpdb->prefix.'bookingpress_users';
	$wpdb->query("ALTER TABLE {$tbl_bookingpress_users} CHANGE bookingpress_wpuser_id bookingpress_wpuser_id INT(11) NULL DEFAULT NULL");
	$wpdb->query("ALTER TABLE {$tbl_bookingpress_users} CHANGE bookingpress_user_password bookingpress_user_password VARCHAR(255) NULL DEFAULT NULL");
	$wpdb->query("ALTER TABLE {$tbl_bookingpress_users} CHANGE bookingpress_user_country_phone bookingpress_user_country_phone VARCHAR(60) NULL DEFAULT NULL");

	$tbl_bookingpress_appointment_bookings = $wpdb->prefix."bookingpress_appointment_bookings";
	$wpdb->query("ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_entry_id INT(11) DEFAULT NULL AFTER bookingpress_appointment_booking_id");

	//Update customers avatar to usermeta table
	$tbl_bookingpress_users_meta = $wpdb->prefix . 'bookingpress_usermeta';

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	@set_time_limit( 0 );

	$charset_collate = '';
	if ( $wpdb->has_cap( 'collation' ) ) {
		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		}
		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE $wpdb->collate";
		}
	}
	$bookingpress_dbtbl_create = array();
	$sql_table = "DROP TABLE IF EXISTS `{$tbl_bookingpress_users_meta}`;
    CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_users_meta}`(
        `bookingpress_usermeta_id` int(11) NOT NULL AUTO_INCREMENT,
        `bookingpress_customer_id` int(11) NOT NULL,
        `bookingpress_usermeta_key` TEXT NOT NULL,
        `bookingpress_usermeta_value` TEXT DEFAULT NULL,
        `bookingpress_usermeta_created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`bookingpress_usermeta_id`)
    ) {$charset_collate};";

	$bookingpress_dbtbl_create[ $tbl_bookingpress_users_meta ] = dbDelta( $sql_table );

	$bookingpress_customer_avatar_details = array();

	$bookingpress_customers_details = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_users} WHERE bookingpress_user_type = 2", ARRAY_A);
	if(is_array($bookingpress_customers_details) && !empty($bookingpress_customers_details)){
		foreach($bookingpress_customers_details as $customer_detail_key => $customer_detail_val){
			$customer_id = $customer_detail_val['bookingpress_customer_id'];
			$wpuser_id = $customer_detail_val['bookingpress_wpuser_id'];

			if(!empty($customer_id) && !empty($wpuser_id)){
				$bookingpress_get_existing_avatar_details = get_user_meta( $wpuser_id, 'customer_avatar_details', true );
				if(!empty($bookingpress_get_existing_avatar_details)){
					$bookingpress_customer_avatar_details[] = array(
						'bookingpress_customer_id' => $customer_id,
						'bookingpress_usermeta_key' => 'customer_avatar_details',
						'bookingpress_usermeta_value' => $bookingpress_get_existing_avatar_details,
					);
				}
			}
		}
	}

	if(is_array($bookingpress_customer_avatar_details) && !empty($bookingpress_customer_avatar_details)){
		foreach($bookingpress_customer_avatar_details as $customer_avatar_key => $customer_avatar_val){
			$BookingPress->update_bookingpress_usermeta($customer_avatar_val['bookingpress_customer_id'], $customer_avatar_val['bookingpress_usermeta_key'], maybe_serialize($customer_avatar_val['bookingpress_usermeta_value']));
		}
	}
}

if(version_compare($bookingpress_old_version, '1.0.9', '<')){
	$tbl_bookingpress_entries = $wpdb->prefix . 'bookingpress_entries';
	$tbl_bookingpress_users = $wpdb->prefix.'bookingpress_users';
	$tbl_bookingpress_users_meta = $wpdb->prefix . 'bookingpress_usermeta';
	$tbl_bookingpress_customers = $wpdb->prefix.'bookingpress_customers';
	$tbl_bookingpress_customers_meta = $wpdb->prefix . 'bookingpress_customers_meta';
	$tbl_bookingpress_default_workhours = $wpdb->prefix.'bookingpress_default_workhours';

	$wpdb->query("ALTER TABLE {$tbl_bookingpress_entries} CHANGE bookingpress_user_id bookingpress_customer_id bigint(11) DEFAULT NULL");
	$wpdb->query("ALTER TABLE {$tbl_bookingpress_users} CHANGE bookingpress_user_id bookingpress_customer_id bigint(11) NOT NULL AUTO_INCREMENT");
	$wpdb->query("ALTER TABLE {$tbl_bookingpress_users_meta} CHANGE bookingpress_usermeta_id bookingpress_customermeta_id bigint(11) NOT NULL AUTO_INCREMENT");
	$wpdb->query("ALTER TABLE {$tbl_bookingpress_users_meta} CHANGE bookingpress_user_id bookingpress_customer_id bigint(11) NOT NULL");
	$wpdb->query("ALTER TABLE {$tbl_bookingpress_users_meta} CHANGE bookingpress_usermeta_key bookingpress_customersmeta_key TEXT NOT NULL");
	$wpdb->query("ALTER TABLE {$tbl_bookingpress_users_meta} CHANGE bookingpress_usermeta_value bookingpress_customersmeta_value TEXT DEFAULT NULL");
	$wpdb->query("ALTER TABLE {$tbl_bookingpress_users_meta} CHANGE bookingpress_usermeta_created_date bookingpress_customersmeta_created_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP");
	

	$wpdb->query("ALTER TABLE {$tbl_bookingpress_users} DROP COLUMN bookingpress_user_password");
	
	$wpdb->query("ALTER TABLE {$tbl_bookingpress_default_workhours} CHANGE bookingpress_employee_workhours_id bookingpress_workhours_id smallint NOT NULL AUTO_INCREMENT");
	$wpdb->query("ALTER TABLE {$tbl_bookingpress_default_workhours} CHANGE bookingpress_employee_workday_key bookingpress_workday_key varchar(11) NOT NULL");

	//RENAME TABLE wpa_bookingpress_users TO wpa_bookingpress_customers
	$wpdb->query("RENAME TABLE {$tbl_bookingpress_users} TO {$tbl_bookingpress_customers}");
	$wpdb->query("RENAME TABLE {$tbl_bookingpress_users_meta} TO {$tbl_bookingpress_customers_meta}");
}


$bookingpress_new_version = '1.0.10';
update_option('bookingpress_new_version_installed',1);
update_option('bookingpress_version', $bookingpress_new_version);
update_option('bookingpress_updated_date_'.$bookingpress_new_version, current_time('mysql'));

?>