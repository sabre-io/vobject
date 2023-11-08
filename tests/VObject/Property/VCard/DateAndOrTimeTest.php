<?php

namespace Sabre\VObject\Property\VCard;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCard;
use Sabre\VObject\Reader;

class DateAndOrTimeTest extends TestCase
{
    /**
     * @dataProvider dates
     */
    public function testGetJsonValue(string $input, string $output): void
    {
        $vcard = new VCard();
        $prop = $vcard->createProperty('BDAY', $input);

        self::assertEquals([$output], $prop->getJsonValue());
    }

    /**
     * @return string[][]
     */
    public function dates(): array
    {
        return [
            [
                '19961022T140000',
                '1996-10-22T14:00:00',
            ],
            [
                '--1022T1400',
                '--10-22T14:00',
            ],
            [
                '---22T14',
                '---22T14',
            ],
            [
                '19850412',
                '1985-04-12',
            ],
            [
                '1985-04',
                '1985-04',
            ],
            [
                '1985',
                '1985',
            ],
            [
                '--0412',
                '--04-12',
            ],
            [
                'T102200',
                'T10:22:00',
            ],
            [
                'T1022',
                'T10:22',
            ],
            [
                'T10',
                'T10',
            ],
            [
                'T-2200',
                'T-22:00',
            ],
            [
                'T102200Z',
                'T10:22:00Z',
            ],
            [
                'T102200-0800',
                'T10:22:00-0800',
            ],
            [
                'T--00',
                'T--00',
            ],
        ];
    }

    public function testSetParts(): void
    {
        $vcard = new VCard();

        $prop = $vcard->createProperty('BDAY');
        $prop->setParts([
            new \DateTime('2014-04-02 18:37:00'),
        ]);

        self::assertEquals('20140402T183700Z', $prop->getValue());
    }

    public function testSetPartsDateTimeImmutable(): void
    {
        $vcard = new VCard();

        $prop = $vcard->createProperty('BDAY');
        $prop->setParts([
            new \DateTimeImmutable('2014-04-02 18:37:00'),
        ]);

        self::assertEquals('20140402T183700Z', $prop->getValue());
    }

    public function testSetPartsTooMany(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $vcard = new VCard();

        $prop = $vcard->createProperty('BDAY');
        $prop->setParts([
            1,
            2,
        ]);
    }

    public function testSetPartsString(): void
    {
        $vcard = new VCard();

        $prop = $vcard->createProperty('BDAY');
        $prop->setParts([
            '20140402T183700Z',
        ]);

        self::assertEquals('20140402T183700Z', $prop->getValue());
    }

    public function testSetValueDateTime(): void
    {
        $vcard = new VCard();

        /**
         * @var DateAndOrTime<string, mixed> $prop
         */
        $prop = $vcard->createProperty('BDAY');
        $prop->setValue(
            new \DateTime('2014-04-02 18:37:00')
        );

        self::assertEquals('20140402T183700Z', $prop->getValue());
    }

    public function testSetValueDateTimeImmutable(): void
    {
        $vcard = new VCard();

        /**
         * @var DateAndOrTime<string, mixed> $prop
         */
        $prop = $vcard->createProperty('BDAY');
        $prop->setValue(
            new \DateTimeImmutable('2014-04-02 18:37:00')
        );

        self::assertEquals('20140402T183700Z', $prop->getValue());
    }

    public function testSetDateTimeOffset(): void
    {
        $vcard = new VCard();

        /**
         * @var DateAndOrTime<string, mixed> $prop
         */
        $prop = $vcard->createProperty('BDAY');
        $prop->setValue(
            new \DateTime('2014-04-02 18:37:00', new \DateTimeZone('America/Toronto'))
        );

        self::assertEquals('20140402T183700-0400', $prop->getValue());
    }

    public function testGetDateTime(): void
    {
        $datetime = new \DateTime('2014-04-02 18:37:00', new \DateTimeZone('America/Toronto'));

        $vcard = new VCard();
        /**
         * @var DateAndOrTime<string, mixed> $prop
         */
        $prop = $vcard->createProperty('BDAY', $datetime);

        $dt = $prop->getDateTime();
        self::assertEquals('2014-04-02T18:37:00-04:00', $dt->format('c'), 'For some reason this one failed. Current default timezone is: '.date_default_timezone_get());
    }

    public function testGetDate(): void
    {
        $datetime = new \DateTime('2014-04-02');

        $vcard = new VCard();
        $prop = $vcard->createProperty('BDAY', $datetime, null, 'DATE');

        self::assertEquals('DATE', $prop->getValueType());
        self::assertEquals('BDAY:20140402', rtrim($prop->serialize()));
    }

    public function testGetDateIncomplete(): void
    {
        $datetime = '--0407';

        $vcard = new VCard();
        /**
         * @var DateAndOrTime<string, mixed> $prop
         */
        $prop = $vcard->add('BDAY', $datetime);

        $dt = $prop->getDateTime();
        // Note: if the year changes between the last line and the next line of
        // code, this test may fail.
        //
        // If that happens, head outside and have a drink.
        $current = new \DateTime('now');
        $year = $current->format('Y');

        self::assertEquals($year.'0407', $dt->format('Ymd'));
    }

    public function testGetDateIncompleteFromVCard(): void
    {
        $input = <<<VCF
BEGIN:VCARD
VERSION:4.0
BDAY:--0407
END:VCARD
VCF;
        /** @var VCard<int, mixed> $vcard */
        $vcard = Reader::read($input);
        /**
         * @var DateAndOrTime<string, mixed> $prop
         */
        $prop = $vcard->BDAY;

        $dt = $prop->getDateTime();
        // Note: if the year changes between the last line and the next line of
        // code, this test may fail.
        //
        // If that happens, head outside and have a drink.
        $current = new \DateTime('now');
        $year = $current->format('Y');

        self::assertEquals($year.'0407', $dt->format('Ymd'));
    }

    public function testValidate(): void
    {
        $datetime = '--0407';

        $vcard = new VCard();
        $prop = $vcard->add('BDAY', $datetime);

        self::assertEquals([], $prop->validate());
    }

    public function testValidateBroken(): void
    {
        $datetime = '123';

        $vcard = new VCard();
        $prop = $vcard->add('BDAY', $datetime);

        self::assertEquals([[
            'level' => 3,
            'message' => 'The supplied value (123) is not a correct DATE-AND-OR-TIME property',
            'node' => $prop,
        ]], $prop->validate());
    }
}
