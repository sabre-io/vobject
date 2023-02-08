<?php

namespace Sabre\VObject;

use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase
{
    public function testGetDocumentType(): void
    {
        $doc = new MockDocument('WHATEVER');
        self::assertEquals(Document::UNKNOWN, $doc->getDocumentType());
    }

    public function testConstruct(): void
    {
        $doc = new MockDocument('VLIST');
        self::assertEquals('VLIST', $doc->name);
    }

    public function testCreateComponent(): void
    {
        $vcal = new Component\VCalendar([], false);

        $event = $vcal->createComponent('VEVENT');

        self::assertInstanceOf(Component\VEvent::class, $event);
        $vcal->add($event);

        $prop = $vcal->createProperty('X-PROP', '1234256', ['X-PARAM' => '3']);
        self::assertInstanceOf(Property::class, $prop);

        $event->add($prop);

        unset(
            $event->DTSTAMP,
            $event->UID
        );

        $out = $vcal->serialize();
        self::assertEquals("BEGIN:VCALENDAR\r\nBEGIN:VEVENT\r\nX-PROP;X-PARAM=3:1234256\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n", $out);
    }

    public function testCreate(): void
    {
        $vcal = new Component\VCalendar([], false);

        $event = $vcal->create('VEVENT');
        self::assertInstanceOf(Component\VEvent::class, $event);

        $prop = $vcal->create('CALSCALE');
        self::assertInstanceOf(Property\Text::class, $prop);
    }

    public function testGetClassNameForPropertyValue(): void
    {
        $vcal = new Component\VCalendar([], false);
        self::assertEquals(Property\Text::class, $vcal->getClassNameForPropertyValue('TEXT'));
        self::assertNull($vcal->getClassNameForPropertyValue('FOO'));
    }

    /**
     * @throws InvalidDataException
     */
    public function testDestroy(): void
    {
        $vcal = new Component\VCalendar([], false);
        $event = $vcal->createComponent('VEVENT');

        self::assertInstanceOf(Component\VEvent::class, $event);
        $vcal->add($event);

        $prop = $vcal->createProperty('X-PROP', '1234256', ['X-PARAM' => '3']);

        $event->add($prop);

        self::assertEquals($event, $prop->parent);

        $vcal->destroy();

        self::assertNull($prop->parent);
    }
}

class MockDocument extends Document
{
}
