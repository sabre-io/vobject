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
     * List of components, along with which classes they map to.
     *
     * @var array
     */
    public $componentMap = array();

    /**
     * List of value-types, and which classes they map to.
     *
     * @var array
     */
    public $valueMap = array(
        'BINARY'           => 'Binary',
        'BOOLEAN'          => 'Boolean',
        'CONTENT-ID'       => 'FlatText', // vCard 2.1 only
        'CAL-ADDRESS'      => 'Uri',      // iCalendar only
        'DATE'             => 'DateTime',
        'DATE-TIME'        => 'DateTime',
        'DATE-AND-OR-TIME' => 'FlatText', // vCard only
        'DURATION'         => 'Duration', // iCalendar only
        'FLOAT'            => 'Text',
        'INTEGER'          => 'Integer',
        'LANGUAGE-TAG'     => 'FlatText', // vCard only
        'PERIOD'           => 'Period',   // iCalendar only
        'RECUR'            => 'Recur',    // iCalendar only
        'TIMESTAMP'        => 'FlatText', // vCard only
        'TEXT'             => 'Text',
        'TIME'             => 'Text',
        'URI'              => 'Uri',
        'URL'              => 'Uri', // vCard 2.1 only
        'UTC-OFFSET'       => 'FlatText',
    );

    /**
     * Creates a new document.
     *
     * We're changing the default behavior slightly here. First, we don't want
     * to have to specify a name (we already know it), and we want to allow
     * children to be specified in the first argument.
     *
     * But, the default behavior also works.
     *
     * So the two sigs:
     *
     * new Document(array $children = array());
     * new Document(string $name, array $children = array())
     *
     * @param string|array $name
     * @param array $children
     * @return void
     */
    public function __construct($name = array(), array $children = array()) {

        if (is_array($name)) {
            parent::__construct($this, static::$defaultName, $name);
        } else {
            // default
            parent::__construct($this, $name, $children);
        }

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
     * Creates a new component or property.
     *
     * If it's a known component, we will automatically call createComponent.
     * otherwise, we'll assume it's a property and call createProperty instead.
     *
     * @param string $name
     * @param string $arg1,... Unlimited number of args
     * @return mixed
     */
    public function create($name) {

        if (isset($this->componentMap[strtoupper($name)])) {

            return call_user_func_array(array($this,'createComponent'), func_get_args());

        } else {

            return call_user_func_array(array($this,'createProperty'), func_get_args());

        }

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

        // If there's a . in the name, it means it's prefixed by a groupname.
        if (($i=strpos($name,'.'))!==false) {
            $group = substr($name, 0, $i);
            $name = strtoupper(substr($name, $i+1));
        } else {
            $name = strtoupper($name);
            $group = null;
        }

        $class = null;

        // If a VALUE parameter is supplied, this will get precedence.
        if (isset($parameters['VALUE'])) {
            $class=$this->getClassNameForPropertyValue($parameters['VALUE']);
        }
        if (is_null($class) && isset($this->propertyMap[$name])) {
            $class='Sabre\\VObject\\Property\\' .$this->propertyMap[$name];
        }
        if (is_null($class)) {
            $class='Sabre\\VObject\\Property\\Text';
        }

        return new $class($this, $name, $value, $parameters, $group);

    }

    /**
     * This method returns a full class-name for a value parameter.
     *
     * For instance, DTSTART may have VALUE=DATE. In that case we will look in
     * our valueMap table and return the appropriate class name.
     *
     * This method returns null if we don't have a specialized class.
     *
     * @param string $valueParam
     * @return void
     */
    public function getClassNameForPropertyValue($valueParam) {

        $valueParam = strtoupper($valueParam);
        if (isset($this->valueMap[$valueParam])) {
            return 'Sabre\\VObject\\Property\\' . $this->valueMap[$valueParam];
        }

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
