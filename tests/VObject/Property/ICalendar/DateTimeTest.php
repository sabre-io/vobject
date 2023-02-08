<?php

namespace Sabre\VObject\Property\ICalendar;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VEvent;
use Sabre\VObject\Component\VTimeZone;
use Sabre\VObject\InvalidDataException;
use Sabre\VObject\Property\FlatText;

class DateTimeTest extends TestCase
{
    /**
     * @var VCalendar<int, mixed>
     */
    protected VCalendar $vcal;

    public function setUp(): void
    {
        $this->vcal = new VCalendar();
    }

    public function testSetDateTime(): void
    {
        $tz = new \DateTimeZone('Europe/Amsterdam');
        $dt = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt->setTimeZone($tz);

        /**
         * @var DateTime<string, mixed> $elem
         */
        $elem = $this->vcal->createProperty('DTSTART');
        $elem->setDateTime($dt);

        self::assertEquals('19850704T013000', (string) $elem);
        self::assertEquals('Europe/Amsterdam', $elem['TZID']);
        self::assertNull($elem['VALUE']);

        self::assertTrue($elem->hasTime());
    }

    public function testSetDateTimeLOCAL(): void
    {
        $tz = new \DateTimeZone('Europe/Amsterdam');
        $dt = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt->setTimeZone($tz);

        /**
         * @var DateTime<string, mixed> $elem
         */
        $elem = $this->vcal->createProperty('DTSTART');
        $elem->setDateTime($dt, true);

        self::assertEquals('19850704T013000', (string) $elem);
        self::assertNull($elem['TZID']);

        self::assertTrue($elem->hasTime());
    }

    public function testSetDateTimeUTC(): void
    {
        $tz = new \DateTimeZone('GMT');
        $dt = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt->setTimeZone($tz);

        /**
         * @var DateTime<string, mixed> $elem
         */
        $elem = $this->vcal->createProperty('DTSTART');
        $elem->setDateTime($dt);

        self::assertEquals('19850704T013000Z', (string) $elem);
        self::assertNull($elem['TZID']);

        self::assertTrue($elem->hasTime());
    }

    public function testSetDateTimeFromUnixTimestamp(): void
    {
        // When initialized from a Unix timestamp, the timezone is set to "+00:00".
        $dt = new \DateTime('@489288600');

        /**
         * @var DateTime<string, mixed> $elem
         */
        $elem = $this->vcal->createProperty('DTSTART');
        $elem->setDateTime($dt);

        self::assertEquals('19850704T013000Z', (string) $elem);
        self::assertNull($elem['TZID']);

        self::assertTrue($elem->hasTime());
    }

    public function testSetDateTimeLOCALTZ(): void
    {
        $tz = new \DateTimeZone('Europe/Amsterdam');
        $dt = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt->setTimeZone($tz);

        /**
         * @var DateTime<string, mixed> $elem
         */
        $elem = $this->vcal->createProperty('DTSTART');
        $elem->setDateTime($dt);

        self::assertEquals('19850704T013000', (string) $elem);
        self::assertEquals('Europe/Amsterdam', $elem['TZID']);

        self::assertTrue($elem->hasTime());
    }

    public function testSetDateTimeDATE(): void
    {
        $tz = new \DateTimeZone('Europe/Amsterdam');
        $dt = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt->setTimeZone($tz);

        /**
         * @var DateTime<string, mixed> $elem
         */
        $elem = $this->vcal->createProperty('DTSTART');
        $elem['VALUE'] = 'DATE';
        $elem->setDateTime($dt);

        self::assertEquals('19850704', (string) $elem);
        self::assertNull($elem['TZID']);
        self::assertEquals('DATE', $elem['VALUE']);

        self::assertFalse($elem->hasTime());
    }

    public function testSetValue(): void
    {
        $tz = new \DateTimeZone('Europe/Amsterdam');
        $dt = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt->setTimeZone($tz);

        /**
         * @var DateTime<string, mixed> $elem
         */
        $elem = $this->vcal->createProperty('DTSTART');
        $elem->setValue($dt);

        self::assertEquals('19850704T013000', (string) $elem);
        self::assertEquals('Europe/Amsterdam', $elem['TZID']);
        self::assertNull($elem['VALUE']);

        self::assertTrue($elem->hasTime());
    }

    public function testSetValueArray(): void
    {
        $tz = new \DateTimeZone('Europe/Amsterdam');
        $dt1 = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt2 = new \DateTime('1985-07-04 02:30:00', $tz);
        $dt1->setTimeZone($tz);
        $dt2->setTimeZone($tz);

        /**
         * @var DateTime<string, mixed> $elem
         */
        $elem = $this->vcal->createProperty('DTSTART');
        $elem->setValue([$dt1, $dt2]);

        self::assertEquals('19850704T013000,19850704T023000', (string) $elem);
        self::assertEquals('Europe/Amsterdam', $elem['TZID']);
        self::assertNull($elem['VALUE']);

        self::assertTrue($elem->hasTime());
    }

