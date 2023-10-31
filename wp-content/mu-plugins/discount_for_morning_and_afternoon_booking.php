<?php

/*
 * Plugin Name:       Discount for morning and afternoon booking
 * Description:       Apply discount when morning & afternoon of the same day are applied.
 * Version:           1.0.0
 * Requires PHP:      7.2
 * Author:            Julien Guibert
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

// Basic security, prevents file from being loaded directly.
defined('ABSPATH') or die('Cheatin&#8217; uh?');

// Applied price for the timeslot when 2 consecutive bookings in the same morning
define("DISCOUNT_OF_5_EUROS", 5);

/**
 * Check if the morning of the current booking is booked
 *
 * @param string $start number of start timeslot
 * @param int $day timestamp of the current booking's day
 * @param WBK_Booking[] $timeslots array of timeslots
 * @return bool true if th morning of the current booking is booked
 */
function is_morning_and_afternoon_booked(string $start, int $day, array $timeslots): bool
{
  foreach ($timeslots as $slot) {
    $slot_start = (int) $slot->get_start();
    $start = (int) $start;

    if ($slot->get_day() === $day && $slot_start !== $start) {
      if (in_array(date('H', $slot_start), MORNING_START_HOURS)) {
        return true;
      }
    }
  }

  return false;
}

/**
 * Apply discount if morning & afternoon of the same day are booked
 * Retrieve origingal file :
 * wp-content/plugins/webba-booking-lite/includes/processors/class-wbk-price-processor.php
 *
 * @param string $default_price Price applied before discount
 * @param WBK_Booking $booking Data of the current booking
 * @param WBK_Booking[] $bookings array of bookings
 * @return string the new price applied
 */
function discount_for_booking_morning_and_afternoon_of_the_same_day(string $default_price, WBK_Booking $booking, array $bookings): string
{
  $cloned_default_price = $default_price;
  $start = $booking->get_start();
  $start_hour = date('H', $start);

  if (!in_array($start_hour, MORNING_START_HOURS)) {
    return $cloned_default_price;
  }
  if (!is_morning_and_afternoon_booked($start, $booking->get_day(), $bookings)) {
    return $cloned_default_price;
  }

  return (string) (intval($cloned_default_price) - DISCOUNT_OF_5_EUROS);;
}
add_filter('webba_after_pricing_rule_applied', 'discount_for_booking_morning_and_afternoon_of_the_same_day', 10, 3);
