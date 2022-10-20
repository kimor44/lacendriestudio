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

$mcesettings = [];
$mcesettings['valid_elements'] ='*[*]';
$mcesettings['extended_valid_elements'] = '*[*]';

$args = [
    'media_buttons' => false,
    'editor_height' => 300,
    'tinymce' => $mcesettings
];
?>
<div class='wbk_option_block' data-dependency = '<?php echo esc_attr( $dependency ); ?>'>
    <?php wp_editor( $value, $slug, $args ); ?>
    <p class="description"><?php echo $description; ?></p>
</div>
