<?php

namespace Sabre\VObject\ITip;

use Sabre\VObject\Component\VCalendar;

/**
 * This class defines the base interface of the various brokers.
 * Most likely you will just want to use the Broker object though.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
abstract class AbstractBroker {

    /**
     * This setting determines whether the rules for the SCHEDULE-AGENT
     * parameter should be followed.
     *
     * This is a parameter defined on ATTENDEE properties, introduced by RFC
     * 6638. This parameter allows a caldav client to tell the server 'Don't do
     * any scheduling operations'.
     *
     * If this setting is turned on, any attendees with SCHEDULE-AGENT set to
     * CLIENT will be ignored. This is the desired behavior for a CalDAV
     * server, but if you're writing an iTip application that doesn't deal with
     * CalDAV, you may want to ignore this parameter.
     *
     * @var bool
     */
    public $scheduleAgentServerRules = true;

    /**
     * The broker will try during 'processICalendarChange' figure out whether
     * the change was significant.
     *
     * It uses a few different ways to do this. One of these ways is seeing if
     * certain properties changed values. This list of specified here.
     *
     * This list is taken from:
     * * http://tools.ietf.org/html/rfc5546#section-2.1.4
     *
     * @var string[]
     */
    public $significantChangeProperties = [
        'DTSTART',
        'DTEND',
        'DURATION',
        'DUE',
        'RRULE',
        'RDATE',
        'EXDATE',
        'STATUS',
    ];

    /**
     * This method takes an old and a new iCalendar object, and based on the
     * difference it tries to determine if iTip messages must be sent.
     *
     * For example, if a DTSTART was changed on an event, and the event had
     * one or more attendees, this method will generate an iTip message for
     * every attendee to notify them of the change.
     *
     * Both the old and the new iCalendar object may be omitted, but not both.
     * If the old iCalendar object was omitted, this method will treat this as
     * if a new event is being created.
     *
     * If the new iCalendar object is omitted, this method will treat it as if
     * it was deleted. A deletion might for example automatically trigger a
     * "CANCELLED" iTip message for an organizer, or a "DECLINED" iTip message
     * for an attendee.
     *
     * You must specify 1 or more uris for the current user. We need that
     * information to figure out who is actually making the change. We're
     * actually comparing this to the values of ATTENDEE and ORGANIZER.
     *
     * @param VCalendar $before
     * @param VCalendar $after
     * @param string|string[] $userUri
     * return Message[]
     */
    abstract function processICalendarChange(VCalendar $before = null, VCalendar $after = null, $userUri);

    /**
     * This messages takes an iTip message as input, and transforms an
     * iCalendar message based on it's input.
     *
     * Some examples:
     *
     * 1. A user is an attendee to an event. The organizer sends an updated
     * meeting using a new iTip message with METHOD:REQUEST. This function
     * will process the message and update the attendee's event accordingly.
     *
     * 2. The organizer cancelled the event using METHOD:CANCEL. We will update
     * the users event to state STATUS:CANCELLED.
     *
     * 3. An attendee sent a reply to an invite using METHOD:REPLY. We can
     * update the organizers event to update the ATTENDEE with its correct
     * PARTSTAT.
     *
     * The $existingObject is updated in-place. If there is no existing object
     * (because it's a new invite for example) a new object will be created.
     *
     * If an existing object does not exist, and the method was CANCEL or
     * REPLY, the message effectively gets ignored, and no 'existingObject'
     * will be created.
     *
     * If the iTip message is not supported, this method will not return
     * anything.
     *
     * @param Message $message
     * @param VCalendar $existingObject
     * @return VCalendar|null
     */
    abstract function applyITipMessage(Message $message, VCalendar $existingObject = null);

    /**
     * This method takes an calendar and spits out a lot of useful
     * information required for scheduling.
     *
* It returns an array with the following elements:
     *
     * * uid - The UID of the scheduled object
     * * component - The component type, for example VEVENT
     * * organizer - The organizer URI, for example mailto:foo@example.org
     * * organizerName - The human readable organizer name, or null
     * * organizerScheduleAgent - The value for the SCHEDULE-AGENT parameter for
     *   the organizer.
     * * organizerForceSend - The value for the FORCE-SEND parameter for the
     *   organizer
     * * instances - A list of components. For example a VEVENT with a
     *   recurrence rule and one exception would have 2 instances: the master
     *   instance and the override. This array is indexed by the RECURRENCE-ID
     *   or the keyword "master".
     * * attendees - A list of attendees, indexed by their URI. Each attendee
     *   has the following information embedded:
     *   * href - a repitition of the URI
     *   * name - A human readable name, or null if not available
     *   * forceSend - The value for the FORCE-SEND parameter.
     *   * instances - An array of instances the attendee is involved in. This
     *     is indexed by the instance RECURRENCE-ID or the keyword "master".
     *     each instance has the following information:
     *     * id - the recurrence id, or master
     *     * partstat - The value for PARTSTAT for that instance for the
     *       attendee.
     * * sequence - The value for the SEQUENCE property.
     * * timezone - In case there's a recurrence rule, this specifies the
     *   timezone the recurrence rule is in.
     * * status - The value for the STATUS property, for example 'CANCELLED'.
     * * exDate - Basically the value for the EXDATE property of the master
     *   event.
     * * significantChangeHash - This is an md5 hash of properties and their
     *   values we consider 'significant'. If the hash changes, it can be
     *   assumed that the change was significant, which may lead to different
     *   decisions. A system might for instance only send out emails for
     *   significant changes.
     *
     * @param VCalendar $calendar
     * @throws ITipException In case invalid data was found.
     * @return array
     */
    protected function extractSchedulingInfo(VCalendar $calendar) {
    
        $result = [
            'uid' => null,
            'component' => null,
            'organizer' => null,
            'organizerName' => null,
            'organizerForceSend' => null,
            'organizerScheduleAgent' => 'SERVER',
            'sequence' => null,
            'timezone' => null,
            'status' => null,
            'significantChangeHash' => '',
            'attendees' => [],
            'instances' => [],
            'exdate' => [],
        ];

        $significantChangeHash = '';

        foreach($calendar->getComponents() as $component) {

            if ($component->name === 'VTIMEZONE') {
                continue;
            }

            // Component
            if (is_null($result['component'])) {
                $result['component'] = $component->name;
            } else {
                if ($result['component'] !== $component->name) {
                    throw new ITipException('All components in a iTip message must have be of the same type.');
                }
            }

            // UID
            if (is_null($result['uid'])) {
                $result['uid'] = $component->UID->getValue();
            } else {
                if ($result['uid'] !== $component->UID->getValue()) {
                    throw new ITipException('All components in a iTip message must have the same UID');
                }
            }

            if (isset($component->ORGANIZER)) {
                if (is_null($result['organizer'])) {
                    $result['organizer'] = $component->ORGANIZER->getNormalizedValue();
                    $result['organizerName'] = isset($component->ORGANIZER['CN']) ? (string)$component->ORGANIZER['CN'] : null;
                } else {
                    if ($result['organizer'] !== $component->ORGANIZER->getNormalizedValue()) {
                        throw new SameOrganizerForAllComponentsException('Every instance of the event must have the same organizer.');
                    }
                }
                $result['organizerForceSend'] =
                    isset($component->ORGANIZER['SCHEDULE-FORCE-SEND']) ?
                    strtoupper($component->ORGANIZER['SCHEDULE-FORCE-SEND']) :
                    null;
                $result['organizerScheduleAgent'] =
                    isset($component->ORGANIZER['SCHEDULE-AGENT']) ?
                    strtoupper((string)$component->ORGANIZER['SCHEDULE-AGENT']) :
                    'SERVER';
            }

            if (is_null($result['sequence']) && isset($component->SEQUENCE)) {
                $result['sequence'] = $component->SEQUENCE->getValue();
            }

            if (isset($component->EXDATE)) {
                foreach ($component->select('EXDATE') as $val) {
                    $result['exdate'] = array_merge($result['exdate'], $val->getParts());
                }
                sort($result['exdate']);
            }
            if (isset($component->STATUS)) {
                $result['status'] = strtoupper($component->STATUS->getValue());
            }

            $recurId = isset($component->{'RECURRENCE-ID'}) ? $component->{'RECURRENCE-ID'}->getValue() : 'master';

            if (is_null($result['timezone'])) {
                if (isset($component->{'RECURRENCE-ID'})) {
                    $result['timezone'] = $component->{'RECURRENCE-ID'}->getDateTime()->getTimeZone();
                } elseif (isset($component->DTSTART)) {
                    $result['timezone'] = $component->DTSTART->getDateTime()->getTimeZone();
                }
            }
            if (isset($component->ATTENDEE)) {
                foreach ($component->ATTENDEE as $attendee) {

                    if ($this->scheduleAgentServerRules &&
                        isset($attendee['SCHEDULE-AGENT']) &&
                        strtoupper($attendee['SCHEDULE-AGENT']->getValue()) === 'CLIENT'
                    ) {
                        continue;
                    }
                    $partStat =
                        isset($attendee['PARTSTAT']) ?
                        strtoupper($attendee['PARTSTAT']) :
                        'NEEDS-ACTION';

                    $forceSend =
                        isset($attendee['SCHEDULE-FORCE-SEND']) ?
                        strtoupper($attendee['SCHEDULE-FORCE-SEND']) :
                        null;


                    if (isset($result['attendees'][$attendee->getNormalizedValue()])) {
                        $result['attendees'][$attendee->getNormalizedValue()]['instances'][$recurId] = [
                            'id'         => $recurId,
                            'partstat'   => $partStat,
                        ];
                    } else {
                        $result['attendees'][$attendee->getNormalizedValue()] = [
                            'href'      => $attendee->getNormalizedValue(),
                            'instances' => [
                                $recurId => [
                                    'id'       => $recurId,
                                    'partstat' => $partStat,
                                ],
                            ],
                            'name'      => isset($attendee['CN']) ? (string)$attendee['CN'] : null,
                            'forceSend' => $forceSend,
                        ];
                    }

                }
                $result['instances'][$recurId] = $component;

            }

            foreach ($this->significantChangeProperties as $prop) {
                if (isset($component->$prop)) {
                    $significantChangeHash .= $prop . ':';

                    if ($prop === 'EXDATE') {

                        $significantChangeHash .= implode(',', $result['exdate']) . ';';

                    } else {
                        $propertyValues = $component->select($prop);

                        foreach ($propertyValues as $val) {
                            $significantChangeHash .= $val->getValue() . ';';
                        }

                    }
                }
            }

        }
        $result['significantChangeHash'] = md5($significantChangeHash);

        //$result2 = $result;
        //$result2['instances'] = array_keys($result2['instances']);
        //print_r($result2);

        return $result;

    }


}
