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

        $eventInfo = $this->parseEventInfo($calendar);

        // Now we generate an iTip message for each attendee.
        $messages = array();

        foreach($eventInfo['attendees'] as $attendee) {

            // An organizer can also be an attendee. We should not generate any
            // messages for those.
            if ($attendee['href']===$eventInfo['organizer']) {
                continue;
            }

            $message = new Message();
            $message->uid = $eventInfo['uid'];
            $message->component = 'VEVENT';
            $message->method = 'REQUEST';
            $message->sender = $eventInfo['organizer'];
            $message->senderName = $eventInfo['organizerName'];
            $message->recipient = $attendee['href'];
            $message->recipientName = $attendee['name'];
            $message->sequence = $eventInfo['sequence'];

            // Creating the new iCalendar body.
            $icalMsg = new VCalendar();
            $icalMsg->METHOD = $message->method;

            foreach($attendee['instances'] as $instanceId) {

                $currentEvent = clone $eventInfo['instances'][$instanceId];
                if ($instanceId === 'master') {

                    // We need to find a list of events that the attendee
                    // is not a part of to add to the list of exceptions.
                    $exceptions = array();
                    foreach($eventInfo['instances'] as $instanceId=>$vevent) {
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

    /**
     * This method is used in cases where an event got updated, and we
     * potentially need to send emails to attendees to let them know of updates
     * in the events.
     *
     * We will detect which attendees got added, which got removed and create
     * specific messages for these situations.
     *
     * @param VCalendar|string $calendar
     * @param VCalendar|string $oldCalendar
     * @return array
     */
    public function updateEvent($calendar, $oldCalendar) {

        if (is_string($calendar)) {
            $calendar = Reader::read($calendar);
        }
        if (is_string($oldCalendar)) {
            $oldCalendar = Reader::read($oldCalendar);
        }

        $oldEventInfo = $this->parseEventInfo($oldCalendar);
        $newEventInfo = $this->parseEventInfo($calendar);

        // Shortcut for noop
        if (!$oldEventInfo['attendees'] && !$newEventInfo['attendees']) {
            return array();
        }

        // Merging attendee lists.
        $attendees = array();
        foreach($oldEventInfo['attendees'] as $attendee) {
            $attendees[$attendee['href']] = array(
                'href' => $attendee['href'],
                'oldInstances' => $attendee['instances'],
                'newInstances' => array(),
                'name' => $attendee['name'],
            );
        }
        foreach($newEventInfo['attendees'] as $attendee) {
            if (isset($attendees[$attendee['href']])) {
                $attendees[$attendee['href']]['name'] = $attendee['name'];
                $attendees[$attendee['href']]['newInstances'] = $attendee['instances'];
            } else {
                $attendees[$attendee['href']] = array(
                    'href' => $attendee['href'],
                    'oldInstances' => array(),
                    'newInstances' => $attendee['instances'],
                    'name' => $attendee['name'],
                );
            }
        }

        foreach($attendees as $attendee) {

            // An organizer can also be an attendee. We should not generate any
            // messages for those.
            if ($attendee['href']===$newEventInfo['organizer']) {
                continue;
            }

            $message = new Message();
            $message->uid = $newEventInfo['uid'];
            $message->component = 'VEVENT';
            $message->sequence = $newEventInfo['sequence'];
            $message->sender = $newEventInfo['organizer'];
            $message->senderName = $newEventInfo['organizerName'];
            $message->recipient = $attendee['href'];
            $message->recipientName = $attendee['name'];

            if (!$attendee['newInstances']) {

                // If there are no instances the attendee is a part of, it
                // means the attendee was removed and we need to send him a
                // CANCEL.
                $message->method = 'CANCEL';

                // Creating the new iCalendar body.
                $icalMsg = new VCalendar();
                $icalMsg->METHOD = $message->method;
                $event = $icalMsg->add('VEVENT', array(
                    'SEQUENCE' => $message->sequence,
                    'UID'      => $message->uid,
                ));
                $event->add('ATTENDEE', $attendee['href'], array(
                    'CN' => $attendee['name'],
                ));
                $org = $event->add('ORGANIZER', $newEventInfo['organizer']);
                if ($newEventInfo['organizerName']) $org['CN'] = $newEventInfo['organizerName'];

            } else {

                // The attendee gets the updated event body
                $message->method = 'REQUEST';

                // Creating the new iCalendar body.
                $icalMsg = new VCalendar();
                $icalMsg->METHOD = $message->method;

                foreach($attendee['newInstances'] as $instanceId) {

                    $currentEvent = clone $newEventInfo['instances'][$instanceId];
                    if ($instanceId === 'master') {

                        // We need to find a list of events that the attendee
                        // is not a part of to add to the list of exceptions.
                        $exceptions = array();
                        foreach($newEventInfo['instances'] as $instanceId=>$vevent) {
                            if (!in_array($instanceId, $attendee['newInstances'])) {
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

            }

            $message->message = $icalMsg;
            $messages[] = $message;

        }

        return $messages;


    }


    /**
     * Returns attendee information and information about instances of an
     * event.
     *
     * Returns an array with the following keys:
     *
     * 1. uid
     * 2. organizer
     * 3. organizerName
     * 4. attendees
     * 5. instances
     *
     * @param VCalendar $calendar
     * @return void
     */
    protected function parseEventInfo(VCalendar $calendar) {

        $uid = null;
        $organizer = null;
        $sequence = null;

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
            if (is_null($sequence) && isset($vevent->SEQUENCE)) {
                $sequence = $vevent->SEQUENCE->getValue();
            }

            $value = isset($vevent->{'RECURRENCE-ID'})?$vevent->{'RECURRENCE-ID'}->getValue():'master';
            if(isset($vevent->ATTENDEE)) foreach($vevent->ATTENDEE as $attendee) {

                if (isset($attendees[$attendee->getValue()])) {
                    $attendees[$attendee->getValue()]['instances'][] = $value;
                } else {
                    $attendees[$attendee->getValue()] = array(
                        'href' => $attendee->getValue(),
                        'instances' => array($value),
                        'name' => isset($attendee['CN'])?(string)$attendee['CN']:null,
                    );
                }

            }
            $instances[$value] = $vevent;

        }

        return array(
            'uid' => $uid,
            'organizer' => $organizer,
            'organizerName' => $organizerName,
            'instances' => $instances,
            'attendees' => $attendees,
            'sequence' => $sequence,
        );

    }

}
