<?php

namespace Sabre\VObject;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VCard;

class ComponentTest extends TestCase
{
    public function testIterate()
    {
        $comp = new VCalendar([], false);

        $sub = $comp->createComponent('VEVENT');
        $comp->add($sub);

        $sub = $comp->createComponent('VTODO');
        $comp->add($sub);

        $count = 0;
        foreach ($comp->children() as $key => $subcomponent) {
            ++$count;
            $this->assertInstanceOf(Component::class, $subcomponent);

            if (2 === $count) {
                $this->assertEquals(1, $key);
            }
        }
        $this->assertEquals(2, $count);
    }

    public function testMagicGet()
    {
        $comp = new VCalendar([], false);

        $sub = $comp->createComponent('VEVENT');
        $comp->add($sub);

        $sub = $comp->createComponent('VTODO');
        $comp->add($sub);

        $event = $comp->VEVENT;
        $this->assertInstanceOf(Component::class, $event);
        $this->assertEquals('VEVENT', $event->name);

        $this->assertNull($comp->VJOURNAL);
    }

    /**
     * @throws InvalidDataException
     */
    public function testMagicGetGroups()
    {
        $comp = new VCard();

        $sub = $comp->createProperty('GROUP1.EMAIL', '1@1.com');
        $comp->add($sub);

        $sub = $comp->createProperty('GROUP2.EMAIL', '2@2.com');
        $comp->add($sub);

        $sub = $comp->createProperty('EMAIL', '3@3.com');
        $comp->add($sub);

        $emails = $comp->EMAIL;
        $this->assertCount(3, $emails);

        $email1 = $comp->{'group1.email'};
        $this->assertEquals('EMAIL', $email1[0]->name);
        $this->assertEquals('GROUP1', $email1[0]->group);

        $email3 = $comp->{'.email'};
        $this->assertEquals('EMAIL', $email3[0]->name);
        $this->assertEquals(null, $email3[0]->group);
    }

    public function testAddGroupProperties()
    {
        $comp = new VCard([
            'VERSION' => '3.0',
            'item2.X-ABLabel' => 'item2-Foo',
        ]);

        $comp->{'ITEM1.X-ABLabel'} = 'ITEM1-Foo';

        foreach (['item2', 'ITEM1'] as $group) {
            $prop = $comp->{"$group.X-ABLabel"};
            $this->assertInstanceOf(Property::class, $prop);
            $this->assertSame("$group-Foo", (string) $prop);
            $this->assertSame($group, $prop->group);
        }
    }

    public function testMagicIsset()
    {
        $comp = new VCalendar();

        $sub = $comp->createComponent('VEVENT');
        $comp->add($sub);

        $sub = $comp->createComponent('VTODO');
        $comp->add($sub);

        $this->assertTrue(isset($comp->vevent));
        $this->assertTrue(isset($comp->vtodo));
        $this->assertFalse(isset($comp->vjournal));
    }

    public function testMagicSetScalar()
    {
        $comp = new VCalendar();
        $comp->myProp = 'myValue';

        $this->assertInstanceOf(Property::class, $comp->MYPROP);
        $this->assertEquals('myValue', (string) $comp->MYPROP);
    }

    public function testMagicSetScalarTwice()
    {
        $comp = new VCalendar([], false);
        $comp->myProp = 'myValue';
        $comp->myProp = 'myValue';

        $this->assertCount(1, $comp->children());
        $this->assertInstanceOf(Property::class, $comp->MYPROP);
        $this->assertEquals('myValue', (string) $comp->MYPROP);
    }

    public function testMagicSetArray()
    {
        $comp = new VCalendar();
        $comp->ORG = ['Acme Inc', 'Section 9'];

        $this->assertInstanceOf(Property::class, $comp->ORG);
        $this->assertEquals(['Acme Inc', 'Section 9'], $comp->ORG->getParts());
    }

    public function testMagicSetComponent()
    {
        $comp = new VCalendar();

        // Note that 'myProp' is ignored here.
        $comp->myProp = $comp->createComponent('VEVENT');

        $this->assertCount(1, $comp);

        $this->assertEquals('VEVENT', $comp->VEVENT->name);
    }

    public function testMagicSetTwice()
    {
        $comp = new VCalendar([], false);

        $comp->VEVENT = $comp->createComponent('VEVENT');
        $comp->VEVENT = $comp->createComponent('VEVENT');

        $this->assertCount(1, $comp->children());

        $this->assertEquals('VEVENT', $comp->VEVENT->name);
    }

