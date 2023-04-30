<?php
if ( !defined( 'ABSPATH' ) ) exit;
$service_ids = $data[0];
$is_hidden = $data[1];

$placeholder = esc_html( get_option('wbk_date_service_placeholder', __( 'select...', 'wbk' ) ) );

?>
<?php
if( $is_hidden ){
?>
    <select class="wbk_hidden" id="wbk_service_id_full_list">
<?php
} else {
?>
    <label class="wbk-input-label"><?php echo WBK_Validator::kses( get_option( 'wbk_service_label',  __( 'Select service', 'wbk' ) ) );  ?></label>
    <select class="wbk-select wbk-input wbk_services" id="wbk-service-id">
<?php
}
?>
    <option value="0" selected="selected"><?php echo $placeholder; ?></option>
<?php
foreach ( $service_ids as $service_id ) {
    $service = new WBK_Service( $service_id );
    if ( !$service->is_loaded() ){
        continue;
    }
    if( function_exists('pll__' ) ){
        $service_name =  pll__( $service->get_name( true ) );
    } else{
        $service_name = $service->get_name( true );
    }
    $service_name = apply_filters( 'wpml_translate_single_string', $service_name, 'wbk', 'Service name id ' . $service->get_id() );
    if( function_exists('pll__' ) ){
        $service_description =  pll__( $service->get_description( true ) );
    } else{
        $service_description = $service->get_description( true );
    }
    apply_filters( 'wpml_translate_single_string', $service->get_description( false ), 'wbk', 'Service description id ' . $service->get_id() );
    if(  get_option( 'wbk_show_service_description', 'disabled' ) == 'disabled' ){
?>
        <option value="<?php echo esc_attr( $service->get_id() ); ?>"  data-multi-low-limit="<?php echo esc_attr( $service->get_multi_mode_low_limit() ); ?>" data-multi-limit="<?php esc_attr( $service->get_multi_mode_limit() ); ?>" ><?php echo WBK_Validator::kses( $service_name ) ?></option>
<?php
    } else {
?>
        <option data-desc="<?php echo htmlspecialchars( WBK_Validator::kses(  $service_description ) ); ?>" value="<?php echo esc_attr( $service->get_id() ); ?>"  data-multi-low-limit="<?php echo esc_attr( $service->get_multi_mode_low_limit() ); ?>"  data-multi-limit="<?php echo esc_attr( $service->get_multi_mode_limit() ); ?>" ><?php echo WBK_Validator::kses( $service_name ); ?></option>
<?php
    }
}
?>
</select>
<?php
if( !$is_hidden ){
?>
<div class="wbk_description_holder" id="wbk_description_holder">
    <label class="wbk-input-label">

    </label>
</div>
<?php
}
?>
