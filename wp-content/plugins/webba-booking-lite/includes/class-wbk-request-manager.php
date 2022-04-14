<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Class WBK_Request_Manager is used to perform requests to the REST API
 */
class WBK_Request_Manager
{
    /**
     * constructor
     */
    public function __construct()
    {
        // register route for getting available timeslots for a day
        add_action( 'rest_api_init', function () {
            register_rest_route( 'wbk/v1', '/get-available-time-slots-day/', [
                'methods'             => 'POST',
                'callback'            => [ $this, 'get_available_time_slots_day' ],
                'permission_callback' => [ $this, 'get_available_time_slots_day_permission' ],
            ] );
        } );
        add_action( 'rest_api_init', function () {
            register_rest_route( 'wbk/v1', '/csv-export/', [
                'methods'             => 'POST',
                'callback'            => [ $this, 'wbk_csv_export' ],
                'permission_callback' => [ $this, 'wbk_csv_export_permission' ],
            ] );
        } );
        add_action( 'wp_ajax_wbk_calculate_amounts', array( $this, 'calculate_amounts' ) );
        add_action( 'wp_ajax_nopriv_wbk_calculate_amounts', array( $this, 'calculate_amounts' ) );
        // add_action( 'wp_ajax_wbk_search_time', array( $this, 'search_time') );
        // add_action( 'wp_ajax_nopriv_wbk_search_time', array( $this,'search_time') );
    }
    
    /**
     * getting time slots for a given day
     * @param  WP_REST_Request $request rest request object
     * @return WP_REST_Response rest response object
     */
    public function get_available_time_slots_day( $request )
    {
        $day = $request['date'];
        $service_id = $request['service_id'];
        $current_booking = $request['current_booking'];
        
        if ( !WBK_Validator::is_service_exists( $service_id ) ) {
            $data = array(
                'Reason' => 'Service not exists',
            );
            $response = new \WP_REST_Response( $data );
            $response->set_status( 400 );
            return $response;
        }
        
        
        if ( !WBK_Validator::is_date( $day ) ) {
            $data = array(
                'Reason' => 'Wrong date passed',
            );
            $response = new \WP_REST_Response( $data );
            $response->set_status( 400 );
            return $response;
        }
        
        if ( !Plugion\Validator::check_integer( $current_booking, 1, 2147483647 ) ) {
            $current_booking = null;
        }
        $sp = new WBK_Schedule_Processor();
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        $day = strtotime( $day );
        $timeslots = $sp->get_time_slots_by_day(
            $day,
            $service_id,
            false,
            true,
            true,
            $current_booking
        );
        $time_slots_filtered = array();
        /*
        foreach( $timeslots as $timeslot ){
            $current_quantity = 0;
            if( !is_array( $timeslot->getStatus() ) && $timeslot->getStatus() == 0 ){
                $time_slots_filtered[] = $timeslot;
            }
            if( !is_array( $timeslot->getStatus() ) && Plugion\Validator::check_integer( $timeslot->getStatus(), 1, 2147483647 ) ){
                if( $current_booking == $timeslot->getStatus() ){
                    $booking = new WBK_Booking( $current_booking );
                    $current_quantity = $booking->get_quantity();
                }
                $timeslot->set_free_places( $timeslot->get_free_places() + $current_quantity );
                $time_slots_filtered[] = $timeslot;
            }
            if( is_array( $timeslot->getStatus() ) && in_array( $current_booking, $timeslot->getStatus() ) ){
                $booking = new WBK_Booking( $current_booking );
                $current_quantity = $booking->get_quantity();
                $timeslot->set_free_places( $timeslot->get_free_places() + $current_quantity );
                $time_slots_filtered[] = $timeslot;
            } elseif ( is_array( $timeslot->getStatus() ) ){
                $timeslot->set_free_places( $timeslot->get_free_places() + $current_quantity );
                $time_slots_filtered[] = $timeslot;
            }
        }
        */
        $timeslots_filtered = $timeslots;
        $data = array(
            'time_slots' => $timeslots_filtered,
        );
        $response = new \WP_REST_Response( $data );
        $response->set_status( 200 );
        date_default_timezone_set( 'UTC' );
        return $response;
    }
    
    /**
     * check if current user can get time slots per day
     * @param  WP_REST_Request $request rest request object
     * @return bool allow or not rest request
     */
    public function get_available_time_slots_day_permission( $request )
    {
        return true;
    }
    