    public function testArrayAccessGet()
    {
        $comp = new VCalendar([], false);

        $event = $comp->createComponent('VEVENT');
        $event->summary = 'Event 1';

        $comp->add($event);

        $event2 = clone $event;
        $event2->summary = 'Event 2';

        $comp->add($event2);

        $this->assertEquals(2, count($comp->children()));
        $this->assertTrue($comp->vevent[1] instanceof Component);
        $this->assertEquals('Event 2', (string) $comp->vevent[1]->summary);
    }

    public function testArrayAccessExists()
    {
        $comp = new VCalendar();

        $event = $comp->createComponent('VEVENT');
        $event->summary = 'Event 1';

        $comp->add($event);

        $event2 = clone $event;
        $event2->summary = 'Event 2';

        $comp->add($event2);

        $this->assertTrue(isset($comp->vevent[0]));
        $this->assertTrue(isset($comp->vevent[1]));
    }

    public function testArrayAccessSet()
    {
        $this->expectException(\LogicException::class);
        $comp = new VCalendar();
        $comp['hey'] = 'hi there';
    }

    public function testArrayAccessUnset()
    {
        $this->expectException(\LogicException::class);
        $comp = new VCalendar();
        unset($comp[0]);
    }

    public function testAddScalar()
    {
        $comp = new VCalendar([], false);

        $comp->add('myprop', 'value');

        $this->assertCount(1, $comp->children());

        $bla = $comp->children()[0];

        $this->assertTrue($bla instanceof Property);
        $this->assertEquals('MYPROP', $bla->name);
        $this->assertEquals('value', (string) $bla);
    }

    public function testAddScalarParams()
    {
        $comp = new VCalendar([], false);

        $comp->add('myprop', 'value', ['param1' => 'value1']);

        $this->assertCount(1, $comp->children());

        $bla = $comp->children()[0];

        $this->assertInstanceOf(Property::class, $bla);
        $this->assertEquals('MYPROP', $bla->name);
        $this->assertEquals('value', (string) $bla);

        $this->assertCount(1, $bla->parameters());

        $this->assertEquals('PARAM1', $bla->parameters['PARAM1']->name);
        $this->assertEquals('value1', $bla->parameters['PARAM1']->getValue());
    }

    public function testAddComponent()
    {
        $comp = new VCalendar([], false);

        $comp->add($comp->createComponent('VEVENT'));

        $this->assertCount(1, $comp->children());

        $this->assertEquals('VEVENT', $comp->VEVENT->name);
    }

    public function testAddComponentTwice()
    {
        $comp = new VCalendar([], false);

        $comp->add($comp->createComponent('VEVENT'));
        $comp->add($comp->createComponent('VEVENT'));

        $this->assertCount(2, $comp->children());

        $this->assertEquals('VEVENT', $comp->VEVENT->name);
    }

    public function testAddArgFail()
    {
        $this->expectException(\InvalidArgumentException::class);
        $comp = new VCalendar();
        $comp->add($comp->createComponent('VEVENT'), 'hello');
    }

    public function testAddArgFail2()
    {
        $this->expectException(\InvalidArgumentException::class);
        $comp = new VCalendar();
        $comp->add([]);
    }

    public function testMagicUnset()
    {
        $comp = new VCalendar([], false);
        $comp->add($comp->createComponent('VEVENT'));

        unset($comp->vevent);

        $this->assertCount(0, $comp->children());
    }

    public function testCount()
    {
        $comp = new VCalendar();
        $this->assertEquals(1, $comp->count());
    }

    public function testChildren()
    {
        $comp = new VCalendar([], false);

        // Note that 'myProp' is ignored here.
        $comp->add($comp->createComponent('VEVENT'));
        $comp->add($comp->createComponent('VTODO'));

        $r = $comp->children();
        $this->assertIsArray($r);
        $this->assertCount(2, $r);
    }

    /**
     * @throws InvalidDataException
     */
    public function testGetComponents()
    {
        $comp = new VCalendar();

        $comp->add($comp->createProperty('FOO', 'BAR'));
        $comp->add($comp->createComponent('VTODO'));

        $r = $comp->getComponents();
        $this->assertIsArray($r);
        $this->assertCount(1, $r);
        $this->assertEquals('VTODO', $r[0]->name);
    }

