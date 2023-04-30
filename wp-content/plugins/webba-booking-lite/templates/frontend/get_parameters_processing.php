<?php
if ( !defined( 'ABSPATH' ) ) exit;
date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
$html = '';
if( isset( $_GET['ggeventadd'] ) ){
	$ggeventadd = $_GET['ggeventadd'];
	$ggeventadd = WBK_Validator::get_param_sanitize( $ggeventadd );
	$booking_ids = WBK_Model_Utils::get_booking_ids_by_group_token( $ggeventadd );
	$booking_ids = array_unique( $booking_ids );
	$html = WBK_Renderer::load_template( 'frontend/add_to_gg_customer_init', array( $booking_ids ), false );
}

if( isset( $_GET['code'] ) ){
	$code = $_GET['code'];
	if( isset( $_SESSION['wbk_ggeventaddtoken'] ) && $_SESSION['wbk_ggeventaddtoken']  != '' ){
		$token = WBK_Validator::get_param_sanitize( $_SESSION['wbk_ggeventaddtoken'] );
		$booking_ids = WBK_Model_Utils::get_booking_ids_by_group_token( $token );
		$booking_ids = array_unique( $booking_ids );
		$adding_result = WBK_Google::add_booking_to_customer_calendar( $booking_ids, $code );
		$html = WBK_Renderer::load_template( 'frontend/add_to_gg_customer_confirm', array( $booking_ids, $adding_result ), false );
	}
}

if( isset( $_GET['paypal_status'] ) && is_numeric( $_GET['paypal_status'] ) ){
	$paypal_status = intval( $_GET['paypal_status'] );
	if( $paypal_status >= 1 && $paypal_status <= 5 ){
		$html = WBK_Renderer::load_template( 'frontend/paypal_result_status', array( $paypal_status ), false );
	}
}
 
if( get_option( 'wbk_allow_manage_by_link', 'no' ) == 'yes' ){
	if( isset( $_GET['admin_approve'] ) ){
		$cancelation = $_GET['admin_approve'];
		$cancelation = WBK_Validator::get_param_sanitize( $cancelation );
		$booking_ids = WBK_Model_Utils::get_booking_ids_by_group_admin_token( $cancelation );

		$bf = new WBK_Booking_Factory();
		$i = $bf->set_as_approved( $booking_ids );
 
		if( $i > 0 ){
			$html = WBK_Renderer::load_template( 'frontend/link_approval_result', array( $i ), false );
		}
	}
}

if( get_option( 'wbk_allow_manage_by_link', 'no' ) == 'yes' ){
	if( isset( $_GET['admin_cancel'] ) ){

		$cancelation = $_GET['admin_cancel'];
		$cancelation = WBK_Validator::get_param_sanitize( $cancelation );
		$booking_ids = WBK_Model_Utils::get_booking_ids_by_group_admin_token( $cancelation );

		$valid = false;
		$i = 0;

		$customer_notification_mode = get_option( 'wbk_email_customer_cancel_multiple_mode', 'foreach' );
		$multiple = false;
		if( get_option( 'wbk_multi_booking' ) == 'enabled' || get_option( 'wbk_multi_booking' ) == 'enabled_slot' ){
			$multiple = true;
		}
		if( $multiple && $customer_notification_mode == 'one' && get_option( 'wbk_email_customer_appointment_cancel_status', '' ) == 'true' ){
			if( count( $booking_ids ) > 0 ){
				$appointment = new WBK_Appointment_deprecated();
				if ( $appointment->setId( $booking_ids[0] ) ) {
					if ( $appointment->load() ) {
						$recipient = $appointment->getEmail();
						$noifications = new WBK_Email_Notifications( null, null );
						$subject = get_option( 'wbk_email_customer_appointment_cancel_subject', '' );
						$message = get_option( 'wbk_email_customer_appointment_cancel_message', '' );
						$noifications->sendMultipleNotification( $booking_ids, $message, $subject, $recipient );
						// send to administrator
						$service_id = $appointment->getService();
						$service = WBK_Db_Utils::initServiceById( $service_id );
						if( $service != FALSE) {
							$subject = get_option( 'wbk_email_adimn_appointment_cancel_subject', '' );
							$message = get_option( 'wbk_email_adimn_appointment_cancel_message', '' );
							$noifications->sendMultipleNotification( $booking_ids, $message, $subject, $service->getEmail() );
							$super_admin_email = get_option( 'wbk_super_admin_email', '' );
							if ( $super_admin_email != '' ) {
								$noifications->sendMultipleNotification( $booking_ids, $message, $subject, $super_admin_email );
							}
						}
					}
				}
			}
		}

		$bf = new WBK_Booking_Factory();
		 
		foreach( $booking_ids as $booking_id ){	 
			$bf->destroy( $booking_id, 'administrator', true );
			$i++;
		}
		if( $i > 0 ){
			$html = WBK_Renderer::load_template( 'frontend/link_cancellation_result', array( $i ), false );
		}
	}
}

