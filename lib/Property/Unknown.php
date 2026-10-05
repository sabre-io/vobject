<?php

namespace Sabre\VObject\Property;

/**
 * Unknown property.
 *
 * This object represents any properties not recognized by the parser.
 * This type of value has been introduced by the jCal, jCard specs.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class Unknown extends Text
{
    /**
     * The value as it appeared in the document.
     *
     * The escaping rules of an unknown property are unknown too, so its value
     * must be written back unprocessed (RFC 7095, section 5). It is null once
     * the value is set through setValue() or setParts().
     */
    private ?string $rawValue = null;

    /**
     * Sets a raw value coming from a mimedir (iCalendar/vCard) file.
     */
    public function setRawMimeDirValue(string $val): void
    {
        parent::setRawMimeDirValue($val);
        $this->rawValue = $val;
    }

    /**
     * Sets the value as a quoted-printable encoded string.
     *
     * Once decoded, the value is kept as is: it is not split, as its delimiter
     * is unknown.
     */
    public function setQuotedPrintableValue(string $val): void
    {
        $this->setRawMimeDirValue(quoted_printable_decode($val));
    }

    /**
     * Updates the current value.
     *
     * @param string|array|null $value
     */
    public function setValue($value): void
    {
        $this->rawValue = null;
        parent::setValue($value);
    }

    /**
     * Sets a multi-valued property.
     */
    public function setParts(array $parts): void
    {
        $this->rawValue = null;
        parent::setParts($parts);
    }

    /**
     * Returns a raw mime-dir representation of the value.
     */
    public function getRawMimeDirValue(): string
    {
        return $this->rawValue ?? parent::getRawMimeDirValue();
    }

    /**
     * Returns the value, in the format it should be encoded for json.
     *
     * This method must always return an array.
     */
    public function getJsonValue(): array
    {
        return [$this->getRawMimeDirValue()];
    }

    /**
     * Sets the JSON value, as it would appear in a jCard or jCal object.
     *
     * A value of the "unknown" type is the unprocessed value text (RFC 7095,
     * section 5).
     */
    public function setJsonValue(array $value): void
    {
        if (1 === count($value) && is_string(reset($value))) {
            $this->setRawMimeDirValue(reset($value));

            return;
        }

        parent::setJsonValue($value);
    }

    /**
     * Returns the type of value.
     *
     * This corresponds to the VALUE= parameter. Every property also has a
     * 'default' valueType.
     */
    public function getValueType(): string
    {
        return 'UNKNOWN';
    }
}