    public function testSerialize()
    {
        $comp = new VCalendar([], false);
        $this->assertEquals("BEGIN:VCALENDAR\r\nEND:VCALENDAR\r\n", $comp->serialize());
    }

    public function testSerializeChildren()
    {
        $comp = new VCalendar([], false);
        $event = $comp->add($comp->createComponent('VEVENT'));
        unset($event->DTSTAMP, $event->UID);
        $todo = $comp->add($comp->createComponent('VTODO'));
        unset($todo->DTSTAMP, $todo->UID);

        $str = $comp->serialize();

        $this->assertEquals("BEGIN:VCALENDAR\r\nBEGIN:VEVENT\r\nEND:VEVENT\r\nBEGIN:VTODO\r\nEND:VTODO\r\nEND:VCALENDAR\r\n", $str);
    }

    public function testSerializeOrderCompAndProp()
    {
        $comp = new VCalendar([], false);
        $comp->add($event = $comp->createComponent('VEVENT'));
        $comp->add('PROP1', 'BLABLA');
        $comp->add('VERSION', '2.0');
        $comp->add($comp->createComponent('VTIMEZONE'));

        unset($event->DTSTAMP, $event->UID);
        $str = $comp->serialize();

        $this->assertEquals("BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPROP1:BLABLA\r\nBEGIN:VTIMEZONE\r\nEND:VTIMEZONE\r\nBEGIN:VEVENT\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n", $str);
    }

    public function testAnotherSerializeOrderProp()
    {
        $prop4s = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'];

        $comp = new VCard([], false);

        $comp->__set('SOMEPROP', 'FOO');
        $comp->__set('ANOTHERPROP', 'FOO');
        $comp->__set('THIRDPROP', 'FOO');
        foreach ($prop4s as $prop4) {
            $comp->add('PROP4', 'FOO '.$prop4);
        }
        $comp->__set('PROPNUMBERFIVE', 'FOO');
        $comp->__set('PROPNUMBERSIX', 'FOO');
        $comp->__set('PROPNUMBERSEVEN', 'FOO');
        $comp->__set('PROPNUMBEREIGHT', 'FOO');
        $comp->__set('PROPNUMBERNINE', 'FOO');
        $comp->__set('PROPNUMBERTEN', 'FOO');
        $comp->__set('VERSION', '2.0');
        $comp->__set('UID', 'FOO');

        $str = $comp->serialize();

        $this->assertEquals("BEGIN:VCARD\r\nVERSION:2.0\r\nSOMEPROP:FOO\r\nANOTHERPROP:FOO\r\nTHIRDPROP:FOO\r\nPROP4:FOO 1\r\nPROP4:FOO 2\r\nPROP4:FOO 3\r\nPROP4:FOO 4\r\nPROP4:FOO 5\r\nPROP4:FOO 6\r\nPROP4:FOO 7\r\nPROP4:FOO 8\r\nPROP4:FOO 9\r\nPROP4:FOO 10\r\nPROPNUMBERFIVE:FOO\r\nPROPNUMBERSIX:FOO\r\nPROPNUMBERSEVEN:FOO\r\nPROPNUMBEREIGHT:FOO\r\nPROPNUMBERNINE:FOO\r\nPROPNUMBERTEN:FOO\r\nUID:FOO\r\nEND:VCARD\r\n", $str);
    }

    public function testInstantiateWithChildren()
    {
        $comp = new VCard([
            'ORG' => ['Acme Inc.', 'Section 9'],
            'FN' => 'Finn The Human',
        ]);

        $this->assertEquals(['Acme Inc.', 'Section 9'], $comp->ORG->getParts());
        $this->assertEquals('Finn The Human', $comp->FN->getValue());
    }

    public function testInstantiateSubComponent()
    {
        $comp = new VCalendar();
        $event = $comp->createComponent('VEVENT', [
            $comp->createProperty('UID', '12345'),
        ]);
        $comp->add($event);

        $this->assertEquals('12345', $comp->VEVENT->UID->getValue());
    }

    public function testRemoveByName()
    {
        $comp = new VCalendar([], false);
        $comp->add('prop1', 'val1');
        $comp->add('prop2', 'val2');
        $comp->add('prop2', 'val2');

        $comp->remove('prop2');
        $this->assertFalse(isset($comp->prop2));
        $this->assertTrue(isset($comp->prop1));
    }