if( isset( $_GET['cancelation'] ) ){

	$cancelation =  $_GET['cancelation'];
	$cancelation = WBK_Validator::get_param_sanitize( $cancelation );
	$booking_ids_not_filtered = WBK_Model_Utils::get_booking_ids_by_group_token( $cancelation );
	$booking_ids = array();
	$tokens = array();
	if( count( $booking_ids_not_filtered ) == 0  ){
		$valid = false;
		echo WBK_Renderer::load_template( 'frontend/message_container', array( __( 'Bookings not found.', 'wbk' ) ), false );
	 
	} else {
		$title_all = array();
		$valid_items = 0;
		foreach( $booking_ids_not_filtered as $booking_id ){
			$booking = new WBK_Booking( $booking_id );
			if( !$booking->is_loaded() ){
				continue;
			}
			$title_this = get_option( 'wbk_appointment_information', '' );
			$title = WBK_Placeholder_Processor::process_placeholders( $title_this, $booking_id );
			if( $booking->get('status') == 'paid' || $booking->get('status') == 'paid_approved'  ){
			    if( get_option( 'wbk_appointments_allow_cancel_paid', 'disallow' ) == 'disallow' ){
			        global $wbk_wording;
			        $paid_error_message = get_option( 'wbk_booking_couldnt_be_canceled',  '' );
			        $title .= ' - ' .  esc_html( $paid_error_message );
			        $title_all[] = $title;	
			        continue;
			    }
			}
			// check buffer
			$buffer = get_option( 'wbk_cancellation_buffer', '' );
			if( $buffer != '' ){
				if( intval( $buffer ) > 0 ){
					$buffer_point = ( intval( $booking->get_start() - intval( $buffer ) * 60 ) );
					if( time() >  $buffer_point ){
						$cancel_error_message = get_option( 'wbk_booking_couldnt_be_canceled2', '' );
						$title .= ' - ' . esc_html( $cancel_error_message );
						$title_all[] = $title;
						continue;
					}
				}
			}
			// end check buffer
			$valid_items++;
			$title_all[] = $title;
			$booking_ids[] = $booking_id;
			$tokens[] = $booking->get('token');
		}
		$title = implode( '<br>', $title_all );
		$html = WBK_Renderer::load_template( 'frontend/cancellation_form', array( $valid_items, $title, $tokens ), false );

	}
}

if( isset( $_GET['order_payment'] ) ){

	$order_payment =  $_GET['order_payment'];
 	$order_payment = WBK_Validator::get_param_sanitize( $order_payment );
 	$booking_ids = WBK_Model_Utils::get_booking_ids_by_group_token( $order_payment );

	if( count( $booking_ids ) == 0 ){
		$valid = false;
	} else {
		$title = array();
		$found_valid_bookings = 0;
		foreach( $booking_ids as $booking_id ){
			$booking = new WBK_Booking( $booking_id );
			if( !$booking->is_loaded() ){
				continue;
			}
			$valid = true;
			$service_id = $booking->get_service();
			$service = new WBK_Service( $service_id );

			if( $booking->get('status') != 'paid' && $booking->get('status') != 'paid_approved' && $booking->get('status') != 'woocommerce' ){
				$title_this = get_option( 'wbk_appointment_information', '' );
				$title_this = WBK_Placeholder_Processor::process_placeholders( $title_this, array( $booking_id ) );
				$title[] = $title_this;
				$found_valid_bookings++;
			}

		}
		$title = implode( '<br>', $title );
		if( $found_valid_bookings == 0 ){
			$title = esc_html( get_option( 'wbk_nothing_to_pay_message', '' ) );
		} else {
			$payment_methods_allowed =  WBK_Model_Utils::get_payment_methods_for_bookings( $booking_ids );
			$html =  WBK_Renderer::load_template( 'frontend/payment_init', array( $payment_methods_allowed, $booking_ids, 'wbk_payment_button_link' ), false );

		}

	}

	 
}
date_default_timezone_set( 'UTC' );
echo $html;
?>
