<?php
if ( !defined( 'ABSPATH' ) ) exit;

$slug = $data[0];
$default_value = $data[1];
$description = $data[2];
$value = get_option( $slug, $default_value );
if( isset( $data[4] ) ){
    $dependency = json_encode( $data[4] );
} else {
    $dependency = json_encode( array() );
}
?>
<div class='wbk_option_block' data-dependency = '<?php echo esc_attr( $dependency ); ?>'>
    <input type="checkbox" <?php echo checked( 'true', $value, false ) ?> id="<?php echo esc_attr( $slug ); ?>" name="<?php echo esc_attr( $slug ); ?>" value="true">
    <br><label for="<?php echo esc_attr( $slug ); ?>"><?php echo $description ?></label>
</div>
