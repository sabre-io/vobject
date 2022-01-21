<?php

namespace Sabre\VObject\Property\VCard;

use Sabre\VObject\Property;

/**
 * PhoneNumber property.
 *
 * This object encodes PHONE-NUMBER values.
 *
 * @author Christian Kraus <christian@kraus.work>
 */
class PhoneNumber extends Property\Text
{
    /** @var array */
    protected $structuredValues = [];

    /**
     * Returns the type of value.
     *
     * This corresponds to the VALUE= parameter. Every property also has a
     * 'default' valueType.
     */
    public function getValueType(): string
    {
        return 'PHONE-NUMBER';
    }
}
