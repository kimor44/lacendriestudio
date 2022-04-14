<?php
if ( !defined( 'ABSPATH' ) ) exit;

$slug = $data[0];
$default_value = $data[1];
$description = $data[2];
if( isset( $data[4] ) ){
    $dependency = json_encode( $data[4] );
} else {
    $dependency = json_encode( array() );

}
$value = get_option( $slug, $default_value );
?>

<div class='wbk_option_block' data-dependency = '<?php echo esc_attr( $dependency ); ?>'>
    <textarea style="width:350px; height:250px" name="<?php echo esc_attr( $slug ); ?>" id="<?php echo esc_attr( $slug ); ?>"><?php echo esc_attr( $value ); ?></textarea>
    <p class="description"><?php echo $description; ?></p>
</div>
