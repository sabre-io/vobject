<?php

namespace Sabre\VObject;

class JCalTest extends \PHPUnit_Framework_TestCase {

    function testToJCal() {

        $cal = new Component\VCalendar();

        $event = $cal->add('VEVENT', array(
            "UID" => "foo",
            "DTSTART" => new \DateTime("2013-05-26 18:10:00Z"),
            "DURATION" => "P1D",
            "CATEGORIES" => array('home', 'testing'),
            "CREATED" => new \DateTime("2013-05-26 18:10:00Z"),
        ));

        // Modifying DTSTART to be a date-only.
        $event->dtstart['VALUE'] = 'DATE';

        $expected = array(
            "vcalendar",
            array(
                array(
                    "version",
                    new \StdClass(),
                    "text",
                    "2.0"
                ),
                array(
                    "prodid",
                    new \StdClass(),
                    "text",
                    "-//Sabre//Sabre VObject " . Version::VERSION . "//EN",
                ),
                array(
                    "calscale",
                    new \StdClass(),
                    "text",
                    "GREGORIAN"
                ),
            ),
            array(
                array("vevent",
                    array(
                        array(
                            "uid", new \StdClass(), "text", "foo",
                        ),
                        array(
                            "dtstart", new \StdClass(), "date", "2013-05-26",
                        ),
                        array(
                            "duration", new \StdClass(), "duration", "P1D",
                        ),
                        array(
                            "categories", new \StdClass(), "text", "home", "testing",
                        ),
                        array(
                            "created", new \StdClass(), "date-time", "2013-05-26T18:10:00Z",
                        ),
                    ),
                    array(),
                )
            ),
        );

        $this->assertEquals($expected, $cal->jsonSerialize());

    }

}
