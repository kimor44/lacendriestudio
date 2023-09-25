<?php
// Webba Booking options page class
// check if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
class WBK_Backend_Options {
	public function __construct() {
		//set component-specific properties
		// init settings
		add_action( 'admin_init', array( $this, 'initSettings' ) );
		// init scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueScripts'), 20 );
		// mce plugin
	 	add_filter( 'mce_buttons',  array( $this, 'wbk_mce_add_button' ) );
	 	add_filter( 'mce_external_plugins',  array( $this, 'wbk_mce_add_javascript' ) );
	 	add_filter( 'wp_default_editor', array( $this, 'wbk_default_editor' )  );
	 	add_filter( 'tiny_mce_before_init', array( $this, 'customizeEditor' ), 1000 );
		// save options
		add_action( 'wp_ajax_wbk_save_options', array( $this, 'save_options' ) );
	}

	public function save_options() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'No permissions' );
		}

		global $wp_settings_fields;

		parse_str( $_POST['form_data'], $options );

		$settings_fields = $wp_settings_fields['wbk-options'];

		foreach ( $settings_fields[ $options['section'] ] as $field ) {
			if ( isset( $options[ $field['id'] ] ) ) {
				update_option( $field['id'], $options[ $field['id'] ] );
			} else {
				update_option( $field['id'], '' );
			}
		}

		wp_send_json_success();
	}

	public function customizeEditor( $in ) {
		if ( $this->is_option_page() ) {
			$in['forced_root_block'] = false;
			$in['remove_linebreaks'] = false;
		 	$in['remove_redundant_brs'] = false;
	 		$in['wpautop'] = false;
			$opts = '*[*]';
		    $in['valid_elements'] = $opts;
		    $in['extended_valid_elements'] = $opts;
	 	}
	 	return $in;
	}
	public function wbk_mce_add_button( $buttons ) {
		if ( $this->is_option_page() ) {
			$buttons[] = 'wbk_service_name_button';
			$buttons[] = 'wbk_category_names_button';
			$buttons[] = 'wbk_customer_name_button';
			$buttons[] = 'wbk_appointment_day_button';
			$buttons[] = 'wbk_appointment_time_button';
			$buttons[] = 'wbk_appointment_local_day_button';
			$buttons[] = 'wbk_appointment_local_time_button';
			$buttons[] = 'wbk_appointment_id_button';
			$buttons[] = 'wbk_customer_phone_button';
			$buttons[] = 'wbk_customer_email_button';
			$buttons[] = 'wbk_customer_comment_button';
			$buttons[] = 'wbk_customer_custom_button';
			$buttons[] = 'wbk_items_count';
			$buttons[] = 'wbk_total_amount';
			$buttons[] = 'wbk_payment_link';
			$buttons[] = 'wbk_cancel_link';
			$buttons[] = 'wbk_tomorrow_agenda';
			$buttons[] = 'wbk_group_customer';
			$buttons[] = 'wbk_multiple_loop';
			$buttons[] = 'wbk_admin_cancel_link';
			$buttons[] = 'wbk_admin_approve_link';
			$buttons[] = 'wbk_customer_ggcl_link';
			$buttons[] = 'wbk_time_range';
		}
		return $buttons;
	}
	public function wbk_mce_add_javascript( $plugin_array ) {
		if ( $this->is_option_page() && ! isset( $plugin_array['wbk_tinynce'] ) ) {
			$plugin_array['wbk_tinynce'] =  WP_WEBBA_BOOKING__PLUGIN_URL . '/public/js/wbk-tinymce.js';
		}
		return $plugin_array;
	}
	// init wp settings api objects for options page
	public function initSettings() {
		// General settings section
		add_settings_section(
	        'wbk_general_settings_section',
	        __( 'General', 'webba-booking-lite' ),
	        array( $this, 'wbk_general_settings_section_callback'),
	        'wbk-options',
			array( 'icon' => 'settings-general-icon' )
   		);
		// Booking rules (ex appointments) section
		add_settings_section(
			'wbk_appointments_settings_section',
			__( 'Booking rules', 'webba-booking-lite' ),
			array( $this, 'wbk_appointments_settings_section_callback'),
			'wbk-options',
			array( 'icon' => 'bookins-rules-icon' )
		);
		// User interface (ex. mode) section
		add_settings_section(
			'wbk_mode_settings_section',
			__( 'User interface', 'webba-booking-lite' ),
			array( $this, 'wbk_mode_settings_section_callback'),
			'wbk-options',
			array( 'icon' => 'user-interface-icon' )
		);

		// Email notifications section
 		add_settings_section(
			'wbk_email_settings_section',
			__( 'Email notifications', 'webba-booking-lite' ),
			array( $this, 'wbk_email_settings_section_callback'),
			'wbk-options',
			array( 'icon' => 'email-notification-icon' )
		);

		// translation settings section
		add_settings_section(
			'wbk_translation_settings_section',
			__( 'Wording / Translation', 'webba-booking-lite' ),
			array( $this, 'wbk_translation_settings_section_callback'),
			'wbk-options',
			array( 'icon' => 'wording-translation-icon' )
		);
		if ( wbk_fs()->is__premium_only() && wbk_fs()->can_use_premium_code() ) {
			add_settings_section(
				'wbk_paypal_settings_section',
				__( 'PayPal', 'webba-booking-lite' ),
				array( $this, 'wbk_paypal_settings_section_callback'),
				'wbk-options',
				array(
					'icon' => 'paypal-icon',
					'pro'  => true
				)
			);
			add_settings_section(
				'wbk_stripe_settings_section',
				__( 'Stripe', 'webba-booking-lite' ),
				array( $this, 'wbk_stripe_settings_section_callback'),
				'wbk-options',
				array(
					'icon' => 'stripe-icon',
					'pro'  => true
				)
			);
			add_settings_section(
				'wbk_gg_calendar_settings_section',
				__( 'Google Calendar', 'webba-booking-lite' ),
				array( $this, 'wbk_gg_calendar_settings_section_callback'),
				'wbk-options',
				array(
					'icon' => 'google-calendar-icon',
					'pro'  => true
				)
			);
			add_settings_section(
				'wbk_sms_settings_section',
				__( 'SMS', 'webba-booking-lite' ),
				array( $this, 'wbk_sms_settings_section_callback'),
				'wbk-options',
				array(
					'icon' => 'sms-icon',
					'pro'  => true
				)
			);
			add_settings_section(
				'wbk_woo_settings_section',
				__( 'WooCommerce', 'webba-booking-lite' ),
				array( $this, 'wbk_woo_settings_section_callback'),
				'wbk-options',
				array(
					'icon' => 'woocommerce-icon',
					'pro'  => true
				)
			);
			add_settings_section(
				'wbk_zoom_settings_section',
				__( 'Zoom', 'webba-booking-lite' ),
				array( $this, 'wbk_zoom_settings_section_callback'),
				'wbk-options',
				array(
					'icon' => 'zoom-icon',
					'pro'  => true
				)
			);
		}
		add_settings_section(
	        'wbk_interface_settings_section',
	        __( 'Backend interface', 'webba-booking-lite' ),
	        array( $this, 'wbk_backend_interface_settings_section_callback'),
	        'wbk-options',
			array( 'icon' => 'backend-interface-icon' )
   		);
 
		wbk_opt()->add_option( 'wbk_timezone', 'select', __( 'Timezone', 'webba-booking-lite' ), 'wbk_general_settings_section',
			array(
				'extra' => array_combine( timezone_identifiers_list(), timezone_identifiers_list() ),
                'default' => 'Europe/London',
                'popup' => __( 'Select your local timezone for both the backend and booking form.', 'webba-booking-lite' )

			)
		);

        wbk_opt()->add_option( 'wbk_form_layout', 'select', __( 'Form layout', 'webba-booking-lite' ), 'wbk_mode_settings_section',
			array(
				'default' => 'default',
				'extra' => array(
					'default' => __( 'Default', 'webba-booking-lite' ),
					'narrow' => __( 'For themes with narrow columns', 'webba-booking-lite' ) 
                ),
                'popup' => __( 'Choose between the default layout or the layout optimized for themes with narrow columns.', 'webba-booking-lite' )
            
            ) 
		);
        
		wbk_opt()->add_option( 'wbk_show_suitable_hours', 'checkbox', __( 'Show suitable hours', 'webba-booking-lite' ), 'wbk_mode_settings_section',
			array(
				'checkbox_value' => 'yes',
				'dependency' => array( 'wbk_mode' => 'extended' )
			)
		);

  
        wbk_opt()->add_option( 'wbk_multi_booking', 'checkbox', __( 'Multiple bookings in one session', 'webba-booking-lite' ), 'wbk_mode_settings_section',
            array(
                'popup' => __( 'Turn on to activate the multiple booking mode that allows booking multiple time slots in the same booking.', 'webba-booking-lite' ),
                'default' => '',
                'checkbox_value' => 'enabled'                 
            )
        );
 
 
		wbk_opt()->add_option( 'wbk_phone_mask', 'select', __( 'Phone number masked input', 'webba-booking-lite' ), 'wbk_mode_settings_section',
			array(
				'default' => 'enabled',
				'extra' => array(
					'enabled' => __( 'jQuery Masked Input Plugin', 'webba-booking-lite' ),
					'enabled_mask_plugin' => __( 'jQuery Mask Plugin', 'webba-booking-lite' ),
					'disabled' => __( 'Disabled', 'webba-booking-lite' ),
                ),
                'dependency' => array('wbk_mode' => 'extended|simple')
			)
		);
		wbk_opt()->add_option( 'wbk_phone_format', 'text', __( 'Phone format', 'webba-booking-lite' ), 'wbk_mode_settings_section',
			array(
                'default' => '(000)000-0000',
				'popup' =>  __( 'Customize the format for phone numbers using numeric symbols. You can use "0" to represent numeric placeholders. E.g.: (000) 000-0000. Leave this field blank if you do not wish to use a specific phone format.', 'webba-booking-lite' )  
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_phone_required', 'checkbox', __( 'Phone field is mandatory', 'webba-booking-lite' ), 'wbk_mode_settings_section',
			array(
                'popup' =>  __( 'Turn on to make the phone field mandatory.', 'webba-booking-lite' ),
	            'checkbox_value' => '3',
                'default' => ''
               
			),
            'advanced'
		);
		wbk_opt()->add_option(
			'wbk_show_booked_slots',
			'checkbox',
			__( 'Show booked time slots', 'webba-booking-lite' ),
			'wbk_mode_settings_section',
			array( 
                'popup' =>  __( 'Turn on to show booked time slots as "Booked".', 'webba-booking-lite' ) 
            ),
			'advanced'
		);
		wbk_opt()->add_option( 'wbk_appointments_auto_lock', 'checkbox', __( 'Autolock bookings', 'webba-booking-lite' ), 'wbk_appointments_settings_section',
			array(
				'popup' => __( 'When one service is booked, it will automatically lock another one, preventing conflicting bookings.', 'webba-booking-lite' )
			)
		);
		wbk_opt()->add_option( 'wbk_appointments_auto_lock_mode', 'select', __( 'Perform autolock on', 'webba-booking-lite' ), 'wbk_appointments_settings_section',
			array(
                'popup' => __( 'Choose whether the autolock feature applies to all services or only services within the same category.', 'webba-booking-lite' ),
				'default' => 'all',
				'extra' => array(
					'all' => __( 'All services', 'webba-booking-lite' ),
					'categories' => __( 'Services in the same category', 'webba-booking-lite' )
				),
				'dependency' => array('wbk_appointments_auto_lock' => ':checked')
			)
		);
		wbk_opt()->add_option( 'wbk_appointments_auto_lock_group', 'select', __( 'Autolock for group booking services', 'webba-booking-lite' ), 'wbk_appointments_settings_section',
			array(
				'default' => 'lock',
                'popup' => __( 'Choose to either "Lock time slot" or "Reduce count of available places" when a group booking is turned on.', 'webba-booking-lite' ),
				'extra' => array(
					'lock' => __( 'Lock time slot', 'webba-booking-lite' ),
					'reduce' => __( 'Reduce count of available places', 'webba-booking-lite' ),
				),
				'dependency' => array('wbk_appointments_auto_lock' => ':checked')
			)
		);
		 
		wbk_opt()->add_option( 'wbk_appointments_default_status', 'select', __( 'Default booking status', 'webba-booking-lite' ), 'wbk_appointments_settings_section',
			array(
                'popup' => __( 'Specify the default status assigned to newly created bookings.', 'webba-booking-lite' ),
				'default' => 'approved',
				'extra' => array(
					'approved' => __( 'Approved', 'webba-booking-lite' ),
					'pending' => __( 'Awaiting approval', 'webba-booking-lite' )
				)
			)
		);

		wbk_opt()->add_option( 'wbk_appointments_allow_payments', 'checkbox', __( 'Allow payments only for approved bookings', 'webba-booking-lite' ),
                               'wbk_appointments_settings_section',
                                array(
                                    'popup' => __( 'Turn on to restrict payment functionality to approved bookings only.', 'webba-booking-lite' ),
                                ),
                                'advanced' );

		wbk_opt()->add_option( 'wbk_allow_coupons', 'checkbox', __( 'Coupons', 'webba-booking-lite' ), 'wbk_appointments_settings_section', 
                                array(
                                    'popup' => __( 'Turn on to activate the coupon feature in the booking system. Read more about <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/set-up-frontend-booking-process/coupons/">Coupns setup</a>.', 'webba-booking-lite' ),
                                ), 'advanced' );

		wbk_opt()->add_option( 'wbk_appointments_delete_not_paid_mode', 'select', __( 'Delete unpaid bookings', 'webba-booking-lite' ), 'wbk_appointments_settings_section',
			array(
				'default' => 'on_booking',
				'extra' => array(
					'disabled' => __( 'Disabled', 'webba-booking-lite' ),
					'on_booking' => __( 'Set expiration time on booking', 'webba-booking-lite' ),
					'on_approve' => __( 'Set expiration time on approve', 'webba-booking-lite' )
				),
				'popup' => __( 'Turn on to automatically delete unpaid bookings.', 'webba-booking-lite' ) . '<br />' 
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_appointments_delete_payment_started', 'select', __( 'Delete unpaid bookings with started payment.', 'webba-booking-lite' ), 'wbk_appointments_settings_section',
			array(
				'default' => 'delete',
				'extra' => array(
					'skip' => __( 'Do not delete bookings with started transaction', 'webba-booking-lite' ),
					'delete' => __( 'Delete bookings with started transaction', 'webba-booking-lite' )
				),
				'popup' => __( 'Choose whether to automatically remove unpaid bookings that have already initiated the payment process.', 'webba-booking-lite' ),
				'dependency' => array('wbk_appointments_delete_not_paid_mode' => 'on_booking|on_approve')
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_appointments_expiration_time', 'text', __( 'Time to pay (in minutes)', 'webba-booking-lite' ), 'wbk_appointments_settings_section',
			array(
				'default' => '10',
                'popup' => __( 'Specify the time given to the customer (in minutes) for completing the payment before the booking is automatically deleted.', 'webba-booking-lite' ),
				'dependency' => array('wbk_appointments_delete_not_paid_mode' => 'on_booking|on_approve')
            ),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_cancellation_buffer', 'text', __( 'Cancellation buffer (in minutes)', 'webba-booking-lite' ), 'wbk_appointments_settings_section',
			array(
				'popup' => __( 'Set the cutoff time (in minutes) before the scheduled booking when customers cannot cancel or modify their bookings.', 'webba-booking-lite' )
            ),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_appointments_allow_cancel_paid', 'select', __( 'Allow cancellation of paid bookings', 'webba-booking-lite' ), 'wbk_appointments_settings_section',
			array(
				'default' => 'disallow',
				'extra' => array(
					'allow' => __( 'Allow', 'webba-booking-lite' ),
					'disallow' => __( 'Disallow', 'webba-booking-lite' )
				),
				'popup' => __( 'Turn on to allow customers to cancel their paid bookings.', 'webba-booking-lite' )
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_appointments_only_one_per_slot', 'checkbox', __( 'Allow only one booking per time slot from an email', 'webba-booking-lite' ), 'wbk_appointments_settings_section',
			array(
				'popup' => __( 'Turn on to restrict customers from making multiple bookings for the same time slot using the same email address.', 'webba-booking-lite' )
            ),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_appointments_only_one_per_day', 'checkbox', __( 'Allow only one booking per day from an email', 'webba-booking-lite' ), 'wbk_appointments_settings_section',
			array(
				'popup' => __( 'Turn on to restrict customers from making multiple bookings for the same day using the same email address.', 'webba-booking-lite' )
            ),
            'advanced'
		);		 
		wbk_opt()->add_option( 'wbk_appointments_only_one_per_service', 'checkbox', __( 'Allow only one booking per service from an email', 'webba-booking-lite' ), 'wbk_appointments_settings_section',
			array(
				'popup' => __( 'Turn on to restrict customers from making multiple bookings for the same service using the same email address.', 'webba-booking-lite' )
            ),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_appointments_expiration_time_pending', 'text', __( 'Delete pending bookings (in minutes)', 'webba-booking-lite' ), 'wbk_appointments_settings_section',
			array(
				'default' => '0',
				'popup' => __( 'Specify the minutes (X) after which "Awaiting Approval" bookings will be automatically deleted. To disable automatic deletion, set the value to 0.', 'webba-booking-lite' ) 
                 ),
                'advanced'
		);
		wbk_opt()->add_option( 'wbk_appointments_autolock_avail_limit', 'text', __( 'Maximum number of bookings at a specific time', 'webba-booking-lite' ), 'wbk_appointments_settings_section',
			array(
				'popup' => __( 'Set the system-wide maximum number of bookings allowed at any given time for all services. Leave it empty for no restrictions.', 'webba-booking-lite' )
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_appointments_limit_by_day', 'text', __( 'Maximum number of bookings on a specific day', 'webba-booking-lite' ), 'wbk_appointments_settings_section',
			array(
				'popup' => __( 'Set the limit for the maximum number of bookings across all services in a day. Leave it empty for no restrictions.', 'webba-booking-lite' )  
            ),
            'advanced'
		);
	 
		wbk_opt()->add_option( 'wbk_appointments_lock_time slot_if_parital_booked', 'select_multiple', __( 'Lock time slot if at least one place is booked', 'webba-booking-lite' ), 'wbk_appointments_settings_section',
			array(
				'extra' => WBK_Model_Utils::get_services(),
				'popup' => __( 'Select the services for which a time slot will be automatically locked once at least one place is booked. Note: With autolock turned on, connected service bookings are considered when locking time slots.', 'webba-booking-lite' )  
						   
            ),
            'advanced'            
		);
		wbk_opt()->add_option( 'wbk_appointments_lock_day_if_time slot_booked', 'select_multiple', __( 'Lock whole day if at least one time slot is booked', 'webba-booking-lite' ), 'wbk_appointments_settings_section',
			array(
				'extra' => WBK_Model_Utils::get_services(),
				'popup' => __( '"Select the services for which a whole day will be automatically locked once at least one time slot is booked. 
Note: With autolock turned on, connected service bookings are considered when locking a day."', 'webba-booking-lite' ) 
						    
            ),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_appointments_lock_one_before_and_one_after', 'select_multiple', __( 'Lock one time slot before and after booking', 'webba-booking-lite' ), 'wbk_appointments_settings_section',
			array(
				'extra' => WBK_Model_Utils::get_services(),
				'popup' => __( '"Select the services for which time slots before and after the booking will be automatically locked.
Note: With autolock turned on, connected service bookings are considered when locking time slots."', 'webba-booking-lite' ) 
						   
			),
            'advanced'
		);

 

		wbk_opt()->add_option( 'wbk_appointments_special_hours', 'textarea', __( 'Special business hours', 'webba-booking-lite' ), 'wbk_appointments_settings_section',
			array(
				'popup' => __( 'Modify the business hours of specific services on particular dates.', 'webba-booking-lite' ) . '<br />' .
						   __( 'Example 1: 1 01/15/2023 15:00-18:00', 'webba-booking-lite' ) . '<br />' .
						   __( 'This indicates that the service with the ID 1 will be available on 01/15/2023 from 15:00 to 18:00.', 'webba-booking-lite' ) . '<br />' . 
                           __( 'Example 2: 01/15/2023 15:00-18:00', 'webba-booking-lite' ) . '<br />' . 
                           __( 'This means that all services will be available on 01/15/2023 from 15:00 to 18:00.', 'webba-booking-lite' )  
            ),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_check_short_code', 'checkbox', __( 'Load CSS & JS only on the booking page', 'webba-booking-lite' ), 'wbk_general_settings_section',
			array(
				'popup' => __( 'Turn on to load CSS and JS files only when the booking form shortcode is detected on the page, optimizing performance for non-booking pages.', 'webba-booking-lite' )
            ),
            'advanced'
		);
		 
        wbk_opt()->add_option( 'wbk_disable_day_on_all_booked', 'select', __( 'Disable booked dates in calendar', 'webba-booking-lite' ),
			'wbk_mode_settings_section',
            array(
				'default' => 'foreach',
				'extra' => array(
					'disabled' => __( 'No', 'webba-booking-lite' ),
			    	'enabled' => __( 'Yes', 'webba-booking-lite' ),
                    'popup' => __('Disable date in the calendar if no free time slots found.','webba-booking-lite'),
                    'enabled_plus' => __( 'Yes (including bookings from neighboring services.)', 'webba-booking-lite' )
				)
			)
		);
 
		wbk_opt()->add_option( 'wbk_holydays', 'text', __( 'Holidays', 'webba-booking-lite' ), 'wbk_general_settings_section',
			array(
				'popup' => __( 'Set dates when your business is closed, and no new bookings will be accepted.', 'webba-booking-lite' ),
                'default' => ''
			)
		);
		wbk_opt()->add_option( 'wbk_recurring_holidays', 'checkbox', __( 'Recurring holidays', 'webba-booking-lite' ), 'wbk_general_settings_section',
			array(
				'checkbox_value' => 'true',
				'popup' => __( 'Turn on to set holidays as recurring yearly.', 'webba-booking-lite' ),
                'default' => 'true'
			)
		);
 
 
		wbk_opt()->add_option( 'wbk_email_customer_book_status', 'checkbox', __( 'Send booking confirmation email (to customer)', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
                'default' => 'true',
				'checkbox_value' => 'true',
				'popup' => __( 'Turn on to automatically send a booking confirmation email to the customer after booking.', 'webba-booking-lite' )
			)
		);

		if ( wbk_fs()->is__premium_only() && wbk_fs()->can_use_premium_code() ) {
			wbk_opt()->add_option( 'wbk_email_customer_book_status_generate_ical', 'checkbox', __( 'Attach iCal file to the email', 'webba-booking-lite' ), 'wbk_email_settings_section',
				array(
                    'default' => '',
					'checkbox_value' => 'true',
					'popup' => __( 'Turn on to attach iCal file to the booking confirmation email sent to customer.', 'webba-booking-lite' ),
                    'dependency' => array( 'wbk_email_customer_book_status' => ':checked' )
                    
				)
			);
		}
		wbk_opt()->add_option( 'wbk_email_customer_book_subject', 'text', __( 'Booking confirmation email subject line (booking done by the customer)', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
                'default' => 'Time reserved',
				'popup' => __('Customize the subject line for the email sent to the customer after they make a booking', 'webba-booking-lite' )  . '<a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>',
                'dependency' => array( 'wbk_email_customer_book_status' => ':checked' )
                
			
            )
		);
		wbk_opt()->add_option( 'wbk_email_customer_book_message', 'editor',
                                __( 'Booking confirmation email message (booking done by the customer)', 'webba-booking-lite' ), 'wbk_email_settings_section',
                                array( 
                                    'popup' => __( 'Customize the email message sent to the customer after they make a booking. ', 'webba-booking-lite' )  . '<a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>',
                                    'default' => '<p>Dear #customer_name,</p><p>You have successfully booked #service_name on #appointment_day at #appointment_time.</p>',
                                    'dependency' => array( 'wbk_email_customer_book_status' => ':checked' )
                                ));

 
		wbk_opt()->add_option( 'wbk_email_customer_manual_book_subject', 'text', __( 'Booking confirmation email subject line (booking done by the admin)', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(               
                'dependency' => array( 'wbk_email_customer_book_status' => ':checked' ),
				'popup' => 'Customize the subject line for the email sent to the customer after a booking is made by an admin. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>'
			)
		);
		wbk_opt()->add_option( 'wbk_email_customer_manual_book_message', 'editor', __( 'Booking confirmation email message (booking done by the admin)', 'webba-booking-lite' ),
                                'wbk_email_settings_section', array( 
                                    'popup' => 'Customize the email message sent to the customer after a booking is made by an admin. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>',
                                    'dependency' => array( 'wbk_email_customer_book_status' => ':checked' )                                     
                                ));
		wbk_opt()->add_option( 'wbk_email_customer_approve_status', 'checkbox', __( 'Send booking approval email (to customer)', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
				'checkbox_value' => 'true',
				'popup' => __( 'Turn on to automatically send a notification email to the customer once their booking request is approved. ', 'webba-booking-lite' )
            ),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_email_customer_approve_subject', 'text', __( 'Booking approval email subject line', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
				'default' => __( 'Your booking has been approved', 'webba-booking-lite' ),
				'popup' => 'Customize the subject line for the booking approval email. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/#subjectplaceholders">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>',
                'dependency' => array( 'wbk_email_customer_approve_status' => ':checked' )
			
            ),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_email_customer_approve_message', 'editor', __( 'Booking approval email message', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
				'default' => '<p>Your booking on #appointment_day at #appointment_time has been approved.</p>',
                'popup' => 'Customize the email message for the booking approval email. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/#subjectplaceholders">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>',
                'dependency' => array( 'wbk_email_customer_approve_status' => ':checked' )
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_email_customer_approve_copy_status', 'checkbox', __( 'Send admin a copy of booking approval email', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
				'checkbox_value' => 'true',
				'popup' => __( 'Turn on if you want to send a copy of the booking approval email to the admin. Please note that the copy will be sent only if the booking is approved via the approval link.', 'webba-booking-lite' ),
                'dependency' => array( 'wbk_email_customer_approve_status' => ':checked' )
			
                ),
            'advanced'
    	);
		wbk_opt()->add_option( 'wbk_email_adimn_appointment_cancel_status', 'checkbox', __( 'Send booking cancelation email (to admin)', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
				'checkbox_value' => 'true',
				'popup' => __( 'Turn on to automatically send a booking cancelation email to the admin after a booking is canceled.', 'webba-booking-lite' )
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_email_adimn_appointment_cancel_subject', 'text', __( 'Booking cancelation email subject line', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
				'default' => __( 'Booking canceled', 'webba-booking-lite' ),
				'popup' => 'Customize the subject line for the booking cancelation email sent to the admin. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>',
                'dependency' => array( 'wbk_email_adimn_appointment_cancel_status' => ':checked' )
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_email_adimn_appointment_cancel_message', 'editor', __( 'Booking cancelation email message', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
				'default' => '<p>#customer_name canceled the appointment with #service_name on #appointment_day at #appointment_time</p>',
                'dependency' => array( 'wbk_email_adimn_appointment_cancel_status' => ':checked' ),
                'popup' => 'Customize the email message for the booking cancelation email sent to the admin. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>',
			),
            'advanced'
		);



		wbk_opt()->add_option( 'wbk_email_customer_appointment_cancel_status', 'checkbox', __( 'Send booking cancelation email (to customer)', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
				'checkbox_value' => 'true',
				'popup' => __( 'Turn on to automatically send a booking cancelation email to the customer after a booking is canceled.', 'webba-booking-lite' )
			),
            'advanced'
		);
		 
		wbk_opt()->add_option( 'wbk_email_customer_appointment_cancel_subject', 'text', __( 'Booking cancelation email subject line', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
                'dependency' => array( 'wbk_email_customer_appointment_cancel_status' => ':checked' ),
				'default' => __( 'Your appointment canceled', 'webba-booking-lite' ),
				'popup' => 'Customize the subject line for the booking cancelation email sent to the customer. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>'
			
                ),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_email_customer_appointment_cancel_message', 'editor', __( 'Booking cancelation email message (cancelation done by the admin)', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
                'dependency' => array( 'wbk_email_customer_appointment_cancel_status' => ':checked' ),
				'default' => '<p>Your appointment with #service_name on #appointment_day at #appointment_time has been canceled</p>',
                'popup' => 'Customize the email message for the booking cancelation email sent to the customer when the cancellation is initiated by the admin. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>'
			
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_email_customer_bycustomer_appointment_cancel_message', 'editor', __( 'Booking cancelation email message  (cancelation done by the customer)', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
                'dependency' => array( 'wbk_email_customer_appointment_cancel_status' => ':checked' ),
				'default' => '<p>Your appointment with #service_name on #appointment_day at #appointment_time has been canceled</p>',
                'popup' => 'Customize the email message for the booking cancelation email sent to the customer when the cancellation is initiated by the customer. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>'
	
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_email_secondary_book_status', 'checkbox', __( 'Send booking cancelation email (to other customers in the group booking)', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
				'checkbox_value' => 'true',
				'popup' => __( 'Turn on to automatically send a booking cancelation email to all the customers added to the group booking.', 'webba-booking-lite' )
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_email_secondary_book_subject', 'text', __( 'Booking cancelation email subject line', 'webba-booking-lite' ),
                               'wbk_email_settings_section',
                                array( 
                                    'checkbox_value' => 'true',
                                    'popup' => 'Customize the email message for the booking cancelation email sent to the customers in the group booking. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>',
	                                'dependency' => array( 'wbk_email_secondary_book_status' => ':checked' ) ),
                                'advanced' );
		wbk_opt()->add_option( 'wbk_email_secondary_book_message', 'editor', __( 'Booking cancelation email message', 'webba-booking-lite' ), 
                               'wbk_email_settings_section',
                                array( 'checkbox_value' => 'true', 
                                        'popup' => 'Customize the email message for the booking cancelation email sent to the customers in the group booking. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>',
	                                    'dependency' => array( 'wbk_email_secondary_book_status' => ':checked',
                                         ) ),
                                'advanced' );
		
        
        wbk_opt()->add_option( 'wbk_email_admin_book_status', 'checkbox', __( 'Send booking confirmation email (to admin)', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
				'checkbox_value' => 'true',
                'default' => 'true',
				'popup' => __( 'Turn on to automatically send a booking confirmation email to the admin.', 'webba-booking-lite' )
			)
		);
		if ( wbk_fs()->is__premium_only() && wbk_fs()->can_use_premium_code() ) {
			wbk_opt()->add_option( 'wbk_email_admin_book_status_generate_ical', 'checkbox', __( 'Attach iCal file to the email', 'webba-booking-lite' ), 'wbk_email_settings_section',
				array(
                    'dependency' => array( 'wbk_email_admin_book_status' => ':checked' ),
					'checkbox_value' => 'true',
					'popup' => __( 'Turn on to attach iCal file to the booking confirmation email sent to the admin.', 'webba-booking-lite' )
				)
			);
		}
		wbk_opt()->add_option( 'wbk_email_admin_book_subject', 'text', __( 'Booking confirmation mail subject line', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
                'default' => __( 'New booking of #service_name', 'webba-booking-lite' ),
                'dependency' => array( 'wbk_email_admin_book_status' => ':checked' ),
				'popup' => 'Customize the subject line for the email sent to the admin after a booking has been made. List of available placeholders. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>'
			)
		);
       
		wbk_opt()->add_option( 'wbk_email_admin_book_message', 'editor', __( 'Booking confirmation email message', 'webba-booking-lite' ), 
                               'wbk_email_settings_section', 
                                array(
                                    'default' => '<p>Details of booking:</p><p>Date: #appointment_day<br/>Time: #appointment_time<br/>Customer name: #customer_name<br/>Customer phone: #customer_phone<br/>Customer email: #customer_email<br/>Customer comment: #customer_comment</p><p></p>',
                                    'dependency' => array( 'wbk_email_admin_book_status' => ':checked' ),
                                    'popup' => 'Customize the email message sent to the admin after a booking has been made. List of available placeholders. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>'

                                ) );
		                    
       
        wbk_opt()->add_option( 'wbk_email_admin_paymentrcvd_status', 'checkbox', __( 'Send payment received email (to admin)', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
				'checkbox_value' => 'true',
				'popup' => __( 'Turn on to automatically send an email notification to the administrator when a payment for a booking is received. ', 'webba-booking-lite' )
			),
            'advanced'
		);

		wbk_opt()->add_option( 'wbk_email_admin_paymentrcvd_subject', 'text', __( 'Payment received email subject line', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
                'dependency' => array( 'wbk_email_admin_paymentrcvd_status' => ':checked' ),
                'defaul' => 'Payment from #customer_name received',
                'popup' => 'Customize the subject line for the payment received email sent to the admin. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>'
			),
            'advanced'
		);
        
		wbk_opt()->add_option( 'wbk_email_admin_paymentrcvd_message', 'editor', __( 'Payment received email message', 'webba-booking-lite' ), 
                               'wbk_email_settings_section',
                                array(  
                                    'popup' => 'Customize the email message for the payment received email sent to the admin. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>',
	
                                    'dependency' => array( 'wbk_email_admin_paymentrcvd_status' => ':checked' ) ),
                                'advanced' );

		wbk_opt()->add_option( 'wbk_email_customer_paymentrcvd_status', 'checkbox', __( 'Send payment received email (to customer)', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
				'checkbox_value' => 'true',
                'default' =>  'Payment from #customer_name for booking of #service_name on #appointment_day at #appointment_time received. <br>Total amount: #total_amount',
				'popup' => __( 'Turn on to automatically send an email notification to the customer when a payment for a booking is received. ', 'webba-booking-lite' )
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_email_customer_paymentrcvd_subject', 'text', __( 'Payment received email subject line', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
                'dependency' => array( 'wbk_email_customer_paymentrcvd_status' => ':checked' ),
                'default' => 'Your payment received',
				'popup' => 'Customize the subject line for the payment received email sent to the customer. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>'
			
            ),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_email_customer_paymentrcvd_message', 'editor', __( 'Payment received email message', 'webba-booking-lite' ), 
                               'wbk_email_settings_section',
                                array(
                                    'default' => 'Payment from #customer_name for booking of #service_name on #appointment_day at #appointment_time received. <br>Total amount: #total_amount',
                                    'dependency' => array( 'wbk_email_customer_paymentrcvd_status' => ':checked' ),
                                    'popup' => 'Customize the email message for the payment received email sent to the customer. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>'
			
                                ),
                                'advanced' );

		wbk_opt()->add_option( 'wbk_email_customer_arrived_status', 'checkbox', __( 'Send status "Arrived" email (to customer)', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
				'checkbox_value' => 'true',
				'popup' => __( 'Turn on to automatically send an email notification to the customer when the status of their booking is changed to "Arrived."', 'webba-booking-lite' )
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_email_customer_arrived_subject', 'text', __( 'Status "Arrived" email subject line', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
                'dependency' => array( 'wbk_email_customer_arrived_status' => ':checked' ),
				'popup' => 'Customize the subject line for the status "Arrived" email sent to the customer. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>'
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_email_customer_arrived_message', 'editor', __( 'Status "Arrived" email message', 'webba-booking-lite' ),
                               'wbk_email_settings_section',
                                array(
                                    'popup' => 'Customize the email message for the status "Arrived" email sent to the customer. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>',
                                    'dependency' => array( 'wbk_email_customer_arrived_status' => ':checked' ) ),
                               'advanced' );

 
		wbk_opt()->add_option( 'wbk_email_admin_daily_status', 'checkbox', __( 'Send reminder email (to admin)', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
				'checkbox_value' => 'true',
				'popup' => __( 'Turn on to send admin automatic email reminders for upcoming bookings.', 'webba-booking-lite' )
			),
            'advanced'
		);

		wbk_opt()->add_option( 'wbk_email_admin_daily_subject', 'text', __( 'Reminder email subject line', 'webba-booking-lite' ), 
                               'wbk_email_settings_section',
                                array( 
                                    'popup' => 'Customize the subject line for reminder email sent to the admin. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>',
                                    'dependency' => array( 'wbk_email_admin_daily_status' => ':checked' ), ),
                                'advanced' );

		wbk_opt()->add_option( 'wbk_email_admin_daily_message', 'editor', __( 'Reminder email message', 'webba-booking-lite' ),
                               'wbk_email_settings_section',
                                array( 
                                    'popup' => 'Customize the email message for reminder email sent to the admin. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>',
                                    'dependency' => array( 'wbk_email_admin_daily_status' => ':checked' ) ),
                                'advanced' );

		wbk_opt()->add_option( 'wbk_email_customer_daily_status', 'checkbox', __( 'Send reminder email (to customer)', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
				'checkbox_value' => 'true',
				'popup' => __( 'Turn on to send customers automatic email reminders for their upcoming bookings.', 'webba-booking-lite' )
            ),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_email_reminder_days', 'text', __( 'Send reminders to customers in X days', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
                'dependency' => array( 'wbk_email_customer_daily_status' => ':checked' ),
				'default' => '1',
				'popup' => __( 'Select the timing for the reminder notification. For instance, set the value to 0 for the day of booking, 1 for one day before the booking, 2 for two days before, and so on.', 'webba-booking-lite' )
            ),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_email_customer_daily_subject', 'text', __( 'Reminder email subject line ', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
                'dependency' => array( 'wbk_email_customer_daily_status' => ':checked' ),
				'popup' => 'Customize the subject line for reminder email sent to the customer. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>'
            ),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_email_customer_daily_message', 'editor', __( 'Reminder email message', 'webba-booking-lite' ), 
                               'wbk_email_settings_section',
                                array( 
                                    'popup' => 'Customize the email message for reminder email sent to the customer. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>',
                                    'dependency' => array( 'wbk_email_customer_daily_status' => ':checked' ) ),
                               'advanced' );

		$format = WBK_Date_Time_Utils::get_time_format();
		date_default_timezone_set( 'UTC' );
		$data_time = [];
		$data_keys = [];
		for( $i = 0; $i < 86400; $i += 600 ) {
			$data_time[] = wp_date( $format, $i, new DateTimeZone( date_default_timezone_get() ) );
			$data_keys[] = $i;
		}
		$data_time = array_combine( $data_keys, $data_time );
		wbk_opt()->add_option( 'wbk_email_admin_daily_time', 'select', __( 'Reminder sending time', 'webba-booking-lite' ), 'wbk_general_settings_section',
			array(
				'default' => '43200',
				'extra' => $data_time,
				'popup' => __( 'Set the preferred hour for email reminders sent to customers and admins, based on your local timezone.', 'webba-booking-lite' )  
            ),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_email_reminders_only_for_approved', 'checkbox', __( 'Send reminders only for approved bookings.', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
                'dependency' => array( 'wbk_email_customer_daily_status' => ':checked' ),
				'checkbox_value' => 'true',
				'popup' => __( 'Turn on to send reminder email notifications only for approved bookings.', 'webba-booking-lite' )
            ),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_email_customer_send_invoice', 'select', __( 'Send invoice to a customer (HTML format) ', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
				'default' => 'disabled',
				'extra' => array(
					'disabled' => __( 'Do not send invoice', 'webba-booking-lite' ),
					'onbooking' => __( 'Send invoice on booking', 'webba-booking-lite' ),
					'onapproval' => __( 'Send invoice on approval', 'webba-booking-lite' ),
					'onpayment' => __( 'Send invoice on payment complete', 'webba-booking-lite' )
				),
				'popup' => __( 'Choose whether you would like to send an invoice to the customer and specify when the invoice email should be sent.', 'webba-booking-lite' )
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_email_customer_invoice_subject', 'text', __( 'Invoice email subject line', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
                'dependency' => array('wbk_email_customer_send_invoice' => 'onbooking|onapproval|onpayment'),
				'default' => __( 'Invoice', 'webba-booking-lite' ),
				'popup' => 'Customize the subject line for invoice email. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>'
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_email_current_invoice_number', 'text', __( 'Current invoice number', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
                'dependency' => array('wbk_email_customer_send_invoice' => 'onbooking|onapproval|onpayment'),
				'default' => '1',
				'popup' => __( 'Set the starting number for your invoices. Use the placeholder #invoice_number in your notifications. Each time a customer makes a payment, the value of this option will be increased by one.' )
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_email_send_invoice_copy', 'checkbox', __( 'Send admin a copy of invoice email', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
                'dependency' => array('wbk_email_customer_send_invoice' => 'onbooking|onapproval|onpayment'),
				'checkbox_value' => 'true',
				'popup' => __( 'Turn on if you want to send a copy of the invoice email to the admin.', 'webba-booking-lite' )
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_email_on_update_booking_subject', 'text', __( 'Notification subject (when booking changes)', 'webba-booking-lite' ),
                               'wbk_email_settings_section', array(
                                    'popup' => __( 'Customize the subject line for booking changes notification email.', 'webba-booking-lite' )                                
                               ),
                               'advanced' );

		wbk_opt()->add_option( 'wbk_email_override_replyto', 'checkbox', __( 'Override default reply-to headers with booking-related data', 'webba-booking-lite' ), 'wbk_email_settings_section',
			array(
				'checkbox_value' => 'true',
				'popup' => __( 'When Enabled:Customer Notifications: The reply-to email address is set to the email specified in the service settings.
Admin Notifications: The reply-to email address is set to the customer\'s email address. When Disabled: The \'From: email\' value is used as the reply-to address for notifications.', 'webba-booking-lite' )
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_from_name', 'text', __( 'From: name', 'webba-booking-lite' ), 'wbk_email_settings_section', array(
            'popup' => __( 'Enter the name that will be displayed as the sender in the email notifications.', 'webba-booking-lite' )

        ) );
		wbk_opt()->add_option( 'wbk_from_email', 'text', __( 'From: email', 'webba-booking-lite' ), 'wbk_email_settings_section', array(
            'popup' => __( 'Enter the email that will be displayed as the sender in the email notifications.', 'webba-booking-lite' )
        ) );
		wbk_opt()->add_option( 'wbk_super_admin_email', 'text', __( 'Send copies of admin email notifications to addresses', 'webba-booking-lite' ), 'wbk_email_settings_section', array(
                                    'popup' => __( 'Enter the email addresses where you want to receive copies of admin notifications. Separate multiple email addresses with comma.', 'webba-booking-lite' )
                                ), 'advanced' );
		wbk_opt()->add_option( 'wbk_email_landing', 'text', __( 'Notifications landing page', 'webba-booking-lite' ), 'wbk_email_settings_section', 
			array(
				'popup' => __( 'Specify the landing page URL for payment or cancelation. This page should include the [webbabooking] shortcode.', 'webba-booking-lite' )
            ), 'advanced'
		);
		 
	  
		wbk_opt()->add_option( 'wbk_date_input_dropdown_count', 'text', __( 'Number of dates in the dropdown input', 'webba-booking-lite' ), 'wbk_mode_settings_section',
			array(
				'default' => '30',
				'popup' => __( 'Used only for dropdown date select', 'webba-booking-lite' ),
				'dependency' => array( 'wbk_date_input' => 'dropdown' )
			)
		);
		wbk_opt()->add_option( 'wbk_avaiability_popup_calendar', 'text', __( 'Number of dates in the calendar', 'webba-booking-lite' ), 'wbk_mode_settings_section',
			array(
				'default' => '360',
				'popup' => __( 'Specify the number of dates displayed in the calendar.', 'webba-booking-lite' )
			),
            'advanced'
		);

        wbk_opt()->add_option( 'wbk_time slot_time_string', 'select', __( 'Time slot format', 'webba-booking-lite' ), 'wbk_mode_settings_section',
			array(
                'popup' => __( 'Choose between displaying only the start time or both the start and end times in the time slots.', 'webba-booking-lite' ),
				'default' => 'start',
				'extra' => array(
					'start' => __( 'Start', 'webba-booking-lite' ),
					'start_end' => __( 'Start', 'webba-booking-lite' ) . ' - ' . __( 'end', 'webba-booking-lite' )
				)
			)
		);
	  
		wbk_opt()->add_option( 'wbk_csv_delimiter', 'select', __( 'CSV delimiter', 'webba-booking-lite' ), 'wbk_general_settings_section',
			array(
				'default' => 'Semicolon',
                 'popup' => __( 'If your date format includes a comma, use a semicolon. Otherwise, select the comma.', 'webba-booking-lite' ),
				'extra' => array(
					'comma' => __( 'Comma', 'webba-booking-lite' ),
					'semicolon' => __( 'Semicolon', 'webba-booking-lite' ) 
                    
				)
            ),'advanced'
		);
		 
		wbk_opt()->add_option( 'wbk_pickadate_load', 'checkbox', __( 'Load Pickadate javascript', 'webba-booking-lite' ), 'wbk_general_settings_section',
			array(
				'checkbox_value' => 'yes',
				'default' => 'yes',
				'popup' => __( 'Turn off if other plugins in your WordPress installation are using the pickadate date picker.', 'webba-booking-lite' )
            ), 'advanced'
		);
 
	 
        wbk_opt()->add_option( 'wbk_general_tax', 'text', __( 'Tax', 'webba-booking-lite' ), 'wbk_general_settings_section',
			array(
				'popup' => __( 'Tax used for online payments.', 'webba-booking-lite' ),
               
                'default' => '0'
			)
		);

		wbk_opt()->add_option( 'wbk_do_not_tax_deposit', 'checkbox', __( 'Do not tax the deposit (service fee)', 'webba-booking-lite' ), 'wbk_general_settings_section',
			array(
				'checkbox_value' => 'true',
				'popup' => __( 'Turn on to avoid adding tax to the deposit. Important note: when this is turned on, do not use subtotal and tax placeholders.', 'webba-booking-lite' )
            ),'advanced'
		);
		wbk_opt()->add_option( 'wbk_price_fractional', 'text', __( 'Fractional digits in price', 'webba-booking-lite' ), 'wbk_general_settings_section',
			array(
				'default' => '2',
				'popup' => __( 'Write the number of decimal places to show for prices. E.g. Write 1 for prices to appear as 25.1 or 2 for prices to appear as 25.10.', 'webba-booking-lite' )
            ),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_price_separator', 'text', __( 'Fraction separator in prices', 'webba-booking-lite' ), 'wbk_general_settings_section',
			array(
				'default' => '.',
				'popup' => __('Choose the symbol or character to separate decimals in prices. E.g. Use either a period (.) or a comma (,).', 'webba-booking-lite')
			)
		);
		 
		wbk_opt()->add_option( 'wbk_general_dynamic_placeholders', 'text', __( 'List of dynamic placeholders', 'webba-booking-lite' ), 'wbk_general_settings_section',
			array(
                'default' => '',
				'popup' => __( 'Enter a comma-separated list of placeholders to remove from the string if they are not replaced with values. This is useful if you are using different custom fields for services and as a result some custom field placeholders are not replaced.','webba-booking-lite')
            ),'advanced'
		);
		wbk_opt()->add_option( 'wbk_load_js_in_footer', 'checkbox', __( 'Load javascript files in the footer', 'webba-booking-lite' ), 'wbk_general_settings_section',
			array(
				'checkbox_value' => 'true',
				'popup' => __( 'Enabling this option may increase page loading time in some cases.', 'webba-booking-lite' )
            ),
            'avanced'
		);
		wbk_opt()->add_option( 'wbk_service_label', 'text', __( 'Service label', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Select a service', 'webba-booking-lite' ),
				'popup' => __('Service label on the booking form.','webba-booking-lite')
			)
		);
		wbk_opt()->add_option( 'wbk_category_label', 'text', __( 'Category label', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Select category', 'webba-booking-lite' ),
				'popup' => __('Category label on the booking form.','webba-booking-lite')
			)
		);
		wbk_opt()->add_option( 'wbk_date_basic_label', 'text', __( 'Date label', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Book an appointment on', 'webba-booking-lite' ),
				'popup' => __('Date label on the booking form.','webba-booking-lite')
			)
		);

		wbk_opt()->add_option( 'wbk_date_input_placeholder', 'text_alfa_numeric', __( 'Select date input placeholder', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'date', 'webba-booking-lite' ),
                'popup' => __('Select date input placeholder on the booking form.','webba-booking-lite')
			)
		);
	  
		wbk_opt()->add_option( 'wbk_form_label', 'text', __( 'Booking form title', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => '',
				'popup' => __( 'Text above the booking form. List of available placeholders.', 'webba-booking-lite' )
            ),
            'advanced'
		);

		wbk_opt()->add_option( 'wbk_booked_text', 'text', __( 'Booked time slot text', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Booked', 'webba-booking-lite' ),
				'popup' => __( 'Text on a booked time slot.','webba-booking-lite' ) 
                
			),
            'advanced'
		);
	    
		wbk_opt()->add_option( 'wbk_name_label', 'text', __( 'Name label', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Name', 'webba-booking-lite' ),
                'popup' => __( 'Name label on the booking form.', 'webba-booking-lite') 
			)
		);
		wbk_opt()->add_option( 'wbk_email_label', 'text', __( 'Email label', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Email', 'webba-booking-lite' ),
                'popup' => __( 'Email label on the booking form.', 'webba-booking-lite') 
			)
		);
		wbk_opt()->add_option( 'wbk_phone_label', 'text', __( 'Phone label', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Phone', 'webba-booking-lite' ),
                'popup' => __( 'Phone label on the booking form.', 'webba-booking-lite') 
            )
        
		);
		wbk_opt()->add_option( 'wbk_comment_label', 'text', __( 'Comment label', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Comment', 'webba-booking-lite' ),
                'popup' => __( 'Comment label on the booking form.', 'webba-booking-lite') 
			)
		);
        
        wbk_opt()->add_option( 'wbk_book_items_quantity_label', 'text', __( 'Quantity label', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'How many people per time slot?', 'webba-booking-lite' ),
				'popup' => __( 'Quantity label on the booking form for group bookings. Available placeholders: #service', 'webba-booking-lite' ) 
			)
		);

		wbk_opt()->add_option( 'wbk_book_thanks_message', 'editor', __( 'Thank you message', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' =>  '',
                'popup' => __( 'Customize the thank you message displayed after a booking is made. Leave it empty to use the default formatted thank you message.', 'webba-booking-lite')
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_book_not_found_message', 'text', __( 'Time slots not found message', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Unfortunately we were unable to meet your search criteria. Please change the criteria and try again.', 'webba-booking-lite' ),
				'popup' => __( 'Message displayed when no time slots are found for the selected service and date.', 'webba-booking-lite' )
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_payment_pay_with_paypal_btn_text', 'text_alfa_numeric', __( 'PayPal option label', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Pay now with PayPal', 'webba-booking-lite' ),
                'popup' => __( 'Label for PayPal payment method', 'webba-booking-lite' )
			),
            'advanced'
		);

      
        wbk_opt()->add_option( 'wbk_stripe_button_text', 'text_alfa_numeric', __( 'Credit card option label', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Pay with credit card', 'webba-booking-lite' ),
                'popup' => __( 'Label for Stripe payment method', 'webba-booking-lite' )
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_stripe_card_element_error_message', 'text', __( 'Stripe card element error message', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'incorrect input', 'webba-booking-lite' ),
                'popup' => __( 'Error message that appears if an issue occurs with the Stripe payment method. To show this message go to Stripe -> Advanced Settings and turn on "override Stripe card element error messages".', 'webba-booking-lite' )
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_stripe_api_error_message', 'text', __( 'Stripe API error message', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Payment failed: #response', 'webba-booking-lite' ),
				'popup' => __( 'Stripe API error message during payment processing. Placeholders: #response.', 'webba-booking-lite' )
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_pay_on_arrival_button_text', 'text_alfa_numeric', __( 'Pay on arrival option label', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Pay on arrival', 'webba-booking-lite' ),
                'popup' => __( 'Label for Pay on arrival payment method.', 'webba-booking-lite' )
			),
            'advanced'
		);

		wbk_opt()->add_option( 'wbk_pay_on_arrival_message',
                 'text', __( 'Message for Pay on arrival payment method', 'webba-booking-lite' ), 
                 'wbk_translation_settings_section',
                  array(
                     'default' => __( 'Pay on arrival', 'webba-booking-lite' ),
                     'popup' => __( 'Message for Pay on arrival payment method.', 'webba-booking-lite' )
                  ),
                  'advanced'
                
        );
		wbk_opt()->add_option( 'wbk_bank_transfer_button_text', 'text_alfa_numeric', __( 'Bank transfer option label', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Pay by bank transfer', 'webba-booking-lite' ),
                'popup' => __( 'Label for Bank transfer payment method.', 'webba-booking-lite' )
			),
            'advanced'
		);

		wbk_opt()->add_option( 'wbk_bank_transfer_message', 'text', __( 'Message for Bank transfer payment method', 'webba-booking-lite' ),
                               'wbk_translation_settings_section',
                                array( 'default' => __( 'Pay by the bank transfer.', 'webba-booking-lite' ),
                                       'popup' => __( 'Message for Bank transfer payment method.', 'webba-booking-lite' )
                                ),
                                'advanced' );

		wbk_opt()->add_option( 'wbk_coupon_field_placeholder', 'text_alfa_numeric', __( 'Coupon code field placeholder', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Coupon code', 'webba-booking-lite' ),
                'popup' => __( 'Placeholder shown in the coupon field', 'webba-booking-lite' )
			),
            'advanced'
		);

        /*
		wbk_opt()->add_option( 'wbk_coupon_applied', 'text_alfa_numeric', __( 'Coupon success message', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Coupon applied', 'webba-booking-lite' ),
                'popup' => __( 'Message shown when  shown in the coupon field', 'webba-booking-lite' )
			)
		);
		wbk_opt()->add_option( 'wbk_coupon_not_applied', 'text', __( 'Coupon failed message', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Coupon not applied', 'webba-booking-lite' )
			)
		);
        */
		
		wbk_opt()->add_option( 'wbk_product_meta_key', 'text', __( 'Meta key for WooCommerce product', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Booking', 'webba-booking-lite' ),
                'popup' => __('Label for services in WooCommerce.', 'webba-booking-lite')
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_woo_button_text', 'text_alfa_numeric', __( 'WooCommerce option label', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Add to cart', 'webba-booking-lite' ),
                 'popup' => __('User in the cart item', 'webba-booking-lite')
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_woo_error_add_to_cart', 'text', __( 'Add to cart error message', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Booking not added to card', 'webba-booking-lite' ),
                'popup' => __( 'Error message that appears if an issue occurs with adding a booking to WooCommerce cart.', 'webba-booking-lite' )
			),
            'advanced'
		);
  


		wbk_opt()->add_option( 'wbk_payment_details_title', 'text_alfa_numeric', __( 'Payment details title', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Payment details', 'webba-booking-lite' ),
                'popup' => __('Message above the payment details.', 'webba-booking-lite')
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_payment_item_name', 'text', __( 'Payment item text', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => '#service_name on #appointment_day at #appointment_time',
                'popup' => '<a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>'
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_payment_price_format', 'text', __( 'Price format', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
                'default' => '$#price',
				'popup' => __( 'Price format on the booking form. Required placeholder: #price. E.g.: $#price.', 'webba-booking-lite' )
            )
		);
		wbk_opt()->add_option( 'wbk_payment_subtotal_title', 'text', __( 'Subtotal title', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Subtotal', 'webba-booking-lite' ),
                'popup' => __( 'Label for the subtotal amount in payment details', 'webba-booking-lite' )    
			),
            'advanced'
		);
        wbk_opt()->add_option( 'wbk_tax_label', 'text', __( 'Tax label', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Tax', 'webba-booking-lite' ),
                'popup' => __( 'Label for the tax in payment details', 'webba-booking-lite' )                
            ),
            'advanced'
		);
    	wbk_opt()->add_option( 'wbk_service_fee_description', 'text', __( 'Service fee label', 'webba-booking-lite' ), 'wbk_translation_settings_section',array(
				'default' => __( 'Service fee', 'webba-booking-lite' ),
                'popup' => __( 'Label for the service fee in payment details', 'webba-booking-lite' )                
            ),
            'advanced' );

        wbk_opt()->add_option( 'wbk_payment_discount_item', 'text', __( 'Discount label', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Discount', 'webba-booking-lite' ),
                'popup' => __('Label for the discount', 'wbk ')
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_payment_total_title', 'text', __( 'Total title', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Total', 'webba-booking-lite' ),
                'popup' => __( 'Label for the total amount in payment details', 'webba-booking-lite' )    
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_nothing_to_pay_message', 'text', __( 'No bookings for payment message', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'There are no bookings available for payment.', 'webba-booking-lite' ),
                'popup' => __( 'Message shown when there are no bookings available for payment', 'webba-booking-lite' )
			),
            'advanced'
		);	

        // continue here
		wbk_opt()->add_option( 'wbk_show_locked_as_booked', 'checkbox', __( 'Show locked time slots as booked', 'webba-booking-lite' ), 'wbk_mode_settings_section',
			array(
                'popup' => __( 'Turn on to show locked time slots as "Booked".', 'webba-booking-lite' ),
				'checkbox_value' => 'yes'
            ),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_allow_attachemnt', 'checkbox', __( 'Allow attachments', 'webba-booking-lite' ), 'wbk_general_settings_section',
			array(
				'checkbox_value' => 'yes',           
				'popup' => __( 'Turn on to allow users to attach files in the booking form. Please include the file input field in the custom form. For more information, see <a href="https://webba-booking.com/documentation/set-up-frontend-booking-process/using-custom-fields-in-the-booking-form/" target="_blank" rel="noopener noreferrer">Custom fields</a>.','webba-booking-lite')
                ),
                'advanced'
		);
		wbk_opt()->add_option( 'wbk_delete_attachemnt', 'checkbox', __( 'Automatically delete attachments', 'webba-booking-lite' ), 'wbk_general_settings_section',
			array(
				'checkbox_value' => 'yes',
                'default' => 'yes',
				'popup' => __( 'Highly Recommended: Turn this on to automatically delete the attachment as soon as the notification is sent.', 'webba-booking-lite')
            ),
            'advanced'
		);

        wbk_opt()->add_option( 'wbk_disable_security', 'checkbox', __( 'Disable advanced security checks', 'webba-booking-lite' ), 'wbk_general_settings_section',
			array(
				'checkbox_value' => 'true',
                'default' => '',
				'popup' => __( 'IMPORTANT: turn on this option ONLY if you experience troubles with caching and can\'t setup caching exceptions for booking page.', 'webba-booking-lite')
            ),
            'advanced'
		);


 

		wbk_opt()->add_option( 'wbk_order_service_by', 'select', __( 'Order service by', 'webba-booking-lite' ), 'wbk_mode_settings_section',
			array(
                'popup' => __( 'Choose between alphabetical order (A - Z) or order by priority for displaying services on the booking form.', 'webba-booking-lite'),
				'default' => 'a-z',
				'extra' => array(
					'a-z' => __( 'A-Z', 'webba-booking-lite' ),
					'priority'  => __( 'Priority (descending)', 'webba-booking-lite' ),
					'priority_a'  => __( 'Priority (ascending)', 'webba-booking-lite' )
				)
            ) 
		);
		wbk_opt()->add_option( 'wbk_night_hours', 'text', __( 'Show night hours time slots in previous day', 'webba-booking-lite' ), 'wbk_mode_settings_section',
			array(
				'default' => '0',
				'popup' => __( 'Specify the number of hours after midnight to display on the next day\'s calendar.', 'webba-booking-lite' )
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_allow_cross_midnight', 'checkbox', __( 'Allow time slots to cross midnight', 'webba-booking-lite' ), 'wbk_appointments_settings_section',
			array(
				'checkbox_value' => 'true',
                'popup' => __( 'Turn on to allow time slots that extend beyond midnight.', 'webba-booking-lite' )
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_disallow_after', 'text', __( 'Block time slots after X hours from the current time', 'webba-booking-lite' ), 'wbk_appointments_settings_section',
			array(
				'default' => '0',
				'popup' => __( 'Set 0 to not disable time slots', 'webba-booking-lite' )
            ),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_gdrp', 'checkbox', __( 'EU GDPR Compliance', 'webba-booking-lite' ), 'wbk_appointments_settings_section',
                                array( 
                                    'popup' => __( 'Turn on to align the booking system with GDPR guidelines, providing enhanced data protection and privacy for customer information.', 'webba-booking-lite' )
                                ) );
		wbk_opt()->add_option( 'wbk_allow_ongoing_time_slot', 'checkbox', __( 'Disallow booking of the current time slot', 'webba-booking-lite' ), 'wbk_appointments_settings_section',
			array(
                'default' => 'disallow',
				'checkbox_value' => 'disallow',
                'popup' => __( 'Turn on to prevent customers from making bookings for the current time slot.', 'webba-booking-lite' )
      
			)
		);
	 
		wbk_opt()->add_option( 'wbk_mode_overlapping_availabiliy', 'checkbox', __( 'Consider the availability of overlapping time intervals', 'webba-booking-lite' ), 'wbk_appointments_settings_section',
			array(
                'default' => 'true',
				'checkbox_value' => 'true',
                'popup' => __( 'Turn on this option to control the availability of time slots for the same service when they overlap. When turned on, the system will automatically adjust the availability to avoid double booking.', 'webba-booking-lite' )
            ), 'advanced'

		);
		wbk_opt()->add_option( 'wbk_set_arrived_after', 'text', __( 'Set the status to "Arrived" X minutes after the end of the booking', 'webba-booking-lite' ), 'wbk_appointments_settings_section',
			array(
				'popup' => __( 'Specify the number of minutes after the end of the booking when the status should be automatically changed to "Arrived." Leave the field empty to keep the status unchanged.', 'webba-booking-lite' )
			),
            'advanced'
		);
 
 
		wbk_opt()->add_option( 'wbk_payment_cancel_message', 'text', __( 'PayPal payment cancelation message', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Payment canceled.', 'webba-booking-lite' ),
                'popup' => __('Message shown when payment with PayPal is canceled', 'webba-booking-lite' )
			),
            'advanced'
		);
 
        wbk_opt()->add_option( 'wbk_booking_cancel_email_label', 'text_alfa_numeric', __( 'Cancellation form label', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Please, enter your email to confirm cancelation', 'webba-booking-lite' ),
                'popup' => __( 'Text of the cancelation form label', 'webba-booking-lite' )
            ),
            'advanced'
		);

		wbk_opt()->add_option( 'wbk_cancel_button_text', 'text_alfa_numeric', __( 'Cancellation button text', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Cancel booking', 'webba-booking-lite' ),
                'popup' => __( 'Text of the cancelation button', 'webba-booking-lite' )
            ),
            'advanced'
		);
		 
		wbk_opt()->add_option( 'wbk_appointment_information', 'text', __( 'Booking details', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Booking on #dt', 'webba-booking-lite' ),
				'popup' => __( '"Message shown when customers pay for a booking or cancel their booking using the link sent in the email notification.
Available placeholders: #name (customer name), #id (appointment id), #service (service name), #date (appointment date), #time (appointment time), #dt (appointment date and time), #start_end (appointment time in start-end format)."', 'webba-booking-lite' ) 
						    
			),
            'advanced'
		);
    
		wbk_opt()->add_option( 'wbk_booking_cancel_error_message', 'text', __( 'Cancellation error message', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Unable to cancel booking, please check the email you\'ve entered.', 'webba-booking-lite' ),
                'popup' => __( 'Message shown when an error occurs on cancelation', 'webba-booking-lite')
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_booking_couldnt_be_canceled', 'text', __( 'Warning message on cancel booking (reason: paid booking)', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Paid booking can\'t be canceled.', 'webba-booking-lite' ),
				'popup' => __( 'Displayed when customer tries to cancel paid booking.', 'webba-booking-lite' )
            ),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_booking_couldnt_be_canceled2', 'text', __( 'Warning message on cancel booking (buffer)', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Sorry, you can not cancel because you have exceeded the time allowed to do so.', 'webba-booking-lite' ),
				'popup' => __( 'Message shown when a customer tries to cancel a booking within a time frame that does not allow cancellations. Buffer time is set in Booking rules -> Cancellation buffer (in minutes)', 'webba-booking-lite' )
            ),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_email_landing_text', 'text', __( 'Text of the payment link sent to a customer', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Click here to pay for your booking.', 'webba-booking-lite' ),
                'popup' => __('Text of the payment link sent to a customer in the email notification.', 'webba-booking-lite' )
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_email_landing_text_cancel', 'text', __( 'Text of the cancelation link sent to a customer', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Click here to cancel your booking.', 'webba-booking-lite' ),
                'popup' => __('Text of the booking cancelation link sent to a customer in the email notification.', 'webba-booking-lite' )
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_email_landing_text_cancel_admin', 'text', __( 'Text of the cancellation link sent to an admin', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Click here to cancel this booking.', 'webba-booking-lite' ),
                'popup' => __('Text of the booking cancelation link sent to the admin in the email notification.', 'webba-booking-lite' )
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_email_landing_text_approve_admin', 'text', __( 'Text of the approval link sent to an admin)', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Click here to approve this booking.', 'webba-booking-lite' ),
                'popup' => __('Text of the booking approval link sent to the admin in the email notification.', 'webba-booking-lite' )
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_email_landing_text_gg_event_add', 'text', __( 'Text of the link for adding event to customer\'s Google Calendar', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Click here to add this event to your Google Calendar.', 'webba-booking-lite' ),
                'popup' => __('Text of the link to add a booking to Google Calendar. Sent to a customer in the email notification.', 'webba-booking-lite' )
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_add_gg_button_text', 'text', __( 'Add to customer\'s Google Calendar button text', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Add to my Google Calendar', 'webba-booking-lite' ),
                'popup' => __('Text of the link included in the email notification', 'webba-booking-lite' )
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_gg_calendar_add_event_success', 'text', __( 'Google calendar event adding success message', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Booking data added to Google Calendar.', 'webba-booking-lite' ),
                'popup' => __( 'Message shown when booking is added to the Google Calendar.', 'webba-booking-lite' )
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_gg_calendar_add_event_canceled', 'text', __( 'Google calendar event adding error message', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Appointment data not added to Google Calendar.', 'webba-booking-lite' ),
                'popup' => __( 'Message shown when there was an issue with adding a booking to the Google Calendar.', 'webba-booking-lite' )
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_email_landing_text_invalid_token', 'text', __( 'Booking token error message', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Booking doesn\'t exist.', 'webba-booking-lite' ),
                'popup' => __( 'Message shown when booking link (cancelation, approval, payment) is invalid in the email notification.', 'webba-booking-lite')
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_gg_calendar_event_title', 'text', __( 'Google calendar event / iCal summary (for admin)', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => '#customer_name',
				'popup' => __( 'Available placeholders:', 'webba-booking-lite' ) . ' #customer_name, #customer_phone, #customer_email, #customer_comment, #items_count, #appointment_id, #customer_custom, #total_amount, #service_name, #status' . '<br />' .
						   __( 'Placeholder for custom field:', 'webba-booking-lite' ) . ' #field_ + custom field id. Example: #field_custom-field-1'
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_gg_calendar_event_description', 'text', __( 'Google calendar event / iCal description (for admin)', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => '#customer_name #customer_phone',
				'popup' => '<a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>' 
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_gg_calendar_event_title_customer', 'text', __( 'Google calendar event / iCal summary (for customer)', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => '#service_name',
                'popup' => '<a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>' 

            ),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_gg_calendar_event_description_customer', 'text', __( 'Google calendar event / iCal description (for customer)', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Your appointment id is #appointment_id', 'webba-booking-lite' ),
		        'popup' => '<a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>' 
			),
            'advanced'
		);
		// cotinue here
	 
		wbk_opt()->add_option( 'wbk_daily_limit_reached_message', 'text', __( 'Daily limit message', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'Daily booking limit is reached, please select another date.', 'webba-booking-lite' ),
                'popup' => __( 'Message shown when daily booking limit reached. Adjust the daily booking limits in the Settings -> Booking Rules.', 'webba-booking-lite' )
			),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_limit_by_email_reached_message', 'text', __( 'User limit message', 'webba-booking-lite' ), 'wbk_translation_settings_section',
			array(
				'default' => __( 'You have reached your booking limit.', 'webba-booking-lite' ),
                'popup' => __( 'Message shown when user limit is reached. Adjust the user booking limits in the Settings -> Booking Rules.', 'webba-booking-lite' )
			),
            'advanced'
		);
		 
 

		if ( wbk_fs()->is__premium_only() && wbk_fs()->can_use_premium_code() ) {
			wbk_opt()->add_option( 'wbk_paypal_mode', 'select', __( 'PayPal mode', 'webba-booking-lite' ), 'wbk_paypal_settings_section',
				array(
					'default' => 'live',
                    'popup' => __( 'Select "Sandbox" to test the integration, and "Live" for actual payment processing.', 'webba-booking-lite' ),
					'extra' => array(
						'sandbox' => __( 'Sandbox', 'webba-booking-lite' ),
						'live' => __( 'Live', 'webba-booking-lite' )
					)
				)
			);
			wbk_opt()->add_option( 'wbk_paypal_sandbox_clientid', 'text', __( 'PayPal Sandbox ClientID', 'webba-booking-lite' ), 'wbk_paypal_settings_section',
				array(
					'popup' => 'Enter the Client ID provided by PayPal for the Sandbox mode integration. <a href="https://www.paypal.com/us/cshelp/article/how-do-i-create-rest-api-credentials-ts1949" rel="noopener" target="_blank">Read more on how to set up PayPal integration.</a>'
			
                        )
			);
			wbk_opt()->add_option( 'wbk_paypal_sandbox_secret', 'pass', __( 'PayPal Sandbox Secret', 'webba-booking-lite' ), 'wbk_paypal_settings_section',
				array(
					'popup' => 'Enter the Client ID provided by PayPal for the Sandbox mode integration. <a href="https://www.paypal.com/us/cshelp/article/how-do-i-create-rest-api-credentials-ts1949" rel="noopener" target="_blank">Read more on how to set up PayPal integration.</a>'
			
                    )
			);
			wbk_opt()->add_option( 'wbk_paypal_live_clientid', 'text', __( 'PayPal Live ClientID', 'webba-booking-lite' ), 'wbk_paypal_settings_section',
				array(
						'popup' => 'Enter the Client ID provided by PayPal for the Sandbox mode integration. <a href="https://www.paypal.com/us/cshelp/article/how-do-i-create-rest-api-credentials-ts1949" rel="noopener" target="_blank">Read more on how to set up PayPal integration.</a>'
			
                    )
			);
			wbk_opt()->add_option( 'wbk_paypal_live_secret', 'pass', __( 'PayPal Live Secret', 'webba-booking-lite' ), 'wbk_paypal_settings_section',
				array(
						'popup' => 'Enter the Client ID provided by PayPal for the Sandbox mode integration. <a href="https://www.paypal.com/us/cshelp/article/how-do-i-create-rest-api-credentials-ts1949" rel="noopener" target="_blank">Read more on how to set up PayPal integration.</a>'
			
                    )
			);
			wbk_opt()->add_option( 'wbk_paypal_currency', 'select', __( 'PayPal currency', 'webba-booking-lite' ), 'wbk_paypal_settings_section',
				array(
                    'popup' => __('Select the currency to use for PayPal payments.', 'webba-booking-lite' ),
					'default' => 'USD',
					'extra' => array(
						'AUD' => __( 'Australian Dollar', 'webba-booking-lite' ),
						'BRL' => __( 'Brazilian Real', 'webba-booking-lite' ),
						'CAD' => __( 'Canadian Dollar', 'webba-booking-lite' ),
						'CZK' => __( 'Czech Koruna', 'webba-booking-lite' ),
						'DKK' => __( 'Danish Krone', 'webba-booking-lite' ),
						'EUR' => __( 'Euro', 'webba-booking-lite' ),
						'HKD' => __( 'Hong Kong Dollar', 'webba-booking-lite' ),
						'HUF' => __( 'Hungarian Forint', 'webba-booking-lite' ),
						'ILS' => __( 'Israeli New Sheqel', 'webba-booking-lite' ),
						'JPY' => __( 'Japanese Yen', 'webba-booking-lite' ),
						'MYR' => __( 'Malaysian Ringgit', 'webba-booking-lite' ),
						'MXN' => __( 'Mexican Peso', 'webba-booking-lite' ),
						'NOK' => __( 'Norwegian Krone', 'webba-booking-lite' ),
						'NZD' => __( 'New Zealand Dollar', 'webba-booking-lite' ),
						'PHP' => __( 'Philippine Peso', 'webba-booking-lite' ),
						'PLN' => __( 'Polish Zloty', 'webba-booking-lite' ),
						'GBP' => __( 'Pound Sterling', 'webba-booking-lite' ),
						'SGD' => __( 'Singapore Dollar', 'webba-booking-lite' ),
						'SEK' => __( 'Swedish Krona', 'webba-booking-lite' ),
						'CHF' => __( 'Swiss Franc', 'webba-booking-lite' ),
						'TWD' => __( 'Taiwan New Dollar', 'webba-booking-lite' ),
						'THB' => __( 'Thai Baht', 'webba-booking-lite' ),
						'USD' => __( 'U.S. Dollar', 'webba-booking-lite' )
					)
				)
			);
			 
			wbk_opt()->add_option( 'wbk_paypal_hide_address', 'checkbox', __( 'Hide address', 'webba-booking-lite' ), 'wbk_paypal_settings_section',
				array(
					'popup' => __( 'Turn on to hide address on PayPal checkout.', 'webba-booking-lite' ),
                    'checkbox_value' => 'enabled'
                ),
                'advanced'
			);
		 	wbk_opt()->add_option( 'wbk_paypal_redirect_url', 'this_domain_url', __( 'Redirect to page when payment is successful', 'webba-booking-lite' ), 'wbk_paypal_settings_section',
				array(
					'popup' => __( 'Enter the URL where customers should be redirected after a successful payment. If left empty, customers will stay on the booking form page after completing the payment.', 'webba-booking-lite' )
                ),
                'advanced'
			);
			wbk_opt()->add_option( 'wbk_paypal_multiplier', 'text', __( 'Currency multiplier', 'webba-booking-lite' ), 'wbk_paypal_settings_section',
				array(
					'popup' => __( 'Add the currency multiplier to update the price before it is sent to PayPal. It is helpful when your service price is set in a currency not supported by PayPal, and you need to convert it to a PayPal-supported currency before checkout. If you do not require currency conversion, leave this field empty.', 'webba-booking-lite' ) 
						 
				),
                'advanced'
			);
			wbk_opt()->add_option( 'wbk_zoom_link_text', 'text', __( 'Text of the Zoom meeting URL', 'webba-booking-lite' ), 'wbk_translation_settings_section',
				array(
					'default' => __( 'Click here to open your meeting in Zoom', 'webba-booking-lite' ),
                    'popup' => __( 'Text displayed as the link to the Zoom meeting.', 'webba-booking-lite' ) 
                ),
                'advanced'
			);
		}

		if ( wbk_fs()->is__premium_only() && wbk_fs()->can_use_premium_code() ) {
			wbk_opt()->add_option( 'wbk_stripe_publishable_key', 'text', __( 'Publishable key', 'webba-booking-lite' ), 'wbk_stripe_settings_section',
				array(
					'popup' => 'Enter the publishable API key provided by Stripe for your integration. <a href="https://stripe.com/docs/keys" rel="noopener" target="_blank">Read more on how to set up Stripe integration.</a>'
				)
			);
			wbk_opt()->add_option( 'wbk_stripe_secret_key', 'pass', __( 'Secret key', 'webba-booking-lite' ), 'wbk_stripe_settings_section',
				array(
					'popup' => 'Enter the publishable API key provided by Stripe for your integration. <a href="https://stripe.com/docs/keys" rel="noopener" target="_blank">Read more on how to set up Stripe integration.</a>'
				)
			);
			wbk_opt()->add_option( 'wbk_stripe_currency', 'select', __( 'Stripe currency', 'webba-booking-lite' ), 'wbk_stripe_settings_section',
				array(
					'default' => 'USD',
                    'popup' => __( 'Select the currency to use for Stripe payments.', 'webba-booking-lite'),
                    
					'extra' => array_combine( WBK_Stripe::getCurrencies(), WBK_Stripe::getCurrencies() )
				)
			);
			wbk_opt()->add_option( 'wbk_load_stripe_js', 'select', __( 'Load Stripe javascript', 'webba-booking-lite' ), 'wbk_stripe_settings_section',
				array(
					'default' => 'yes',
                    'popup' => __('Select how the Stripe Javascript needs to be loaded', 'webba-booking-lite'),
					'extra' => array(
						'yes' => __( 'Yes', 'webba-booking-lite' ),
						'no' => __( 'No', 'webba-booking-lite' ),
						'shortcode' => __( 'Only on the booking page (not recommended)', 'webba-booking-lite' )
                    )
				),
                'advanced'
			);
			wbk_opt()->add_option( 'wbk_load_stripe_api', 'select', __( 'Load Stripe API', 'webba-booking-lite' ), 'wbk_stripe_settings_section',
				array(
					'default' => 'yes',
					'extra' => array(
						'yes' => __( 'Yes, load version 7.26.0', 'webba-booking-lite' ),
						'old' => __( 'Yes, load version 6.21.1 (not recommended)', 'webba-booking-lite' ),
						'no' => __( 'No', 'webba-booking-lite' )
					),
					'popup' => __( 'Select how to load Stripe API. Set \'no\' or 6.21.1 only if there is a conflict with another plugin that uses Stripe.', 'webba-booking-lite' )
				),
                'advanced'
			);
			wbk_opt()->add_option( 'wbk_stripe_hide_postal', 'checkbox', __( 'Hide the postal code field', 'webba-booking-lite' ), 'wbk_stripe_settings_section',
				array(
					'checkbox_value' => 'true',
                    'popup' => __( 'Turn on to hide the postal code field in the Stripe checkout.', 'webba-booking-lite' )
				),
                'advanced'
			);
			wbk_opt()->add_option( 'wbk_stripe_card_input_mode', 'checkbox', __( 'Override Stripe card element error messages', 'webba-booking-lite' ), 'wbk_stripe_settings_section',
				array(
					'checkbox_value' => 'yes',
                    'popup' => __( 'Turn on to override the default error message displayed for Stripe card elements. To customize the error message, navigate to Wording/Translation -> Advanced Settings and modify the "Stripe card element error message" according to your preferences.', 'webba-booking-lite' )
				),
                'advanced'
			);
		 
			wbk_opt()->add_option( 'wbk_stripe_additional_fields', 'select_multiple', __( 'Additional payment information', 'webba-booking-lite' ), 'wbk_stripe_settings_section',
				array(
					'default' => '',
					'extra' => WBK_Db_Utils::getPaymentFields(),
					'popup' => __('Select the additional fields that you wish to include in the Stripe payment process.', 'webba-booking-lite')
				),
                'advanced'
			);
			wbk_opt()->add_option( 'wbk_stripe_redirect_url', 'text', __( 'Redirect to page when payment is successful', 'webba-booking-lite' ), 'wbk_stripe_settings_section',
				array(
					'popup' => __( 'Enter the URL where customers should be redirected after successful payment. If left empty, customers will stay on the booking form page after completing the payment.', 'webba-booking-lite' )
				),
                'advanced'
			);
			wbk_opt()->add_option( 'wbk_stripe_mob_font_size', 'text', __( 'Font size for card element on mobile devices', 'webba-booking-lite' ), 'wbk_stripe_settings_section',
				array(
					'popup' => __( 'Set the card element font size on mobile devices. Leave empty for the default input field font size.', 'webba-booking-lite' )
				),
                'advanced'
			);
			wbk_opt()->add_option( 'wbk_stripe_status_after_payment', 'select', __( 'Set status after booking is paid with Stripe to', 'webba-booking-lite' ), 'wbk_stripe_settings_section',
				array(
					'default' => 'based',
                    'popup' => __( 'Choose how to update the status after booking is paid with Stripe. To keep the current status unchanged, select "Based on status before payment". ', 'webba-booking-lite' ),
					'extra' => array(
						'based' => __( 'Based on status before payment', 'webba-booking-lite'),
						'paid' =>  __( 'Paid (awaiting approval)', 'webba-booking-lite' ),
						'paid_approved' => __( 'Paid (approved)', 'webba-booking-lite' )
					)
				),
                'advanced'
			);
		}

		if ( wbk_fs()->is__premium_only() && wbk_fs()->can_use_premium_code() ) {
			wbk_opt()->add_option( 'wbk_gg_clientid', 'text', __( 'Google API Client ID', 'webba-booking-lite' ),
                                   'wbk_gg_calendar_settings_section',
                                    array( 
                                        'popup' => __( 'Enter the Google API Client ID. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/google-calendar/">Read more on how to set up Google Calendar integration.</a>.', 'webba-booking-lite' ), 
                                        
                                    ) );

			wbk_opt()->add_option( 'wbk_gg_secret', 'pass', __( 'Google API Client Secret', 'webba-booking-lite' ), 'wbk_gg_calendar_settings_section',
                                    array( 
                                        'popup' => __( 'Enter the Google API Client Secret. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/google-calendar/">Read more on how to set up Google Calendar integration.</a>.', 'webba-booking-lite' ), 
                                    )
                                 );
 
			 
			wbk_opt()->add_option( 'wbk_gg_created_by', 'text', __( '"Created by" property for the events', 'webba-booking-lite' ), 'wbk_gg_calendar_settings_section',
				array(
					'default' => 'webba_booking',
					'popup' => __( 'Do not change this option if you do not plan to use the same Google calendars on different websites with Webba Booking.', 'webba-booking-lite' )
                ),
                'advanced'
			);
			wbk_opt()->add_option( 'wbk_gg_customers_time_zone', 'select', __( 'Customer\'s time zone', 'webba-booking-lite' ), 'wbk_gg_calendar_settings_section',
				array(
					'default' => 'webba',
                    'popup' => __('Choose the time zone to be used for events added to the customer\'s calendar.', 'webba-booking-lite'),
					'extra' => array(
						'webba' => __( 'Use Webba Booking time zone', 'webba-booking-lite' ),
						'customer' => __( 'Use customer\'s calendar time zone', 'webba-booking-lite' )
                    )
				),
                'advanced'
			);
			wbk_opt()->add_option( 'wbk_gg_when_add', 'select', __( 'Admin calendar event creation', 'webba-booking-lite' ), 'wbk_gg_calendar_settings_section',
				array(
					'default' => 'onbooking',
                    'popup' => __('Specify when the event should be added to the admin\'s calendar when creating bookings. ', 'webba-booking-lite'),
					'extra' => array(
						'onbooking' => __( 'On booking', 'webba-booking-lite' ),
						'onpaymentorapproval' => __( 'On payment or approval', 'webba-booking-lite' )
					)
                ),
                'advanced'
			);
			wbk_opt()->add_option( 'wbk_gg_2way_group', 'select', __( 'Group services synchronization', 'webba-booking-lite' ), 'wbk_gg_calendar_settings_section',
				array(
					'default' => 'lock',
                    'popup' => __( 'Choose how group services are integrated with the events in Google calendar.', 'webba-booking-lite'),
					'extra' => array(
						'lock' => __( 'Lock time slot', 'webba-booking-lite' ),
						'reduce' => __( 'Reduce count of available places', 'webba-booking-lite' )
					)
				),
                'advanced'
			);
			wbk_opt()->add_option( 'wbk_gg_ignore_free', 'checkbox', __( 'Ignore free events', 'webba-booking-lite' ), 'wbk_gg_calendar_settings_section',
				array(
					'checkbox_value' => 'yes',
                    'popup' => __( 'Turn on if free Google Calendar events should not be considered in 2-ways synchronization.', 'webba-booking-lite'),
				),
                'advanced'
			);
			wbk_opt()->add_option( 'wbk_ignore_webba_events', 'checkbox', __( 'Ignore events added by Webba Booking', 'webba-booking-lite' ), 'wbk_gg_calendar_settings_section',
				array(
					'checkbox_value' => 'yes',
                    'popup' => __( 'Turn on if Webba Booking events should not be considered in 2-ways synchronization.', 'webba-booking-lite'),
				),
                'advanced'
			);
			wbk_opt()->add_option( 'wbk_gg_group_service_export', 'select', __( 'Export for group services', 'webba-booking-lite' ), 'wbk_gg_calendar_settings_section',
				array(
					'default' => 'event_foreach_appointment',
                    'popup' => __( 'Select the method of exporting group services.', 'webba-booking-lite'),
					'extra' => array(
						'one_event' => __( 'Add one event', 'webba-booking-lite' ),
						'event_foreach_appointment' => __( 'Add event for each appointment', 'webba-booking-lite' )
					)
				),
                'advanced'
			);
			wbk_opt()->add_option( 'wbk_gg_send_alerts_to_admin', 'checkbox', __( 'Send an alert email to administrator if any issue occurred with the integration', 'webba-booking-lite' ), 'wbk_gg_calendar_settings_section',
				array(
					'checkbox_value' => 'yes',
                    'popup' => __('Turn on to alert admin about issues with integration. Notification is sent to the email set in the service settings.','webba-booking-lite')
                ),
                'advanced'
			);
			if (version_compare(PHP_VERSION, '7.4.0') >= 0) {
				$version_list = array( '2.9.1' => '2.9.1' );
			} else {
				$version_list = array( '2.5' => '2.5' );
			}
			if (version_compare(PHP_VERSION, '8.0.0') >= 0) {
				$version_list = array( '2.9.1' => '2.9.1', '2.13.0' => '2.13.0' );
			}
			wbk_opt()->add_option( 'wbk_gg_client_version', 'select', __( 'Version of Google Client API', 'webba-booking-lite' ), 'wbk_gg_calendar_settings_section',
				array(
					'default' => '2.9.1',
					'extra' => $version_list,
                    'popup' =>  __( 'Modify this setting only if you have other plugins in your WordPress that utilize a different version of the Google API and conflicts have arisen.', 'webba-booking-lite' )
                ),
                'advanced'
			);
		}

		if ( wbk_fs()->is__premium_only() && wbk_fs()->can_use_premium_code() ) {
			wbk_opt()->add_option( 'wbk_woo_product_id', 'text', __( 'WooCommerce product ID', 'webba-booking-lite' ), 'wbk_woo_settings_section',
				array(                    
					'popup' => __( 'Product ID. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/payment/steps-to-setup-the-integration-of-woocommerce-with-webba/">Read more</a>
 .', 'webba-booking-lite' )
				)
			);
		
            wbk_opt()->add_option( 'wbk_woo_check_coupons_inwebba', 'checkbox', __( 'Validate WooCommerce coupons as Webba Coupons', 'webba-booking-lite' ), 
                                   'wbk_woo_settings_section',
                                    array( 'popup' => __( 'Enable this option if you need to validate wooCommerce coupons as Webba coupons.', 'webba-booking-lite' ) ),
                                    'advanced' );

			wbk_opt()->add_option( 'wbk_woo_update_status', 'select', __( 'Status of the booking paid with WooCommerce', 'webba-booking-lite' ), 'wbk_woo_settings_section',
				array(
                    'popup' => __( 'Choose the desired status update after a booking has been paid through WooCommerce.', 'webba-booking-lite' ),
					'default' => 'disabled',
					'extra' => array(
						'disabled' => __( 'Disabled (do not update status)', 'webba-booking-lite' ),
						'approved' => __( 'Approved', 'webba-booking-lite' ),
						'paid' => __( 'Paid (awaiting approval)', 'webba-booking-lite' ),
						'paid_approved' => __( 'Paid (approved)', 'webba-booking-lite' )
					)
				)
			);
			wbk_opt()->add_option( 'wbk_woo_prefil_fields', 'checkbox', __( 'Prefill fields in WooCommerce checkout with the data used in the booking form', 'webba-booking-lite' ), 'wbk_woo_settings_section',
				array(  
                    'default' => 'true',
					'checkbox_value' => 'true',
                    'popup' => __( 'Turn on to prefill fields in the WooCommerce checkout with the data entered in the Webba booking form.', 'webba-booking-lite' )
                ),
                'advanced'
			);
		}

		if ( wbk_fs()->is__premium_only() && wbk_fs()->can_use_premium_code() ) {
             

			wbk_opt()->add_option( 'wbk_zoom_client_id', 'text', __( 'Client ID', 'webba-booking-lite' ), 
                                   'wbk_zoom_settings_section',
                                    array( 'popup' =>  'Enter the Zoom Client ID. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/integrations/integration-with-zoom/">Read more on how to set up Zoom integration.</a>')

                                    );
			wbk_opt()->add_option( 'wbk_zoom_client_secret', 'pass', __( 'Client secret', 'webba-booking-lite' ), 
                                   'wbk_zoom_settings_section',
                                    array( 'popup' =>  'Enter the Zoom Client secret. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/integrations/integration-with-zoom/">Read more on how to set up Zoom integration.</a>')

                                );
			wbk_opt()->add_option( 'wbk_zoom_auth_stat', 'zoom_auth', __( 'Authorization', 'webba-booking-lite' ), 'wbk_zoom_settings_section' );
			wbk_opt()->add_option( 'wbk_zoom_when_add', 'select', __( 'Zoom meeting creation', 'webba-booking-lite' ), 'wbk_zoom_settings_section',
				array(
                    'popup' => __('Select when to create the meeting in Zoom - on booking or on payment or booking approval.', 'webba-booking-lite'),
					'default' => 'onbooking',
					'extra' => array(
						'onbooking' => __( 'On booking', 'webba-booking-lite' ), 
						'onpaymentorapproval' => __( 'On payment or approval', 'webba-booking-lite' )
                    )
                    ),
                    'advanced'
			);
		}

		wbk_opt()->add_option( 'wbk_customer_name_output', 'text', __( 'Customer name in the backend', 'webba-booking-lite' ), 'wbk_interface_settings_section',
			array(
				'default' => '#name',
				'popup' => __( 'Use this option to display custom fields alongside the customer name in the appointments table and schedules. For instance, you can show the customer\'s name and last name by using the placeholder #name #field_lastname. In this example, the last name is stored in a custom field with the ID "lastname". Remember to include the #name placeholder in the value of this option for it to work correctly.', 'webba-booking-lite' ) 
						 
            ),
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_date_format_backend', 'select', __( 'Date format (backend)', 'webba-booking-lite' ), 'wbk_interface_settings_section',
			array(
				'default' => 'M d, Y',
				'extra' => array(
					'm/d/y' => __( 'm/d/y', 'webba-booking-lite' ),
					'y/m/d' => __( 'y/m/d', 'webba-booking-lite' ),
					'y-m-d' => __( 'y-m-d', 'webba-booking-lite' ),					 
					'M d, Y' => __( 'M d, Y', 'webba-booking-lite' ),
                    'd/m/y' => __( 'd/m/y', 'webba-booking-lite' )
				),
				'popup' => __( 'Select how the date will be displayed on the Appointments page in the admin area.', 'webba-booking-lite' )
			)
		);
		 
		wbk_opt()->add_option( 'wbk_date_format_time_slot_schedule', 'select', __( 'Time slots format on the schedule page', 'webba-booking-lite' ), 'wbk_interface_settings_section',
			array(
                'popup' => __('Select how to display time slots on the Schedule page.', 'webba-booking-lite'), 
				'default' => 'start',
				'extra' => array(
					'start' => __( 'Start', 'webba-booking-lite' ),
					'start-end' => __( 'Start - End', 'webba-booking-lite' )
				)
                ),
                'advanced'
		);
		wbk_opt()->add_option( 'wbk_custom_fields_columns', 'text', __( 'Custom field columns', 'webba-booking-lite' ), 'wbk_interface_settings_section',
			array(
				'popup' => __( 'Enter a comma-separated list of custom field IDs. To set custom column headers, use square brackets with the desired titles. For example: custom-field1[Title 1],custom-field2[Title 2].', 'webba-booking-lite' ) 
						   
            ), 
            'advanced'
		);
		wbk_opt()->add_option( 'wbk_filter_default_days_number', 'text', __( 'Default number of days on Bookings page', 'webba-booking-lite' ), 'wbk_interface_settings_section',
			array(
				'default' => '30',
				'popup' => __( 'Set the default number of days to be displayed on the appointment page. To improve performance, consider using a lower value.', 'webba-booking-lite' )
			)
		);
		  
		if ( wbk_fs()->is__premium_only() && wbk_fs()->can_use_premium_code() ) {
			wbk_opt()->add_option( 'wbk_twilio_account_sid', 'text', __( 'Twilio ACCOUNT SID', 'webba-booking-lite' ), 
                                   'wbk_sms_settings_section',
                                    array( 'popup' =>  'Enter the Twilio ACCOUNT SID. <a rel="noopener" target="_blank" href="https://support.twilio.com/hc/en-us/articles/14726256820123-What-is-a-Twilio-Account-SID-and-where-can-I-find-it-">Read more.</a>')
                                  );

			wbk_opt()->add_option( 'wbk_twilio_auth_token', 'pass', __( 'Twilio AUTH TOKEN', 'webba-booking-lite' ), 'wbk_sms_settings_section',
                                    array( 'popup' =>  'Enter the Twilio AUTH TOKEN. <a rel="noopener" target="_blank" href="https://support.twilio.com/hc/en-us/articles/223136027-Auth-Tokens-and-How-to-Change-Them">Read more.</a>')
                                );

            
			wbk_opt()->add_option( 'wbk_twilio_phone_number', 'text', __( 'Twilio phone number or Messaging Service SID', 'webba-booking-lite' ), 'wbk_sms_settings_section',
				array(
					'popup' => __( 'The phone number must start with a + sign.', 'webba-booking-lite' )
				)
			);
			wbk_opt()->add_option( 'wbk_sms_send_on_booking', 'checkbox', __( 'Send SMS after customer makes a booking', 'webba-booking-lite' ), 'wbk_sms_settings_section',
				array(
					'checkbox_value' => 'true',
                    'popup' => __( 'Turn on to send booking SMS notifications to customers when they make a booking.', 'webba-booking-lite' )
				)
			);
			wbk_opt()->add_option( 'wbk_sms_send_on_manual_booking', 'checkbox', __( 'Send SMS after admin adds a booking', 'webba-booking-lite' ), 'wbk_sms_settings_section',
				array(
					'checkbox_value' => 'true',
                    'dependency' => array( 'wbk_sms_send_on_booking' => ':checked' ),
                    'popup' => __( 'Turn on to send booking SMS notifications to customers when the booking was done by an admin.', 'webba-booking-lite' )
				)
			);
			wbk_opt()->add_option( 'wbk_sms_message_on_booking', 'textarea', __( 'Booking SMS:', 'webba-booking-lite' ), 'wbk_sms_settings_section',
				array(
                    'dependency' => array( 'wbk_sms_send_on_booking' => ':checked' ),
					'default' => __( 'Dear #customer_name, You have successfully booked #service_name on #appointment_day at #appointment_time.', 'webba-booking-lite' ),
					'popup' => 'Customize the booking SMS message. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>'
				)
			);
			wbk_opt()->add_option( 'wbk_sms_send_reminder', 'checkbox', __( 'Send booking reminder SMS', 'webba-booking-lite' ), 'wbk_sms_settings_section',
				array(
					'checkbox_value' => 'true',
                    'popup' => __('Turn on to send booking reminder SMS notifications to customers.', 'webba-booking-lite' )
                ),
                'advanced'
			);
			wbk_opt()->add_option( 'wbk_sms_reminder_days', 'text', __( 'Send reminder to customer X days before booking', 'webba-booking-lite' ), 'wbk_sms_settings_section',
				array(
                    'dependency' => array( 'wbk_sms_send_reminder' => ':checked' ),
					'default' => '1',
					'popup' => __( 'Select the timing for the booking reminder SMS. For instance, set the value to 0 for the day of booking, 1 for one day before the booking, 2 for two days before, and so on.', 'webba-booking-lite' )
                ),
                'advanced'
			);
			wbk_opt()->add_option( 'wbk_sms_message_reminder', 'textarea', __( 'Reminder message', 'webba-booking-lite' ), 'wbk_sms_settings_section',
				array(
                    'dependency' => array( 'wbk_sms_send_reminder' => ':checked' ),
					'default' => __( 'Dea #customer_name, we would like to remind that you have booked the #service_name tomorrow at #appointment_time.', 'webba-booking-lite' ),
					'popup' => 'Customize the booking reminder SMS message. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>'
                ),
                'advanced'
			);
			wbk_opt()->add_option( 'wbk_sms_send_on_approval', 'checkbox', __( 'Send booking approval SMS', 'webba-booking-lite' ), 'wbk_sms_settings_section',
				array(
					'checkbox_value' => 'true',
                    'popup' => __('Turn on to send booking approval SMS notifications to customers once their booking has been approved.', 'webba-booking-lite' )
				)
			);

			wbk_opt()->add_option( 'wbk_sms_message_on_approval', 'textarea', __( 'Booking approval SMS:', 'webba-booking-lite' ), 'wbk_sms_settings_section',
				array(
                    'dependency' => array( 'wbk_sms_send_on_approval' => ':checked' ),
					'default' => __( 'Dear #customer_name, your booking on #appointment_day at #appointment_time has been approved.', 'webba-booking-lite' ),
					'popup' => 'Customize the booking approval SMS message. <a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'webba-booking-lite' ). '</a>'
				)
			);
		}
        if( get_option( 'wbk_price_separator' ) === false ){
            wbk_opt()->reset_defaults();
        }

		do_action('wbk_options_after');
	}

	public function wbk_default_editor(){
		return 'tinymce';
	}

	// init styles and scripts
	public function enqueueScripts() {
 		if ( $this->is_option_page() ) {
    	}
	}

    // general settings section callback
	public function wbk_general_settings_section_callback( $arg ) {
	}
    // schedule settings section callback
	public function wbk_schedule_settings_section_callback( $arg ) {
	}
    // email settings section callback
	public function wbk_email_settings_section_callback( $arg ) {
	}
    // appearance  settings section callback
	public function wbk_mode_settings_section_callback( $arg ) {
	}
	// appearance  settings section callback
	public function wbk_translation_settings_section_callback( $arg ) {
	}
	// backend interface settings section callback
	public function wbk_backend_interface_settings_section_callback( $arg ) {
	}
	// paypal settings section callback
	public function wbk_paypal_settings_section_callback( $arg ) {
	}
	// stripe settings section callback
	public function wbk_stripe_settings_section_callback( $arg ) {
	}
	// google calendar settings section callback
	public function wbk_gg_calendar_settings_section_callback( $arg ) {
	}
	// sms settings section callback
	public function wbk_sms_settings_section_callback( $arg ) {
	}
	// woo settings section callback
	public function wbk_woo_settings_section_callback( $arg ) {
	}
	// zoom setting section callback
	public function wbk_zoom_settings_section_callback( $arg ) {
	}
	// appointments settings section callback
	public function wbk_appointments_settings_section_callback( $arg ){
	}

	public function is_option_page() {
		return isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'wbk-options';
	}
}
?>
