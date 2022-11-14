<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class WBK_Zoom{
    protected $redirect_url;
    public function __construct() {
        $this->redirect_url = get_site_url() . '/?wbk_zoom_auth=true';
    }
    public function generate_access_token( $code ){
        if ( wbk_fs()->is__premium_only() ) {
            if ( wbk_fs()->can_use_premium_code() ) {
                if( $code != '' ){
                    try {
                        $client = new GuzzleHttp\Client(['base_uri' => 'https://zoom.us']);
                        $response = $client->request('POST', '/oauth/token', [
                            "headers" => [
                                "Authorization" => "Basic ". base64_encode( get_option( 'wbk_zoom_client_id', '' ) . ':' . get_option( 'wbk_zoom_client_secret', '' ) )
                            ],
                            'form_params' => [
                                "grant_type" => "authorization_code",
                                "code" => $_GET['code'],
                                "redirect_uri" => $this->redirect_url
                            ],
                        ]);
                        $token = json_decode($response->getBody()->getContents(), true );
                        update_option( 'wbk_zoom_auth_stat', json_encode($token) );
                    } catch(Exception $e) {
                        echo $e->getMessage();
                    }
                }
            }
        }
    }

    public function add_meeting( $booking_id, $duration = null ){
        if ( wbk_fs()->is__premium_only() ) {
            if ( wbk_fs()->can_use_premium_code() ) {
                $client = new GuzzleHttp\Client(['base_uri' => 'https://api.zoom.us']);
                $token_data = json_decode( get_option( 'wbk_zoom_auth_stat', '') );
                if( is_null( $token_data ) ){
                    return;
                }
                $booking = new WBK_Booking( $booking_id );
                if( !$booking->is_loaded() ){
                    return;
                }
                $service = new WBK_Service( $booking->get_service() );
                if( !$service->is_loaded() ){
                    return;
                }
                if( $service->get('zoom') != 'yes' ){
                    return;
                }

                if( is_null( $duration ) ){
                    $duration = $booking->get('duration');
                }

                try {
                    $response = $client->request('POST', '/v2/users/me/meetings', [
                        "headers" => [
                            "Authorization" => "Bearer $token_data->access_token"
                        ],
                        'json' => [
                            "topic" => $booking->get_name() . ' - ' . $service->get_name(),
                            "type" => 2,
                            "start_time" => date( 'c', $booking->get_start() ),
                            "duration" => $duration
                        ],
                    ]);

                    $data = json_decode( $response->getBody() );
                    Plugion()->set_value( get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments', 'appointment_zoom_meeting_url', $booking_id, $data->join_url );
                    Plugion()->set_value( get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments', 'appointment_zoom_meeting_id', $booking_id, $data->id );
                    Plugion()->set_value( get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments', 'appointment_zoom_meeting_pwd', $booking_id, $data->password );
                } catch( Exception $e ) {
                    if( 401 == $e->getCode() ) {
                        $this->refresh_token( $token_data );
                        $this->add_meeting( $booking_id );
                    } else {
                        error_log( $e->getMessage() );
                    }
                }
            }
        }
    }
    public function refresh_token( $token_data ){
        if ( wbk_fs()->is__premium_only() ) {
            if ( wbk_fs()->can_use_premium_code() ) {
                $client = new GuzzleHttp\Client(['base_uri' => 'https://zoom.us']);
                $response = $client->request('POST', '/oauth/token', [
                    "headers" => [
                        "Authorization" => "Basic " . base64_encode( get_option( 'wbk_zoom_client_id', '' ) . ':' . get_option( 'wbk_zoom_client_secret', '' ) )
                    ],
                    'form_params' => [
                        "grant_type" => "refresh_token",
                        "refresh_token" => $token_data->refresh_token
                    ],
                ]);
                $res = (string) $response->getBody();
                $update_res =  update_option( 'wbk_zoom_auth_stat', $res );
            }
        }
    }
    public function update_meeting( $booking_id ){
        if ( wbk_fs()->is__premium_only() ) {
            if ( wbk_fs()->can_use_premium_code() ) {
                $booking = new WBK_Booking( $booking_id );
                if( !$booking->is_loaded() ){
                    return;
                }
                $service = new WBK_Service( $booking->get_service() );
                if( !$service->is_loaded() ){
                    return;
                }
                $meeting_id = $booking->get('zoom_meeting_id');
                if( $meeting_id == '' || is_null( $meeting_id ) ){
                    return;
                }
                $token_data = json_decode( get_option( 'wbk_zoom_auth_stat', '') );
                if( is_null( $token_data ) ){
                    return;
                }
                $client = new GuzzleHttp\Client(['base_uri' => 'https://api.zoom.us']);
                try {
                    $response = $client->request('PATCH', '/v2/meetings/' . $meeting_id, [
                        "headers" => [
                            "Authorization" => "Bearer $token_data->access_token"
                        ],
                        'json' => [
                            "topic" =>  $service->get_name() . ' ' . $booking->get_name(),
                            "type" => 2,
                            "start_time" => date( 'c', $booking->get_start() ),

                        ],
                    ]);
                } catch( Exception $e ) {
                    if( 401 == $e->getCode() ) {
                        $this->refresh_token( $token_data  );
                        $this->update_meeting( $booking_id );
                    } else {
                        error_log( $e->getMessage() );
                    }
                }
            }
        }
    }

    public function delete_meeting( $booking_id ){
        if ( wbk_fs()->is__premium_only() ) {
            if ( wbk_fs()->can_use_premium_code() ) {
                $booking = new WBK_Booking( $booking_id );
                if( !$booking->is_loaded() ){
                    return;
                }
                $meeting_id = $booking->get('zoom_meeting_id');
                if( $meeting_id == '' || is_null( $meeting_id ) ){
                    return;
                }
                $token_data = json_decode( get_option( 'wbk_zoom_auth_stat', '') );
                if( is_null( $token_data ) ){
                    return;
                }
                $client = new GuzzleHttp\Client(['base_uri' => 'https://api.zoom.us']);
                try {
                    $response = $client->request('DELETE', '/v2/meetings/' . $meeting_id, [
                        "headers" => [
                            "Authorization" => "Bearer $token_data->access_token"
                        ]
                    ]);
                } catch( Exception $e ) {
                    if( 401 == $e->getCode() ) {
                        $this->refresh_token( $token_data );
                        $this->delete_meeting( $booking_id );
                    } else {
                        error_log( $e->getMessage() );
                    }
                }
            }
        }
    }


}
