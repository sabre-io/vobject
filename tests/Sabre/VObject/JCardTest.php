<?php

namespace Sabre\VObject;

class JCardTest extends \PHPUnit_Framework_TestCase {

    function testToJCard() {

        $card = new Component\VCard(array(
            "VERSION" => "4.0",
            "UID" => "foo",
            "BDAY" => "19850407",
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
                    "date",
                    "1985-04-07",
                ),
            ),
        );

        $this->assertEquals($expected, $card->jsonSerialize());

    }

}
