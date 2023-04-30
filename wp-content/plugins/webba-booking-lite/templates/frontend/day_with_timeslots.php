<?php
if ( !defined( 'ABSPATH' ) ) exit;
$date_format = WBK_Format_Utils::get_date_format();

$day_to_render = $data[0];
$timeslots = $data[1];
$offset = $data[2];
$service_id = $data[3];
$multi_service = $data[4];
$time_after = $data[5];

$pre_slot = '';
$post_slot = '';
if( count( $timeslots ) == 1 ){
    $pre_slot = '<div class="wbk-col-3-12">';
    $post_slot = '</div>';
}

if( $multi_service ){
    $service = new WBK_Service( $service_id );
    $title =  '<div class="wbk-col-12-12"><label class="wbk-multiple-service-title">' . $service->get_name() . '</label></div>';
} else {
    $title =  WBK_Renderer::load_template( 'frontend/day_title', array( $day_to_render, $offset, $timeslots ), false );
}
$time_slots =  WBK_Renderer::load_template( 'frontend/day_timeslots', array( $timeslots, $day_to_render, $service_id, $offset, $time_after ), false );
 

if( $time_slots != '' ){
    echo $pre_slot . $title . $time_slots . $post_slot;
}
?>
