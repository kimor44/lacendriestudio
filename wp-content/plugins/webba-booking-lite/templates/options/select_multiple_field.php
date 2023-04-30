<?php
if ( !defined( 'ABSPATH' ) ) exit;
$slug = $data[0];
$temp_array = $data[1];
$default_value = array();
if( is_array( $temp_array ) ){
    foreach( $temp_array as $key => $value ){
        $default_value[] = $key;
    }
} 
$value = get_option( $slug, $default_value);
 
if( !is_array($value) ){
    $value = array();
}
 
$description = $data[2];
$extra = $data[3];

 

if( isset( $data[4] ) ){
    $dependency = json_encode( $data[4] );
} else {
    $dependency = json_encode( array() );
}
?>
<div class='wbk_option_block' data-dependency = '<?php echo esc_attr( $dependency ); ?>'>
    <select class="wbk_option_field_select_multiple" id="<?php echo esc_attr( $slug ); ?>[]" name="<?php echo esc_attr( $slug ); ?>[]" multiple>
    <?php
        foreach( $extra as $key => $value_this ){          
            if( in_array( $key, $value ) ){
                $selected = 'selected';
            } else {
                $selected = '';
            }
            echo '<option ' . $selected . ' value="' . esc_attr( $key ) . '">' . esc_html( $value_this ) . '</option>';
        }
    ?>
    </select>
    <p class="description"><?php echo $description?></p>
</div>
