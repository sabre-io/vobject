<?php

namespace Sabre\VObject;

use DateTimeInterface;
use DateTimezone;
use Sabre\VObject\Component\VCalendar;

/**
 * This class generates birthday calendars.
 *
 * @copyright Copyright (C) 2011-2015 fruux GmbH (https://fruux.com/).
 * @author Dominik Tobschall (http://tobschall.de/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class BirthdayCalendarGenerator {

    /**
     * Input objects.
     *
     * @var array
     */
    protected $objects = [];

    /**
     * Reference timezone.
     *
     * When generating events, and we come across so-called
     * floating times (times without a timezone), we use the reference timezone
     * instead.
     *
     * This is also used for all-day events.
     *
     * This defaults to UTC.
     *
     * @var DateTimeZone
     */
    protected $timeZone;

    /**
     * Default year.
     * Used for dates without a year.
     *
     * @var string
     */
    protected $defaultYear = 1900;

    /**
     * Creates the generator.
     *
     * Check the setTimeRange and setObjects methods for details about the
     * arguments.
     *
     * @param mixed $objects
     * @param DateTimeZone $timeZone
     */
    function __construct($objects = null, DateTimeZone $timeZone = null) {

        if ($objects) {
            $this->setObjects($objects);
        }
        if (is_null($timeZone)) {
            $timeZone = new DateTimeZone('UTC');
        }
        $this->setTimeZone($timeZone);

    }

    /**
     * Sets the input objects.
     *
     * You must either supply a vCard as a string or as a Component/VCard object.
     * It's also possible to supply an array of strings or objects.
     *
     * @param mixed $objects
     *
     * @return void
     */
    function setObjects($objects) {

        if (!is_array($objects)) {
            $objects = [$objects];
        }

        $this->objects = [];
        foreach ($objects as $object) {

            if (is_string($object)) {

                $vObj = Reader::read($object);
                if (!$vObj instanceof Component\VCard) {
                    throw new \InvalidArgumentException('String could not be parsed as \\Sabre\\VObject\\Component\\VCard by setObjects');
                }

                $this->objects[] = $vObj;

            } elseif ($object instanceof Component\VCard) {

                $this->objects[] = $object;

            } else {

                throw new \InvalidArgumentException('You can only pass strings or \\Sabre\\VObject\\Component\\VCard arguments to setObjects');

            }

        }

    }

    /**
     * Sets the reference timezone for floating times.
     *
     * @param DateTimeZone $timeZone
     *
     * @return void
     */
    function setTimeZone(DateTimeZone $timeZone) {

        $this->timeZone = $timeZone;

    }

    /**
     * Parses the input data and returns a VCALENDAR.
     *
     * @return Component/VCalendar
     */
    function getResult() {

        $calendar = new VCalendar();

        foreach ($this->objects as $object) {

            // Skip if there is no BDAY property.
            if (!$object->select('BDAY')) {
                continue;
            }

            // We're always converting to vCard 4.0 so we can rely on the
            // VCardConverter handling the X-APPLE-OMIT-YEAR property for us.
            $object = $object->convert(Document::VCARD40);

            // Skip if the BDAY property is not of the right type.
            if (!$object->BDAY instanceof Property\VCard\DateAndOrTime) {
                continue;
            }

            // Skip if we can't parse the BDAY value.
            try {
                $dateParts = DateTimeParser::parseVCardDateTime($object->BDAY->getValue());
            } catch (\InvalidArgumentException $e) {
                continue;
            }

            // Set a year if it's not set.
            if (!$dateParts['year']) {
                $object->BDAY=$this->defaultYear . '-' . $dateParts['month'] . '-' . $dateParts['date'];

                // @TODO:
                // It may make sense to add an info to our event that the year was unknown.
            }

            // Generate the event.
            $calendar->add('VEVENT', [
                'SUMMARY'      => $object->FN->getValue() .'\'s Birthday',
                'DTSTART'      => new \DateTime($object->BDAY->getValue(), $this->timeZone),
                'RRULE'        => 'FREQ=YEARLY',
                'TRANSP'       => 'TRANSPARENT',
                'X-SABRE-BDAY' => [$object->UID->getValue(), 'BDAY', $object->FN->getValue()]
            ]);

        }

        return $calendar;

    }



}