    public function testSetParts(): void
    {
        $tz = new \DateTimeZone('Europe/Amsterdam');
        $dt1 = new \DateTime('1985-07-04 01:30:00', $tz);
        $dt2 = new \DateTime('1985-07-04 02:30:00', $tz);
        $dt1->setTimeZone($tz);
        $dt2->setTimeZone($tz);

        /**
         * @var DateTime<string, mixed> $elem
         */
        $elem = $this->vcal->createProperty('DTSTART');
        $elem->setParts([$dt1, $dt2]);

        self::assertEquals('19850704T013000,19850704T023000', (string) $elem);
        self::assertEquals('Europe/Amsterdam', $elem['TZID']);
        self::assertNull($elem['VALUE']);

        self::assertTrue($elem->hasTime());
    }

    public function testSetPartsStrings(): void
    {
        $dt1 = '19850704T013000Z';
        $dt2 = '19850704T023000Z';

        /**
         * @var DateTime<string, mixed> $elem
         */
        $elem = $this->vcal->createProperty('DTSTART');
        $elem->setParts([$dt1, $dt2]);

        self::assertEquals('19850704T013000Z,19850704T023000Z', (string) $elem);
        self::assertNull($elem['VALUE']);

        self::assertTrue($elem->hasTime());
    }

    public function testGetDateTimeCached(): void
    {
        $tz = new \DateTimeZone('Europe/Amsterdam');
        $dt = new \DateTimeImmutable('1985-07-04 01:30:00', $tz);
        $dt->setTimeZone($tz); /* @phpstan-ignore-line */

        /**
         * @var DateTime<string, mixed> $elem
         */
        $elem = $this->vcal->createProperty('DTSTART');
        $elem->setDateTime($dt);

        self::assertEquals($elem->getDateTime(), $dt);
    }

    public function testGetDateTimeDateNULL(): void
    {
        /**
         * @var DateTime<string, mixed> $elem
         */
        $elem = $this->vcal->createProperty('DTSTART');
        $dt = $elem->getDateTime();

        self::assertNull($dt);
    }

    public function testGetDateTimeDateDATE(): void
    {
        /**
         * @var DateTime<string, mixed> $elem
         */
        $elem = $this->vcal->createProperty('DTSTART', '19850704');
        $dt = $elem->getDateTime();

        self::assertInstanceOf('DateTimeImmutable', $dt);
        self::assertEquals('1985-07-04 00:00:00', $dt->format('Y-m-d H:i:s'));
    }

    public function testGetDateTimeDateDATEReferenceTimeZone(): void
    {
        /**
         * @var DateTime<string, mixed> $elem
         */
        $elem = $this->vcal->createProperty('DTSTART', '19850704');

        $tz = new \DateTimeZone('America/Toronto');
        $dt = $elem->getDateTime($tz);
        $dt = $dt->setTimeZone(new \DateTimeZone('UTC'));

        self::assertInstanceOf('DateTimeImmutable', $dt);
        self::assertEquals('1985-07-04 04:00:00', $dt->format('Y-m-d H:i:s'));
    }

    public function testGetDateTimeDateFloating(): void
    {
        /**
         * @var DateTime<string, mixed> $elem
         */
        $elem = $this->vcal->createProperty('DTSTART', '19850704T013000');
        $dt = $elem->getDateTime();

        self::assertInstanceOf('DateTimeImmutable', $dt);
        self::assertEquals('1985-07-04 01:30:00', $dt->format('Y-m-d H:i:s'));
    }

    public function testGetDateTimeDateFloatingReferenceTimeZone(): void
    {
        /**
         * @var DateTime<string, mixed> $elem
         */
        $elem = $this->vcal->createProperty('DTSTART', '19850704T013000');

        $tz = new \DateTimeZone('America/Toronto');
        $dt = $elem->getDateTime($tz);
        $dt = $dt->setTimeZone(new \DateTimeZone('UTC'));

        self::assertInstanceOf('DateTimeInterface', $dt);
        self::assertEquals('1985-07-04 05:30:00', $dt->format('Y-m-d H:i:s'));
    }

    public function testGetDateTimeDateUTC(): void
    {
        /**
         * @var DateTime<string, mixed> $elem
         */
        $elem = $this->vcal->createProperty('DTSTART', '19850704T013000Z');
        $dt = $elem->getDateTime();

        self::assertInstanceOf('DateTimeImmutable', $dt);
        self::assertEquals('1985-07-04 01:30:00', $dt->format('Y-m-d H:i:s'));
        self::assertEquals('UTC', $dt->getTimeZone()->getName());
    }

