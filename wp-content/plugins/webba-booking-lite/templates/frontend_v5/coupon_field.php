<?php
if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="field-row-w ">
    <label><?php echo esc_html__( 'Coupon', 'webba-booking-lite') ?></label>
    <input type="text" class="input-text-w wbk-input wbk_input_small wbk_coupon_input" name="coupon">
    <button type="button" class="button-w wbk_apply_coupon" disabled=""><?php echo __( 'Apply', 'webba-booking-lite' ) ?><span class="btn-ring-wb" style="opacity: 0;"></span></button>
</div>