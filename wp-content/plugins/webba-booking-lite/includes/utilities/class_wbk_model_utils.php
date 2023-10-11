<?php

// check if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class WBK_Model_Utils
{
    /**
     * get ids of all services
     * @return array ids of services
     */
    public static function get_service_ids( $restricted = false )
    {
        global  $wpdb ;
        $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( get_option( 'wbk_db_prefix', '' ) . 'wbk_services' ) );
        if ( !$wpdb->get_var( $query ) == get_option( 'wbk_db_prefix', '' ) . 'wbk_services' ) {
            return array();
        }
        $sql = "SELECT id,users FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_services ";
        $sql = apply_filters( 'wbk_get_service_ids', $sql );
        $order_type = get_option( 'wbk_order_service_by', 'a-z' );
        if ( $order_type == 'a-z' ) {
            $sql .= " order by name asc ";
        }
        if ( $order_type == 'priority' ) {
            $sql .= " order by priority desc";
        }
        if ( $order_type == 'priority_a' ) {
            $sql .= " order by priority asc";
        }
        $rows = $wpdb->get_results( $sql, ARRAY_A );
        $result = array();
        foreach ( $rows as $item ) {
            
            if ( $restricted ) {
                $user = wp_get_current_user();
                
                if ( in_array( 'administrator', $user->roles, true ) || is_multisite() && !is_super_admin() ) {
                    $result[] = $item['id'];
                } else {
                    $users = json_decode( $item['users'] );
                    if ( is_array( $users ) ) {
                        if ( in_array( get_current_user_id(), $users ) ) {
                            $result[] = $item['id'];
                        }
                    }
                }
            
            } else {
                $result[] = $item['id'];
            }
        
        }
        return $result;
    }
    
    /**
     * get pairs of service id - names
     * @return array array of id-name pair
     */
    public static function get_services( $restricted = false )
    {
        global  $wpdb ;
        $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( get_option( 'wbk_db_prefix', '' ) . 'wbk_services' ) );
        if ( !$wpdb->get_var( $query ) == get_option( 'wbk_db_prefix', '' ) . 'wbk_services' ) {
            return array();
        }
        $sql = "SELECT id,name,users FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_services ";
        $sql = apply_filters( 'wbk_get_services', $sql );
        $order_type = get_option( 'wbk_order_service_by', 'a-z' );
        if ( $order_type == 'a-z' ) {
            $sql .= " order by name asc ";
        }
        if ( $order_type == 'priority' ) {
            $sql .= " order by priority desc";
        }
        if ( $order_type == 'priority_a' ) {
            $sql .= " order by priority asc";
        }
        $rows = $wpdb->get_results( $sql, ARRAY_A );
        $result = array();
        foreach ( $rows as $item ) {
            
            if ( $restricted ) {
                $user = wp_get_current_user();
                
                if ( in_array( 'administrator', $user->roles, true ) || is_multisite() && !is_super_admin() ) {
                    $result[$item['id']] = $item['name'];
                } else {
                    $users = json_decode( $item['users'] );
                    if ( is_array( $users ) ) {
                        if ( in_array( get_current_user_id(), $users ) ) {
                            $result[$item['id']] = $item['name'];
                        }
                    }
                }
            
            } else {
                $result[$item['id']] = $item['name'];
            }
        
        }
        return $result;
    }
    
    /**
     * get pairs of email template id - name
     * @return array  of id-name pair
     */
    public static function get_email_templates()
    {
        global  $wpdb ;
        $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( get_option( 'wbk_db_prefix', '' ) . 'wbk_email_templates' ) );
        if ( !$wpdb->get_var( $query ) == get_option( 'wbk_db_prefix', '' ) . 'wbk_email_templates' ) {
            return array();
        }
        $sql = "SELECT id,name FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_email_templates ";
        $rows = $wpdb->get_results( $sql, ARRAY_A );
        $result_converted = array();
        foreach ( $rows as $item ) {
            $result_converted[$item['id']] = $item['name'];
        }
        return $result_converted;
    }
    
    /**
     * get pairs of google calendars id - name
     * @return array  of id-name pair
     */
    public static function get_google_calendars()
    {
        global  $wpdb ;
        $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( get_option( 'wbk_db_prefix', '' ) . 'wbk_gg_calendars' ) );
        if ( !$wpdb->get_var( $query ) == get_option( 'wbk_db_prefix', '' ) . 'wbk_gg_calendars' ) {
            return array();
        }
        $sql = "SELECT id,name FROM  " . get_option( 'wbk_db_prefix', '' ) . "wbk_gg_calendars ";
        $result = $wpdb->get_results( $sql, ARRAY_A );
        $result_converted = array();
        foreach ( $result as $item ) {
            $result_converted[$item['id']] = $item['name'];
        }
        return $result_converted;
    }
    
    /**
     * list of available modes of google calendars
     * @return array key -title pair array
     */
    public static function get_gg_calendar_modes()
    {
        $result = array(
            'One-way'        => __( 'One-way (export)', 'webba-booking-lite' ),
            'One-way-import' => __( 'One-way (import)', 'webba-booking-lite' ),
            'Two-ways'       => __( 'Two-ways', 'webba-booking-lite' ),
        );
        return $result;
    }
    
    /**
     * extract custom fields value from extr-data json_decode
     * @param  string $data extra-data
     * @param  int $id custom fiekd id
     * @return string value of the custom field or null if not set
     */
    public static function extract_custom_field_value( $data, $id )
    {
        if ( $data == '' ) {
            return null;
        }
        $data = json_decode( $data );
        if ( $data === NULL ) {
            return null;
        }
        foreach ( $data as $item ) {
            if ( !is_array( $item ) ) {
                continue;
            }
            if ( count( $item ) != 3 ) {
                continue;
            }
            if ( trim( $item[0] ) == trim( $id ) ) {
                return $item[2];
            }
        }
        return null;
    }
    
    /**
     * get array of available columns on the
     * Appoointments page
     * @param boolean $keys_only return only keys
     * @return array
     */
    public static function get_appointment_columns( $keys_only = false )
    {
        if ( $keys_only ) {
            return array(
                'service_id',
                'day',
                'time',
                'quantity',
                'name',
                'email',
                'description',
                'extra',
                'status',
                'payment_method',
                'moment_price',
                'coupon'
            );
        }
        return array(
            'service_id'     => __( 'Service', 'webba-booking-lite' ),
            'created_on'     => __( 'Created on', 'webba-booking-lite' ),
            'day'            => __( 'Date', 'webba-booking-lite' ),
            'time'           => __( 'Time', 'webba-booking-lite' ),
            'quantity'       => __( 'Places booked', 'webba-booking-lite' ),
            'name'           => __( 'Customer name', 'webba-booking-lite' ),
            'email'          => __( 'Customer email', 'webba-booking-lite' ),
            'phone'          => __( 'Phone', 'webba-booking-lite' ),
            'description'    => __( 'Customer comment', 'webba-booking-lite' ),
            'extra'          => __( 'Custom fields', 'webba-booking-lite' ),
            'status'         => __( 'Status', 'webba-booking-lite' ),
            'payment_method' => __( 'Payment method', 'webba-booking-lite' ),
            'moment_price'   => __( 'Price', 'webba-booking-lite' ),
            'coupon'         => __( 'Coupon', 'webba-booking-lite' ),
            'ip'             => __( 'User IP', 'webba-booking-lite' ),
        );
    }
    
    /**
     * get available appointment statuses
     * @return array array
     */
    static function get_booking_status_list()
    {
        $result = array(
            'pending'                 => __( 'Awaiting approval', 'webba-booking-lite' ),
            'approved'                => __( 'Approved', 'webba-booking-lite' ),
            'paid'                    => __( 'Paid (awaiting approval)', 'webba-booking-lite' ),
            'paid_approved'           => __( 'Paid (approved)', 'webba-booking-lite' ),
            'arrived'                 => __( 'Arrived', 'webba-booking-lite' ),
            'woocommerce'             => __( 'Managed by WooCommerce', 'webba-booking-lite' ),
            'added_by_admin_not_paid' => __( 'Added by the administrator (not paid)', 'webba-booking-lite' ),
            'added_by_admin_paid'     => __( 'Added by the administrator (paid)', 'webba-booking-lite' ),
        );
        return $result;
    }
    
    /**
     * get services in category
     * @param  int $category_id id of the category
     * @return array ids of the services
     */
    static function get_services_in_category( $category_id, $pair = false )
    {
        global  $wpdb ;
        $list = $wpdb->get_var( $wpdb->prepare( "SELECT category_list FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_service_categories WHERE id = %d", $category_id ) );
        if ( $list == '' ) {
            return FALSE;
        }
        
        if ( !$pair ) {
            return json_decode( $list );
        } else {
            $ids = json_decode( $list );
            $result = array();
            foreach ( $ids as $id ) {
                $service = new WBK_Service( $id );
                if ( !$service->is_loaded() ) {
                    continue;
                }
                $result[$id] = $service->get_name();
            }
            return $result;
        }
    
    }
    
    /**
     * get services with the same category
     * as given service
     * @param  int $service_id service id
     * @return array ids of services
     */
    public static function get_services_with_same_category( $service_id )
    {
        global  $wpdb ;
        $result = array();
        $categories = self::get_service_categories();
        foreach ( $categories as $key => $value ) {
            $services = self::get_services_in_category( $key );
            if ( is_array( $services ) && in_array( $service_id, $services ) ) {
                foreach ( $services as $current_service ) {
                    if ( $current_service != $service_id ) {
                        $result[] = $current_service;
                    }
                }
            }
        }
        $result = array_unique( $result );
        return $result;
    }
    
    /**
     * get list of service categories
     * @return array ids of categories
     */
    public static function get_service_categories()
    {
        global  $wpdb ;
        $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( get_option( 'wbk_db_prefix', '' ) . 'wbk_service_categories' ) );
        if ( !$wpdb->get_var( $query ) == get_option( 'wbk_db_prefix', '' ) . 'wbk_service_categories' ) {
            return array();
        }
        $sql = "SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_service_categories";
        $sql = apply_filters( 'wbk_get_categories', $sql );
        $categories = $wpdb->get_col( $sql );
        $result = array();
        foreach ( $categories as $category_id ) {
            $name = $wpdb->get_var( $wpdb->prepare( " SELECT name FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_service_categories WHERE id = %d", $category_id ) );
            $result[$category_id] = $name;
        }
        return $result;
    }
    
    /**
     * get IDds of service catgegories
     * @return array array of the service categories
     */
    public static function get_service_category_ids()
    {
        global  $wpdb ;
        $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( get_option( 'wbk_db_prefix', '' ) . 'wbk_service_categories' ) );
        if ( !$wpdb->get_var( $query ) == get_option( 'wbk_db_prefix', '' ) . 'wbk_service_categories' ) {
            return array();
        }
        $sql = "SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_service_categories ";
        $rows = $wpdb->get_results( $sql, ARRAY_A );
        $result = array();
        foreach ( $rows as $item ) {
            $result[] = $item['id'];
        }
        return $result;
    }
    
    /**
     * get booking ids by day and array of services
     * @param  int $day timestamp of the day
     * @param  array $service_ids array of service ids
     * @return array id of the bookings
     */
    public static function get_booking_ids_by_day_service( $day, $service_id )
    {
        global  $wpdb ;
        $result = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments where day=%d and service_id=%d ", $day, $service_id ) );
        return $result;
    }
    
    public static function get_booking_ids_by_day( $day )
    {
        global  $wpdb ;
        $result = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments where day=%d", $day ) );
        return $result;
    }
    
    public static function get_booking_ids_by_day_service_email( $day, $service_id, $email )
    {
        global  $wpdb ;
        $result = $wpdb->get_col( $wpdb->prepare(
            "SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments where day=%d and service_id=%d and email=%s order by time ASC",
            $day,
            $service_id,
            $email
        ) );
        return $result;
    }
    
    public static function get_booking_ids_by_time_service_email( $time, $service_id, $email )
    {
        global  $wpdb ;
        $result = $wpdb->get_col( $wpdb->prepare(
            "SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments where time=%d and service_id=%d and email=%s order by time ASC",
            $time,
            $service_id,
            $email
        ) );
        return $result;
    }
    
    public static function get_booking_ids_by_service_email( $service_id, $email )
    {
        global  $wpdb ;
        $result = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments where service_id=%d and email=%s order by time ASC", $service_id, $email ) );
        return $result;
    }
    
    public static function get_all_quantity_intersecting_range( $start, $end )
    {
        $service_ids = self::get_service_ids();
        $day = strtotime( date( 'Y-m-d', $start ) . ' 00:00:00' );
        $total_quantity = 0;
        foreach ( $service_ids as $service_id ) {
            $booking_ids = self::get_booking_ids_by_day_service( $day, $service_id );
            foreach ( $booking_ids as $booking_id ) {
                $booking = new WBK_Booking( $booking_id );
                if ( WBK_Time_Math_Utils::check_range_intersect(
                    $start,
                    $end,
                    $booking->get_start(),
                    $booking->get_end()
                ) ) {
                    $total_quantity += $booking->get_quantity();
                }
            }
        }
        return $total_quantity;
    }
    
    public static function get_booking_ids_by_range_service( $start, $end, $service_id )
    {
        global  $wpdb ;
        $result = $wpdb->get_col( $wpdb->prepare(
            "SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments where time >= %d AND time < %d AND service_id=%d ",
            $start,
            $end,
            $service_id
        ) );
        return $result;
    }
    
    /**
     * Get the IDs of bookings created at a specific date range
     * @param int $start An unix timestamp integer indica5tng the earliest second (inclusive)
     * @param int $end   An unix timestamp integer indica5tng the latest second (not inclusive)
     * 
     * @return array The booking IDs reletaed to the specific bookings, sorted by creation time from
     *               from earliest to latest. empty if no matching results.
     */
    public static function get_booking_by_date_range( $start, $end )
    {
        global  $wpdb ;
        $result = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments where created_on >= %d AND created_on < %d ORDER BY created_on", $start, $end ) );
        return $result;
    }
    
    public static function get_booking_ids_by_email( $email )
    {
        global  $wpdb ;
        $result = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments where email = %s order by time desc", $email ) );
        return $result;
    }
    
    public static function get_booking_ids_for_today_by_service( $service_id )
    {
        global  $wpdb ;
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        $today = strtotime( 'today midnight' );
        $result = $wpdb->get_col( $wpdb->prepare( " SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE service_id=%d AND day=%d  ORDER BY time ", $service_id, $today ) );
        date_default_timezone_set( 'UTC' );
        return $result;
    }
    
    public static function get_booking_ids_for_today_not_arrived()
    {
        global  $wpdb ;
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        $today = strtotime( 'today midnight' );
        $result = $wpdb->get_col( $wpdb->prepare( " SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE status <> 'arrived' AND day=%d  ORDER BY time ", $today ) );
        date_default_timezone_set( 'UTC' );
        return $result;
    }
    
    public static function get_booking_ids_for_last_week_not_arrived()
    {
        global  $wpdb ;
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        $time_in_past = time() - 86400 * 7;
        $result = $wpdb->get_col( $wpdb->prepare( " SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE status <> 'arrived' AND time > %d ORDER BY time ", $time_in_past ) );
        date_default_timezone_set( 'UTC' );
        return $result;
    }
    
    public static function auto_set_arrived_satus()
    {
        if ( !is_numeric( get_option( 'wbk_set_arrived_after', '' ) ) ) {
            return;
        }
        $ids = self::get_booking_ids_for_last_week_not_arrived();
        foreach ( $ids as $id ) {
            $booking = new WBK_Booking( $id );
            if ( !$booking->is_loaded() ) {
                continue;
            }
            $update_interval = get_option( 'wbk_set_arrived_after', '' ) * 60;
            
            if ( time() > $booking->get_end() + $update_interval ) {
                self::set_booking_status( $booking->get_id(), 'arrived' );
                $ids = self::get_booking_ids_by_day_service_email( $booking->get_day(), $booking->get_service(), $booking->get( 'email' ) );
                
                if ( count( $ids ) > 0 && $booking->get_id() == end( $ids ) ) {
                    $noifications = new WBK_Email_Notifications( $booking->get_service(), $booking->get_id() );
                    $noifications->sendSingleArrived();
                }
            
            }
        
        }
    }
    
    public static function get_coupons()
    {
        global  $wpdb ;
        $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( get_option( 'wbk_db_prefix', '' ) . 'wbk_coupons' ) );
        if ( !$wpdb->get_var( $query ) == get_option( 'wbk_db_prefix', '' ) . 'wbk_coupons' ) {
            return array();
        }
        $sql = "SELECT id,name FROM  " . get_option( 'wbk_db_prefix', '' ) . "wbk_coupons ";
        $result = $wpdb->get_results( $sql, ARRAY_A );
        $result_converted = array();
        foreach ( $result as $item ) {
            $result_converted[$item['id']] = $item['name'];
        }
        return $result_converted;
    }
    
    public static function get_pricing_rules()
    {
        global  $wpdb ;
        $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( get_option( 'wbk_db_prefix', '' ) . 'wbk_pricing_rules' ) );
        if ( !$wpdb->get_var( $query ) == get_option( 'wbk_db_prefix', '' ) . 'wbk_pricing_rules' ) {
            return;
        }
        $sql = "SELECT id,name FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_pricing_rules";
        $result = $wpdb->get_results( $sql, ARRAY_A );
        $result_converted = array();
        foreach ( $result as $item ) {
            $result_converted[$item['id']] = $item['name'];
        }
        return $result_converted;
    }
    
    public static function set_amount_for_booking( $booking_id, $amount, $details = '' )
    {
        global  $wpdb ;
        $result = $wpdb->update(
            get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
            array(
            'moment_price'   => $amount,
            'amount_details' => $details,
        ),
            array(
            'id' => $booking_id,
        ),
            array( '%s', '%s' ),
            array( '%d' )
        );
    }
    
    public static function set_booking_canceled_by( $booking_id, $value )
    {
        global  $wpdb ;
        $wpdb->update(
            get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
            array(
            'canceled_by' => $value,
        ),
            array(
            'id' => $booking_id,
        ),
            array( '%s' ),
            array( '%d' )
        );
    }
    
    public static function set_booking_status( $booking_id, $status )
    {
        global  $wpdb ;
        $booking = new WBK_Booking( $booking_id );
        $prev_status = $booking->get( 'status' );
        $wpdb->update(
            get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
            array(
            'status' => $status,
        ),
            array(
            'id' => $booking_id,
        ),
            array( '%s' ),
            array( '%d' )
        );
        $wpdb->update(
            get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
            array(
            'prev_status' => $prev_status,
        ),
            array(
            'id' => $booking_id,
        ),
            array( '%s' ),
            array( '%d' )
        );
    }
    
    public static function set_booking_end( $booking_id )
    {
        global  $wpdb ;
        $booking = new WBK_Booking( $booking_id );
        if ( $booking->get_name() == '' ) {
            return;
        }
        $result = $wpdb->update(
            get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
            array(
            'end' => $booking->get_end(),
        ),
            array(
            'id' => $booking_id,
        ),
            array( '%d' ),
            array( '%d' )
        );
    }
    
    static function get_booking_ids_by_service_and_time( $service_id, $time )
    {
        global  $wpdb ;
        $booking_ids = $wpdb->get_col( $wpdb->prepare( "\r\n\t\t\tSELECT      id\r\n\t\t\tFROM        " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments\r\n \t\t\tWHERE       service_id = %d\r\n\t\t\tAND \t\ttime  = %d\r\n\t\t\t", $service_id, $time ) );
        return $booking_ids;
    }
    
    static function get_bookings_page_columns( $keys_only = false )
    {
        if ( $keys_only ) {
            return array(
                'service_id',
                'day',
                'time',
                'quantity',
                'name',
                'email',
                'description',
                'extra',
                'status',
                'payment_method',
                'moment_price',
                'coupon'
            );
        }
        return array(
            'service_id'     => __( 'Service', 'webba-booking-lite' ),
            'created_on'     => __( 'Created on', 'webba-booking-lite' ),
            'day'            => __( 'Date', 'webba-booking-lite' ),
            'time'           => __( 'Time', 'webba-booking-lite' ),
            'quantity'       => __( 'Places booked', 'webba-booking-lite' ),
            'name'           => __( 'Customer name', 'webba-booking-lite' ),
            'email'          => __( 'Customer email', 'webba-booking-lite' ),
            'phone'          => __( 'Phone', 'webba-booking-lite' ),
            'description'    => __( 'Customer comment', 'webba-booking-lite' ),
            'extra'          => __( 'Custom fields', 'webba-booking-lite' ),
            'status'         => __( 'Status', 'webba-booking-lite' ),
            'payment_method' => __( 'Payment method', 'webba-booking-lite' ),
            'moment_price'   => __( 'Price', 'webba-booking-lite' ),
            'coupon'         => __( 'Coupon', 'webba-booking-lite' ),
            'ip'             => __( 'User IP', 'webba-booking-lite' ),
        );
    }
    
    static function get_custom_fields_list()
    {
        $ids = get_option( 'wbk_custom_fields_columns', '' );
        $result = array();
        
        if ( $ids != '' ) {
            $ids = explode( ',', $ids );
            $html = '';
            foreach ( $ids as $id ) {
                $col_title = '';
                preg_match( "/\\[[^\\]]*\\]/", $id, $matches );
                if ( is_array( $matches ) && count( $matches ) > 0 ) {
                    $col_title = rtrim( ltrim( $matches[0], '[' ), ']' );
                }
                $id = explode( '[', $id );
                $id = $id[0];
                if ( $col_title == '' ) {
                    $col_title = $id;
                }
                $result[$id] = $col_title;
            }
        }
        
        return $result;
    }
    
    static function get_service_availability_in_range( $service_id, $number_of_days, $mode = 'classic' )
    {
        // analog of getServiceAbiliy
        $service = new WBK_Service( $service_id );
        if ( !$service->is_loaded() ) {
            return array();
        }
        // init service schedulle
        $sp = new WBK_Schedule_Processor();
        $sp->load_data();
        $date_format = WBK_Format_Utils::get_date_format();
        $prepare_time = round( $service->get_prepare_time() / 1440 );
        $arr_disabled = array();
        $arr_enabled = array();
        $day_to_render = strtotime( 'today midnight' );
        $last_day = $day_to_render + 86400 * $number_of_days;
        $google_events = array();
        
        if ( !is_null( $service->get_availability_range() ) && is_array( $service->get_availability_range() ) && count( $service->get_availability_range() ) == 2 ) {
            $availability_range = $service->get_availability_range();
            $limit_start = strtotime( trim( $availability_range[0] ) );
            $limit_end = strtotime( trim( $availability_range[1] ) );
        }
        
        
        if ( $mode == 'dropdown' ) {
            $added_dates = 0;
            $added_dates_limit = $number_of_days;
            $number_of_days = 1000000;
        }
        
        for ( $i = 1 ;  $i <= $number_of_days ;  $i++ ) {
            
            if ( $mode == 'dropdown' && $added_dates >= $added_dates_limit ) {
                $number_of_days = 1000001;
                continue;
            }
            
            // check if current day is inside the limit
            if ( !is_null( $service->get_availability_range() ) && is_array( $service->get_availability_range() ) && count( $service->get_availability_range() ) == 2 ) {
                
                if ( $day_to_render < $limit_start || $day_to_render > $limit_end ) {
                    $day_to_render = strtotime( 'tomorrow', $day_to_render );
                    continue;
                }
            
            }
            
            if ( get_option( 'wbk_disallow_after', '0' ) != '0' ) {
                $limit2 = time() + get_option( 'wbk_disallow_after' ) * 60 * 60;
                
                if ( $day_to_render > $limit2 ) {
                    $day_to_render = strtotime( 'tomorrow', $day_to_render );
                    continue;
                }
            
            }
            
            
            if ( $i <= $prepare_time ) {
                $day_to_render = strtotime( 'tomorrow', $day_to_render );
                continue;
            }
            
            $day_status = $sp->get_day_status( $day_to_render, $service_id );
            
            if ( $day_status == 0 || $day_status == 2 ) {
                
                if ( $mode == 'dropdown' && $day_status == 2 ) {
                    $added_dates++;
                    $arr_enabled[] = $day_to_render . '-HM-' . wp_date( $date_format, $day_to_render, new DateTimeZone( date_default_timezone_get() ) ) . ' ' . get_option( 'wbk_daily_limit_reached_message', __( 'Daily booking limit is reached, please select another date', 'webba-booking-lite' ) ) . '-HM-wbk_dropdown_limit_reached';
                }
                
                $day_to_render = strtotime( 'tomorrow', $day_to_render );
                continue;
            } else {
                
                if ( get_option( 'wbk_disable_day_on_all_booked', 'disabled' ) == 'enabled' || get_option( 'wbk_disable_day_on_all_booked', 'disabled' ) == 'enabled_plus' ) {
                    
                    if ( get_option( 'wbk_disable_day_on_all_booked', 'disabled' ) == 'enabled' ) {
                        $calculate_availability = false;
                    } elseif ( get_option( 'wbk_disable_day_on_all_booked', 'disabled' ) == 'enabled_plus' ) {
                        $calculate_availability = true;
                    }
                    
                    $sp->get_time_slots_by_day(
                        $day_to_render,
                        $service_id,
                        array(
                        'calculate_availability' => $calculate_availability,
                        'calculate_night_hours'  => false,
                        'skip_gg_calendar'       => true,
                        null,
                        null,
                    ),
                        null,
                        false
                    );
                    
                    if ( !$sp->has_free_time_slots() ) {
                        
                        if ( $mode == 'dropdown' ) {
                            $added_dates++;
                            $arr_enabled[] = $day_to_render . '-HM-' . wp_date( $date_format, $day_to_render, new DateTimeZone( date_default_timezone_get() ) ) . ' ' . get_option( 'wbk_daily_limit_reached_message', __( 'Daily booking limit is reached, please select another date', 'webba-booking-lite' ) ) . '-HM-wbk_dropdown_limit_reached';
                        }
                        
                        $day_to_render = strtotime( 'tomorrow', $day_to_render );
                        continue;
                    }
                
                }
            
            }
            
            $valid = apply_filters( 'wbk_check_date_availability', true, $day_to_render );
            if ( !$valid ) {
                continue;
            }
            
            if ( $mode == 'dropdown' ) {
                $added_dates++;
                $arr_enabled[] = $day_to_render . '-HM-' . wp_date( $date_format, $day_to_render, new DateTimeZone( date_default_timezone_get() ) ) . '-HM-wbk_dropdown_regular_item';
            } else {
                $arr_disabled[] = date( 'Y', $day_to_render ) . ',' . intval( date( 'n', $day_to_render ) - 1 ) . ',' . date( 'j', $day_to_render );
            }
            
            $day_to_render = strtotime( 'tomorrow', $day_to_render );
        }
        
        if ( $mode == 'dropdown' ) {
            return $arr_enabled;
        } else {
            return $arr_disabled;
        }
    
    }
    
    static function get_quantity_by_range_sevices( $start, $end, $services )
    {
        global  $wpdb ;
        $quantity = $wpdb->get_var( $wpdb->prepare( "SELECT SUM(quantity) FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments" . " WHERE " . "service_id IN (" . implode( ',', $services ) . ") AND " . "( ( time = %d ) OR " . "( time > %d AND time < %d ) OR " . "( time > %d AND time <= %d ) OR " . "( time >= %d AND time <= %d ) ) ", array(
            $start,
            $start,
            $end,
            $start,
            $end,
            $start,
            $end
        ) ) );
        return $quantity;
    }
    
    static function get_service_limits( $service_id )
    {
        $service = new WBK_Service( $service_id );
        $result = '';
        $range = $service->get_availability_range();
        
        if ( !is_array( $range ) ) {
            $limit_value = '';
        } else {
            
            if ( count( $range ) == 1 ) {
                $limit_value = '';
            } else {
                
                if ( $range[0] == $range[1] ) {
                    $limit_value = date( 'Y,n,j', strtotime( trim( $range[0] ) ) ) . '-' . date( 'Y,n,j', strtotime( trim( $range[1] ) ) );
                } else {
                    $limit_value = date( 'Y,n,j', strtotime( trim( $range[0] ) ) ) . '-' . date( 'Y,n,j', strtotime( trim( $range[1] ) ) );
                }
            
            }
        
        }
        
        $result .= $limit_value;
        return $result;
    }
    
    public static function get_service_weekly_availability( $service_id )
    {
        $sp = new WBK_Schedule_Processor();
        $sp->load_unlocked_days();
        $result = array();
        for ( $i = 1 ;  $i <= 7 ;  $i++ ) {
            if ( !$sp->is_working_day( $i, $service_id ) && !$sp->is_unlockced_has_dow( $i, $service_id ) ) {
                
                if ( get_option( 'wbk_start_of_week', 'monday' ) == 'monday' ) {
                    $result[] = $i;
                } else {
                    $term = $i + 1;
                    if ( $term == 8 ) {
                        $term = 1;
                    }
                    $result[] = $term;
                }
            
            }
        }
        return $result;
    }
    
    static function get_category_names_by_service( $service_id )
    {
        $categories = self::get_service_categories();
        $result = array();
        foreach ( $categories as $key => $value ) {
            $category = new WBK_Service_Category( $key );
            $services = json_decode( $category->get( 'category_list' ) );
            if ( is_array( $services ) && in_array( $service_id, $services ) ) {
                $result[] = $value;
            }
        }
        if ( count( $result ) > 0 ) {
            return implode( ', ', $result );
        }
        return '';
    }
    
    static function copy_booking_to_cancelled( $booking_id, $cancelled_by )
    {
        global  $wpdb ;
        if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
            $cancelled_by .= ' (' . $_SERVER['REMOTE_ADDR'] . ')';
        }
        $booking_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments  WHERE id = %d", $booking_id ), ARRAY_A );
        $wpdb->insert( get_option( 'wbk_db_prefix', '' ) . 'wbk_cancelled_appointments', array(
            'id_cancelled'         => $booking_id,
            'cancelled_by'         => $cancelled_by,
            'name'                 => $booking_data['name'],
            'email'                => $booking_data['email'],
            'phone'                => $booking_data['phone'],
            'description'          => $booking_data['description'],
            'extra'                => $booking_data['extra'],
            'attachment'           => $booking_data['attachment'],
            'service_id'           => $booking_data['service_id'],
            'time'                 => $booking_data['time'],
            'day'                  => $booking_data['day'],
            'duration'             => $booking_data['duration'],
            'created_on'           => $booking_data['created_on'],
            'quantity'             => $booking_data['quantity'],
            'status'               => $booking_data['status'],
            'payment_id'           => $booking_data['payment_id'],
            'token'                => 'not_used',
            'payment_cancel_token' => 'not_used',
            'admin_token'          => 'not_used',
            'expiration_time'      => '0',
            'time_offset'          => '0',
            'gg_event_id'          => $booking_data['gg_event_id'],
            'coupon'               => '0',
            'payment_method'       => $booking_data['payment_method'],
            'lang'                 => $booking_data['lang'],
            'moment_price'         => $booking_data['moment_price'],
        ), array(
            '%d',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d',
            '%d',
            '%s',
            '%d',
            '%s',
            '%s',
            '%s',
            '%s'
        ) );
        do_action( 'wbk_table_after_add', [ $wpdb->insert_id, get_option( 'wbk_db_prefix', '' ) . 'wbk_cancelled_appointments' ] );
    }
    
    static function get_booking_ids_by_group_token( $token )
    {
        global  $wpdb ;
        $arr_tokens = explode( '-', $token );
        $result = array();
        if ( count( $arr_tokens ) > 60 ) {
            return $result;
        }
        foreach ( $arr_tokens as $token ) {
            $booking_id = $wpdb->get_var( $wpdb->prepare( " SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE token = %s ", $token ) );
            
            if ( $booking_id == null ) {
                continue;
            } else {
                $result[] = $booking_id;
            }
        
        }
        return $result;
    }
    
    static function get_booking_ids_by_group_admin_token( $token )
    {
        global  $wpdb ;
        $arr_tokens = explode( '-', $token );
        $result = array();
        if ( count( $arr_tokens ) > 60 ) {
            return $result;
        }
        foreach ( $arr_tokens as $token ) {
            $booking_id = $wpdb->get_var( $wpdb->prepare( " SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE admin_token = %s ", $token ) );
            
            if ( $booking_id == null ) {
                continue;
            } else {
                $result[] = $booking_id;
            }
        
        }
        return $result;
    }
    
    static function get_payment_methods_for_bookings_intersected( $booking_ids )
    {
        $services_ids = array();
        foreach ( $booking_ids as $booking_id ) {
            $booking = new WBK_Booking( $booking_id );
            if ( !$booking->is_loaded() ) {
                continue;
            }
            $services_ids[] = $booking->get_service();
        }
        $db_prefix = get_option( 'wbk_db_prefix', '' );
        $payment_methods_result = array();
        foreach ( $services_ids as $service_id ) {
            $service = new WBK_Service( $service_id );
            $payment_methods_service = json_decode( $service->get( 'payment_methods' ) );
            if ( !is_null( $payment_methods_service ) && is_array( $payment_methods_service ) ) {
                
                if ( count( $payment_methods_result ) == 0 ) {
                    $payment_methods_result = $payment_methods_service;
                } else {
                    $payment_methods_result = array_intersect( $payment_methods_result, $payment_methods_service );
                }
            
            }
        }
        return $payment_methods_result;
    }
    
    static function get_payment_methods_for_bookings( $booking_ids )
    {
        $services_ids = array();
        foreach ( $booking_ids as $booking_id ) {
            $booking = new WBK_Booking( $booking_id );
            if ( !$booking->is_loaded() ) {
                continue;
            }
            $services_ids[] = $booking->get_service();
        }
        $db_prefix = get_option( 'wbk_db_prefix', '' );
        $payment_methods_all = Plugion()->tables->get_element_at( $db_prefix . 'wbk_services' )->fields->get_element_at( 'service_payment_methods' )->get_extra_data()['items'];
        $payment_methods_allowed = array();
        foreach ( $payment_methods_all as $payment_method => $payment_method_name ) {
            $allowed = true;
            foreach ( $services_ids as $service_id ) {
                $service = new WBK_Service( $service_id );
                $payment_method_service = json_decode( $service->get( 'payment_methods' ) );
                if ( !is_null( $payment_method_service ) && is_array( $payment_method_service ) ) {
                    if ( in_array( $payment_method, $payment_method_service ) ) {
                        continue;
                    }
                }
                $allowed = false;
            }
            if ( $allowed ) {
                $payment_methods_allowed[] = $payment_method;
            }
        }
        return $payment_methods_allowed;
    }
    
    static function get_booking_ids_by_payment_id( $payment_id )
    {
        global  $wpdb ;
        $ids = $wpdb->get_col( $wpdb->prepare( 'select id from ' . get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments where payment_id = %s', $payment_id ) );
        return $ids;
    }
    
    public static function get_payment_fields()
    {
        return array(
            'name'        => __( 'Cardholder name', 'webba-booking-lite' ),
            'city'        => __( 'City', 'webba-booking-lite' ),
            'country'     => __( 'Country', 'webba-booking-lite' ),
            'line1'       => __( 'Address line 1', 'webba-booking-lite' ),
            'line2'       => __( 'Address line 1', 'webba-booking-lite' ),
            'postal_code' => __( 'Postal code', 'webba-booking-lite' ),
            'state'       => __( 'State', 'webba-booking-lite' ),
        );
    }
    
    public static function delete_booking( $booking_id )
    {
        global  $wpdb ;
        $db_prefix = get_option( 'wbk_db_prefix', '' );
        $wpdb->delete( $db_prefix . 'wbk_appointments', array(
            'id' => $booking_id,
        ), array( '%d' ) );
    }
    
    public static function get_bookings_by_service_and_time( $service_id, $time )
    {
        global  $wpdb ;
        $booking_ids = $wpdb->get_col( $wpdb->prepare( "\r\n\t\t\tSELECT      id\r\n\t\t\tFROM        " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments\r\n \t\t\tWHERE       service_id = %d\r\n\t\t\tAND \t\ttime  = %d\r\n\t\t\t", $service_id, $time ) );
        return $booking_ids;
    }
    
    static function get_total_count_of_bookings_by_day( $day )
    {
        global  $wpdb ;
        $count = $wpdb->get_var( $wpdb->prepare( " SELECT COUNT(*) FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE  day = %d", $day ) );
        return $count;
    }
    
    static function get_booking_payment_methods( $booking_id )
    {
        $booking = new WBK_Booking( $booking_id );
        if ( !$booking->is_loaded() ) {
            return false;
        }
        if ( $booking->get_status() == 'paid' || $booking->get_status() == 'woocommerce' || $booking->get_status() == 'paid_approved' ) {
            return false;
        }
        $service = new WBK_Service( $booking->get_service() );
        if ( !$service->is_loaded() ) {
            return false;
        }
        if ( $service->get_payment_methods() == '' ) {
            return false;
        }
        if ( $service->get_payment_methods() != '' ) {
            
            if ( get_option( 'wbk_appointments_allow_payments', '' ) == '' ) {
                return json_decode( $service->get_payment_methods() );
            } else {
                
                if ( $booking->get_status() == 'approved' ) {
                    return json_decode( $service->get_payment_methods() );
                } else {
                    return false;
                }
            
            }
        
        }
    }
    
    public static function get_booking_by_date_revenue( $start, $end, $type = "" )
    {
        global  $wpdb ;
        $type_condition = "1 = 1";
        
        if ( $type ) {
            $types = explode( ",", $type );
            $type_conditions = [];
            foreach ( $types as $type ) {
                $type_conditions[] = "status='" . esc_sql( trim( $type ) ) . "'";
            }
            $type_condition = " ( " . implode( " OR ", $type_conditions ) . " ) ";
        }
        
        $sql = $wpdb->prepare( "SELECT GROUP_CONCAT(id SEPARATOR ',') as ids, {{date}} FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments where time >= %d AND time < %d AND {$type_condition} GROUP BY created_date ", $start, $end );
        $result = $wpdb->get_results( str_replace( "{{date}}", "from_unixtime(time, '%Y-%m-%d') as created_date", $sql ) );
        
        if ( !empty($result) ) {
            $sorted = [];
            foreach ( $result as $res ) {
                $sorted[$res->created_date] = (int) $res->ids;
            }
            return $sorted;
        }
        
        return [];
    }
    
    public static function get_booking_by_date_range_type(
        $start,
        $end,
        $type = "",
        $return = 'fields'
    )
    {
        global  $wpdb ;
        $type_condition = "1 = 1";
        
        if ( $type ) {
            $types = explode( ",", $type );
            $type_conditions = [];
            foreach ( $types as $type ) {
                $type_conditions[] = "status='" . esc_sql( trim( $type ) ) . "'";
            }
            $type_condition = " ( " . implode( " OR ", $type_conditions ) . " ) ";
        }
        
        
        if ( $return == 'fields' ) {
            $sql = $wpdb->prepare( "SELECT COUNT(id) as count, {{date}} FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments where time >= %d AND time < %d AND {$type_condition} GROUP BY created_date ", $start, $end );
            $result = $wpdb->get_results( str_replace( "{{date}}", "from_unixtime(time, '%Y-%m-%d') as created_date", $sql ) );
            
            if ( !empty($result) ) {
                $sorted = [];
                foreach ( $result as $res ) {
                    $sorted[$res->created_date] = (int) $res->count;
                }
                return $sorted;
            }
        
        } else {
            $sql = $wpdb->prepare( "SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments where time >= %d AND time < %d AND {$type_condition}", $start, $end );
            return $wpdb->get_col( $sql );
        }
        
        return [];
    }

}