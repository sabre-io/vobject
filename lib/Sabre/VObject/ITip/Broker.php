<?php

namespace Sabre\VObject\ITip;

use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Reader;

/**
 * The ITip class is a utility class that helps with processing so-called iTip
 * messages.
 *
 * ITip is defined in rfc5546, stands for iCalendar Transport-Independent
 * Interoperability Protocol, and describes the underlying mechanism for
 * using iCalendar for scheduling for for example through email (also known as
 * IMip) and CalDAV Scheduling.
 *
 * This class helps by:
 *
 * 1. Creating individual invites based on an iCalendar event for each
 *    attendee.
 * 2. Generating invite updates based on an iCalendar update. This may result
 *    in new invites, updates and cancellations for attendees, if that list
 *    changed.
 * 3. On the receiving end, it can create a local iCalendar event based on
 *    a received invite.
 * 4. It can also process an invite update on a local event, ensuring that any
 *    overridden properties from attendees are retained.
 * 5. It can create a accepted or declined iTip reply based on an invite.
 * 6. It can process a reply from an invite and update an events attendee
 *     status based on a reply.
 *
 * @copyright Copyright (C) 2007-2014 fruux GmbH. All rights reserved.
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class Broker {

    /**
     * This function parses a VCALENDAR object, and if the object had an
     * organizer and attendees, it will generate iTip messages for every
     * attendee.
     *
     * If the passed object did not have any attendees, no messages will be
     * created.
     *
     * @param VCalendar|string $calendar
     * @return array
     */
    public function createEvent($calendar) {

        if (is_string($calendar)) {
            $calendar = Reader::read($calendar);
        }

        if (!isset($calendar->VEVENT)) {
            // We only support events at the moment
            return array();
        }

        // Events that don't have an organizer or attendees don't generate
        // messages.
        if (!isset($calendar->VEVENT->ORGANIZER) || !isset($calendar->VEVENT->ATTENDEE)) {
            return array();
        }

        $uid = null;
        $organizer = null;

        // Now we need to collect a list of attendees, and which instances they
        // are a part of.
        $attendees = array();

        $instances = array();

        foreach($calendar->VEVENT as $vevent) {
            if (is_null($uid)) {
                $uid = $vevent->UID->getValue();
            } else {
                if ($uid !== $vevent->UID->getValue()) {
                    throw new ITipException('If a calendar contained more than one event, they must have the same UID.');
                }
            }
            if (is_null($organizer)) {
                $organizer = $vevent->ORGANIZER->getValue();
                $organizerName = isset($vevent->ORGANIZER['CN'])?$vevent->ORGANIZER['CN']:null;
            } else {
                if ($organizer !== $vevent->ORGANIZER->getValue()) {
                    throw new ITipException('Every instance of the event must have the same organizer.');
                }
            }

            $value = isset($vevent->{'RECURRENCE-ID'})?$vevent->{'RECURRENCE-ID'}->getValue():'master';
            foreach($vevent->ATTENDEE as $attendee) {

                if (isset($attendees[$attendee->getValue()])) {
                    $attendees[$attendee->getValue()]['instances'][] = $value;
                } else {
                    $attendees[$attendee->getValue()] = array(
                        'href' => $attendee->getValue(),
                        'instances' => array($value),
                        'name' => isset($attendee['CN'])?$attendee['CN']:null,
                    );
                }

            }
            $instances[$value] = $vevent;

        }

        // Now we generate an iTip message for each attendee.
        $messages = array();

        foreach($attendees as $attendee) {

            // An organizer can also be an attendee. We should not generate any
            // messages for those.
            if ($attendee['href']===$organizer) {
                continue;
            }

            $message = new Message();
            $message->uid = $uid;
            $message->component = 'VEVENT';
            $message->method = 'REQUEST';
            $message->sender = $organizer;
            $message->senderName = $organizerName;
            $message->recipient = $attendee['href'];
            $message->recipientName = $attendee['name'];

            // Creating the new iCalendar body.
            $icalMsg = new VCalendar();
            $icalMsg->METHOD = $message->method;

            foreach($attendee['instances'] as $instanceId) {

                $currentEvent = clone $instances[$instanceId];
                if ($instanceId === 'master') {

                    // We need to find a list of events that the attendee
                    // is not a part of to add to the list of exceptions.
                    $exceptions = array();
                    foreach($instances as $instanceId=>$vevent) {
                        if (!in_array($instanceId, $attendee['instances'])) {
                            $exceptions[] = $instanceId;
                        }
                    }

                    // If there were exceptions, we need to add it to an
                    // existing EXDATE property, if it exists.
                    if ($exceptions) {
                        if (isset($currentEvent->EXDATE)) {
                            $currentEvent->EXDATE->setParts(array_merge(
                                $currentEvent->EXDATE->getParts(),
                                $exceptions
                            ));
                        } else {
                            $currentEvent->EXDATE = $exceptions;
                        }
                    }

                }

                $icalMsg->add($currentEvent);

            }

            $message->message = $icalMsg;
            $messages[] = $message;

        }

        return $messages;

    }

}
