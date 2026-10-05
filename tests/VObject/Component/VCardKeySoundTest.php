<?php

namespace Sabre\VObject\Component;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Document;
use Sabre\VObject\Property;
use Sabre\VObject\Reader;

class VCardKeySoundTest extends TestCase
{
    public function testKeyAndSoundAreUrisInVCard40(): void
    {
        $vcard = Reader::read("BEGIN:VCARD\r\nVERSION:4.0\r\nKEY:https://example.com/key.asc\r\nSOUND:https://example.com/name.wav\r\nEND:VCARD\r\n");

        self::assertInstanceOf(Property\Uri::class, $vcard->KEY);
        self::assertInstanceOf(Property\Uri::class, $vcard->SOUND);
        self::assertEquals(['key', new \stdClass(), 'uri', 'https://example.com/key.asc'], $vcard->KEY->jsonSerialize());
    }

    public function testATextKeyStaysText(): void
    {
        $vcard = Reader::read("BEGIN:VCARD\r\nVERSION:4.0\r\nKEY;VALUE=text:-----BEGIN PGP PUBLIC KEY BLOCK-----\r\nEND:VCARD\r\n");

        self::assertInstanceOf(Property\Text::class, $vcard->KEY);
    }

    public function testVCard30IsUnchanged(): void
    {
        $vcard = Reader::read("BEGIN:VCARD\r\nVERSION:3.0\r\nKEY:https://example.com/key.asc\r\nEND:VCARD\r\n");

        self::assertInstanceOf(Property\FlatText::class, $vcard->KEY);
    }

    public function testSoundDataUriConvertsToBinaryInVCard30(): void
    {
        $vcard = Reader::read("BEGIN:VCARD\r\nVERSION:4.0\r\nSOUND:data:audio/wav;base64,UklGRg==\r\nEND:VCARD\r\n");

        $converted = $vcard->convert(Document::VCARD30);

        self::assertInstanceOf(Property\Binary::class, $converted->SOUND);
        self::assertEquals('RIFF', $converted->SOUND->getValue());
    }
}
