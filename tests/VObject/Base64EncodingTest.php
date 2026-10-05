<?php

namespace Sabre\VObject;

use PHPUnit\Framework\TestCase;

class Base64EncodingTest extends TestCase
{
    public function testKeyInVCard30(): void
    {
        $input = "BEGIN:VCARD\r\nVERSION:3.0\r\nKEY;ENCODING=b;TYPE=PGP:LS0tLS1CRUdJTg==\r\nEND:VCARD\r\n";
        $vcard = Reader::read($input);

        self::assertInstanceOf(Property\Binary::class, $vcard->KEY);
        self::assertEquals('-----BEGIN', $vcard->KEY->getValue());
        self::assertEquals($input, $vcard->serialize());
    }

    public function testSoundInVCard21(): void
    {
        $vcard = Reader::read("BEGIN:VCARD\r\nVERSION:2.1\r\nSOUND;ENCODING=BASE64;TYPE=WAVE:UklGRg==\r\nEND:VCARD\r\n");

        self::assertInstanceOf(Property\Binary::class, $vcard->SOUND);
        self::assertEquals('RIFF', $vcard->SOUND->getValue());
    }

    public function testConversionToVCard40GivesADataUri(): void
    {
        $vcard = Reader::read("BEGIN:VCARD\r\nVERSION:3.0\r\nKEY;ENCODING=b;TYPE=PGP:LS0tLS1CRUdJTg==\r\nEND:VCARD\r\n");

        $converted = $vcard->convert(Document::VCARD40);

        self::assertStringStartsWith('data:', (string) $converted->KEY);
        self::assertStringEndsWith(';base64,LS0tLS1CRUdJTg==', (string) $converted->KEY);
    }

    public function testValueParameterComesFirst(): void
    {
        $vcard = Reader::read("BEGIN:VCARD\r\nVERSION:3.0\r\nKEY;ENCODING=b;VALUE=text:LS0tLS1CRUdJTg==\r\nEND:VCARD\r\n");

        self::assertInstanceOf(Property\Text::class, $vcard->KEY);
    }
}
