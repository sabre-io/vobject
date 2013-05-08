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
    public $value;

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
        $this->value = $value;

    }

    /**
     * Turns the object back into a serialized blob.
     *
     * @return string
     */
    public function serialize() {

        if (is_null($this->value)) {
            return $this->name;
        }
        $src = array(
            '\\',
            "\n",
            ';',
            ',',
        );
        $out = array(
            '\\\\',
            '\n',
            '\;',
            '\,',
        );

        return $this->name . '=' . str_replace($src, $out, $this->value);

    }

    /**
     * Called when this object is being cast to a string
     *
     * @return string
     */
    public function __toString() {

        return $this->value;

    }

}
