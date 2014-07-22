<?php
namespace Sabre\VObject;

class RecurrenceIteratorExdateForFirstOccurrenceTest extends \PHPUnit_Framework_TestCase {
	public function testFastForwardSkipsExdateAtStart()
	{
		$feed = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject 3.2.0//EN
CALSCALE:GREGORIAN
X-WR-TIMEZONE:America/Chicago
METHOD:PUBLISH
X-WR-CALNAME:New Vision - Foster Parent Training
BEGIN:VTIMEZONE
TZID:America/Chicago
X-LIC-LOCATION:America/Chicago
BEGIN:DAYLIGHT
TZOFFSETFROM:-0600
TZOFFSETTO:-0500
TZNAME:CDT
DTSTART:19700308T020000
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=2SU
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:-0500
TZOFFSETTO:-0600
TZNAME:CST
DTSTART:19701101T020000
RRULE:FREQ=YEARLY;BYMONTH=11;BYDAY=1SU
END:STANDARD
END:VTIMEZONE
BEGIN:VEVENT
SUMMARY:New Vision - Foster Parent Training
LOCATION:435 Madison Street\, Clarksville TN 37040
CREATED;TZID=America/Chicago:20131220T123351
LAST-MODIFIED;TZID=America/Chicago:20140411T115847
UID:uid
DTSTART;TZID=America/Chicago:20140503T080000
DTEND;TZID=America/Chicago:20140503T130000
RRULE:FREQ=WEEKLY;BYDAY=SA;UNTIL=20140614T130000Z;WKST=MO
EXDATE;TZID=America/Chicago:20140503T080000
EXDATE;TZID=America/Chicago:20140510T080000
EXDATE;TZID=America/Chicago:20140517T080000
END:VEVENT
END:VCALENDAR
ICS;
		$calendar = Reader::read($feed);
		$start = new \DateTime("2014-05-03", new \DateTimeZone("America/Chicago"));
		$expected = new \DateTime("2014-05-24 08:00:00", new \DateTimeZone("America/Chicago"));

		$it = new RecurrenceIterator($calendar, "uid");
		$it->fastForward($start);

		$this->assertEquals($expected, $it->currentDate);
	}

	public function testOnlyOccurrenceRemovedIsNotValid()
	{
		$feed = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject 3.2.0//EN
CALSCALE:GREGORIAN
X-WR-TIMEZONE:America/Chicago
METHOD:PUBLISH
X-WR-CALNAME:New Vision - Foster Parent Training
BEGIN:VTIMEZONE
TZID:America/Chicago
X-LIC-LOCATION:America/Chicago
BEGIN:DAYLIGHT
TZOFFSETFROM:-0600
TZOFFSETTO:-0500
TZNAME:CDT
DTSTART:19700308T020000
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=2SU
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:-0500
TZOFFSETTO:-0600
TZNAME:CST
DTSTART:19701101T020000
RRULE:FREQ=YEARLY;BYMONTH=11;BYDAY=1SU
END:STANDARD
END:VTIMEZONE
BEGIN:VEVENT
SUMMARY:New Vision - Foster Parent Training
LOCATION:435 Madison Street\, Clarksville TN 37040
CREATED;TZID=America/Chicago:20131220T123351
LAST-MODIFIED;TZID=America/Chicago:20140411T115847
UID:uid
DTSTART;TZID=America/Chicago:20140503T080000
DTEND;TZID=America/Chicago:20140503T130000
RRULE:FREQ=WEEKLY;BYDAY=SA;UNTIL=20140504T130000Z;WKST=MO
EXDATE;TZID=America/Chicago:20140503T080000
END:VEVENT
END:VCALENDAR
ICS;
		$calendar = Reader::read($feed);
		$start = new \DateTime("2014-05-03", new \DateTimeZone("America/Chicago"));

		$it = new RecurrenceIterator($calendar, "uid");
		$it->fastForward($start);

		$this->assertFalse($it->valid());
	}

	public function testIssue75()
	{
		$feed = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
DTSTART:20140117T073000Z
DTEND:20140117T083000Z
RRULE:FREQ=WEEKLY;BYDAY=TU;INTERVAL=1;UNTIL=20140319T235900Z
UID:a0fd324402f181769bf3f2b4bed17d82
SUMMARY:rec test
DTSTAMP:20140119T142008Z
DESCRIPTION:
X-ALT-DESC;FMTTYPE=text/html:
EXDATE:20140117T073000Z
ORGANIZER:Super User
END:VEVENT
END:VCALENDAR
ICS;

		$calendar = Reader::read($feed);
		$start = new \DateTime("2014-01-17 07:30:00", new \DateTimeZone("UTC"));
		$end = new \DateTime("2014-03-19 23:59:00", new \DateTimeZone("UTC"));

		$calendar->expand($start, $end);
		$vevents = $calendar->select("VEVENT");

		$actual = array_shift($vevents);

		$this->assertNotEquals($start, $actual->DTSTART->getDateTime());
	}
}

