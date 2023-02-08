<?php

namespace Sabre\VObject\Property;

use PHPUnit\Framework\TestCase;
use Sabre\VObject;

class BooleanTest extends TestCase
{
    /**
     * @throws VObject\ParseException
     */
    public function testMimeDir(): void
    {
        $input = "BEGIN:VCARD\r\nX-AWESOME;VALUE=BOOLEAN:TRUE\r\nX-SUCKS;VALUE=BOOLEAN:FALSE\r\nEND:VCARD\r\n";

        $vcard = VObject\Reader::read($input);
        self::assertTrue($vcard->{'X-AWESOME'}->getValue());
        self::assertFalse($vcard->{'X-SUCKS'}->getValue());

        self::assertEquals('BOOLEAN', $vcard->{'X-AWESOME'}->getValueType());
        self::assertEquals($input, $vcard->serialize());
    }
}
