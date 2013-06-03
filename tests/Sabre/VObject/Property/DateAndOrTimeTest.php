<?php

namespace Sabre\VObject\Property;

use Sabre\VObject;

class DateAndOrTimeTest extends \PHPUnit_Framework_TestCase {

    /**
     * @dataProvider dates
     */
    function testGetJsonValue($input, $output) {

        $vcard = new VObject\Component\VCard();
        $prop = $vcard->createProperty('BDAY', $input);

        $this->assertEquals($output, $prop->getJsonValue()[0]);

    }

    function dates() {

        return array(
            array(
                "19961022T140000",
                "1996-10-22T14:00:00",
            ),
            array(
                "--1022T1400",
                "--10-22T14:00",
            ),
            array(
                "---22T14",
                "---22T14",
            ),
            array(
                "19850412",
                "1985-04-12",
            ),
            array(
                "1985-04",
                "1985-04",
            ),
            array(
                "1985",
                "1985",
            ),
            array(
                "--0412",
                "--04-12",
            ),
            array(
                "T102200",
                "T10:22:00",
            ),
            array(
                "T1022",
                "T10:22",
            ),
            array(
                "T10",
                "T10",
            ),
            array(
                "T-2200",
                "T-22:00",
            ),
            array(
                "T102200Z",
                "T10:22:00Z",
            ),
            array(
                "T102200-0800",
                "T10:22:00-0800",
            ),
            array(
                "T--00",
                "T--00",
            ),
        );

    }

}

