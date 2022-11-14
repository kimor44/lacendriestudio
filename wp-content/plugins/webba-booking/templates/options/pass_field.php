<?php
if ( !defined( 'ABSPATH' ) ) exit;

$slug = $data[0];
$default_value = $data[1];
if( isset( $data[4] ) ){
    $dependency = json_encode( $data[4] );
} else {
    $dependency = json_encode( array() );

}
$value = get_option( $slug, $default_value );
?>

<div class='wbk_option_block' data-dependency = '<?php echo esc_attr( $dependency ); ?>'>
    <input type="password" class="wbk_middle_field" id="<?php echo esc_attr( $slug ); ?>" name="<?php echo esc_attr( $slug ); ?>" value="<?php echo $value; ?>">
</div>
