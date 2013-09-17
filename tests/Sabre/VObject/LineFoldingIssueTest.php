<?php

namespace Sabre\VObject;

class LineFoldingIssueTest extends \PHPUnit_Framework_TestCase {

    function testRead() {

        $event = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
DESCRIPTION:TEST\n\n \n\nTEST\n\n \
 n\nTEST\n\n \n\nTEST\n\nTEST
  TEST\nTEST\, TEST
END:VEVENT
END:VCALENDAR
ICS;
        $obj = Reader::read($event);
        $this->assertEquals($event, $obj->serialize());

    }

}
