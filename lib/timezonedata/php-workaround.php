<?php
/**
 * A list of PHP timezones that were supported in PHP 5.5.9, but are no longer
 * in PHP 5.5.10.
 *
 * They are still returned by DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC),
 * but are invalid for new DateTimeZone(). Fixed in PHP 5.5.17.
 * https://bugs.php.net/bug.php?id=66985
 *
 * Some more info here:
 * http://evertpot.com/php-5-5-10-timezone-changes/
 *
 * @copyright Copyright (C) 2007-2014 fruux GmbH (https://fruux.com/).
 * @license http://sabre.io/license/ Modified BSD License
 */
return array(
    'CST6CDT'   => 'America/Chicago',
    'Cuba'      => 'America/Havana',
    'Egypt'     => 'Africa/Cairo',
    'Eire'      => 'Europe/Dublin',
    'EST5EDT'   => 'America/New_York',
    'Factory'   => 'UTC',
    'GB-Eire'   => 'Europe/London',
    'GMT0'      => 'UTC',
    'Greenwich' => 'UTC',
    'Hongkong'  => 'Asia/Hong_Kong',
    'Iceland'   => 'Atlantic/Reykjavik',
    'Iran'      => 'Asia/Tehran',
    'Israel'    => 'Asia/Jerusalem',
    'Jamaica'   => 'America/Jamaica',
    'Japan'     => 'Asia/Tokyo',
    'Kwajalein' => 'Pacific/Kwajalein',
    'Libya'     => 'Africa/Tripoli',
    'MST7MDT'   => 'America/Denver',
    'Navajo'    => 'America/Denver',
    'NZ-CHAT'   => 'Pacific/Chatham',
    'Poland'    => 'Europe/Warsaw',
    'Portugal'  => 'Europe/Lisbon',
    'PST8PDT'   => 'America/Los_Angeles',
    'Singapore' => 'Asia/Singapore',
    'Turkey'    => 'Europe/Istanbul',
    'Universal' => 'UTC',
    'W-SU'      => 'Europe/Moscow',
    'Zulu'      => 'UTC',
);
