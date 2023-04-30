<?php
if ( !defined( 'ABSPATH' ) ) exit;
/*
 * This file is part of Webba Booking plugin



 */

$field = $data[0];
$slug = $data[1];

?>

<div class="plugion_input_container">
    <label for="<?php echo esc_attr( $slug ); ?>" class="plugion_input_label"><?php echo esc_html( $field->get_title() ); ?></label>
    <textarea  id="<?php echo esc_attr( $slug ) ?>" name="<?php echo esc_attr( $field->get_name() ); ?>" data-default="<?php echo esc_attr( $field->get_default_value() ); ?>" class="plugion_input plugion_input_text plugion_input_textarea plugion_input_editor plugion_property_input" type="text" required data-validation="editor" data-setter="editor" data-getter="editor" data-required="<?php echo esc_attr( $field->get_required() ); ?>"></textarea>
</div>
