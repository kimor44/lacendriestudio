<?php
// check if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
class WBK_Time_Math_Utils {
	public static function check_range_intersect( $start, $end, $start_compare, $end_compare ){
		if ( $start_compare == $start ){
			return true;
		}
		if ( $start_compare > $start && $start_compare < $end ){
			return true;
		}
		if ( $end_compare > $start && $end_compare <= $end  ){
			return true;
		}
		if ( $start >= $start_compare && $end <= $end_compare  ){
			return true;
		}
		if ( $start <= $start_compare && $end >= $end_compare  ){
			return true;
		}
		return false;
	}
	public static function adjust_times( $time_1, $time_2, $time_zone ){
		$tz = new DateTimeZone( $time_zone );
		$transition = $tz->getTransitions( $time_1, $time_1 );
		$offset1 = $transition[0]['offset'];
		$transition = $tz->getTransitions( $time_1 + $time_2, $time_1 + $time_2 );
		$offset2 = $transition[0]['offset'];
		$difference = $offset1 - $offset2;
		if( $difference < 0 ){
			$difference = 0;
		}
 		return $time_1 + $time_2 + $difference;
	}
	public static function get_offset_difference_with_midnight( $time ){

		$prev_time_zone = date_default_timezone_get();
		date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
		$time_midnight = strtotime( date( 'Y-m-d 00:00:00', $time ) );
		date_default_timezone_set( $prev_time_zone );

		$tz = new DateTimeZone( get_option( 'wbk_timezone', 'UTC' ) );
		$transition = $tz->getTransitions( $time_midnight, $time_midnight );
		$offset1 = $transition[0]['offset'];
		$transition = $tz->getTransitions( $time, $time );
		$offset2 = $transition[0]['offset'];
		$difference = $offset1 - $offset2;
		return $difference;
	}

	public static function get_utc_offset_by_time( $time ){
		$prev_time_zone = date_default_timezone_get();
		date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
		$date = strtotime( date( 'Y-m-d 00:00:00', $time ) );
		date_default_timezone_set( $prev_time_zone );
		$time_zone =  get_option( 'wbk_timezone', 'UTC' );
		$timezone_to_use =  new DateTimeZone( $time_zone );
		$this_tz = new DateTimeZone( $time_zone );
		$date = ( new DateTime('@' . $date ) )->setTimezone(new DateTimeZone( $time_zone ) );
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
		return new DateTimeZone($timezone_utc_string);

	}

}
?>