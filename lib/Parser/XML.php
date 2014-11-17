<?php

namespace Sabre\VObject\Parser;

use
    Sabre\VObject\Component\VCalendar,
    Sabre\VObject\Component\VCard,
    Sabre\VObject\EofException,
    Sabre\XML as SabreXML,
    DateTime;

/**
 * XML Parser.
 *
 * This parser parses both the xCal and xCard formats.
 *
 * @copyright Copyright (C) 2007-2014 fruux GmbH. All rights reserved.
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class XML extends Parser {

    const XCAL_NAMESPACE  = 'urn:ietf:params:xml:ns:icalendar-2.0';
    const XCARD_NAMESPACE = 'urn:ietf:params:xml:ns:vcard-4.0';

    /**
     * The input data.
     *
     * @var array
     */
    protected $input;

    /**
     * A pointer/reference to the input.
     *
     * @var array
     */
    private $pointer;

    /**
     * Document, root component.
     *
     * @var Sabre\VObject\Document
     */
    protected $root;

    /**
     *
     * @param resource|string $input
     */
    public function parse ( $input = null, $options = null ) {

        if(!is_null($input))
            $this->setInput($input);

        if(is_null($this->input))
            throw new EofException('End of input stream, or no input supplied');

        if($this->input['name'] === '{' . self::XCAL_NAMESPACE . '}icalendar') {

            $this->root = new VCalendar([], false);
            $this->pointer = &$this->input['value'][0];
            $this->parseVcalendarComponents($this->root, $options);
        }
        else
            throw new \Exception('Arg');

        return $this->root;
    }

    protected function parseVcalendarComponents ( $parentComponent, $options = null ) {

        foreach($this->pointer['value'] as $children) {

            switch(static::getTagName($children['name'])) {

                // Properties.
                case 'properties':
                    $xmlProperties = $children['value'];

                    foreach($xmlProperties as $xmlProperty) {

                        // Property.
                        $propertyName        = static::getTagName($xmlProperty['name']);
                        $xmlPropertyChildren = $xmlProperty['value'];
                        $property            = $this->root->createProperty(
                            $propertyName
                        );
                        $parentComponent->add($property);

                        /*
                        switch($propertyName) {

                            // special cases
                            case 'categories':
                            case 'resources':
                            case 'freebusy':
                            case 'exdate':
                            case 'rdate':
                              break;

                            case 'geo':
                              break;

                            case 'request-status':
                              break;

                            default:
                              break;
                        }
                        */

                        foreach($xmlPropertyChildren as $xmlPropertyChild) {

                            $xmlPropertyChildName = static::getTagName($xmlPropertyChild['name']);

                            // Parameters.
                            if('parameters' === $xmlPropertyChildName) {

                                $xmlParameters = $xmlPropertyChild['value'];

                                foreach($xmlParameters as $xmlParameter) {

                                    $property->add(
                                        static::getTagName($xmlParameter['name']),
                                        $xmlParameter['value'][0]['value']
                                    );
                                }

                                continue;
                            }

                            // Property type and value(s).
                            $propertyType     = $xmlPropertyChildName;
                            $xmlPropertyValue = $xmlPropertyChild['value'];

                            switch($propertyType) {

                                case 'binary':
                                case 'boolean':
                                case 'duration':
                                case 'float':
                                case 'integer':
                                    $property->setRawMimeDirValue($xmlPropertyValue);
                                  break;

                                case 'cal-address':
                                case 'text':
                                case 'uri':
                                    $property->setValue($xmlPropertyValue);
                                  break;

                                case 'date':
                                    $property->setValue(DateTime::createFromFormat(
                                        'Y-m-d',
                                        $xmlPropertyValue
                                        // TODO: TimeZone? TZID
                                    ));
                                  break;

                                case 'date-time':
                                    $xmlPropertyValue = rtrim($xmlPropertyValue, 'Z');
                                    $property->setValue(DateTime::createFromFormat(
                                        'Y-m-d\TH:i:s',
                                        $xmlPropertyValue
                                        // TODO: TimeZone? TZID
                                    ));
                                  break;

                                case 'period':
                                    $periodStart         = null;
                                    $periodEndOrDuration = null;

                                    foreach($xmlPropertyValue as $xmlPeriodChild) {

                                        $xmlPeriodValue = $xmlPeriodChild['value'];

                                        switch(static::getTagName($xmlPeriodChild['name'])) {

                                            case 'start':
                                                $periodStart = $xmlPeriodValue;
                                              break;

                                            case 'end':
                                            case 'duration':
                                                $periodEndOrDuration = $xmlPeriodValue;
                                              break;

                                            default:
                                                // TODO: EXCEPTION
                                              break;
                                        }
                                    }

                                    $property->setRawMimeDirValue(
                                        $periodStart .
                                        '/' .
                                        $periodEndOrDuration
                                    );
                                  break;

                                case 'recur':
                                    $recur = [];

                                    foreach($xmlPropertyValue as $xmlRecurChild) {

                                        $xmlRecurName  = static::getTagName($xmlRecurChild['name']);
                                        $xmlRecurValue = $xmlRecurChild['value'];

                                        if('until' === $xmlRecurName)
                                            $xmlRecurName = str_replace(
                                                ['-', ':'],
                                                '',
                                                $xmlRecurName
                                            );

                                        $recur[] = strtoupper($xmlRecurName) .
                                                   '=' .
                                                   $xmlRecurValue;
                                    }

                                    $property->setRawMimeDirValue(
                                        implode(';', $recur)
                                    );
                                  break;

                                case 'time':
                                case 'utc-offset':
                                    $property->setValue(
                                        str_replace(':', '', $xmlPropertyValue)
                                    );
                                  break;
                            }
                        }
                    }
                    break;

                case 'components':
                    $components = $children['value'];

                    foreach($components as $component) {

                        $componentName    = static::getTagName($component['name']);
                        $currentComponent = $this->root->createComponent(
                            $componentName
                        );

                        $this->pointer = &$component;
                        $this->parseVcalendarComponents(
                            $currentComponent,
                            $options
                        );

                        $parentComponent->add($currentComponent);
                    }
                    break;

                default:
                    throw new \Exception('Oops');
            }
        }
    }

    /**
     * Sets the input data.
     *
     * @param resource|string $input
     * @return void
     */
    public function setInput ( $input ) {

        if(is_resource($input))
            $input = stream_get_contents($input);

        if(is_string($input)) {

            $reader = new SabreXML\Reader();
            $reader->xml($input);
            $input  = $reader->parse();
        }

        $this->input = $input;

        return;
    }

    static protected function getTagName ( $clarkedTagName ) {

        list($namespace, $tagName) = SabreXML\Util::parseClarkNotation($clarkedTagName);

        return $tagName;
    }
}
