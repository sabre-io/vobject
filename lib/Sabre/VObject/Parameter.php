<?php

namespace Sabre\VObject;

/**
 * VObject Parameter
 *
 * This class represents a parameter. A parameter is always tied to a property.
 * In the case of:
 *   DTSTART;VALUE=DATE:20101108
 * VALUE=DATE would be the parameter name and value.
 *
 * @copyright Copyright (C) 2007-2013 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class Parameter extends Node {

    /**
     * Parameter name
     *
     * @var string
     */
    public $name;

    /**
     * Parameter value
     *
     * @var string
     */
    protected $value;

    /**
     * Creates a new parameter
     *
     * @param string $name
     * @param string|array $value
     * @return Parameter
     */
    static public function create($name, $value) {

        return NodeFactory::createParameter($name, $value);

    }

    /**
     * Sets up the object.
     *
     * It's recommended to use the create:: factory method instead.
     *
     * @param string $name
     * @param string $value
     */
    public function __construct($name, $value = null) {

        $this->name = strtoupper($name);
        $this->setValue($value);

    }

    /**
     * Updates the current value.
     *
     * This may be either a single, or multiple strings in an array.
     *
     * @param string|array $value
     * @return void
     */
    public function setValue($value) {

        $this->value = $value;

    }

    /**
     * Returns the current value
     *
     * This may be either a single, or multiple strings in an array.
     *
     * @param string|array $value
     * @return void
     */
    public function getValue() {

        return $this->value;

    }

    /**
     * Turns the object back into a serialized blob.
     *
     * @return string
     */
    public function serialize() {

        $value = $this->getValue();

        if (is_null($this->value)) {
            return $this->name;
        }

        // If there's no special characters in the string, we'll use the simple
        // format
        if (!preg_match('#(?: [\n":;^] )#x', $value)) {
            return $this->name . '=' . $value;
        } else {
            // Enclosing in double-quotes, and using RFC6868 for encoding any
            // special characters
            $value = strtr(array(
                '^'  => '^^',
                "\n" => '^n',
                '"'  => '^',
            ), $value);
            return $this->name . '="' . $value . '"';
        }

    }

    /**
     * Called when this object is being cast to a string
     *
     * @return string
     */
    public function __toString() {

        return $this->getValue();

    }

}
