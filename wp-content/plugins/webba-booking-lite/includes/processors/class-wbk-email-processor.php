<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class WBK_Email_Processor {
  
    public static function send( $bookings, $action ){

        
        if( !is_array( $bookings ) || count( $bookings ) == 0 ){
             return;
        } 
        
        $booking = new WBK_Booking( $bookings[0] );
        if( !$booking->get_service() ){
             return;
        }

        $service = new WBK_Service( $booking->get_service() );
        if( !$service->is_loaded() ){
             return;
        }
         
        $headers[] = 'From: ' . get_option( 'wbk_from_name' ) . ' <' .  $from_email = get_option( 'wbk_from_email' ) .'>';
        $queue = array();
        switch ( $action) {
            case 'approval':
                
                $message = get_option('wbk_email_customer_approve_message');
                $template_id = $service->get_on_approval_template();
                if( $template_id != false ){
                    $template_obj = new WBK_Email_Template( $template_id );
                    $message = $template_obj->get_template();
                }

                $current_time_zone = date_default_timezone_get();
                date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
                if( get_option( 'wbk_multi_booking', '' )  == 'enabled' ){
                    $message = WBK_Placeholder_Processor::process_placeholders( $message, $bookings );                        
                } else {
                    $message = WBK_Placeholder_Processor::process_placeholders( $message, $bookings[0] );                       
                }           
                $subject = WBK_Placeholder_Processor::process_placeholders( get_option( 'wbk_email_customer_approve_subject' ), $bookings[0] );    
                date_default_timezone_set( $current_time_zone );

                $queue[] = array( 'address' => $booking->get( 'email' ), 'message' => $message, 'subject' => $subject );                
                if( get_option('wbk_email_customer_approve_copy_status' ) == 'true' ){
                    
                    if( $service->is_loaded() ){
                         $queue[] = array( 'address' => $service->get( 'email' ), 'message' => $message, 'subject' => $subject );      
                    }
                }
                break;
            default:
                break;
        }
        foreach( $queue as $notification ){
            add_filter( 'wp_mail_content_type', 'wbk_wp_mail_content_type' );
            wp_mail( $notification['address'], $notification['subject'], $notification['message'], $headers );
            remove_filter( 'wp_mail_content_type', 'wbk_wp_mail_content_type' );
            
        }
    }    
}

function wbk_wp_mail_content_type(){
    return 'text/html';
}