<?php

namespace Sabre\VObject\ITip;

use PHPUnit\Framework\Attributes\DataProvider;

class BrokerSignificantChangesTest extends BrokerTester
{
    /**
     * Check significant changes detection (no change).
     */
    public function testSignificantChangesNoChange(): void
    {
        $old = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
CALSCALE:GREGORIAN
PRODID:-//Ximian//NONSGML Evolution Calendar//EN
BEGIN:VEVENT
UID:20140813T153116Z-12176-1000-1065-6@johnny-lubuntu
DTSTAMP:20140813T142829Z
DTSTART;TZID=America/Toronto:20140815T110000
SEQUENCE:2
SUMMARY:Evo makes a Meeting
LOCATION:fruux HQ
CLASS:PUBLIC
RRULE:FREQ=WEEKLY
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;RSVP=
 TRUE;LANGUAGE=en:MAILTO:dominik@fruux.com
CREATED:20140813T153211Z
LAST-MODIFIED:20140813T155353Z
END:VEVENT
END:VCALENDAR
ICS;

        $new = $old;
        $expected = [['significantChange' => false]];

        $this->parse($old, $new, $expected, 'mailto:martin@fruux.com');
    }

    /**
     * Check significant changes detection (no change).
     */
    public function testSignificantChangesRRuleNoChange(): void
    {
        $old = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
CALSCALE:GREGORIAN
PRODID:-//Ximian//NONSGML Evolution Calendar//EN
BEGIN:VEVENT
UID:20140813T153116Z-12176-1000-1065-6@johnny-lubuntu
DTSTAMP:20140813T142829Z
DTSTART;TZID=America/Toronto:20140815T110000
SEQUENCE:2
SUMMARY:Evo makes a Meeting
LOCATION:fruux HQ
CLASS:PUBLIC
RRULE:FREQ=WEEKLY
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;RSVP=
 TRUE;LANGUAGE=en:MAILTO:dominik@fruux.com
CREATED:20140813T153211Z
LAST-MODIFIED:20140813T155353Z
END:VEVENT
END:VCALENDAR
ICS;

        $new = str_replace('FREQ=WEEKLY', 'FREQ=WEEKLY;INTERVAL=1', $old);
        $expected = [['significantChange' => false]];

        $this->parse($old, $new, $expected, 'mailto:martin@fruux.com');
    }

    /**
     * Check significant changes detection (no change).
     */
    public function testSignificantChangesRRuleOrderNoChange(): void
    {
        $old = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
CALSCALE:GREGORIAN
PRODID:-//Ximian//NONSGML Evolution Calendar//EN
BEGIN:VEVENT
UID:20140813T153116Z-12176-1000-1065-6@johnny-lubuntu
DTSTAMP:20140813T142829Z
DTSTART;TZID=America/Toronto:20140815T110000
SEQUENCE:2
SUMMARY:Evo makes a Meeting
LOCATION:fruux HQ
CLASS:PUBLIC
RRULE:FREQ=WEEKLY;BYDAY=MO
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;RSVP=
 TRUE;LANGUAGE=en:MAILTO:dominik@fruux.com
CREATED:20140813T153211Z
LAST-MODIFIED:20140813T155353Z
END:VEVENT
END:VCALENDAR
ICS;

        $new = str_replace('FREQ=WEEKLY;BYDAY=MO', 'BYDAY=MO;FREQ=WEEKLY', $old);
        $expected = [['significantChange' => false]];

        $this->parse($old, $new, $expected, 'mailto:martin@fruux.com');
    }

