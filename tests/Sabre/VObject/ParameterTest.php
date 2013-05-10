<?php

namespace Sabre\VObject;

class ParameterTest extends \PHPUnit_Framework_TestCase {

    function testSetup() {

        $cal = new Component\VCalendar();

        $param = $cal->createParameter('name','value');
        $this->assertEquals('NAME',$param->name);
        $this->assertEquals('value',$param->getValue());

    }

    function testCastToString() {

        $cal = new Component\VCalendar();
        $param = $cal->createParameter('name','value');
        $this->assertEquals('value',$param->__toString());
        $this->assertEquals('value',(string)$param);

    }

    function testSerialize() {

        $cal = new Component\VCalendar();
        $param = $cal->createParameter('name','value');
        $this->assertEquals('NAME=value',$param->serialize());

    }

    function testSerializeEmpty() {

        $cal = new Component\VCalendar();
        $param = $cal->createParameter('name',null);
        $this->assertEquals('NAME',$param->serialize());

    }
}
