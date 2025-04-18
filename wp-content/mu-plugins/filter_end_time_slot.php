<?php

/*
 * Plugin Name:       Filter end time slot
 * Description: Change end time slot for edge case.
 * Version:           2.0.0
 * Requires PHP:      7.2
 * Author:            Julien Guibert
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

// Basic security, prevents file from being loaded directly.
defined('ABSPATH') or die('Cheatin&#8217; uh?');

// Number of seconds in one hour constant
define("SECONDS_IN_HOUR", 60 * 60);

// End hours of the morning timeslots
define("MORNING_END_HOURS", ['10' => '14', '14' => '18']);

// Start hours of the morning timeslots
define("MORNING_START_HOURS", array_keys(MORNING_END_HOURS));

/**
 * catch and set the end time when timeslot is 10:00 or 14:00
 * Retrieve original file :
 * wp-content/plugins/webba-booking-lite/includes/processors/class-wbk-schedule-processor.php
 *
 * @return array Array of timeslots
 */
function cendrie_set_end_timeslots($timeslots, $day, $service_id): array
{
	foreach ($timeslots as $timeslot) {
		$start = $timeslot->get_start();
		$start_hour = date('H', $start);
		if (in_array($start_hour, MORNING_START_HOURS)) {
			/**
			 * global variables
			 */
			$corrected_end_time = MORNING_END_HOURS[$start_hour];
			$end = $timeslot->get_end();
			$end_hour = date('H', $end);

			/**
			 * set (timestamps start & end)
			 */
			$corrected_ending_timestamp = $end + SECONDS_IN_HOUR;
			$timeslot->set($start, $corrected_ending_timestamp);

			/**
			 * set_formated_time
			 */
			$corrected_formated_time = str_replace($end_hour, $corrected_end_time, $timeslot->get_formated_time());
			$timeslot->set_formated_time($corrected_formated_time);

			/**
			 * set_formated_time_local
			 */
			$corrected_formated_time_local = $corrected_formated_time;
			$timeslot->set_formated_time_local($corrected_formated_time_local);

			/**
			 * set_formated_time_backend
			 */
			$corrected_formated_time_backend = str_replace($end_hour, $corrected_end_time, $timeslot->get_formated_time_backend());
			$timeslot->set_formated_time_backend($corrected_formated_time_backend);

			/**
			 * set_offset
			 */
			$timeslot->set_offset(0);
		}
	}
	return $timeslots;
}
add_filter('get_time_slots_by_day', 'cendrie_set_end_timeslots', 10, 3);

/**
 * Retrieve original files :
 * wp-content/plugins/webba-booking-lite/deprecated/class_wbk_service_schedule.php
 * wp-content/plugins/webba-booking-lite/templates/backend/schedule_day_timeslot.php
 * wp-content/plugins/webba-booking-lite/templates/backend/schedule_day_timeslots.php
 */
function cendrie_time_control($time_controls, $timeslot, $service_id)
{
	if ($timeslot->offset !== 0) {
		$offset = $timeslot->offset;
		$timeslot->set(($timeslot->start - $offset), ($timeslot->end - $offset));
		$timeslot->set_formated_time_local($timeslot->formated_time);
		$timeslot->set_offset(0);
	}

	return $time_controls;
}
add_filter('wbk_backend_schedule_time_controls', 'cendrie_time_control', 10, 3);

/**
 * Retrieve original file :
 * wp-content/plugins/webba-booking-lite/includes/class-wbk-request-manager.php
 */
function cendrie_form_validation($wbk_external_validation, $POST)
{
	$POST['offset'] = '0';
	return $wbk_external_validation;
}
add_filter('wbk_booking_form_validation', 'cendrie_form_validation', 10, 2);
