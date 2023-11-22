<?php

namespace Sabre\VObject;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VEvent;

class JCalTest extends TestCase
{
    public function testToJCal(): void
    {
        $cal = new Component\VCalendar();

        /** @var VEvent<int, mixed> $event */
        $event = $cal->add('VEVENT', [
            'UID' => 'foo',
            'DTSTART' => new \DateTime('2013-05-26 18:10:00Z'),
            'DURATION' => 'P1D',
            'CATEGORIES' => ['home', 'testing'],
            'CREATED' => new \DateTime('2013-05-26 18:10:00Z'),

            'ATTENDEE' => 'mailto:armin@example.org',
            'GEO' => [51.96668, 7.61876],
            'SEQUENCE' => 5,
            'FREEBUSY' => ['20130526T210213Z/PT1H', '20130626T120000Z/20130626T130000Z'],
            'URL' => 'http://example.org/',
            'TZOFFSETFROM' => '+0500',
            'RRULE' => ['FREQ' => 'WEEKLY', 'BYDAY' => ['MO', 'TU']],
        ], false);

        // Modifying DTSTART to be a date-only.
        $event->dtstart['VALUE'] = 'DATE';
        $event->add('X-BOOL', true, ['VALUE' => 'BOOLEAN']);
        $event->add('X-TIME', '08:00:00', ['VALUE' => 'TIME']);
        $event->add('ATTACH', 'attachment', ['VALUE' => 'BINARY']);
        $event->add('ATTENDEE', 'mailto:dominik@example.org', ['CN' => 'Dominik', 'PARTSTAT' => 'DECLINED']);

        $event->add('REQUEST-STATUS', ['2.0', 'Success']);
        $event->add('REQUEST-STATUS', ['3.7', 'Invalid Calendar User', 'ATTENDEE:mailto:jsmith@example.org']);

        $event->add('DTEND', '20150108T133000');

        $expected = [
            'vcalendar',
            [
                [
                    'version',
                    new \stdClass(),
                    'text',
                    '2.0',
                ],
                [
                    'prodid',
                    new \stdClass(),
                    'text',
                    '-//Sabre//Sabre VObject '.Version::VERSION.'//EN',
                ],
                [
                    'calscale',
                    new \stdClass(),
                    'text',
                    'GREGORIAN',
                ],
            ],
            [
                ['vevent',
                    [
                        [
                            'uid', new \stdClass(), 'text', 'foo',
                        ],
                        [
                            'dtstart', new \stdClass(), 'date', '2013-05-26',
                        ],
                        [
                            'duration', new \stdClass(), 'duration', 'P1D',
                        ],
                        [
                            'categories', new \stdClass(), 'text', 'home', 'testing',
                        ],
                        [
                            'created', new \stdClass(), 'date-time', '2013-05-26T18:10:00Z',
                        ],
                        [
                            'attendee', new \stdClass(), 'cal-address', 'mailto:armin@example.org',
                        ],
                        [
                            'attendee',
                            (object) [
                                'cn' => 'Dominik',
                                'partstat' => 'DECLINED',
                            ],
                            'cal-address',
                            'mailto:dominik@example.org',
                        ],
                        [
                            'geo', new \stdClass(), 'float', [51.96668, 7.61876],
                        ],
                        [
                            'sequence', new \stdClass(), 'integer', 5,
                        ],
                        [
                            'freebusy', new \stdClass(), 'period',  ['2013-05-26T21:02:13', 'PT1H'], ['2013-06-26T12:00:00', '2013-06-26T13:00:00'],
                        ],
                        [
                            'url', new \stdClass(), 'uri', 'http://example.org/',
                        ],
                        [
                            'tzoffsetfrom', new \stdClass(), 'utc-offset', '+05:00',
                        ],
                        [
                            'rrule', new \stdClass(), 'recur', [
                                'freq' => 'WEEKLY',
                                'byday' => ['MO', 'TU'],
                            ],
                        ],
                        [
                            'x-bool', new \stdClass(), 'boolean', true,
                        ],
                        [
                            'x-time', new \stdClass(), 'time', '08:00:00',
                        ],
                        [
                            'attach', new \stdClass(), 'binary', base64_encode('attachment'),
                        ],
                        [
                            'request-status',
                            new \stdClass(),
                            'text',
                            ['2.0', 'Success'],
                        ],
                        [
                            'request-status',
                            new \stdClass(),
                            'text',
                            ['3.7', 'Invalid Calendar User', 'ATTENDEE:mailto:jsmith@example.org'],
                        ],
                        [
                            'dtend',
                            new \stdClass(),
                            'date-time',
                            '2015-01-08T13:30:00',
                        ],
                    ],
                    [],
                ],
            ],
        ];

        self::assertEquals($expected, $cal->jsonSerialize());
    }
}
