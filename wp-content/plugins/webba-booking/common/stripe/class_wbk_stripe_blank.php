<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// webba booking Stripe integration class
class WBK_Stripe{
	protected
	$api_key;
	protected
	$api_sectet;
	protected
	$tax;
	protected
	$currency;

	public function init( $service_id ){
		if ( wbk_fs()->is__premium_only() ) {
		    if ( wbk_fs()->can_use_premium_code() ) {
                \Stripe\Stripe::setAppInfo("WordPress Webba Booking plugin", "3.8", "https://webba-booking.com/", "pp_partner_EejuT4kiSF2Bav");
				\Stripe\Stripe::setApiVersion("2019-05-16");
				$this->tax =  get_option( 'wbk_stripe_tax', 0 );
				$this->currency = get_option( 'wbk_stripe_currency', '' );
                $credentials = array( trim( get_option( 'wbk_stripe_publishable_key', '' ) ), trim(  get_option( 'wbk_stripe_secret_key', '' ) ) );
                $credentials = apply_filters( 'wbk_stripe_credentials', $credentials, $service_id );
				$this->api_key = $credentials[0];
				$this->api_sectet = $credentials[1];
				if( $this->currency == '' || $this->api_key == '' || $this->api_sectet == '' || !is_numeric( $this->tax )  ){
					return FALSE;
				}
				if ( $this->tax < 0 || $this->tax > 100 ){
					$this->tax = 0;
				}
				return TRUE;
		    }
		}
		return FALSE;
	}
	public static function getCurrencies(){
		return array('USD','AED','AFN','ALL','AMD','ANG','AOA','ARS','AUD','AWG','AZN','BAM','BBD','BDT','BGN','BIF','BMD','BND','BOB','BRL','BSD','BWP','BZD','CAD','CDF','CHF','CLP','CNY','COP','CRC','CVE','CZK','DJF','DKK','DOP','DZD','EGP','ETB','EUR','FJD','FKP','GBP','GEL','GIP','GMD','GNF','GTQ','GYD','HKD','HNL','HRK','HTG','HUF','IDR','ILS','INR','ISK','JMD','JPY','KES','KGS','KHR','KMF','KRW','KYD','KZT','LAK','LBP','LKR','LRD','LSL','MAD','MDL','MGA','MKD','MMK','MNT','MOP','MRO','MUR','MVR','MWK','MXN','MYR','MZN','NAD','NGN','NIO','NOK','NPR','NZD','PAB','PEN','PGK','PHP','PKR','PLN','PYG','QAR','RON','RSD','RUB','RWF','SAR','SBD','SCR','SEK','SGD','SHP','SLL','SOS','SRD','STD','SVC','SZL','THB','TJS','TOP','TRY','TTD','TWD','TZS','UAH','UGX','UYU','UZS','VND','VUV','WST','XAF','XCD','XOF','XPF','YER','ZAR','ZMW');

	}
	public static function isCurrencyZeroDecimal( $currency ){
		$arr_list = array('MGA','BIF','CLP','PYG','DJF','RWF','GNF','JPY','VND','VUV','XAF','KMF','KRW','XOF','XPF');
		if( in_array( $currency, $arr_list ) ){
			return TRUE;
		} else{
			return FALSE;
		}
	}
	static function	renderPaymentMethods( $service_id, $appointment_ids, $button_class = ''  ){
		global $wbk_wording;

		if( !is_array( $service_id ) ){
			$services = array( $service_id );
		} else {
			$services = $service_id;
		}
		foreach( $services as $service_id ){

			$service = new WBK_Service_deprecated();
		    if ( !$service->setId( $service_id ) ){
		        return 'Unable to access service: wrong service id.';
		    }
		    if ( !$service->load() ){
		         return 'Unable to access service: load failed.';
		    }

		    $arr_items = json_decode( $service->getPayementMethods() );
			if( !in_array( 'stripe', $arr_items) ){
				return '';
			}
		}
		$html = '';
		$stripe_btn_text = WBK_Validator::alfa_numeric( get_option( 'wbk_stripe_button_text', 'Pay with credit card' ) );

		$html .= '<input class="wbk-button wbk-width-100 wbk-mt-10-mb-10 wbk-payment-init '. $button_class .'" data-method="stripe" data-app-id="'. implode(',',  $appointment_ids ) . '"  value="' . $stripe_btn_text . '" type="button">';
		return $html;
	}
    public function createPayment( $method, $app_ids, $coupon ){
    	$html = '';
    	if ( wbk_fs()->is__premium_only() ) {
		    if ( wbk_fs()->can_use_premium_code() ) {
		    	global $wbk_wording;
		        $payment_details = get_option( 'wbk_payment_details_title',  '' );
		        if( $payment_details == '' ){
		        	$payment_details = sanitize_text_field( $wbk_wording['payment_details'] );
		        }
		        $html = '<div class="wbk-details-sub-title">' . $payment_details . '</div>
			                 <hr class="wbk-form-separator">';
				$subtotal = 0;
				$item_names = array();
		        foreach( $app_ids as $app_id ){
					$service_id = WBK_Db_Utils::getServiceIdByAppointmentId( $app_id );
					if( $service_id === false ){
						return -1;
					}
					$service = new WBK_Service_deprecated();
					if ( !$service->setId( $service_id ) ) {
						return -1;
					}
					if ( !$service->load() ) {
						return -1;
					}
			        $appointment = new WBK_Appointment_deprecated();
			        if ( !$appointment->setId( $app_id ) ) {
			            return -1;
			        }
			        if ( !$appointment->load() ){
			            return -1;
			        }
					$time_format = WBK_Date_Time_Utils::getTimeFormat();
					$date_format = WBK_Date_Time_Utils::getDateFormat();
					WBK_Db_Utils::setPaymentId( $app_id, uniqid() );

			        $item_name = get_option( 'wbk_payment_item_name', '' );
			        if( $item_name == '' ){
			        	$item_name = sanitize_text_field( $wbk_wording['payment_item_name'] );
			        }

			        $item_name = str_replace( '#date', wp_date( $date_format, $appointment->getTime(), new DateTimeZone( date_default_timezone_get() ) ), $item_name );
			        $item_name = str_replace( '#time', wp_date( $time_format, $appointment->getTime(), new DateTimeZone( date_default_timezone_get() ) ), $item_name );
			        $item_name = str_replace( '#tr', wp_date( $time_format, $appointment->getTime(), new DateTimeZone( date_default_timezone_get() ) ) . ' - ' .  wp_date( $time_format, $appointment->getTime() + $service->getDuration() * 60, new DateTimeZone( date_default_timezone_get() ) ) , $item_name );
			        $item_name = str_replace( '#id',  $appointment->getId(), $item_name );
			        $item_name = str_replace( '#name',  $appointment->getName(), $item_name );
			        $item_name = str_replace( '#email',  $appointment->getEmail(), $item_name );
			        $item_name = str_replace( '#quantity',  $appointment->getQuantity(), $item_name );
					$item_name = WBK_Db_Utils::message_placeholder_processing( $item_name, $appointment, $service );
			       	$item_names[] = $item_name;

					$price_per_service = $service->getPrice( $appointment->getTime() );

					$price_per_service = apply_filters( 'wbk_stripe_price_per_appointment', $price_per_service, $app_id, $service_id );

			        $quantity =  $appointment->getQuantity();

				    $price_format = get_option( 'wbk_payment_price_format', '$#price' );
					if( strpos( $item_name , '#mrange') == FALSE ){
						$html .= '<div class="wbk-col-9-12 wbk-amount-label">'.$item_name.' ('. $quantity . ')</div>';
						$html .= '<div class="wbk-col-3-12 wbk-amount-label wbk-align-right">'. str_replace( '#price', number_format( $price_per_service * $quantity,  get_option( 'wbk_price_fractional', '2' ), get_option( 'wbk_price_separator', '.' ), ''  ), $price_format ) .'</div>';
						$html .= '<div class="wbk-clear"></div>';
					}
					$subtotal +=  $price_per_service * $quantity;
			    }
				if( strpos( $item_name , '#mrange') !== FALSE ){
					$item_name = get_option( 'wbk_payment_item_name', '' );
				    if( $item_name == '' ){
				  	  $item_name = sanitize_text_field( $wbk_wording['payment_item_name'] );
				    }
				    $item_name = str_replace( '#service', $service->getName(), $item_name );
		    $item_name = str_replace( '#date', wp_date( $date_format, $appointment->getTime(), new DateTimeZone( date_default_timezone_get() ) ), $item_name );
		    $item_name = str_replace( '#time', wp_date( $time_format, $appointment->getTime(), new DateTimeZone( date_default_timezone_get() ) ), $item_name );
		    $item_name = str_replace( '#tr', wp_date( $time_format, $appointment->getTime(), new DateTimeZone( date_default_timezone_get() ) ) . ' - ' .  wp_date( $time_format, $appointment->getTime() + $service->getDuration() * 60, new DateTimeZone( date_default_timezone_get() ) ) , $item_name );
				    $item_name = str_replace( '#id',  $appointment->getId(), $item_name );
				    $item_name = str_replace( '#name',  $appointment->getName(), $item_name );
				    $item_name = str_replace( '#email',  $appointment->getEmail(), $item_name );
				    $item_name = str_replace( '#quantity',  $appointment->getQuantity(), $item_name );

					$item_name = str_replace( '#mrange',  '#timerange', $item_name );
					$item_name = WBK_Db_Utils::replaceRanges( $item_name, $app_ids );

					$html .= '<div class="wbk-col-9-12 wbk-amount-label">'.$item_name. '</div>';
					$html .= '<div class="wbk-col-3-12 wbk-amount-label wbk-align-right">'. str_replace( '#price', number_format( $subtotal,  get_option( 'wbk_price_fractional', '2' ), get_option( 'wbk_price_separator', '.' ), ''  ), $price_format ) .'</div>';
					$html .= '<div class="wbk-clear"></div>';
				}

			    $amount_of_discount = 0;
			    $discount_item = '';
			    if( $coupon != FALSE ){
			    	$discount_item = get_option( 'wbk_payment_discount_item', __( 'Discount', 'wbk' ) );
			    	if( $discount_item == '' ){
			    		global $wbk_wording;
			    		$discount_item = $wbk_wording['wbk_payment_discount_item'];
			    	}
			    	if( $coupon[1] > 0 ){
		    	    	$html .= '<div class="wbk-col-9-12 wbk-amount-label">' . $discount_item . '</div>';
				        $html .= '<div class="wbk-col-3-12 wbk-amount-label wbk-align-right">'. str_replace( '#price', number_format( $coupon[1],  get_option( 'wbk_price_fractional', '2' ), get_option( 'wbk_price_separator', '.' ), ''  ), $price_format ) .'</div>';
				        $amount_of_discount = $coupon[1];
				        $subtotal -= $coupon[1];
			    	} elseif( $coupon[2] > 0 ){
			    		$amount_of_discount = ( $subtotal / 100 ) * $coupon[2];
			    		$html .= '<div class="wbk-col-9-12 wbk-amount-label">' . $discount_item . '</div>';
				        $html .= '<div class="wbk-col-3-12 wbk-amount-label wbk-align-right">'. str_replace( '#price', number_format( $amount_of_discount,  get_option( 'wbk_price_fractional', '2' ), get_option( 'wbk_price_separator', '.' ), ''  ), $price_format ) .'</div>';
						$subtotal -= $amount_of_discount;
			    	}
			    }

			    $subtotal_label = get_option( 'wbk_payment_subtotal_title', '' );
			    if( $subtotal_label == '' ){
			    	$subtotal_label = sanitize_text_field( $wbk_wording['subtotal'] );
			    }
		 		$html .= '<div class="wbk-col-9-12 wbk-amount-label">'. $subtotal_label .'</div>';
			    $html .= '<div class="wbk-col-3-12 wbk-amount-label wbk-align-right">' . str_replace( '#price', number_format( $subtotal,  get_option( 'wbk_price_fractional', '2' ), get_option( 'wbk_price_separator', '.' ), ''  ), $price_format ) . '</div>';
			    $html .= '<div class="wbk-clear"></div>';
		        $tax_to_pay = ( ( $subtotal ) / 100 ) * $this->tax;
		        if( is_numeric( $this->tax )  && $this->tax > 0 ){
		        	$html .= '<div class="wbk-col-9-12 wbk-amount-label">'. get_option( 'wbk_tax_label', __( 'Tax', 'wbk' ) ) .'</div>';
		       		$html .= '<div class="wbk-col-3-12 wbk-amount-label wbk-align-right">' . str_replace( '#price', number_format( $tax_to_pay,  get_option( 'wbk_price_fractional', '2' ), get_option( 'wbk_price_separator', '.' ), ''  ), $price_format ) . '</div>';
		        	$html .= '<div class="wbk-clear"></div>';
		        }
		        $html .= '<hr class="wbk-form-separator">';
		        $total_label = get_option( 'wbk_payment_total_title', '' );
		        if( $total_label == '' ){
		        	$total_label = $wbk_wording['total'];
		        }
		        $html .= '<div class="wbk-col-9-12 wbk-amount-label"><strong>'. $total_label .'</strong></div>';
		        $html .= '<div class="wbk-col-3-12 wbk-amount-label wbk-align-right"><strong>'. str_replace( '#price', number_format( $subtotal + $tax_to_pay,  get_option( 'wbk_price_fractional', '2' ), get_option( 'wbk_price_separator', '.' ), ''  ), $price_format ) .'</strong></div>';
        	}
		}
        return $html;
    }
    public function getOrderData( $app_ids, $coupon = FALSE ){

		$subtotal = 0;
        $time_format = WBK_Date_Time_Utils::getTimeFormat();
        $date_format = WBK_Date_Time_Utils::getDateFormat();
        $result_item_name = array();
        foreach( $app_ids as $app_id ){
        	$service_id = WBK_Db_Utils::getServiceIdByAppointmentId( $app_id );
			if( $service_id === false ){
				continue;
			}
			$service = new WBK_Service_deprecated();
			if ( !$service->setId( $service_id ) ) {
				continue;
			}
			if ( !$service->load() ) {
				continue;
			}
	        $appointment = new WBK_Appointment_deprecated();
	        if ( !$appointment->setId( $app_id ) ) {
	           continue;
	        }
	        if ( !$appointment->load() ){
	           continue;
	        }
 	        $price = $service->getPrice( $appointment->getTime() );
			$price = apply_filters( 'wbk_stripe_price_per_appointment', $price, $app_id, $service_id );
	        $quantity =  $appointment->getQuantity();
     		$subtotal += $price * $quantity;
	        $item_name = get_option( 'wbk_payment_item_name', '' );
	        if( $item_name == '' ){
	        	$item_name = sanitize_text_field( $wbk_wording['payment_item_name'] );
	        }
 	        $item_name = str_replace( '#date', wp_date( $date_format, $appointment->getTime(), new DateTimeZone( date_default_timezone_get() ) ), $item_name );
	        $item_name = str_replace( '#time', wp_date( $time_format, $appointment->getTime(), new DateTimeZone( date_default_timezone_get() ) ), $item_name );
	        $item_name = str_replace( '#id',  $appointment->getId(), $item_name );
			$item_name = WBK_Db_Utils::message_placeholder_processing( $item_name, $appointment, $service );
	        $result_item_name[] = $item_name;
	    }


	    if( $coupon != FALSE ){
	    	if( intval( $coupon[1] ) > 0 ){
	    		$subtotal -= intval( $coupon[1] );
	    	}
	    	if( intval( $coupon[2] ) > 0 ){
	    		$discounted = ( $subtotal / 100 ) * intval( $coupon[2] ) ;
	    		$subtotal -= $discounted;
	    	}
	    }
        $tax_to_pay = ( ( $subtotal ) / 100 ) * $this->tax;
        $total = $subtotal + $tax_to_pay;

	    if( self::isCurrencyZeroDecimal( $this->currency ) ){
	        return array( round( $total ), implode( ', ', $result_item_name ) );
	    } else {
	    	return array( round(  $total * 100 ),  implode( ', ', $result_item_name ) );
	    }
    }
    public function charge( $app_ids, $amount, $payment_id, $intent_id = null ){
    	if ( wbk_fs()->is__premium_only() ) {
		    if ( wbk_fs()->can_use_premium_code() ) {
				$coupon = FALSE;
			   	if( count( $app_ids ) > 0 ){
		    		$coupon_id = WBK_Db_Utils::getCouponByAppointmentId( $app_ids[0] );

		    		if( $coupon_id != 0 ){
			    		$coupon_details = WBK_Db_Utils::getCouponDiscount( $coupon_id );
			    		if( $coupon_details != FALSE ){
			    			$coupon = array( $coupon_id, $coupon_details[0], $coupon_details[1] );
			    		}
		    		}
				$appointment = new WBK_Appointment_deprecated();
				   $receipt_email = null;
				   if ( $appointment->setId( $app_ids[0] ) ) {
					   if ( $appointment->load() ){
						   $receipt_email = $appointment->getEmail();
					   }
				   }
		    	}
		 		$orderData = $this->getOrderData( $app_ids, $coupon );
		 		$error_message = get_option( 'wbk_stripe_api_error_message', 'Payment failed: #response' );
                \Stripe\Stripe::setAppInfo("WordPress Webba Booking plugin", "3.8", "https://webba-booking.com/", "pp_partner_EejuT4kiSF2Bav");
                \Stripe\Stripe::setApiVersion("2019-05-16");
				\Stripe\Stripe::setApiKey( $this->api_sectet );

				try {
					if( is_null( $intent_id ) ){
						$intent = \Stripe\PaymentIntent::create(array(
							"amount" => $orderData[0],
							"currency" =>  strtolower( $this->currency ),
							"description" => $orderData[1],
							"payment_method" => $payment_id,
							"receipt_email" => $receipt_email,
							"confirmation_method" => "manual",
							"confirm" => true,
						));
					} else {
						$intent = \Stripe\PaymentIntent::retrieve( $intent_id );
					    $intent->confirm();
					}
					if( $intent->status == 'succeeded' ){
						global $wbk_wording;
						$payment_complete_label  =  get_option( 'wbk_payment_success_message', '' );
						if( $payment_complete_label == ''){
							$payment_complete_label = sanitize_text_field( $wbk_wording['payment_complete'] );
						}
						WBK_Db_Utils::updatePaymentStatusByIds( $app_ids );
						if( self::isCurrencyZeroDecimal( $this->currency ) ){
							$paid_amount = $orderData[0];
						} else {
							$paid_amount =  $orderData[0] / 100;
						}
						foreach( $app_ids as $app_id ){
							WBK_Db_Utils::setPaymentMethodToAppointment( $app_id, 'Stripe' );
						}
						return( array( 1, $payment_complete_label ) );
					} else {

						if ( $intent->status == 'requires_action' && $intent->next_action->type == 'use_stripe_sdk' ){
							return( array( 2,  $intent->client_secret ) );
						} else{
							return array( 0, __( 'unknown error', 'wbk') );
						}
					}
				} catch(\Stripe\Error\Card $e) {
					$body = $e->getJsonBody();
					$err  = $body['error'];
					$error_message = str_replace( '#response', $err['message'], $error_message );
				  	return array( 0, $error_message );
				} catch (\Stripe\Error\RateLimit $e) {
					$error_message = str_replace( '#response',   __( 'too many requests made to the API too quickly', 'wbk'), $error_message );
				  	return array( 0, $error_message );
				} catch (\Stripe\Error\InvalidRequest $e) {
					$error_message = str_replace( '#response', __( 'invalid parameters were supplied to Stripe\'s API', 'wbk'), $error_message );
				  	return array( 0, $error_message );
				} catch (\Stripe\Error\Authentication $e) {
					$error_message = str_replace( '#response', __( 'authentication with Stripe\'s API failed', 'wbk'), $error_message );
				  	return array( 0, $error_message );
				} catch (\Stripe\Error\ApiConnection $e) {
					$error_message = str_replace( '#response', __( 'network communication with Stripe failed', 'wbk'), $error_message );
				  	return array( 0, $error_message );
				} catch (\Stripe\Error\Base $e) {
					$error_message = str_replace( '#response', __( 'generic Stripe error', 'wbk'), $error_message );
					return array( 0, $error_message );
				} catch (Exception $e) {
					$error_message = str_replace( '#response', __( 'unknown error', 'wbk'), $error_message );
					return array( 0, $error_message );
				}
		    }
		}
		return array( 0, __( 'Payment method not supported') );
    }


}
?>
