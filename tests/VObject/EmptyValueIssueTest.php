<?php

namespace Sabre\VObject;

use PHPUnit\Framework\TestCase;

/**
 * This test is written for Issue 68:.
 *
 * https://github.com/fruux/sabre-vobject/issues/68
 */
class EmptyValueIssueTest extends TestCase
{
    public function testDecodeValue(): void
    {
        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
DESCRIPTION:This is a description\\nwith a linebreak and a \\; \\, and :
END:VEVENT
END:VCALENDAR
ICS;

        $vobj = Reader::read($input);

        // Before this bug was fixed, getValue() would return nothing.
        self::assertEquals("This is a description\nwith a linebreak and a ; , and :", $vobj->VEVENT->DESCRIPTION->getValue());
    }
}
