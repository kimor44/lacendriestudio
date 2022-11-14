<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// webba booking PayPal integration class
if ( wbk_fs()->is__premium_only() ) {
    if ( wbk_fs()->can_use_premium_code() ) {
    	require 'autoload.php';

    }
}
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\ExecutePayment;
use PayPal\Api\PaymentExecution;
class WBK_PayPal{
	protected
	$apiContext;
	protected
	$currency;
	protected
	$tax;
	protected
	$fee;
	protected
	$referer;
	protected
	$experience_profile_id;
	public function init( $referer, $appointment_ids ){
		if ( wbk_fs()->is__premium_only() ) {
		    if ( wbk_fs()->can_use_premium_code() ) {
		    	if ( $referer === '' ){
					return FALSE;
				}
				$this->referer = $referer;
				$clientId = '';
		        $clientSecret = '';
		        $mode = '';
		        $loglevel = 'DEBUG';
		        if( get_option( 'wbk_paypal_mode', 'sandbox') == 'Sandbox' ||  get_option( 'wbk_paypal_mode', 'sandbox') == 'sandbox' )  {
		            $clientId = get_option('wbk_paypal_sandbox_clientid', '');
		            $clientSecret = get_option('wbk_paypal_sandbox_secret', '');
		            $mode = 'sandbox';
		            $loglevel = 'DEBUG';
		        } else {
		            if( get_option( 'wbk_paypal_mode', 'sandbox') == 'Live' ||  get_option( 'wbk_paypal_mode', 'sandbox') == 'live' ){
		                $clientId = get_option('wbk_paypal_live_clientid', '');
		                $clientSecret = get_option('wbk_paypal_live_secret', '');
		                $mode = 'live';
		                $loglevel = 'INFO';
		            }
		        }
                $paypal_settings = array( 'clientid' => $clientId, 'secret' => $clientSecret, 'mode' => $mode );
                $paypal_settings = apply_filters( 'wbk_paypal_settings', $paypal_settings, $appointment_ids );
                $clientId = $paypal_settings['clientid'];
                $clientSecret =  $paypal_settings['secret'];
                $mode =  $paypal_settings['mode'];

		        if( $clientId == '' || $clientSecret == '' || $mode == '' ){

		            return FALSE;
		        }
			    $apiContext = new ApiContext(
			        new OAuthTokenCredential(
			            $clientId,
			            $clientSecret
			        )
			    );
			    $apiContext->setConfig(
			        array(
			            'mode' =>  $mode,
			            'log.LogEnabled' => true,
			            'log.FileName' =>  __DIR__ . '/PayPal.log',
			            'log.LogLevel' => $loglevel,
			            'cache.enabled' => false,
			        )
			    );
			    $this->apiContext  = $apiContext;
			    $this->currency = get_option( 'wbk_paypal_currency', 'USD' );
			    $this->tax = get_option( 'wbk_paypal_tax', 0 );
				if ( get_option( 'wbk_paypal_hide_address', 'disabled' ) == 'enabled' ){
				    if (  $referer != false ){
				    	$this->experience_profile_id = $this->getWebProfileId();
				   		if ( $this->experience_profile_id == FALSE ){

				    		return FALSE;
				    	}
				    }
				}
			    return TRUE;
		    }
		}
		return FALSE;
	}
    public function createPaymentPaypal( $item_names, $price, $quantity, $sku, $amount_of_discount, $discount_item_name, $service_fee ){
    	if ( wbk_fs()->is__premium_only() ) {
		    if ( wbk_fs()->can_use_premium_code() ) {
		    	$payer = new Payer();
				$payer->setPaymentMethod("paypal");
				$arr_items = array();
				$i_cur_item = -1;
				$price_total = 0;

				foreach( $price as $key => $value ){
					$price[$key] = $this->conversion( $value );
				}

				foreach( $item_names as $item_name ) {
					$i_cur_item++;
					$item1 = new Item();
                    $price_rounded =  round( $price[ $i_cur_item ], get_option( 'wbk_price_fractional', '2' )  );
					$item1->setName( $item_name )
				     	  ->setCurrency( $this->currency )
					      ->setQuantity( $quantity[ $i_cur_item ] )
					      ->setSku( $sku[ $i_cur_item ]  )
					      ->setPrice( $price_rounded );
					$arr_items[] = $item1;
					$price_total += $quantity[ $i_cur_item ] * $price_rounded;
				}


				if( $amount_of_discount > 0 ){
					$amount_of_discount = $this->conversion( $amount_of_discount );
					$itemds = new Item();
					$itemds->setName( $discount_item_name )
				     	   ->setCurrency( $this->currency )
					       ->setQuantity( 1 )
				           ->setSku( 1 )
					       ->setPrice( $amount_of_discount * -1 );
					$arr_items[] = $itemds;
				}



				$itemList = new ItemList();
				$itemList->setItems( $arr_items );
				$details = new Details();

				$tax = ( ( $price_total - $amount_of_discount  ) / 100 ) * $this->tax;

				$details->setShipping(0)
					    ->setTax($tax)
					    ->setSubtotal( $price_total - $amount_of_discount  );


                $sum = $price_total + $tax - $amount_of_discount;
                if( get_option( 'wbk_do_not_tax_deposit', '' ) == 'true' && $service_fee > 0 ){
                    $details->setHandlingFee( $service_fee );
                    $sum += $service_fee;
                }
                $sum = round( $sum, get_option( 'wbk_price_fractional', '2' ) );
				$amount = new Amount();
				$amount->setCurrency($this->currency)
				    ->setTotal( $sum )
				    ->setDetails($details);
				$transaction = new Transaction();
				$transaction->setAmount($amount)
				    ->setItemList($itemList)
				    ->setInvoiceNumber(uniqid());
				$baseUrl = $this->referer;
				$redirectUrls = new RedirectUrls();

				$cancel_token = bin2hex(openssl_random_pseudo_bytes(16));

				$redirectUrls->setReturnUrl("$baseUrl?pp_aprove=true")
				   			 ->setCancelUrl("$baseUrl?pp_aprove=false&cancel_token=" . $cancel_token );
				$payment = new Payment();
				$payment->setIntent("sale")
				    ->setPayer($payer)
				    ->setRedirectUrls($redirectUrls)
				    ->setTransactions(array($transaction));
				if ( get_option( 'wbk_paypal_hide_address', 'disabled' ) == 'enabled' ){
					$payment->setExperienceProfileId( $this->experience_profile_id );
				}
				try {
					$payment->create( $this->apiContext )	;

				} catch (Exception $ex) {

					return FALSE;
				}
				return  array( $payment, $cancel_token );

		    }
		}
		return FALSE;
    }
    public function createPayment( $method, $app_ids, $coupon  ){
    	if ( wbk_fs()->is__premium_only() ) {
		    if ( wbk_fs()->can_use_premium_code() ) {
		    	global $wbk_wording;
		        if ( $method != 'paypal' && $method != 'paypal_cc' ){
		            return -1;
		        }
		        $payment_details = get_option( 'wbk_payment_details_title',  '' );
		        if( $payment_details == '' ){
		        	$payment_details = sanitize_text_field( $wbk_wording['payment_details'] );
		        }
		        $html = '<div class="wbk-details-sub-title">' . $payment_details . '</div>
			                 <hr class="wbk-form-separator">';
				$subtotal = 0;
				$item_names = array();
				$prices 	= array();
				$quantities = array();
				$services 	= array();
		        foreach( $app_ids as $app_id ){
					$service_id = WBK_Db_Utils::getServiceIdByAppointmentId( $app_id );
					if( $service_id === false ){
						return -2;
					}
					$service = new WBK_Service_deprecated();
					if ( !$service->setId( $service_id ) ) {
						return -3;
					}
					if ( !$service->load() ) {
						return -3;
					}
			        $appointment = new WBK_Appointment_deprecated();
			        if ( !$appointment->setId( $app_id ) ) {
			            return -4;
			        }
			        if ( !$appointment->load() ){
			            return -4;
			        }
			        $item_name = get_option( 'wbk_payment_item_name', '' );
			        if( $item_name == '' ){
			        	$item_name = sanitize_text_field( $wbk_wording['payment_item_name'] );
			        }
			        $time_format = WBK_Date_Time_Utils::getTimeFormat();
			        $date_format = WBK_Date_Time_Utils::getDateFormat();


        	        $item_name = str_replace( '#date', wp_date( $date_format, $appointment->getTime(), new DateTimeZone( date_default_timezone_get() ) ), $item_name );
        	        $item_name = str_replace( '#time', wp_date( $time_format, $appointment->getTime(), new DateTimeZone( date_default_timezone_get() ) ), $item_name );
        	        $item_name = str_replace( '#tr', wp_date( $time_format, $appointment->getTime(), new DateTimeZone( date_default_timezone_get() ) ) . ' - ' . wp_date( $time_format, $appointment->getTime() + $service->getDuration() * 60, new DateTimeZone( date_default_timezone_get() ) ) , $item_name );
			        $item_name = str_replace( '#id',  $appointment->getId(), $item_name );
			        $item_name = str_replace( '#name',  $appointment->getName(), $item_name );
			        $item_name = str_replace( '#email',  $appointment->getEmail(), $item_name );
			        $item_name = str_replace( '#quantity',  $appointment->getQuantity(), $item_name );
                    $item_name = WBK_Db_Utils::message_placeholder_processing( $item_name, $appointment, $service );

			       	$item_names[] = $item_name;

                    $booking = new WBK_Booking(  $appointment->getId() );
                    if( $booking->get_name() == '' ){
                        return -4;
                    }
                    $price = $booking->get_price();
			        $quantity =  $appointment->getQuantity();
			        $prices[] = $price;
			        $quantities[] = $quantity;
			        $services[] = $service_id;

			        $price_format = get_option( 'wbk_payment_price_format', '$#price' );

			        $html .= '<div class="wbk-col-9-12 wbk-amount-label">'.$item_name.' ('. $quantity . ')</div>';
			        $html .= '<div class="wbk-col-3-12 wbk-amount-label wbk-align-right">'. str_replace( '#price', number_format( $booking->get_price() * $quantity,  get_option( 'wbk_price_fractional', '2' ), get_option( 'wbk_price_separator', '.' ), ''  ), $price_format ) .'</div>';
			        $html .= '<div class="wbk-clear"></div>';
		     		$subtotal +=  $booking->get_price() * $quantity;

			    }
                $service_fee = WBK_Price_Processor::get_servcie_fees( $app_ids );
                if( get_option( 'wbk_do_not_tax_deposit', '' ) != 'true' ){
        			$subtotal += $service_fee[0];
                    if( $service_fee > 0 ){
                        $item_names[] = implode( ', ', $service_fee[2] );
                        $prices[] = $service_fee[0];
                        $quantities[]  = 1;
                        $services[] = 'service fee';
                    }
                }
                if( $service_fee[0] > 0 ){
                    $html .= '<div class="wbk-col-9-12 wbk-amount-label">'. implode( ', ', $service_fee[2] ) .'</div>';
                    $html .= '<div class="wbk-col-3-12 wbk-amount-label wbk-align-right">' . str_replace( '#price', number_format( $service_fee[0],  get_option( 'wbk_price_fractional', '2' ), get_option( 'wbk_price_separator', '.' ), ''  ), $price_format ) . '</div>';
                    $html .= '<div class="wbk-clear"></div>';
                }

			    $subtotal_label = get_option( 'wbk_payment_subtotal_title', '' );
			    if( $subtotal_label == '' ){
			    	$subtotal_label = sanitize_text_field( $wbk_wording['subtotal'] );
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
					if( $subtotal < 0 ){
						return -9;
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

                $total = $subtotal + $tax_to_pay;
                if( get_option( 'wbk_do_not_tax_deposit', '' ) == 'true' ){
                    $total += $service_fee[0];
                }
		        $html .= '<div class="wbk-col-3-12 wbk-amount-label wbk-align-right"><strong>'. str_replace( '#price', number_format( $total,  get_option( 'wbk_price_fractional', '2' ),  get_option( 'wbk_price_separator', '.' ), ''  ), $price_format ) .'</strong></div>';
                if ( $method == 'paypal' ){
		            $payment = $this->createPaymentPaypal( $item_names, $prices, $quantities, $services, $amount_of_discount, $discount_item, $service_fee[0] );
		            if( $payment === FALSE ){
		                return -5;
		            } else {
						foreach ( $app_ids as $app_id ) {
							if ( WBK_Db_Utils::setPaymentId( $app_id, $payment[0]->getId() ) === FALSE ){
			            		return -6;
			            	}
							if ( WBK_Db_Utils::setPaymentCancelToken( $app_id, $payment[1] ) === FALSE ){
			            		return -6;
			            	}

						}
						$approve_btn = WBK_Validator::alfa_numeric( get_option( 'wbk_payment_approve_text', '' ) );

		                $html .= '<input type="button" class="wbk-button wbk-width-100 wbk-mt-10-mb-10 wbk-approval-link" data-link="' . $payment[0]->getApprovalLink() . '"  value="' . $approve_btn . '">';
		            }
		        }
		        if( get_option( 'wbk_paypal_auto_redirect', 'disabled' ) == 'enabled' ){
		        	return $payment[0]->getApprovalLink();
		        }
		        return $html;
			}
		}
		return -1;
    }
    protected function createWebProfile(){
    	if ( wbk_fs()->is__premium_only() ) {
		    if ( wbk_fs()->can_use_premium_code() ) {
		        $flowConfig = new \PayPal\Api\FlowConfig();
		        $flowConfig->setLandingPageType("Billing");
		        $flowConfig->setBankTxnPendingUrl($this->referer);
		        $presentation = new \PayPal\Api\Presentation();
	 	        $inputFields = new \PayPal\Api\InputFields();
		        $inputFields->setAllowNote(false)
		            ->setNoShipping(1)
		            ->setAddressOverride(0);
		        // #### Payment Web experience profile resource
		        $webProfile = new \PayPal\Api\WebProfile();
		        // Name of the web experience profile. Required. Must be unique
		        $webProfile->setName(uniqid())
		            // Parameters for flow configuration.
		            ->setFlowConfig($flowConfig)
		            // Parameters for style and presentation.
		            ->setPresentation($presentation)
		            // Parameters for input field customization.
		            ->setInputFields($inputFields);
		        try {
		            // Use this call to create a profile.
		            $createProfileResponse = $webProfile->create($this->apiContext);
		            $createProfileResponse = json_decode($createProfileResponse);
		            $web_profile_id = $createProfileResponse->id;
		            update_option( 'wbk_paypal_profile_id', $web_profile_id);
		            return $web_profile_id;
		        } catch (\PayPal\Exception\PayPalConnectionException $ex) {
		        	return FALSE;
		        }
		    }
		}
		return FALSE;
    }
    public function getWebProfileId(){
    	if ( wbk_fs()->is__premium_only() ) {
		    if ( wbk_fs()->can_use_premium_code() ) {
		    	$web_profile_id  = get_option('wbk_paypal_profile_id', '');
	 			if( $web_profile_id != '' ){
					try {
						$webProfile = \PayPal\Api\WebProfile::get( $web_profile_id, $this->apiContext );
						return $web_profile_id;
					} catch (\PayPal\Exception\PayPalConnectionException $ex) {
						return $this->createWebProfile();
					}
	 			}
	 			return $this->createWebProfile();

		    }
		}
		return '';
    }
	public function executePayment( $paymentId, $payerId ){
		if ( wbk_fs()->is__premium_only() ) {
		    if ( wbk_fs()->can_use_premium_code() ) {
		    	$payment = Payment::get( $paymentId, $this->apiContext );
		 		$execution = new PaymentExecution();
				$execution->setPayerId( $payerId );
				$transaction = new Transaction();
				$amount = new Amount();
				$details = new Details();
				$app_ids =  WBK_Db_Utils::getAppointmentIdsByPaymentId( $paymentId );
				$coupon_result = FALSE;
				if( count( $app_ids ) > 0 ){
					$coupon = WBK_Db_Utils::getCouponByAppointmentId( $app_ids[0] );
					if( $coupon != 0 ){
						$coupon_result = WBK_Db_Utils::getCouponDiscount( $coupon );
					}
				}
				$price_total = 0;
				foreach ( $app_ids as $appointment_id ){
			        $appointment = new WBK_Appointment_deprecated();
			        if ( !$appointment->setId( $appointment_id ) ) {
			            continue;
			        }
			        if ( !$appointment->load() ){
			            continue;
			        }
					$service = WBK_Db_Utils::initServiceById( $appointment->getService() );
					if( $service ==  FALSE ){
						continue;
					}
                    $booking = new WBK_Booking( $appointment_id );
                    if( $booking->get_name() == '' ){
                        continue;
                    }
					$price_total += $booking->get_price() * $booking->get_quantity();
				}
                $service_fee = WBK_Price_Processor::get_servcie_fees( $app_ids );
                if( get_option( 'wbk_do_not_tax_deposit', '' ) != 'true'  ){
        			$price_total += $service_fee[0];
                }

				if( $coupon_result != FALSE ){
					if( $coupon_result[0] != 0 ){
						$price_total -=  $coupon_result[0];
					}
					if( $coupon_result[1] != 0 ){
						$discount = ( $price_total / 100 ) *  $coupon_result[1];
						$price_total -= $discount;
					}
				}
				$tax = (  $price_total / 100 ) * $this->tax;
				$details->setShipping(0)
				        ->setTax( $this->conversion( $tax ) )
				        ->setSubtotal(  $this->conversion( $price_total ) );

				$amount->setCurrency( $this->currency );
                if( get_option( 'wbk_do_not_tax_deposit', '' ) == 'true' && $service_fee[0] > 0 ){
                    $price_total += $service_fee[0];
                    $details->setHandlingFee( $service_fee[0] );
                }
				$amount->setTotal(  $this->conversion( $price_total + $tax ) );
				$amount->setDetails($details);
				$transaction->setAmount($amount);
	 			$execution->addTransaction($transaction);

				try {
			        $result = $payment->execute($execution, $this->apiContext);
			        if( count( $app_ids ) > 0 ){
                        if( get_option( 'wbk_zoom_when_add', 'onbooking' ) == 'onpaymentorapproval' ){
                            foreach ( $app_ids as $app_id ){
                                $wbk_zoom = new WBK_Zoom();
                                $wbk_zoom->add_meeting( $app_id );
                            }
                        }
						WBK_Db_Utils::updatePaymentStatus( $paymentId, ( $price_total + $tax ) );
			        	WBK_Db_Utils::increeaseCouponUsage( $app_ids[0] );
					}
					if( get_option( 'wbk_gg_when_add', 'onbooking' ) == 'onpaymentorapproval' ){
						date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
						foreach ( $app_ids as $app_id ){
							if( !WBK_Db_Utils::idEventAddedToGoogle( $app_id ) ){
								$service_id = WBK_Db_Utils::getServiceIdByAppointmentId( $app_id );
			    	            WBK_Db_Utils::addAppointmentDataToGGCelendar( $service_id, $app_id );
							}

						}
						date_default_timezone_set( 'UTC' );
	                }
					foreach ( $app_ids as $app_id ){
						WBK_Db_Utils::setPaymentMethodToAppointment( $app_id, 'PayPal' );
					}
                    WBK_Db_Utils::updatePaymentStatusByIds( $app_ids );
				} catch (Exception $ex) {
					return false;
				}
				return true;

		    }
		}
		return false;
	}
	static function	renderPaymentMethods( $service_id, $appointment_ids, $button_class = '' ){
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
		    if ( $service->getPayementMethods() == '' ){
		      	return '';
		    }
		    $arr_items = json_decode( $service->getPayementMethods() );
			if( !in_array( 'paypal', $arr_items) ){
				return '';
			}
		}
		$html = '';
		$paypal_btn_text = WBK_Validator::alfa_numeric( get_option( 'wbk_payment_pay_with_paypal_btn_text', '' ) );

		$html .= '<input class="wbk-button wbk-width-100 wbk-mt-10-mb-10 wbk-payment-init' . $button_class .'" data-method="paypal" data-app-id="'. implode(',',  $appointment_ids ) . '"  value="' . $paypal_btn_text . '  " type="button">';
		return $html;
	}
	private function conversion( $price ){
		$multiplier = get_option( 'wbk_paypal_multiplier', '' );
		if( $multiplier == '' ){
			return $price;
		} elseif ( filter_var( $multiplier, FILTER_VALIDATE_FLOAT) && $multiplier > 0  ) {
			return number_format( floatval( $multiplier ) * floatval( $price ), 2, '.', ''  );
		}
		return $price;
	}
}
?>
