<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( wbk_fs()->is__premium_only() ) {
    if ( wbk_fs()->can_use_premium_code() ) {
        if( !class_exists( 'Eluceo\iCal\Component') ){
            require_once('classes/Component.php');
            require_once('classes/ParameterBag.php');
            require_once('classes/Property.php');
            require_once('classes/PropertyBag.php');
            require_once('classes/Property/ValueInterface.php');
            require_once('classes/Property/ArrayValue.php');
            require_once('classes/Property/DateTimeProperty.php');
            require_once('classes/Property/DateTimesProperty.php');
            require_once('classes/Property/StringValue.php');
            require_once('classes/Property/RawStringValue.php');
            require_once('classes/Component/Alarm.php');
            require_once('classes/Component/Calendar.php');
            require_once('classes/Component/Event.php');
            require_once('classes/Component/Timezone.php');
            require_once('classes/Component/TimezoneRule.php');
            require_once('classes/Property/Event/Attendees.php');
            require_once('classes/Property/Event/Geo.php');
            require_once('classes/Property/Event/Organizer.php');
            require_once('classes/Property/Event/RecurrenceId.php');
            require_once('classes/Property/Event/RecurrenceRule.php');
            require_once('classes/Util/ComponentUtil.php');
            require_once('classes/Util/DateUtil.php');
        }
    }
}

class WBK_Ical {
    static public function generateICal( $appointment_ids, $type = 'admin' ) {
        if ( wbk_fs()->is__premium_only() ) {
            if ( wbk_fs()->can_use_premium_code() ) {
                $time_zone = get_option( 'wbk_timezone', 'UTC' );
                date_default_timezone_set($time_zone);
                $utc_timezone = new DateTimeZone('UTC');
                $domain = parse_url(get_site_url(), PHP_URL_HOST);
                $vCalendar = new \Eluceo\iCal\Component\Calendar( $domain );
                $range_selection = get_option( 'wbk_range_selection', 'enabled' );
                $initial_start = '';
                foreach($appointment_ids as $appointment_id) {
                    $service_id = WBK_Db_Utils::getServiceIdByAppointmentId( $appointment_id );
                    $appointment = new WBK_Appointment_deprecated();
                    if ( !$appointment->setId( $appointment_id ) ) {
                        continue;
                    }
                    if ( !$appointment->load() ) {
                        continue;
                    }
                    $service = new WBK_Service_deprecated();
                    if ( !$service->setId( $service_id ) ) {
                        continue;
                    }
                    if ( !$service->load() ) {
                        continue;
                    }
                    if( $type == 'admin' ){
                        $title = get_option( 'wbk_gg_calendar_event_title', '#customer_name' );
                        $description = get_option( 'wbk_gg_calendar_event_description', '#customer_name #customer_phone' );
                        $description = str_replace( '{n}', "\n",  $description );
                    } elseif ( $type == 'customer' ){
                        $title = get_option( 'wbk_gg_calendar_event_title_customer', '#service_name' );
                        $description = get_option( 'wbk_gg_calendar_event_description_customer', 'Your appointment id is #appointment_id' );
                        $description = str_replace( '{n}', "\n",  $description );
                    }
                    $title = WBK_Db_Utils::subject_placeholder_processing_gg( $title, $appointment, $service );
                    $description = WBK_Db_Utils::message_placeholder_processing_gg( $description, $appointment, $service );

                    $start = date( 'Y-m-d', $appointment->getTime()  ) . 'T' . date(  'H:i:00', $appointment->getTime()  );
                    $end = date( 'Y-m-d', $appointment->getTime() + $service->getDuration() * 60  ) . 'T' . date(  'H:i:00', $appointment->getTime() + $service->getDuration() * 60  );

                    if( $range_selection == 'disabled' ){
                        $vEvent = new \Eluceo\iCal\Component\Event($domain . '_appointment_' . $appointment_id);

                        $start = new \DateTime($start,  new DateTimeZone($time_zone));
                        $start->setTimezone($utc_timezone);

                        $end = new \DateTime($end,  new DateTimeZone($time_zone));
                        $end->setTimezone($utc_timezone);

                        $vEvent->setDtStart($start);
                        $vEvent->setDtEnd($end);

                        $vEvent->setSummary($title);
                        $vEvent->setDescription($description);

                        $vCalendar->addComponent($vEvent);

                    } else {
                        if( $initial_start == '' ){
                            $initial_start = $start;
                        }
                    }
                }
                if( $range_selection == 'enabled' ){
                    $vEvent = new \Eluceo\iCal\Component\Event($domain . '_appointment_' . implode('-', $appointment_ids ) );

                    $start = new \DateTime($initial_start,  new DateTimeZone($time_zone));
                    $start->setTimezone($utc_timezone);

                    $end = new \DateTime($end,  new DateTimeZone($time_zone));
                    $end->setTimezone($utc_timezone);

                    $vEvent->setDtStart($start);
                    $vEvent->setDtEnd($end);

                    $vEvent->setSummary($title);
                    $vEvent->setDescription($description);

                    $vCalendar->addComponent($vEvent);

                }
                $file_prefix = '';
                if( $type == 'customer' ){
                    $file_prefix = 'c_';
                }

                $filename = get_temp_dir() . 'calendar_' . $file_prefix . implode( '_', array_values($appointment_ids) ) . '_' . time() . '.ics';
                file_put_contents($filename, $vCalendar->render());
                return $filename;
            }
        }
        return '';
    }
}