    /**
     * Check significant changes detection (no change).
     * Reordering of the attendees should not be a significant change (#540)
     * https://github.com/sabre-io/vobject/issues/540.
     */
    public function testSignificantChangesAttendeesOrderNoChange(): void
    {
        $old = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
CALSCALE:GREGORIAN
PRODID:-//Ximian//NONSGML Evolution Calendar//EN
BEGIN:VEVENT
UID:20140813T153116Z-12176-1000-1065-6@johnny-lubuntu
DTSTAMP:20140813T142829Z
DTSTART;TZID=America/Toronto:20140815T110000
SEQUENCE:2
SUMMARY:Evo makes a Meeting
LOCATION:fruux HQ
CLASS:PUBLIC
RRULE:FREQ=WEEKLY;BYDAY=MO
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;RSVP=
 TRUE;LANGUAGE=en:MAILTO:dominik@fruux.com
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;RSVP=
 TRUE;LANGUAGE=de:MAILTO:holger@fruux.com
CREATED:20140813T153211Z
LAST-MODIFIED:20140813T155353Z
END:VEVENT
END:VCALENDAR
ICS;

        $new = str_replace('holger@fruux.com', 'dominik1@fruux.com', $old);
        $new = str_replace('dominik@fruux.com', 'holger@fruux.com', $new);
        $new = str_replace('dominik1@fruux.com', 'dominik@fruux.com', $new);
        $expected = [];
        $expected[] = ['significantChange' => false];
        $expected[] = ['significantChange' => false];

        $this->parse($old, $new, $expected, 'mailto:martin@fruux.com');
    }

    /**
     * Check significant changes detection (no change).
     * Reordering of vevent in a recurring event with exceptions should
     * not be a significant change
     * https://github.com/sabre-io/vobject/issues/542.
     */
    public function testSignificantChangesVeventOrderNoChange(): void
    {
        $vevent1 = <<<ICS
BEGIN:VEVENT
UID:20140813T153116Z-12176-1000-1065-6@johnny-lubuntu
DTSTAMP:20140813T142829Z
DTSTART;TZID=America/Toronto:20140815T110000
SEQUENCE:2
SUMMARY:Evo makes a Meeting
LOCATION:fruux HQ
CLASS:PUBLIC
RRULE:FREQ=WEEKLY;BYDAY=MO
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;RSVP=
 TRUE;LANGUAGE=en:MAILTO:dominik@fruux.com
CREATED:20140813T153211Z
LAST-MODIFIED:20140813T155353Z
END:VEVENT
ICS;
        // This event is slightly different. DTSTAMP is in 2021
        $vevent2 = <<<ICS
BEGIN:VEVENT
UID:20140813T153116Z-12176-1000-1065-6@johnny-lubuntu
DTSTAMP:20210813T142829Z
DTSTART;TZID=America/Toronto:20140815T110000
SEQUENCE:2
SUMMARY:Evo makes a Meeting
LOCATION:fruux HQ
CLASS:PUBLIC
RRULE:FREQ=WEEKLY;BYDAY=MO
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;RSVP=
 TRUE;LANGUAGE=en:MAILTO:dominik@fruux.com
CREATED:20140813T153211Z
LAST-MODIFIED:20140813T155353Z
END:VEVENT
ICS;

        $head = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
CALSCALE:GREGORIAN
PRODID:-//Ximian//NONSGML Evolution Calendar//EN
ICS;

        $old = $head;
        $old .= "\n".$vevent1;
        $old .= "\n".$vevent2;
        $old .= "\nEND:VCALENDAR";

        $new = $head;
        $new .= "\n".$vevent1;
        $new .= "\n".$vevent2;
        $new .= "\nEND:VCALENDAR";

        $expected = [['significantChange' => false]];

        $this->parse($old, $new, $expected, 'mailto:martin@fruux.com');
    }

