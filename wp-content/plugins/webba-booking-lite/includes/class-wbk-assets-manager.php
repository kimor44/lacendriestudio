<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Class WBK_Assets_Manager is used to load CSS and JS files depended on detecting of backend or frontend
 */
class WBK_Assets_Manager
{
    protected  $css ;
    protected  $js ;
    public function __construct( $css, $js )
    {
        $this->css = $css;
        $this->js = $js;
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 20 );
        add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ), 20 );
    }
    
    public function admin_enqueue_scripts()
    {
        $admin_pages = array(
            'wbk-services',
            'wbk-email-templates',
            'wbk-service-categories',
            'wbk-appointments',
            'wbk-coupons',
            'wbk-gg-calendars',
            'wbk-pricing-rules'
        );
        if ( isset( $_GET['page'] ) && in_array( $_GET['page'], $admin_pages ) ) {
            Plugion()->initialize_assets();
        }
        foreach ( $this->css as $item ) {
            if ( $item[0] == 'backend' ) {
                if ( isset( $_GET['page'] ) || $item[1] == 'all' ) {
                    if ( $item[1] == 'all' || in_array( $_GET['page'], $item[1] ) ) {
                        wp_enqueue_style(
                            $item[2],
                            $item[3],
                            $item[4],
                            $item[5]
                        );
                    }
                }
            }
        }
        foreach ( $this->js as $item ) {
            if ( $item[0] == 'backend' ) {
                if ( isset( $_GET['page'] ) || $item[1] == 'all' ) {
                    if ( $item[1] == 'all' || isset( $_GET['page'] ) && is_array( $item[1] ) && in_array( $_GET['page'], $item[1] ) ) {
                        wp_enqueue_script(
                            $item[2],
                            $item[3],
                            $item[4],
                            $item[5]
                        );
                    }
                }
            }
        }
        $translation_array = array(
            'disable_nice_select' => get_option( 'wbk_disable_nice_select', '' ),
            'export_csv'          => __( 'Export to CSV', 'wbk' ),
            'start_export'        => __( 'Start export', 'wbk' ),
            'please_wait'         => __( 'Please wait...', 'wbk' ),
            'duplication_warning' => __( 'Duplication of bookings is highly discouraged because it can lead to errors in determining free timeslots.', 'wbk' ),
            'wbkb_nonce'          => wp_create_nonce( 'wbkb_nonce' ),
            'ajaxurl'             => admin_url( 'admin-ajax.php' ),
            'nofication_icon'     => WP_WEBBA_BOOKING__PLUGIN_URL . '/public/images/notification-icon.png',
        );
        wp_localize_script( 'wbk-dashboard-script', 'wbk_dashboardl10n', $translation_array );
        // remove in V5
        
        if ( isset( $_GET['page'] ) && $_GET['page'] == 'wbk-schedule' ) {
            wp_deregister_script( 'chosen' );
            $translation_array = array(
                'addappointment' => __( 'Add appointment', 'wbk' ),
                'add'            => __( 'Add', 'wbk' ),
                'close'          => __( 'Close', 'wbk' ),
                'appointment'    => __( 'Appointment', 'wbk' ),
                'delete'         => __( 'Delete', 'wbk' ),
                'shownextweek'   => __( 'Show next week', 'wbk' ),
                'phonemask'      => get_option( 'wbk_phone_mask', 'enabled' ),
                'phoneformat'    => get_option( 'wbk_phone_format', '(999) 999-9999' ),
                'confirm'        => __( 'Confirm', 'wbk' ),
                'phone_required' => get_option( 'wbk_phone_required', '3' ),
                'wbkb_nonce'     => wp_create_nonce( 'wbkb_nonce' ),
                'ajaxurl'        => admin_url( 'admin-ajax.php' ),
            );
            wp_localize_script( 'wbk-schedule', 'wbkl10n', $translation_array );
        }
    
    }
    
    public function wp_enqueue_scripts()
    {
        
        if ( get_option( 'wbk_load_js_in_footer', '' ) == 'true' ) {
            $in_footer = true;
        } else {
            $in_footer = false;
        }
        
        $has_shortcode = false;
        if ( $this->has_shortcode( 'webba_booking' ) || $this->has_shortcode( 'webba_email_landing' ) || $this->has_shortcode( 'webba_multi_service_booking' ) ) {
            $has_shortcode = true;
        }
        if ( isset( $_GET['ct_builder'] ) || !$has_shortcode ) {
            return;
        }
        wp_enqueue_script( 'jquery-effects-fade' );
        foreach ( $this->css as $item ) {
            if ( $item[0] == 'frontend' ) {
                wp_enqueue_style(
                    $item[2],
                    $item[3],
                    $item[4],
                    $item[5]
                );
            }
        }
        foreach ( $this->js as $item ) {
            if ( $item[0] == 'frontend' ) {
                wp_enqueue_script(
                    $item[2],
                    $item[3],
                    $item[4],
                    $item[5],
                    $in_footer
                );
            }
        }
        $start_of_week = get_option( 'wbk_start_of_week', 'monday' );
        
        if ( $start_of_week == 'monday' ) {
            $start_of_week = true;
        } else {
            $start_of_week = false;
        }
        
        $select_slots_label = WBK_Validator::kses( get_option( 'wbk_slots_label', '' ) );
        $thanks_message = WBK_Validator::kses( get_option( 'wbk_book_thanks_message', '' ) );
        $select_date_placeholder = WBK_Validator::alfa_numeric( get_option( 'wbk_date_input_placeholder', '' ) );
        $booked_text = WBK_Validator::kses( get_option( 'wbk_booked_text', '' ) );
        $checkout_label = WBK_Validator::kses( get_option( 'wbk_checkout_button_text', '' ) );
        $checkout_label = str_replace( '#selected_count', '<span class="wbk_multi_selected_count"></span>', $checkout_label );
        $checkout_label = str_replace( '#total_count', '<span class="wbk_multi_total_count"></span>', $checkout_label );
        $checkout_label = str_replace( '#low_limit', '<span class="wbk_multi_low_limit"></span>', $checkout_label );
        $continuous_appointments = get_option( 'wbk_appointments_continuous' );
        
        if ( is_array( $continuous_appointments ) ) {
            $continuous_appointments = implode( ',', $continuous_appointments );
        } else {
            $continuous_appointments = '';
        }
        
        $translation_array = array(
            'mode'                      => get_option( 'wbk_mode', 'extended' ),
            'phonemask'                 => get_option( 'wbk_phone_mask', 'enabled' ),
            'phoneformat'               => get_option( 'wbk_phone_format', '(999) 999-9999' ),
            'ajaxurl'                   => admin_url( 'admin-ajax.php' ),
            'selectdatestart'           => WBK_Validator::kses( get_option( 'wbk_date_extended_label', '' ) ),
            'selectdatestartbasic'      => WBK_Validator::kses( get_option( 'wbk_date_basic_label', '' ) ),
            'selecttime'                => WBK_Validator::kses( get_option( 'wbk_slots_label', '' ) ),
            'selectdate'                => $select_date_placeholder,
            'thanksforbooking'          => $thanks_message,
            'january'                   => __( 'January', 'wbk' ),
            'february'                  => __( 'February', 'wbk' ),
            'march'                     => __( 'March', 'wbk' ),
            'april'                     => __( 'April', 'wbk' ),
            'may'                       => __( 'May', 'wbk' ),
            'june'                      => __( 'June', 'wbk' ),
            'july'                      => __( 'July', 'wbk' ),
            'august'                    => __( 'August', 'wbk' ),
            'september'                 => __( 'September', 'wbk' ),
            'october'                   => __( 'October', 'wbk' ),
            'november'                  => __( 'November', 'wbk' ),
            'december'                  => __( 'December', 'wbk' ),
            'jan'                       => __( 'Jan', 'wbk' ),
            'feb'                       => __( 'Feb', 'wbk' ),
            'mar'                       => __( 'Mar', 'wbk' ),
            'apr'                       => __( 'Apr', 'wbk' ),
            'mays'                      => __( 'May', 'wbk' ),
            'jun'                       => __( 'Jun', 'wbk' ),
            'jul'                       => __( 'Jul', 'wbk' ),
            'aug'                       => __( 'Aug', 'wbk' ),
            'sep'                       => __( 'Sep', 'wbk' ),
            'oct'                       => __( 'Oct', 'wbk' ),
            'nov'                       => __( 'Nov', 'wbk' ),
            'dec'                       => __( 'Dec', 'wbk' ),
            'sunday'                    => __( 'Sunday', 'wbk' ),
            'monday'                    => __( 'Monday', 'wbk' ),
            'tuesday'                   => __( 'Tuesday', 'wbk' ),
            'wednesday'                 => __( 'Wednesday', 'wbk' ),
            'thursday'                  => __( 'Thursday', 'wbk' ),
            'friday'                    => __( 'Friday', 'wbk' ),
            'saturday'                  => __( 'Saturday', 'wbk' ),
            'sun'                       => __( 'Sun', 'wbk' ),
            'mon'                       => __( 'Mon', 'wbk' ),
            'tue'                       => __( 'Tue', 'wbk' ),
            'wed'                       => __( 'Wed', 'wbk' ),
            'thu'                       => __( 'Thu', 'wbk' ),
            'fri'                       => __( 'Fri', 'wbk' ),
            'sat'                       => __( 'Sat', 'wbk' ),
            'today'                     => __( 'Today', 'wbk' ),
            'clear'                     => __( 'Clear', 'wbk' ),
            'close'                     => __( 'Close', 'wbk' ),
            'startofweek'               => $start_of_week,
            'nextmonth'                 => __( 'Next month', 'wbk' ),
            'prevmonth'                 => __( 'Previous  month', 'wbk' ),
            'hide_form'                 => get_option( 'wbk_hide_from_on_booking', 'disabled' ),
            'booked_text'               => $booked_text,
            'show_booked'               => get_option( 'wbk_show_booked_slots', 'disabled' ),
            'multi_booking'             => get_option( 'wbk_multi_booking', 'disabled' ),
            'checkout'                  => $checkout_label,
            'multi_limit'               => get_option( 'wbk_multi_booking_max', '' ),
            'multi_limit_default'       => get_option( 'wbk_multi_booking_max', '' ),
            'phone_required'            => get_option( 'wbk_phone_required', '3' ),
            'show_desc'                 => get_option( 'wbk_show_service_description', 'disabled' ),
            'date_input'                => get_option( 'wbk_date_input', 'popup' ),
            'allow_attachment'          => get_option( 'wbk_allow_attachemnt', 'no' ),
            'stripe_public_key'         => get_option( 'wbk_stripe_publishable_key', '' ),
            'override_stripe_error'     => get_option( 'wbk_stripe_card_input_mode', 'no' ),
            'stripe_card_error_message' => get_option( 'wbk_stripe_card_element_error_message', 'incorrect input' ),
            'something_wrong'           => __( 'Something went wrong, please try again.', 'wbk' ),
            'time_slot_booked'          => __( 'Time slot(s) already booked.', 'wbk' ),
            'pp_redirect'               => get_option( 'wbk_paypal_auto_redirect', 'disabled' ),
            'show_prev_booking'         => get_option( 'wbk_show_details_prev_booking', 'disabled' ),
            'scroll_container'          => get_option( 'wbk_scroll_container', 'html, body' ),
            'continious_appointments'   => $continuous_appointments,
            'show_suitable_hours'       => get_option( 'wbk_show_suitable_hours', 'yes' ),
            'stripe_redirect_url'       => get_option( 'wbk_stripe_redirect_url', '' ),
            'stripe_mob_size'           => get_option( 'wbk_stripe_mob_font_size', '' ),
            'auto_add_to_cart'          => get_option( 'wbk_woo_auto_add_to_cart', 'disabled' ),
            'range_selection'           => get_option( 'wbk_range_selection', 'disabled' ),
            'picker_format'             => WBK_Db_Utils::convertDateFormatForPicker(),
            'scroll_value'              => get_option( 'wbk_scroll_value', '120' ),
            'field_required'            => get_option( 'wbk_validation_error_message', '' ),
            'error_status_scroll_value' => '0',
            'limit_per_email_message'   => get_option( 'wbk_limit_by_email_reached_message', __( 'You have reached your booking limit.', 'wbk' ) ),
            'stripe_hide_postal'        => get_option( 'wbk_stripe_hide_postal', 'false' ),
            'jquery_no_conflict'        => get_option( 'wbk_jquery_nc', 'disabled' ),
            'no_available_dates'        => get_option( 'wbk_no_dates_label', __( 'No available dates message', 'wbk' ) ),
            'auto_select_first_date'    => get_option( 'wbk_auto_select_first_date', 'disabled' ),
            'book_text_timeslot'        => WBK_Validator::alfa_numeric( get_option( 'wbk_book_text_timeslot', __( 'Book', 'wbk' ) ) ),
            'deselect_text_timeslot'    => get_option( 'wbk_deselect_text_timeslot', '' ),
            'wbkf_nonce'                => wp_create_nonce( 'wbkf_nonce' ),
        );
        $sanitized_array = array();
        foreach ( $translation_array as $key => $value ) {
            $sanitized_array[$key] = WBK_Validator::alfa_numeric( $value );
        }
        wp_localize_script( 'wbk-frontend', 'wbkl10n', $sanitized_array );
    }
    
    private function has_shortcode( $shortcode = '' )
    {
        if ( get_option( 'wbk_check_short_code', 'disabled' ) == 'disabled' ) {
            return true;
        }
        $post_to_check = get_post( get_the_ID() );
        if ( !$post_to_check ) {
            return false;
        }
        $found = false;
        if ( !$shortcode ) {
            return $found;
        }
        if ( stripos( $post_to_check->post_content, '[' . $shortcode ) !== false ) {
            $found = true;
        }
        return $found;
    }

}