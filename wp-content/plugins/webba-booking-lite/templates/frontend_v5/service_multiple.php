<?php
if ( !defined( 'ABSPATH' ) ) exit;
$service_ids = $data[0];
 
?>
<?php

 
?>
    <div class="category-content-w">
        <div class="field-row-w"> 
            <label class="wbk_service_label wbk_hidden"><?php echo WBK_Validator::kses( get_option( 'wbk_service_label',  __( 'Select service', 'webba-booking-lite' ) ) );  ?></label>
                 
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
?>
     <li class="wbk_service_item timeslot-animation-w wbk_hidden" data-servicei_id="<?php echo esc_attr( $service->get_id() ); ?>" >
        <label class="checkbox-row-w   wbk_service_checkbox_holder">


            <span class="checkbox-custom-w">
                <input class="wbk-input wbk_service_checkbox wbk_services" data-payable="<?php echo esc_attr( $payable ); ?>"  name="initial_services[]" data-validation="must_have_items" type="checkbox" value="<?php echo esc_attr( $service->get_id() ); ?>">
                <span class="checkmark-w"></span>
            </span> 
            <span class="checkbox-text-w">
                <span class="wbk_single_service_title"><?php echo $service_name ?></span>
                <img class="wbk_service_sub_img" src="<?php echo WP_WEBBA_BOOKING__PLUGIN_URL . '/public/images/clock_grey.png"'; ?>   height="20"> 
                <img class="wbk_service_sub_img_active" src="<?php echo WP_WEBBA_BOOKING__PLUGIN_URL . '/public/images/clock_white.png"'; ?>   height="20"> 
                <span class="wbk_single_service_sub_title">
                    <?php echo $service->get_duration()  . ' ' . __( 'min', 'webba-booking-lite' ); ?>
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
            </span> 


        </label>
     </li>


<?php
    }
?>   
    </ul>    
            
 
        </div> 
    </div>
  
 
 