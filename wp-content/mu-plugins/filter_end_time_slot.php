<?php
/**
 * Plugin Name: Filter end time slot
 * Description: Change end time slot for edge case.
 * Author:      Julien Guibert
 * Version1: 25 03 2022
 */

// Basic security, prevents file from being loaded directly.
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

/**
 * Pass your custom function to the wp_rocket_loaded action hook.
 *
 * Note: wp_rocket_loaded itself is hooked into WordPress’ own
 * plugins_loaded hook.
 * Depending what kind of functionality your custom plugin
 * should implement, you can/should hook your function(s) into
 * different action hooks, such as for example
 * init, after_setup_theme, or template_redirect.
 * 
 * Learn more about WordPress actions and filters here:
 * https://developer.wordpress.org/plugins/hooks/
 */
// add_action( 'wp_rocket_loaded', 'yourprefix__do_something' );

/**
 * catch and set the end time when timeslot is 10:00 or 14:00
 * 
 * @return array Array of timeslots
 */
 function cendrie_filter_timeslots( $timeslots, $day, $service_id )
{
	
	foreach($timeslots as $timeslot){
		$formated_timeslot = $timeslot->get_formated_time();
		if($formated_timeslot == '10:00 - 13:00'){
			$timeslot->set($timeslot->getStart(), $timeslot->getEnd() + 3600);
			$timeslot->set_formated_time('10:00 - 14:00');
			$timeslot->set_formated_time_backend('10:00 - 14:00');
		} elseif ($formated_timeslot == '14:00 - 17:00'){
			$timeslot->set($timeslot->getStart(), $timeslot->getEnd() + 3600);
			$timeslot->set_formated_time('14:00 - 18:00');
			$timeslot->set_formated_time_backend('14:00 - 18:00');
		}
	}

	return $timeslots;
}
add_filter( 'get_time_slots_by_day', 'cendrie_filter_timeslots', 10, 3 );

function cendrie_set_correct_end_timeslot($form_html, $service_id, $first_time){
	// doing development...	
	$patterns = array();
	$patterns[0] = '/13/';
	$patterns[1] = '/17/';
	$pat = '/13/';
	// $patterns[0] = '/10:00 - 13:00/';
	// $patterns[1] = '/14:00 - 17:00/';

	$replacements = array();
	$replacements[0] = '14';
	$replacements[1] = '18';
	$rep = '14';

	$form_html = preg_replace($pat, $rep, $form_html);
	// $form_html = preg_replace($patterns, $replacements, $form_html);

	return $form_html;
}
add_filter('wbk_form_html', 'cendrie_set_correct_end_timeslot', 10, 3);

