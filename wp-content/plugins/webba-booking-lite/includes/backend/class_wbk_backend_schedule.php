<?php
// check if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WBK_Backend_Schedule  {
	public function __construct() {

		// add ajax actions
		add_action( 'wp_ajax_wbk_schedule_load', array( $this, 'schedule_load' ) );
		add_action( 'wp_ajax_wbk_lock_day', array( $this, 'ajax_lock_day' ) );
		add_action( 'wp_ajax_wbk_unlock_day', array( $this, 'ajax_unlock_day' ) );
		add_action( 'wp_ajax_wbk_lock_time', array( $this, 'ajax_lock_time' ) );
		add_action( 'wp_ajax_wbk_unlock_time', array( $this, 'ajax_unlock_time' ) );
		add_action( 'wp_ajax_wbk_prepare_appointment', array( $this, 'prepare_appointment' ) );
		add_action( 'wp_ajax_wbk_add_appointment_backend', array( $this, 'add_appointment_backend' ) );
		add_action( 'wp_ajax_wbk_view_appointment', array( $this, 'view_appointment' ) );
		add_action( 'wp_ajax_wbk_create_multiple_bookings', array( $this, 'wbk_create_multiple_bookings' ) );
        add_action( 'wp_ajax_wbk_delete_appointment', array( $this, 'wbk_delete_appointment' ) );

        
	}


    public function wbk_create_multiple_bookings(){ 

        if ( !wp_verify_nonce( $_POST['nonce'], 'wbkb_nonce' ) ) {
            wp_die();
            return;
        }
        
		global $wpdb;
		date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
		$html = '';
		$offset = 0;
		$service_id = $_POST['service_id'];
		$date = strtotime( $_POST['date']);

		$name = $_POST['name'];
		$email = $_POST['email'];
		$phone = $_POST['phone'];
		$desc = $_POST['desc'];
		$quantity = $_POST['quantity'];
		$times = explode( ',', $_POST['times'] );

        $appointment_ids = array();
		foreach( $times as $time ){
			if( !WBK_Validator::validateId( $service_id, 'wbk_services' /* passed newdb */  ) ){
				echo 'Error -1';
				date_default_timezone_set( 'UTC' );
				die();
				return;
			}
			$day = strtotime( date( 'Y-m-d', $time ).' 00:00:00' );
			if( !WBK_Validator::validateId( $service_id, 'wbk_services' /* passed newdb */  ) ){
				echo 'Error -1';
				date_default_timezone_set( 'UTC' );
				die();
				return;
			}
			$service = new WBK_Service_deprecated();
			if ( !$service->setId( $service_id ) ) {
				echo 'Error -6';
				date_default_timezone_set( 'UTC' );
				die();
				return;
			}
			if ( !$service->load() ) {
				echo 'Error -6';
				date_default_timezone_set( 'UTC' );
				die();
				return;
			}
			$count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . get_option('wbk_db_prefix', '' ) . 'wbk_appointments where service_id = %d and time = %d', $service_id, $time ) );
			if ( $count > 0 && $service->getQuantity() == 1 ) {
				echo __( 'Overbooking error', 'wbk' );
				date_default_timezone_set( 'UTC' );
				die();
				return;
			}
			$duration = $service->getDuration();
			$appointment = new WBK_Appointment_deprecated();
			if ( !$appointment->setName( $name ) ){
				echo 'Error -1';
				date_default_timezone_set( 'UTC' );
				die();
				return;
			}
			if ( !$appointment->setEmail( $email ) ){
				echo 'Error -2';
				date_default_timezone_set( 'UTC' );
				die();
				return;
			}
			if ( !$appointment->setPhone( $phone ) ){
				echo 'Error -3';
				date_default_timezone_set( 'UTC' );
				die();
				return;
			}
			if ( !$appointment->setTime( $time ) ){
				echo 'Error -4';
				date_default_timezone_set( 'UTC' );
				die();
				return;
			}
			if ( !$appointment->setDay( $day ) ){
				echo 'Error -5';
				date_default_timezone_set( 'UTC' );
				die();
				return;
			}
			if ( !$appointment->setService( $service_id ) ){
				echo 'Error -6';
				date_default_timezone_set( 'UTC' );
				die();
				return;
			}
			if ( !$appointment->setDuration( $duration ) ){
				echo 'Error -7';
				date_default_timezone_set( 'UTC' );
				die();
				return;
			}
			if ( !$appointment->setDescription( $desc ) ){
				echo 'Error -91';
				date_default_timezone_set( 'UTC' );
				die();
				return;
			}
			if ( !$appointment->setExtra( '') ){
				echo 'Error -92';
				date_default_timezone_set( 'UTC' );
				die();
				return;
			}
			if ( !$appointment->setQuantity( $quantity ) ){
				echo 'Error -93';
				date_default_timezone_set( 'UTC' );
				die();
				return;
			}
			$appointment_id = $appointment->add();
			if ( $id === false ) {
				echo 'Error -8';
				date_default_timezone_set( 'UTC' );
				die();
				return;
			}
			$appointment_ids[] = $appointment_id;
			do_action( 'wbk_table_after_add', array( $appointment_id, get_option( 'wbk_db_prefix', '' ) .'wbk_appointments' ) );
			$auto_lock = get_option( 'wbk_appointments_auto_lock', 'disabled' );
			if ( $auto_lock == 'enabled' ){
				WBK_Db_Utils::lockTimeSlotsOfOthersServices( $service_id, $appointment_id );
			}
			// *** GG ADD
			WBK_Db_Utils::addAppointmentDataToGGCelendar( $service_id, $appointment_id );
			WBK_Db_Utils::setCreatedOnToAppointment( $id );

			if( get_option( 'wbk_multi_booking', 'disabled' ) == 'disabled' || ( get_option( 'wbk_multi_booking', 'disabled' ) != 'disabled' &&  get_option( 'wbk_email_customer_book_multiple_mode', 'one' ) != 'one' ) ){
				$noifications = new WBK_Email_Notifications( $service_id, $appointment_id );
				$noifications->send( 'book', TRUE );
			}
		}

		if( count( $appointment_ids ) == 0 ){
			echo __( 'Booking not complete', 'wbk' );
			wp_die();
			return;
		}
		if( get_option( 'wbk_multi_booking', 'disabled' ) != 'disabled' &&  get_option( 'wbk_email_customer_book_multiple_mode', 'one' ) == 'one' ) {
			$noifications = new WBK_Email_Notifications( $service_id, $appointment_id, $current_category );
			$noifications->sendMultipleCustomerNotification( $appointment_ids );
			if(	get_option( 'wbk_email_customer_send_invoice', 'disabled' ) == 'onbooking' ){
				$noifications->sendMultipleCustomerInvoice( $appointment_ids );
			}
		}
		if( get_option( 'wbk_multi_booking', 'disabled' ) != 'disabled' &&  get_option( 'wbk_email_admin_book_multiple_mode', 'one' ) == 'one' ) {
			$noifications = new WBK_Email_Notifications( $service_id, $appointment_id, $current_category );
			$noifications->sendMultipleAdminNotification( $appointment_ids );
		}
		$html = __( 'Appointments added:', 'wbk' ) . ' ' . count( $appointment_ids );
		date_default_timezone_set( 'UTC' );
		echo $html;
		wp_die();
		return;


	}


	public function wbk_delete_appointment() {
		if ( !wp_verify_nonce( $_POST['nonce'], 'wbkb_nonce' ) ) {
            wp_die();
            return;
        }
		global $wpdb;
		global $current_user;
		date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
		$service_id = $_POST['service_id'];
		if( !is_numeric( $service_id ) ){
			echo '-1';
			date_default_timezone_set( 'UTC' );
			wp_die();
			return;
		} 
		$booking_id = $_POST['appointment_id'];
		if( !is_numeric( $booking_id ) ){
			echo '-1';
			date_default_timezone_set( 'UTC' );
			wp_die();
			return;
		} 
		$booking = new WBK_Booking( $booking_id );
		if( !$booking->is_loaded() ){
			echo '-1';
			date_default_timezone_set( 'UTC' );
			wp_die();
			return;
		}
		$day = $booking->get_day();
	    // check access
	    if ( !current_user_can('manage_options') ) {
		   if ( !WBK_Validator::check_access_to_service ( $service_id ) ) {
			   echo '-1';
			   date_default_timezone_set( 'UTC' );
			   wp_die();
			   return;
		   }
		}
		 
		$bf = new WBK_Booking_Factory();
        $bf->destroy( $booking_id, 'administrator', true );


        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
		$sp = new WBK_Schedule_Processor();
		$time_slots = $sp->get_time_slots_by_day( $day,
												  $service_id,
												  array( 'ignore_preparation' => true,
														  'calculate_availability' => true ) );

		$html_schedule = WBK_Renderer::load_template( 'backend/schedule_day_timeslots', array( $time_slots, $service_id, $sp->get_locked_time_slots( $service_id ) ), false );
        date_default_timezone_set('UTC' );

		$resarray = array( 'day' =>  $html_schedule );
		date_default_timezone_set( 'UTC' );
		echo json_encode($resarray);
		wp_die();
		 
	}

	public function view_appointment() {
		if ( !wp_verify_nonce( $_POST['nonce'], 'wbkb_nonce' ) ) {
            wp_die();
            return;
        }
		global $wpdb;
		global $current_user;
		date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
		$service_id = $_POST['service_id'];		
		$booking_id = $_POST['appointment_id'];
	    // check access
	    if ( !current_user_can('manage_options') ) {
		   if ( !WBK_Validator::check_access_to_service( $service_id ) ) {
			   echo '-1';
			   date_default_timezone_set( 'UTC' );
			   die();
			   return;
		   }
	    }
	    $booking = new WBK_Booking( $booking_id );
	    if ( !$booking->is_loaded() ) {
		    echo '-2';
		    date_default_timezone_set( 'UTC' );
		    die();
		    return;
	    }
	    
	    $name = esc_html( WBK_Db_Utils::backend_customer_name_processing( $booking_id, $booking->get_name() ) );
	    $desc = esc_html( $booking->get('description') );
	    $email = esc_html( $booking->get('email') );
	    $phone = esc_html( $booking->get('phone') );
	    $time = esc_html( $booking->get_start() );
	    $quantity = esc_html( $booking->get_quantity() );
	    $extra =  $booking->get('extra');
   

		$extra = json_decode( $extra );
		$extra_data = '';

		$date_format = WBK_Date_Time_Utils::get_date_format();
		$time_format = WBK_Date_Time_Utils::get_time_format();
		$time_string = wp_date( $date_format, $time, new DateTimeZone( date_default_timezone_get() ) ) . ' ' . wp_date( $time_format, $time, new DateTimeZone( date_default_timezone_get() ) );
		$resarray = array( 'name' => $name, 'desc' =>  $desc, 'email' => $email, 'phone' => $phone, 'time' => $time_string, 'extra' => $extra, 'quantity' => $quantity );
		echo json_encode($resarray);
		date_default_timezone_set( 'UTC' );
		die();
		return;
	}
 

	public function add_appointment_backend() {
		global $wpdb;
		if ( !wp_verify_nonce( $_POST['nonce'], 'wbkb_nonce' ) ) {
            wp_die();
            return;
        }
		date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
		$name = $_POST['name'];
		$email = $_POST['email'];
		$phone = $_POST['phone'];
		$time = $_POST['time'];
		$desc = $_POST['desc'];
		$extra =  stripcslashes( $_POST['extra'] );
		$quantity = $_POST['quantity'];
		$service_id = $_POST['service_id'];
 
		$day = strtotime( date( 'Y-m-d', $time ).' 00:00:00' );
		
		$service = new WBK_Service( $service_id );
		if( !$service->is_loaded() ){
			wp_die();
            return;
		}

		$sp = new WBK_Schedule_Processor();
		$day = strtotime('today midnight', $time );
		$sp->get_time_slots_by_day( $day,
									$service_id,
									array( 'skip_gg_calendar'    => false,
										'ignore_preparation'     => true,
										'calculate_availability' => true,
										'calculate_night_hours'  => false ) );
		$available = $sp->get_available_count( $time );
		if( $available < $quantity ){
			wp_die();
            return;
		}
		$quantity = esc_html( sanitize_text_field( $quantity ) );
		$booking_data['duration'] = $service->get_duration();
	 	$booking_data['name'] =  esc_html( trim( apply_filters( 'wbk_field_before_book', sanitize_text_field( $name ), 'name' ) ) );
		$booking_data['email'] = esc_html( strtolower( trim( apply_filters( 'wbk_field_before_book', sanitize_text_field( $email ), 'email' ) ) ) );
		$booking_data['phone'] = esc_html( trim( sanitize_text_field( $phone ) ) );
        $booking_data['extra'] =  stripcslashes( $_POST['extra'] );
        $booking_data['description'] = esc_html( sanitize_text_field( $desc ) );
		$booking_data['quantity'] = $quantity;
		$booking_data['time'] = $time;
		$booking_data['time_offset'] = WBK_Time_Math_Utils::get_offset_local( $time );
		$booking_data['service_id'] = $service_id;
		$booking_data['service_category'] = 0; 

		$boking_factory = new WBK_Booking_Factory();
		$status = $boking_factory->build_from_array( $booking_data );
		$boking_factory->post_production( array( $status[1] ) );

		
		if( $status[0] == true ){
			$booking_ids[] = $status[1];
 			do_action( 'wbk_table_after_add', [ $status[1], get_option('wbk_db_prefix', '' ) . 'wbk_appointments' ] );
			$wbk_action_data = array(  'appointment_id' => $status[1],
										'customer' => $booking_data['name'],
										'email' => $booking_data['email'],
										'phone' => $booking_data['phone'],
										'time' => $booking_data['time'],
										'serice id' => $booking_data['service_id'],
										'duration' => $booking_data['duration'],
										'comment' => $booking_data['description'],
										'quantity' => $booking_data['quantity'] );

			do_action( 'wbk_add_appointment', $wbk_action_data );
			$time_slots = $sp->get_time_slots_by_day( $day,
													  $service_id,
													  array( 'ignore_preparation' => true,
												   			 'calculate_availability' => true ) );

 			$html_schedule = WBK_Renderer::load_template( 'backend/schedule_day_timeslots', array( $time_slots, $service_id,$sp->get_locked_time_slots( $service_id ) ), false );
			
			$resarray = array( 'day' =>  $html_schedule );
			date_default_timezone_set( 'UTC' );
			echo json_encode($resarray);
			wp_die();
			return;
		}

		wp_die();
		return;
	}
	public function prepare_appointment(){
		global $wpdb;
 		global $current_user;
		if ( !wp_verify_nonce( $_POST['nonce'], 'wbkb_nonce' ) ) {
            wp_die();
            return;
        }
		date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );

 		$time = $_POST['time'];
 		$service_id = $_POST['service_id'];

		if( !is_numeric( $time ) || !is_numeric( $service_id ) ){
			echo '-1';
			date_default_timezone_set( 'UTC' );
			wp_die();
			return;
		}
 	
 		$service = new WBK_Service_deprecated();
 		
 		if ( !$service->setId( $service_id ) ){
			echo '-1';
			date_default_timezone_set( 'UTC' );
			die();
			return;
 		}
  		if ( !$service->load() ){
			echo '-1';
			date_default_timezone_set( 'UTC' );
			die();
			return;
 		}
 		$quantity = $service->getQuantity();
        // check access
        if ( !current_user_can('manage_options') ) {
        	if ( !WBK_Validator::check_access_to_service( $service_id ) ) {
	            echo '-1';
	            date_default_timezone_set( 'UTC' );
	            die();
	            return;
	        }
        }
		$date_format = WBK_Date_Time_Utils::get_date_format();
		$time_format = WBK_Date_Time_Utils::get_time_format();
		$time_string = wp_date( $date_format, $time, new DateTimeZone( date_default_timezone_get() ) ) . ' ' . wp_date( $time_format, $time, new DateTimeZone( date_default_timezone_get() ) );
		
		 

		$sp = new WBK_Schedule_Processor();
		$day = strtotime('today midnight', $time );
		$sp->get_time_slots_by_day( $day,
									$service_id,
									array( 'skip_gg_calendar'    => false,
										'ignore_preparation'     => true,
										'calculate_availability' => true,
										'calculate_night_hours'  => false ) );
		$current_avail = $sp->get_available_count( $time );

		$phone_mask = get_option( 'wbk_phone_mask', 'disabled' );
		$phone_format = '';
		if( $phone_mask == 'enabled' ){
			$phone_format = get_option( 'wbk_phone_format', '999-9999' );
		}
		$resarray = array( 'time' => $time_string, 'timestamp' => $time, 'quantity' => $quantity, 'available' => $current_avail, 'phone_format' => $phone_format );
        echo json_encode($resarray);
        date_default_timezone_set( 'UTC' );
        die();
        return;
	}
 	
	public function schedule_load() {
		if ( !wp_verify_nonce( $_POST['nonce'], 'wbkb_nonce' ) ) {
            wp_die();
            return;
        }
		date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
 		$service_id = $_POST['service_id'];
		global $current_user;
        // check access
        if ( !current_user_can('manage_options') ) {
        	if ( !WBK_Validator::check_access_to_service( $service_id ) ) {
	            echo '-1';
	            date_default_timezone_set( 'UTC' );
	            die();
	            return;
	        }
        }
 		$start = $_POST['start'];
 		if ( !WBK_Validator::check_integer( $service_id, 1, 99999 ) ){
			echo '-1';
            date_default_timezone_set( 'UTC' );
 			die();
 			return;
 		}
 		if ( !WBK_Validator::check_integer( $start, 0, 99999 ) ){
			echo '-2';
            date_default_timezone_set( 'UTC' );
 			die();
 			return;
 		}
 		// check if service exists
 		$service_test = new WBK_Service( $service_id );
 		if ( !$service_test->is_loaded() ){
 			echo -1;
            date_default_timezone_set( 'UTC' );
 			die();
 			return;
 		}

 		// init service schedulle

		$sp = new WBK_Schedule_Processor();
		$sp->load_data();
 		// output days
 		if ( $start == 0 ){
			$day_to_render = WBK_Time_Math_Utils::get_start_of_current_week();
 		} else {
			$next_week_day = WBK_Time_Math_Utils::adjust_times( strtotime('today'), 86400 * 7 * $start, get_option( 'wbk_timezone' ,'UTC' )  );
 			$day_to_render = WBK_Time_Math_Utils::get_start_of_week_day( $next_week_day );

 		}
		$date_format = WBK_Format_Utils::get_date_format();
		$html = '';

		$html = '<div class="wbk-schedule-row-simple">';
		for ( $i = 1;  $i <= 7 ;  $i++ ) {
            date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
			$day_status = $sp->get_day_status( $day_to_render, $service_id );
			$time_slots = $sp->get_time_slots_by_day( $day_to_render,
													  $service_id,
													  array( 'ignore_preparation' => true,
												   			 'calculate_availability' => true ) );
		 

            $html .= WBK_Renderer::load_template( 'backend/schedule_day', array( $day_status, $time_slots, $day_to_render, $service_id, $sp->get_locked_time_slots( $service_id ) ), false );
			$day_to_render = strtotime( 'tomorrow', $day_to_render  );
		}
  		$html .= '</div>';

 	    date_default_timezone_set( 'UTC' );
		echo $html;
		die();
 	}
 	// ajax lock day
 	public function ajax_lock_day() {
		if ( !wp_verify_nonce( $_POST['nonce'], 'wbkb_nonce' ) ) {
            wp_die();
            return;
        }
		global $current_user;
 		date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
 		global $wpdb;
 		$service_id = $_POST['service_id'];

        // check access
        if ( !current_user_can('manage_options')  ) {
        	if ( !WBK_Validator::check_access_to_service( $service_id ) ) {
	            echo '-1';
	            date_default_timezone_set( 'UTC' );
	            die();
	            return;
	        }
        }
 		if ( !WBK_Validator::check_integer( $service_id, 1, 99999 ) ){
 			echo -1;
 			date_default_timezone_set( 'UTC' );
 			die();
 			return;
 		}
 		$day = $_POST['day'];
 		if ( !WBK_Validator::check_integer( $day, 1438426800, 1754046000 ) ){
 			echo -1;
 			date_default_timezone_set( 'UTC' );
 			die();
 			return;
 		}
 		if ( $wpdb->query( $wpdb->prepare( "DELETE FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_days_on_off WHERE day = %d and service_id = %d",  $day, $service_id ) ) === false ){
 			echo -1;
 			date_default_timezone_set( 'UTC' );
 			die();
 			return;
 		}
 		if ( $wpdb->insert(  get_option( 'wbk_db_prefix', '' ) . 'wbk_days_on_off', array( 'service_id' => $service_id, 'day' => $day, 'status' => 0 ), array( '%d', '%d', '%d' ) ) === false ){
 			echo -1;
 			date_default_timezone_set( 'UTC' );
 			die();
 			return;
 		}
		date_default_timezone_set( 'UTC' );

 		WBK_Renderer::load_template( 'backend/schedule_day_unlock_link', array( $service_id, $day ), true );
		die();
		return;
 	}
	// ajax unlock day
 	public function ajax_unlock_day() {
		if ( !wp_verify_nonce( $_POST['nonce'], 'wbkb_nonce' ) ) {
            wp_die();
            return;
        }
 		global $wpdb;
 		global $current_user;
 		date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
 		$service_id = $_POST['service_id'];
 		if( !WBK_Validator::validateId( $service_id, 'wbk_services' /* passed newdb */ ) ){
			echo '-1';
	        date_default_timezone_set( 'UTC' );
	        die();
	        return;
		}
        // check access
        if ( !current_user_can('manage_options')  ) {
        	if ( !WBK_Validator::check_access_to_service( $service_id ) ) {
	            echo '-1';
	            date_default_timezone_set( 'UTC' );
	            die();
	            return;
	        }
        }
 		if ( !WBK_Validator::check_integer( $service_id, 1, 99999 ) ){
 			echo -1;
 			date_default_timezone_set( 'UTC' );
 			die();
 			return;
 		}
 		$day = $_POST['day'];
 		if ( !WBK_Validator::check_integer( $day, 1438426800, 1754046000 ) ){
 			echo -1;
 			date_default_timezone_set( 'UTC' );
 			die();
 			return;
 		}
 		if ( $wpdb->query( $wpdb->prepare( "DELETE FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_days_on_off WHERE day = %d and service_id = %d",  $day, $service_id ) ) === false ){
 			echo -1;
 			date_default_timezone_set( 'UTC' );
 			die();
 			return;
 		}
 		if ( $wpdb->insert( get_option( 'wbk_db_prefix', '' ) . 'wbk_days_on_off', array( 'service_id' => $service_id, 'day' => $day, 'status' => 1 ), array( '%d', '%d', '%d' ) ) === false ){
 			echo -1;
 			date_default_timezone_set( 'UTC' );
 			die();
 			return;
 		}
 		date_default_timezone_set( 'UTC' );
 		WBK_Renderer::load_template( 'backend/schedule_day_lock_link', array( $service_id, $day ), true );
		die();
 	}
 	// ajax lock time
 	public function ajax_lock_time() {
		if ( !wp_verify_nonce( $_POST['nonce'], 'wbkb_nonce' ) ) {
            wp_die();
            return;
        }
 		global $wpdb;
 		global $current_user;
 		date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
 		$service_id = $_POST['service_id'];
 		if( !WBK_Validator::validateId( $service_id, 'wbk_services' /* passed newdb */ ) ){
			echo '-1';
	        date_default_timezone_set( 'UTC' );
	        die();
	        return;
		}
        // check access
        if ( !current_user_can('manage_options') ) {
        	if ( !WBK_Validator::check_access_to_service( $service_id ) ) {
	            echo '-1';
	            date_default_timezone_set( 'UTC' );
	            die();
	            return;
	        }
        }
 		if ( !WBK_Validator::check_integer( $service_id, 1, 99999 ) ){
 			echo -1;
 			date_default_timezone_set( 'UTC' );
 			die();
 			return;
 		}
 		$time = $_POST['time'];
 		if ( !WBK_Validator::check_integer( $time, 1438426800, 1754046000 ) ){
 			echo -1;
 			date_default_timezone_set( 'UTC' );
 			die();
 			return;
 		}
 		if ( $wpdb->query( $wpdb->prepare( "DELETE FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_locked_time_slots WHERE time = %d and service_id = %d",  $time, $service_id ) ) === false ){
 			echo -1;
 			date_default_timezone_set( 'UTC' );
 			die();
 			return;
 		}
 		if ( $wpdb->insert( get_option( 'wbk_db_prefix', '' ) . 'wbk_locked_time_slots', array( 'service_id' => $service_id, 'time' => $time ), array( '%d', '%d' ) ) === false ){
 			echo -1;
 			date_default_timezone_set( 'UTC' );
 			die();
 			return;
 		}
 		WBK_Renderer::load_template( 'backend/schedule_time_unlock_link', array( $service_id, $time ) );
 		date_default_timezone_set( 'UTC' );
		die();
 	}
 	// ajax unlock time
 	public function ajax_unlock_time() {
		if ( !wp_verify_nonce( $_POST['nonce'], 'wbkb_nonce' ) ) {
            wp_die();
            return;
        }
 		global $wpdb;
 		global $current_user;
 		$service_id = $_POST['service_id'];
 		if( !WBK_Validator::validateId( $service_id, 'wbk_services' /* passed newdb */  ) ){
			echo '-1';
	        date_default_timezone_set( 'UTC' );
	        die();
	        return;
		}
 		date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        // check access
        if ( !current_user_can('manage_options') ) {
        	if ( !WBK_Validator::check_access_to_service( $service_id ) ) {
	            echo '-1';
	            date_default_timezone_set( 'UTC' );
	            die();
	            return;
	        }
        }
 		if ( !WBK_Validator::check_integer( $service_id, 1, 99999 ) ){
 			echo -1;
 			date_default_timezone_set( 'UTC' );
 			die();
 			return;
 		}
 		$time = $_POST['time'];
 		if ( !WBK_Validator::check_integer( $time, 1438426800, 1754046000 ) ){
 			echo -1;
 			date_default_timezone_set( 'UTC' );
 			die();
 			return;
 		}
 		if ( $wpdb->query( $wpdb->prepare( "DELETE FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_locked_time_slots WHERE time = %d and service_id = %d",  $time, $service_id ) ) === false ){
 			echo -1;
 			date_default_timezone_set( 'UTC' );
 			die();
 			return;
 		}
 		date_default_timezone_set( 'UTC' );
 		WBK_Renderer::load_template( 'backend/schedule_time_lock_link', array( $service_id, $time ) );
		die();
 	}



}
?>
