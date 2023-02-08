<?php

namespace Sabre\VObject;

use PHPUnit\Framework\TestCase;

class Issue96Test extends TestCase
{
    public function testRead(): void
    {
        $input = <<<VCF
BEGIN:VCARD
VERSION:2.1
SOURCE:Yahoo Contacts (http://contacts.yahoo.com)
URL;CHARSET=utf-8;ENCODING=QUOTED-PRINTABLE:=
http://www.example.org
END:VCARD
VCF;

        $vcard = Reader::read($input, Reader::OPTION_FORGIVING);
        self::assertInstanceOf(Component\VCard::class, $vcard);
        self::assertEquals('http://www.example.org', $vcard->URL->getValue());
    }
}
