<?php

namespace Sabre\VObject\Component;

use Sabre\VObject;

/**
 * The VAvailability component
 *
 * This component adds functionality to a component, specific for VAVAILABILITY
 * components.
 *
 * @copyright Copyright (C) 2011-2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class VAvailability extends VObject\Component {

    /**
     * A simple list of validation rules.
     *
     * This is simply a list of properties, and how many times they either
     * must or must not appear.
     *
     * Possible values per property:
     *   * 0 - Must not appear.
     *   * 1 - Must appear exactly once.
     *   * + - Must appear at least once.
     *   * * - Can appear any number of times.
     *   * ? - May appear, but not more than once.
     *
     * @var array
     */
    function getValidationRules() {

        return [
            'UID' => 1,
            'DTSTAMP' => 1,

            'BUSYTYPE' => '?',
            'CLASS' => '?',
            'CREATED' => '?',
            'DESCRIPTION' => '?',
            'DTSTART' => '?',
            'LAST-MODIFIED' => '?',
            'ORGANIZER' => '?',
            'PRIORITY' => '?',
            'SEQUENCE' => '?',
            'SUMMARY' => '?',
            'URL' => '?',
            'DTEND' => '?',
            'DURATION' => '?',

            'CATEGORIES' => '*',
            'COMMENT' => '*',
            'CONTACT' => '*',
        ];

    }
}
