<?php

// Webba Booking options page class
// check if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
require_once dirname( __FILE__ ) . '/../../common/class_wbk_date_time_utils.php';
require_once dirname( __FILE__ ) . '/../../common/class_wbk_business_hours.php';
class WBK_Backend_Options extends WBK_Backend_Component
{
    public function __construct()
    {
        //set component-specific properties
        $this->name = 'wbk-options';
        $this->title = 'Settings';
        $this->main_template = 'tpl_wbk_backend_options.php';
        $this->capability = 'manage_options';
        // init settings
        add_action( 'admin_init', array( $this, 'initSettings' ) );
        // init scripts
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueueScripts' ), 20 );
        // mce plugin
        add_filter( 'mce_buttons', array( $this, 'wbk_mce_add_button' ) );
        add_filter( 'mce_external_plugins', array( $this, 'wbk_mce_add_javascript' ) );
        add_filter( 'wp_default_editor', 'wbk_default_editor' );
        add_filter( 'tiny_mce_before_init', array( $this, 'customizeEditor' ), 1000 );
    }
    
    public function customizeEditor( $in )
    {
        
        if ( isset( $_GET['page'] ) && $_GET['page'] == 'wbk-options' ) {
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
    
    public function wbk_mce_add_button( $buttons )
    {
        
        if ( isset( $_GET['page'] ) && $_GET['page'] == 'wbk-options' ) {
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
    
    public function wbk_mce_add_javascript( $plugin_array )
    {
        if ( isset( $_GET['page'] ) && $_GET['page'] == 'wbk-options' ) {
            if ( !isset( $plugin_array['wbk_tinynce'] ) ) {
                $plugin_array['wbk_tinynce'] = plugins_url( 'js/wbk-tinymce.js', dirname( __FILE__ ) );
            }
        }
        return $plugin_array;
    }
    
    // init wp settings api objects for options page
    public function initSettings()
    {
        // General settings section
        add_settings_section(
            'wbk_general_settings_section',
            __( 'General', 'wbk' ),
            array( $this, 'wbk_general_settings_section_callback' ),
            'wbk-options'
        );
        // Booking rules (ex appointments) section
        add_settings_section(
            'wbk_appointments_settings_section',
            __( 'Booking rules', 'wbk' ),
            array( $this, 'wbk_appointments_settings_section_callback' ),
            'wbk-options'
        );
        // User interface (ex. mode) section
        add_settings_section(
            'wbk_mode_settings_section',
            __( 'User interface', 'wbk' ),
            array( $this, 'wbk_mode_settings_section_callback' ),
            'wbk-options'
        );
        // Email notifications section
        add_settings_section(
            'wbk_email_settings_section',
            __( 'Email notifications', 'wbk' ),
            array( $this, 'wbk_email_settings_section_callback' ),
            'wbk-options'
        );
        // translation settings section
        add_settings_section(
            'wbk_translation_settings_section',
            __( 'Wording / Translation', 'wbk' ),
            array( $this, 'wbk_translation_settings_section_callback' ),
            'wbk-options'
        );
        add_settings_section(
            'wbk_interface_settings_section',
            __( 'Backend interface', 'wbk' ),
            array( $this, 'wbk_backend_interface_settings_section_callback' ),
            'wbk-options'
        );
        wbk_opt()->add_option(
            'wbk_start_of_week',
            'select',
            __( 'Week starts on', 'wbk' ),
            '',
            'wbk_general_settings_section',
            'monday',
            array(
            'sunday'    => __( 'Sunday', 'wbk' ),
            'monday'    => __( 'Monday', 'wbk' ),
            'wordpress' => __( 'Wordpress default', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_date_format',
            'text',
            __( 'Date format', 'wbk' ),
            __( 'Set <a href="https://wordpress.org/support/article/formatting-date-and-time/"   rel="noopener" target="_blank" >format</a> or leave empty to use Wordpress Date Format. ', 'wbk' ),
            'wbk_general_settings_section',
            ''
        );
        wbk_opt()->add_option(
            'wbk_time_format',
            'text',
            __( 'Time format', 'wbk' ),
            __( 'Set <a href="https://wordpress.org/support/article/formatting-date-and-time/"   rel="noopener" target="_blank" >format</a> or leave empty to use Wordpress Time Format. ', 'wbk' ),
            'wbk_general_settings_section',
            ''
        );
        $arr_timezones = array_combine( timezone_identifiers_list(), timezone_identifiers_list() );
        wbk_opt()->add_option(
            'wbk_timezone',
            'select',
            __( 'Timezone', 'wbk' ),
            '',
            'wbk_general_settings_section',
            '',
            $arr_timezones
        );
        wbk_opt()->add_option(
            'wbk_mode',
            'select',
            __( 'Mode', 'wbk' ),
            '',
            'wbk_mode_settings_section',
            'extended',
            array(
            'extended' => __( 'Extended', 'wbk' ),
            'simple'   => __( 'Basic', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_show_suitable_hours',
            'select',
            __( 'Show suitable hours', 'wbk' ),
            '',
            'wbk_mode_settings_section',
            'yes',
            array(
            'yes' => __( 'Yes', 'wbk' ),
            'no'  => __( 'No', 'wbk' ),
        ),
            'wbk-options',
            'wbk_options',
            array(
            'wbk_mode' => 'extended',
        )
        );
        wbk_opt()->add_option(
            'wbk_multi_booking',
            'select',
            __( 'Multiple bookings in one session', 'wbk' ),
            '',
            'wbk_mode_settings_section',
            'disabled',
            array(
            'disabled'     => __( 'Disabled', 'wbk' ),
            'enabled'      => __( 'Enabled (top bar checkout button)', 'wbk' ),
            'enabled_slot' => __( 'Enabled (time slot checkout button)', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_phone_mask',
            'select',
            __( 'Phone number masked input', 'wbk' ),
            '',
            'wbk_mode_settings_section',
            'monday',
            array(
            'enabled'             => __( 'jQuery Masked Input Plugin', 'wbk' ),
            'enabled_mask_plugin' => __( 'jQuery Mask Plugin', 'wbk' ),
            'disabled'            => __( 'Disabled', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_phone_format',
            'text',
            __( 'Phone format', 'wbk' ),
            __( 'jQuery Masked Input Plugin format example: (999) 999-9999. "9" represents numeric symbol. ', 'wbk' ) . '<br />' . __( 'jQuery Mask Plugin format example: (000) 000-0000. "0" represents numeric symbol. ', 'wbk' ) . '<a href="https://igorescobar.github.io/jQuery-Mask-Plugin/" rel="noopener" target="_blank">' . __( 'More information', 'wbk' ) . '</a>',
            'wbk_mode_settings_section',
            ''
        );
        $value = sanitize_text_field( get_option( 'wbk_phone_required', '3' ) );
        wbk_opt()->add_option(
            'wbk_phone_required',
            'select',
            __( 'Phone field is mandatory', 'wbk' ),
            '',
            'wbk_mode_settings_section',
            '',
            array(
            '3' => __( 'Yes', 'wbk' ),
            '0' => __( 'No', 'wbk' ),
        )
        );
        // booked slots
        wbk_opt()->add_option(
            'wbk_show_booked_slots',
            'select',
            __( 'Show booked timeslots', 'wbk' ),
            '',
            'wbk_mode_settings_section',
            'disabled',
            array(
            'enabled'  => __( 'Enabled', 'wbk' ),
            'disabled' => __( 'Disabled', 'wbk' ),
        )
        );
        // auto lock slots
        wbk_opt()->add_option(
            'wbk_appointments_auto_lock',
            'select',
            __( 'Autolock bookings', 'wbk' ),
            __( 'Enable this option for auto lock time slots of different services on booking (connection between services).', 'wbk' ),
            'wbk_appointments_settings_section',
            'disabled',
            array(
            'enabled'  => __( 'Enabled', 'wbk' ),
            'disabled' => __( 'Disabled', 'wbk' ),
        )
        );
        // auto lock mode
        wbk_opt()->add_option(
            'wbk_appointments_auto_lock_mode',
            'select',
            __( 'Perform autolock on', 'wbk' ),
            '',
            'wbk_appointments_settings_section',
            'all',
            array(
            'all'        => __( 'All services', 'wbk' ),
            'categories' => __( 'Services in the same categories', 'wbk' ),
        ),
            'wbk-options',
            'wbk_options',
            array(
            'wbk_appointments_auto_lock' => 'enabled',
        )
        );
        // auto lock group
        wbk_opt()->add_option(
            'wbk_appointments_auto_lock_group',
            'select',
            __( 'Autolock for group booking services', 'wbk' ),
            '',
            'wbk_appointments_settings_section',
            'lock',
            array(
            'lock'   => __( 'Lock time slot', 'wbk' ),
            'reduce' => __( 'Reduce count of available places', 'wbk' ),
        ),
            'wbk-options',
            'wbk_options',
            array(
            'wbk_appointments_auto_lock' => 'enabled',
        )
        );
        wbk_opt()->add_option(
            'wbk_appointments_auto_lock_allow_unlock',
            'select',
            __( 'Allow unlock manually', 'wbk' ),
            '',
            'wbk_appointments_settings_section',
            'allow',
            array(
            'allow'    => __( 'Allow', 'wbk' ),
            'disallow' => __( 'Disallow', 'wbk' ),
        ),
            'wbk-options',
            'wbk_options',
            array(
            'wbk_appointments_auto_lock' => 'enabled',
        )
        );
        wbk_opt()->add_option(
            'wbk_appointments_default_status',
            'select',
            __( 'Default booking status', 'wbk' ),
            '',
            'wbk_appointments_settings_section',
            'approved',
            array(
            'approved' => __( 'Approved', 'wbk' ),
            'pending'  => __( 'Awaiting approval', 'wbk' ),
        )
        );
        // appointment allow payments for
        wbk_opt()->add_option(
            'wbk_appointments_allow_payments',
            'select',
            __( 'Allow payments only for approved bookings', 'wbk' ),
            __( 'Enable this option if you want to allow online payments for the approved appointments <b>ONLY</b>.', 'wbk' ),
            'wbk_appointments_settings_section',
            'disabled',
            array(
            'enabled'  => __( 'Enabled', 'wbk' ),
            'disabled' => __( 'Disabled', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_allow_coupons',
            'select',
            __( 'Coupons', 'wbk' ),
            '',
            'wbk_appointments_settings_section',
            'disabled',
            array(
            'enabled'  => __( 'Enabled', 'wbk' ),
            'disabled' => __( 'Disabled', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_appointments_delete_not_paid_mode',
            'select',
            __( 'Delete not paid bookings', 'wbk' ),
            __( 'Enable this option to delete expired (not paid) appointments', 'wbk' ) . '<br />' . __( '*Expiration feature affect only on booking made at the front-end', 'wbk' ) . '<br />' . __( '*Expiration feature will not affect on bookings in the process of payment, except if a customer canceled payment at PayPal side', 'wbk' ),
            'wbk_appointments_settings_section',
            'disabled',
            array(
            'disabled'   => __( 'Disabled', 'wbk' ),
            'on_booking' => __( 'Set expiration time on booking', 'wbk' ),
            'on_approve' => __( 'Set expiration time on approve', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_appointments_delete_payment_started',
            'select',
            __( 'Delete not paid bookings with started but not finished transaction', 'wbk' ),
            __( 'IMPORTANT: if you choose "Delete appointments with started transaction", expired appointments will be deleted even if a customer started (and not finished) the payment (initialized transaction).', 'wbk' ),
            'wbk_appointments_settings_section',
            'skip',
            array(
            'skip'   => __( 'Do not delete appointments with started transaction', 'wbk' ),
            'delete' => __( 'Delete appointments with started transaction', 'wbk' ),
        ),
            'wbk-options',
            'wbk_options',
            array(
            'wbk_appointments_delete_not_paid_mode' => 'on_booking|on_approve',
        )
        );
        // appointment expiration
        wbk_opt()->add_option(
            'wbk_appointments_expiration_time',
            'text',
            __( 'Time to pay', 'wbk' ),
            __( 'Expiration time in minutes.', 'wbk' ),
            'wbk_appointments_settings_section',
            '60',
            null,
            'wbk-options',
            'wbk_options',
            array(
            'wbk_appointments_delete_not_paid_mode' => 'on_booking|on_approve',
        )
        );
        wbk_opt()->add_option(
            'wbk_cancellation_buffer',
            'text',
            __( 'Cancellation buffer (minutes)', 'wbk' ),
            __( 'Buffer time: minimum time to allow a cancellation before the appointment / reservation.', 'wbk' ),
            'wbk_appointments_settings_section',
            ''
        );
        wbk_opt()->add_option(
            'wbk_appointments_allow_cancel_paid',
            'select',
            __( 'Allow cancellation of paid bookings', 'wbk' ),
            __( 'Enable this option if you want to allow CUSTOMERS to cancel paid appointments.', 'wbk' ),
            'wbk_appointments_settings_section',
            'disallow',
            array(
            'allow'    => __( 'Allow', 'wbk' ),
            'disallow' => __( 'Disallow', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_appointments_only_one_per_slot',
            'select',
            __( 'Allow only one booking per slot from an email', 'wbk' ),
            __( 'Enable this option to allow only one appointment per time slot from one email.', 'wbk' ),
            'wbk_appointments_settings_section',
            'disabled',
            array(
            'enabled'  => __( 'Enabled', 'wbk' ),
            'disabled' => __( 'Disabled', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_appointments_only_one_per_day',
            'select',
            __( 'Allow only one booking per day from an email', 'wbk' ),
            __( 'Enable this option to allow only one appointment per day from one email.', 'wbk' ),
            'wbk_appointments_settings_section',
            'disabled',
            array(
            'enabled'  => __( 'Enabled', 'wbk' ),
            'disabled' => __( 'Disabled', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_hide_from_on_booking',
            'select',
            __( 'Hide the form after booking', 'wbk' ),
            __( 'Enable this option to hide all sections of the booking form when booking is done.', 'wbk' ),
            'wbk_mode_settings_section',
            'disabled',
            array(
            'enabled'  => __( 'Enabled', 'wbk' ),
            'disabled' => __( 'Disabled', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_appointments_only_one_per_service',
            'select',
            __( 'Allow only one booking per service from an email', 'wbk' ),
            __( 'Enable this option to allow only one appointment per service from one email.', 'wbk' ),
            'wbk_appointments_settings_section',
            'disabled',
            array(
            'enabled'  => __( 'Enabled', 'wbk' ),
            'disabled' => __( 'Disabled', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_appointments_expiration_time_pending',
            'text',
            __( 'Delete pending bookings', 'wbk' ),
            __( 'Automatically delete bookings with the "Awaiting approval" status after X minutes.', 'wbk' ) . '<br>' . __( 'Set 0 to not delete automatically', 'wbk' ),
            'wbk_appointments_settings_section',
            '0'
        );
        wbk_opt()->add_option(
            'wbk_appointments_autolock_avail_limit',
            'text',
            __( 'Maximum number of bookings at a specific time', 'wbk' ),
            __( 'Maximum number of bookings at given time for the entire system (all services)', 'wbk' ),
            'wbk_appointments_settings_section',
            ''
        );
        wbk_opt()->add_option(
            'wbk_appointments_limit_by_day',
            'text',
            __( 'Maximum number of bookings on a specific day', 'wbk' ),
            __( 'Maximum number of bookings of all services at one day', 'wbk' ) . '<br />' . __( 'Leave empty to not set limit', 'wbk' ),
            'wbk_appointments_settings_section',
            ''
        );
        wbk_opt()->add_option(
            'wbk_appointments_continuous',
            'select_multiple',
            __( 'Continuous bookings', 'wbk' ),
            __( 'Select the services for which this rule is applied', 'wbk' ),
            'wbk_mode_settings_section',
            '',
            WBK_Model_Utils::get_services()
        );
        wbk_opt()->add_option(
            'wbk_appointments_lock_timeslot_if_parital_booked',
            'select_multiple',
            __( 'Lock timeslot if at least one place is booked', 'wbk' ),
            __( 'Select the services for which this rule is applied', 'wbk' ) . '<br>' . __( 'Note: this option is used by front-end booking', 'wbk' ),
            'wbk_appointments_settings_section',
            '',
            WBK_Model_Utils::get_services()
        );
        wbk_opt()->add_option(
            'wbk_appointments_lock_day_if_timeslot_booked',
            'select_multiple',
            __( 'Lock whole day if at least one timeslot is booked', 'wbk' ),
            __( 'Select the services for which this rule is applied', 'wbk' ) . '<br>' . __( 'Note: if autolock is enabled, appointments of the connected services are taken into amount.', 'wbk' ),
            'wbk_appointments_settings_section',
            '',
            WBK_Model_Utils::get_services()
        );
        wbk_opt()->add_option(
            'wbk_appointments_lock_one_before_and_one_after',
            'select_multiple',
            __( 'Lock one timeslot before and after booking', 'wbk' ),
            __( 'Select the services for which this rule is applied', 'wbk' ) . '<br>' . __( 'Note: if autolock is enabled, bookings of the connected services are taken into amount.', 'wbk' ),
            'wbk_appointments_settings_section',
            '',
            WBK_Model_Utils::get_services()
        );
        wbk_opt()->add_option(
            'wbk_appointments_special_hours',
            'textarea',
            __( 'Special business hours', 'wbk' ),
            __( 'Set this option to override the business hours of certain services on specific dates.', 'wbk' ) . '<br />' . __( 'Example 1:', 'wbk' ) . '<br />' . __( '1 01/15/2020 15:00-18:00', 'wbk' ) . '<br />' . __( 'Service with the id equals 1 is available on 01/15/2021 at 15:00-18:00', 'wbk' ) . '<br />' . __( 'Example 2:', 'wbk' ) . '<br />' . __( '01/15/2020 15:00-18:00', 'wbk' ) . '<br />' . __( 'All services are available on 01/15/2021 at 15:00-18:00', 'wbk' ),
            'wbk_appointments_settings_section',
            ''
        );
        // shortcode checking
        wbk_opt()->add_option(
            'wbk_check_short_code',
            'select',
            __( 'Load CSS & JS only on the booking page', 'wbk' ),
            'Enable this option to check if the page has shortcode before booking form initialized.',
            'wbk_general_settings_section',
            'disabled',
            array(
            'enabled'  => __( 'Enabled', 'wbk' ),
            'disabled' => __( 'Disabled', 'wbk' ),
        )
        );
        // show cancel button
        wbk_opt()->add_option(
            'wbk_show_cancel_button',
            'select',
            __( 'Show cancel button', 'wbk' ),
            'Enable this option to show cancel button on the steps of the booking process.',
            'wbk_mode_settings_section',
            'disabled',
            array(
            'enabled'  => __( 'Enabled', 'wbk' ),
            'disabled' => __( 'Disabled', 'wbk' ),
        )
        );
        // disable day on all booked
        wbk_opt()->add_option(
            'wbk_disable_day_on_all_booked',
            'select',
            __( 'Disable booked dates in calendar', 'wbk' ),
            'Disable date in the calendar if no free time slots found.',
            'wbk_mode_settings_section',
            'disabled',
            array(
            'enabled'  => __( 'Yes', 'wbk' ),
            'disabled' => __( 'No', 'wbk' ),
        )
        );
        // holydays
        wbk_opt()->add_option(
            'wbk_holydays',
            'text',
            __( 'Holidays', 'wbk' ),
            __( 'Please set this option as a comma-separated list of dates (no spaces). Use the same date format as set in the Backend interface tab.' . '<br />' . 'This option should be used to set only holidays. Do not use it to set weekends (there is a Business hours parameter of services for this purpose)', 'wbk' ),
            'wbk_general_settings_section',
            ''
        );
        wbk_opt()->add_option(
            'wbk_recurring_holidays',
            'checkbox',
            __( 'Recurring holidays', 'wbk' ),
            __( 'Check if you\'d like to make holidays recurring', 'wbk' ),
            'wbk_general_settings_section',
            ''
        );
        wbk_opt()->add_option(
            'wbk_email_customer_book_multiple_mode',
            'select',
            __( 'Multiple booking notification mode (customer)', 'wbk' ),
            '',
            'wbk_email_settings_section',
            'foreach',
            array(
            'foreach' => __( 'Send Email for each booked time slot', 'wbk' ),
            'one'     => __( 'Send one Email for all booked time slots', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_email_admin_book_multiple_mode',
            'select',
            __( 'Multiple booking notification mode (admin)', 'wbk' ),
            __( 'IMPORTANT NOTICE: using "Send one Email for all booked time slots" with multi-service booking mode is recommended only if all services has the same e-mail.', 'wbk' ),
            'wbk_email_settings_section',
            'foreach',
            array(
            'foreach' => __( 'Send Email for each booked time slot', 'wbk' ),
            'one'     => __( 'Send one Email for all booked time slots', 'wbk' ),
        )
        );
        add_settings_field(
            'wbk_email_customer_book_status',
            __( 'Send customer an email (on booking)', 'wbk' ),
            array( $this, 'render_email_customer_book_status' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_book_status', array( $this, 'validate_email_customer_book_status' ) );
        add_settings_field(
            'wbk_email_customer_book_subject',
            __( 'Subject of an email to a customer (on booking)', 'wbk' ),
            array( $this, 'render_email_customer_book_subject' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_book_subject', array( $this, 'validate_email_customer_book_subject' ) );
        add_settings_field(
            'wbk_email_customer_book_message',
            __( 'Message to a customer (on booking)', 'wbk' ),
            array( $this, 'render_email_customer_book_message' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_book_message', array( $this, 'validate_email_customer_book_message' ) );
        add_settings_field(
            'wbk_email_customer_manual_book_subject',
            __( 'Subject of an email to a customer (on manual booking)', 'wbk' ),
            array( $this, 'render_email_customer_manual_book_subject' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_manual_book_subject', array( $this, 'validate_email_customer_manual_book_subject' ) );
        add_settings_field(
            'wbk_email_customer_manual_book_message',
            __( 'Message to a customer (on manual booking)', 'wbk' ),
            array( $this, 'render_email_customer_manual_book_message' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_manual_book_message', array( $this, 'validate_email_customer_manual_book_message' ) );
        add_settings_field(
            'wbk_email_customer_approve_status',
            __( 'Send customer an email (on approval)', 'wbk' ),
            array( $this, 'render_email_customer_approve_status' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_approve_status', array( $this, 'validate_email_customer_approve_status' ) );
        add_settings_field(
            'wbk_email_customer_approve_subject',
            __( 'Subject of an email to a customer (on approval)', 'wbk' ),
            array( $this, 'render_email_customer_approve_subject' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_approve_subject', array( $this, 'validate_email_customer_approve_subject' ) );
        add_settings_field(
            'wbk_email_customer_approve_message',
            __( 'Message to a customer (on approval)', 'wbk' ),
            array( $this, 'render_email_customer_approve_message' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_approve_message', array( $this, 'validate_email_customer_approve_message' ) );
        wbk_opt()->add_option(
            'wbk_email_customer_approve_copy_status',
            'checkbox',
            __( 'Send copy of approval notification to administrator', 'wbk' ),
            __( 'Check if you\'d like to send copy of approval notification to administrator' . '<br />' . 'Please, note: copy of notification will be sent if appointment is approved by the approval link.', 'wbk' ),
            'wbk_email_settings_section',
            ''
        );
        // *** BEGIN  apppointment cancellation email (admin)
        add_settings_field(
            'wbk_email_adimn_appointment_cancel_status',
            __( 'Send administrator an email (on cancellation)', 'wbk' ),
            array( $this, 'render_email_admin_appointment_cancel_status' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_adimn_appointment_cancel_status', array( $this, 'validate_email_admin_appointment_cancel_status' ) );
        wbk_opt()->add_option(
            'wbk_email_admin_cancel_multiple_mode',
            'select',
            __( 'Multiple booking cancellation notification mode (admin)', 'wbk' ),
            '',
            'wbk_email_settings_section',
            'foreach',
            array(
            'foreach' => __( 'Send Email for each booked time slot', 'wbk' ),
            'one'     => __( 'Send one Email for all booked time slots', 'wbk' ),
        )
        );
        add_settings_field(
            'wbk_email_adimn_appointment_cancel_subject',
            __( 'Subject of an email to administrator (on cancellation)', 'wbk' ),
            array( $this, 'render_email_admin_appointment_cancel_subject' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_adimn_appointment_cancel_subject', array( $this, 'validate_email_admin_appointment_cancel_subject' ) );
        add_settings_field(
            'wbk_email_adimn_appointment_cancel_message',
            __( 'Message to administrator (on cancellation)', 'wbk' ),
            array( $this, 'render_email_admin_appointment_cancel_message' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_adimn_appointment_cancel_message', array( $this, 'validate_email_admin_appointment_cancel_message' ) );
        // *** END  apppointment cancellation email (admin)
        // *** BEGIN appointment cancellation email (customer)
        add_settings_field(
            'wbk_email_customer_appointment_cancel_status',
            __( 'Send customer an email (on cancellation)', 'wbk' ),
            array( $this, 'render_email_customer_appointment_cancel_status' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_appointment_cancel_status', array( $this, 'validate_email_customer_appointment_cancel_status' ) );
        wbk_opt()->add_option(
            'wbk_email_customer_cancel_multiple_mode',
            'select',
            __( 'Multiple booking cancellation notification mode (customer)', 'wbk' ),
            '',
            'wbk_email_settings_section',
            'foreach',
            array(
            'foreach' => __( 'Send Email for each cancelled time slot', 'wbk' ),
            'one'     => __( 'Send one Email for all cancelled time slots', 'wbk' ),
        )
        );
        add_settings_field(
            'wbk_email_customer_appointment_cancel_subject',
            __( 'Subject of an email to customer (on cancellation)', 'wbk' ),
            array( $this, 'render_email_customer_appointment_cancel_subject' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_appointment_cancel_subject', array( $this, 'validate_email_customer_appointment_cancel_subject' ) );
        add_settings_field(
            'wbk_email_customer_appointment_cancel_message',
            __( 'Message to customer (on cancellation by administrator)', 'wbk' ),
            array( $this, 'render_email_customer_appointment_cancel_message' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_appointment_cancel_message', array( $this, 'validate_email_customer_appointment_cancel_message' ) );
        add_settings_field(
            'wbk_email_customer_bycustomer_appointment_cancel_message',
            __( 'Message to customer (on cancellation by customer)', 'wbk' ),
            array( $this, 'render_email_customer_bycustomer_appointment_cancel_message' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_bycustomer_appointment_cancel_message', array( $this, 'validate_email_customer_bycustomer_appointment_cancel_message' ) );
        add_settings_field(
            'wbk_email_secondary_book_status',
            __( 'Send an email to other customers from the group (if provided)', 'wbk' ),
            array( $this, 'render_email_secondary_book_status' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_secondary_book_status', array( $this, 'validate_email_secondary_book_status' ) );
        add_settings_field(
            'wbk_email_secondary_book_subject',
            __( 'Subject of an email to a customers from the group', 'wbk' ),
            array( $this, 'render_email_secondary_book_subject' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_secondary_book_subject', array( $this, 'validate_email_secondary_book_subject' ) );
        add_settings_field(
            'wbk_email_secondary_book_message',
            __( 'Message to a customers from the group', 'wbk' ),
            array( $this, 'render_email_secondary_book_message' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_secondary_book_message', array( $this, 'validate_email_secondary_book_message' ) );
        add_settings_field(
            'wbk_email_admin_book_status',
            __( 'Send administrator an email (on booking)', 'wbk' ),
            array( $this, 'render_email_admin_book_status' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_admin_book_status', array( $this, 'validate_email_admin_book_status' ) );
        add_settings_field(
            'wbk_email_admin_book_subject',
            __( 'Subject of an email to an administrator (on booking)', 'wbk' ),
            array( $this, 'render_email_admin_book_subject' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_admin_book_subject', array( $this, 'validate_email_admin_book_subject' ) );
        add_settings_field(
            'wbk_email_admin_book_message',
            __( 'Message to an administrator (on booking)', 'wbk' ),
            array( $this, 'render_email_admin_book_message' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_admin_book_message', array( $this, 'validate_email_admin_book_message' ) );
        add_settings_field(
            'wbk_email_admin_paymentrcvd_status',
            __( 'Send administrator an email (on payment received)', 'wbk' ),
            array( $this, 'render_email_admin_paymentrecvd_status' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_admin_paymentrcvd_status', array( $this, 'validate_email_admin_paymentrcvd_status' ) );
        add_settings_field(
            'wbk_email_admin_paymentrcvd_subject',
            __( 'Subject of an email to an administrator (on payment received)', 'wbk' ),
            array( $this, 'render_email_admin_paymentrcvd_subject' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_admin_paymentrcvd_subject', array( $this, 'validate_email_admin_paymentrcvd_subject' ) );
        add_settings_field(
            'wbk_email_admin_paymentrcvd_message',
            __( 'Message to an administrator (on payment received)', 'wbk' ),
            array( $this, 'render_email_admin_paymentrcvd_message' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_admin_paymentrcvd_message', array( $this, 'validate_email_admin_paymentrcvd_message' ) );
        add_settings_field(
            'wbk_email_customer_paymentrcvd_status',
            __( 'Send customer an email (on payment received)', 'wbk' ),
            array( $this, 'render_email_customer_paymentrecvd_status' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_paymentrcvd_status', array( $this, 'validate_email_customer_paymentrcvd_status' ) );
        add_settings_field(
            'wbk_email_customer_paymentrcvd_subject',
            __( 'Subject of an email to to a customer (on payment received)', 'wbk' ),
            array( $this, 'render_email_customer_paymentrcvd_subject' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_paymentrcvd_subject', array( $this, 'validate_email_customer_paymentrcvd_subject' ) );
        add_settings_field(
            'wbk_email_customer_paymentrcvd_message',
            __( 'Message to a customer (on payment received)', 'wbk' ),
            array( $this, 'render_email_customer_paymentrcvd_message' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_paymentrcvd_message', array( $this, 'validate_email_customer_paymentrcvd_message' ) );
        add_settings_field(
            'wbk_email_customer_arrived_status',
            __( 'Send notification when status is changed to Arrived', 'wbk' ),
            array( $this, 'render_email_customer_arrived_status' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_arrived_status', array( $this, 'validate_email_customer_arrived_status' ) );
        add_settings_field(
            'wbk_email_customer_arrived_subject',
            __( 'Subject of an email to a customer (on status changed to arrived)', 'wbk' ),
            array( $this, 'render_email_customer_arrived_subject' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_arrived_subject', array( $this, 'validate_email_customer_arrived_subject' ) );
        add_settings_field(
            'wbk_email_customer_arrived_message',
            __( 'Message to a customer (on status is changed to arrived)', 'wbk' ),
            array( $this, 'render_email_customer_arrived_message' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_arrived_message', array( $this, 'validate_email_customer_arrived_message' ) );
        add_settings_field(
            'wbk_email_admin_daily_status',
            __( 'Send administrator reminders', 'wbk' ),
            array( $this, 'render_email_admin_daily_status' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_admin_daily_status', array( $this, 'validate_email_admin_daily_status' ) );
        //
        add_settings_field(
            'wbk_email_admin_daily_subject',
            __( 'Subject of administrator reminders', 'wbk' ),
            array( $this, 'render_email_admin_daily_subject' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_admin_daily_subject', array( $this, 'validate_email_admin_daily_subject' ) );
        add_settings_field(
            'wbk_email_admin_daily_message',
            __( 'Administrator reminders message', 'wbk' ),
            array( $this, 'render_email_admin_daily_message' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_admin_daily_message', array( $this, 'validate_email_admin_daily_message' ) );
        // customer daily
        add_settings_field(
            'wbk_email_customer_daily_status',
            __( 'Send customer reminders', 'wbk' ),
            array( $this, 'render_email_customer_daily_status' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_daily_status', array( $this, 'validate_email_customer_daily_status' ) );
        wbk_opt()->add_option(
            'wbk_email_reminder_days',
            'text',
            __( 'Send reminders to customers in X days', 'wbk' ),
            __( 'Number of days: ' . '<br />' . 'Today: 0; ' . 'Tomorrow: 1; ' . 'Day after tomorrow: 2; ' . 'etc.', 'wbk' ),
            'wbk_email_settings_section',
            '1'
        );
        add_settings_field(
            'wbk_email_customer_daily_subject',
            __( 'Subject of customer reminders', 'wbk' ),
            array( $this, 'render_email_customer_daily_subject' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_daily_subject', array( $this, 'validate_email_customer_daily_subject' ) );
        add_settings_field(
            'wbk_email_customer_daily_message',
            __( 'Customer reminders message', 'wbk' ),
            array( $this, 'render_email_customer_daily_message' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_daily_message', array( $this, 'validate_email_customer_daily_message' ) );
        // customer daily end
        $format = WBK_Date_Time_Utils::getTimeFormat();
        date_default_timezone_set( 'UTC' );
        $data_time = [];
        $data_keys = [];
        for ( $i = 0 ;  $i < 86400 ;  $i += 600 ) {
            array_push( $data_time, wp_date( $format, $i, new DateTimeZone( date_default_timezone_get() ) ) );
            array_push( $data_keys, $i );
        }
        $data_time = array_combine( $data_keys, $data_time );
        wbk_opt()->add_option(
            'wbk_email_admin_daily_time',
            'select',
            __( 'Reminder sending time', 'wbk' ),
            __( 'Current local time: ', 'wbk' ) . wp_date( $format, time(), new DateTimeZone( get_option( 'wbk_timezone', 'UTC' ) ) ),
            'wbk_general_settings_section',
            'disabled',
            $data_time
        );
        wbk_opt()->add_option(
            'wbk_email_reminders_only_for_approved',
            'checkbox',
            __( 'Send reminders only for approved appointments', 'wbk' ),
            __( 'Check if you\'d like to send reminders only for approved appointments.', 'wbk' ),
            'wbk_email_settings_section',
            ''
        );
        wbk_opt()->add_option(
            'wbk_email_customer_send_invoice',
            'select',
            __( 'Send invoice to customer', 'wbk' ),
            __( 'Use this option to control the dispatch of the invoice in parallel with the notification.', 'wbk' ),
            'wbk_email_settings_section',
            'disabled',
            array(
            'disabled'   => __( 'Do not send invoice', 'wbk' ),
            'onbooking'  => __( 'Send invoice on booking', 'wbk' ),
            'onapproval' => __( 'Send invoice on approval', 'wbk' ),
            'onpayment'  => __( 'Send invoice on payment complete', 'wbk' ),
        )
        );
        add_settings_field(
            'wbk_email_customer_invoice_subject',
            __( 'Invoice email subject', 'wbk' ),
            array( $this, 'render_email_customer_invoice_subject' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_invoice_subject', array( $this, 'validate_email_customer_invoice_subject' ) );
        wbk_opt()->add_option(
            'wbk_email_current_invoice_number',
            'text',
            __( 'Current invoice number', 'wbk' ),
            __( 'Set the initial number of invoice. Placeholder for notifications: #invoice_number' . '<br />' . 'Each time a customer pay with PayPal or Stripe, the value of this option will be increased by one.', 'wbk' ),
            'wbk_email_settings_section',
            '1'
        );
        wbk_opt()->add_option(
            'wbk_email_on_update_booking_subject',
            'text',
            __( 'Notification subject (when booking changes)', 'wbk' ),
            '',
            'wbk_email_settings_section',
            ''
        );
        wbk_opt()->add_option(
            'wbk_email_send_invoice_copy',
            'checkbox',
            __( 'Send copies of invoices to the administrator', 'wbk' ),
            __( 'Check if you\'d like to send copies of invoices to administrator', 'wbk' ),
            'wbk_email_settings_section',
            ''
        );
        wbk_opt()->add_option(
            'wbk_email_override_replyto',
            'checkbox',
            __( 'Override default reply-to headers with booking-related data', 'wbk' ),
            __( 'Check to override', 'wbk' ),
            'wbk_email_settings_section',
            ''
        );
        wbk_opt()->add_option(
            'wbk_from_name',
            'text',
            __( 'From: name', 'wbk' ),
            '',
            'wbk_email_settings_section',
            ''
        );
        wbk_opt()->add_option(
            'wbk_from_email',
            'text',
            __( 'From: email', 'wbk' ),
            '',
            'wbk_email_settings_section',
            ''
        );
        wbk_opt()->add_option(
            'wbk_super_admin_email',
            'text',
            __( 'Send copies of service notifications to addresses', 'wbk' ),
            '',
            'wbk_email_settings_section',
            ''
        );
        wbk_opt()->add_option(
            'wbk_email_landing',
            'text',
            __( 'Notifications landing page', 'wbk' ),
            __( 'This page will be used as a landing for payment or cancellation. Page should contain [webba_email_landing] or [webba_booking] shortcode.', 'wbk' ),
            'wbk_email_settings_section',
            ''
        );
        wbk_opt()->add_option(
            'wbk_multi_booking_max',
            'text',
            __( 'Maximum number of bookings in one session', 'wbk' ),
            __( 'Default value can be overriden in the service settings', 'wbk' ),
            'wbk_mode_settings_section',
            '',
            null,
            'wbk-options',
            'wbk_options',
            array(
            'wbk_multi_booking' => 'enabled|enabled_slot',
        )
        );
        wbk_opt()->add_option(
            'wbk_skip_timeslot_select',
            'select',
            __( 'Skip timeslot selection', 'wbk' ),
            __( 'Skip time slot selection if only one time slot is available.' . '<br />' . 'IMPORTANT: enable this option only with Basic mode and multiple booking disabled.' . '<br />' . 'Make sure your service schedule includes ONLY ONE time slot available on a day.', 'wbk' ),
            'wbk_mode_settings_section',
            'disabled',
            array(
            'disabled' => __( 'Disabled', 'wbk' ),
            'enabled'  => __( 'Enabled', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_places_selection_mode',
            'select',
            __( 'Multiple seat selection mode', 'wbk' ),
            '',
            'wbk_mode_settings_section',
            'normal',
            array(
            'normal'            => __( 'Let users select count', 'wbk' ),
            'normal_no_default' => __( 'Let users select count (no default value)', 'wbk' ),
            '1'                 => __( 'Allow select only one place', 'wbk' ),
            'max'               => __( 'Allow select only maximum places', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_show_service_description',
            'select',
            __( 'Show service description', 'wbk' ),
            __( 'Enable this option to show service description below the service select on the frontend.' ),
            'wbk_mode_settings_section',
            'disabled',
            array(
            'disabled' => __( 'Disabled', 'wbk' ),
            'enabled'  => __( 'Enabled', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_range_selection',
            'select',
            __( 'Range selection', 'wbk' ),
            __( 'Enable this option to allow range selection in multiple booking mode', 'wbk' ),
            'wbk_mode_settings_section',
            'disabled',
            array(
            'disabled' => __( 'Disabled', 'wbk' ),
            'enabled'  => __( 'Enabled', 'wbk' ),
        ),
            'wbk-options',
            'wbk_options',
            array(
            'wbk_multi_booking' => 'enabled|enabled_slot',
        )
        );
        wbk_opt()->add_option(
            'wbk_date_input',
            'select',
            __( 'Date input', 'wbk' ),
            '',
            'wbk_mode_settings_section',
            'popup',
            array(
            'popup'    => __( 'Popup', 'wbk' ),
            'classic'  => __( 'Classic', 'wbk' ),
            'dropdown' => __( 'Dropdown', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_date_input_dropdown_count',
            'text',
            __( 'Number of dates in the dropdown input', 'wbk' ),
            __( 'Used only for dropdown date select.', 'wbk' ),
            'wbk_mode_settings_section',
            '30',
            'null',
            'wbk-options',
            'wbk_options',
            array(
            'wbk_date_input' => 'dropdown',
        )
        );
        wbk_opt()->add_option(
            'wbk_avaiability_popup_calendar',
            'text',
            __( 'Number of dates in the calendar for single-service mode', 'wbk' ),
            __( 'IMPORTANT: this option is used for single-service booking only', 'wbk' ),
            'wbk_mode_settings_section',
            '360'
        );
        wbk_opt()->add_option(
            'wbk_auto_select_first_date',
            'select',
            __( 'Automatically select first available date', 'wbk' ),
            '',
            'wbk_mode_settings_section',
            'disabled',
            array(
            'disabled' => __( 'Disabled', 'wbk' ),
            'enabled'  => __( 'Enabled', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_timeslot_time_string',
            'select',
            __( 'Timeslot time format', 'wbk' ),
            '',
            'wbk_mode_settings_section',
            'start',
            array(
            'start'     => __( 'Start', 'wbk' ),
            'start_end' => __( 'Start', 'wbk' ) . ' - ' . __( 'end', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_timeslot_format',
            'select',
            __( 'Timeslot format', 'wbk' ),
            '',
            'wbk_mode_settings_section',
            'detailed',
            array(
            'detailed'  => __( 'Show details and BOOK button', 'wbk' ),
            'time_only' => __( 'Show only time button', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_show_local_time',
            'select',
            __( 'Show the user\'s local time in a timeslot', 'wbk' ),
            '',
            'wbk_mode_settings_section',
            'disabled',
            array(
            'disabled'     => __( 'Disabled', 'wbk' ),
            'enabled'      => __( 'Enabled', 'wbk' ),
            'enabled_only' => __( 'Enabled (show only local time)', 'wbk' ),
        )
        );
        // csv delimiter
        wbk_opt()->add_option(
            'wbk_csv_delimiter',
            'select',
            __( 'CSV delimiter', 'wbk' ),
            __( 'If your date format does not include comma, use a comma in this option.<br>Otherwise use semicolon.', 'wbk' ),
            'wbk_general_settings_section',
            'comma',
            array(
            'comma'     => __( 'Comma', 'wbk' ),
            'semicolon' => __( 'Semicolon', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_jquery_nc',
            'select',
            __( 'jQuery no-conflict mode', 'wbk' ),
            __( 'If date picker does not work, enabling this option may be a possible solution.', 'wbk' ),
            'wbk_general_settings_section',
            'disabled',
            array(
            'enabled'  => __( 'Enabled', 'wbk' ),
            'disabled' => __( 'Disabled', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_pickadate_load',
            'select',
            __( 'Load Pickadate javascript', 'wbk' ),
            __( 'Set "no" if there are plugins in your WordPress that are using the pickadate date picker.', 'wbk' ),
            'wbk_general_settings_section',
            'yes',
            array(
            'yes' => __( 'Yes', 'wbk' ),
            'no'  => __( 'No', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_allow_manage_by_link',
            'select',
            __( 'Allow cancellation or approval by link', 'wbk' ),
            __( 'Set "yes" to allow administrator to cancel or approve appointment with the link sent in notification.', 'wbk' ),
            'wbk_mode_settings_section',
            'no',
            array(
            'yes' => __( 'Yes', 'wbk' ),
            'no'  => __( 'No', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_tax_for_messages',
            'select',
            __( 'Tax used for the #total_amount placeholder', 'wbk' ),
            __( 'This option is used when calculating the total amount with #total_amount placeholders in email and interface messages', 'wbk' ),
            'wbk_general_settings_section',
            'paypal',
            array(
            'paypal' => __( 'PayPal tax option', 'wbk' ),
            'stripe' => __( 'Stripe tax option', 'wbk' ),
            'none'   => __( 'Do not include tax', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_do_not_tax_deposit',
            'checkbox',
            __( 'Do not tax the deposit (service fee)', 'wbk' ),
            __( 'If enabled, deposit part of the amount will be not taxed.<br>Important notice: do not use subtotal and tax placeholders when this option is enabled.<br>', 'wbk' ),
            'wbk_general_settings_section',
            ''
        );
        wbk_opt()->add_option(
            'wbk_price_fractional',
            'text',
            __( 'Number of digits in the fractional part of the price', 'wbk' ),
            __( 'Examples: 1 - 25.1, 2 - 25.10', 'wbk' ),
            'wbk_general_settings_section',
            '2'
        );
        wbk_opt()->add_option(
            'wbk_price_separator',
            'text',
            __( 'Price fraction separator', 'wbk' ),
            __( 'Examples: . - 25.50, , - 25,50', 'wbk' ),
            'wbk_general_settings_section',
            '.'
        );
        wbk_opt()->add_option(
            'wbk_scroll_container',
            'text',
            __( 'Scroll container', 'wbk' ),
            '',
            'wbk_mode_settings_section',
            'html, body'
        );
        wbk_opt()->add_option(
            'wbk_scroll_value',
            'text',
            __( 'Scroll value', 'wbk' ),
            '',
            'wbk_mode_settings_section',
            '120'
        );
        wbk_opt()->add_option(
            'wbk_general_dynamic_placeholders',
            'text',
            __( 'List of dynamic placeholders', 'wbk' ),
            __( 'Specify a comma-separated list of placeholders that should be removed from the string unless they have been replaced with values. This option is useful if you are using different custom fields for services and as a result some custom field placeholders are not replaced.', 'wbk' ),
            'wbk_general_settings_section',
            ''
        );
        wbk_opt()->add_option(
            'wbk_load_js_in_footer',
            'checkbox',
            __( 'Load javascript files in footer', 'wbk' ),
            'Enabling this option may increase page loading time in some cases.',
            'wbk_general_settings_section',
            ''
        );
        wbk_opt()->add_option(
            'wbk_service_label',
            'text',
            __( 'Select service label', 'wbk' ),
            __( 'Service frontend label', 'wbk' ),
            'wbk_translation_settings_section',
            __( 'Select a service', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_category_label',
            'text',
            __( 'Select category label', 'wbk' ),
            __( 'Category frontend label', 'wbk' ),
            'wbk_translation_settings_section',
            __( 'Select category', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_date_extended_label',
            'text',
            __( 'Select date label (extended mode)', 'wbk' ),
            __( 'Date frontend label', 'wbk' ),
            'wbk_translation_settings_section',
            __( 'Book an appointment on or after', 'wbk' ),
            array(
            'no_html' => true,
        )
        );
        wbk_opt()->add_option(
            'wbk_date_basic_label',
            'text',
            __( 'Select date label (basic mode)', 'wbk' ),
            __( 'Date frontend label', 'wbk' ),
            'wbk_translation_settings_section',
            __( 'Book an appointment on', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_date_input_placeholder',
            'text_alfa_numeric',
            __( 'Select date input placeholder', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'date', 'wbk' ),
            array(
            'no_html' => true,
        )
        );
        wbk_opt()->add_option(
            'wbk_hours_label',
            'text',
            __( 'Select hours label', 'wbk' ),
            __( 'Hours frontend label', 'wbk' ),
            'wbk_translation_settings_section',
            __( 'Suitable hours', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_slots_label',
            'text',
            __( 'Select timeslots label', 'wbk' ),
            __( 'Timeslots frontend label', 'wbk' ),
            'wbk_translation_settings_section',
            __( 'Available timeslots', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_form_label',
            'text',
            __( 'Booking form label', 'wbk' ),
            __( 'Message before the booking form', 'wbk' ) . '<br />' . __( 'Available placeholders', 'wbk' ) . ': #service (service name), #date (appointment date), #time (appointment time), #dt (appointment date and time).' . '<br />' . '#drt (appointment date and time with new line), #dre (appointment date and time range with new line), #price (service price for a single timeslot), #total_amount (price for selected timeslot(s) + tax), #selected_count (total count of timeslots), #local (local time), #dlocal (local date)' . '<br />' . '#sd (single day in multiple selection mode), #dnl (dates with new line)' . '<br />' . __( 'For the multi-service mode use [split] placeholder to split the string in 2 parts: static and repeatable', 'wbk' ) . '<br />' . '#range (time range in multiple selection mode), #lrange (local time range in multiple selection mode)',
            'wbk_translation_settings_section',
            __( 'You are booking #service on #date at #time <br>Please, fill in a form:', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_book_items_quantity_label',
            'text',
            __( 'Booking items count label', 'wbk' ),
            __( 'Booking items count frontend label', 'wbk' ) . '<br />' . __( 'Available placeholders: ', 'wbk' ) . '#service',
            'wbk_translation_settings_section',
            __( 'Booking items count', 'wbk' )
        );
        // booked slot
        wbk_opt()->add_option(
            'wbk_booked_text',
            'text',
            __( 'Booked timeslot text', 'wbk' ),
            __( 'Text on booked timeslot. Available placeholders: #username, #time.', 'wbk' ) . '<br />' . __( 'Since version 3.3.61 you can use general placeholders in this option. More information: ', 'wbk' ) . '<a href="https://webba-booking.com/documentation/placeholders/" rel="noopener" target="_blank" >' . __( 'Placeholders', 'wbk' ) . '</a>.',
            'wbk_translation_settings_section',
            __( 'Booked', 'wbk' )
        );
        // format of local time (in timeslots)
        // added since 3.1.1
        wbk_opt()->add_option(
            'wbk_local_time_format',
            'text',
            __( 'Local time format', 'wbk' ),
            __( 'Available placeholders', 'wbk' ) . ':  #ts (start local time), #te (end local time), #ds (local date).',
            'wbk_translation_settings_section',
            __( 'Your local time', 'wbk' ) . ':<br>#ds<br>#ts'
        );
        wbk_opt()->add_option(
            'wbk_server_time_format',
            'text',
            __( 'Text before the time in timeslot', 'wbk' ),
            __( 'Available placeholders', 'wbk' ) . ':  #ts (start local time), #te (end local time), #ds (local date).',
            'wbk_translation_settings_section',
            ''
        );
        wbk_opt()->add_option(
            'wbk_server_time_format2',
            'text',
            __( 'Text after the time in timeslot', 'wbk' ),
            __( 'Available placeholders', 'wbk' ) . ':  #ts (start local time), #te (end local time), #ds (local date).',
            'wbk_translation_settings_section',
            ''
        );
        wbk_opt()->add_option(
            'wbk_time_slot_available_text',
            'text',
            __( 'Availability label', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'available', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_book_text_timeslot',
            'text_alfa_numeric',
            __( 'Book button text (timeslot)', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Book', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_deselect_text_timeslot',
            'text',
            __( 'Deselect text (timeslot)', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            ''
        );
        wbk_opt()->add_option(
            'wbk_book_text_form',
            'text',
            __( 'Book button text (form)', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Book', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_name_label',
            'text',
            __( 'Name label (booking form)', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Name', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_email_label',
            'text',
            __( 'Email label (booking form)', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Email', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_phone_label',
            'text',
            __( 'Phone label (booking form)', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Phone', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_comment_label',
            'text',
            __( 'Comment label (booking form)', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Comment', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_book_thanks_message',
            'editor',
            __( 'Booking done message', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Thanks for booking appointment', 'wbk' ),
            '',
            'wbk-options',
            'wbk_options'
        );
        wbk_opt()->add_option(
            'wbk_book_not_found_message',
            'text',
            __( 'Timeslots not found message', 'wbk' ),
            __( 'Timeslots not found message', 'wbk' ),
            'wbk_translation_settings_section',
            __( 'Unfortunately we were unable to meet your search criteria. Please change the criteria and try again.', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_payment_pay_with_paypal_btn_text',
            'text_alfa_numeric',
            __( 'PayPal payment button text', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Pay now with PayPal', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_payment_details_title',
            'text_alfa_numeric',
            __( 'Payment details title', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Payment details', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_payment_item_name',
            'text',
            __( 'Payment item name', 'wbk' ),
            __( 'Available placeholders: #service, #date, #time, #tr (time range), #id, #quantity', 'wbk' ),
            'wbk_translation_settings_section',
            '#service on #date at #time'
        );
        wbk_opt()->add_option(
            'wbk_payment_price_format',
            'text',
            __( 'Price format', 'wbk' ),
            __( 'Required placeholder: #price.', 'wbk' ),
            'wbk_translation_settings_section',
            '$#price'
        );
        wbk_opt()->add_option(
            'wbk_payment_subtotal_title',
            'text',
            __( 'Subtotal title', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Subtotal', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_payment_total_title',
            'text',
            __( 'Total title', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Total', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_nothing_to_pay_message',
            'text',
            __( 'Message if no booking available for payment found', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'There are no bookings available for payment.', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_show_locked_as_booked',
            'select',
            __( 'Show locked timeslots as booked', 'wbk' ),
            '',
            'wbk_mode_settings_section',
            'no',
            array(
            'yes' => __( 'Yes', 'wbk' ),
            'no'  => __( 'No', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_allow_attachemnt',
            'select',
            __( 'Allow attachments', 'wbk' ),
            __( 'Enable this option to allow users attach files in the booking form.<br>File input field needs to be included in the custom form. More information here: <a href="https://webba-booking.com/documentation/set-up-frontend-booking-process/using-custom-fields-in-the-booking-form/" target="_blank" rel="noopener noreferrer">Using custom fields in the booking form
</a>', 'wbk' ),
            'wbk_general_settings_section',
            'no',
            array(
            'yes' => __( 'Yes', 'wbk' ),
            'no'  => __( 'No', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_delete_attachemnt',
            'select',
            __( 'Automatically delete attachments', 'wbk' ),
            __( 'It is highly recommended that you set this option to Yes to delete the attachment as soon as the notification is sent.', 'wbk' ),
            'wbk_general_settings_section',
            'yes',
            array(
            'yes' => __( 'Yes', 'wbk' ),
            'no'  => __( 'No', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_order_service_by',
            'select',
            __( 'Order service by', 'wbk' ),
            '',
            'wbk_mode_settings_section',
            'a-z',
            array(
            'a-z'        => __( 'A-Z', 'wbk' ),
            'priority'   => __( 'Priority (descending)', 'wbk' ),
            'priority_a' => __( 'Priority (ascending)', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_night_hours',
            'text',
            __( 'Show night hours timeslots in previous day', 'wbk' ),
            __( 'The number of hours after midnight.', 'wbk' ),
            'wbk_mode_settings_section',
            '0'
        );
        wbk_opt()->add_option(
            'wbk_allow_cross_midnight',
            'checkbox',
            __( 'Allow timeslots to cross midnight', 'wbk' ),
            '',
            'wbk_appointments_settings_section',
            ''
        );
        wbk_opt()->add_option(
            'wbk_multi_serv_date_limit',
            'text',
            __( 'Number of dates in the calendar for multiple-services mode', 'wbk' ),
            __( 'IMPORTANT: this option is used for multiple-services booking only', 'wbk' ),
            'wbk_mode_settings_section',
            '360'
        );
        wbk_opt()->add_option(
            'wbk_disallow_after',
            'text',
            __( 'Block timeslots after X hours from the current time', 'wbk' ),
            __( 'Set 0 to not disable time slots', 'wbk' ),
            'wbk_appointments_settings_section',
            '0'
        );
        wbk_opt()->add_option(
            'wbk_gdrp',
            'select',
            __( 'EU GDPR Compliance', 'wbk' ),
            '',
            'wbk_appointments_settings_section',
            'disabled',
            array(
            'disabled' => __( 'Disabled', 'wbk' ),
            'enabled'  => __( 'Enabled', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_allow_ongoing_time_slot',
            'select',
            __( 'Allow to book the ongoing timeslot', 'wbk' ),
            '',
            'wbk_appointments_settings_section',
            'disabled',
            array(
            'allow'    => __( 'Allow', 'wbk' ),
            'disallow' => __( 'Disallow', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_days_in_extended_mode',
            'select',
            __( 'Default number of days shown in extended mode', 'wbk' ),
            '',
            'wbk_mode_settings_section',
            '3',
            array(
            '1'        => '1',
            '2'        => '2',
            'default'  => '3',
            '4'        => '4',
            '5'        => '5',
            '6'        => '6',
            '7'        => '7',
            '8'        => '8',
            '9'        => '9',
            '10'       => '10',
            'lowlimit' => __( 'Use Low limit value of service', 'wbk' ),
            'uplimit'  => __( 'Use Up limit value of services', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_show_details_prev_booking',
            'select',
            __( 'Show details of previous bookings in a timeslot', 'wbk' ),
            __( 'This option applies to services with a multiple places per time slot', 'wbk' ),
            'wbk_mode_settings_section',
            'disabled',
            array(
            'disabled' => __( 'Disabled', 'wbk' ),
            'enabled'  => __( 'Enabled', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_mode_overlapping_availabiliy',
            'checkbox',
            __( 'Consider the availability of overlapping time intervals', 'wbk' ),
            '',
            'wbk_appointments_settings_section',
            'true'
        );
        wbk_opt()->add_option(
            'wbk_set_arrived_after',
            'text',
            __( 'Set the status to "Arrived" X minutes after the end of the booking', 'wbk' ),
            __( 'Leave empty to not update the status', 'wbk' ),
            'wbk_appointments_settings_section',
            ''
        );
        wbk_opt()->add_option(
            'wbk_payment_approve_text',
            'text_alfa_numeric',
            __( 'Approve payment', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Approve payment', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_payment_result_title',
            'text',
            __( 'Payment result title', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Payment status', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_payment_success_message',
            'text',
            __( 'Payment result success message', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Payment completed.', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_payment_cancel_message',
            'text',
            __( 'Payment result cancel message', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Payment canceled.', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_cancel_button_text',
            'text_alfa_numeric',
            __( 'Booking cancel button text', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Cancel booking', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_checkout_button_text',
            'text',
            __( 'Checkout button text', 'wbk' ),
            __( 'Available placeholders: ', 'wbk' ) . '#selected_count, #total_count, #low_limit',
            'wbk_translation_settings_section',
            __( 'Checkout', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_appointment_information',
            'text',
            __( 'Appointment information', 'wbk' ),
            __( 'Appointment information on payment and cancellation forms.', 'wbk' ) . '<br />' . __( 'Displayed when customers pay for booking or cancel the booking with the link sent in e-mail notification.', 'wbk' ) . '<br />' . __( 'Available placeholders', 'wbk' ) . ': #name (customer name), #id (appointment id), #service (service name), #date (appointment date), #time (appointment time), #dt (appointment date and time), #start_end (appointment time in start-end fornmat).',
            'wbk_translation_settings_section',
            __( 'Appointment on #dt', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_booking_cancel_email_label',
            'text',
            __( 'Email input label on cancel booking', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Please, enter your email to confirm cancellation', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_booking_canceled_message',
            'text',
            __( 'Booking canceled message (cutomer)', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Your appointment booking has been canceled.', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_booking_canceled_message_admin',
            'text',
            __( 'Booking canceled message (admin)', 'wbk' ),
            __( 'Available placeholders:', 'wbk' ) . ' #count',
            'wbk_translation_settings_section',
            __( 'Appointment canceled #count', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_booking_approved_message_admin',
            'text',
            __( 'Booking approved message (admin)', 'wbk' ),
            __( 'Available placeholders:', 'wbk' ) . ' #count',
            'wbk_translation_settings_section',
            __( 'Appointment approved #count', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_booking_cancel_error_message',
            'text',
            __( 'Error message on cancel booking', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Unable to cancel booking, please check the email you\'ve entered.', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_booking_couldnt_be_canceled',
            'text',
            __( 'Warning message on cancel booking (reason: paid booking)', 'wbk' ),
            __( 'Displayed when customer tries to cancel paid booking.', 'wbk' ),
            'wbk_translation_settings_section',
            __( 'Paid booking can\'t be canceled.', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_booking_couldnt_be_canceled2',
            'text',
            __( 'Warning message on cancel booking (buffer)', 'wbk' ),
            __( 'Displayed when a customer tries to cancel an appointment/reservation within less than the time allowed to do so.', 'wbk' ),
            'wbk_translation_settings_section',
            __( 'Sorry, you can not cancel because you have exceeded the time allowed to do so.', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_email_landing_text',
            'text',
            __( 'Text of the payment link (customer)', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Click here to pay for your booking.', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_email_landing_text_cancel',
            'text',
            __( 'Text of the cancellation link (customer)', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Click here to cancel your booking.', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_email_landing_text_cancel_admin',
            'text',
            __( 'Text of the cancellation link (administrator)', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Click here to cancel this booking.', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_email_landing_text_approve_admin',
            'text',
            __( 'Text of the approval link (administrator)', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Click here to approve this booking.', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_email_landing_text_gg_event_add',
            'text',
            __( 'Text of the link for adding to customer\'s Google Calendar', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Click here to add this event to your Google Calendar.', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_add_gg_button_text',
            'text',
            __( 'Add to customer\'s Google Calendar button text', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Add to my Google Calendar', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_gg_calendar_add_event_success',
            'text',
            __( 'Google calendar event adding success message', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Appointment data added to Google Calendar.', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_gg_calendar_add_event_canceled',
            'text',
            __( 'Google calendar event adding error message', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Appointment data not added to Google Calendar.', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_email_landing_text_invalid_token',
            'text',
            __( 'Appointment token error message', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Appointment booking doesn\'t exist.', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_gg_calendar_event_title',
            'text',
            __( 'Google calendar event / iCal summary (administrator)', 'wbk' ),
            __( 'Available placeholders:', 'wbk' ) . ' #customer_name, #customer_phone, #customer_email, #customer_comment, #items_count, #appointment_id, #customer_custom, #total_amount, #service_name, #status' . '<br />' . __( 'Placeholder for custom field:', 'wbk' ) . ' #field_ + custom field id. Example: #field_custom-field-1',
            'wbk_translation_settings_section',
            __( '#customer_name', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_gg_calendar_event_description',
            'text',
            __( 'Google calendar event / iCal description (administrator)', 'wbk' ),
            __( 'Available placeholders:', 'wbk' ) . ' ' . '#customer_name, #customer_phone, #customer_email, #customer_comment, #items_count, #appointment_id, #customer_custom, #total_amount, #service_name, #status' . '<br />' . __( 'Placeholder for custom field:', 'wbk' ) . ' #field_ + custom field id. Example: #field_custom-field-1' . '<br />' . __( 'Add {n} for new line (only for Google Calendar events)', 'wbk' ),
            'wbk_translation_settings_section',
            __( '#customer_name #customer_phone', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_gg_calendar_event_title_customer',
            'text',
            __( 'Google calendar event / iCal summary (customer)', 'wbk' ),
            __( 'Available placeholders:', 'wbk' ) . '#customer_name, #customer_phone, #customer_email, #customer_comment, #items_count, #appointment_id, #customer_custom, #total_amount, #service_name' . '<br />' . __( 'Placeholder for custom field:', 'wbk' ) . ' #field_ + custom field id. Example: #field_custom-field-1',
            'wbk_translation_settings_section',
            __( '#service_name', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_gg_calendar_event_description_customer',
            'text',
            __( 'Google calendar event / iCal description (customer)', 'wbk' ),
            __( 'Available placeholders:', 'wbk' ) . '#customer_name, #customer_phone, #customer_email, #customer_comment, #items_count, #appointment_id, #customer_custom, #total_amount, #service_name' . '<br />' . __( 'Placeholder for custom field:', 'wbk' ) . ' #field_ + custom field id. Example: #field_custom-field-1',
            'wbk_translation_settings_section',
            __( 'Your appointment id is #appointment_id', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_stripe_button_text',
            'text_alfa_numeric',
            __( 'Stripe button text', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Pay with credit card', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_stripe_card_element_error_message',
            'text',
            __( 'Stripe card element error message', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'incorrect input', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_stripe_api_error_message',
            'text',
            __( 'Stripe API error message', 'wbk' ),
            __( 'Available placeholders', 'wbk' ) . ': ' . '#response',
            'wbk_translation_settings_section',
            __( 'Payment failed: #response', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_pay_on_arrival_button_text',
            'text_alfa_numeric',
            __( 'Pay on arrival button text', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Pay on arrival', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_pay_on_arrival_message',
            'text',
            __( 'Message for Pay on arrival payment method', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            ''
        );
        wbk_opt()->add_option(
            'wbk_bank_transfer_button_text',
            'text_alfa_numeric',
            __( 'Bank transfer button text', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Pay by bank transfer', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_bank_transfer_message',
            'text',
            __( 'Message for Bank transfer payment method', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            ''
        );
        wbk_opt()->add_option(
            'wbk_coupon_field_placeholder',
            'text_alfa_numeric',
            __( 'Coupon code field placeholder', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Coupon code', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_coupon_applied',
            'text',
            __( 'Coupon success message', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Coupon applied', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_coupon_not_applied',
            'text',
            __( 'Coupon failed message', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Coupon not applied', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_payment_discount_item',
            'text',
            __( 'Discount in payment calculation', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Discount', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_product_meta_key',
            'text',
            __( 'Meta key for WooCommerce product', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Appointments', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_woo_button_text',
            'text_alfa_numeric',
            __( 'WooCommerce button text', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Add to cart', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_woo_error_add_to_cart',
            'text',
            __( 'Add to cart error message', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Booking not added to card', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_day_label',
            'text',
            __( 'Date label above the timeslots', 'wbk' ),
            __( 'Available placeholders', 'wbk' ) . ': #date, #local_date',
            'wbk_translation_settings_section',
            '#date'
        );
        wbk_opt()->add_option(
            'wbk_validation_error_message',
            'text',
            __( 'Field validation error message', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            ''
        );
        wbk_opt()->add_option(
            'wbk_daily_limit_reached_message',
            'text',
            __( 'Daily limit reached message', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Daily booking limit is reached, please select another date.', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_limit_by_email_reached_message',
            'text',
            __( 'Limit by email reached message', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'You have reached your booking limit.', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_service_fee_description',
            'text',
            __( 'Description of the service fee', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            ''
        );
        wbk_opt()->add_option(
            'wbk_tax_label',
            'text',
            __( 'Tax label', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Tax', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_no_dates_label',
            'text',
            __( 'No available dates message', 'wbk' ),
            __( 'Used with the dropdown date input', 'wbk' ),
            'wbk_translation_settings_section',
            __( 'Sorry, no free dates', 'wbk' )
        );
        wbk_opt()->add_option(
            'wbk_customer_name_output',
            'text',
            __( 'Customer name in the backend', 'wbk' ),
            __( 'Use this option if you need show custom fields near customer name in the appointments table and in the schedules.', 'wbk' ) . '<br />' . __( 'Example: #name #field_lastname', 'wbk' ) . '<br />' . __( 'The example above show how to show customer\'s name and last name. The last name is stored in the custom field with id "lastname" in this example.', 'wbk' ) . '<br />' . __( 'Note, it\'s necessary to include #name placeholder into the value of this option.', 'wbk' ),
            'wbk_interface_settings_section',
            '#name'
        );
        // date format backend
        wbk_opt()->add_option(
            'wbk_date_format_backend',
            'select',
            __( 'Date format (backend)', 'wbk' ),
            __( 'Used in the "Appointments" page controls. d - day, m - month, y - year.', 'wbk' ),
            'wbk_interface_settings_section',
            'm/d/y',
            array(
            'm/d/y' => __( 'm/d/y', 'wbk' ),
            'y/m/d' => __( 'y/m/d', 'wbk' ),
            'y-m-d' => __( 'y-m-d', 'wbk' ),
            'd.m.y' => __( 'd.m.y', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_appointments_table_columns',
            'select_multiple',
            __( 'Columns of Appointment table', 'wbk' ),
            '',
            'wbk_interface_settings_section',
            WBK_Model_Utils::get_bookings_page_columns(),
            WBK_Model_Utils::get_bookings_page_columns()
        );
        wbk_opt()->add_option(
            'wbk_date_format_time_slot_schedule',
            'select',
            __( 'Format of timeslots in the Schedule page', 'wbk' ),
            '',
            'wbk_interface_settings_section',
            'start',
            array(
            'start'     => __( 'Start', 'wbk' ),
            'start-end' => __( 'Start - End', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_backend_select_services_onload',
            'select',
            __( 'Select all services on the Appointments page automatically', 'wbk' ),
            '',
            'wbk_interface_settings_section',
            'disabled',
            array(
            'enabled'  => __( 'Enabled', 'wbk' ),
            'disabled' => __( 'Disabled', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_custom_fields_columns',
            'text',
            __( 'Custom field columns', 'wbk' ),
            __( 'Comma-separated list of IDs of custom fields.', 'wbk' ) . '<br />' . __( 'Use square brackets to set column headers.', 'wbk' ) . '<br />' . __( 'Example: custom-field1[Title 1],custom-field2[Title 2]', 'wbk' ),
            'wbk_interface_settings_section',
            ''
        );
        wbk_opt()->add_option(
            'wbk_backend_show_category_name',
            'select',
            __( 'Show category name after service name', 'wbk' ),
            '',
            'wbk_interface_settings_section',
            'disabled',
            array(
            'enabled'  => __( 'Enabled', 'wbk' ),
            'disabled' => __( 'Disabled', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_filter_default_days_number',
            'text',
            __( 'The default number of days to display on the appointment page', 'wbk' ),
            __( 'Tip: set lower value for better performance.', 'wbk' ),
            'wbk_interface_settings_section',
            '14'
        );
        wbk_opt()->add_option(
            'wbk_backend_add_buttons_in_editor',
            'checkbox',
            __( 'Add shortcode buttons to the editor', 'wbk' ),
            '',
            'wbk_interface_settings_section',
            'true'
        );
        wbk_opt()->add_option(
            'wbk_disable_nice_select',
            'checkbox',
            __( 'Disable Nice Select', 'wbk' ),
            '',
            'wbk_interface_settings_section',
            ''
        );
        // backend interface section init end  ********************************************************************
        date_default_timezone_set( 'UTC' );
        $all_times = array();
        $format = WBK_Date_Time_Utils::getTimeFormat();
        for ( $i = 0 ;  $i < 86400 ;  $i += 600 ) {
            $all_times[$i] = wp_date( $format, $i, new DateTimeZone( date_default_timezone_get() ) );
        }
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        $time_description = __( 'Current local time:', 'wbk' ) . ' ' . date( $format );
        date_default_timezone_set( 'UTC' );
        do_action( 'wbk_options_after' );
    }
    
    public function wbk_settings_section_callback()
    {
    }
    
    // init styles and scripts
    public function enqueueScripts()
    {
        
        if ( isset( $_GET['page'] ) && $_GET['page'] == 'wbk-options' ) {
            wp_deregister_script( 'chosen' );
            wp_enqueue_script( 'jquery-plugin', plugins_url( 'js/jquery.plugin.js', dirname( __FILE__ ) ), array( 'jquery' ) );
            wp_enqueue_script( 'multidate-picker', plugins_url( 'js/jquery.datepick.min.js', dirname( __FILE__ ) ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker' ) );
            wp_enqueue_script(
                'wbk-options',
                plugins_url( 'js/wbk-options.js', dirname( __FILE__ ) ),
                array(
                'jquery',
                'jquery-ui-core',
                'jquery-ui-dialog',
                'jquery-ui-tabs'
            ),
                '4.2.0'
            );
            wp_enqueue_script( 'wbk-minicolors', plugins_url( 'js/jquery.minicolors.min.js', dirname( __FILE__ ) ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-dialog' ) );
            wp_enqueue_style( 'wbk-datepicker-css', plugins_url( 'css/jquery.datepick.css', dirname( __FILE__ ) ) );
            wp_enqueue_script( 'slf-chosen', plugins_url( 'js/chosen.jquery.min.js', dirname( __FILE__ ) ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker' ) );
            wp_enqueue_style( 'slf-chosen-css', plugins_url( 'css/chosen.min.css', dirname( __FILE__ ) ) );
        }
    
    }
    
    // general settings section callback
    public function wbk_general_settings_section_callback( $arg )
    {
    }
    
    // schedule settings section callback
    public function wbk_schedule_settings_section_callback( $arg )
    {
    }
    
    // email settings section callback
    public function wbk_email_settings_section_callback( $arg )
    {
    }
    
    // appearance  settings section callback
    public function wbk_mode_settings_section_callback( $arg )
    {
    }
    
    // appearance  settings section callback
    public function wbk_translation_settings_section_callback( $arg )
    {
    }
    
    // backend interface settings section callback
    public function wbk_backend_interface_settings_section_callback( $arg )
    {
    }
    
    // paypal settings section callback
    public function wbk_paypal_settings_section_callback( $arg )
    {
    }
    
    // stripe settings section callback
    public function wbk_stripe_settings_section_callback( $arg )
    {
    }
    
    // google
    // google calendar settings section callback
    public function wbk_gg_calendar_settings_section_callback( $arg )
    {
    }
    
    // woo settings section callback
    public function wbk_woo_settings_section_callback( $arg )
    {
    }
    
    // woo settings section callback
    public function wbk_zoom_settings_section_callback( $arg )
    {
    }
    
    // appointments settings section callback
    public function wbk_appointments_settings_section_callback( $arg )
    {
    }
    
    public function render_email_admin_paymentrecvd_status()
    {
        $value = get_option( 'wbk_email_admin_paymentrcvd_status' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_admin_paymentrcvd_status" name="wbk_email_admin_paymentrcvd_status" value="true" >';
        $html .= '<label for="wbk_email_admin_paymentrcvd_status">' . __( 'Check if you\'d like to send administrator an email when payment received', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    public function validate_email_admin_paymentrcvd_status( $input )
    {
        
        if ( $input != 'true' && $input != '' ) {
            $input = '';
            add_settings_error(
                'wbk_email_admin_paymentrcvd_status',
                'wbk_email_admin_paymentrcvd_status_error',
                __( 'Email status updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    public function render_email_admin_paymentrcvd_subject()
    {
        $value = get_option( 'wbk_email_admin_paymentrcvd_subject' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_admin_paymentrcvd_subject" name="wbk_email_admin_paymentrcvd_subject" value="' . $value . '" >';
        $html .= '<p class="description"><a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'wbk' ) . '</a></p>';
        echo  $html ;
    }
    
    public function validate_email_admin_paymentrcvd_subject( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    public function render_email_admin_paymentrcvd_message()
    {
        $value = get_option( 'wbk_email_admin_paymentrcvd_message' );
        $mcesettings = array();
        $mcesettings['valid_elements'] = '*[*]';
        $mcesettings['extended_valid_elements'] = '*[*]';
        $args = array(
            'media_buttons' => false,
            'editor_height' => 300,
            'tinymce'       => $mcesettings,
        );
        echo  '<a class="button wbk_email_editor_toggle">' . __( 'Toggle editor', 'wbk' ) . '</a><div class="wbk_email_editor_wrap" style="display:none;">' ;
        wp_editor( $value, 'wbk_email_admin_paymentrcvd_message', $args );
        echo  '</div>' ;
    }
    
    public function validate_email_admin_paymentrcvd_message( $input )
    {
        return $input;
    }
    
    public function render_email_customer_paymentrecvd_status()
    {
        $value = get_option( 'wbk_email_customer_paymentrcvd_status' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_customer_paymentrcvd_status" name="wbk_email_customer_paymentrcvd_status" value="true" >';
        $html .= '<label for="wbk_email_customer_paymentrcvd_status">' . __( 'Check if you\'d like to send customer an email when payment received', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    public function validate_email_customer_paymentrcvd_status( $input )
    {
        
        if ( $input != 'true' && $input != '' ) {
            $input = '';
            add_settings_error(
                'wbk_email_customer_paymentrcvd_status',
                'wbk_email_customer_paymentrcvd_status_error',
                __( 'Email status updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    public function render_email_customer_paymentrcvd_subject()
    {
        $value = get_option( 'wbk_email_customer_paymentrcvd_subject' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_customer_paymentrcvd_subject" name="wbk_email_customer_paymentrcvd_subject" value="' . $value . '" >';
        $html .= '<p class="description"><a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'wbk' ) . '</a></p>';
        echo  $html ;
    }
    
    public function validate_email_customer_paymentrcvd_subject( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    public function render_email_customer_arrived_subject()
    {
        $value = get_option( 'wbk_email_customer_arrived_subject' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_customer_arrived_subject" name="wbk_email_customer_arrived_subject" value="' . $value . '" >';
        $html .= '<p class="description"><a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'wbk' ) . '</a></p>';
        echo  $html ;
    }
    
    public function validate_email_customer_arrived_subject( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    public function render_email_customer_arrived_message()
    {
        $value = get_option( 'wbk_email_customer_arrived_message' );
        $args = array(
            'media_buttons' => false,
            'editor_height' => 300,
        );
        echo  '<a class="button wbk_email_editor_toggle">' . __( 'Toggle editor', 'wbk' ) . '</a><div class="wbk_email_editor_wrap" style="display:none;">' ;
        wp_editor( $value, 'wbk_email_customer_arrived_message', $args );
        echo  '</div>' ;
    }
    
    public function validate_email_customer_arrived_message( $input )
    {
        return $input;
    }
    
    public function render_email_customer_paymentrcvd_message()
    {
        $value = get_option( 'wbk_email_customer_paymentrcvd_message' );
        $args = array(
            'media_buttons' => false,
            'editor_height' => 300,
        );
        echo  '<a class="button wbk_email_editor_toggle">' . __( 'Toggle editor', 'wbk' ) . '</a><div class="wbk_email_editor_wrap" style="display:none;">' ;
        wp_editor( $value, 'wbk_email_customer_paymentrcvd_message', $args );
        echo  '</div>' ;
    }
    
    public function validate_email_customer_paymentrcvd_message( $input )
    {
        return $input;
    }
    
    // render email to customer (on booking)
    public function render_email_customer_book_status()
    {
        $value = get_option( 'wbk_email_customer_book_status' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_customer_book_status" name="wbk_email_customer_book_status" value="true" >';
        $html .= '<label for="wbk_email_customer_book_status">' . __( 'Check if you\'d like to send customer an email on booking', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    // validate email to customer (on booking)
    public function validate_email_customer_book_status( $input )
    {
        
        if ( $input != 'true' && $input != '' ) {
            $input = '';
            add_settings_error(
                'wbk_email_customer_book_status',
                'wbk_email_customer_book_status_error',
                __( 'Email status updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    // render email to customer (on approve)
    public function render_email_customer_approve_status()
    {
        $value = get_option( 'wbk_email_customer_approve_status' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_customer_approve_status" name="wbk_email_customer_approve_status" value="true" >';
        $html .= '<label for="wbk_email_customer_approve_status">' . __( 'Check if you\'d like to send customer an email on approval', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    // validate email to customer (on approve)
    public function validate_email_customer_approve_status( $input )
    {
        
        if ( $input != 'true' && $input != '' ) {
            $input = '';
            add_settings_error(
                'wbk_email_customer_approve_status',
                'wbk_email_customer_approve_status_error',
                __( 'Email (on approval) status updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    //****** start admin cacnelation block
    // render email to admin (on cancel)
    public function render_email_admin_appointment_cancel_status()
    {
        $value = get_option( 'wbk_email_adimn_appointment_cancel_status' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_adimn_appointment_cancel_status" name="wbk_email_adimn_appointment_cancel_status" value="true" >';
        $html .= '<label for="wbk_email_adimn_appointment_cancel_status">' . __( 'Check if you\'d like to send administrator an email on appointment cancellation', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    // validate email to customer (on cancel)
    public function validate_email_admin_appointment_cancel_status( $input )
    {
        
        if ( $input != 'true' && $input != '' ) {
            $input = '';
            add_settings_error(
                'wbk_email_adimn_appointment_cancel_status',
                'wbk_email_adimn_appointment_cancel_status',
                __( 'Email (on cancellation) status updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    // render admin email subject (on cancellation)
    public function render_email_admin_appointment_cancel_subject()
    {
        $value = get_option( 'wbk_email_adimn_appointment_cancel_subject', __( 'Appointment canceled', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_adimn_appointment_cancel_subject" name="wbk_email_adimn_appointment_cancel_subject" value="' . $value . '" >';
        $html .= '<p class="description"><a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'wbk' ) . '</a></p>';
        echo  $html ;
    }
    
    // validate admin email subject (on cancellation)
    public function validate_email_admin_appointment_cancel_subject( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render email to admin message (cancellation)
    public function render_email_admin_appointment_cancel_message()
    {
        $value = get_option( 'wbk_email_adimn_appointment_cancel_message', '<p>#customer_name canceled the appointment with #service_name on #appointment_day at #appointment_time</p>' );
        $args = array(
            'media_buttons' => false,
            'editor_height' => 300,
        );
        echo  '<a class="button wbk_email_editor_toggle">' . __( 'Toggle editor', 'wbk' ) . '</a><div class="wbk_email_editor_wrap" style="display:none;">' ;
        wp_editor( $value, 'wbk_email_adimn_appointment_cancel_message', $args );
        echo  '</div>' ;
    }
    
    // validate email toadmin message (cancellation)
    public function validate_email_admin_appointment_cancel_message( $input )
    {
        return $input;
    }
    
    //****** end admin cacnelation block
    //****** start customer cacnelation block
    // render email to customer (on cancel)
    public function render_email_customer_appointment_cancel_status()
    {
        $value = get_option( 'wbk_email_customer_appointment_cancel_status' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_customer_appointment_cancel_status" name="wbk_email_customer_appointment_cancel_status" value="true" >';
        $html .= '<label for="wbk_email_customer_appointment_cancel_status">' . __( 'Check if you\'d like to send customer an email on appointment cancellation', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    // validate email to customer (on cancel)
    public function validate_email_customer_appointment_cancel_status( $input )
    {
        
        if ( $input != 'true' && $input != '' ) {
            $input = '';
            add_settings_error(
                'wbk_email_customer_appointment_cancel_status',
                'wbk_email_customer_appointment_cancel_status',
                __( 'Email (on cancellation) status updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    // render customer email subject (on cancellation)
    public function render_email_customer_appointment_cancel_subject()
    {
        $value = get_option( 'wbk_email_customer_appointment_cancel_subject', __( 'Your appointment canceled', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_customer_appointment_cancel_subject" name="wbk_email_customer_appointment_cancel_subject" value="' . $value . '" >';
        $html .= '<p class="description"><a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'wbk' ) . '</a></p>';
        echo  $html ;
    }
    
    // validate customer email subject (on cancellation)
    public function validate_email_customer_appointment_cancel_subject( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render email to customer message (cancellation)
    public function render_email_customer_appointment_cancel_message()
    {
        $value = get_option( 'wbk_email_customer_appointment_cancel_message', '<p>Your appointment with #service_name on #appointment_day at #appointment_time has been canceled</p>' );
        $args = array(
            'media_buttons' => false,
            'editor_height' => 300,
        );
        echo  '<a class="button wbk_email_editor_toggle">' . __( 'Toggle editor', 'wbk' ) . '</a><div class="wbk_email_editor_wrap" style="display:none;">' ;
        wp_editor( $value, 'wbk_email_customer_appointment_cancel_message', $args );
        echo  '</div>' ;
    }
    
    public function validate_email_customer_appointment_cancel_message( $input )
    {
        return $input;
    }
    
    public function render_email_customer_bycustomer_appointment_cancel_message()
    {
        $value = get_option( 'wbk_email_customer_bycustomer_appointment_cancel_message', '<p>Your appointment with #service_name on #appointment_day at #appointment_time has been canceled</p>' );
        $args = array(
            'media_buttons' => false,
            'editor_height' => 300,
        );
        echo  '<a class="button wbk_email_editor_toggle">' . __( 'Toggle editor', 'wbk' ) . '</a><div class="wbk_email_editor_wrap" style="display:none;">' ;
        wp_editor( $value, 'wbk_email_customer_bycustomer_appointment_cancel_message', $args );
        echo  '</div>' ;
    }
    
    public function validate_email_customer_bycustomer_appointment_cancel_message( $input )
    {
        return $input;
    }
    
    public function render_email_customer_book_message()
    {
        $value = get_option( 'wbk_email_customer_book_message' );
        $args = array(
            'media_buttons' => false,
            'editor_height' => 300,
        );
        echo  '<a class="button wbk_email_editor_toggle">' . __( 'Toggle editor', 'wbk' ) . '</a><div class="wbk_email_editor_wrap" style="display:none;">' ;
        wp_editor( $value, 'wbk_email_customer_book_message', $args );
        echo  '</div>' ;
    }
    
    // validate email to customer message
    public function validate_email_customer_book_message( $input )
    {
        return $input;
    }
    
    public function render_email_customer_manual_book_message()
    {
        $value = get_option( 'wbk_email_customer_manual_book_message' );
        $args = array(
            'media_buttons' => false,
            'editor_height' => 300,
        );
        echo  '<a class="button wbk_email_editor_toggle">' . __( 'Toggle editor', 'wbk' ) . '</a><div class="wbk_email_editor_wrap" style="display:none;">' ;
        wp_editor( $value, 'wbk_email_customer_manual_book_message', $args );
        echo  '</div>' ;
    }
    
    public function validate_email_customer_manual_book_message( $input )
    {
        return $input;
    }
    
    // render email to customer message (approve)
    public function render_email_customer_approve_message()
    {
        $value = get_option( 'wbk_email_customer_approve_message', '<p>Your appointment bookin on #appointment_day at #appointment_time has been approved.</p>' );
        $args = array(
            'media_buttons' => false,
            'editor_height' => 300,
        );
        echo  '<a class="button wbk_email_editor_toggle">' . __( 'Toggle editor', 'wbk' ) . '</a><div class="wbk_email_editor_wrap" style="display:none;">' ;
        wp_editor( $value, 'wbk_email_customer_approve_message', $args );
        echo  '</div>' ;
    }
    
    // validate email to customer message (approve)
    public function validate_email_customer_approve_message( $input )
    {
        return $input;
    }
    
    // render customer email subject (on booking)
    public function render_email_customer_book_subject()
    {
        $value = get_option( 'wbk_email_customer_book_subject' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_customer_book_subject" name="wbk_email_customer_book_subject" value="' . $value . '" >';
        $html .= '<p class="description"><a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'wbk' ) . '</a></p>';
        echo  $html ;
    }
    
    // validate email to customer message (on booking)
    public function validate_email_customer_book_subject( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    public function render_email_customer_manual_book_subject()
    {
        $value = get_option( 'wbk_email_customer_manual_book_subject' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_customer_manual_book_subject" name="wbk_email_customer_manual_book_subject" value="' . $value . '" >';
        $html .= '<p class="description"><a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'wbk' ) . '</a></p>';
        echo  $html ;
    }
    
    // validate email to customer message (on booking)
    public function validate_email_customer_manual_book_subject( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render customer email subject (on approve)
    public function render_email_customer_approve_subject()
    {
        $value = get_option( 'wbk_email_customer_approve_subject', __( 'Your booking has been approved', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_customer_approve_subject" name="wbk_email_customer_approve_subject" value="' . $value . '" >';
        $html .= '<p class="description"><a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/#subjectplaceholders">' . __( 'List of available placeholders', 'wbk' ) . '</a></p>';
        echo  $html ;
    }
    
    // validate email to customer message (on approve)
    public function validate_email_customer_approve_subject( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render email to secondary
    public function render_email_secondary_book_status()
    {
        $value = get_option( 'wbk_email_secondary_book_status', '' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_secondary_book_status" name="wbk_email_secondary_book_status" value="true" >';
        $html .= '<label for="wbk_email_secondary_book_status">' . __( 'Check if you\'d like to send an email to a customers from the group', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    // validate email to secondary
    public function validate_email_secondary_book_status( $input )
    {
        
        if ( $input != 'true' && $input != '' ) {
            $input = '';
            add_settings_error(
                'wbk_email_secondary_book_status',
                'wbk_email_secondary_book_status_error',
                __( 'Email status updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    // render email to secondary message
    public function render_email_secondary_book_message()
    {
        $value = get_option( 'wbk_email_secondary_book_message' );
        $args = array(
            'media_buttons' => false,
            'editor_height' => 300,
        );
        echo  '<a class="button wbk_email_editor_toggle">' . __( 'Toggle editor', 'wbk' ) . '</a><div class="wbk_email_editor_wrap" style="display:none;">' ;
        wp_editor( $value, 'wbk_email_secondary_book_message', $args );
        echo  '</div>' ;
    }
    
    // validate email to secondary message
    public function validate_email_secondary_book_message( $input )
    {
        return $input;
    }
    
    // render secondary email subject
    public function render_email_secondary_book_subject()
    {
        $value = get_option( 'wbk_email_secondary_book_subject' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_secondary_book_subject" name="wbk_email_secondary_book_subject" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate email to secondary message
    public function validate_email_secondary_book_subject( $input )
    {
        $input = sanitize_text_field( $input );
        
        if ( !WBK_Validator::checkStringSize( $input, 1, 100 ) ) {
        } else {
            return $input;
        }
    
    }
    
    // render admin email subject
    public function render_email_admin_book_subject()
    {
        $value = get_option( 'wbk_email_admin_book_subject' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_admin_book_subject" name="wbk_email_admin_book_subject" value="' . $value . '" >';
        $html .= '<p class="description"><a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'wbk' ) . '</a></p>';
        echo  $html ;
    }
    
    // validate email to admin message
    public function validate_email_admin_book_subject( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render admin daily subject
    public function render_email_admin_daily_subject()
    {
        $value = get_option( 'wbk_email_admin_daily_subject' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_admin_daily_subject" name="wbk_email_admin_daily_subject" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate email to admin message
    public function validate_email_admin_daily_subject( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render customer daily subject
    public function render_email_customer_daily_subject()
    {
        $value = get_option( 'wbk_email_customer_daily_subject' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_customer_daily_subject" name="wbk_email_customer_daily_subject" value="' . $value . '" >';
        $html .= '<p class="description"><a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'wbk' ) . '</a></p>';
        echo  $html ;
    }
    
    // validate email to customer message
    public function validate_email_customer_daily_subject( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render invoice subject //  todo
    public function render_email_customer_invoice_subject()
    {
        $value = get_option( 'wbk_email_customer_invoice_subject', __( 'Invoice', 'wbk' ) );
        $html = '<input type="text" id="wbk_email_customer_invoice_subject" name="wbk_email_customer_invoice_subject" value="' . $value . '" >';
        $html .= '<p class="description"><a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'wbk' ) . '</a></p>';
        echo  $html ;
    }
    
    // validate invoice subject
    public function validate_email_customer_invoice_subject( $input )
    {
        return $input;
    }
    
    // render email to admin
    public function render_email_admin_book_status()
    {
        $value = get_option( 'wbk_email_admin_book_status' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_admin_book_status" name="wbk_email_admin_book_status" value="true" >';
        $html .= '<label for="wbk_email_admin_book_status">' . __( 'Check if you\'d like to send administrator an email', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    // validate email to admin
    public function validate_email_admin_book_status( $input )
    {
        
        if ( $input != 'true' && $input != '' ) {
            $input = '';
            add_settings_error(
                'wbk_email_admin_book_status',
                'wbk_email_admin_book_status_error',
                __( 'Email status updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    /* START: ICal Generation   */
    public function render_email_admin_book_status_generate_ical()
    {
        $value = get_option( 'wbk_email_admin_book_status_generate_ical' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_admin_book_status_generate_ical" name="wbk_email_admin_book_status_generate_ical" value="true" >';
        $html .= '<label for="wbk_email_admin_book_status_generate_ical">' . __( 'Check if you\'d like to attach iCal file to the notification', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    public function validate_email_admin_book_status_generate_ical( $input )
    {
        
        if ( $input != 'true' && $input != '' ) {
            $input = '';
            add_settings_error(
                'wbk_email_admin_book_status_generate_ical',
                'wbk_email_admin_book_status_generate_ical_error',
                __( 'Attach iCal file to the notification status updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    public function render_email_customer_book_status_generate_ical()
    {
        $value = get_option( 'wbk_email_customer_book_status_generate_ical' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_customer_book_status_generate_ical" name="wbk_email_customer_book_status_generate_ical" value="true" >';
        $html .= '<label for="wbk_email_customer_book_status_generate_ical">' . __( 'Check if you\'d like to attach iCal file to the notification', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    public function validate_email_customer_book_status_generate_ical( $input )
    {
        
        if ( $input != 'true' && $input != '' ) {
            $input = '';
            add_settings_error(
                'wbk_email_customer_book_status_generate_ical',
                'wbk_email_customer_book_status_generate_ical_error',
                __( 'Attach iCal file to the notification status updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    /* END: ICal Generation   */
    // render email to admin daily
    public function render_email_admin_daily_status()
    {
        $value = get_option( 'wbk_email_admin_daily_status' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_admin_daily_status" name="wbk_email_admin_daily_status" value="true" >';
        $html .= '<label for="wbk_email_admin_daily_status">' . __( 'Check if you\'d like to send reminders to administrator', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    // validate email to admin
    public function validate_email_admin_daily_status( $input )
    {
        
        if ( $input != 'true' && $input != '' ) {
            $input = '';
            add_settings_error(
                'wbk_email_admin_daily_status',
                'wbk_email_admin_daily_status_error',
                __( 'Email status updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    public function render_email_customer_arrived_status()
    {
        $value = get_option( 'wbk_email_customer_arrived_status' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_customer_arrived_status" name="wbk_email_customer_arrived_status" value="true" >';
        $html .= '<label for="wbk_email_customer_arrived_status">' . __( 'Check if you\'d like to send notification to customer when status is changed to Arrived', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    public function validate_email_customer_arrived_status( $input )
    {
        
        if ( $input != 'true' && $input != '' ) {
            $input = '';
            add_settings_error(
                'wbk_email_customer_arrived_status',
                'wbk_email_customer_arrived_status_error',
                __( 'Email status updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    // render email to customer daily
    public function render_email_customer_daily_status()
    {
        $value = get_option( 'wbk_email_customer_daily_status' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_customer_daily_status" name="wbk_email_customer_daily_status" value="true" >';
        $html .= '<label for="wbk_email_customer_daily_status">' . __( 'Check if you\'d like to send reminders to customer', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    // validate email to customer
    public function validate_email_customer_daily_status( $input )
    {
        
        if ( $input != 'true' && $input != '' ) {
            $input = '';
            add_settings_error(
                'wbk_email_customer_daily_status',
                'wbk_email_customer_daily_status_error',
                __( 'Email status updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    // render email to admin message
    public function render_email_admin_book_message()
    {
        $value = get_option( 'wbk_email_admin_book_message' );
        $args = array(
            'media_buttons' => false,
            'editor_height' => 300,
        );
        echo  '<a class="button wbk_email_editor_toggle">' . __( 'Toggle editor', 'wbk' ) . '</a><div class="wbk_email_editor_wrap" style="display:none;">' ;
        wp_editor( $value, 'wbk_email_admin_book_message', $args );
        echo  '</div>' ;
    }
    
    // validate email to admin message
    public function validate_email_admin_book_message( $input )
    {
        return $input;
    }
    
    // render email to admin  daily message
    public function render_email_admin_daily_message()
    {
        $value = get_option( 'wbk_email_admin_daily_message' );
        $args = array(
            'media_buttons' => false,
            'editor_height' => 300,
        );
        echo  '<a class="button wbk_email_editor_toggle">' . __( 'Toggle editor', 'wbk' ) . '</a><div class="wbk_email_editor_wrap" style="display:none;">' ;
        wp_editor( $value, 'wbk_email_admin_daily_message', $args );
        echo  '</div>' ;
    }
    
    // validate email to admin daily message
    public function validate_email_admin_daily_message( $input )
    {
        return $input;
    }
    
    // render email to customer  daily message
    public function render_email_customer_daily_message()
    {
        $value = get_option( 'wbk_email_customer_daily_message' );
        $args = array(
            'media_buttons' => false,
            'editor_height' => 300,
        );
        echo  '<a class="button wbk_email_editor_toggle">' . __( 'Toggle editor', 'wbk' ) . '</a><div class="wbk_email_editor_wrap" style="display:none;">' ;
        wp_editor( $value, 'wbk_email_customer_daily_message', $args );
        echo  '</div>' ;
    }
    
    // validate email to customer daily message
    public function validate_email_customer_daily_message( $input )
    {
        return $input;
    }
    
    // render show locked as booked
    public function render_show_locked_as_booked()
    {
        $value = get_option( 'wbk_show_locked_as_booked', 'no' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_show_locked_as_booked" name="wbk_show_locked_as_booked">
				    <option ' . selected( $value, 'yes', false ) . ' value="yes">' . __( 'Yes', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'no', false ) . ' value="no">' . __( 'No', 'wbk' ) . '</option>
   				 </select>';
        echo  $html ;
    }
    
    public function render_attachment_file_types()
    {
        $value = get_option( 'wbk_attachment_file_types', 'image/*' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_attachment_file_types" name="wbk_attachment_file_types" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Example: file_extension. A file extension starting with the STOP character, e.g: .gif, .jpg, .png, .doc', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'Example: audio/* all sound files are accepted.', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'Example: video/* all video files are accepted.', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'Example: image/* all image files are accepted.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    public function validate_attachment_file_types( $input )
    {
        return sanitize_text_field( $input );
    }

}
function wbk_default_editor( $param )
{
    return 'tinymce';
}
