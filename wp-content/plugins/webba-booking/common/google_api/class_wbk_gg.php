<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// webba booking Google integration class
if ( wbk_fs()->is__premium_only() ) {
    if ( wbk_fs()->can_use_premium_code() ) {
    	if ( get_option( 'wbk_gg_client_version', '2.5') == '2.5' ) {
 			require_once __DIR__ . DIRECTORY_SEPARATOR . 'google-api-2.5' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
		} else {
     	    require_once __DIR__ . DIRECTORY_SEPARATOR . 'google-api-2.9.1' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
		}
    }
}
class WBK_Google{
	protected
	$client;

	protected
	$calendar_id;

	protected
	$gg_calendar_id;

	public function init( $calendar_id ){
		if ( wbk_fs()->is__premium_only() ) {
		    if ( wbk_fs()->can_use_premium_code() ) {
		    	if(  !is_null( $calendar_id ) && !WBK_Validator::validateId( $calendar_id, 'wbk_gg_calendars' ) ){
					wp_die();
					return FALSE;
				}
				$credentials = array( get_option( 'wbk_gg_clientid', '' ),  get_option( 'wbk_gg_secret', '' ) );

				$credentials = apply_filters( 'wbk_gg_credentials', $credentials );

				$client_id =  $credentials[0];
				$client_secret = $credentials[1];
				$this->scopes = implode(' ', array( Google_Service_Calendar::CALENDAR ) );
				$this->client = new Google_Client();
				$this->client->setClientId( $client_id );
			    $this->client->setClientSecret(  $client_secret );
				$this->client->setApplicationName('Webba Booking');
				$this->client->setScopes( $this->scopes );
				$this->client->setAccessType( 'offline' );
				$this->client->setApprovalPrompt( 'force');
				$this->calendar_id = $calendar_id;
				if( !is_null( $calendar_id ) ){
			 		$this->client->setRedirectUri(  get_admin_url() . 'admin.php?page=wbk-gg-calendars&clid=' . $this->calendar_id );
					return TRUE;
				} else {
					$redirect_url = trim( get_option( 'wbk_email_landing', '' ) );
					if( $redirect_url == '' ){
						return FALSE;
					} else {
						$this->client->setRedirectUri( $redirect_url );
						return TRUE;
					}
				}
		    }
		}

		return FALSE;
	}
	public function getAuthUrl(){
		return $this->client->createAuthUrl();
	}

	// connect to google and get calendar authorization status
	// 0 - no token set
	// 1 - authorization success
	// 2 - authorization failed
	public function connect(){
		if ( wbk_fs()->is__premium_only() ) {
		    if ( wbk_fs()->can_use_premium_code() ) {
		    	$access_token = $this->getAccessToken();
				if( !is_null( $access_token ) && $access_token != '' ){
					try {
							$this->client->setAccessToken( $access_token );
							// Refresh the token if it's expired.
							if ( $this->client->isAccessTokenExpired() ) {
							    $auth_result = $this->client->fetchAccessTokenWithRefreshToken( $this->client->getRefreshToken() );
							}
							$service = new Google_Service_Calendar( $this->client );
							$calendar_gg_id = $this->getGGCalendarId();
		 					$calendar = $service->calendars->get( $calendar_gg_id );
							$calendar_name = $calendar->getSummary();

		 					return array( 1, $calendar_name );

					} catch (\Exception $e) {
						return array( 2, $e->getMessage() );
					}
				} else {
					return array( 0, 'token is null' );
				}

		    }
		}
		return array( 2, 'not connected, premium feature not available' );
	}

