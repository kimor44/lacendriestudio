<?php
if ( !defined( 'ABSPATH' ) ) exit;
$service_ids = $data[0];
?>

<label class="wbk-input-label wbk_service_label wbk_hidden"><?php echo WBK_Validator::kses( get_option( 'wbk_service_label',  __( 'Select service', 'webba-booking-lite' ) ) );  ?></label>
<ul class="wbk_v5_service_list">

<?php

foreach ( $service_ids as $service_id ) {
    $service = new WBK_Service( $service_id );
    if ( !$service->is_loaded() ){
        continue;
    }
    if( $service->get('payment_methods') == '' ){
        $payable = 'false';
    } else {
        if( get_option( 'wbk_appointments_default_status', '') == 'pending' &&  get_option( 'wbk_appointments_allow_payments', '') == 'enabled' ){
            $payable = 'false';
        } else {
            $payable = 'true';
        }
    }
    if( function_exists('pll__' ) ){
        $service_name =  pll__( $service->get_name( true ) );
    } else{
        $service_name = $service->get_name( true );
    }
    $service_name = apply_filters( 'wpml_translate_single_string', $service_name, 'webba-booking-lite', 'Service name id ' . $service->get_id() );
    if( function_exists('pll__' ) ){
        $service_description =  pll__( $service->get_description( true ) );
    } else{
        $service_description = $service->get_description( true );
    }
    apply_filters( 'wpml_translate_single_string', $service->get_description( false ), 'webba-booking-lite', 'Service description id ' . $service->get_id() );
?>
    <li class="wbk_service_item  timeslot-animation-w wbk_hidden" data-servicei_id="<?php echo esc_attr( $service->get_id() ); ?>" >
        <label class="custom-radiobutton-w">
            <span style="display:inline-block">
                <span class="wbk_single_service_title"><?php echo $service_name ?></span>
                <img class="wbk_service_sub_img" src="<?php echo WP_WEBBA_BOOKING__PLUGIN_URL . '/public/images/clock_grey.png"'; ?>   height="20"> 
                <img class="wbk_service_sub_img_active" src="<?php echo WP_WEBBA_BOOKING__PLUGIN_URL . '/public/images/clock_white.png"'; ?>   height="20"> 
                <span class="wbk_single_service_sub_title">
                    <?php echo $service->get_duration()  . ' ' . esc_html( get_option( 'wbk_minutes_label', __( 'min', 'webba-booking-lite' ) ) ); ?>
                </span>
                <?php
                    if( $service->get_price() > 0){
                ?>
                    <img class="wbk_service_sub_img" src="<?php echo WP_WEBBA_BOOKING__PLUGIN_URL . '/public/images/money_grey.png"'; ?>   height="20"> 
                    <img class="wbk_service_sub_img_active" src="<?php echo WP_WEBBA_BOOKING__PLUGIN_URL . '/public/images/money_white.png"'; ?>   height="20"> 
                    <span class="wbk_single_service_sub_title">
                        <?php echo  WBK_Format_Utils::format_price( $service->get_price() ) ?>
                    </span>
                <?php
                    }
                ?>
            </span><br>
            <input type="radio" data-payable="<?php echo esc_attr( $payable ); ?>" class="wbk-input wbk_hidden wbk_services wbk_service_radio" data-validation="positive"  name="service" value="<?php echo $service->get_id(); ?>">
            <span class="checkmark-w">  
            </span>
        </label>              
    </li>
<?php
}            
?>
</ul>
 


 