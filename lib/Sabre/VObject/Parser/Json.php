<?php

namespace Sabre\VObject\Parser;

use
    Sabre\VObject\Document\VCalendar,
    Sabre\VObject\Document\VCard;

/**
 * Json Parser.
 *
 * This parser parses both the jCal and jCard formats.
 *
 * @copyright Copyright (C) 2007-2013 fruux GmbH. All rights reserved.
 * @author Evert Pot (http://evertpot.com/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class Json extends Parser {

    /**
     * The input data
     *
     * @var array
     */
    protected $input;

    /**
     * Root component
     *
     * @var Document
     */
    protected $root;

    /**
     * This method starts the parsing process.
     *
     * If the input was not supplied during construction, it's possible to pass
     * it here instead.
     *
     * If either input or options are not supplied, the defaults will be used.
     *
     * @param resource|string|array|null $input
     * @param int|null $options
     * @return array
     */
    public function parse($input = null, $options = null) {

        if (!is_null($input)) {
            $this->setInput($input);
        }

        if (!is_null($options)) {
            $this->options = $options;
        }

        switch($input[0]) {
            case 'vcalendar' :
                $this->root = new VCalendar(array(), false);
                break;
            case 'vcard' :
                $this->root = new VCard(array(), false);
                break;
            default :
                throw new ParseException('The root component must either be a vcalendar, or a vcard');

        }
        foreach($input[1] as $prop) {
            $this->root->add($this->parseProperty($prop));
        }
        if (isset($input[2])) foreach($input[2] as $comp) {
            $this->root->add($this->parseComponent($comp));
        }

        return $this->root;

    }

    /**
     * Parses a component
     *
     * @param array $jComp
     * @return \Sabre\VObject\Component
     */
    protected function parseComponent(array $jComp) {

        // We can remove $self from PHP 5.4 onward.
        $self = $this;

        $properties = array_map(function($jProp) use ($self) {
            return $self->parseProperty($jProp);
        }, $jComp[1]);

        if (isset($jComp[2])) {

            $components = array_map(function($jComp) use ($self) {
                return $self->parseComponent($jProp);
            }, $jComp[2]);

        } else $components = array();

        return $this->root->createComponent(
            $jComp[0],
            array_merge( $properties, $components),
            $defaults = false
        );

    }

    /**
     * Parses properties.
     *
     * @param array $jProp
     * @return \Sabre\VObject\Property
     */
    protected function parseProperty(array $jProp) {

        list(
            $propertyName,
            $parameters,
            $valueType
        ) = $jProp;

        $value = array_slice($jProp, 3);

        if (count($value)===1) {
            $value = $value[0];
        }

        $parameters['VALUE'] = strtoupper($valueType);

        if (isset($parameters['group'])) {
            $propertyName = $parameters['group'] . '.' . $propertyName;
            unset($parameters['group']);
        }

        return $this->root->createProperty($propertyName, $value, $parameters);

    }

    /**
     * Sets the input data
     *
     * @param resource|string|array $input
     * @return void
     */
    public function setInput($input) {

        if (is_resource($input)) {
            $input = stream_get_contents($input);
        }
        if (is_string($input)) {
            $input = json_decode($input);
        }
        $this->input = $input;

    }

}