	public function renderCalendarBlock(){
		$auth_status = $this->connect();
		$html = '';
		if ( wbk_fs()->is__premium_only() ) {
		    if ( wbk_fs()->can_use_premium_code() ) {
		    	switch ( $auth_status[0]) {
					case 0:
						$html .=  '<p>' . __( 'Status', 'wbk') . ': <span class="slf_table_warning">'.   __( 'authorization required', 'wbk' ) . '</span></p>';
						$html .=  '<label class="wbk_authorization_message" style="clear:both;display: block;" for="redirect_url">' . __( 'IMPORTANT NOTICE: add the following URL in the Google Cloud Console or contact administrator before authorization:', 'wbk' ) . '</label>';
						$redirect_url = get_admin_url() . 'admin.php?page=wbk-gg-calendars&clid=' . $this->calendar_id;
						$html .=  '<input  class="wbk_authorization_url" type="text" value="' . $redirect_url .'" style="width:700px;clear:both;">';
						$html .= '<p><a class="button" href="' . $this->getAuthUrl() . '">' . __( 'Authorize', 'wbk' ) . '</a></p>';
					break;
					case 2:
		                $html .=  '<p>' . __( 'Status', 'wbk') . ': <span class="slf_table_error">'.   __( 'authorization failed', 'wbk' ) . '</span></p>';
		                $html .=  '<span class="slf_table_desc">' . __( 'Check Google API credentials, calendar ID and try to re-authorize this calendar', 'wbk' ) . '</span>';
		                $html .=  '<a class="button" href="' . $this->getAuthUrl() . '">' . __( 'Re-authorize', 'wbk' ) . '</a>';

					break;
					case 1:
						$html .=  '<span class="slf_table_success">' .  __( 'Authorized', 'wbk' ) . '</span>';
				  		$html .= '<span class="slf_table_desc">' . __( 'Calendar name on Google:', 'wbk' );
				  		$html .=  ' ' . $auth_status[1] . '</span>';

				  		$revoke_url = get_admin_url() . 'admin.php?page=wbk-gg-calendars&clid=' . $this->calendar_id  . '&action=revoke';
				  		$html .= '<a class="button" href="' . $revoke_url . '">' . __( 'Remove authorization', 'wbk' ) . '</a>';

					break;
				}
		    }
		}
 		return $html;
	}

