<?php

namespace Sabre\VObject\ITip;

use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Reader;

/**
 * The ITip\Broker class is a utility class that helps with processing
 * so-called iTip messages.
 *
 * iTip is defined in rfc5546, stands for iCalendar Transport-Independent
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
 * There are basically two major modes of operation:
 *
 * 1. processing a change in an object. The product of this is a series of
 *    iTip messages.
 * 2. process an iTip message. The prodcut of this is a new calendar object,
 *    or an update to a calendar object.
 *
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class Broker extends AbstractBroker {

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
    function processICalendarChange(VCalendar $before = null, VCalendar $after = null, $userUri) {

        if ($before === null && $after === null) {
            throw new \InvalidArgumentException('$before and $after must both not be null');
        }

        $componentType = $before ? $before->getComponentType() : $after->getComponentType();

        $broker = $this->getBroker($componentType);
        return $broker->processICalendarChange($before, $after, $userUri);

    }

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
    function applyITipMessage(Message $message, VCalendar $existingObject = null) {

        $broker = $this->getBroker($message->component);
        return $broker->applyITipMessage($message, $existingObject);


    }
    /**
     * This method an alias of applyITipMessage, and is deprecated.
     *
     * @param Message $itipMessage
     * @param VCalendar $existingObject
     * @deprecated
     * @return VCalendar|null
     */
    function processMessage(Message $itipMessage, VCalendar $existingObject = null) {

        return $this->applyITipMessage($itipMessage, $existingObject);

    }

    /**
     * This method is an alias of processICalendarChange, and is deprecated.
     *
     * @param VCalendar|string $calendar
     * @param string|array $userHref
     * @param VCalendar|string $oldCalendar
     * @return Message[]
     */
    function parseEvent($calendar = null, $userHref, $oldCalendar = null) {

        if (is_string($calendar)) {
            $calendar = Reader::read($calendar);
        }
        if (is_string($oldCalendar)) {
            $oldCalendar = Reader::read($oldCalendar);
        }

        return $this->processICalendarChange($oldCalendar, $calendar, $userHref);

    }

    /**
     * Returns a Broker for the specified component type.
     *
     * @param string $componentType String such as VEVENT
     * @return AbstractBroker
     */
    protected function getBroker($componentType) {

        switch ($componentType) {

            case 'VEVENT' :
                $broker = new EventBroker();
                break;
            case 'VTODO' :
                $broker = new TodoBroker();
                break;
            default :
                $broker = new NullBroker();
                break;

        }
        $broker->scheduleAgentServerRules = $this->scheduleAgentServerRules;

        return $broker;

    }


}
