<?php

namespace Sabre\VObject\ITip;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VEvent;
use Sabre\VObject\InvalidDataException;
use Sabre\VObject\ParseException;
use Sabre\VObject\PHPUnitAssertions;
use Sabre\VObject\Reader;
use Sabre\VObject\Recur\MaxInstancesExceededException;
use Sabre\VObject\Recur\NoInstancesException;
use Sabre\VObject\Version;

/**
 * Utilities for testing the broker.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
abstract class BrokerTester extends TestCase
{
    use PHPUnitAssertions;

    public function parse($oldMessage, $newMessage, array $expected = [], string $currentUser = 'mailto:one@example.org'): void
    {
        $broker = new Broker();
        $result = $broker->parseEvent($newMessage, $currentUser, $oldMessage);

        $this->assertSameSize($expected, $result);

        foreach ($expected as $index => $ex) {
            $message = $result[$index];

            foreach ($ex as $key => $val) {
                if ('message' === $key) {
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

    /**
     * @throws ParseException
     * @throws MaxInstancesExceededException
     * @throws NoInstancesException
     * @throws InvalidDataException
     */
    public function process($input, $existingObject = null, $expected = false): void
    {
        $version = Version::VERSION;

        $vcal = Reader::read($input);

        $mainComponent = new VEvent($vcal, 'VEVENT');
        foreach ($vcal->getComponents() as $mainComponent) {
            if ('VEVENT' === $mainComponent->name) {
                break;
            }
        }

        $message = new Message();
        $message->message = $vcal;
        $message->method = isset($vcal->METHOD) ? $vcal->METHOD->getValue() : null;
        $message->component = $mainComponent->name;
        $message->uid = $mainComponent->UID->getValue();
        $message->sequence = isset($vcal->VEVENT[0]) ? (string) $vcal->VEVENT[0]->SEQUENCE : null;

        if ('REPLY' === $message->method) {
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
