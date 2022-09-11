<?php

namespace Sabre\VObject;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VCard;

class PropertyTest extends TestCase
{
    public function testToString(): void
    {
        $cal = new VCalendar();

        $property = $cal->createProperty('propname', 'propvalue');
        self::assertEquals('PROPNAME', $property->name);
        self::assertEquals('propvalue', $property->__toString());
        self::assertEquals('propvalue', (string) $property);
        self::assertEquals('propvalue', $property->getValue());
    }

    public function testCreate(): void
    {
        $cal = new VCalendar();

        $params = [
            'param1' => 'value1',
            'param2' => 'value2',
        ];

        $property = $cal->createProperty('propname', 'propvalue', $params);
        /**
         * @var Parameter $param1
         */
        $param1 = $property['param1'];
        /**
         * @var Parameter $param2
         */
        $param2 = $property['param2'];

        self::assertEquals('value1', $param1->getValue());
        self::assertEquals('value2', $param2->getValue());
    }

    public function testSetValue(): void
    {
        $cal = new VCalendar();

        $property = $cal->createProperty('propname', 'propvalue');
        $property->setValue('value2');

        self::assertEquals('PROPNAME', $property->name);
        self::assertEquals('value2', $property->__toString());
    }

    public function testParameterExists(): void
    {
        $cal = new VCalendar();
        $property = $cal->createProperty('propname', 'propvalue');
        $property['paramname'] = 'paramvalue';

        self::assertTrue(isset($property['PARAMNAME']));
        self::assertTrue(isset($property['paramname']));
        self::assertFalse(isset($property['foo']));
    }

    public function testParameterGet(): void
    {
        $cal = new VCalendar();
        $property = $cal->createProperty('propname', 'propvalue');
        $property['paramname'] = 'paramvalue';

        self::assertInstanceOf(Parameter::class, $property['paramname']);
    }

    public function testParameterNotExists(): void
    {
        $cal = new VCalendar();
        $property = $cal->createProperty('propname', 'propvalue');
        $property['paramname'] = 'paramvalue';

        self::assertNull($property['foo']);
    }

    public function testParameterMultiple(): void
    {
        $cal = new VCalendar();
        $property = $cal->createProperty('propname', 'propvalue');
        $property['paramname'] = 'paramvalue';
        $property->add('paramname', 'paramvalue');
        /**
         * @var Parameter $param
         */
        $param = $property['paramname'];

        self::assertInstanceOf(Parameter::class, $param);
        self::assertCount(2, $param->getParts());
    }

    public function testSetParameterAsString(): void
    {
        $cal = new VCalendar();
        $property = $cal->createProperty('propname', 'propvalue');
        $property['paramname'] = 'paramvalue';

        self::assertCount(1, $property->parameters());
        self::assertInstanceOf(Parameter::class, $property->parameters['PARAMNAME']);
        self::assertEquals('PARAMNAME', $property->parameters['PARAMNAME']->name);
        self::assertEquals('paramvalue', $property->parameters['PARAMNAME']->getValue());
    }

    public function testUnsetParameter(): void
    {
        $cal = new VCalendar();
        $property = $cal->createProperty('propname', 'propvalue');
        $property['paramname'] = 'paramvalue';

        unset($property['PARAMNAME']);
        self::assertCount(0, $property->parameters());
    }

    public function testSerialize(): void
    {
        $cal = new VCalendar();
        $property = $cal->createProperty('propname', 'propvalue');

        self::assertEquals("PROPNAME:propvalue\r\n", $property->serialize());
    }

    public function testSerializeParam(): void
    {
        $cal = new VCalendar();
        $property = $cal->createProperty('propname', 'propvalue', [
            'paramname' => 'paramvalue',
            'paramname2' => 'paramvalue2',
        ]);

        self::assertEquals("PROPNAME;PARAMNAME=paramvalue;PARAMNAME2=paramvalue2:propvalue\r\n", $property->serialize());
    }

    public function testSerializeNewLine(): void
    {
        $cal = new VCalendar();
        $property = $cal->createProperty('SUMMARY', "line1\nline2");

        self::assertEquals("SUMMARY:line1\\nline2\r\n", $property->serialize());
    }

    public function testSerializeLongLine(): void
    {
        $cal = new VCalendar();
        $value = str_repeat('!', 200);
        $property = $cal->createProperty('propname', $value);

        $expected = 'PROPNAME:'.str_repeat('!', 66)."\r\n ".str_repeat('!', 74)."\r\n ".str_repeat('!', 60)."\r\n";

        self::assertEquals($expected, $property->serialize());
    }

