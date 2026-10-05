<?php

namespace Sabre\VObject\Property;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCard;
use Sabre\VObject\Reader;

class UnknownTest extends TestCase
{
    public function testMimeDirRoundTripKeepsTheValueUnprocessed(): void
    {
        $input = "BEGIN:VCARD\r\n"
            ."VERSION:3.0\r\n"
            ."X-ABUID:7EC63789-9F24-4F95-AF74-A85483437BC8\\:ABPerson\r\n"
            ."X-ANDROID-CUSTOM:vnd.android.cursor.item/nickname\;Pseudo;1\r\n"
            ."X-COFFEE-DATA:Stenophylla;Guinea\\,Africa\r\n"
            ."END:VCARD\r\n";

        self::assertEquals($input, Reader::read($input)->serialize());
    }

    public function testGetValueIsStillUnescaped(): void
    {
        $vcard = Reader::read("BEGIN:VCARD\r\nVERSION:3.0\r\nX-FOO:a\\,b,c\r\nEND:VCARD\r\n");

        self::assertEquals(['a,b', 'c'], $vcard->{'X-FOO'}->getParts());
    }

    /**
     * Example of RFC 7095, section 5.3.
     */
    public function testJCardValueIsTheUnprocessedValue(): void
    {
        $vcard = Reader::read("BEGIN:VCARD\r\nVERSION:4.0\r\nX-COFFEE-DATA:Stenophylla;Guinea\\,Africa\r\nEND:VCARD\r\n");

        self::assertEquals(
            ['x-coffee-data', new \stdClass(), 'unknown', 'Stenophylla;Guinea\\,Africa'],
            $vcard->{'X-COFFEE-DATA'}->jsonSerialize()
        );
    }

    /**
     * Example of RFC 7095, section 5.3.
     */
    public function testJCardValueIsWrittenUnprocessed(): void
    {
        $vcard = Reader::readJson([
            'vcard',
            [
                ['version', new \stdClass(), 'text', '4.0'],
                ['x-coffee-data', new \stdClass(), 'unknown', 'Stenophylla;Guinea\\,Africa'],
            ],
        ]);

        self::assertEquals(
            "BEGIN:VCARD\r\nVERSION:4.0\r\nX-COFFEE-DATA:Stenophylla;Guinea\\,Africa\r\nEND:VCARD\r\n",
            $vcard->serialize()
        );
    }

    public function testUnknownValueTypeIsNotWrittenAsAValueParameter(): void
    {
        $vcard = Reader::readJson([
            'vcard',
            [
                ['version', new \stdClass(), 'text', '4.0'],
                ['bday', new \stdClass(), 'unknown', 'not a date'],
            ],
        ]);

        self::assertEquals("BEGIN:VCARD\r\nVERSION:4.0\r\nBDAY:not a date\r\nEND:VCARD\r\n", $vcard->serialize());
    }

    public function testQuotedPrintableValueIsNotSplit(): void
    {
        $vcard = Reader::read("BEGIN:VCARD\r\nVERSION:2.1\r\nX-ANDROID-CUSTOM;ENCODING=QUOTED-PRINTABLE:vnd.a;=32=30;0\r\nEND:VCARD\r\n");

        self::assertEquals('vnd.a;20;0', $vcard->{'X-ANDROID-CUSTOM'}->getRawMimeDirValue());
        self::assertEquals('vnd.a;20;0', $vcard->{'X-ANDROID-CUSTOM'}->getJsonValue()[0]);
    }

    public function testSettingTheValueDropsTheUnprocessedValue(): void
    {
        $vcard = Reader::read("BEGIN:VCARD\r\nVERSION:4.0\r\nX-FOO:a\;b\r\nEND:VCARD\r\n");

        $vcard->{'X-FOO'}->setValue('c;d');
        self::assertEquals('c\;d', $vcard->{'X-FOO'}->getRawMimeDirValue());

        $vcard->{'X-FOO'}->setParts(['e', 'f']);
        self::assertEquals('e,f', $vcard->{'X-FOO'}->getRawMimeDirValue());
    }

    public function testAnUnknownPropertyCreatedFromScratchIsEscaped(): void
    {
        $vcard = new VCard(['VERSION' => '4.0'], false);
        $vcard->add('X-FOO', 'a;b');

        self::assertEquals("BEGIN:VCARD\r\nVERSION:4.0\r\nX-FOO:a\;b\r\nEND:VCARD\r\n", $vcard->serialize());
    }
}
