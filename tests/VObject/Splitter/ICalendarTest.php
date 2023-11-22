<?php

namespace Sabre\VObject\Splitter;

use PHPUnit\Framework\TestCase;
use Sabre\VObject;
use Sabre\VObject\ParseException;

class ICalendarTest extends TestCase
{
    protected string $version;

    public function setUp(): void
    {
        $this->version = VObject\Version::VERSION;
    }

    /**
     * @return false|resource
     */
    public function createStream(string $data)
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $data);
        rewind($stream);

        return $stream;
    }

    public function testICalendarImportValidEvent(): void
    {
        $data = <<<EOT
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTAMP:20140122T233226Z
DTSTART:20140101T070000Z
END:VEVENT
END:VCALENDAR
EOT;
        $tempFile = $this->createStream($data);

        $objects = new ICalendar($tempFile);

        $return = '';
        while ($object = $objects->getNext()) {
            $return .= $object->serialize();
        }
        self::assertEquals([], VObject\Reader::read($return)->validate());
    }

    public function testICalendarImportWrongType(): void
    {
        $this->expectException(ParseException::class);
        $data = <<<EOT
BEGIN:VCARD
UID:foo1
END:VCARD
BEGIN:VCARD
UID:foo2
END:VCARD
EOT;
        $tempFile = $this->createStream($data);

        new ICalendar($tempFile);
    }

    public function testICalendarImportEndOfData(): void
    {
        $data = <<<EOT
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTAMP:20140122T233226Z
END:VEVENT
END:VCALENDAR
EOT;
        $tempFile = $this->createStream($data);

        $objects = new ICalendar($tempFile);

        $return = '';
        while ($object = $objects->getNext()) {
            $return .= $object->serialize();
        }
        self::assertNull($objects->getNext());
    }

    public function testICalendarImportInvalidEvent(): void
    {
        $this->expectException(ParseException::class);
        $data = <<<EOT
EOT;
        $tempFile = $this->createStream($data);
        new ICalendar($tempFile);
    }

    public function testICalendarImportMultipleValidEvents(): void
    {
        $event[] = <<<EOT
BEGIN:VEVENT
UID:foo1
DTSTAMP:20140122T233226Z
DTSTART:20140101T050000Z
END:VEVENT
EOT;

        $event[] = <<<EOT
BEGIN:VEVENT
UID:foo2
DTSTAMP:20140122T233226Z
DTSTART:20140101T060000Z
END:VEVENT
EOT;

        $data = <<<EOT
BEGIN:VCALENDAR
$event[0]
$event[1]
END:VCALENDAR

EOT;
        $tempFile = $this->createStream($data);

        $objects = new ICalendar($tempFile);

        $return = '';
        $i = 0;
        while ($object = $objects->getNext()) {
            $expected = <<<EOT
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject $this->version//EN
CALSCALE:GREGORIAN
$event[$i]
END:VCALENDAR

EOT;

            $return .= $object->serialize();
            $expected = str_replace("\n", "\r\n", $expected);
            self::assertEquals($expected, $object->serialize());
            ++$i;
        }
        self::assertEquals([], VObject\Reader::read($return)->validate());
    }

    public function testICalendarImportEventWithoutUID(): void
    {
        $data = <<<EOT
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject $this->version//EN
CALSCALE:GREGORIAN
BEGIN:VEVENT
DTSTART:20140101T040000Z
DTSTAMP:20140122T233226Z
END:VEVENT
END:VCALENDAR

EOT;
        $tempFile = $this->createStream($data);

        $objects = new ICalendar($tempFile);

        $return = '';
        while ($object = $objects->getNext()) {
            $return .= $object->serialize();
        }

        $messages = VObject\Reader::read($return)->validate();

        if ($messages) {
            $messages = array_map(
                function ($item) { return $item['message']; },
                $messages
            );
            $this->fail('Validation errors: '.implode("\n", $messages));
        } else {
            self::assertEquals([], $messages);
        }
    }

    public function testICalendarImportMultipleVTIMEZONESAndMultipleValidEvents(): void
    {
        $timezones = <<<EOT
BEGIN:VTIMEZONE
TZID:Europe/Berlin
BEGIN:DAYLIGHT
TZOFFSETFROM:+0100
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU
DTSTART:19810329T020000
TZNAME:MESZ
TZOFFSETTO:+0200
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+0200
RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU
DTSTART:19961027T030000
TZNAME:MEZ
TZOFFSETTO:+0100
END:STANDARD
END:VTIMEZONE
BEGIN:VTIMEZONE
TZID:Europe/London
BEGIN:DAYLIGHT
TZOFFSETFROM:+0000
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU
DTSTART:19810329T010000
TZNAME:GMT+01:00
TZOFFSETTO:+0100
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+0100
RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU
DTSTART:19961027T020000
TZNAME:GMT
TZOFFSETTO:+0000
END:STANDARD
END:VTIMEZONE
EOT;

        $event[] = <<<EOT
BEGIN:VEVENT
UID:foo1
DTSTAMP:20140122T232710Z
DTSTART:20140101T010000Z
END:VEVENT
EOT;

        $event[] = <<<EOT
BEGIN:VEVENT
UID:foo2
DTSTAMP:20140122T232710Z
DTSTART:20140101T020000Z
END:VEVENT
EOT;

        $event[] = <<<EOT
BEGIN:VEVENT
UID:foo3
DTSTAMP:20140122T232710Z
DTSTART:20140101T030000Z
END:VEVENT
EOT;

        $data = <<<EOT
BEGIN:VCALENDAR
$timezones
$event[0]
$event[1]
$event[2]
END:VCALENDAR

EOT;
        $tempFile = $this->createStream($data);

        $objects = new ICalendar($tempFile);

        $return = '';
        $i = 0;
        while ($object = $objects->getNext()) {
            $expected = <<<EOT
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject $this->version//EN
CALSCALE:GREGORIAN
$timezones
$event[$i]
END:VCALENDAR

EOT;
            $expected = str_replace("\n", "\r\n", $expected);

            self::assertEquals($expected, $object->serialize());
            $return .= $object->serialize();
            ++$i;
        }

        self::assertEquals([], VObject\Reader::read($return)->validate());
    }

    public function testICalendarImportWithOutVTIMEZONES(): void
    {
        $data = <<<EOT
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Apple Inc.//Mac OS X 10.8//EN
CALSCALE:GREGORIAN
BEGIN:VEVENT
CREATED:20120605T072109Z
UID:D6716295-C10F-4B20-82F9-E1A3026C7DCF
DTEND;VALUE=DATE:20120717
TRANSP:TRANSPARENT
SUMMARY:Start Vorbereitung
DTSTART;VALUE=DATE:20120716
DTSTAMP:20120605T072115Z
SEQUENCE:2
BEGIN:VALARM
X-WR-ALARMUID:A99EDA6A-35EB-4446-B8BC-CDA3C60C627D
UID:A99EDA6A-35EB-4446-B8BC-CDA3C60C627D
TRIGGER:-PT15H
X-APPLE-DEFAULT-ALARM:TRUE
ATTACH;VALUE=URI:Basso
ACTION:AUDIO
END:VALARM
END:VEVENT
END:VCALENDAR

EOT;
        $tempFile = $this->createStream($data);

        $objects = new ICalendar($tempFile);

        $return = '';
        while ($object = $objects->getNext()) {
            $return .= $object->serialize();
        }

        $messages = VObject\Reader::read($return)->validate();
        self::assertEquals([], $messages);
    }
}
