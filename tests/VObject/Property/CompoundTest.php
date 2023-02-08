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

        self::assertEquals('ABC\, Inc.;North American Division;Marketing\;Sales', $elem->getValue());
        self::assertCount(3, $elem->getParts());
        $parts = $elem->getParts();
        self::assertEquals('Marketing;Sales', $parts[2]);
    }

    public function testGetParts(): void
    {
        $str = 'ABC\, Inc.;North American Division;Marketing\;Sales';

        $vcard = new VCard();
        $elem = $vcard->createProperty('ORG');
        $elem->setRawMimeDirValue($str);

        self::assertCount(3, $elem->getParts());
        $parts = $elem->getParts();
        self::assertEquals('Marketing;Sales', $parts[2]);
    }

    public function testGetPartsNull(): void
    {
        $vcard = new VCard();
        $elem = $vcard->createProperty('ORG', null);

        self::assertCount(0, $elem->getParts());
    }
}
