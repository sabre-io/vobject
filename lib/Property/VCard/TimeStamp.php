<?php

namespace Sabre\VObject\Property\VCard;

use Sabre\VObject\DateTimeParser;
use Sabre\VObject\InvalidDataException;
use Sabre\VObject\Property\Text;
use Sabre\Xml;

/**
 * TimeStamp property.
 *
 * This object encodes TIMESTAMP values.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class TimeStamp extends Text
{
    /**
     * In case this is a multi-value property. This string will be used as a
     * delimiter.
     */
    public string $delimiter = '';

    /**
     * Returns the type of value.
     *
     * This corresponds to the VALUE= parameter. Every property also has a
     * 'default' valueType.
     */
    public function getValueType(): string
    {
        return 'TIMESTAMP';
    }

    /**
     * Returns the value, in the format it should be encoded for json.
     *
     * This method must always return an array.
     *
     * @throws InvalidDataException
     */
    public function getJsonValue(): array
    {
        $parts = DateTimeParser::parseVCardDateTime($this->getValue());

        $dateStr =
            $parts['year'].'-'.
            $parts['month'].'-'.
            $parts['date'].'T'.
            $parts['hour'].':'.
            $parts['minute'].':'.
            $parts['second'];

        // Timezone
        if (!is_null($parts['timezone'])) {
            $dateStr .= $parts['timezone'];
        }

        return [$dateStr];
    }

    /**
     * This method serializes only the value of a property. This is used to
     * create xCard or xCal documents.
     */
    protected function xmlSerializeValue(Xml\Writer $writer): void
    {
        // xCard is the only XML and JSON format that has the same date and time
        // format than vCard.
        $valueType = strtolower($this->getValueType());
        $writer->writeElement($valueType, $this->getValue());
    }
}