    /**
     * An attendee accepting a single instance creates a participation-only
     * override: significant for the replier, not for the other attendees.
     */
    public function testParticipationOnlyOverrideInsignificantForOtherAttendees(): void
    {
        $old = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:recurring-partstat-override
DTSTAMP:20140813T142829Z
DTSTART;TZID=America/Toronto:20140815T110000
DTEND;TZID=America/Toronto:20140815T120000
RRULE:FREQ=WEEKLY
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:dominik@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:charlie@fruux.com
END:VEVENT
END:VCALENDAR
ICS;

        $new = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:recurring-partstat-override
DTSTAMP:20140813T142829Z
DTSTART;TZID=America/Toronto:20140815T110000
DTEND;TZID=America/Toronto:20140815T120000
RRULE:FREQ=WEEKLY
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:dominik@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:charlie@fruux.com
END:VEVENT
BEGIN:VEVENT
UID:recurring-partstat-override
RECURRENCE-ID;TZID=America/Toronto:20140822T110000
DTSTAMP:20140813T152829Z
DTSTART;TZID=America/Toronto:20140822T110000
DTEND;TZID=America/Toronto:20140822T120000
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=ACCEPTED:MAILTO:dominik@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:charlie@fruux.com
END:VEVENT
END:VCALENDAR
ICS;

        $expected = [
            ['significantChange' => true],
            ['significantChange' => false],
        ];

        $this->parse($old, $new, $expected, 'mailto:martin@fruux.com');
    }

    /**
     * A participation-only override on an all-day event, where DTSTART,
     * DTEND and RECURRENCE-ID are DATE values, is detected as well.
     */
    public function testParticipationOnlyOverrideOnAllDayEvent(): void
    {
        $old = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:recurring-allday-override
DTSTAMP:20140813T142829Z
DTSTART;VALUE=DATE:20140815
DTEND;VALUE=DATE:20140816
RRULE:FREQ=WEEKLY
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:dominik@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:charlie@fruux.com
END:VEVENT
END:VCALENDAR
ICS;

        $new = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:recurring-allday-override
DTSTAMP:20140813T142829Z
DTSTART;VALUE=DATE:20140815
DTEND;VALUE=DATE:20140816
RRULE:FREQ=WEEKLY
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:dominik@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:charlie@fruux.com
END:VEVENT
BEGIN:VEVENT
UID:recurring-allday-override
RECURRENCE-ID;VALUE=DATE:20140822
DTSTAMP:20140813T152829Z
DTSTART;VALUE=DATE:20140822
DTEND;VALUE=DATE:20140823
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=ACCEPTED:MAILTO:dominik@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:charlie@fruux.com
END:VEVENT
END:VCALENDAR
ICS;

        $expected = [
            ['significantChange' => true],
            ['significantChange' => false],
        ];

        $this->parse($old, $new, $expected, 'mailto:martin@fruux.com');
    }

    /**
     * A participation-only override on an event that uses DURATION instead
     * of DTEND is detected as well.
     */
    public function testParticipationOnlyOverrideOnDurationBasedEvent(): void
    {
        $old = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:recurring-duration-override
DTSTAMP:20140813T142829Z
DTSTART;TZID=America/Toronto:20140815T110000
DURATION:PT1H
RRULE:FREQ=WEEKLY
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:dominik@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:charlie@fruux.com
END:VEVENT
END:VCALENDAR
ICS;

        $new = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:recurring-duration-override
DTSTAMP:20140813T142829Z
DTSTART;TZID=America/Toronto:20140815T110000
DURATION:PT1H
RRULE:FREQ=WEEKLY
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:dominik@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:charlie@fruux.com
END:VEVENT
BEGIN:VEVENT
UID:recurring-duration-override
RECURRENCE-ID;TZID=America/Toronto:20140822T110000
DTSTAMP:20140813T152829Z
DTSTART;TZID=America/Toronto:20140822T110000
DURATION:PT1H
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=ACCEPTED:MAILTO:dominik@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:charlie@fruux.com
END:VEVENT
END:VCALENDAR
ICS;

        $expected = [
            ['significantChange' => true],
            ['significantChange' => false],
        ];

        $this->parse($old, $new, $expected, 'mailto:martin@fruux.com');
    }

