<?php

namespace Sabre\VObject\ITip;

/**
 * This class represents an iTip message
 *
 * A message holds all the information relevant to the message, including the
 * object itself.
 *
 * It should for the most part be treated as immutable.
 *
 * @copyright Copyright (C) 2007-2014 fruux GmbH. All rights reserved.
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class Message {

    /**
     * The object's UID
     *
     * @var string
     */
    public $uid;

    /**
     * The component type, such as VEVENT
     *
     * @var string
     */
    public $component;

    /**
     * Contains the ITip method, which is something like REQUEST, REPLY or
     * CANCEL.
     *
     * @var string
     */
    public $method;

    /**
     * The current sequence number for the event.
     *
     * @var int
     */
    public $sequence;

    /**
     * The senders' email address.
     *
     * Note that this does not imply that this has to be used in a From: field
     * if the message is sent by email. It may also be populated in Reply-To:
     * or not at all.
     *
     * @var string
     */
    public $sender;

    /**
     * The name of the sender. This is often populated from a CN parameter from
     * either the ORGANIZER or ATTENDEE, depending on the message.
     *
     * @var string|null
     */
    public $senderName;

    /**
     * The recipient's email address.
     *
     * @var string
     */
    public $recipient;

    /**
     * The name of the recipient. This is usually populated with the CN
     * parameter from the ATTENDEE or ORGANIZER property, if it's available.
     *
     * @var string|null
     */
    public $recipientName;

    /**
     * After the message has been delivered, this should contain a string such
     * as : 1.1;Sent or 1.2;Delivered.
     *
     * In case of a failure, this will hold the error status code.
     *
     * See:
     * http://tools.ietf.org/html/rfc6638#section-7.3
     *
     * @var string
     */
    public $scheduleStatus;

    /**
     * The iCalendar / iTip body.
     *
     * @var \Sabre\VObject\Component\VCalendar
     */
    public $message;

    /**
     * Returns the schedule status as an array:
     * [
     *     0 => '1.2',
     *     1 => 'Delivered',
     * ]
     *
     * @return mixed bool|array
     */
    public function getScheduleStatus() {

        if(!$this->scheduleStatus) {

            return false;

        } else {
            
            $scheduleStatus = explode(';', $this->scheduleStatus);

            if(!isset($scheduleStatus[1])) {
                $scheduleStatus[1]='';
            }

            return $scheduleStatus;

        }

    }

}
