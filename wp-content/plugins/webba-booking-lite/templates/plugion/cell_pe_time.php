<?php
if ( !defined( 'ABSPATH' ) ) exit;
/*
 * This file is part of Plugion framework.
 * (c) plugion.com <hello@plugion.org>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$field = $data[0];
$value = $data[2];
$row_data = $data[3];

if( isset( $row_data['duration'] ) && !is_null(isset( $row_data['duration'] ) ) && $row_data['duration'] != '' ){
    $duration = $row_data['duration'] * 60;
    $end = $value + $duration;
} else {
    $end = 0;
}

date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
$day = strtotime( $row_data['day'] );
date_default_timezone_set( 'UTC' );

if ( is_null( $value ) || $value == '' ) {
    return;
}

if( isset( $field->get_extra_data()['time_format'] ) ){
    $time_format =  $field->get_extra_data()['time_format'];
} else {
    $time_format = get_option('time_format');
}

if ( isset( $field->get_extra_data()['time_zone'] ) ) {
    $time_zone =  $field->get_extra_data()['time_zone'];
} else {
    $time_zone =  get_option('timezone_string');
}

$timezone_to_use =  new DateTimeZone( $time_zone );
$this_tz = new DateTimeZone( $time_zone );
$date = ( new DateTime('@' . $day ) )->setTimezone(new DateTimeZone( $time_zone ) );
$now = new DateTime('now', $this_tz);
$offset_sign = $this_tz->getOffset($date);
if ($offset_sign > 0) {
    $sign = '+';
} else {
    $sign = '-';
}
$offset_rounded =  abs($offset_sign / 3600);
$offset_int = floor($offset_rounded);
if (($offset_rounded - $offset_int) == 0.5) {
    $offset_fractional = ':30';
} else {
    $offset_fractional = '';
}
$timezone_utc_string = $sign . $offset_int . $offset_fractional;
$timezone_to_use =  new DateTimeZone($timezone_utc_string);
$timezone_to_use_end = $timezone_to_use;

$time = wp_date( $time_format, $value, $timezone_to_use );
if( get_option( 'wbk_date_format_time_slot_schedule', 'start' ) == 'start-end' && $end != 0){
    $time .= ' - ' . wp_date( $time_format, $end, $timezone_to_use );

}
echo $time;
