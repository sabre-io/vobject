<?php

namespace Sabre\VObject\ITip;

use Sabre\VObject\Component\VCalendar;

/**
 * This class is the broker that handle VTODO.
 *
 * While you can use this class directly, it probably makes more sense to use
 * the main Broker class instead.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (https://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class TodoBroker extends AbstractBroker {

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

        if ($before) {
            $beforeInfo = $this->extractSchedulingInfo($before);
        } else {
            $beforeInfo = null;
        }
        if ($after) {
            $afterInfo = $this->extractSchedulingInfo($after);
        } else {
            $afterInfo = null;
        }

        $info = $beforeInfo ?: $afterInfo;


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

        return null;

    }



}
