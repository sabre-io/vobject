<?php

namespace Sabre\VObject\Parser;

use
    Sabre\VObject;

const CRLF = "\r\n";

class XmlTest extends \PHPUnit_Framework_TestCase {

    function testRFC6321Example1() {

        $this->assertXMLEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <calscale>
     <text>GREGORIAN</text>
   </calscale>
   <prodid>
    <text>-//Example Inc.//Example Calendar//EN</text>
   </prodid>
   <version>
     <text>2.0</text>
   </version>
  </properties>
  <components>
   <vevent>
    <properties>
     <dtstamp>
       <date-time>2008-02-05T19:12:24Z</date-time>
     </dtstamp>
     <dtstart>
       <date>2008-10-06</date>
     </dtstart>
     <summary>
      <text>Planning meeting</text>
     </summary>
     <uid>
      <text>4088E990AD89CB3DBB484909</text>
     </uid>
    </properties>
   </vevent>
  </components>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            // VERSION comes first because this is required by vCard 4.0.
            'VERSION:2.0' . CRLF .
            'CALSCALE:GREGORIAN' . CRLF .
            'PRODID:-//Example Inc.//Example Calendar//EN' . CRLF .
            'BEGIN:VEVENT' . CRLF .
            'DTSTAMP:20080205T191224Z' . CRLF .
            'DTSTART;VALUE=DATE:20081006' . CRLF .
            'SUMMARY:Planning meeting' . CRLF .
            'UID:4088E990AD89CB3DBB484909' . CRLF .
            'END:VEVENT' . CRLF .
            'END:VCALENDAR' . CRLF
        );

    }

    function testRFC6321Example2() {

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
  <vcalendar>
    <properties>
      <prodid>
        <text>-//Example Inc.//Example Client//EN</text>
      </prodid>
      <version>
        <text>2.0</text>
      </version>
    </properties>
    <components>
      <vtimezone>
        <properties>
          <last-modified>
            <date-time>2004-01-10T03:28:45Z</date-time>
          </last-modified>
          <tzid><text>US/Eastern</text></tzid>
        </properties>
        <components>
          <daylight>
            <properties>
              <dtstart>
                <date-time>2000-04-04T02:00:00</date-time>
              </dtstart>
              <rrule>
                <recur>
                  <freq>YEARLY</freq>
                  <byday>1SU</byday>
                  <bymonth>4</bymonth>
                </recur>
              </rrule>
              <tzname>
                <text>EDT</text>
              </tzname>
              <tzoffsetfrom>
                <utc-offset>-05:00</utc-offset>
              </tzoffsetfrom>
              <tzoffsetto>
                <utc-offset>-04:00</utc-offset>
              </tzoffsetto>
            </properties>
          </daylight>
          <standard>
            <properties>
              <dtstart>
                <date-time>2000-10-26T02:00:00</date-time>
              </dtstart>
              <rrule>
                <recur>
                  <freq>YEARLY</freq>
                  <byday>-1SU</byday>
                  <bymonth>10</bymonth>
                </recur>
              </rrule>
              <tzname>
                <text>EST</text>
              </tzname>
              <tzoffsetfrom>
                <utc-offset>-04:00</utc-offset>
              </tzoffsetfrom>
              <tzoffsetto>
                <utc-offset>-05:00</utc-offset>
              </tzoffsetto>
            </properties>
          </standard>
        </components>
      </vtimezone>
      <vevent>
        <properties>
          <dtstamp>
            <date-time>2006-02-06T00:11:21Z</date-time>
          </dtstamp>
          <dtstart>
            <parameters>
              <tzid><text>US/Eastern</text></tzid>
            </parameters>
            <date-time>2006-01-02T12:00:00</date-time>
          </dtstart>
          <duration>
            <duration>PT1H</duration>
          </duration>
          <rrule>
            <recur>
              <freq>DAILY</freq>
              <count>5</count>
            </recur>
          </rrule>
          <rdate>
            <parameters>
              <tzid><text>US/Eastern</text></tzid>
            </parameters>
            <period>
              <start>2006-01-02T15:00:00</start>
              <duration>PT2H</duration>
            </period>
          </rdate>
          <summary>
            <text>Event #2</text>
          </summary>
          <description>
            <text>We are having a meeting all this week at 12
pm for one hour, with an additional meeting on the first day
2 hours long.&#x0a;Please bring your own lunch for the 12 pm
meetings.</text>
          </description>
          <uid>
            <text>00959BC664CA650E933C892C@example.com</text>
          </uid>
        </properties>
      </vevent>
      <vevent>
        <properties>
          <dtstamp>
            <date-time>2006-02-06T00:11:21Z</date-time>
          </dtstamp>
          <dtstart>
            <parameters>
              <tzid><text>US/Eastern</text></tzid>
            </parameters>
            <date-time>2006-01-04T14:00:00</date-time>
          </dtstart>
          <duration>
            <duration>PT1H</duration>
          </duration>
          <recurrence-id>
            <parameters>
              <tzid><text>US/Eastern</text></tzid>
            </parameters>
            <date-time>2006-01-04T12:00:00</date-time>
          </recurrence-id>
          <summary>
            <text>Event #2 bis</text>
          </summary>
          <uid>
            <text>00959BC664CA650E933C892C@example.com</text>
          </uid>
        </properties>
      </vevent>
    </components>
  </vcalendar>
</icalendar>
XML;

        $component = VObject\Reader::readXML($xml);
        $this->assertEquals(
            'BEGIN:VCALENDAR' . CRLF .
            'VERSION:2.0' . CRLF .
            'PRODID:-//Example Inc.//Example Client//EN' . CRLF .
            'BEGIN:VTIMEZONE' . CRLF .
            'LAST-MODIFIED:20040110T032845Z' . CRLF .
            'TZID:US/Eastern' . CRLF .
            'BEGIN:DAYLIGHT' . CRLF .
            'DTSTART:20000404T020000' . CRLF .
            'RRULE:FREQ=YEARLY;BYDAY=1SU;BYMONTH=4' . CRLF .
            'TZNAME:EDT' . CRLF .
            'TZOFFSETFROM:-0500' . CRLF .
            'TZOFFSETTO:-0400' . CRLF .
            'END:DAYLIGHT' . CRLF .
            'BEGIN:STANDARD' . CRLF .
            'DTSTART:20001026T020000' . CRLF .
            'RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10' . CRLF .
            'TZNAME:EST' . CRLF .
            'TZOFFSETFROM:-0400' . CRLF .
            'TZOFFSETTO:-0500' . CRLF .
            'END:STANDARD' . CRLF .
            'END:VTIMEZONE' . CRLF .
            'BEGIN:VEVENT' . CRLF .
            'DTSTAMP:20060206T001121Z' . CRLF .
            'DTSTART;TZID=US/Eastern:20060102T120000' . CRLF .
            'DURATION:PT1H' . CRLF .
            'RRULE:FREQ=DAILY;COUNT=5' . CRLF .
            'RDATE;TZID=US/Eastern;VALUE=PERIOD:20060102T150000/PT2H' . CRLF .
            'SUMMARY:Event #2' . CRLF .
            'DESCRIPTION:We are having a meeting all this week at 12\npm for one hour\, ' . CRLF .
            ' with an additional meeting on the first day\n2 hours long.\nPlease bring y' . CRLF .
            ' our own lunch for the 12 pm\nmeetings.' . CRLF .
            'UID:00959BC664CA650E933C892C@example.com' . CRLF .
            'END:VEVENT' . CRLF .
            'BEGIN:VEVENT' . CRLF .
            'DTSTAMP:20060206T001121Z' . CRLF .
            'DTSTART;TZID=US/Eastern:20060104T140000' . CRLF .
            'DURATION:PT1H' . CRLF .
            'RECURRENCE-ID;TZID=US/Eastern:20060104T120000' . CRLF .
            'SUMMARY:Event #2 bis' . CRLF .
            'UID:00959BC664CA650E933C892C@example.com' . CRLF .
            'END:VEVENT' . CRLF .
            'END:VCALENDAR' . CRLF,
            VObject\Writer::write($component)
        );

    }

    /**
     * iCalendar Stream.
     */
    function testRFC6321Section3_2() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar/>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'END:VCALENDAR' . CRLF
        );
    }

    /**
     * All components exist.
     */
    function testRFC6321Section3_3() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <components>
   <vtimezone/>
   <vevent/>
   <vtodo/>
   <vjournal/>
   <vfreebusy/>
   <standard/>
   <daylight/>
   <valarm/>
  </components>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'BEGIN:VTIMEZONE' . CRLF .
            'END:VTIMEZONE' . CRLF .
            'BEGIN:VEVENT' . CRLF .
            'END:VEVENT' . CRLF .
            'BEGIN:VTODO' . CRLF .
            'END:VTODO' . CRLF .
            'BEGIN:VJOURNAL' . CRLF .
            'END:VJOURNAL' . CRLF .
            'BEGIN:VFREEBUSY' . CRLF .
            'END:VFREEBUSY' . CRLF .
            'BEGIN:STANDARD' . CRLF .
            'END:STANDARD' . CRLF .
            'BEGIN:DAYLIGHT' . CRLF .
            'END:DAYLIGHT' . CRLF .
            'BEGIN:VALARM' . CRLF .
            'END:VALARM' . CRLF .
            'END:VCALENDAR' . CRLF
        );

    }

    /**
     * Properties, Special Cases, GEO.
     */
    function testRFC6321Section3_4_1_2() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <geo>
    <latitude>37.386013</latitude>
    <longitude>-122.082932</longitude>
   </geo>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'GEO:37.386013;-122.082932' . CRLF .
            'END:VCALENDAR' . CRLF
        );

    }

    /**
     * Properties, Special Cases, REQUEST-STATUS.
     */
    function testRFC6321Section3_4_1_3() {

        // Example 1 of RFC5545, Section 3.8.8.3.
        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <request-status>
    <code>2.0</code>
    <description>Success</description>
   </request-status>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'REQUEST-STATUS:2.0;Success' . CRLF .
            'END:VCALENDAR' . CRLF
        );

        // Example 2 of RFC5545, Section 3.8.8.3.
        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <request-status>
    <code>3.1</code>
    <description>Invalid property value</description>
    <data>DTSTART:96-Apr-01</data>
   </request-status>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'REQUEST-STATUS:3.1;Invalid property value;DTSTART:96-Apr-01' . CRLF .
            'END:VCALENDAR' . CRLF
        );

        // Example 3 of RFC5545, Section 3.8.8.3.
        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <request-status>
    <code>2.8</code>
    <description>Success, repeating event ignored. Scheduled as a single event.</description>
    <data>RRULE:FREQ=WEEKLY;INTERVAL=2</data>
   </request-status>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'REQUEST-STATUS:2.8;Success\, repeating event ignored. Scheduled as a single' .  CRLF .
            '  event.;RRULE:FREQ=WEEKLY\;INTERVAL=2' . CRLF .
            'END:VCALENDAR' . CRLF
        );

        // Example 4 of RFC5545, Section 3.8.8.3.
        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <request-status>
    <code>4.1</code>
    <description>Event conflict.  Date-time is busy.</description>
   </request-status>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'REQUEST-STATUS:4.1;Event conflict.  Date-time is busy.' . CRLF .
            'END:VCALENDAR' . CRLF
        );

        // Example 5 of RFC5545, Section 3.8.8.3.
        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <request-status>
    <code>3.7</code>
    <description>Invalid calendar user</description>
    <data>ATTENDEE:mailto:jsmith@example.com</data>
   </request-status>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'REQUEST-STATUS:3.7;Invalid calendar user;ATTENDEE:mailto:jsmith@example.com' . CRLF .
            'END:VCALENDAR' . CRLF
        );

    }

    /**
     * Values, Binary.
     */
    function testRFC6321Section3_6_1() {

        $this->assertXMLEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <attach>
    <binary>SGVsbG8gV29ybGQh</binary>
   </attach>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'ATTACH:SGVsbG8gV29ybGQh' . CRLF .
            'END:VCALENDAR' . CRLF
        );

        // In vCard 4, BINARY no longer exists and is replaced by URI.
        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <attach>
    <uri>SGVsbG8gV29ybGQh</uri>
   </attach>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'ATTACH:SGVsbG8gV29ybGQh' . CRLF .
            'END:VCALENDAR' . CRLF
        );

    }

    /**
     * Values, Boolean.
     */
    function testRFC6321Section3_6_2() {

        $this->assertXMLEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <attendee>
    <parameters>
     <rsvp><boolean>true</boolean></rsvp>
    </parameters>
    <cal-address>mailto:cyrus@example.com</cal-address>
   </attendee>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'ATTENDEE;RSVP=true:mailto:cyrus@example.com' . CRLF .
            'END:VCALENDAR' . CRLF
        );

    }

    /**
     * Values, Calendar User Address.
     */
    function testRFC6321Section3_6_3() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <attendee>
    <cal-address>mailto:cyrus@example.com</cal-address>
   </attendee>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'ATTENDEE:mailto:cyrus@example.com' . CRLF .
            'END:VCALENDAR' . CRLF
        );

    }

    /**
     * Values, Date.
     */
    function testRFC6321Section3_6_4() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <dtstart>
    <date>2011-05-17</date>
   </dtstart>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'DTSTART;VALUE=DATE:20110517' . CRLF .
            'END:VCALENDAR' . CRLF
        );

    }

    /**
     * Values, Date-Time.
     */
    function testRFC6321Section3_6_5() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <dtstart>
    <date-time>2011-05-17T12:00:00</date-time>
   </dtstart>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'DTSTART:20110517T120000' . CRLF .
            'END:VCALENDAR' . CRLF
        );

    }

    /**
     * Values, Duration.
     */
    function testRFC6321Section3_6_6() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <duration>
    <duration>P1D</duration>
   </duration>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'DURATION:P1D' . CRLF .
            'END:VCALENDAR' . CRLF
        );

    }

    /**
     * Values, Float.
     */
    function testRFC6321Section3_6_7() {

        // GEO uses <float /> with a positive and a non-negative numbers.
        $this->testRFC6321Section3_4_1_2();

    }

    /**
     * Values, Integer.
     */
    function testRFC6321Section3_6_8() {

        $this->assertXMLEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <foo>
    <integer>42</integer>
   </foo>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'FOO:42' . CRLF .
            'END:VCALENDAR' . CRLF
        );

        $this->assertXMLEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <foo>
    <integer>-42</integer>
   </foo>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'FOO:-42' . CRLF .
            'END:VCALENDAR' . CRLF
        );

    }

    /**
     * Values, Period of Time.
     */
    function testRFC6321Section3_6_9() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <freebusy>
    <period>
     <start>2011-05-17T12:00:00</start>
     <duration>P1H</duration>
    </period>
   </freebusy>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'FREEBUSY:20110517T120000/P1H' . CRLF .
            'END:VCALENDAR' . CRLF
        );

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <freebusy>
    <period>
     <start>2011-05-17T12:00:00</start>
     <end>2012-05-17T12:00:00</end>
    </period>
   </freebusy>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'FREEBUSY:20110517T120000/20120517T120000' . CRLF .
            'END:VCALENDAR' . CRLF
        );

    }

    /**
     * Values, Recurrence Rule.
     */
    function testRFC6321Section3_6_10() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <rrule>
    <recur>
     <freq>YEARLY</freq>
     <count>5</count>
     <byday>-1SU</byday>
     <bymonth>10</bymonth>
    </recur>
   </rrule>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'RRULE:FREQ=YEARLY;COUNT=5;BYDAY=-1SU;BYMONTH=10' . CRLF .
            'END:VCALENDAR' . CRLF
        );

    }

    /**
     * Values, Text.
     */
    function testRFC6321Section3_6_11() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <calscale>
    <text>GREGORIAN</text>
   </calscale>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'CALSCALE:GREGORIAN' . CRLF .
            'END:VCALENDAR' . CRLF
        );

    }

    /**
     * Values, Time.
     */
    function testRFC6321Section3_6_12() {

        $this->assertXMLEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <foo>
    <time>12:00:00</time>
   </foo>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'FOO:120000' . CRLF .
            'END:VCALENDAR' . CRLF
        );

    }

    /**
     * Values, URI.
     */
    function testRFC6321Section3_6_13() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <attach>
    <uri>http://calendar.example.com</uri>
   </attach>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'ATTACH:http://calendar.example.com' . CRLF .
            'END:VCALENDAR' . CRLF
        );

    }

    /**
     * Values, UTC Offset.
     */
    function testRFC6321Section3_6_14() {

        // Example 1 of RFC5545, Section 3.3.14.
        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <tzoffsetfrom>
    <utc-offset>-05:00</utc-offset>
   </tzoffsetfrom>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'TZOFFSETFROM:-0500' . CRLF .
            'END:VCALENDAR' . CRLF
        );

        // Example 2 of RFC5545, Section 3.3.14.
        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <tzoffsetfrom>
    <utc-offset>+01:00</utc-offset>
   </tzoffsetfrom>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'TZOFFSETFROM:+0100' . CRLF .
            'END:VCALENDAR' . CRLF
        );

    }

    /**
     * Handling Unrecognized Properties or Parameters.
     */
    function testRFC6321Section5() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <x-property>
    <unknown>20110512T120000Z</unknown>
   </x-property>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'X-PROPERTY:20110512T120000Z' . CRLF .
            'END:VCALENDAR' . CRLF
        );

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <dtstart>
    <parameters>
     <x-param>
      <text>PT30M</text>
     </x-param>
    </parameters>
    <date-time>2011-05-12T13:00:00Z</date-time>
   </dtstart>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'DTSTART;X-PARAM=PT30M:20110512T130000Z' . CRLF .
            'END:VCALENDAR' . CRLF
        );

    }

    function testRDateWithDateTime() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <rdate>
    <date-time>2008-02-05T19:12:24Z</date-time>
   </rdate>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'RDATE:20080205T191224Z' . CRLF .
            'END:VCALENDAR' . CRLF
        );

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <rdate>
    <date-time>2008-02-05T19:12:24Z</date-time>
    <date-time>2009-02-05T19:12:24Z</date-time>
   </rdate>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'RDATE:20080205T191224Z,20090205T191224Z' . CRLF .
            'END:VCALENDAR' . CRLF
        );

    }

    function testRDateWithDate() {

        $this->assertXMLEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <rdate>
    <date>2008-10-06</date>
   </rdate>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'RDATE:20081006' . CRLF .
            'END:VCALENDAR' . CRLF
        );

        $this->assertXMLEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <rdate>
    <date>2008-10-06</date>
    <date>2009-10-06</date>
    <date>2010-10-06</date>
   </rdate>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'RDATE:20081006,20091006,20101006' . CRLF .
            'END:VCALENDAR' . CRLF
        );

    }

    function testRDateWithPeriod() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <rdate>
    <parameters>
     <tzid>
      <text>US/Eastern</text>
     </tzid>
    </parameters>
    <period>
     <start>2006-01-02T15:00:00</start>
     <duration>PT2H</duration>
    </period>
   </rdate>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'RDATE;TZID=US/Eastern;VALUE=PERIOD:20060102T150000/PT2H' . CRLF .
            'END:VCALENDAR' . CRLF
        );

        $this->assertXMLEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
  <properties>
   <rdate>
    <parameters>
     <tzid>
      <text>US/Eastern</text>
     </tzid>
    </parameters>
    <period>
     <start>2006-01-02T15:00:00</start>
     <duration>PT2H</duration>
    </period>
    <period>
     <start>2008-01-02T15:00:00</start>
     <duration>PT1H</duration>
    </period>
   </rdate>
  </properties>
 </vcalendar>
</icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'RDATE;TZID=US/Eastern;VALUE=PERIOD:20060102T150000/PT2H,20080102T150000/PT1' . CRLF .
            ' H' . CRLF .
            'END:VCALENDAR' . CRLF
        );

    }

    /**
     * Basic example.
     */
    function testRFC6351Basic() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <fn>
   <text>J. Doe</text>
  </fn>
  <n>
   <surname>Doe</surname>
   <given>J.</given>
   <additional/>
   <prefix/>
   <suffix/>
  </n>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'FN:J. Doe' . CRLF .
            'N:Doe;J.;;;' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Example 1.
     */
    function testRFC6351Example1() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <fn>
   <text>J. Doe</text>
  </fn>
  <n>
   <surname>Doe</surname>
   <given>J.</given>
   <additional/>
   <prefix/>
   <suffix/>
  </n>
  <x-file>
   <parameters>
    <mediatype>
     <text>image/jpeg</text>
    </mediatype>
   </parameters>
   <unknown>alien.jpg</unknown>
  </x-file>
  <a xmlns="http://www.w3.org/1999/xhtml" href="http://www.example.com">My web page!</a>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'FN:J. Doe' . CRLF .
            'N:Doe;J.;;;' . CRLF .
            'X-FILE;MEDIATYPE=image/jpeg:alien.jpg' . CRLF .
            'XML:<a xmlns="http://www.w3.org/1999/xhtml" href="http://www.example.com">M' . CRLF .
            ' y web page!</a>' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Design Considerations.
     */
    function testRFC6351Section5() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <tel>
   <parameters>
    <type>
     <text>voice</text>
     <text>video</text>
    </type>
   </parameters>
   <uri>tel:+1-555-555-555</uri>
  </tel>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'TEL;TYPE="voice,video":tel:+1-555-555-555' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Design Considerations.
     */
    function testRFC6351Section5Group() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <tel>
   <uri>tel:+1-555-555-556</uri>
  </tel>
  <group name="contact">
   <fn>
    <text>Gordon</text>
   </fn>
   <tel>
    <uri>tel:+1-555-555-555</uri>
   </tel>
  </group>
  <group name="media">
   <fn>
    <text>Gordon</text>
   </fn>
  </group>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'TEL:tel:+1-555-555-556' . CRLF .
            'contact.FN:Gordon' . CRLF .
            'contact.TEL:tel:+1-555-555-555' . CRLF .
            'media.FN:Gordon' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Extensibility.
     */
    function testRFC6351Section5_1_NoNamespace() {

        $this->assertXMLEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <x-my-prop>
   <parameters>
    <pref>
     <integer>1</integer>
    </pref>
   </parameters>
   <text>value goes here</text>
  </x-my-prop>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'X-MY-PROP;PREF=1:value goes here' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: SOURCE.
     */
    function testRFC6350Section6_1_3() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <source>
   <uri>ldap://ldap.example.com/cn=Babs%20Jensen,%20o=Babsco,%20c=US</uri>
  </source>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'SOURCE:ldap://ldap.example.com/cn=Babs%20Jensen\,%20o=Babsco\,%20c=US' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: KIND.
     */
    function testRFC6350Section6_1_4() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <kind>
   <text>individual</text>
  </kind>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'KIND:individual' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: FN.
     */
    function testRFC6350Section6_2_1() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <fn>
   <text>Mr. John Q. Public, Esq.</text>
  </fn>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'FN:Mr. John Q. Public\, Esq.' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: N.
     */
    function testRFC6350Section6_2_2() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <n>
   <surname>Stevenson</surname>
   <given>John</given>
   <additional>Philip,Paul</additional>
   <prefix>Dr.</prefix>
   <suffix>Jr.,M.D.,A.C.P.</suffix>
  </n>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'N:Stevenson;John;Philip\,Paul;Dr.;Jr.\,M.D.\,A.C.P.' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: NICKNAME.
     */
    function testRFC6350Section6_2_3() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <nickname>
   <text>Jim</text>
   <text>Jimmie</text>
  </nickname>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'NICKNAME:Jim,Jimmie' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: PHOTO.
     */
    function testRFC6350Section6_2_4() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <photo>
   <uri>http://www.example.com/pub/photos/jqpublic.gif</uri>
  </photo>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'PHOTO:http://www.example.com/pub/photos/jqpublic.gif' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    function testRFC6350Section6_2_5() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <bday>
   <date-and-or-time>19531015T231000Z</date-and-or-time>
  </bday>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'BDAY:19531015T231000Z' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    function testRFC6350Section6_2_6() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <anniversary>
   <date-and-or-time>19960415</date-and-or-time>
  </anniversary>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'ANNIVERSARY:19960415' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: GENDER.
     */
    function testRFC6350Section6_2_7() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <gender>
   <sex>Jim</sex>
   <text>Jimmie</text>
  </gender>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'GENDER:Jim;Jimmie' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: ADR.
     */
    function testRFC6350Section6_3_1() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <adr>
   <pobox/>
   <ext/>
   <street>123 Main Street</street>
   <locality>Any Town</locality>
   <region>CA</region>
   <code>91921-1234</code>
   <country>U.S.A.</country>
  </adr>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'ADR:;;123 Main Street;Any Town;CA;91921-1234;U.S.A.' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: TEL.
     */
    function testRFC6350Section6_4_1() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <tel>
   <parameters>
    <type>
     <text>home</text>
    </type>
   </parameters>
   <uri>tel:+33-01-23-45-67</uri>
  </tel>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'TEL;TYPE=home:tel:+33-01-23-45-67' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: EMAIL.
     */
    function testRFC6350Section6_4_2() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <email>
   <parameters>
    <type>
     <text>work</text>
    </type>
   </parameters>
   <text>jqpublic@xyz.example.com</text>
  </email>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'EMAIL;TYPE=work:jqpublic@xyz.example.com' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: IMPP.
     */
    function testRFC6350Section6_4_3() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <impp>
   <parameters>
    <pref>
     <text>1</text>
    </pref>
   </parameters>
   <uri>xmpp:alice@example.com</uri>
  </impp>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'IMPP;PREF=1:xmpp:alice@example.com' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: LANG.
     */
    function testRFC6350Section6_4_4() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <lang>
   <parameters>
    <type>
     <text>work</text>
    </type>
    <pref>
     <text>2</text>
    </pref>
   </parameters>
   <language-tag>en</language-tag>
  </lang>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'LANG;TYPE=work;PREF=2:en' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: TZ.
     */
    function testRFC6350Section6_5_1() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <tz>
   <text>Raleigh/North America</text>
  </tz>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'TZ:Raleigh/North America' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: GEO.
     */
    function testRFC6350Section6_5_2() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <geo>
   <uri>geo:37.386013,-122.082932</uri>
  </geo>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'GEO:geo:37.386013\,-122.082932' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: TITLE.
     */
    function testRFC6350Section6_6_1() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <title>
   <text>Research Scientist</text>
  </title>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'TITLE:Research Scientist' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: ROLE.
     */
    function testRFC6350Section6_6_2() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <role>
   <text>Project Leader</text>
  </role>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'ROLE:Project Leader' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: LOGO.
     */
    function testRFC6350Section6_6_3() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <logo>
   <uri>http://www.example.com/pub/logos/abccorp.jpg</uri>
  </logo>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'LOGO:http://www.example.com/pub/logos/abccorp.jpg' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: ORG.
     */
    function testRFC6350Section6_6_4() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <org>
   <text>ABC, Inc.</text>
   <text>North American Division</text>
   <text>Marketing</text>
  </org>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'ORG:ABC\, Inc.;North American Division;Marketing' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: MEMBER.
     */
    function testRFC6350Section6_6_5() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <member>
   <uri>urn:uuid:03a0e51f-d1aa-4385-8a53-e29025acd8af</uri>
  </member>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'MEMBER:urn:uuid:03a0e51f-d1aa-4385-8a53-e29025acd8af' . CRLF .
            'END:VCARD' . CRLF
        );

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <member>
   <uri>mailto:subscriber1@example.com</uri>
  </member>
  <member>
   <uri>xmpp:subscriber2@example.com</uri>
  </member>
  <member>
   <uri>sip:subscriber3@example.com</uri>
  </member>
  <member>
   <uri>tel:+1-418-555-5555</uri>
  </member>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'MEMBER:mailto:subscriber1@example.com' . CRLF .
            'MEMBER:xmpp:subscriber2@example.com' . CRLF .
            'MEMBER:sip:subscriber3@example.com' . CRLF .
            'MEMBER:tel:+1-418-555-5555' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: RELATED.
     */
    function testRFC6350Section6_6_6() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <related>
   <parameters>
    <type>
     <text>friend</text>
    </type>
   </parameters>
   <uri>urn:uuid:f81d4fae-7dec-11d0-a765-00a0c91e6bf6</uri>
  </related>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'RELATED;TYPE=friend:urn:uuid:f81d4fae-7dec-11d0-a765-00a0c91e6bf6' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: CATEGORIES.
     */
    function testRFC6350Section6_7_1() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <categories>
   <text>INTERNET</text>
   <text>IETF</text>
   <text>INDUSTRY</text>
   <text>INFORMATION TECHNOLOGY</text>
  </categories>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'CATEGORIES:INTERNET,IETF,INDUSTRY,INFORMATION TECHNOLOGY' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: NOTE.
     */
    function testRFC6350Section6_7_2() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <note>
   <text>Foo, bar</text>
  </note>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'NOTE:Foo\, bar' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: PRODID.
     */
    function testRFC6350Section6_7_3() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <prodid>
   <text>-//ONLINE DIRECTORY//NONSGML Version 1//EN</text>
  </prodid>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'PRODID:-//ONLINE DIRECTORY//NONSGML Version 1//EN' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    function testRFC6350Section6_7_4() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <rev>
   <timestamp>19951031T222710Z</timestamp>
  </rev>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'REV:19951031T222710Z' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: SOUND.
     */
    function testRFC6350Section6_7_5() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <sound>
   <uri>CID:JOHNQPUBLIC.part8.19960229T080000.xyzMail@example.com</uri>
  </sound>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'SOUND:CID:JOHNQPUBLIC.part8.19960229T080000.xyzMail@example.com' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: UID.
     */
    function testRFC6350Section6_7_6() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <uid>
   <text>urn:uuid:f81d4fae-7dec-11d0-a765-00a0c91e6bf6</text>
  </uid>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'UID:urn:uuid:f81d4fae-7dec-11d0-a765-00a0c91e6bf6' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: CLIENTPIDMAP.
     */
    function testRFC6350Section6_7_7() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <clientpidmap>
   <sourceid>1</sourceid>
   <uri>urn:uuid:3df403f4-5924-4bb7-b077-3c711d9eb34b</uri>
  </clientpidmap>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'CLIENTPIDMAP:1;urn:uuid:3df403f4-5924-4bb7-b077-3c711d9eb34b' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: URL.
     */
    function testRFC6350Section6_7_8() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <url>
   <uri>http://example.org/restaurant.french/~chezchic.html</uri>
  </url>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'URL:http://example.org/restaurant.french/~chezchic.html' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: VERSION.
     */
    function testRFC6350Section6_7_9() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard/>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: KEY.
     */
    function testRFC6350Section6_8_1() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <key>
   <parameters>
    <mediatype>
     <text>application/pgp-keys</text>
    </mediatype>
   </parameters>
   <text>ftp://example.com/keys/jdoe</text>
  </key>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'KEY;MEDIATYPE=application/pgp-keys:ftp://example.com/keys/jdoe' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: FBURL.
     */
    function testRFC6350Section6_9_1() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <fburl>
   <parameters>
    <pref>
     <text>1</text>
    </pref>
   </parameters>
   <uri>http://www.example.com/busy/janedoe</uri>
  </fburl>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'FBURL;PREF=1:http://www.example.com/busy/janedoe' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: CALADRURI.
     */
    function testRFC6350Section6_9_2() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <caladruri>
   <uri>http://example.com/calendar/jdoe</uri>
  </caladruri>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'CALADRURI:http://example.com/calendar/jdoe' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: CALURI.
     */
    function testRFC6350Section6_9_3() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <caluri>
   <parameters>
    <pref>
     <text>1</text>
    </pref>
   </parameters>
   <uri>http://cal.example.com/calA</uri>
  </caluri>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'CALURI;PREF=1:http://cal.example.com/calA' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Property: CAPURI.
     */
    function testRFC6350SectionA_3() {

        $this->assertXMLReflexivelyEqualsToMimeDir(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<vcards xmlns="urn:ietf:params:xml:ns:vcard-4.0">
 <vcard>
  <capuri>
   <uri>http://cap.example.com/capA</uri>
  </capuri>
 </vcard>
</vcards>
XML
,
            'BEGIN:VCARD' . CRLF .
            'VERSION:4.0' . CRLF .
            'CAPURI:http://cap.example.com/capA' . CRLF .
            'END:VCARD' . CRLF
        );

    }

    /**
     * Check this equality:
     *     XML -> object model -> MIME Dir.
     */
    protected function assertXMLEqualsToMimeDir($xml, $mimedir) {

        $component = VObject\Reader::readXML($xml);
        $this->assertEquals($mimedir, VObject\Writer::write($component));

    }

    /**
     * Check this (reflexive) equality:
     *     XML -> object model -> MIME Dir -> object model -> XML.
     */
    protected function assertXMLReflexivelyEqualsToMimeDir($xml, $mimedir) {

        $this->assertXMLEqualsToMimeDir($xml, $mimedir);

        $component = VObject\Reader::read($mimedir);
        $this->assertEquals($xml, rtrim(VObject\Writer::writeXML($component)));

    }
}
