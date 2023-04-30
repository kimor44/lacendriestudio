<?php
if ( !defined( 'ABSPATH' ) ) exit;
$timeslots = $data[0];
$service_id = $data[1];
$locked_time_slots = $data[2];

$service = new WBK_Service( $service_id );


$html_schedule = '';
foreach ( $timeslots as $timeslot ) {
    if( $service->get_quantity() == 1 && $timeslot->get_free_places() == 0 && $timeslot->get_status() == 0 ){
        $timeslot->set_status( -2 );
    }
    $time = $timeslot->get_formated_time_backend();
    $status_class = '';
    $time_controls = WBK_Renderer::load_template( 'backend/schedule_time_lock_link', array( $service_id, $timeslot->get_start() ), false );
    $time_controls = '<a id="app_add_' . esc_attr( $service_id . '_' . $timeslot->getStart() ) . '"><span class="dashicons dashicons-welcome-add-page"></span></a>' . $time_controls;
    if( is_array( $timeslot->get_status() ) ){
        $time_controls = '';
        $items_booked = 0;
        foreach ( $timeslot->get_status() as $booking_id ) {
            $booking = new WBK_Booking( $booking_id );
            if ( !$booking->is_loaded() ) {
                continue;
            };
            $items_booked += $booking->get_quantity();
            $time_controls .= '<a class="wbk-appointment-backend" id="wbk_appointment_' . esc_attr(  $booking_id . '_'. $service_id )  .'_1" >' . esc_html( $booking->get_name() ) . ' ('. esc_html( $booking->get_quantity() ) . ')' . '</a> ';
        }
        if ( $items_booked < $service->get_quantity( $timeslot->get_start() ) ) {
            $time_controls .= '<a id="app_add_' . esc_attr( $service_id . '_' . $timeslot->get_start() ). '"><span class="dashicons dashicons-welcome-add-page"></span></a>';
        }
        if ( in_array( $timeslot->get_start(), $locked_time_slots ) ) {
            $status_class = 'red_font';
            $time_controls .= WBK_Renderer::load_template( 'backend/schedule_time_unlock_link', array( $service_id, $timeslot->get_start() ), false );
        } else {
            $time_controls .= WBK_Renderer::load_template( 'backend/schedule_time_lock_link', array( $service_id, $timeslot->get_start() ), false );
        }
    }
   
    if ( $timeslot->get_status() == -2 || ( in_array( $timeslot->get_start(), $locked_time_slots ) && !is_array( $timeslot->get_status() )  ) ) {
        $status_class = 'red_font';
        $time_controls = WBK_Renderer::load_template( 'backend/schedule_time_unlock_link', array( $service_id, $timeslot->get_start() ), false );
        $booking_ids = WBK_Model_Utils::get_booking_ids_by_service_and_time( $service_id,   $timeslot->get_start() );
        foreach ( $booking_ids as $booking_id ) {
            $booking = new WBK_Booking( $booking_id );
            if ( !$booking->is_loaded() ) {
                continue;
            };
            $time_controls .= '<a class="wbk-appointment-backend" id="wbk_appointment_' . esc_attr( $booking_id ) . '_'. esc_attr( $service_id ) .'_1" >' . esc_html( $booking->get_name() ) . '</a>';
        }
        
    }
         
    if ( $timeslot->get_status() > 0 && !is_array(  $timeslot->get_status() ) ) {
            $booking_ids = WBK_Model_Utils::get_booking_ids_by_service_and_time( $service_id,   $timeslot->get_start() );
            $time_controls = '';
            foreach ( $booking_ids as $booking_id ) {
                $booking = new WBK_Booking( $booking_id );
                if ( !$booking->is_loaded() ) {
                    continue;
                };
                $time_controls .= '<a class="wbk-appointment-backend" id="wbk_appointment_' . esc_attr( $booking_id ) . '_'. esc_attr( $service_id ) .'_1" >' . esc_html( $booking->get_name() ) . '</a>';
            }
    }
  
    $time_controls = apply_filters( 'wbk_backend_schedule_time_controls', $time_controls, $timeslot, $service_id  );

    $html_schedule .= '<div class="timeslot_container">
                            <div class="timeslot_time ' . $status_class . '">'.
                                $time.
                            '</div>
                            <div class="timeslot_controls">'.
                              $time_controls . '
                            </div>
                            <div class="cb"></div>
                        </div>';
}

echo $html_schedule;