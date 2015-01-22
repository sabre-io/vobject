<?php

namespace Sabre\VObject;

class WriterTest extends \PHPUnit_Framework_TestCase {

    function getComponent() {

        $data = "BEGIN:VCALENDAR\r\nEND:VCALENDAR";
        return Reader::read($data);

    }

    function testWriteToMimeDir() {

        $result = Writer::write($this->getComponent());
        $this->assertEquals("BEGIN:VCALENDAR\r\nEND:VCALENDAR\r\n", $result);

    }

    function testWriteToMimeDirWithIterator() {

        $iterator = function() {
            for($i = 0; $i < 3; ++$i) {
                yield $this->getComponent();
            }
        };

        $result = Writer::write($iterator());
        $this->assertEquals(
            "BEGIN:VCALENDAR\r\nEND:VCALENDAR\r\n" .
            "BEGIN:VCALENDAR\r\nEND:VCALENDAR\r\n" .
            "BEGIN:VCALENDAR\r\nEND:VCALENDAR\r\n",
            $result
        );

    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testWriteToMimeDirWithUnexpectedArgument() {

        $result = Writer::write('foo');

    }

    function testWriteToJson() {

        $result = Writer::writeJson($this->getComponent());
        $this->assertEquals('["vcalendar",[],[]]', $result);

    }

    function testWriteToXml() {

        $result = Writer::writeXml($this->getComponent());
        $this->assertEquals(
            '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
            '<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">' . "\n" .
            ' <vcalendar/>' . "\n" .
            '</icalendar>' . "\n",
            $result
        );

    }

}
