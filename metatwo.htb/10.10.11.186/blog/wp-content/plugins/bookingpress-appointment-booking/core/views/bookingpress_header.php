<?php
	global $bookingpress_slugs;
	$request_module = (!empty($_REQUEST['page']) && ($_REQUEST['page'] != "bookingpress")) ? sanitize_text_field( str_replace('bookingpress_', '', $_REQUEST['page']) ) : 'dashboard';

?>
<nav class="bpa-header-navbar">
	<div class="bpa-header-navbar-wrap">
		<div class="bpa-navbar-brand">
			<a href="<?php echo esc_url( BOOKINGPRESS_MENU_URL ); ?>" class="navbar-logo">
				<svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
					<rect width="64" height="64" rx="12"/>
					<path d="M50 18.9608V47.2745C50 49.3359 48.325 51 46.25 51H17.75C15.675 51 14 49.3359 14 47.2745V18.9608C14 16.8993 15.675 15.2353 17.75 15.2353H23V14.1176C23 13.7451 23.375 13 24.125 13C24.875 13 25.25 13.7451 25.25 14.1176V18.5882C25.25 18.9608 24.875 19.7059 24.125 19.7059C23.375 19.7059 23 18.9608 23 18.5882V17.4706H18.5C17.25 17.4706 16.25 18.4641 16.25 19.7059V46.5294C16.25 47.7712 17.25 48.7647 18.5 48.7647H45.5C46.75 48.7647 47.75 47.7712 47.75 46.5294V19.7059C47.75 18.4641 46.75 17.4706 45.5 17.4706H41C41 17.4706 41 18.0418 41 18.5882C41 18.9608 40.625 19.7059 39.875 19.7059C39.125 19.7059 38.75 18.9608 38.75 18.5882V17.4706H33.125C32.5 17.4706 32 16.9739 32 16.3529C32 15.732 32.5 15.2353 33.125 15.2353H38.75V14.1176C38.75 13.7451 39.125 13 39.875 13C40.625 13 41 13.7451 41 14.1176V15.2353H46.25C48.325 15.2353 50 16.8993 50 18.9608Z" fill="white"/>
					<path d="M37.2501 30.8823C37.2501 30.8823 38.0001 30.1372 38.0001 27.9019C38.0001 24.1765 35.7501 23.4314 32.7501 23.4314H26.0001V39.0784H30.5001V43.549H32.7501V39.0784C35.3501 39.0784 37.1751 39.0784 38.5251 37.4144C39.1751 36.6196 39.5001 35.6013 39.5001 34.5582C39.5001 34.0118 39.4251 33.4654 39.3001 33.1176C38.9751 32.2732 38.7501 31.6274 37.2501 30.8823ZM35.0001 36.8431C34.2501 36.8431 32.7501 36.8431 32.7501 36.8431C32.7501 36.8431 32.7501 36.098 32.7501 34.6078C32.7501 33.366 33.7501 32.3725 35.0001 32.3725C36.2001 32.3725 37.2501 33.3412 37.2501 34.6078C37.2501 35.9242 36.1501 36.8431 35.0001 36.8431ZM33.1751 30.6836C32.8001 30.8575 32.4251 31.081 32.0751 31.3294C31.2501 31.9503 30.5001 32.8444 30.5001 34.6078V36.8431H28.2501V25.6667H32.7501C34.5501 25.6667 35.7501 26.4118 35.7501 27.9019C35.7501 29.268 34.7251 29.9137 33.1751 30.6836Z" fill="white"/>
				</svg>
			</a>
		</div>
		<div class="bpa-navbar-nav" id="bpa-navbar-nav">
			<div class="bpa-menu-toggle" id="bpa-mobile-menu">
				<span class="bpa-mm-bar"></span>
				<span class="bpa-mm-bar"></span>
				<span class="bpa-mm-bar"></span>
			</div>
			<ul>
				<li class="bpa-nav-item <?php echo ( 'calendar' == $request_module ) ? '__active' : ''; ?>">
					<a href="<?php echo add_query_arg( 'page', $bookingpress_slugs->bookingpress_calendar, esc_url( BOOKINGPRESS_MENU_URL ) ); ?>" class="bpa-nav-link">
						<span class="material-icons-round">calendar_today</span>
						<?php esc_html_e( 'Calendar', 'bookingpress-appointment-booking' ); ?>
					</a>
				</li>
				<li class="bpa-nav-item <?php echo ( 'appointments' == $request_module ) ? '__active' : ''; ?>">
					<a href="<?php echo add_query_arg( 'page', $bookingpress_slugs->bookingpress_appointments, esc_url( BOOKINGPRESS_MENU_URL ) ); ?>" class="bpa-nav-link">
						<span class="material-icons-round">insert_invitation</span>
						<?php esc_html_e( 'Appointments', 'bookingpress-appointment-booking' ); ?>
					</a>
				</li>
				<li class="bpa-nav-item <?php echo ( 'payments' == $request_module ) ? '__active' : ''; ?>">
					<a href="<?php echo add_query_arg( 'page', $bookingpress_slugs->bookingpress_payments, esc_url( BOOKINGPRESS_MENU_URL ) ); ?>" class="bpa-nav-link">
						<span class="material-icons-round">monetization_on</span>
						<?php esc_html_e( 'Payments', 'bookingpress-appointment-booking' ); ?>
					</a>
				</li>
				<li class="bpa-nav-item <?php echo ( 'customers' == $request_module ) ? '__active' : ''; ?>">
					<a href="<?php echo add_query_arg( 'page', $bookingpress_slugs->bookingpress_customers, esc_url( BOOKINGPRESS_MENU_URL ) ); ?>" class="bpa-nav-link">
						<span class="material-icons-round">supervisor_account</span>
						<?php esc_html_e( 'Customers', 'bookingpress-appointment-booking' ); ?>
					</a>
				</li>
				<li class="bpa-nav-item <?php echo ( 'services' == $request_module ) ? '__active' : ''; ?>">
					<a href="<?php echo add_query_arg( 'page', $bookingpress_slugs->bookingpress_services, esc_url( BOOKINGPRESS_MENU_URL ) ); ?>" class="bpa-nav-link">
						<span class="material-icons-round">ballot</span>
						<?php esc_html_e( 'Services', 'bookingpress-appointment-booking' ); ?>
					</a>
				</li>
				<li class="bpa-nav-item <?php echo ( 'notifications' == $request_module ) ? '__active' : ''; ?>">
					<a href="<?php echo add_query_arg( 'page', $bookingpress_slugs->bookingpress_notifications, esc_url( BOOKINGPRESS_MENU_URL ) ); ?>" class="bpa-nav-link">
						<span class="material-icons-round">mark_email_unread</span>
						<?php esc_html_e( 'Notifications', 'bookingpress-appointment-booking' ); ?>
					</a>
				</li>
				<li class="bpa-nav-item <?php echo ( 'customize' == $request_module ) ? '__active' : ''; ?>">
					<a href="<?php echo add_query_arg( 'page', $bookingpress_slugs->bookingpress_customize, esc_url( BOOKINGPRESS_MENU_URL ) ); ?>" class="bpa-nav-link">
						<span class="material-icons-round">color_lens</span>
						<?php esc_html_e( 'Customize', 'bookingpress-appointment-booking' ); ?>
					</a>
				</li>
				<li class="bpa-nav-item <?php echo ( 'settings' == $request_module ) ? '__active' : ''; ?>">
					<a href="<?php echo add_query_arg( 'page', $bookingpress_slugs->bookingpress_settings, esc_url( BOOKINGPRESS_MENU_URL ) ); ?>" class="bpa-nav-link">
						<span class="material-icons-round">settings</span>
						<?php esc_html_e( 'Settings', 'bookingpress-appointment-booking' ); ?>
					</a>
				</li>
			</ul>
		</div>
	</div>
</nav>
<div class="bpa-mob-nav-overlay" id="bpa-mob-nav-overlay"></div>
