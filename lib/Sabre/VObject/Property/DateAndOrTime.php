<?php

namespace Sabre\VObject\Property;

use
    Sabre\VObject\DateTimeParser;

/**
 * DateAndOrTime property
 *
 * This object encodes DATE-AND-OR-TIME values.
 *
 * @copyright Copyright (C) 2007-2013 fruux GmbH. All rights reserved.
 * @author Evert Pot (http://evertpot.com/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class DateAndOrTime extends Text {

    protected $delimiter = null;

    /**
     * Returns the type of value.
     *
     * This corresponds to the VALUE= parameter. Every property also has a
     * 'default' valueType.
     *
     * @return string
     */
    public function getValueType() {

        return "DATE-AND-OR-TIME";

    }

    /**
     * Returns the value, in the format it should be encoded for json.
     *
     * This method must always return an array.
     *
     * @return array
     */
    public function getJsonValue() {

        $parts = DateTimeParser::parseVCardDateTime($this->getValue());

        $dateStr = '';

        // Year
        if (!is_null($parts['year'])) {
            $dateStr.=$parts['year'];

            if (!is_null($parts['month'])) {
                // If a year and a month is set, we need to insert a separator
                // dash.
                $dateStr.='-';
            }

        } else {

            if (!is_null($parts['month']) || !is_null($parts['date'])) {
                // Inserting two dashes
                $dateStr.='--';
            }

        }

        // Month

        if (!is_null($parts['month'])) {
            $dateStr.=$parts['month'];

            if (isset($parts['date'])) {
                // If month and date are set, we need the separator dash.
                $dateStr.='-';
            }
        } else {
            if (isset($parts['date'])) {
                // If the month is empty, and a date is set, we need a 'empty
                // dash'
                $dateStr.='-';
            }
        }

        // Date
        if (!is_null($parts['date'])) {
            $dateStr.=$parts['date'];
        }


        // Early exit if we don't have a time string.
        if (is_null($parts['hour']) && is_null($parts['minute']) && is_null($parts['second'])) {
            return array($dateStr);
        }

        $dateStr.='T';

        // Hour
        if (!is_null($parts['hour'])) {
            $dateStr.=$parts['hour'];

            if (!is_null($parts['minute'])) {
                $dateStr.=':';
            }
        } else {
            // We know either minute or second _must_ be set, so we insert a
            // dash for an empty value.
            $dateStr.='-';
        }

        // Minute
        if (!is_null($parts['minute'])) {
            $dateStr.=$parts['minute'];

            if (!is_null($parts['second'])) {
                $dateStr.=':';
            }
        } else {
            if (isset($parts['second'])) {
                // Dash for empty minute
                $dateStr.='-';
            }
        }

        // Second
        if (!is_null($parts['second'])) {
            $dateStr.=$parts['second'];
        }

        // Timezone
        if (!is_null($parts['timezone'])) {
            $dateStr.=$parts['timezone'];
        }

        return array($dateStr);

    }

    /**
     * This method returns an array, with the representation as it should be
     * encoded in json. This is used to create jCard or jCal documents.
     *
     * We are overriding this, because there is no DATE-AND-OR-TIME value in
     * jCard. Instead, we need to check what the type is, and encode it as
     * DATE-TIME, DATE or TIME.
     *
     * @return array
     */
    public function jsonSerialize() {

        $parameters = array();

        foreach($this->parameters as $parameter) {
            if ($parameter->name === 'VALUE') {
                continue;
            }
            $parameters[$parameter->name] = $parameter->jsonSerialize();
        }
        // In jCard, we need to encode the property-group as a separate 'group'
        // parameter.
        if ($this->group) {
            $parameters['group'] = $this->group;
        }

        $valueType = 'date-time';
        $value = $this->getJsonValue()[0];

        $tPos = strpos($value, 'T');
        // If the string starts with a T, it's a time-only value.
        if ($tPos === 0) {
            $valueType = 'time';
        // If there's no T in the string it all, it's a date-only value.
        } elseif ($tPos === false) {
            $valueType = 'date';
        }

        return array_merge(
            array(
                strtolower($this->name),
                (object)$parameters,
                $valueType,
            ),
            array($value)
        );
    }
}
