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
            "item1.TEL" => "+1 555 123456",
            "item1.X-AB-LABEL" => "Walkie Talkie",
        ));

        $card->add('BDAY', '1979-12-25', array('VALUE' => 'DATE'));

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
                    "bday",
                    new \StdClass(),
                    "date",
                    "1979-12-25",
                ),

            ),
        );

        $this->assertEquals($expected, $card->jsonSerialize());

    }

}
