<?php

namespace Sabre\VObject\Parser;

use
    Sabre\VObject;

class JsonTest extends \PHPUnit_Framework_TestCase {

    function testRoundTrip() {

        $input = array(
            "vcard",
            array(
                array(
                    "version",
                    new \StdClass(),
                    "text",
                    "4.0"
                ),
                array(
                    "prodid",
                    new \StdClass(),
                    "text",
                    "-//Sabre//Sabre VObject " . VObject\Version::VERSION . "//EN",
                ),
                array(
                    "uid",
                    new \StdClass(),
                    "text",
                    "foo",
                ),
                array(
                    "bday",
                    new \StdClass(),
                    "date-and-or-time",
                    "1985-04-07",
                ),
                array(
                    "rev",
                    new \StdClass(),
                    "timestamp",
                    "1995-10-31T22:27:10Z",
                ),
                array(
                    "lang",
                    new \StdClass(),
                    "language-tag",
                    "nl",
                ),
                array(
                    "n",
                    new \StdClass(),
                    "text",
                    array("Last", "First", "Middle", "", ""),
                ),
                array(
                    "tel",
                    (object)array(
                        "group" => "item1",
                    ),
                    "text",
                    "+1 555 123456",
                ),
                array(
                    "x-ab-label",
                    (object)array(
                        "group" => "item1",
                    ),
                    "unknown",
                    "Walkie Talkie",
                ),
                array(
                    "adr",
                    new \StdClass(),
                    "text",
                        array(
                            "",
                            "",
                            array("My Street", "Left Side", "Second Shack"),
                            "Hometown",
                            "PA",
                            "18252",
                            "U.S.A",
                        ),
                ),
                array(
                    "bday",
                    (object)array(
                        'x-param' => array(1,2),
                    ),
                    "date",
                    "1979-12-25",
                ),
                array(
                    "bday",
                    new \StdClass(),
                    "date-time",
                    "1979-12-25T02:00:00",
                ),
                array(
                    "x-truncated",
                    new \StdClass(),
                    "date",
                    "--12-25",
                ),
                array(
                    "x-time-local",
                    new \StdClass(),
                    "time",
                    "12:30:00"
                ),
                array(
                    "x-time-utc",
                    new \StdClass(),
                    "time",
                    "12:30:00Z"
                ),
                array(
                    "x-time-offset",
                    new \StdClass(),
                    "time",
                    "12:30:00-08:00"
                ),
                array(
                    "x-time-reduced",
                    new \StdClass(),
                    "time",
                    "23"
                ),
                array(
                    "x-time-truncated",
                    new \StdClass(),
                    "time",
                    "--30"
                ),
                array(
                    "x-karma-points",
                    new \StdClass(),
                    "integer",
                    42
                ),
                array(
                    "x-grade",
                    new \StdClass(),
                    "float",
                    1.3
                ),
                array(
                    "tz",
                    new \StdClass(),
                    "utc-offset",
                    "-05:00",
                ),
            ),
        );

        $parser = new Json(json_encode($input));
        $vobj = $parser->parse();        

        $result = $vobj->serialize();
        $expected = <<<VCF
BEGIN:VCARD
VERSION:4.0
PRODID:-//Sabre//Sabre VObject 3.0.1//EN
UID:foo
BDAY:1985-04-07
REV:1995-10-31T22:27:10Z
LANG:nl
N:Last;First;Middle;;
item1.TEL:+1 555 123456
item1.X-AB-LABEL:Walkie Talkie
ADR:;;My Street,Left Side,Second Shack;Hometown;PA;18252;U.S.A
BDAY;X-PARAM=1,2;VALUE=DATE:1979-12-25
BDAY;VALUE=DATE-TIME:1979-12-25T02:00:00
X-TRUNCATED;VALUE=DATE:--12-25
X-TIME-LOCAL;VALUE=TIME:12:30:00
X-TIME-UTC;VALUE=TIME:12:30:00Z
X-TIME-OFFSET;VALUE=TIME:12:30:00-08:00
X-TIME-REDUCED;VALUE=TIME:23
X-TIME-TRUNCATED;VALUE=TIME:--30
X-KARMA-POINTS;VALUE=INTEGER:42
X-GRADE;VALUE=FLOAT:1.3
TZ;VALUE=UTC-OFFSET:-05:00
END:VCARD

VCF;
        $this->assertEquals($expected, str_replace("\r", "", $result));

        $this->assertEquals(
            $input,
            $vobj->jsonSerialize()
        );

    }

}
