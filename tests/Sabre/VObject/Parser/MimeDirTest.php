<?php

namespace Sabre\VObject\Parser;

class MimeDirTest extends \PHPUnit_Framework_TestCase {

    function parse($input) {

        $parser = new MimeDir();
        return $parser->parse($input);

    }

    function assertParse($expected, $input) {

        $this->assertEquals(
            $expected,
            $this->parse($input)
        );

    }

    function testComponent() {

        $input = <<<ICS
BEGIN:VCALENDAR
END:VCALENDAR
ICS;

        $expected = array(
            'vcalendar',
            array(),
            array(),
        );

        $this->assertParse($expected, $input);

    }

    function testProperty() {

        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
END:VCALENDAR
ICS;

        $expected = array(
            'vcalendar',
            array(
                array(
                    'version',
                    array(),
                    null,
                    '2.0',
                ),
            ),
            array(),
        );

        $this->assertParse($expected, $input);

    }

    function testSubComponent() {

        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
END:VEVENT
END:VCALENDAR
ICS;

        $expected = array(
            'vcalendar',
            array(
                array(
                    'version',
                    array(),
                    null,
                    '2.0',
                ),
            ),
            array(
                array(
                    'vevent',
                    array(),
                    array(),
                )
            ),
        );

        $this->assertParse($expected, $input);

    }

    function testLineFolding() {

        $input = <<<ICS
BEGIN:VCALENDAR
V
 ERSION:2.0
BEGIN:V
 EVENT
END:VE
\tVENT
END:VCALENDAR
ICS;

        $expected = array(
            'vcalendar',
            array(
                array(
                    'version',
                    array(),
                    null,
                    '2.0',
                ),
            ),
            array(
                array(
                    'vevent',
                    array(),
                    array(),
                )
            ),
        );

        $this->assertParse($expected, $input);

    }

    function testParameters() {

        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
DTSTART;TZID="Europe/London";VALUE=DATE-TIME:20130513T2058
END:VEVENT
END:VCALENDAR
ICS;

        $expected = array(
            'vcalendar',
            array(
                array(
                    'version',
                    array(),
                    null,
                    '2.0',
                ),
            ),
            array(
                array(
                    'vevent',
                    array(
                        array(
                            'dtstart',
                            array(
                                'tzid' => 'Europe/London',
                                'value' => 'DATE-TIME',
                            ),
                            null,
                            '20130513T2058'
                        )
                    ),
                    array(),
                )
            ),
        );

        $this->assertParse($expected, $input);

    }

    function testRepeatingParameters() {

        $input = <<<ICS
BEGIN:VCARD
VERSION:4.0
TEL;TYPE=PREF;TYPE=VOICE:123
TEL;TYPE=PREF,FAX:123
TEL;TYPE="PREF","PAGER":123
END:VCARD
ICS;

        $expected = array(
            'vcard',
            array(
                array(
                    'version',
                    array(),
                    null,
                    '4.0',
                ),
                array(
                    'tel',
                    array(
                        'type' => array('PREF', 'VOICE'),
                    ),
                    null,
                    '123',
                ),
                array(
                    'tel',
                    array(
                        'type' => array('PREF', 'FAX'),
                    ),
                    null,
                    '123',
                ),
                array(
                    'tel',
                    array(
                        'type' => array('PREF', 'PAGER'),
                    ),
                    null,
                    '123',
                ),
            ),
            array(),
        );

        $this->assertParse($expected, $input);

    }
}
