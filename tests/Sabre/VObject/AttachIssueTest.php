<?php

namespace Sabre\VObject;

class AttachIssueTest extends \PHPUnit_Framework_TestCase {

    function testRead() {

        $event = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
ATTACH;FMTTYPE=;ENCODING=:about:blank
END:VEVENT
END:VCALENDAR
ICS;
        $obj = Reader::read($event);
        $this->assertEquals($event, $obj->serialize());

    }

}
