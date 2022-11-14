<?php
if ( ! defined( 'ABSPATH' ) ) exit;

use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;

class WBK_Sms{
    public static function send( $bookings, $message ){
        if ( wbk_fs()->is__premium_only() ) {
            if ( wbk_fs()->can_use_premium_code() ) {
                $message = WBK_Placeholder_Processor::process_placeholders( $message, $bookings );        
                $account_sid = get_option( 'wbk_twilio_account_sid' );
                $auth_token = get_option( 'wbk_twilio_auth_token' );
                $from_origin = get_option( 'wbk_twilio_phone_number' );
                if( $from_origin != '' ){
                    if( substr( $from_origin, 0, 1 ) == '+' ){
                        $from =  '+' . preg_replace( '/[^0-9]/', '', $from_origin );
                    } else {
                        $from = $from_origin;
                    }
                } else {
                    $from = '';
                }
                $booking_id = null;
                if( is_array( $bookings ) && count( $bookings ) > 0 ){
                    $booking_id = $bookings[0];
                } elseif ( is_numeric( $bookings ) ) {
                    $booking_id = $bookings;
                }
                if( $booking_id == null ){

                    return;
                }
                $booking = new WBK_Booking( $booking_id );
                if( $booking->get_name() == '' ){
                    return;
                }
                $to = '+' . preg_replace( '/[^0-9]/', '', $booking->get_phone() );
                if( $to =='' || $message == '' || $from == '' || $account_sid == '' || $auth_token == '' ){
                    return;
                }
                $client = new Client( $account_sid, $auth_token );
                try {
                    if( substr( $from, 0, 1 ) == '+' ){
                        $client->messages->create(
                            $to,
                            array(
                                'from' => $from,
                                'body' => $message
                            )
                        );
                    } else {
                        $client->messages->create(
                            $to,
                            array(
                                'messagingServiceSid' => $from,
                                'body' => $message
                            )
                        );
                    }

                } catch ( TwilioException $e ) {
                    error_log( 'Failed to send SMS: ' . $e->getMessage() );

                }
            }
        }
    }
}