    /**
     * A participation-only override on an event that has neither DTEND nor
     * DURATION is detected as well.
     */
    public function testParticipationOnlyOverrideOnEventWithoutEndOrDuration(): void
    {
        $old = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:recurring-no-duration-override
DTSTAMP:20140813T142829Z
DTSTART;TZID=America/Toronto:20140815T110000
RRULE:FREQ=WEEKLY
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:dominik@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:charlie@fruux.com
END:VEVENT
END:VCALENDAR
ICS;

        $new = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:recurring-no-duration-override
DTSTAMP:20140813T142829Z
DTSTART;TZID=America/Toronto:20140815T110000
RRULE:FREQ=WEEKLY
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:dominik@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:charlie@fruux.com
END:VEVENT
BEGIN:VEVENT
UID:recurring-no-duration-override
RECURRENCE-ID;TZID=America/Toronto:20140822T110000
DTSTAMP:20140813T152829Z
DTSTART;TZID=America/Toronto:20140822T110000
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=ACCEPTED:MAILTO:dominik@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:charlie@fruux.com
END:VEVENT
END:VCALENDAR
ICS;

        $expected = [
            ['significantChange' => true],
            ['significantChange' => false],
        ];

        $this->parse($old, $new, $expected, 'mailto:martin@fruux.com');
    }

    /**
     * An override with RANGE=THISANDFUTURE affects more than one instance
     * and therefore stays significant for everybody.
     */
    public function testThisAndFutureOverrideStaysSignificant(): void
    {
        $old = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:recurring-range-override
DTSTAMP:20140813T142829Z
DTSTART;TZID=America/Toronto:20140815T110000
DTEND;TZID=America/Toronto:20140815T120000
RRULE:FREQ=WEEKLY
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:dominik@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:charlie@fruux.com
END:VEVENT
END:VCALENDAR
ICS;

        $new = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:recurring-range-override
DTSTAMP:20140813T142829Z
DTSTART;TZID=America/Toronto:20140815T110000
DTEND;TZID=America/Toronto:20140815T120000
RRULE:FREQ=WEEKLY
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:dominik@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:charlie@fruux.com
END:VEVENT
BEGIN:VEVENT
UID:recurring-range-override
RECURRENCE-ID;RANGE=THISANDFUTURE;TZID=America/Toronto:20140822T110000
DTSTAMP:20140813T152829Z
DTSTART;TZID=America/Toronto:20140822T110000
DTEND;TZID=America/Toronto:20140822T120000
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=ACCEPTED:MAILTO:dominik@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:charlie@fruux.com
END:VEVENT
END:VCALENDAR
ICS;

        $expected = [
            ['significantChange' => true],
            ['significantChange' => true],
        ];

        $this->parse($old, $new, $expected, 'mailto:martin@fruux.com');
    }

    /**
     * An override that keeps the original start but changes the duration is
     * a real change and stays significant for everybody.
     */
    public function testResizedOverrideStaysSignificant(): void
    {
        $old = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:recurring-resized-override
DTSTAMP:20140813T142829Z
DTSTART;TZID=America/Toronto:20140815T110000
DTEND;TZID=America/Toronto:20140815T120000
RRULE:FREQ=WEEKLY
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:dominik@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:charlie@fruux.com
END:VEVENT
END:VCALENDAR
ICS;

        $new = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:recurring-resized-override
DTSTAMP:20140813T142829Z
DTSTART;TZID=America/Toronto:20140815T110000
DTEND;TZID=America/Toronto:20140815T120000
RRULE:FREQ=WEEKLY
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:dominik@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:charlie@fruux.com
END:VEVENT
BEGIN:VEVENT
UID:recurring-resized-override
RECURRENCE-ID;TZID=America/Toronto:20140822T110000
DTSTAMP:20140813T152829Z
DTSTART;TZID=America/Toronto:20140822T110000
DTEND;TZID=America/Toronto:20140822T130000
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:dominik@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:charlie@fruux.com
END:VEVENT
END:VCALENDAR
ICS;

        $expected = [
            ['significantChange' => true],
            ['significantChange' => true],
        ];

        $this->parse($old, $new, $expected, 'mailto:martin@fruux.com');
    }