    public function testSerializeUTF8LineFold(): void
    {
        $cal = new VCalendar();
        $value = str_repeat('!', 65)."\xc3\xa4bla".str_repeat('!', 142)."\xc3\xa4foo"; // inserted umlaut-a
        $property = $cal->createProperty('propname', $value);

        // PROPNAME:!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! ("PROPNAME:" + 65x"!" = 74 bytes)
        //  äbla!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! (" äbla"     + 69x"!" = 75 bytes)
        //  !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! (" "         + 73x"!" = 74 bytes)
        //  äfoo
        $expected = 'PROPNAME:'.str_repeat('!', 65)."\r\n \xc3\xa4bla".str_repeat('!', 69)."\r\n ".str_repeat('!', 73)."\r\n \xc3\xa4foo\r\n";
        self::assertEquals($expected, $property->serialize());
    }

    public function testGetIterator(): void
    {
        $cal = new VCalendar();
        $it = new ElementList([]);
        $property = $cal->createProperty('propname', 'propvalue');
        $property->setIterator($it);
        self::assertEquals($it, $property->getIterator());
    }

    public function testGetIteratorDefault(): void
    {
        $cal = new VCalendar();
        $property = $cal->createProperty('propname', 'propvalue');
        $it = $property->getIterator();
        self::assertTrue($it instanceof ElementList);
        self::assertCount(1, $it);
    }

    public function testAddScalar(): void
    {
        $cal = new VCalendar();
        $property = $cal->createProperty('EMAIL');

        $property->add('myparam', 'value');

        self::assertCount(1, $property->parameters());

        self::assertTrue($property->parameters['MYPARAM'] instanceof Parameter);
        self::assertEquals('MYPARAM', $property->parameters['MYPARAM']->name);
        self::assertEquals('value', $property->parameters['MYPARAM']->getValue());
    }

    public function testAddParameter(): void
    {
        $cal = new VCalendar();
        $prop = $cal->createProperty('EMAIL');

        $prop->add('MYPARAM', 'value');
        /**
         * @var Parameter $param
         */
        $param = $prop['myparam'];

        self::assertCount(1, $prop->parameters());
        self::assertEquals('MYPARAM', $param->name);
    }

    public function testAddParameterTwice(): void
    {
        $cal = new VCalendar();
        $prop = $cal->createProperty('EMAIL');

        $prop->add('MYPARAM', 'value1');
        $prop->add('MYPARAM', 'value2');

        self::assertCount(1, $prop->parameters);
        self::assertCount(2, $prop->parameters['MYPARAM']->getParts());
        /**
         * @var Parameter $param
         */
        $param = $prop->parameters['MYPARAM'];

        self::assertEquals('MYPARAM', $param->name);
    }

    public function testClone(): void
    {
        $cal = new VCalendar();
        $property = $cal->createProperty('EMAIL', 'value');
        $property['FOO'] = 'BAR';

        $property2 = clone $property;

        $property['FOO'] = 'BAZ';
        self::assertEquals('BAR', $property2['FOO']);
    }

    public function testCreateParams(): void
    {
        $cal = new VCalendar();
        $property = $cal->createProperty('X-PROP', 'value', [
            'param1' => 'value1',
            'param2' => ['value2', 'value3'],
        ]);

        /**
         * @var Parameter $param1
         */
        $param1 = $property['PARAM1'];

        /**
         * @var Parameter $param2
         */
        $param2 = $property['PARAM2'];

        self::assertCount(1, $param1->getParts());
        self::assertCount(2, $param2->getParts());
    }

    public function testValidateNonUTF8(): void
    {
        $calendar = new VCalendar();
        $property = $calendar->createProperty('X-PROP', "Bla\x00");
        $result = $property->validate(Property::REPAIR);

        self::assertEquals('Property contained a control character (0x00)', $result[0]['message']);
        self::assertEquals('Bla', $property->getValue());
    }

    public function testValidateControlChars(): void
    {
        $s = 'chars[';
        foreach ([
            0x7F, 0x5E, 0x5C, 0x3B, 0x3A, 0x2C, 0x22, 0x20,
            0x1F, 0x1E, 0x1D, 0x1C, 0x1B, 0x1A, 0x19, 0x18,
            0x17, 0x16, 0x15, 0x14, 0x13, 0x12, 0x11, 0x10,
            0x0F, 0x0E, 0x0D, 0x0C, 0x0B, 0x0A, 0x09, 0x08,
            0x07, 0x06, 0x05, 0x04, 0x03, 0x02, 0x01, 0x00,
          ] as $c) {
            $s .= sprintf('%02X(%c)', $c, $c);
        }
        $s .= ']end';

        $calendar = new VCalendar();
        $property = $calendar->createProperty('X-PROP', $s);
        $result = $property->validate(Property::REPAIR);

        self::assertEquals('Property contained a control character (0x7f)', $result[0]['message']);
        self::assertEquals("chars[7F()5E(^)5C(\\\\)3B(\\;)3A(:)2C(\\,)22(\")20( )1F()1E()1D()1C()1B()1A()19()18()17()16()15()14()13()12()11()10()0F()0E()0D()0C()0B()0A(\\n)09(\t)08()07()06()05()04()03()02()01()00()]end", $property->getRawMimeDirValue());
    }

