<?php
if ( !defined( 'ABSPATH' ) ) exit;
/*
 * This file is part of Webba Booking plugin



 */

$field = $data[0];
$slug = $data[1];
$data = $field->get_extra_data();
$keys = array_keys( $data );
?>
<span class="plugion_input_label"><?php echo esc_html( $field->get_title() ); ?></span>
<div class="plution_checkbox">
    <input id="<?php echo esc_attr( $slug ) ?>" name="<?php echo esc_attr( $field->get_name() ); ?>"  class="plugion_input plugion_input_checkbox plugion_property_input" type="checkbox"  value="<?php echo esc_attr( $keys[0] );?>" data-validation="checkbox" data-getter="checkbox"  data-setter="checkbox">
    <label for="<?php echo esc_attr( $slug ) ?>"><?php echo esc_html( $data[ $keys[0] ] );?></label>
</div>
