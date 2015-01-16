<?php

namespace Sabre\VObject\Property;

use
    Sabre\VObject\Property;

/**
 * Float property
 *
 * This object represents FLOAT values. These can be 1 or more floating-point
 * numbers.
 *
 * @copyright Copyright (C) 2011-2015 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class Float extends Property {

    /**
     * In case this is a multi-value property. This string will be used as a
     * delimiter.
     *
     * @var string|null
     */
    public $delimiter = ';';

    /**
     * Sets a raw value coming from a mimedir (iCalendar/vCard) file.
     *
     * This has been 'unfolded', so only 1 line will be passed. Unescaping is
     * not yet done, but parameters are not included.
     *
     * @param string $val
     * @return void
     */
    public function setRawMimeDirValue($val) {

        $val = explode($this->delimiter, $val);
        foreach($val as &$item) {
            $item = (float)$item;
        }
        $this->setParts($val);

    }

    /**
     * Returns a raw mime-dir representation of the value.
     *
     * @return string
     */
    public function getRawMimeDirValue() {

        return implode(
            $this->delimiter,
            $this->getParts()
        );

    }

    /**
     * Returns the type of value.
     *
     * This corresponds to the VALUE= parameter. Every property also has a
     * 'default' valueType.
     *
     * @return string
     */
    public function getValueType() {

        return "FLOAT";

    }

    /**
     * Returns the value, in the format it should be encoded for JSON.
     *
     * This method must always return an array.
     *
     * @return array
     */
    public function getJsonValue() {

        $val = array_map('floatval', $this->getParts());

        // Special-casing the GEO property.
        //
        // See:
        // http://tools.ietf.org/html/draft-ietf-jcardcal-jcal-04#section-3.4.1.2
        if ($this->name === 'GEO') {
            return [$val];
        }

        return $val;

    }

    /**
     * Sets the XML value, as it would appear in a xCard or xCal object.
     *
     * The value must always be an array.
     *
     * @param array $value
     * @return void
     */
    function setXmlValue(array $value) {

        $value = array_map('floatval', $value);
        parent::setXmlValue($value);

    }

    /**
     * Returns the value, in the format it should be encoded for XML.
     *
     * This method must always return an array.
     *
     * @return array
     */
    public function getXmlValue() {

        $val = array_map('floatval', $this->getParts());

        // Special-casing the GEO property.
        //
        // See:
        // http://tools.ietf.org/html/rfc6321#section-3.4.1.2
        if ($this->name === 'GEO') {
            return [
                [
                    [
                        'name' => 'latitude',
                        'value' => $val[0]
                    ],
                    [
                        'name' => 'longitude',
                        'value' => $val[1]
                    ]
                ]
            ];
        }

        return $val;

    }

    /**
     * This method returns an array, with the representation as it should be
     * encoded in XML. This is used to create xCard or xCal documents.
     *
     * @return array
     */
    function xmlSerialize() {

        $serialization = parent::xmlSerialize();

        // Special-casing the GEO property.
        //
        // See:
        // http://tools.ietf.org/html/rfc6321#section-3.4.1.2
        if ($this->name === 'GEO') {

            $handle = $serialization['value'][0]['value'];
            $serialization['value'] = $handle;
            return $serialization;

        }

        return $serialization;

    }

}
