<?php

namespace Sabre\VObject;

use PHPUnit\Framework\TestCase;

class LineFoldingIssueTest extends TestCase
{
    public function testRead(): void
    {
        $event = <<<ICS
BEGIN:VCALENDAR\r
BEGIN:VEVENT\r
DESCRIPTION:TEST\\n\\n \\n\\nTEST\\n\\n \\n\\nTEST\\n\\n \\n\\nTEST\\n\\nTEST\\nTEST\\, TEST\r
END:VEVENT\r
END:VCALENDAR\r

ICS;

        $expected = <<<ICS
BEGIN:VCALENDAR\r
BEGIN:VEVENT\r
DESCRIPTION:TEST\\n\\n \\n\\nTEST\\n\\n \\n\\nTEST\\n\\n \\n\\nTEST\\n\\nTEST\\nTEST\\, TES\r
 T\r
END:VEVENT\r
END:VCALENDAR\r

ICS;

        $obj = Reader::read($event);
        self::assertEquals("TEST\n\n \n\nTEST\n\n \n\nTEST\n\n \n\nTEST\n\nTEST\nTEST, TEST", $obj->VEVENT->DESCRIPTION->getValue());
        self::assertEquals($expected, $obj->serialize());
    }
}
