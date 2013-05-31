<?php

namespace Sabre\VObject\Property;

/**
 * TimeStamp property
 *
 * This object encodes TIMESTAMP values.
 *
 * @copyright Copyright (C) 2007-2013 fruux GmbH. All rights reserved.
 * @author Evert Pot (http://evertpot.com/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class TimeStamp extends Text {

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

        return "TIMESTAMP";

    }

    /**
     * Returns the value, in the format it should be encoded for json.
     *
     * This method must always return an array.
     *
     * @return array
     */
    public function getJsonValue() {

        throw new \Exception('This needs to be verified against the spec for correctness');

        $parts = DateTimeParser::parseVCardDateTime($this->getValue());

        $dateStr = $parts['year'] . '-' . $parts['month'] . '-' . $parts['date'];
        $dateStr.='T' . $parts['hour'] . ':' . $parts['minute'] . ':' . $parts['second'];

        if (!is_null($parts['timezone'])) {
            $dateStr.=$parts['timezone'];
        }

        return array($dateStr);

    }

    /**
     * This method returns an array, with the representation as it should be
     * encoded in json. This is used to create jCard or jCal documents.
     *
     * We need to override this method here, because the TIMESTAMP value does
     * not exist in jCard, and we need to encode it as 'DATE-TIME' instead.
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

        return array_merge(
            array(
                strtolower($this->name),
                (object)$parameters,
                'date-time',
            ),
            $this->getJsonValue()
        );
    }

}
