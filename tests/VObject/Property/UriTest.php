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

    public function testNoEscapeForDataValue()
    {
        $input = <<<VCARD_WRAP
        BEGIN:VCARD
        VERSION:4.0
        PHOTO;VALUE=URI:data:image/jpeg;base64,MIICajCCAd
        END:VCARD
        VCARD_WRAP;
        $vObject = Reader::read($input);
        self::assertStringContainsString('data:image/jpeg;base64,MIICajCCAd',
            $vObject->PHOTO->serialize(),
            'Comma is not escaped');
    }
}
