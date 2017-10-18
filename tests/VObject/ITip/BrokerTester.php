<?php

namespace Sabre\VObject\ITip;

use Sabre\VObject\Reader;

/**
 * Utilities for testing the broker
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
abstract class BrokerTester extends \PHPUnit_Framework_TestCase {

    use \Sabre\VObject\PHPUnitAssertions;

    /**
     * This method calls processICalendarChange and asserts whether the
     * change yielded a list of iTip messsages.
     */
    function assertICalendarChange($before, $after, $expected = [], $userUri = 'mailto:one@example.org') {

        if (is_string($before)) {
            $before = Reader::read($before);
        }
        if (is_string($after)) {
            $after = Reader::read($after);
        }

        $broker = new Broker();
        $result = $broker->processICalendarChange($before, $after, $userUri);

        $this->assertEquals(count($expected), count($result));

        foreach ($expected as $index => $ex) {

            $message = $result[$index];

            foreach ($ex as $key => $val) {

                if ($key === 'message') {
                    $this->assertVObjectEqualsVObject(
                        $val,
                        $message->message->serialize()
                    );
                } else {
                    $this->assertEquals($val, $message->$key);
                }

            }

        }

    }

    function process($input, $existingObject = null, $expected = false) {

        $version = \Sabre\VObject\Version::VERSION;

        $vcal = Reader::read($input);

        foreach ($vcal->getComponents() as $mainComponent) {
            break;
        }

        $message = new Message();
        $message->message = $vcal;
        $message->method = isset($vcal->METHOD) ? $vcal->METHOD->getValue() : null;
        $message->component = $mainComponent->name;
        $message->uid = $mainComponent->UID->getValue();
        $message->sequence = isset($vcal->VEVENT[0]) ? (string)$vcal->VEVENT[0]->SEQUENCE : null;

        if ($message->method === 'REPLY') {

            $message->sender = $mainComponent->ATTENDEE->getValue();
            $message->senderName = isset($mainComponent->ATTENDEE['CN']) ? $mainComponent->ATTENDEE['CN']->getValue() : null;
            $message->recipient = $mainComponent->ORGANIZER->getValue();
            $message->recipientName = isset($mainComponent->ORGANIZER['CN']) ? $mainComponent->ORGANIZER['CN'] : null;

        }

        $broker = new Broker();

        if (is_string($existingObject)) {
            $existingObject = str_replace(
                '%foo%',
                "VERSION:2.0\nPRODID:-//Sabre//Sabre VObject $version//EN\nCALSCALE:GREGORIAN",
                $existingObject
            );
            $existingObject = Reader::read($existingObject);
        }

        $result = $broker->processMessage($message, $existingObject);

        if (is_null($expected)) {
            $this->assertTrue(!$result);
            return;
        }

        $this->assertVObjectEqualsVObject(
            $expected,
            $result
        );

    }
}
