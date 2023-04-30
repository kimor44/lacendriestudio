<?php
if ( !defined( 'ABSPATH' ) ) exit;
/*
 * This file is part of Webba Booking plugin



 */

$field = $data[0];
$slug = $data[1];
$filter_extra = $field->get_filter_extra();

?>

<div class="plugion_input_container">
    <select id="<?php echo esc_attr( $slug ) ?>" name="<?php echo esc_html( $field->get_name() ); ?>" class="plugion_input plugion_input_select plugion_filter_input" data-validation="select" data-default="" data-required="1">
<?php
    $items = $field->get_filter_extra();
    $i = 0;
    foreach( $items as $key => $value ){
 ?>
        <option value="<?php echo esc_attr( $key) ?>"><?php echo esc_html( $value ) ?></option>
<?php
        $i++;
    }
?>
</select>
    <label for="<?php echo esc_attr( $slug ) ?>" name="<?php echo esc_attr( $slug ) ?>" class="plugion_input_select_label"><?php echo esc_html( $field->get_title() ) ?></label>
</div>
