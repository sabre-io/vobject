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
    static public $propertyMap = array();

    /**
     * List of components, along with which classes they map to.
     *
     * @var array
     */
    static public $componentMap = array();

    /**
     * List of value-types, and which classes they map to.
     *
     * @var array
     */
    static public $valueMap = array(
        'BINARY'           => 'Binary',
        'BOOLEAN'          => 'Boolean',
        'CONTENT-ID'       => 'FlatText',   // vCard 2.1 only
        'CAL-ADDRESS'      => 'CalAddress', // iCalendar only
        'DATE'             => 'DateTime',
        'DATE-TIME'        => 'DateTime',
        'DATE-AND-OR-TIME' => 'DateAndOrTime', // vCard only
        'DURATION'         => 'Duration', // iCalendar only
        'FLOAT'            => 'Float',
        'INTEGER'          => 'Integer',
        'LANGUAGE-TAG'     => 'LanguageTag', // vCard only
        'PERIOD'           => 'Period',   // iCalendar only
        'RECUR'            => 'Recur',    // iCalendar only
        'TIMESTAMP'        => 'TimeStamp', // vCard only
        'TEXT'             => 'Text',
        'TIME'             => 'Time',
        'URI'              => 'Uri',
        'URL'              => 'Uri', // vCard 2.1 only
        'UTC-OFFSET'       => 'UtcOffset',
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
     * new Document(array $children = array(), $defaults = true);
     * new Document(string $name, array $children = array(), $defaults = true)
     *
     * @return void
     */
    public function __construct() {

        $args = func_get_args();
        if (count($args)===0 || is_array($args[0])) {
            array_unshift($args, $this, static::$defaultName);
            call_user_func_array(array('parent', '__construct'), $args);
        } else {
            array_unshift($args, $this);
            call_user_func_array(array('parent', '__construct'), $args);
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

        if (isset(static::$componentMap[strtoupper($name)])) {

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
     * By default, a set of sensible values will be added to the component. For
     * an iCalendar object, this may be something like CALSCALE:GREGORIAN. To
     * ensure that this does not happen, set $defaults to false.
     *
     * @param string $name
     * @param array $children
     * @param bool $defaults
     * @return Component
     */
    public function createComponent($name, array $children = null, $defaults = true) {

        $name = strtoupper($name);
        $class = 'Sabre\\VObject\\Component';

        if (isset(static::$componentMap[$name])) {
            $class.='\\' . static::$componentMap[$name];
        }
        if (is_null($children)) $children = array();
        return new $class($this, $name, $children, $defaults);

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
    public function createProperty($name, $value = null, array $parameters = null) {

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
        if (is_null($class) && isset(static::$propertyMap[$name])) {
            $class='Sabre\\VObject\\Property\\' .static::$propertyMap[$name];
        }
        if (is_null($class)) {
            $class='Sabre\\VObject\\Property\\Unknown';
        }
        if (is_null($parameters)) $parameters = array();

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
        if (isset(static::$valueMap[$valueParam])) {
            return 'Sabre\\VObject\\Property\\' . static::$valueMap[$valueParam];
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