    /**
     * An override that changes the STATUS of the instance is a real change
     * and stays significant for everybody.
     */
    public function testStatusChangedOverrideStaysSignificant(): void
    {
        $old = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:recurring-status-override
DTSTAMP:20140813T142829Z
DTSTART;TZID=America/Toronto:20140815T110000
DTEND;TZID=America/Toronto:20140815T120000
RRULE:FREQ=WEEKLY
STATUS:CONFIRMED
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:dominik@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:charlie@fruux.com
END:VEVENT
END:VCALENDAR
ICS;

        $new = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:recurring-status-override
DTSTAMP:20140813T142829Z
DTSTART;TZID=America/Toronto:20140815T110000
DTEND;TZID=America/Toronto:20140815T120000
RRULE:FREQ=WEEKLY
STATUS:CONFIRMED
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:dominik@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:charlie@fruux.com
END:VEVENT
BEGIN:VEVENT
UID:recurring-status-override
RECURRENCE-ID;TZID=America/Toronto:20140822T110000
DTSTAMP:20140813T152829Z
DTSTART;TZID=America/Toronto:20140822T110000
DTEND;TZID=America/Toronto:20140822T120000
STATUS:TENTATIVE
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:dominik@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:charlie@fruux.com
END:VEVENT
END:VCALENDAR
ICS;

        $expected = [
            ['significantChange' => true],
            ['significantChange' => true],
        ];

        $this->parse($old, $new, $expected, 'mailto:martin@fruux.com');
    }

    /**
     * An override that moves the occurrence to another time is a real change
     * and stays significant for everybody.
     */
    public function testMovedOverrideStaysSignificant(): void
    {
        $old = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:recurring-moved-override
DTSTAMP:20140813T142829Z
DTSTART;TZID=America/Toronto:20140815T110000
DTEND;TZID=America/Toronto:20140815T120000
RRULE:FREQ=WEEKLY
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:dominik@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:charlie@fruux.com
END:VEVENT
END:VCALENDAR
ICS;

        $new = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:recurring-moved-override
DTSTAMP:20140813T142829Z
DTSTART;TZID=America/Toronto:20140815T110000
DTEND;TZID=America/Toronto:20140815T120000
RRULE:FREQ=WEEKLY
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:dominik@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:charlie@fruux.com
END:VEVENT
BEGIN:VEVENT
UID:recurring-moved-override
RECURRENCE-ID;TZID=America/Toronto:20140822T110000
DTSTAMP:20140813T152829Z
DTSTART;TZID=America/Toronto:20140822T140000
DTEND;TZID=America/Toronto:20140822T150000
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:dominik@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:charlie@fruux.com
END:VEVENT
END:VCALENDAR
ICS;

        $expected = [
            ['significantChange' => true],
            ['significantChange' => true],
        ];

        $this->parse($old, $new, $expected, 'mailto:martin@fruux.com');
    }

