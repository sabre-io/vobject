<?php

namespace Sabre\VObject\Parser;

use
    Sabre\VObject;

class XmlTest extends \PHPUnit_Framework_TestCase {

    function testRFC6321Example1() {

        $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
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
XML;

        $component = VObject\Reader::readXML($xml);
        $this->assertEquals(
            'BEGIN:VCALENDAR' . "\r\n" .
            // VERSION comes first because this is required by vCard 4.0.
            'VERSION:2.0' . "\r\n" .
            'CALSCALE:GREGORIAN' . "\r\n" .
            'PRODID:-//Example Inc.//Example Calendar//EN' . "\r\n" .
            'BEGIN:VEVENT' . "\r\n" .
            'DTSTAMP:20080205T191224Z' . "\r\n" .
            'DTSTART;VALUE=DATE:20081006' . "\r\n" .
            'SUMMARY:Planning meeting' . "\r\n" .
            'UID:4088E990AD89CB3DBB484909' . "\r\n" .
            'END:VEVENT' . "\r\n" .
            'END:VCALENDAR' . "\r\n",
            VObject\Writer::write($component)
        );

    }
}
