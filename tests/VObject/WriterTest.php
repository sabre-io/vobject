<?php

namespace Sabre\VObject;

use PHPUnit\Framework\TestCase;

class WriterTest extends TestCase
{
    /**
     * @return Document<int, mixed>|null
     */
    public function getComponent(): ?Document
    {
        $data = "BEGIN:VCALENDAR\r\nEND:VCALENDAR";

        return Reader::read($data);
    }

    public function testWriteToMimeDir(): void
    {
        $result = Writer::write($this->getComponent());
        self::assertEquals("BEGIN:VCALENDAR\r\nEND:VCALENDAR\r\n", $result);
    }

    public function testWriteToJson(): void
    {
        $result = Writer::writeJson($this->getComponent());
        self::assertEquals('["vcalendar",[],[]]', $result);
    }

    public function testWriteToXml(): void
    {
        $result = Writer::writeXml($this->getComponent());
        self::assertEquals(
            '<?xml version="1.0" encoding="UTF-8"?>'."\n".
            '<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">'."\n".
            ' <vcalendar/>'."\n".
            '</icalendar>'."\n",
            $result
        );
    }
}
