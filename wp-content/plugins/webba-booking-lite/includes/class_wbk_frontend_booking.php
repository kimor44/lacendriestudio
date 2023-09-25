<?php
// check if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WBK_Frontend_Booking {
	private $scenario;
	public function __construct() {
		// add shortcode
		add_shortcode( 'webba_booking' , array( $this, 'wbk_shc_webba_booking' ) );
		add_shortcode( 'webbabooking' , array( $this, 'wbk_shc_webbabooking' ) );

		add_shortcode( 'webba_email_landing' , array( $this, 'wbk_email_landing_shortcode' ) );
		add_shortcode( 'webba_multi_service_booking' , array( $this, 'wbk_shc_multi_service_booking' ) );
 		// init scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts') );
		// param process
		add_action ('wp_loaded', array( $this, 'param_processing' ) );
		 
	}

    // deprecated shortcodes
	public function wbk_email_landing_shortcode(){
        return do_shortcode( '[webbabooking]' );;
	}
    
    public function wbk_shc_multi_service_booking( $attr ) {
		extract( shortcode_atts( array( 'category' => '0', 'skip_services' => '0', 'category_list' => '0' ), $attr ) );
		return do_shortcode( '[webbabooking multiservice=yes]' );;
	}

    public function wbk_shc_webba_booking( $attr ) {
        extract( shortcode_atts( array( 'service' => '0' ), $attr ) );
		extract( shortcode_atts( array( 'category' => '0' ), $attr ) );
		extract( shortcode_atts( array( 'category_list' => '0' ), $attr ) );
        if( $service != '0' ){
            return do_shortcode( '[webbabooking service=' . $service . ']' );
        }
        if( $category != '0' ){
            return do_shortcode( '[webbabooking category=' . $category . ']' );
        }
        if( $category_list != '0' ){
            return do_shortcode( '[webbabooking category_list=yes]' );
        }
      
		return do_shortcode( '[webbabooking]' );
 	}


    public function render( $template, $data ){
        return;
	}
    // end of deprecated shortcodes

	 

	public function param_processing() {
		if( isset( $_GET[ 'error' ]  ) ){
			wp_redirect( get_permalink() . '?ggadd_cancelled=1' );
			exit;
		}
		if( isset( $_GET['ggeventadd'] ) ){
			$ggeventadd =  $_GET['ggeventadd'];
			$ggeventadd = WBK_Db_Utils::wbk_sanitize( $ggeventadd );
			$appointment_ids = WBK_Db_Utils::getAppointmentIdsByGroupToken( $ggeventadd );
	 		if( count( $appointment_ids ) > 0 ){
	 	 
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
	    if( isset( $_GET['pp_aprove'] ) && !wbk_is5() ){
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



	

	 public function wbk_shc_webbabooking( $attr ) {

        $get_processing = WBK_Renderer::load_template( 'frontend/get_parameters_processing', array(), false );
        if( $get_processing != '' ){
            return $get_processing;
        }

		extract( shortcode_atts( array( 'service' => '0' ), $attr ) );
		extract( shortcode_atts( array( 'category' => '0' ), $attr ) );
		extract( shortcode_atts( array( 'category_list' => 'no' ), $attr ) );
        extract( shortcode_atts( array( 'multiservice' => 'no' ), $attr ) );
        
		if( $category > 0 ){
			$service_ids = WBK_Model_Utils::get_services_in_category( $category );
		} else{
			$service_ids = WBK_Model_Utils::get_service_ids();
		}
        $category_ids = WBK_Model_Utils::get_service_categories();
       
        if( isset( $_GET['service'] ) && is_numeric( $_GET['service'] ) ){
            $service = $_GET['service'];
        }
		
        if( $service == 0 ){
            if( $multiservice != 'yes' ){
                if( $category_list == 'yes' ){
                    $templates = array( 'frontend_v5/category_dropdown' => array( $category_ids ),
                                        'frontend_v5/service_single_radio' => array( $service_ids, false, true ) );
                } else {
                    $templates = array( 'frontend_v5/service_single_radio' => array( $service_ids, false, false ) );
                }
            } else {
                if( $category_list == 'yes' ){
                    $templates = array( 'frontend_v5/category_dropdown' => array( $category_ids ),
                                        'frontend_v5/service_multiple' => array( $service_ids, false, true ) );
                } else {
                    $templates = array( 'frontend_v5/service_multiple' => array( $service_ids, false, false ) );
                    
                }
            }

            $this->scenario[] = array( 'title' => esc_html__( 'Service', 'webba-booking-lite' ),
                                       'slug' => 'services',
                                       'templates' =>  $templates, 					 
                                       'request' => '' 
                                        );
    
            $this->scenario[] = array( 'title' => esc_html__( 'Date and time', 'webba-booking-lite' ),
                                       'slug' => 'date_time',
                                       'templates' => array( 'frontend_v5/date_time' => array( false ) ),							 
                                       'request' => 'wbk_prepare_service_data' 
                                      );
        } else {
            $service_ids = array( $service );

            $this->scenario[] = array( 'title' => esc_html__( 'Date and time', 'webba-booking-lite' ),
                                       'slug' => 'date_time',
                                       'templates' => array( 'frontend_v5/service_dropdown' => array( $service_ids, true ),
                                                             'frontend_v5/date_time' => array( true ) ),							 
                                       'request' => 'wbk_prepare_service_data' 
                                    );

        }
        


        // detect if there are free services
        $free_services = 0;
        $paid_services = 0;                         
        foreach( $service_ids as $service_id ){
            $service = new WBK_Service( $service_id );
            if( !$service->is_loaded() ){
                continue;
            }
            if( $service->get_payment_methods() == '' ){
                $free_services++;
            } else {
                $paid_services++;
            }
        }         
 
        $this->scenario[] = array( 
                            'title' => esc_html__( 'Details', 'webba-booking-lite' ),
                            'slug' => 'form',								     					 
                            'request' => 'wbk_render_booking_form',
                        );
  
        if( $paid_services > 0 ) {

            $payment_slug = 'payment';
            if( $free_services > 0 ){
                $payment_slug = 'payment_optional';
                
            }
            // only paid services
            $this->scenario[] = array(      
                        'title' => esc_html__( 'Payment', 'webba-booking-lite' ),          
                        'slug' => $payment_slug,								     					 
                        'request' => 'wbk_book'
                        );
            
            $this->scenario[] = array(                
                        'slug' => 'final_screen',								     					 
                        'request' => 'wbk_approve_payment'
                        );
          
        } else {
            $this->scenario[] = array(                
                            'slug' => 'final_screen',								     					 
                            'request' => 'wbk_book'
                            );
        }       
  	
 		return WBK_Renderer::load_template( 'frontend_v5/webba5_form_container', array( $this->scenario ), false );

 	}	
	
	public function wp_enqueue_scripts() {

	  
	 		$select_date_extended_label = get_option( 'wbk_date_extended_label', '' );
	 		$select_date_basic_label = get_option( 'wbk_date_basic_label', '' );
	 		$select_slots_label = get_option( 'wbk_slots_label', '' );
			$thanks_message = get_option( 'wbk_book_thanks_message', '' );
	 		$select_date_placeholder = WBK_Validator::alfa_numeric( get_option( 'wbk_date_input_placeholder', '' ) );
 
	 		$booked_text = get_option( 'wbk_booked_text', '' );
 
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
