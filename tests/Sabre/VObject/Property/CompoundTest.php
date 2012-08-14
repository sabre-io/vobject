<?php

namespace Sabre\VObject\Property;
use Sabre\VObject\Component;

class CompoundTest extends \PHPUnit_Framework_TestCase {

    function testSetArray() {

        $arr = array(
            'ABC, Inc.',
            'North American Division',
            'Marketing;Sales',
        );

        $elem = new Compound('ORG');
        $elem->setArray($arr);

        $this->assertEquals('ABC\, Inc.;North American Division;Marketing\;Sales', $elem->value);
        $this->assertEquals(3, count($elem->getArray()));
        $parts = $elem->getArray();
        $this->assertEquals('Marketing;Sales', $parts[2]);

    }

    function testGetArray() {

        $str = 'ABC\, Inc.;North American Division;Marketing\;Sales';

        $elem = new Compound('ORG', $str);
        $arr = $elem->getArray();

        $this->assertEquals(3, count($elem->getArray()));
        $parts = $elem->getArray();
        $this->assertEquals('Marketing;Sales', $parts[2]);
    }

}
