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
     * This setting determines wether the rules for the SCHEDULE-AGENT
     * parameter should be followed.
     *
     * This is a parameter defined on ATTENDEE properties, introduced by RFC
     * 6638. This parameter allows a caldav client to tell the server 'don't do
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
     * This method is used to process an incoming itip message.
     *
     * Examples:
     *
     * 1. A user is an attendee to an event. The organizer sends an updated
     * meeting using a new ITip message with METHOD:REQUEST. This function
     * will process the message and update the attendee's event accordingly.
     *
     * 2. The organizer cancelled the event using METHOD:CANCEL. We will update
     * the users event to state STATUS:CANCELLED.
     *
     * 3. An attendee sent a reply to an invite using METHOD:REPLY. We can
     * update the organizers event to update the ATTENDEE with its correct
     * PARTSTAT.
     *
     * The $existingObject is updated in-place. If no existing object exists
     * (because it's a new invite for example) a new object will be created.
     *
     * If an existing object does not exist, and the method was CANCEL or
     * REPLY, the message effectively gets ignored, and no 'existingObject'
     * will be created.
     *
     * The updated $existingObject is also returned from this function.
     *
     * @param Message $itipMessage
     * @param VCalendar $existingObject
     * @return VCalendar
     */
    public function processMessage(Message $itipMessage, VCalendar $existingObject = null) {

        switch($itipMessage->method) {

            /**
             * This is message from an organizer, and is either a new event
             * invite, or an update to an existing one.
             */
            case 'REQUEST' :
                if (!$existingObject) {
                    // This is a new invite, and we're just going to copy over
                    // all the components from the invite.
                    $existingObject = new VCalendar();
                    foreach($itipMessage->message->getComponents() as $component) {
                        $existingObject->add(clone $component);
                    }
                } else {
                    // We need to update an existing object with all the new
                    // information. We can just remove all existing components
                    // and create new ones.
                    foreach($existingObject->getComponents() as $component) {
                        $existingObject->remove($component);
                    }
                    foreach($itipMessage->message->getComponents() as $component) {
                        $existingObject->add(clone $component);
                    }
                }
                break;

            /**
             * This is a message from an organizer, and means that either an
             * attendee got removed from an event, or an event got cancelled
             * altogether.
             */
            case 'CANCEL' :
                if (!$existingObject) {
                    // The event didn't exist in the first place, so we're just
                    // ignoring this message.
                } else {
                    foreach($existingObject->VEVENT as $vevent) {
                        $vevent->STATUS = 'CANCELLED';
                        $vevent->SEQUENCE = $itipMessage->sequence;
                    }
                }
                break;

        }

        return $existingObject;

    }

    /**
     * This function parses a VCALENDAR object and figure out if any messages
     * need to be sent.
     *
     * A VCALENDAR object will be created from the perspective of either an
     * attendee, or an organizer. You must pass a string identifying the
     * current user, so we can figure out who in the list of attendees or the
     * organizer we are sending this message on behalf of.
     *
     * It's possible to specify the current user as an array, in case the user
     * has more than one identifying href (such as multiple emails).
     *
     * It $oldCalendar is specified, it is assumed that the operation is
     * updating an existing event, which means that we need to look at the
     * differences between events, and potentially send old attendees
     * cancellations, and current attendees updates.
     *
     * @param VCalendar|string $calendar
     * @param string|array $userHref
     * @param VCalendar|string $oldCalendar
     * @return array
     */
    public function parseEvent($calendar, $userHref, $oldCalendar = null) {

        if (is_string($calendar)) {
            $calendar = Reader::read($calendar);
        }
        if (!isset($calendar->VEVENT)) {
            // We only support events at the moment
            return array();
        }

        $eventInfo = $this->parseEventInfo($calendar);

        if ($oldCalendar) {
            if (is_string($oldCalendar)) {
                $oldCalendar = Reader::read($oldCalendar);
            }
            $oldEventInfo = $this->parseEventInfo($oldCalendar);
        } else {
            $oldEventInfo = array(
                'attendees' => [],
            );
        }

        // Events that don't have an organizer or attendees don't generate
        // messages.
        if (!$eventInfo['attendees'] && $oldEventInfo['attendees']) {
            return array();
        }

        $userHref = (array)$userHref;
        $organizer = (string)$calendar->VEVENT->ORGANIZER;
        if (in_array($organizer, $userHref)) {
            return $this->parseEventForOrganizer($calendar, $eventInfo, $oldEventInfo);
        }
        return array();

    }

    /**
     * This method is used in cases where an event got updated, and we
     * potentially need to send emails to attendees to let them know of updates
     * in the events.
     *
     * We will detect which attendees got added, which got removed and create
     * specific messages for these situations.
     *
     * @param VCalendar $calendar
     * @param array $eventInfo
     * @param VCalendar $oldCalendar
     * @param array $oldEventInfo
     * @return array
     */
    protected function parseEventForOrganizer(VCalendar $calendar, array $eventInfo, array $oldEventInfo) {

        // Shortcut for noop
        if (!$oldEventInfo['attendees'] && !$eventInfo['attendees']) {
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
        foreach($eventInfo['attendees'] as $attendee) {
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
            if ($attendee['href']===$eventInfo['organizer']) {
                continue;
            }

            $message = new Message();
            $message->uid = $eventInfo['uid'];
            $message->component = 'VEVENT';
            $message->sequence = $eventInfo['sequence'];
            $message->sender = $eventInfo['organizer'];
            $message->senderName = $eventInfo['organizerName'];
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
                $org = $event->add('ORGANIZER', $eventInfo['organizer']);
                if ($eventInfo['organizerName']) $org['CN'] = $eventInfo['organizerName'];

            } else {

                // The attendee gets the updated event body
                $message->method = 'REQUEST';

                // Creating the new iCalendar body.
                $icalMsg = new VCalendar();
                $icalMsg->METHOD = $message->method;

                foreach($attendee['newInstances'] as $instanceId) {

                    $currentEvent = clone $eventInfo['instances'][$instanceId];
                    if ($instanceId === 'master') {

                        // We need to find a list of events that the attendee
                        // is not a part of to add to the list of exceptions.
                        $exceptions = array();
                        foreach($eventInfo['instances'] as $instanceId=>$vevent) {
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
        $organizerName = null;
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
            if (isset($vevent->ORGANIZER)) {
                if (is_null($organizer)) {
                    $organizer = $vevent->ORGANIZER->getValue();
                    $organizerName = isset($vevent->ORGANIZER['CN'])?$vevent->ORGANIZER['CN']:null;
                } else {
                    if ($organizer !== $vevent->ORGANIZER->getValue()) {
                        throw new ITipException('Every instance of the event must have the same organizer.');
                    }
                }
            }
            if (is_null($sequence) && isset($vevent->SEQUENCE)) {
                $sequence = $vevent->SEQUENCE->getValue();
            }

            $value = isset($vevent->{'RECURRENCE-ID'})?$vevent->{'RECURRENCE-ID'}->getValue():'master';
            if(isset($vevent->ATTENDEE)) foreach($vevent->ATTENDEE as $attendee) {

                if ($this->scheduleAgentServerRules &&
                    isset($attendee['SCHEDULE-AGENT']) &&
                    strtoupper($attendee['SCHEDULE-AGENT']->getValue()) === 'CLIENT'
                ) {
                    continue;
                }
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
