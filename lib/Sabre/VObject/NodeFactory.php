<?php

namespace Sabre\VObject;

 /**
  * NodeFactory
  *
  * This class is responsible for figuring out exactly which objects should be
  * instantiated for every component and property.
  *
  * This class is marked abstract, as it should only be used statically.
  *
  * @copyright Copyright (C) 2007-2013 fruux GmbH. All rights reserved.
  * @author Evert Pot (http://evertpot.com/)
  * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
  */
abstract class NodeFactory {

    static $componentMap = array(
        'VALARM'        => 'VAlarm',
        'VCALENDAR'     => 'VCalendar',
        'VCARD'         => 'VCard',
        'VEVENT'        => 'VEvent',
        'VJOURNAL'      => 'VJournal',
        'VTODO'         => 'VTodo',
        'VFREEBUSY'     => 'VFreeBusy',
    );

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
    static public function createComponent($name, array $children = array()) {

        $name = strtoupper($name);
        $class = 'Sabre\\VObject\\Component';

        if (isset(self::$componentMap[$name])) {
            $class.='\\' . self::$componentMap[$name];
        }
        return new $class($name, $children);

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
    static public function createProperty($name, $value, array $parameters = array()) {

        return new Property\Text($name, $value, $parameters);

    }

    /**
     * Factory method for creating new parameters
     *
     * This method automatically searches for the correct parameter class, based
     * on its name.
     *
     * @param string $name
     * @param string|array $value
     * @return Parameter
     */
    static public function createParameter($name, $value) {

        return new Parameter($name, $value);

    }
}
