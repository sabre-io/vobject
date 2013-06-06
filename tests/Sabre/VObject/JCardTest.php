<?php

namespace Sabre\VObject;

class JCardTest extends \PHPUnit_Framework_TestCase {

    function testToJCard() {

        $card = new Component\VCard(array(
            "VERSION" => "4.0",
            "UID" => "foo",
            "BDAY" => "19850407",
            "REV"  => "19951031T222710Z",
            "LANG" => "nl",
            "N" => array("Last", "First", "Middle", "", ""),
        ));

        $expected = array(
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
                    "-//Sabre//Sabre VObject " . Version::VERSION . "//EN",
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
            ),
        );

        $this->assertEquals($expected, $card->jsonSerialize());

    }

}
