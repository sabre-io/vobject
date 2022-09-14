<?php

namespace Sabre\VObject\ITip;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VEvent;
use Sabre\VObject\InvalidDataException;
use Sabre\VObject\ParseException;
use Sabre\VObject\PHPUnitAssertions;
use Sabre\VObject\Property;
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

    /**
     * @param VCalendar<int, mixed>|string|null           $oldMessage
     * @param VCalendar<int, mixed>|string|null           $newMessage
     * @param array<int, array<string, bool|string|null>> $expected
     *
     * @throws ITipException
     * @throws InvalidDataException
     * @throws ParseException
     * @throws SameOrganizerForAllComponentsException
     */
    public function parse($oldMessage, $newMessage, array $expected = [], string $currentUser = 'mailto:one@example.org'): void
    {
        $broker = new Broker();
        $result = $broker->parseEvent($newMessage, $currentUser, $oldMessage);

        self::assertSameSize($expected, $result);

        foreach ($expected as $index => $ex) {
            $message = $result[$index];

            foreach ($ex as $key => $val) {
                if ('message' === $key) {
                    self::assertVObjectEqualsVObject(
                        $val,
                        $message->message->serialize()
                    );
                } else {
                    self::assertEquals($val, $message->$key);
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
    public function process(string $input, ?string $old = null, ?string $expected = null): void
    {
        $version = Version::VERSION;

        /** @var VCalendar<int, mixed> $vcal */
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
        $message->sequence = isset($vcal->VEVENT[0]) ? $vcal->VEVENT[0]->SEQUENCE->getValue() : null;

        if ('REPLY' === $message->method) {
            /**
             * @var Property<int, mixed> $attendee
             */
            $attendee = $mainComponent->ATTENDEE;
            $message->sender = $attendee->getValue();
            /* @phpstan-ignore-next-line Offset 'CN' in isset() does not exist. Call to an undefined method getValue(). */
            $message->senderName = isset($attendee['CN']) ? $attendee['CN']->getValue() : null;
            $organizer = $mainComponent->ORGANIZER;
            $message->recipient = $organizer->getValue();
            /* @phpstan-ignore-next-line Offset 'CN' in isset() does not exist. */
            $message->recipientName = isset($organizer['CN']) ? $organizer['CN'] : null;
        }

        $broker = new Broker();

        if (is_string($old)) {
            $existingObject = str_replace(
                '%foo%',
                "VERSION:2.0\nPRODID:-//Sabre//Sabre VObject $version//EN\nCALSCALE:GREGORIAN",
                $old
            );
            /** @var VCalendar<int, mixed> $existingObject */
            $existingObject = Reader::read($old);
        } else {
            $existingObject = $old;
        }

        $result = $broker->processMessage($message, $existingObject);

        if (is_null($expected)) {
            self::assertTrue(!$result);

            return;
        }

        self::assertVObjectEqualsVObject(
            $expected,
            $result
        );
    }
}