    public function testRemoveByObj()
    {
        $comp = new VCalendar([], false);
        $comp->add('prop1', 'val1');
        $prop = $comp->add('prop2', 'val2');

        $comp->remove($prop);
        $this->assertFalse(isset($comp->prop2));
        $this->assertTrue(isset($comp->prop1));
    }

    /**
     * @throws InvalidDataException
     */
    public function testRemoveNotFound()
    {
        $this->expectException(\InvalidArgumentException::class);
        $comp = new VCalendar([], false);
        $prop = $comp->createProperty('A', 'B');
        $comp->remove($prop);
    }

    /**
     * @dataProvider ruleData
     */
    public function testValidateRules($componentList, $errorCount)
    {
        $vcard = new Component\VCard();

        $component = new FakeComponent($vcard, 'Hi', [], $defaults = false);
        foreach ($componentList as $v) {
            $component->add($v, 'Hello.');
        }

        $this->assertCount($errorCount, $component->validate());
    }

    public function testValidateRepair()
    {
        $vcard = new Component\VCard();

        $component = new FakeComponent($vcard, 'Hi', [], $defaults = false);
        $component->validate(Component::REPAIR);
        $this->assertEquals('yow', $component->BAR->getValue());
    }

    public function testValidateRepairShouldNotDeduplicatePropertiesWhenValuesDiffer()
    {
        $vcard = new Component\VCard();

        $component = new FakeComponent($vcard, 'WithDuplicateGIR', []);
        $component->add('BAZ', 'BAZ');
        $component->add('GIR', 'VALUE1');
        $component->add('GIR', 'VALUE2'); // Different values

        $messages = $component->validate(Component::REPAIR);

        $this->assertCount(1, $messages);
        $this->assertEquals(3, $messages[0]['level']);
        $this->assertCount(2, $component->GIR);
    }

    public function testValidateRepairShouldNotDeduplicatePropertiesWhenParametersDiffer()
    {
        $vcard = new Component\VCard();

        $component = new FakeComponent($vcard, 'WithDuplicateGIR', []);
        $component->add('BAZ', 'BAZ');
        $component->add('GIR', 'VALUE')->add('PARAM', '1');
        $component->add('GIR', 'VALUE')->add('PARAM', '2'); // Same value but different parameters

        $messages = $component->validate(Component::REPAIR);

        $this->assertCount(1, $messages);
        $this->assertEquals(3, $messages[0]['level']);
        $this->assertCount(2, $component->GIR);
    }

    public function testValidateRepairShouldDeduplicatePropertiesWhenValuesAndParametersAreEqual()
    {
        $vcard = new Component\VCard();

        $component = new FakeComponent($vcard, 'WithDuplicateGIR', []);
        $component->add('BAZ', 'BAZ');
        $component->add('GIR', 'VALUE')->add('PARAM', 'P');
        $component->add('GIR', 'VALUE')->add('PARAM', 'P');

        $messages = $component->validate(Component::REPAIR);

        $this->assertCount(1, $messages);
        $this->assertEquals(1, $messages[0]['level']);
        $this->assertCount(1, $component->GIR);
    }

    public function testValidateRepairShouldDeduplicatePropertiesWhenValuesAreEqual()
    {
        $vcard = new Component\VCard();

        $component = new FakeComponent($vcard, 'WithDuplicateGIR', []);
        $component->add('BAZ', 'BAZ');
        $component->add('GIR', 'VALUE');
        $component->add('GIR', 'VALUE');

        $messages = $component->validate(Component::REPAIR);

        $this->assertCount(1, $messages);
        $this->assertEquals(1, $messages[0]['level']);
        $this->assertCount(1, $component->GIR);
    }

    public function ruleData(): array
    {
        return [
            [[], 2],
            [['FOO'], 3],
            [['BAR'], 1],
            [['BAZ'], 1],
            [['BAR', 'BAZ'], 0],
            [['BAR', 'BAZ', 'ZIM'], 0],
            [['BAR', 'BAZ', 'ZIM', 'GIR'], 0],
            [['BAR', 'BAZ', 'ZIM', 'GIR', 'GIR'], 1],
        ];
    }
}

class FakeComponent extends Component
{
    public function getValidationRules(): array
    {
        return [
            'FOO' => '0',
            'BAR' => '1',
            'BAZ' => '+',
            'ZIM' => '*',
            'GIR' => '?',
        ];
    }

    public function getDefaults(): array
    {
        return [
            'BAR' => 'yow',
        ];
    }
}
