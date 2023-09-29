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

/**
 * catch and set the end time when timeslot is 10:00 or 14:00
 *
 * @return array Array of timeslots
 */
function cendrie_set_end_timeslots($timeslots, $day, $service_id)
{
	$disallawed_hours = ['13', '17'];
	foreach ($timeslots as $timeslot) {
		$end = $timeslot->get_end();
		$end_hour = date('H', $end);
		if (in_array($end_hour, $disallawed_hours)) {
			// Set start & end of the timeslot
			$end_timeslot = $end + SECONDS_IN_HOUR;
			$timeslot->set($timeslot->get_start(), $end_timeslot);

			// Set the formated time of the timeslot
			$hour_plus_one = (string) ((int) $end_hour + 1);
			$formated_time = str_replace($end_hour, $hour_plus_one, $timeslot->get_formated_time());
			$timeslot->set_formated_time($formated_time);

			// Set the formated time local of the timeslot
			$local_hour_plus_one = (string) ((int) $hour_plus_one + 1);
			$formated_time_local = str_replace($hour_plus_one, $local_hour_plus_one, $timeslot->get_formated_time_local());
			$timeslot->set_formated_time_local($formated_time_local);

			// Set the formated time backend of the timeslot
			$formated_time_backend = str_replace($end_hour, $hour_plus_one, $timeslot->get_formated_time_backend());
			$timeslot->set_formated_time_backend($formated_time_backend);
		}
	}
	return $timeslots;
}
add_filter('get_time_slots_by_day', 'cendrie_set_end_timeslots', 10, 3);

// function cendrie_set_correct_end_timeslot($form_html, $service_id, $first_time)
// {
// 	do_action('inspect', ['form html', $form_html]);
// 	// doing development...
// 	$patterns = array();
// 	$patterns[0] = '/13/';
// 	$patterns[1] = '/17/';
// 	$pat = '/13/';
// 	// $patterns[0] = '/10:00 - 13:00/';
// 	// $patterns[1] = '/14:00 - 17:00/';

// 	$replacements = array();
// 	$replacements[0] = '14';
// 	$replacements[1] = '18';
// 	$rep = '14';

// 	$form_html = preg_replace($pat, $rep, $form_html);
// 	// $form_html = preg_replace($patterns, $replacements, $form_html);

// 	return $form_html;
// }
// add_filter('wbk_form_html', 'cendrie_set_correct_end_timeslot', 10, 3);