	public function processAuthCode( $authCode ){
		if ( wbk_fs()->is__premium_only() ) {
		    if ( wbk_fs()->can_use_premium_code() ) {
		    	$status = $this->connect();
				if( $status[0] == 1 ){
					return;
				}
				try {
					$auth_result = $this->client->fetchAccessTokenWithAuthCode( $authCode );
		    	    $this->saveAccessToken(  json_encode( $auth_result ) );
		    	    return 1;
		    	} catch (Exception $e) {
		 	   		return 0;
		    	}

		    }
		}
		return 0;

	}
	protected function getAccessToken(){
		if ( wbk_fs()->is__premium_only() ) {
		    if ( wbk_fs()->can_use_premium_code() ) {
    			global $wpdb;
				$access_token =  $wpdb->get_var( $wpdb->prepare( " SELECT access_token FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_gg_calendars WHERE id = %d ", $this->calendar_id ) );
				return $access_token;
		    }
		}
		return '';
	}
 	protected function getGGCalendarId(){
 		if ( wbk_fs()->is__premium_only() ) {
		    if ( wbk_fs()->can_use_premium_code() ) {
		    	global $wpdb;
				$calendar_id =  $wpdb->get_var( $wpdb->prepare( " SELECT calendar_id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_gg_calendars WHERE id = %d ", $this->calendar_id ) );
				return $calendar_id;
		    }
		}
		return 0;
	}
	protected function saveAccessToken( $access_token ){
		if ( wbk_fs()->is__premium_only() ) {
		    if ( wbk_fs()->can_use_premium_code() ) {
		    	global $wpdb;
				$result = $wpdb->update(
								get_option( 'wbk_db_prefix', '' ) . 'wbk_gg_calendars',
								array( 'access_token' => $access_token ),
								array( 'id' => $this->calendar_id ),
								array( '%s'),
								array( '%d' )
							);

		    }
		}
	}
	public function getCalendarName(){
		if ( wbk_fs()->is__premium_only() ) {
		    if ( wbk_fs()->can_use_premium_code() ) {
		    	global $wpdb;
				$value =  $wpdb->get_var( $wpdb->prepare( " SELECT name FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_gg_calendars WHERE id = %d ", $this->calendar_id ) );
				return $value;
		    }
		}
		return '';
	}
	public function getCalendarMode(){
		if ( wbk_fs()->is__premium_only() ) {
		    if ( wbk_fs()->can_use_premium_code() ) {
				global $wpdb;
				$value =  $wpdb->get_var( $wpdb->prepare( " SELECT mode FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_gg_calendars WHERE id = %d ", $this->calendar_id ) );
				return $value;
		    }
		}
		return '';
	}
	public function clearToken(){
		$this->saveAccessToken('');
	}
	public function insertEvent( $title, $description, $start, $end, $time_zone, $calendar_id = '', $use_current_time_zone = false ){
		if ( wbk_fs()->is__premium_only() ) {
		    if ( wbk_fs()->can_use_premium_code() ) {
 		    	$service = new Google_Service_Calendar( $this->client );
				if ( get_option( 'wbk_gg_customers_time_zone', 'webba' ) == 'customer' ){
					$calendar = $service->calendars->get( 'primary' );
					$time_zone = $calendar->getTimeZone();
				}
				try {
		            $title = str_replace( '&amp;', '&', $title );
		            $description = str_replace( '&amp;', '&', $description );
					$extended_property = new Google_Service_Calendar_EventExtendedProperties();
					$extended_property->setPrivate( array( 'createdby' => get_option( 'wbk_gg_created_by', 'webba_booking' ) ) );


					$event = new Google_Service_Calendar_Event(array(
					  'summary' =>  $title,
					  'extendedProperties' => $extended_property,
					  'description' => $description,
					  'transparency' => 'opaque',
					  'start' => array(
					    'dateTime' => $start,
					    'timeZone' => $time_zone,
					   ),
					  'end' => array(
					    'dateTime' => $end,
					    'timeZone' => $time_zone,
					) ) );
					if( $calendar_id == ''){
						$calendar_id = $this->getGGCalendarId();
					}
					$event = $service->events->insert( $calendar_id, $event);
					return( array( $this->calendar_id, $event->id ) );
				} catch (Exception $e) {
					return FALSE;
				}

		    }
		}
		return FALSE;
	}
	public function updateEvent( $event_id, $title, $description, $start = null, $end = null , $time_zone = null ){
		if ( wbk_fs()->is__premium_only() ) {
		    if ( wbk_fs()->can_use_premium_code() ) {
		    	$service = new Google_Service_Calendar( $this->client );
				try {
					$event = $service->events->get( $this->getGGCalendarId(), $event_id );

					$event->setSummary( $title );
					$event->setDescription( $description );

					if( !is_null( $start ) && !is_null( $time_zone ) ){
						$event_start = new Google_Service_Calendar_EventDateTime();
						$event_start->setDateTime( $start );
						$event_start->setTimeZone( $time_zone );
						$event->setStart( $event_start );
					}
					if( !is_null( $end ) && !is_null( $time_zone ) ){
						$event_end = new Google_Service_Calendar_EventDateTime();
						$event_end->setDateTime( $end );
						$event_end->setTimeZone( $time_zone );
						$event->setEnd( $event_end );
					}
					$updatedEvent = $service->events->update( $this->getGGCalendarId(), $event->getId(), $event);
					return TRUE;
				} catch (Exception $e) {
					return FALSE;
				}
		    }
		}
		return FALSE;
	}
	public function deleteEvent( $event_id ){
		if ( wbk_fs()->is__premium_only() ) {
		    if ( wbk_fs()->can_use_premium_code() ) {
		    	$service = new Google_Service_Calendar( $this->client );
				try {
					$service->events->delete( $this->getGGCalendarId(),  $event_id );
					return TRUE;
				} catch (Exception $e) {
					return FALSE;
				}
		    }
		}
		return FALSE;
	}
	public function initCalendarByAuthcode( $code ){
		if ( wbk_fs()->is__premium_only() ) {
		    if ( wbk_fs()->can_use_premium_code() ) {
    		 	try{
					$auth_result = $this->client->fetchAccessTokenWithAuthCode( $code );
		    	    $this->client->setAccessToken( $auth_result );
		    	    return TRUE;
		    	} catch (Exception $e) {
		    		return FALSE;
		    	}
		    }
		}
    	return FALSE;
	}

