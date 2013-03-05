<?php

namespace Sabre\VObject\Property;

use
    Sabre\VObject\Parameter;

class TextTest extends \PHPUnit_Framework_TestCase {

    public function testSerialize() {

        $property = new Text('propname','propvalue');

        $this->assertEquals("PROPNAME:propvalue\r\n",$property->serialize());

    }

    public function testSerializeEscape() {

        $property = new Text('propname','propvalue\\;\\');

        $this->assertEquals("PROPNAME:propvalue\\\\\\;\\\\\r\n",$property->serialize());

    }

    public function testSerializeParam() {

        $property = new Text('propname','propvalue');
        $property->parameters[] = new Parameter('paramname','paramvalue');
        $property->parameters[] = new Parameter('paramname2','paramvalue2');

        $this->assertEquals("PROPNAME;PARAMNAME=paramvalue;PARAMNAME2=paramvalue2:propvalue\r\n",$property->serialize());

    }

    public function testSerializeNewLine() {

        $property = new Text('propname',"line1\nline2");

        $this->assertEquals("PROPNAME:line1\\nline2\r\n",$property->serialize());

    }

    public function testSerializeLongLine() {

        $value = str_repeat('!',200);
        $property = new Text('propname',$value);

        $expected = "PROPNAME:" . str_repeat('!',66) . "\r\n " . str_repeat('!',74) . "\r\n " . str_repeat('!',60) . "\r\n";

        $this->assertEquals($expected,$property->serialize());

    }

    public function testSerializeUTF8LineFold() {

        $value = str_repeat('!',65) . "\xc3\xa4bla"; // inserted umlaut-a
        $property = new Text('propname', $value);
        $expected = "PROPNAME:" . str_repeat('!',65) . "\r\n \xc3\xa4bla\r\n";
        $this->assertEquals($expected, $property->serialize());

    }

}
