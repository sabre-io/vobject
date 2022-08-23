<?php

namespace Sabre\VObject\Property;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCard;

class CompoundTest extends TestCase
{
    public function testSetParts(): void
    {
        $arr = [
            'ABC, Inc.',
            'North American Division',
            'Marketing;Sales',
        ];

        $vcard = new VCard();
        $elem = $vcard->createProperty('ORG');
        $elem->setParts($arr);

        $this->assertEquals('ABC\, Inc.;North American Division;Marketing\;Sales', $elem->getValue());
        $this->assertCount(3, $elem->getParts());
        $parts = $elem->getParts();
        $this->assertEquals('Marketing;Sales', $parts[2]);
    }

    public function testGetParts(): void
    {
        $str = 'ABC\, Inc.;North American Division;Marketing\;Sales';

        $vcard = new VCard();
        $elem = $vcard->createProperty('ORG');
        $elem->setRawMimeDirValue($str);

        $this->assertCount(3, $elem->getParts());
        $parts = $elem->getParts();
        $this->assertEquals('Marketing;Sales', $parts[2]);
    }

    public function testGetPartsNull(): void
    {
        $vcard = new VCard();
        $elem = $vcard->createProperty('ORG', null);

        $this->assertCount(0, $elem->getParts());
    }
}
