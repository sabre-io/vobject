<?php

namespace Sabre\VObject;

class ParameterTest extends \PHPUnit_Framework_TestCase {

    function testSetup() {

        $cal = new Component\VCalendar();

        $param = $cal->createParameter('name','value');
        $this->assertEquals('NAME',$param->name);
        $this->assertEquals('value',$param->getValue());

    }

    function testModify() {

        $cal = new Component\VCalendar();

        $param = $cal->createParameter('name',null);
        $param->addValue(1);
        $this->assertEquals(array(1), $param->getParts());

        $param->setParts(array(1,2));
        $this->assertEquals(array(1,2), $param->getParts());

        $param->addValue(3);
        $this->assertEquals(array(1,2,3), $param->getParts());

        $param->setValue(4);
        $param->addValue(5);
        $this->assertEquals(array(4,5), $param->getParts());

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

    function testSerializeComplex() {

        $cal = new Component\VCalendar();
        $param = $cal->createParameter('name',array("val1", "val2;", "val3^", "val4\n", "val5\""));
        $this->assertEquals('NAME=val1,"val2;","val3^^","val4^n","val5^\'"',$param->serialize());

    }
}
