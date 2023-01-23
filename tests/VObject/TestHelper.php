<?php

namespace Sabre\VObject;

use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VCard;
use Sabre\VObject\Property\FlatText;
use Sabre\VObject\Property\ICalendar\DateTime;
use Sabre\VObject\Property\ICalendar\Duration;
use Sabre\VObject\Property\ICalendar\Recur;

class TestHelper
{
    /**
     * @param VCalendar<mixed, mixed> $vcal
     *
     * @return DateTime<mixed, mixed>
     *
     * @throws InvalidDataException
     * @throws \Exception
     */
    public static function createDateCreated(VCalendar $vcal, string $dateTime, ?string $timezone = null): DateTime
    {
        return self::createDt($vcal, 'CREATED', $dateTime, $timezone);
    }

    /**
     * @param VCalendar<mixed, mixed> $vcal
     *
     * @return DateTime<mixed, mixed>
     *
     * @throws InvalidDataException
     * @throws \Exception
     */
    public static function createDateCompleted(VCalendar $vcal, string $dateTime, ?string $timezone = null): DateTime
    {
        return self::createDt($vcal, 'COMPLETED', $dateTime, $timezone);
    }

    /**
     * @param VCalendar<mixed, mixed> $vcal
     *
     * @return DateTime<mixed, mixed>
     *
     * @throws InvalidDataException
     * @throws \Exception
     */
    public static function createDateDue(VCalendar $vcal, string $dateTime, ?string $timezone = null): DateTime
    {
        return self::createDt($vcal, 'DUE', $dateTime, $timezone);
    }

    /**
     * @param VCalendar<mixed, mixed> $vcal
     *
     * @return DateTime<mixed, mixed>
     *
     * @throws InvalidDataException
     * @throws \Exception
     */
    public static function createDtStart(VCalendar $vcal, string $dateTime, ?string $timezone = null): DateTime
    {
        return self::createDt($vcal, 'DTSTART', $dateTime, $timezone);
    }

    /**
     * @param VCalendar<mixed, mixed> $vcal
     *
     * @return DateTime<mixed, mixed>
     *
     * @throws InvalidDataException
     * @throws \Exception
     */
    public static function createDtEnd(VCalendar $vcal, string $dateTime, ?string $timezone = null): DateTime
    {
        return self::createDt($vcal, 'DTEND', $dateTime, $timezone);
    }

    /**
     * @param VCalendar<mixed, mixed> $vcal
     *
     * @return DateTime<mixed, mixed>
     *
     * @throws InvalidDataException
     * @throws \Exception
     */
    public static function createDt(VCalendar $vcal, string $propertyName, string $dateTime, ?string $timezone = null): DateTime
    {
        /** @var DateTime<mixed, mixed> $property */
        $property = $vcal->createProperty($propertyName);
        if (null !== $timezone) {
            $timezone = new \DateTimeZone($timezone);
        }
        $property->setDateTime(new \DateTimeImmutable($dateTime, $timezone));

        return $property;
    }

    /**
     * @param VCalendar<mixed, mixed>|VCard<mixed, mixed> $vcalOrCard
     *
     * @return FlatText<mixed, mixed>
     *
     * @throws InvalidDataException
     */
    public static function createUid($vcalOrCard, string $uidString): FlatText
    {
        /** @var FlatText<mixed, mixed> $property */
        $property = $vcalOrCard->createProperty('UID');
        $property->setValue($uidString);

        return $property;
    }

    /**
     * @param VCalendar<mixed, mixed> $vcal
     *
     * @return Duration<mixed, mixed>
     *
     * @throws InvalidDataException
     */
    public static function createDuration(VCalendar $vcal, string $duration): Duration
    {
        /** @var Duration<mixed, mixed> $property */
        $property = $vcal->createProperty('DURATION');
        $property->setValue($duration);

        return $property;
    }

    /**
     * @param VCalendar<mixed, mixed> $vcal
     *
     * @return Duration<mixed, mixed>
     *
     * @throws InvalidDataException
     */
    public static function createTrigger(VCalendar $vcal, string $duration): Duration
    {
        /** @var Duration<mixed, mixed> $property */
        $property = $vcal->createProperty('TRIGGER');
        $property->setValue($duration);

        return $property;
    }

    /**
     * @param VCalendar<mixed, mixed>        $vcal
     * @param array<int, \DateTimeImmutable> $dateTimes
     *
     * @return DateTime<mixed, mixed>
     *
     * @throws InvalidDataException
     */
    public static function createRDate(VCalendar $vcal, array $dateTimes): DateTime
    {
        /** @var DateTime<mixed, mixed> $property */
        $property = $vcal->createProperty('RDATE');
        $property->setValue($dateTimes);

        return $property;
    }

    /**
     * @param VCalendar<mixed, mixed>     $vcal
     * @param array<string, mixed>|string $rule
     *
     * @return Recur<mixed, mixed>
     *
     * @throws InvalidDataException
     */
    public static function createRRule(VCalendar $vcal, $rule): Recur
    {
        /** @var Recur<mixed, mixed> $property */
        $property = $vcal->createProperty('RRULE');
        $property->setValue($rule);

        return $property;
    }
}
