<?php

namespace Sabre\VObject;

use PHPUnit\Framework\TestCase;

class ParameterTest extends TestCase
{
    public function testSetup(): void
    {
        $cal = new Component\VCalendar();

        $param = new Parameter($cal, 'name', 'value');
        self::assertEquals('NAME', $param->name);
        self::assertEquals('value', $param->getValue());
    }

    public function testSetupNameLess(): void
    {
        $card = new Component\VCard();

        $param = new Parameter($card, null, 'URL');
        self::assertEquals('VALUE', $param->name);
        self::assertEquals('URL', $param->getValue());
        self::assertTrue($param->noName);
    }

    public function testModify(): void
    {
        $cal = new Component\VCalendar();

        $param = new Parameter($cal, 'name', null);
        $param->addValue(1);
        self::assertEquals([1], $param->getParts());

        $param->setParts([1, 2]);
        self::assertEquals([1, 2], $param->getParts());

        $param->addValue(3);
        self::assertEquals([1, 2, 3], $param->getParts());

        $param->setValue(4);
        $param->addValue(5);
        self::assertEquals([4, 5], $param->getParts());
    }

    public function testCastToString(): void
    {
        $cal = new Component\VCalendar();
        $param = new Parameter($cal, 'name', 'value');
        self::assertEquals('value', $param->__toString());
        self::assertEquals('value', (string) $param);
    }

    public function testCastNullToString(): void
    {
        $cal = new Component\VCalendar();
        $param = new Parameter($cal, 'name', null);
        self::assertEquals('', $param->__toString());
        self::assertEquals('', (string) $param);
    }

    public function testSerialize(): void
    {
        $cal = new Component\VCalendar();
        $param = new Parameter($cal, 'name', 'value');
        self::assertEquals('NAME=value', $param->serialize());
    }

    public function testSerializeEmpty(): void
    {
        $cal = new Component\VCalendar();
        $param = new Parameter($cal, 'name', null);
        self::assertEquals('NAME=', $param->serialize());
    }

    public function testSerializeComplex(): void
    {
        $cal = new Component\VCalendar();
        $param = new Parameter($cal, 'name', ['val1', 'val2;', 'val3^', "val4\n", 'val5"']);
        self::assertEquals('NAME=val1,"val2;","val3^^","val4^n","val5^\'"', $param->serialize());
    }

    /**
     * iCal 7.0 (OSX 10.9) has major issues with the EMAIL property, when the
     * value contains a plus sign, and it's not quoted.
     *
     * So we specifically added support for that.
     */
    public function testSerializePlusSign(): void
    {
        $cal = new Component\VCalendar();
        $param = new Parameter($cal, 'EMAIL', 'user+something@example.org');
        self::assertEquals('EMAIL="user+something@example.org"', $param->serialize());
    }

    public function testIterate(): void
    {
        $cal = new Component\VCalendar();

        $param = new Parameter($cal, 'name', [1, 2, 3, 4]);
        $result = [];

        foreach ($param as $value) {
            $result[] = $value;
        }

        self::assertEquals([1, 2, 3, 4], $result);
    }

    public function testSerializeColon(): void
    {
        $cal = new Component\VCalendar();
        $param = new Parameter($cal, 'name', 'va:lue');
        self::assertEquals('NAME="va:lue"', $param->serialize());
    }

    public function testSerializeSemiColon(): void
    {
        $cal = new Component\VCalendar();
        $param = new Parameter($cal, 'name', 'va;lue');
        self::assertEquals('NAME="va;lue"', $param->serialize());
    }
}