    public function wbk_csv_export_permission()
    {
        return true;
    }
    
    /**
     * function CSV export
     * @return null
     */
    public function wbk_csv_export( $request )
    {
    }
    
    public function calculate_amounts()
    {
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        $times = explode( ',', $_POST['times'] );
        $services = explode( ',', $_POST['services'] );
        $quantities = explode( ',', $_POST['quantities'] );
        $extra_data = stripslashes( $_POST['extra_data'] );
        $desc = sanitize_text_field( $_POST['desc'] );
        $phone = sanitize_text_field( $_POST['phone'] );
        $name = sanitize_text_field( $_POST['name'] );
        $i = -1;
        $bookings = array();
        foreach ( $times as $time ) {
            $i++;
            $service = 0;
            if ( isset( $services[$i] ) ) {
                $service = $services[$i];
            }
            $quantity = 0;
            if ( isset( $quantities[$i] ) ) {
                $quantity = $quantities[$i];
            }
            if ( !is_numeric( $time ) || !is_numeric( $service ) || !is_numeric( $quantity ) ) {
                return 0;
            }
            $day = strtotime( date( 'Y-m-d', $time ) . ' 00:00:00' );
            $booking = new WBK_Booking( null );
            $booking->set_parameters(
                $day,
                $time,
                $service,
                $quantity,
                $name,
                $phone,
                $desc,
                $extra_data
            );
            $bookings[] = $booking;
        }
        $sub_total = 0;
        foreach ( $bookings as $booking ) {
            $price = WBK_Price_Processor::calculate_single_booking_price( $booking, $bookings );
            $sub_total += $price['price'] * $booking->get_quantity();
        }
        $service_fees = 0;
        $services = array_unique( $services );
        foreach ( $services as $service ) {
            $service = new WBK_Service( $service );
            $service_fees += $service->get_fee();
        }
        if ( get_option( 'wbk_do_not_tax_deposit', '' ) != 'true' ) {
            $sub_total += $service_fees;
        }
        $price_format = get_option( 'wbk_payment_price_format', '$#price' );
        $sub_total_formated = str_replace( '#price', number_format(
            $sub_total,
            get_option( 'wbk_price_fractional', '2' ),
            get_option( 'wbk_price_separator', '.' ),
            ''
        ), $price_format );
        $tax_amount = WBK_Price_Processor::get_tax_amount( $sub_total, WBK_Price_Processor::get_tax_for_messages() );
        $tax_amount_formated = str_replace( '#price', number_format(
            $tax_amount,
            get_option( 'wbk_price_fractional', '2' ),
            get_option( 'wbk_price_separator', '.' ),
            ''
        ), $price_format );
        $total_amount = WBK_Price_Processor::get_total_amount( $sub_total, WBK_Price_Processor::get_tax_for_messages() );
        if ( get_option( 'wbk_do_not_tax_deposit', '' ) == 'true' ) {
            $total_amount += $service_fees;
        }
        $total_formated = str_replace( '#price', number_format(
            $total_amount,
            get_option( 'wbk_price_fractional', '2' ),
            get_option( 'wbk_price_separator', '.' ),
            ''
        ), $price_format );
        date_default_timezone_set( 'UTC' );
        $result = array(
            'sub_total'          => $sub_total,
            'tax'                => $tax_amount,
            'total'              => $total_amount,
            'sub_total_formated' => $sub_total_formated,
            'tax_formated'       => $tax_amount_formated,
            'total_formated'     => $total_formated,
        );
        $result = json_encode( $result );
        echo  $result ;
        wp_die();
        return;
    }
    
