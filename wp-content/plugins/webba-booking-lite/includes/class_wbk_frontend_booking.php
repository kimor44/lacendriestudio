<?php
// check if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WBK_Frontend_Booking {
	public function __construct() {
		// add shortcode
		add_shortcode( 'webba_booking' , array( $this, 'wbk_shc_webba_booking' ) );

		add_shortcode( 'webba_email_landing' , array( $this, 'wbk_email_landing_shortcode' ) );
		add_shortcode( 'webba_multi_service_booking' , array( $this, 'wbk_shc_multi_service_booking' ) );
 		// init scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts') );
		// param process
		add_action ('wp_loaded', array( $this, 'param_processing' ) );
		 
	}

	public function wbk_email_landing_shortcode(){
		$get_processing = WBK_Renderer::load_template( 'frontend/get_parameters_processing', array(), false );
		if( $get_processing != '' ){
			echo $get_processing;
			return;
		}

	}

	public function param_processing() {
		if ( class_exists( 'WooCommerce' ) ){
			if( !session_id() ){
	    		session_start();
			}
		}

		if( isset( $_GET[ 'error' ]  ) ){
			wp_redirect( get_permalink() . '?ggadd_cancelled=1' );
			exit;
		}
		if( isset( $_GET['ggeventadd'] ) ){
			$ggeventadd =  $_GET['ggeventadd'];
			$ggeventadd = WBK_Db_Utils::wbk_sanitize( $ggeventadd );
			$appointment_ids = WBK_Db_Utils::getAppointmentIdsByGroupToken( $ggeventadd );
	 		if( count( $appointment_ids ) == 0 ){
	 		} else {
	 			if( !session_id() ){
        			session_start();
   				}
   				$_SESSION['wbk_ggeventaddtoken'] = $ggeventadd;
	 		}
		}
		if( isset( $_GET['code'] ) ){
			if( !session_id() ){
    			session_start();
			}
 		}
		// check if called as payment result
	    if( isset( $_GET['pp_aprove'] ) ){
	    	if ( $_GET['pp_aprove'] == 'true' ){
	    		if ( isset( $_GET['paymentId'] ) && isset( $_GET['PayerID'] ) ){
	    			$paymentId = $_GET['paymentId'];
	    			$PayerID = $_GET['PayerID'];
	    			$paypal = new WBK_PayPal();
					$booking_ids =  WBK_Model_Utils::get_booking_ids_by_payment_id( $paymentId );
	    			$init_result = $paypal->init( false, $booking_ids );
	    			if ( $init_result === FALSE ){
	    			 	wp_redirect( get_permalink() . '?paypal_status=2'  );
					 	exit;
	    			} else {
	    				$execResult = $paypal->execute_payment( $paymentId, $PayerID );
	    				if( $execResult === false ){
	    					wp_redirect( get_permalink() . '?paypal_status=3' );
							exit;
	    				} else {
	    					$pp_redirect_url = trim( get_option( 'wbk_paypal_redirect_url', '' ) );
	    					if( $pp_redirect_url != '' ){
								if( filter_var( $pp_redirect_url, FILTER_VALIDATE_URL ) !== FALSE) {
									wp_redirect( $pp_redirect_url );
									exit;
								}
	    					}
	    					wp_redirect( get_permalink() . '?paypal_status=1' );
							exit;
	    				}
	    			}
	    		} else {
		   			wp_redirect( get_permalink() . '?paypal_status=4' );
					exit;
	    		}
	    	} elseif( $_GET['pp_aprove'] == 'false' ) {
				if( isset( $_GET['cancel_token'] ) ){
					$cancel_token =  $_GET['cancel_token'];
					$cancel_token = str_replace('"', '', $cancel_token );
					$cancel_token = str_replace('<', '', $cancel_token );
					$cancel_token = str_replace('\'', '', $cancel_token );
					$cancel_token = str_replace('>', '', $cancel_token );
					$cancel_token = str_replace('/', '', $cancel_token );
					$cancel_token = str_replace('\\',  '', $cancel_token );
					WBK_Db_Utils::clearPaymentIdByToken( $cancel_token );

				}
				wp_redirect( get_permalink() . '?paypal_status=5' );
				exit;
	    	}
		}
 	}



	public function render( $template, $data ){
		// load and output view template
		ob_start();
        ob_implicit_flush(0);
		try {
             include  dirname(__FILE__) . '/../templates/tpl_wbk_frontend_' . $template . '.php';
        } catch (Exception $e) {
        	ob_end_clean();
            throw $e;
        }
        return ob_get_clean();
	}
	public function wbk_shc_webba_booking( $attr ) {

		extract( shortcode_atts( array( 'service' => '0' ), $attr ) );
		extract( shortcode_atts( array( 'category' => '0' ), $attr ) );
		extract( shortcode_atts( array( 'category_list' => '0' ), $attr ) );

 		return WBK_Renderer::load_template( 'frontend/form_container_s', array( $service, $category, $category_list ), false );

 	}
	public function wbk_shc_multi_service_booking( $attr ) {
		extract( shortcode_atts( array( 'category' => '0', 'skip_services' => '0', 'category_list' => '0' ), $attr ) );
		return WBK_Renderer::load_template( 'frontend/form_container_m', array( $category, $skip_services, $category_list ), false );

	}
	 
	public function wp_enqueue_scripts() {

	 		$startOfWeek = get_option( 'wbk_start_of_week', 'monday' );
	 		if ( $startOfWeek == 'monday' ){
	 			$startOfWeek = '1';
	 		} else{
	 			$startOfWeek = '0';
	 		}
	 		$select_date_extended_label = get_option( 'wbk_date_extended_label', '' );
	 	 
	 		$select_date_basic_label = get_option( 'wbk_date_basic_label', '' );
	 		 
	 		$select_slots_label = get_option( 'wbk_slots_label', '' );
	 
			$thanks_message = get_option( 'wbk_book_thanks_message', '' );
	  
	 		$select_date_placeholder = WBK_Validator::alfa_numeric( get_option( 'wbk_date_input_placeholder', '' ) );

	 		$booked_text = get_option( 'wbk_booked_text',  '' );
	 	 
			// Localize the script with new data
			$checkout_label = get_option( 'wbk_checkout_button_text', '' );
	 
			$checkout_label = str_replace( '#selected_count', '<span class="wbk_multi_selected_count"></span>', $checkout_label );
			$checkout_label = str_replace( '#total_count', '<span class="wbk_multi_total_count"></span>', $checkout_label );
			$checkout_label = str_replace( '#low_limit', '<span class="wbk_multi_low_limit"></span>', $checkout_label );
			$continuous_appointments =  get_option( 'wbk_appointments_continuous' );
			if( is_array( $continuous_appointments ) ){
				$continuous_appointments = implode( ',', $continuous_appointments );
			} else {
				$continuous_appointments = '';
			}
			$translation_array = array(
				'mode' => get_option( 'wbk_mode', 'extended' ),
				'phonemask' => get_option( 'wbk_phone_mask', 'enabled' ),
				'phoneformat' => get_option( 'wbk_phone_format', '(999) 999-9999' ),
				'ajaxurl' => admin_url( 'admin-ajax.php'),
				'selectdatestart' => $select_date_extended_label,
				'selectdatestartbasic' => $select_date_basic_label,
				'selecttime' => $select_slots_label,
				'selectdate' => $select_date_placeholder,
				'thanksforbooking' =>  $thanks_message,
				'january' => __( 'January', 'wbk' ),
				'february' => __( 'February', 'wbk' ),
				'march' => __( 'March', 'wbk' ),
				'april' => __( 'April', 'wbk' ),
				'may' => __( 'May', 'wbk' ),
				'june' => __( 'June', 'wbk' ),
				'july' => __( 'July', 'wbk' ),
				'august' => __( 'August', 'wbk' ),
				'september' => __( 'September', 'wbk' ),
				'october' => __( 'October', 'wbk' ),
				'november' => __( 'November', 'wbk' ),
				'december' => __( 'December', 'wbk' ),
				'jan' =>  __( 'Jan', 'wbk' ),
				'feb' =>  __( 'Feb', 'wbk' ),
				'mar' =>  __( 'Mar', 'wbk' ),
				'apr' =>  __( 'Apr', 'wbk' ),
				'mays' =>  __( 'May', 'wbk' ),
				'jun' =>  __( 'Jun', 'wbk' ),
				'jul' =>  __( 'Jul', 'wbk' ),
				'aug' =>  __( 'Aug', 'wbk' ),
				'sep' =>  __( 'Sep', 'wbk' ),
				'oct' =>  __( 'Oct', 'wbk' ),
				'nov' =>  __( 'Nov', 'wbk' ),
				'dec' =>  __( 'Dec', 'wbk' ),
				'sunday' =>  __( 'Sunday', 'wbk' ),
				'monday' =>  __( 'Monday', 'wbk' ),
				'tuesday' =>  __( 'Tuesday', 'wbk' ),
				'wednesday' =>  __( 'Wednesday', 'wbk' ),
				'thursday' =>  __( 'Thursday', 'wbk' ),
				'friday' =>  __( 'Friday', 'wbk' ),
				'saturday' =>  __( 'Saturday', 'wbk' ),
				'sun' =>  __( 'Sun', 'wbk' ),
				'mon' =>  __( 'Mon', 'wbk' ),
				'tue' =>  __( 'Tue', 'wbk' ),
				'wed' =>  __( 'Wed', 'wbk' ),
				'thu' =>  __( 'Thu', 'wbk' ),
				'fri' =>  __( 'Fri', 'wbk' ),
				'sat' =>  __( 'Sat', 'wbk' ),
				'today' =>  __( 'Today', 'wbk' ),
				'clear' =>  __( 'Clear', 'wbk' ),
				'close' =>  __( 'Close', 'wbk' ),
				'startofweek' => $startOfWeek,
			 
				'nextmonth' => __( 'Next month', 'wbk' ),
				'prevmonth'=> __( 'Previous  month', 'wbk' ),
				'hide_form' => get_option( 'wbk_hide_from_on_booking', 'disabled' ),
				'booked_text' => $booked_text,
				'show_booked'  => get_option( 'wbk_show_booked_slots', 'disabled' ),
				'multi_booking'  => get_option( 'wbk_multi_booking', 'disabled' ),
				'checkout'  => $checkout_label,
				'multi_limit'  => get_option( 'wbk_multi_booking_max', '' ),
				'multi_limit_default'  => get_option( 'wbk_multi_booking_max', '' ),
				'phone_required'  => get_option( 'wbk_phone_required', '3' ),
				'show_desc' => get_option( 'wbk_show_service_description', 'disabled' ),
				'date_input' => get_option( 'wbk_date_input', 'popup' ),
				'allow_attachment' => get_option( 'wbk_allow_attachemnt',  'no' ),
				'stripe_public_key' => get_option( 'wbk_stripe_publishable_key', '' ),
				'override_stripe_error' => get_option( 'wbk_stripe_card_input_mode', 'no' ),
				'stripe_card_error_message' => get_option( 'wbk_stripe_card_element_error_message', 'incorrect input' ),
				'something_wrong' => __( 'Something went wrong, please try again.', 'wbk' ),
				'time_slot_booked' => __( 'Time slot(s) already booked.', 'wbk' ),
				'pp_redirect' => get_option( 'wbk_paypal_auto_redirect', 'disabled' ),
				'show_prev_booking' =>	get_option( 'wbk_show_details_prev_booking', 'disabled' ),
				'scroll_container' => get_option( 'wbk_scroll_container', 'html, body' ),
				'continious_appointments' => $continuous_appointments,
				'show_suitable_hours' => get_option( 'wbk_show_suitable_hours', 'yes' ),
				'stripe_redirect_url' => get_option( 'wbk_stripe_redirect_url', '' ),
				'stripe_mob_size' => get_option( 'wbk_stripe_mob_font_size', '' ),
				'auto_add_to_cart' => get_option( 'wbk_woo_auto_add_to_cart', 'disabled' ),
				'range_selection' => get_option( 'wbk_range_selection', 'disabled' ),
				'picker_format' => WBK_Db_Utils::convertDateFormatForPicker(),
				'scroll_value' => get_option( 'wbk_scroll_value', '120' ),
				'field_required' =>  get_option( 'wbk_validation_error_message', '' ),
				'error_status_scroll_value' => '0',
				'limit_per_email_message' => get_option( 'wbk_limit_by_email_reached_message', __( 'You have reached your booking limit.', 'wbk' ) ),
			 	'stripe_hide_postal' => get_option( 'wbk_stripe_hide_postal', 'false' ),
				'jquery_no_conflict' => get_option( 'wbk_jquery_nc', 'disabled' ),
				'no_available_dates' => get_option( 'wbk_no_dates_label', __( 'No available dates message', 'wbk' ) ),
				'auto_select_first_date' =>  get_option( 'wbk_auto_select_first_date', 'disabled'),
				'book_text_timeslot' => WBK_Validator::alfa_numeric( get_option( 'wbk_book_text_timeslot', __( 'Book', 'wbk' ) ) ),
				'deselect_text_timeslot' => get_option( 'wbk_deselect_text_timeslot', '' )


 			);
			$sanitized_array = array();
			foreach(  $translation_array as $key => $value ){
				$sanitized_array[$key] = WBK_Validator::alfa_numeric( $value );
			}


	}
	private function has_shortcode_strong( $shortcode ){
	    $post_to_check = get_post(get_the_ID());
	    if ( !$post_to_check ) {
	    	return false;
	    }
	    $found = false;
	    if ( !$shortcode ) {
	        return $found;
	    }
	    if ( stripos($post_to_check->post_content, '[' . $shortcode) !== false ) {
 	        $found = true;
	    }
 	    return $found;

	}

	// check if post has shortcode using option wbk_check_short_code
	// if wbk_check_short_code is disable - always return true
	private function has_shortcode( $shortcode = '' ) {
	    if( get_option('wbk_check_short_code', 'disabled') == 'disabled' ){
	    	return true;
	    }
	    $post_to_check = get_post(get_the_ID());
	    if ( !$post_to_check ) {
	    	return false;
	    }
	    $found = false;
	    if ( !$shortcode ) {
	        return $found;
	    }
	    if ( stripos($post_to_check->post_content, '[' . $shortcode) !== false ) {
 	        $found = true;
	    }
 	    return $found;
	}
 


}
?>
