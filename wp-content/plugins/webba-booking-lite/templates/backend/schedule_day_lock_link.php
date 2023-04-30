<?php
if ( !defined( 'ABSPATH' ) ) exit;
$service_id = $data[0];
$day_to_render = $data[1];
?>
<a class="green_font" id="day_lock_<?php echo esc_attr( $service_id . '_' . $day_to_render ) ?>"><?php echo esc_html( __( 'close', 'wbk' ) ); ?></a>
