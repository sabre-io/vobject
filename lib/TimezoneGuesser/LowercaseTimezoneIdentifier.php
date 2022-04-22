<?php

declare(strict_types=1);

namespace Sabre\VObject\TimezoneGuesser;

use DateTimeZone;

class LowercaseTimezoneIdentifier implements TimezoneFinder
{
    public function find(string $tzid, bool $failIfUncertain = false): ?DateTimeZone
    {
        foreach (DateTimeZone::listIdentifiers() as $timezone) {
            if (strtolower($tzid) === strtolower($timezone)) {
                return new DateTimeZone($timezone);
            }
        }

        return null;
    }
}
