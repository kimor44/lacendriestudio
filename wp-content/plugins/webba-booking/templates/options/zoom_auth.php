<?php
if ( !defined( 'ABSPATH' ) ) exit;

$slug = $data[0];
$default_value = $data[1];
$description = $data[2];

$redirect_url =  get_site_url() . '/?wbk_zoom_auth=true';  

if( isset( $data[4] ) ){
    $dependency = json_encode( $data[4] );
} else {
    $dependency = json_encode( array() );
}
$value = get_option( $slug, $default_value );
?>
<div class='wbk_option_block' data-dependency="<?php echo esc_attr( $dependency ); ?>">
    <input type="hidden" class="wbk_middle_field" id="<?php echo esc_attr( $slug ); ?>" name="<?php echo esc_attr( $slug ); ?>" value="<?php echo esc_attr( $value ); ?>">
    <?php
        if( get_option( 'wbk_zoom_client_id', '' ) == '' || get_option( 'wbk_zoom_client_id', '' ) == '' ){
            echo esc_html( 'Please, set up Client ID and Client secret');
        } else {
            if ( get_option( $slug, '') == ''  ){
                $url = 'https://zoom.us/oauth/authorize?response_type=code&client_id=' . get_option( 'wbk_zoom_client_id', '' ) . '&redirect_uri=' . $redirect_url;
                echo '<a class="wbk_zoom_authorize"  rel="noopener" href="' . $url . '">Authorize</a>';
            } else{
                echo '<span style="color:green;" class="wbk_zoom_authorized_label">' . esc_html( 'Authorized', 'wbk' ) . '</span><br>' . '<a class="wbk_zoom_remove_auth" href="#">Remove authorization</a>';
            }
        }
    ?>
    <p class="description"><?php echo $description; ?></p>
</div>
