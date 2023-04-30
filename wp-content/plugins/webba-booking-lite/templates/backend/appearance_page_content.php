<?php

$slf = new SoloFramework( 'wbk_settings_data' );
echo $slf->renderSectionSet( 'wbk_extended_appearance_options' );
					 

return;

if ( !defined( 'ABSPATH' ) ) exit;

$appearance_data = get_option( 'wbk_apperance_data' );

if( isset( $appearance_data[ 'wbk_appearance_field_1' ] ) ){
    $field_value_1 = $appearance_data[ 'wbk_appearance_field_1' ];
} else {
    $field_value_1 = '#ababab';
}

if( isset( $appearance_data[ 'wbk_appearance_field_2' ] ) ){
    $field_value_2 = $appearance_data[ 'wbk_appearance_field_2' ];
} else {
    $field_value_2 = '#ffffff';
}

if( isset( $appearance_data[ 'wbk_appearance_field_3' ] ) ){
    $field_value_3 = $appearance_data[ 'wbk_appearance_field_3' ];
} else {
    $field_value_3 = '#ffffff';
}

if( isset( $appearance_data[ 'wbk_appearance_field_4' ] ) ){
    $field_value_4 = $appearance_data[ 'wbk_appearance_field_4' ];
} else {
    $field_value_4 = '0';
}

?>

<div class="appearance-block-wrapper-wb">
    <div class="appearance-block-wb">
        <div class="left-part-wb">
            <div class="appearance-tabs-wb" data-js="appearance-tabs-wb">
                <div class="single-tab-wb active-wb" data-js="single-tab-wb" data-name="borders">
                    <div class="field-block-wb">
                        <div class="label-wb">
                            <label for="input-text-color-wb"><b>Color 1</b></label>
                        </div><!-- /.label-wb -->
                        <div class="field-wrapper-wb" data-js-block="color-picker-wrapper-wb">
                            <input type="color" value="<?php echo $field_value_1;?>" class="color-picker-wb input-wb" data-class="wbk-button,wbk-slot-button" data-property="background-color">
                            <input type="text"  class="input-text-color-wb input-text-wb input-wb" value="#ababab" data-class="wbk-button,wbk-slot-button" id="wbk_appearance_field_1" data-property="background-color" >
                        </div><!-- /.field-wrapper-wb -->
                    </div><!-- /.field-block-wb -->
                    <div class="field-block-wb">
                        <div class="label-wb">
                            <label for="input-text-color-wb"><b>Color 2</b></label>
                        </div><!-- /.label-wb -->
                        <div class="field-wrapper-wb" data-js-block="color-picker-wrapper-wb">
                            <input type="color" value="<?php echo $field_value_2;?>" class="color-picker-wb input-wb" data-class="wbk-button,wbk-slot-button" data-property="color">
                            <input type="text"  class="input-text-color-wb input-text-wb input-wb" value="#ffffff" data-class="wbk-button,wbk-slot-button" id="wbk_appearance_field_2" data-property="color" >
                        </div><!-- /.field-wrapper-wb -->
                    </div><!-- /.field-block-wb -->
                    <div class="field-block-wb">
                        <div class="label-wb">
                            <label for="input-text-color-wb"><b>Color 3</b></label>
                        </div><!-- /.label-wb -->
                        <div class="field-wrapper-wb" data-js-block="color-picker-wrapper-wb">
                            <input type="color" value="<?php echo $field_value_3;?>" class="color-picker-wb input-wb" data-class="wbk-slot-inner,wbk-slot-active-button" data-property="background-color">
                            <input type="text"  class="input-text-color-wb input-text-wb input-wb" value="#ffffff" data-class="wbk-slot-inner,wbk-slot-active-button" id="wbk_appearance_field_3" data-property="background-color" >
                        </div><!-- /.field-wrapper-wb -->
                    </div><!-- /.field-block-wb -->
                    <div class="field-block-wb">
                        <div class="label-wb">
                            Controls border radius
                        </div><!-- /.label-wb -->
                        <div class="field-wrapper-wb">
                            <input type="number" value="<?php echo $field_value_4;?>" class="input-text-wb border-radius-input-wb input-wb" data-class="wbk-input,wbk-button" id="wbk_appearance_field_4" data-property="border-radius">
                        </div><!-- /.field-wrapper-wb -->
                    </div><!-- /.field-block-wb -->
                </div><!-- /.single-tab-wb -->
            </div><!-- /.appearance-tabs-wb -->


            <div class="buttons-block-wb">
    			<button class="button-wb button-wb-appearance-save"><?php echo __( 'Save', 'wbk' ) ?><span class="btn-ring-wb"></span></button>
    		</div>
        </div><!-- /.left-part-wb -->
        <div class="right-part-wb">
            <div class="appearance-result-block-wb">
                <div class="appointment-box-wrapper-wb" data-appearance-font="" data-js-appointment-box-wrapper="">
                    <div class="appointment-box-w">
                            <?php
                                WBK_Renderer::load_template( 'backend/appearance_preview_v4', array(), true );
                             ?>
                    </div><!-- /.appointment-box-w -->
                </div><!-- /.appointment-box-wrapper-wb -->
            </div><!-- /.appearance-result-block-wb -->
        </div><!-- /.right-part-wb -->
    </div><!-- /.appearance-block-wb -->
</div><!-- /.appearance-block-wrapper-wb -->
