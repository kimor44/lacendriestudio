<?php
if ( !defined( 'ABSPATH' ) ) exit;

$slug = $data[0];
$default_value = $data[1];
$value = get_option( $slug, $default_value );
$description = $data[2];
$extra = $data[3];
if( isset( $data[4] ) ){
    $dependency = json_encode( $data[4] );
} else {
    $dependency = json_encode( array() );

}
?>

<div class='wbk_option_block' data-dependency = '<?php echo esc_attr( $dependency ); ?>'>
    <select id="<?php echo esc_attr( $slug ); ?>" name="<?php echo esc_attr( $slug ); ?>">
    <?php
        foreach( $extra as $key => $value_this ){
            echo '<option '. selected( $value, $key, false ) . ' value="' . esc_attr( $key ) . '">' . esc_html( $value_this ) . '</option>';
        }
    ?>
    </select>
    <p class="description"><?php echo $description?></p>
</div>
