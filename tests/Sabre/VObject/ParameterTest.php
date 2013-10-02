<?php

namespace Sabre\VObject;

class ParameterTest extends \PHPUnit_Framework_TestCase {

    function testSetup() {

        $param = new Parameter('name','value');
        $this->assertEquals('NAME',$param->name);
        $this->assertEquals('value',$param->value);
        $this->assertEquals('value',$param->getValue());

    }

    function testCastToString() {

        $param = new Parameter('name','value');
        $this->assertEquals('value',$param->__toString());
        $this->assertEquals('value',(string)$param);

    }

    function testSerialize() {

        $param = new Parameter('name','value');
        $this->assertEquals('NAME=value',$param->serialize());

    }

    function testSerializeEmpty() {

        $param = new Parameter('name',null);
        $this->assertEquals('NAME',$param->serialize());

    }

    function testSerializeColon() {

        $param = new Parameter('name','va:lue');
        $this->assertEquals('NAME="va:lue"',$param->serialize());

    }
}