    /**
     * @throws InvalidDataException
     */
    public function testValidateBadPropertyName(): void
    {
        $calendar = new VCalendar();
        $property = $calendar->createProperty('X_*&PROP*', 'Bla');
        $result = $property->validate(Node::REPAIR);

        self::assertEquals('The property name: X_*&PROP* contains invalid characters. Only A-Z, 0-9 and - are allowed', $result[0]['message']);
        self::assertEquals('X-PROP', $property->name);
    }

    /**
     * @throws InvalidDataException
     */
    public function testGetValue(): void
    {
        $calendar = new VCalendar();
        $property = $calendar->createProperty('SUMMARY', null);
        self::assertEquals([], $property->getParts());
        self::assertNull($property->getValue());

        $property->setValue([]);
        self::assertEquals([], $property->getParts());
        self::assertNull($property->getValue());

        $property->setValue([1]);
        self::assertEquals([1], $property->getParts());
        self::assertEquals(1, $property->getValue());

        $property->setValue([1, 2]);
        self::assertEquals([1, 2], $property->getParts());
        self::assertEquals('1,2', $property->getValue());

        $property->setValue('str');
        self::assertEquals(['str'], $property->getParts());
        self::assertEquals('str', $property->getValue());
    }

    /**
     * ElementList should reject this.
     */
    public function testArrayAccessSetInt(): void
    {
        $this->expectException(\LogicException::class);
        $calendar = new VCalendar();
        $property = $calendar->createProperty('X-PROP', null);

        $calendar->add($property);
        $calendar->{'X-PROP'}[0] = 'Something!';
    }

    /**
     * ElementList should reject this.
     */
    public function testArrayAccessUnsetInt(): void
    {
        $this->expectException(\LogicException::class);
        $calendar = new VCalendar();
        $property = $calendar->createProperty('X-PROP', null);

        $calendar->add($property);
        unset($calendar->{'X-PROP'}[0]);
    }

    public function testValidateBadEncoding(): void
    {
        $document = new VCalendar();
        $property = $document->add('X-FOO', 'value');
        $property['ENCODING'] = 'invalid'; /* @phpstan-ignore-line */

        $result = $property->validate();

        self::assertEquals('ENCODING=INVALID is not valid for this document type.', $result[0]['message']);
        self::assertEquals(3, $result[0]['level']);
    }

    public function testValidateBadEncodingVCard4(): void
    {
        $document = new VCard(['VERSION' => '4.0']);
        $property = $document->add('X-FOO', 'value');
        $property['ENCODING'] = 'BASE64'; /* @phpstan-ignore-line */

        $result = $property->validate();

        self::assertEquals('ENCODING parameter is not valid in vCard 4.', $result[0]['message']);
        self::assertEquals(3, $result[0]['level']);
    }

    public function testValidateBadEncodingVCard3(): void
    {
        $document = new VCard(['VERSION' => '3.0']);
        $property = $document->add('X-FOO', 'value');
        $property['ENCODING'] = 'BASE64'; /* @phpstan-ignore-line */

        $result = $property->validate();

        self::assertEquals('ENCODING=BASE64 is not valid for this document type.', $result[0]['message']);
        self::assertEquals(3, $result[0]['level']);

        // Validate the reparation of BASE64 formatted vCard v3
        $result = $property->validate(Property::REPAIR);

        self::assertEquals('ENCODING=BASE64 has been transformed to ENCODING=B.', $result[0]['message']);
        self::assertEquals(1, $result[0]['level']);
    }

    public function testValidateBadEncodingVCard21(): void
    {
        $document = new VCard(['VERSION' => '2.1']);
        $property = $document->add('X-FOO', 'value');
        $property['ENCODING'] = 'B'; /* @phpstan-ignore-line */
        $result = $property->validate();

        self::assertEquals('ENCODING=B is not valid for this document type.', $result[0]['message']);
        self::assertEquals(3, $result[0]['level']);
    }
}
