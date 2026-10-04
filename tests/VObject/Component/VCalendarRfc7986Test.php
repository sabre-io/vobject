<?php

namespace Sabre\VObject\Component;

use PHPUnit\Framework\TestCase;
use Sabre\VObject;

class VCalendarRfc7986Test extends TestCase
{
    private function calendar(): VCalendar
    {
        $calendar = new VCalendar();
        $calendar->add('VEVENT', ['DTSTART' => '20261004T120000Z']);

        return $calendar;
    }

    public function testCalendarPropertiesAndRoundTrip(): void
    {
        $calendar = $this->calendar();
        $calendar->add('NAME', 'Company Vacation Days', ['LANGUAGE' => 'en']);
        $calendar->add('DESCRIPTION', 'Company holidays', ['LANGUAGE' => 'en']);
        $calendar->add('UID', '5FC53010-1267-4F8E-BC28-1D7AE55A7C99');
        $calendar->add('LAST-MODIFIED', '20261001T120000Z');
        $calendar->add('URL', 'https://example.com/holidays');
        $calendar->add('CATEGORIES', ['Holidays', 'Company']);
        $calendar->add('REFRESH-INTERVAL', 'P1W', ['VALUE' => 'DURATION']);
        $calendar->add('SOURCE', 'https://example.com/holidays.ics', ['VALUE' => 'URI']);
        $calendar->add('COLOR', 'turquoise');
        $calendar->add('IMAGE', 'https://example.com/images/party.png', [
            'VALUE' => 'URI', 'DISPLAY' => ['BADGE', 'THUMBNAIL'], 'FMTTYPE' => 'image/png',
        ]);
        $parsed = VObject\Reader::read($calendar->serialize());
        self::assertInstanceOf(VObject\Property\FlatText::class, $parsed->NAME);
        self::assertInstanceOf(VObject\Property\FlatText::class, $parsed->COLOR);
        self::assertSame('DURATION', $parsed->{'REFRESH-INTERVAL'}->getValueType());
        self::assertSame('URI', $parsed->SOURCE->getValueType());
        self::assertSame('URI', $parsed->IMAGE->getValueType());
        self::assertSame([], $parsed->validate());
        self::assertSame($calendar->serialize(), $parsed->serialize());
        $json = VObject\Writer::writeJson($calendar);
        self::assertSame($json, VObject\Writer::writeJson(VObject\Reader::readJson($json)));
    }

    public function testPropertiesWithoutDefaultTypesRetainValueParameters(): void
    {
        $calendar = $this->calendar();
        $calendar->add('REFRESH-INTERVAL', 'P1W', ['VALUE' => 'DURATION']);
        $calendar->add('SOURCE', 'https://example.com/calendar.ics', ['VALUE' => 'URI']);
        $calendar->add('IMAGE', 'image bytes', ['VALUE' => 'BINARY', 'ENCODING' => 'BASE64', 'FMTTYPE' => 'image/png']);
        $calendar->VEVENT->add('CONFERENCE', 'https://example.com/meeting', ['VALUE' => 'URI']);
        $parsed = VObject\Reader::readJson(VObject\Writer::writeJson($calendar));
        self::assertSame('DURATION', (string) $parsed->{'REFRESH-INTERVAL'}['VALUE']);
        self::assertSame('URI', (string) $parsed->SOURCE['VALUE']);
        self::assertSame('BINARY', (string) $parsed->IMAGE['VALUE']);
        self::assertSame('image bytes', $parsed->IMAGE->getValue());
        self::assertSame('URI', (string) $parsed->VEVENT->CONFERENCE['VALUE']);
        self::assertSame(VObject\Writer::writeJson($calendar), VObject\Writer::writeJson($parsed));
    }

    public function testCalendarSingletonCardinalities(): void
    {
        $properties = [
            'UID' => ['calendar-id', []],
            'LAST-MODIFIED' => ['20261001T120000Z', []],
            'URL' => ['https://example.com/calendar', []],
            'REFRESH-INTERVAL' => ['P1W', ['VALUE' => 'DURATION']],
            'SOURCE' => ['https://example.com/calendar.ics', ['VALUE' => 'URI']],
            'COLOR' => ['turquoise', []],
        ];
        foreach ($properties as $name => [$value, $parameters]) {
            $calendar = $this->calendar();
            $calendar->add($name, $value, $parameters);
            self::assertSame([], $calendar->validate(), $name);
            $calendar->add($name, $value, $parameters);
            self::assertContains(
                $name.' MUST NOT appear more than once in a VCALENDAR component',
                array_column($calendar->validate(), 'message')
            );
        }
    }

    public function testRepeatableCalendarProperties(): void
    {
        $calendar = $this->calendar();
        foreach (['en', 'de'] as $language) {
            $calendar->add('NAME', 'Calendar', ['LANGUAGE' => $language]);
            $calendar->add('DESCRIPTION', 'Description', ['LANGUAGE' => $language]);
        }
        $calendar->add('CATEGORIES', ['Company']);
        $calendar->add('CATEGORIES', ['Holidays']);
        $calendar->add('IMAGE', 'https://example.com/one.png', ['VALUE' => 'URI']);
        $calendar->add('IMAGE', 'https://example.com/two.png', ['VALUE' => 'URI']);
        self::assertSame([], $calendar->validate());
        foreach (['NAME', 'DESCRIPTION', 'CATEGORIES', 'IMAGE'] as $name) {
            self::assertCount(2, $calendar->select($name));
        }
    }

    public function testComponentPropertiesAndColorCardinality(): void
    {
        foreach (['VEVENT', 'VTODO', 'VJOURNAL'] as $name) {
            $calendar = new VCalendar();
            $component = $calendar->add($name, ['DTSTART' => '20261004T120000Z']);
            $component->add('COLOR', 'turquoise');
            $component->add('IMAGE', 'https://example.com/one.png', ['VALUE' => 'URI']);
            $component->add('IMAGE', 'https://example.com/two.png', ['VALUE' => 'URI']);
            if ('VJOURNAL' !== $name) {
                $component->add('CONFERENCE', 'https://example.com/meeting', [
                    'VALUE' => 'URI', 'FEATURE' => ['AUDIO', 'VIDEO'], 'LABEL' => 'Attendee dial-in',
                ]);
                $component->add('CONFERENCE', 'xmpp:chat@example.com', ['VALUE' => 'URI', 'FEATURE' => 'CHAT']);
                $component->add('ATTENDEE', 'mailto:opaque-token@example.com', ['EMAIL' => 'person@example.com']);
                $parsed = VObject\Reader::read($calendar->serialize());
                $parsedComponent = $parsed->{$name};
                self::assertSame('URI', $parsedComponent->CONFERENCE->getValueType());
                self::assertTrue($parsedComponent->CONFERENCE['FEATURE']->has('VIDEO'));
                self::assertSame('Attendee dial-in', (string) $parsedComponent->CONFERENCE['LABEL']);
                self::assertSame('person@example.com', (string) $parsedComponent->ATTENDEE['EMAIL']);
                self::assertCount(2, $parsedComponent->select('CONFERENCE'));
            }
            self::assertSame([], $calendar->validate(), $name);
            $component->add('COLOR', 'red');
            self::assertContains(
                'COLOR MUST NOT appear more than once in a '.$name.' component',
                array_column($calendar->validate(), 'message')
            );
        }
    }
}