    public function search_time()
    {
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        $service_id = $_POST['service'];
        $date = $_POST['date'];
        $days = $_POST['days'];
        $times = $_POST['times'];
        $offset = $_POST['offset'];
        $time_zone_client = $_POST['time_zone_client'];
        $search_allowed = apply_filters(
            'wbk_time_slots_search_allowed',
            true,
            $service_id,
            $date
        );
        
        if ( $search_allowed !== true ) {
            $result = array(
                'dest' => 'slot',
                'data' => $search_allowed,
            );
            echo  json_encode( $result ) ;
            date_default_timezone_set( 'UTC' );
            die;
            return;
        }
        
        if ( !is_numeric( $offset ) ) {
            $offset = 0;
        }
        
        if ( !is_numeric( $date ) ) {
            $day_to_render = strtotime( $date );
        } else {
            $day_to_render = $date;
        }
        
        if ( !is_numeric( $offset ) ) {
            $offset = 0;
        }
        
        if ( $time_zone_client != '' ) {
            $this_tz = new DateTimeZone( $time_zone_client );
            $date_this = ( new DateTime( '@' . $day_to_render ) )->setTimezone( new DateTimeZone( $time_zone_client ) );
            $offset = $this_tz->getOffset( $date_this );
            $offset = $offset * -1 / 60;
        }
        
        // validation
        
        if ( get_option( 'wbk_mode', 'extended' ) == 'extended' && !is_array( $service_id ) ) {
            
            if ( !is_array( $days ) || !is_array( $times ) ) {
                echo  -1 ;
                die;
                return;
            }
            
            foreach ( $days as $day ) {
                
                if ( !WBK_Validator::checkDayofweek( $day ) ) {
                    date_default_timezone_set( 'UTC' );
                    echo  -3 ;
                    die;
                    return;
                }
            
            }
            foreach ( $times as $time ) {
                
                if ( !WBK_Validator::checkInteger( $time, 0, 1758537351 ) ) {
                    date_default_timezone_set( 'UTC' );
                    echo  -4 ;
                    die;
                    return;
                }
            
            }
        }
        
        
        if ( !WBK_Validator::checkInteger( $day_to_render, 0, 1758537351 ) ) {
            date_default_timezone_set( 'UTC' );
            echo  -5 ;
            die;
            return;
        }
        
        // end validation
        $sp = new WBK_Schedule_Processor();
        $sp->load_data();
        
        if ( !is_array( $service_id ) ) {
            // Single service booking
            $service = new WBK_Service( $service_id );
            
            if ( !$service->loaded() ) {
                date_default_timezone_set( 'UTC' );
                echo  -5 ;
                die;
                return;
            }
            
            $limit_end = 0;
            $range = $service->get_availability_range();
            if ( count( $range ) == 2 ) {
                $limit_end = $range[2];
            }
            $i = 0;
            // set number of days to show - $output_count
            
            if ( get_option( 'wbk_mode', 'extended' ) == 'extended' ) {
                $days_count_opt = apply_filters(
                    'wbk_days_extended_mode',
                    get_option( 'wbk_days_in_extended_mode', 'default' ),
                    $days_count_opt,
                    $service_id
                );
                
                if ( $days_count_opt == 'default' ) {
                    $output_count = 2;
                } else {
                    
                    if ( $days_count_opt == 'lowlimit' || $days_count_opt == 'uplimit' ) {
                        $low_limit = $service->get( 'multi_mode_low_limit' );
                        $up_limit = $service->get( 'multi_mode_limit' );
                        $output_count = 2;
                        if ( $days_count_opt == 'lowlimit' && $low_limit != '' ) {
                            $output_count = $low_limit - 1;
                        }
                        if ( $days_count_opt == 'uplimit' && $up_limit != '' ) {
                            $output_count = $up_limit - 1;
                        }
                    } else {
                        $output_count = $days_count_opt - 1;
                    }
                
                }
            
            } else {
                $output_count = 0;
            }
            
            $html = '';
            $limit_year = 0;
            while ( $i <= $output_count ) {
                $limit_year++;
                
                if ( $limit_year > 360 ) {
                    $i = $output_count + 1;
                    // stop loop
                    continue;
                }
                
                if ( $limit_end != 0 && get_option( 'wbk_mode', 'extended' ) == 'extended' ) {
                    
                    if ( $day_to_render > $limit_end ) {
                        $i = $output_count + 1;
                        // stop loop
                        continue;
                    }
                
                }
                $day_status = $sp->get_day_status( $day_to_render, $service_id );
                
                if ( $day_status == 1 ) {
                    
                    if ( get_option( 'wbk_mode', 'extended' ) == 'extended' ) {
                        $day_name = strtolower( date( 'l', $day_to_render ) );
                        $key = array_search( $day_name, $days );
                        
                        if ( $key === FALSE ) {
                            $day_to_render = strtotime( 'tomorrow', $day_to_render );
                            continue;
                        } else {
                            $time_after = WBK_Time_Math_Utils::adjust_times( $day_to_render, ${$times[$key]}, get_option( 'wbk_timezone', 'UTC' ) );
                        }
                    
                    } else {
                        $time_after = $day_to_render;
                    }
                    
                    $timeslots = $sp->get_time_slots_by_day(
                        $day_to_render,
                        $service_id,
                        false,
                        false,
                        true
                    );
                    $html = WBK_Renderer::load_template( 'frontend/day_with_timeslots', array( $day_to_render, $time_slots, $service_id ) );
                    echo  json_encode( array(
                        'data' => $html,
                        'dest' => 'slot',
                    ) ) ;
                    wp_die();
                    return;
                    // todo procees only one slot and skip time slot selection
                    // 	if ( substr_count( $day_slots, 'wbk-timeslot-btn' ) == 1  &&  $skip_value == 'enabled' ){
                    /*
                    
                    		 				$first_time = $service_schedule->getFirstAvailableTime();
                    		 				$form_html = $this->render_booking_form( $service_id, $first_time );
                    						$form_html = apply_filters( 'wbk_form_html', $form_html, $service_id, $first_time );
                    		 				$result =  array( 'dest' => 'form', 'data'  => $form_html,  'time' => $first_time );
                    		 				date_default_timezone_set('UTC');
                    						echo json_encode( $result );
                    				 		die();
                    						return;
                    		 			}
                    */
                    // end first time slot
                }
                
                
                if ( get_option( 'wbk_mode', 'extended' ) == 'extended' ) {
                    $day_to_render = strtotime( 'tomorrow', $day_to_render );
                } else {
                    $i++;
                }
            
            }
        } else {
            // multi service boooking
            $service_ids = $service_id;
            $date_format = WBK_Date_Time_Utils::getDateFormat();
            
            if ( !is_numeric( $date ) ) {
                $day_to_render = strtotime( $date );
            } else {
                $day_to_render = $date;
            }
            
            if ( !is_numeric( $offset ) ) {
                $offset = 0;
            }
            $html = '';
            $i = 0;
            $multi_serv_date_limit = get_option( 'wbk_multi_serv_date_limit', '360' );
            $up_limit = strtotime( 'today midnight' ) + 86400 * $multi_serv_date_limit;
            $output_counts = array();
            foreach ( $service_ids as $service_id ) {
                $service_schedule = new WBK_Service_Schedule();
                $service_schedule->setServiceId( $service_id );
                if ( !$service_schedule->load() ) {
                    continue;
                }
                
                if ( get_option( 'wbk_mode', 'extended' ) == 'extended' ) {
                    $days_count_opt = get_option( 'wbk_days_in_extended_mode', 'default' );
                    $days_count_opt = apply_filters( 'wbk_days_extended_mode', $days_count_opt, $service_id );
                    
                    if ( $days_count_opt == 'default' ) {
                        $output_count = 2;
                    } else {
                        
                        if ( $days_count_opt == 'lowlimit' || $days_count_opt == 'uplimit' ) {
                            $low_limit_x = $service_schedule->getService()->getMultipleLowLimit();
                            $up_limit_x = $service_schedule->getService()->getMultipleLimit();
                            $output_count = 2;
                            if ( $days_count_opt == 'lowlimit' && $low_limit_x != '' ) {
                                $output_count = $low_limit_x - 1;
                            }
                            if ( $days_count_opt == 'uplimit' && $up_limit_x != '' ) {
                                $output_count = $up_limit_x - 1;
                            }
                        } else {
                            $output_count = $days_count_opt - 1;
                        }
                    
                    }
                
                } else {
                    $output_count = 0;
                }
                
                $output_counts[] = $output_count;
            }
            $output_count = min( $output_counts );
            $limit_end = 0;
            while ( $i <= $output_count ) {
                
                if ( $day_to_render > $up_limit ) {
                    $limit_end = $day_to_render + 1;
                    $i = $output_count + 1;
                }
                
                $day_slots_all_services = array();
                $array_sort = array();
                foreach ( $service_ids as $service_id ) {
                    $service_schedule = new WBK_Service_Schedule();
                    $service_schedule->setServiceId( $service_id );
                    if ( !$service_schedule->load() ) {
                        continue;
                    }
                    
                    if ( $service_schedule->getService()->getDateRange() != '' ) {
                        $limit_end_current_service = $service_schedule->getService()->getDateRangeEnd();
                        $limit_start_current_service = $service_schedule->getService()->getDateRangeStart();
                    } else {
                        $limit_end_current_service = 0;
                        $limit_start_current_service = 0;
                    }
                    
                    if ( $day_to_render > $limit_end_current_service && $limit_end_current_service != 0 || $day_to_render < $limit_start_current_service && $limit_start_current_service != 0 ) {
                        continue;
                    }
                    $day_status = $service_schedule->getDayStatus( $day_to_render );
                    
                    if ( $day_status == 1 ) {
                        $time_after = $day_to_render;
                        $service_schedule->buildSchedule(
                            $day_to_render,
                            false,
                            false,
                            true,
                            true
                        );
                        $first_time = 0;
                        $time_slots = $service_schedule->getTimeSlots();
                        if ( count( $time_slots ) > 0 ) {
                            $first_time = $time_slots[0]->getStart();
                        }
                        $day_slots = $service_schedule->renderDayFrontend( $time_after, $offset );
                    } else {
                        $day_slots = '';
                    }
                    
                    
                    if ( $day_slots != '' ) {
                        $array_sort[] = $first_time;
                        $day_slots_all_services[] = '<label class="wbk-multiple-service-title">' . $service_schedule->getService()->getName() . '</label>' . $day_slots;
                    }
                
                }
                array_multisort( $array_sort, $day_slots_all_services, SORT_NUMERIC );
                $day_slots_all_services = implode( '', $day_slots_all_services );
                $date_regular = wp_date( $date_format, $day_to_render, new DateTimeZone( date_default_timezone_get() ) );
                $timezone = new DateTimeZone( get_option( 'wbk_timezone', 'UTC' ) );
                $current_offset = $offset * -60 - $timezone->getOffset( new DateTime() );
                $date_local = wp_date( $date_format, $day_to_render + $current_offset, new DateTimeZone( date_default_timezone_get() ) );
                $day_title = get_option( 'wbk_day_label', '#date' );
                $day_title = str_replace( '#date', $date_regular, $day_title );
                $day_title = str_replace( '#local_date', $date_local, $day_title );
                
                if ( get_option( 'wbk_mode', 'extended' ) == 'extended' ) {
                    $day_to_render = strtotime( 'tomorrow', $day_to_render );
                } else {
                    $i++;
                }
                
                
                if ( $day_slots_all_services != '' ) {
                    $html .= '<div class="wbk-col-12-12">
								<div class="wbk-day-title">
									' . $day_title . '
								</div>
								<hr class="wbk-day-separator">
	  						  </div>';
                    $html .= '<div class="wbk-col-12-12 wbk-text-center" >' . $day_slots_all_services . '</div>';
                    $i++;
                }
            
            }
        }
        
        
        if ( get_option( 'wbk_mode', 'extended' ) == 'extended' ) {
            
            if ( $html != '' ) {
                
                if ( $limit_end != 0 ) {
                    if ( $day_to_render <= $limit_end ) {
                        $html .= '<div class="wbk-frontend-row" id="wbk-show_more_container">
									<input type="button" class="wbk-button"  id="wbk-show_more_btn" value="' . __( 'Show more', 'wbk' ) . '"  />
									<input type="hidden" id="wbk-show-more-start" value="' . $day_to_render . '">
								  </div>';
                    }
                    $html .= '<div class="wbk-more-container"></div>';
                } else {
                    $html .= '<div class="wbk-frontend-row" id="wbk-show_more_container">
								<input type="button" class="wbk-button"  id="wbk-show_more_btn" value="' . __( 'Show more', 'wbk' ) . '"  />
								<input type="hidden" id="wbk-show-more-start" value="' . $day_to_render . '">
							  </div>';
                    $html .= '<div class="wbk-more-container"></div>';
                }
            
            } else {
                $html = get_option( 'wbk_book_not_found_message', 'Unfortunately we were unable to meet your search criteria. Please change the criteria and try again.' );
            }
        
        } else {
            if ( $html == '' ) {
                $html = get_option( 'wbk_book_not_found_message', 'Unfortunately we were unable to meet your search criteria. Please change the criteria and try again.' );
            }
        }
        
        
        if ( get_option( 'wbk_show_cancel_button', 'disabled' ) == 'enabled' ) {
            global  $wbk_wording ;
            $cancel_label = get_option( 'wbk_cancel_button_text', '' );
            if ( $cancel_label == '' ) {
                $cancel_label = sanitize_text_field( $wbk_wording['cancel_label_form'] );
            }
            $html .= '<input class="wbk-button wbk-width-100 wbk-cancel-button"  value="' . $cancel_label . '" type="button">';
        }
        
        $result = array(
            'dest' => 'slot',
            'data' => $html,
        );
        echo  json_encode( $result ) ;
        date_default_timezone_set( 'UTC' );
        die;
        return;
    }

}