    /**
     * An attendee invited via a participation-only override still has to
     * receive their invitation for that instance.
     */
    public function testAttendeeAddedOnOverrideStaysSignificant(): void
    {
        $old = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:recurring-added-attendee-override
DTSTAMP:20140813T142829Z
DTSTART;TZID=America/Toronto:20140815T110000
DTEND;TZID=America/Toronto:20140815T120000
RRULE:FREQ=WEEKLY
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:dominik@fruux.com
END:VEVENT
END:VCALENDAR
ICS;

        $new = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:recurring-added-attendee-override
DTSTAMP:20140813T142829Z
DTSTART;TZID=America/Toronto:20140815T110000
DTEND;TZID=America/Toronto:20140815T120000
RRULE:FREQ=WEEKLY
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:dominik@fruux.com
END:VEVENT
BEGIN:VEVENT
UID:recurring-added-attendee-override
RECURRENCE-ID;TZID=America/Toronto:20140822T110000
DTSTAMP:20140813T152829Z
DTSTART;TZID=America/Toronto:20140822T110000
DTEND;TZID=America/Toronto:20140822T120000
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:dominik@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:newperson@fruux.com
END:VEVENT
END:VCALENDAR
ICS;

        $expected = [
            ['significantChange' => false],
            ['significantChange' => true],
        ];

        $this->parse($old, $new, $expected, 'mailto:martin@fruux.com');
    }

    /**
     * Recurrence properties have no place on an overridden instance, but if a
     * client writes them anyway the override shapes the recurrence itself and
     * stays significant for everybody.
     */
    #[DataProvider('recurrenceProperties')]
    public function testOverrideWithRecurrencePropertyStaysSignificant(string $property): void
    {
        $old = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:recurring-recurrence-property-override
DTSTAMP:20140813T142829Z
DTSTART;TZID=America/Toronto:20140815T110000
DTEND;TZID=America/Toronto:20140815T120000
RRULE:FREQ=WEEKLY
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:dominik@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:charlie@fruux.com
END:VEVENT
END:VCALENDAR
ICS;

        $new = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:recurring-recurrence-property-override
DTSTAMP:20140813T142829Z
DTSTART;TZID=America/Toronto:20140815T110000
DTEND;TZID=America/Toronto:20140815T120000
RRULE:FREQ=WEEKLY
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:dominik@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:charlie@fruux.com
END:VEVENT
BEGIN:VEVENT
UID:recurring-recurrence-property-override
RECURRENCE-ID;TZID=America/Toronto:20140822T110000
DTSTAMP:20140813T152829Z
DTSTART;TZID=America/Toronto:20140822T110000
DTEND;TZID=America/Toronto:20140822T120000
$property
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=ACCEPTED:MAILTO:dominik@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:charlie@fruux.com
END:VEVENT
END:VCALENDAR
ICS;

        $expected = [
            ['significantChange' => true],
            ['significantChange' => true],
        ];

        $this->parse($old, $new, $expected, 'mailto:martin@fruux.com');
    }

    /**
     * @return array<string, array{string}>
     */
    public static function recurrenceProperties(): array
    {
        return [
            'RRULE' => ['RRULE:FREQ=DAILY'],
            'RDATE' => ['RDATE;TZID=America/Toronto:20140829T110000'],
            'EXDATE' => ['EXDATE;TZID=America/Toronto:20140829T110000'],
            'DUE' => ['DUE;TZID=America/Toronto:20140822T113000'],
        ];
    }

    /**
     * An object that holds a detached instance only has no master to compare
     * an override against, so significance is decided the usual way.
     */
    public function testDetachedInstanceWithoutMasterStaysSignificant(): void
    {
        $old = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:detached-instance
RECURRENCE-ID;TZID=America/Toronto:20140822T110000
DTSTAMP:20140813T142829Z
DTSTART;TZID=America/Toronto:20140822T110000
DTEND;TZID=America/Toronto:20140822T120000
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:dominik@fruux.com
END:VEVENT
END:VCALENDAR
ICS;

        $new = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:detached-instance
RECURRENCE-ID;TZID=America/Toronto:20140822T110000
DTSTAMP:20140813T152829Z
DTSTART;TZID=America/Toronto:20140822T140000
DTEND;TZID=America/Toronto:20140822T150000
ORGANIZER:MAILTO:martin@fruux.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:MAILTO:dominik@fruux.com
END:VEVENT
END:VCALENDAR
ICS;

        $expected = [['significantChange' => true]];

        $this->parse($old, $new, $expected, 'mailto:martin@fruux.com');
    }
}
