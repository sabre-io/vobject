<?php

namespace Sabre\VObject;

/**
 * Document
 *
 * A document is just like a component, except that it's also the top level
 * element.
 *
 * Both a VCALENDAR and a VCARD are considered documents.
 *
 * This class also provides a registry for document types.
 *
 * @copyright Copyright (C) 2007-2013 fruux GmbH. All rights reserved.
 * @author Evert Pot (http://evertpot.com/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
abstract class Document extends Component {

    /**
     * Unknown document type
     */
    const UNKNOWN = 1;

    /**
     * vCalendar 1.0
     */
    const VCALENDAR10 = 2;

    /**
     * iCalendar 2.0
     */
    const ICALENDAR20 = 3;

    /**
     * vCard 2.1
     */
    const VCARD21 = 4;

    /**
     * vCard 3.0
     */
    const VCARD30 = 5;

    /**
     * vCard 4.0
     */
    const VCARD40 = 6;

    /**
     * The default name for this component.
     *
     * This should be 'VCALENDAR' or 'VCARD'.
     *
     * @var string
     */
    static $defaultName;

    /**
     * List of properties, and which classes they map to.
     *
     * @var array
     */
    public $propertyMap = array();

    /**
     * Creates a new document

     * @return void
     */
    public function __construct() {

        parent::__construct($this, static::$defaultName, array());

    }

    /**
     * Returns the current document type.
     *
     * @return void
     */
    public function getDocumentType() {

        return self::UNKNOWN;

    }

    /**
     * Creates a new component
     *
     * This method automatically searches for the correct component class, based
     * on its name.
     *
     * You can specify the children either in key=>value syntax, in which case
     * properties will automatically be created, or you can just pass a list of
     * Component and Property object.
     *
     * @param string $name
     * @param array $children
     * @return Component
     */
    public function createComponent($name, array $children = array()) {

        $name = strtoupper($name);
        $class = 'Sabre\\VObject\\Component';

        if (isset($this->componentMap[$name])) {
            $class.='\\' . $this->componentMap[$name];
        }
        return new $class($this, $name, $children);

    }

    /**
     * Factory method for creating new properties
     *
     * This method automatically searches for the correct property class, based
     * on its name.
     *
     * You can specify the parameters either in key=>value syntax, in which case
     * parameters will automatically be created, or you can just pass a list of
     * Parameter objects.
     *
     * @param string $name
     * @param mixed $value
     * @param array $parameters
     * @return Property
     */
    public function createProperty($name, $value = null, array $parameters = array()) {

        $name = strtoupper($name);
        $class = 'Sabre\\VObject\\Property';

        if (isset($this->propertyMap[$name])) {
            $class.='\\' . $this->propertyMap[$name];
        } else {
            $class.='\\Text';
        }
        return new $class($this, $name, $value, $parameters);

    }

    /**
     * Factory method for creating new parameters
     *
     * This method automatically searches for the correct parameter class, based
     * on its name.
     *
     * @param string $name
     * @param string|array|null $value
     * @return Parameter
     */
    public function createParameter($name, $value = null) {

        return new Parameter($this, $name, $value);

    }

}
