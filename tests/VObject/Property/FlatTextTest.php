<?php

namespace Sabre\VObject\Property;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Reader;

class FlatTextTest extends TestCase
{
    public function testUnescapedCommaDoesNotSplitTheValue(): void
    {
        $vcard = Reader::read("BEGIN:VCARD\r\nVERSION:4.0\r\nNOTE:Line 1\\nLine 2, and more\r\nEND:VCARD\r\n");

        self::assertEquals(['Line 1'."\n".'Line 2, and more'], $vcard->NOTE->getParts());
        self::assertEquals('Line 1'."\n".'Line 2, and more', $vcard->NOTE->getValue());
    }

    public function testEscapedCommaIsUnescaped(): void
    {
        $vcard = Reader::read("BEGIN:VCARD\r\nVERSION:4.0\r\nNOTE:Line 1\\nLine 2\\, and more\r\nEND:VCARD\r\n");

        self::assertEquals('Line 1'."\n".'Line 2, and more', $vcard->NOTE->getValue());
    }

    public function testICalendarText(): void
    {
        $vcal = Reader::read("BEGIN:VCALENDAR\r\nBEGIN:VEVENT\r\nDESCRIPTION:Agenda:\\n1. Budget, staffing\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n");

        self::assertEquals('Agenda:'."\n".'1. Budget, staffing', $vcal->VEVENT->DESCRIPTION->getValue());
    }

    public function testJCardHasASingleValue(): void
    {
        $vcard = Reader::read("BEGIN:VCARD\r\nVERSION:4.0\r\nFN:Doe, Jane\r\nEND:VCARD\r\n");

        self::assertEquals(['fn', new \stdClass(), 'text', 'Doe, Jane'], $vcard->FN->jsonSerialize());
    }

    public function testUnescapedCommaIsEscapedOnWrite(): void
    {
        $vcard = Reader::read("BEGIN:VCARD\r\nVERSION:4.0\r\nFN:Doe, Jane\r\nEND:VCARD\r\n");

        self::assertEquals("BEGIN:VCARD\r\nVERSION:4.0\r\nFN:Doe\\, Jane\r\nEND:VCARD\r\n", $vcard->serialize());
    }
}
