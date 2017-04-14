<?php

namespace Sabre\VObject;

class CollectionTest extends \PHPUnit_Framework_TestCase {

    /**
     * @expectedException InvalidArgumentException
     */
    function testInvalidType() {

        $collection = new Collection('StdClass');

    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testExpectVCardGiveStdClass() {

        $collection = new Collection('Sabre\VObject\Component\VCard');
        $collection[] = new \StdClass();

    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testExpectVCardGiveVCalendar() {

        $collection = new Collection('Sabre\VObject\Component\VCard');
        $collection[] = new Component\VCalendar([], false);

    }

    function testCountEmpty() {

        $collection = new Collection('Sabre\VObject\Component\VCard');

        $this->assertEquals(0, count($collection));

    }

    function testCount() {

        $collection = new Collection('Sabre\VObject\Component\VCard');
        $collection[] = $this->newVCard();
        $collection[] = $this->newVCard();
        $collection[] = $this->newVCard();

        $this->assertEquals(3, count($collection));

    }

    function testIterator() {

        $collection = new Collection('Sabre\VObject\Component\VCard');
        $handle = [];
        $collection[] = $handle[] = $this->newVCard();
        $collection[] = $handle[] = $this->newVCard();
        $collection[] = $handle[] = $this->newVCard();

        $this->assertEquals(
            iterator_to_array($collection),
            $handle
        );

    }

    function testOffsetExists() {

        $collection = new Collection('Sabre\VObject\Component\VCard');
        $collection['a'] = $this->newVCard();

        $this->assertFalse(isset($collection[0]));
        $this->assertTrue(isset($collection['a']));
    }

    function testOffsetGet() {

        $collection = new Collection('Sabre\VObject\Component\VCard');
        $collection['a'] = $vcard = $this->newVCard();

        $this->assertEquals($collection['a'], $vcard);
        $this->assertEquals($collection[0], null);
    }

    function testOffsetUnset() {

        $collection = new Collection('Sabre\VObject\Component\VCard');
        $collection['a'] = $this->newVCard();

        $this->assertTrue(isset($collection['a']));
        $this->assertFalse(isset($collection[0]));

        unset($collection['a']);
        unset($collection[0]);

        $this->assertFalse(isset($collection['a']));
        $this->assertFalse(isset($collection[0]));
    }

    protected function newVCard() {

        return new Component\VCard([], false);

    }

}
