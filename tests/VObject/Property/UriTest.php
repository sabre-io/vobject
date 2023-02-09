<?php

namespace Sabre\VObject\Property;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Reader;

class UriTest extends TestCase
{
    public function testAlwaysEncodeUriVCalendar(): void
    {
        // Apple iCal has issues with URL properties that don't have
        // VALUE=URI specified. We added a workaround to vobject that
        // ensures VALUE=URI always appears for these.
        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
URL:http://example.org/
END:VEVENT
END:VCALENDAR
ICS;
        $output = Reader::read($input)->serialize();
        self::assertStringContainsString('URL;VALUE=URI:http://example.org/', $output);
    }

    public function testUriUnescapedProperly(): void
    {
        // The colon should normally not be escaped in URL, but Google Contacts does it and
        // vobject contains a workaround for it
        $input = <<<VCF
BEGIN:VCARD
VERSION:4.0
PRODID:-//Thunderbird.net/NONSGML Thunderbird CardBook V83.6//EN-GB
UID:5cdac90f-cb4c-4c1c-ad66-fe8591bdf3a2
FN:vCard 4 Contact with image
EMAIL:bbb@example.org
REV:20221222T213003Z
PHOTO:data:image/JPEG\;base64\,/9j/4AAQSkZJRgABAQAAYABgAAD/2wBDAAgGBgcGBQgHB
 wcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/
 wAALCAABAAEBAREA/8QAFAABAAAAAAAAAAAAAAAAAAAAAv/EABQQAQAAAAAAAAAAAAAAAAAAAAD
 /2gAIAQEAAD8AL//Z
item1.URL:http\://www.example.com/hello?world
item1.X-ABLabel:
END:VCARD
VCF;
        $output = Reader::read($input);
        $this->assertSame('http://www.example.com/hello?world', (string) $output->URL);
        $this->assertSame('data:image/JPEG;base64,/9j/4AAQSkZJRgABAQAAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/wAALCAABAAEBAREA/8QAFAABAAAAAAAAAAAAAAAAAAAAAv/EABQQAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEAAD8AL//Z', (string) $output->PHOTO);
    }
}
