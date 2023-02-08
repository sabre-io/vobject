<?php

namespace Sabre\VObject\Property\VCard;

use PHPUnit\Framework\TestCase;
use Sabre\VObject;

class PhoneNumberTest extends TestCase
{
    public function testParser(): void
    {
        $input = "BEGIN:VCARD\r\nVERSION:3.0\r\nTEL;TYPE=HOME;VALUE=PHONE-NUMBER:+1234\r\nEND:VCARD\r\n";

        $vCard = VObject\Reader::read($input);
        self::assertInstanceOf(PhoneNumber::class, $vCard->TEL);
        self::assertEquals('PHONE-NUMBER', $vCard->TEL->getValueType());
        self::assertEquals($input, $vCard->serialize());
    }
}
