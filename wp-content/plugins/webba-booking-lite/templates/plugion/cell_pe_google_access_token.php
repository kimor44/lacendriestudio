<?php
if ( !defined( 'ABSPATH' ) ) exit;

$field = $data[0];
$slug = $data[1];
$value = $data[2];
$row = $data[3];
$calendar_id = $row['id'];
$html = '';

if ( wbk_fs()->is__premium_only() ) {
    if ( wbk_fs()->can_use_premium_code() ) {
        $credentials = array( get_option( 'wbk_gg_clientid', '' ),  get_option( 'wbk_gg_secret', '' ) );
        $credentials = apply_filters( 'wbk_gg_credentials', $credentials );
        $client_id =  $credentials[0];
        $client_secret = $credentials[1];
        if( $client_id  == '' || $client_secret == '' ){
            echo '<span class="wbk_google_auth_error"><img src="'. WP_WEBBA_BOOKING__PLUGIN_URL . '/backend/images/error.png"
                        alt="error">' . __( 'Authorization failed', 'wbk' ) . '</span>' .
                '<span class="wbk_google_auth_desc">' . __( 'Google API credentials not set', 'wbk' ) . '</span>';
            return;
        }
        $google = new WBK_Google();
        $google->init( $calendar_id );
        $connection_status =  $google->connect();
        $html = '';
        $control_html = '<a target="_blank" class="wbk_google_auth_link" href="' .  get_admin_url()  . 'admin.php?page=wbk-gg-calendars&clid=' . $calendar_id . '">' . __( 'Manage authorization', 'wbk' ) .'</a>';
        if( $connection_status[0] == 1 ){
            $html .=  '<span class="wbk_google_auth_success"><img src="'. WP_WEBBA_BOOKING__PLUGIN_URL . '/public/images/success.png"
                        alt="success">' .  __( 'Authorized', 'wbk' ) . '</span>';
            $html .= '<span class="wbk_google_auth_desc">' . __( 'Calendar name on Google:', 'wbk' );
            $html .=  ' ' . $connection_status[1] . '</span>';
            $html .= $control_html;
        }
        if( $connection_status[0] == 0 ){
            $html .= '<span class="wbk_google_auth_warning"><img src="'. WP_WEBBA_BOOKING__PLUGIN_URL . '/public/images/warning.png"
                        alt="warning">' . __( 'Authorization required', 'wbk' ) . '</span>' .
                '<span class="wbk_google_auth_desc">' . __( 'Click on the link below to start the authorization process', 'wbk' ) . '</span>' .
                $control_html;
            $html .= '<span class="wbk_google_auth_desc">' .  __( 'Details: ', 'wbk' ) . $connection_status[1] . '</span>';
        }
        if( $connection_status[0] == 2 ){
            $html .= '<span class="wbk_google_auth_error"><img src="'. WP_WEBBA_BOOKING__PLUGIN_URL . '/public/images/error.png"
                        alt="error">' . __( 'Authorization failed', 'wbk' ) . '</span>' .
                '<span class="wbk_google_auth_desc">' . __( 'Check Google API credentials, calendar ID and try to re-authorize this calendar', 'wbk' ) . '</span>' . $control_html;
                    $html .= '<span class="wbk_google_auth_desc">' . __( 'Details: ', 'wbk') . $connection_status[1] . '</span>';
        }
    }
}
echo $html;