	public function getEventsTimeRanges( $start, $end ){
		$result = FALSE;
		if ( wbk_fs()->is__premium_only() ) {
		    if ( wbk_fs()->can_use_premium_code() ) {
		    	$cached = $this->try_to_get_from_cache( $start, $end );
				if( $cached !== FALSE ){
					return $cached;
				}
				$service = new Google_Service_Calendar( $this->client );
				$result = array();
				$optParams = array(
					  	'singleEvents' => TRUE,
						'timeMin' => $start,
						'timeMax' => $end,
                        'maxResults' => 2000
				);
				try{
					$results = $service->events->listEvents( $this->getGGCalendarId(), $optParams);
					foreach ( $results->getItems() as $event) {
 						if( $event->transparency == 'transparent' && get_option( 'wbk_gg_ignore_free', 'no' ) == 'yes' ){
							continue;
						}
						if( isset( $event->extendedProperties->private ) ){
                            if( isset( $event->extendedProperties->private['createdby'] ) ){
								if( $event->extendedProperties->private['createdby'] == get_option( 'wbk_gg_created_by', 'webba_booking' ) && get_option( 'wbk_ignore_webba_events', 'yes' ) == 'yes' ){
                                    $event_id = explode( '_',  $event->getId() );
                                    if( count( $event_id ) <> 2 ){
                                        continue;
                                    }
								}
							}
						}
					    $start = $event->start->dateTime;
					    if ( empty( $start ) ) {
					      $start = $event->start->date;
					    }
					    $end = $event->end->dateTime;
					    if ( empty($end)) {
						    $end = $event->end->date;
					    }

					    $start = strtotime( $start );
					    $end = strtotime( $end );

						$breaker = new WBK_Time_Slot( $start, $end );
						$result[] = $breaker;
					}
				} catch (Exception $e) {
					return FALSE;
				}
		    }
		}

		return $result;
	}
	public function doCache( $start ){
		if ( wbk_fs()->is__premium_only() ) {
		    if ( wbk_fs()->can_use_premium_code() ) {
		    	$service = new Google_Service_Calendar( $this->client );
				$result = array();
				$optParams = array(
					  	'singleEvents' => TRUE,
						'timeMin' => $start
				);
				$result = array();
				try{
					$results = $service->events->listEvents( $this->getGGCalendarId(), $optParams);
					foreach ( $results->getItems() as $event) {
						if( isset( $event->extendedProperties->private ) ){
							if( isset( $event->extendedProperties->private['createdby'] ) ){
								if( $event->extendedProperties->private['createdby'] == 'webba_booking' ){
									continue;
								}
							}
						}
					    $start = $event->start->dateTime;
					    if ( empty( $start ) ) {
					      $start = $event->start->date;
					    }
					    $end = $event->end->dateTime;
					    if ( empty($start)) {
						    $end = $event->end->date;
					    }
					    $start = strtotime( $start );
					    $end = strtotime( $end );
						$result[] =  array($start, $end );

					}
				} catch (Exception $e) {
					return FALSE;
				}
				$this->saveCache( json_encode( $result ) );
		    }
		}
		return FALSE;
	}
	protected function getCacheTime(){
		$value = '';
		if ( wbk_fs()->is__premium_only() ) {
		    if ( wbk_fs()->can_use_premium_code() ) {
		    	global $wpdb;
				$value =  $wpdb->get_var( $wpdb->prepare( " SELECT cache_time FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_gg_calendars WHERE id = %d ", $this->calendar_id ) );
		    }
		}
		return $value;
	}
	protected function getCacheContent(){
		$value = '';
		if ( wbk_fs()->is__premium_only() ) {
		    if ( wbk_fs()->can_use_premium_code() ) {
		    	global $wpdb;
				$value =  $wpdb->get_var( $wpdb->prepare( " SELECT cache_content FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_gg_calendars WHERE id = %d ", $this->calendar_id ) );
		    }
		}
		return $value;
	}
	protected function saveCache( $cache_content ){
		$value = '';
		if ( wbk_fs()->is__premium_only() ) {
		    if ( wbk_fs()->can_use_premium_code() ) {
		    	global $wpdb;
				$result = $wpdb->update(
					get_option( 'wbk_db_prefix', '' ) . 'wbk_gg_calendars',
					array( 'cache_content' => $cache_content, 'cache_time' => time() ),
					array( 'id' => $this->calendar_id ),
					array( '%s', '%d' ),
					array( '%d' )
				);
		    }
		}
		return $value;
	}
	protected function try_to_get_from_cache( $start, $end ){
		if ( wbk_fs()->is__premium_only() ) {
		    if ( wbk_fs()->can_use_premium_code() ) {
		    	$start = strtotime( $start );
				$end   = strtotime( $end );
				$hold_time = intval( get_option( 'wbk_gg_sync_cache_time', '0' ) );
				if( $hold_time == 0 ){
					return FALSE;
				}
				$cache_time = $this->getCacheTime();
				if( ( time() - $cache_time ) < $hold_time * 60 ) {
					$arr_events_ranges = json_decode( $this->getCacheContent() );
					$result = array();
					foreach( $arr_events_ranges as $event_range ){
						if( $event_range[0] < $start || $event_range[0] > $end ){
							continue;
						}
						$breaker = new WBK_Time_Slot( $event_range[0], $event_range[1] );
						$result[] = $breaker;
					}
					return $result;
				} else{
					return FALSE;
				}

		    }
		}
		return FALSE;
	}
}
?>