    public function testGetDateTimeDateLOCALTZ(): void
    {
        /**
         * @var DateTime<string, mixed> $elem
         */
        $elem = $this->vcal->createProperty('DTSTART', '19850704T013000');
        $elem['TZID'] = 'Europe/Amsterdam';

        $dt = $elem->getDateTime();

        self::assertInstanceOf('DateTimeImmutable', $dt);
        self::assertEquals('1985-07-04 01:30:00', $dt->format('Y-m-d H:i:s'));
        self::assertEquals('Europe/Amsterdam', $dt->getTimeZone()->getName());
    }

    public function testGetDateTimeDateInvalid(): void
    {
        $this->expectException(InvalidDataException::class);
        /**
         * @var DateTime<string, mixed> $elem
         */
        $elem = $this->vcal->createProperty('DTSTART', 'bla');
        $elem->getDateTime();
    }

    public function testGetDateTimeWeirdTZ(): void
    {
        /**
         * @var DateTime<string, mixed> $elem
         */
        $elem = $this->vcal->createProperty('DTSTART', '19850704T013000');
        $elem['TZID'] = '/freeassociation.sourceforge.net/Tzfile/Europe/Amsterdam';

        $event = $this->vcal->createComponent('VEVENT');
        $event->add($elem);

        /**
         * @var VTimeZone<int, mixed> $timezone
         */
        $timezone = $this->vcal->createComponent('VTIMEZONE');
        /** @var FlatText<mixed, mixed> $property */
        $property = $this->vcal->createProperty('TZID');
        $property->setValue('/freeassociation.sourceforge.net/Tzfile/Europe/Amsterdam');
        $timezone->TZID = $property;
        $timezone->{'X-LIC-LOCATION'} = 'Europe/Amsterdam';

        $this->vcal->add($event);
        $this->vcal->add($timezone);

        $dt = $elem->getDateTime();

        self::assertInstanceOf('DateTimeImmutable', $dt);
        self::assertEquals('1985-07-04 01:30:00', $dt->format('Y-m-d H:i:s'));
        self::assertEquals('Europe/Amsterdam', $dt->getTimeZone()->getName());
    }

    public function testGetDateTimeBadTimeZone(): void
    {
        $default = date_default_timezone_get();
        date_default_timezone_set('Canada/Eastern');

        /**
         * @var DateTime<string, mixed> $elem
         */
        $elem = $this->vcal->createProperty('DTSTART', '19850704T013000');
        $elem['TZID'] = 'Moon';

        $event = $this->vcal->createComponent('VEVENT');
        $event->add($elem);

        /**
         * @var VTimeZone<int, mixed> $timezone
         */
        $timezone = $this->vcal->createComponent('VTIMEZONE');
        /** @var FlatText<mixed, mixed> $property */
        $property = $this->vcal->createProperty('TZID');
        $property->setValue('Moon');
        $timezone->TZID = $property;
        $timezone->{'X-LIC-LOCATION'} = 'Moon';

        $this->vcal->add($event);
        $this->vcal->add($timezone);

        $dt = $elem->getDateTime();

        self::assertInstanceOf('DateTimeImmutable', $dt);
        self::assertEquals('1985-07-04 01:30:00', $dt->format('Y-m-d H:i:s'));
        self::assertEquals('Canada/Eastern', $dt->getTimeZone()->getName());
        date_default_timezone_set($default);
    }

    public function testUpdateValueParameter(): void
    {
        $dtStart = $this->vcal->createProperty('DTSTART', new \DateTime('2013-06-07 15:05:00'));
        $dtStart['VALUE'] = 'DATE';

        self::assertEquals("DTSTART;VALUE=DATE:20130607\r\n", $dtStart->serialize());
    }

    public function testValidate(): void
    {
        $exDate = $this->vcal->createProperty('EXDATE', '-00011130T143000Z');
        $messages = $exDate->validate();
        self::assertCount(1, $messages);
        self::assertEquals(3, $messages[0]['level']);
    }

    /**
     * This issue was discovered on the sabredav mailing list.
     */
    public function testCreateDatePropertyThroughAdd(): void
    {
        $vcal = new VCalendar();
        /** @var VEvent<int, mixed> $vevent */
        $vevent = $vcal->add('VEVENT');

        $dtstart = $vevent->add(
            'DTSTART',
            new \DateTime('2014-03-07'),
            ['VALUE' => 'DATE']
        );

        self::assertEquals("DTSTART;VALUE=DATE:20140307\r\n", $dtstart->serialize());
    }
}
