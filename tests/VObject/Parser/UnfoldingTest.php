<?php

namespace Sabre\VObject\Parser;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\ParseException;
use Sabre\VObject\Reader;

class UnfoldingTest extends TestCase
{
    public function testFixUnfoldingICS()
    {
        $vcard = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
DTEND;TZID=America/Edmonton:20181212T112000
LAST-MODIFIED:20181128T171443Z
UID:071597C2-0692-4B67-9C8C-3FC0FEABB9E6
DTSTAMP:20181211T181642Z
LOCATION: test
SEQUENCE:0
SUMMARY:test
DTSTART;TZID=America/Edmonton:20181212T102000
CREATED:20171107T180706Z
X-APPLE-STRUCTURED-LOCATION;VALUE=URI;X-ADDRESS=221-501 BBBBBB BBbbb\\nSs
 sssss BBBBB XXXXX\\nAAAAA AAAA AB AA B\\nCCCCC;X-APPLE-ABUID=
 ab://Trent%E2%80%99s%20Work";X-APPLE-REFERENCEFRAME=1;X-TITLE=111-111 Ww 
 xxxx xxxx
aabbabc aabbabc xxxaaaccc 
hahaha eeeee UU WW PPP 
Canada:geo:11.11111,-111.111111
END:VEVENT
END:VCALENDAR
ICS;

        $mimeDir = new MimeDir();
        $vcard = $mimeDir->parse($vcard, Reader::OPTION_FIX_UNFOLDING);

        $this->assertNotNull($vcard->children()[0]->{'X-APPLE-STRUCTURED-LOCATION'}->getValue());
    }

    public function testNotFixUnfolding()
    {
        $this->expectException(ParseException::class);

        $vcard = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
DTEND;TZID=America/Edmonton:20181212T112000
LAST-MODIFIED:20181128T171443Z
UID:071597C2-0692-4B67-9C8C-3FC0FEABB9E6
DTSTAMP:20181211T181642Z
LOCATION: test
SEQUENCE:0
SUMMARY:test
DTSTART;TZID=America/Edmonton:20181212T102000
CREATED:20171107T180706Z
X-APPLE-STRUCTURED-LOCATION;VALUE=URI;X-ADDRESS=221-501 BBBBBB BBbbb\\nSs
 sssss BBBBB XXXXX\\nAAAAA AAAA AB AA B\\nCCCCC;X-APPLE-ABUID=
 ab://Trent%E2%80%99s%20Work";X-APPLE-REFERENCEFRAME=1;X-TITLE=111-111 Ww 
 xxxx xxxx
aabbabc aabbabc xxxaaaccc 
hahaha eeeee UU WW PPP 
Canada:geo:11.11111,-111.111111
END:VEVENT
END:VCALENDAR
ICS;

        (new MimeDir())->parse($vcard);
    }

    public function testNotFixUnknownProperty()
    {
        $vcard = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
DTEND;TZID=America/Edmonton:20211111T200000
X-APPLE-STRUCTURED-LOCATION;VALUE=URI;X-ADDRESS=111 11111 xxxxxxxx xx 111
 \\nLllll cccccc xx xxx xxx\\nCanada;X-APPLE-RADIUS=100;X-APPLE-REFERENCE
 FRAME=1;X-TITLE=111 11111 xxxxx xx 111:geo:11.111111,-111.111111
UID:25A069F7-5FAA-48FB-BA3E-01CAFC4A1814
DTSTAMP:20211112T020041Z
LOCATION:test
DESCRIPTION:test
SEQUENCE:0
CONFERENCE;VALUE=URI:tel://(111)%11111-1111
SUMMARY:test
LAST-MODIFIED:20211110T132821Z
CREATED:20211110T132805Z
DTSTART;TZID=America/Edmonton:20211111T190000
END:VEVENT
END:VCALENDAR
ICS;

        $vcard = (new MimeDir())->parse($vcard);

        $this->assertNotNull($vcard->children()[0]->CONFERENCE->getValue());
    }
}
