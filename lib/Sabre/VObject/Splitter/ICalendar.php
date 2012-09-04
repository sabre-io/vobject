<?php

namespace Sabre\VObject\Splitter;

use Sabre\VObject;

/**
 * Splitter
 *
 * This class is responsible for splitting up iCalendar objects.
 *
 * @copyright Copyright (C) 2007-2012 Rooftop Solutions. All rights reserved.
 * @author Dominik Tobschall
 * @author Armin Hackmann
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class ICalendar implements VObject\Splitter {

    /**
     * Timezones
     *
     * @var array
     */
    public $vtimezones = array();

    /**
     * File handle
     *
     * @var resource
     */
    protected $objects = array();

    /**
     * Creates a new VObject/Splitter/ICalendar object.
     *
     * @param string $filename
     */
    public function __construct($filename) {

        $data = VObject\Reader::read(file_get_contents($filename));
        $vtimezones = array();
        $components = array();

        foreach($data->children as $component) {
            if (!$component instanceof VObject\Component) {
                continue;
            }

            // Get all timezones
            if ($component->name === 'VTIMEZONE') {
                $this->vtimezones[(string)$component->TZID] = $component;
                continue;
            }
            
            // Get component UID for recurring Events search
            if($component->uid) {
                $uid = (string)$component->UID;
            } else {
                $uid = '';
            }
            
            // Take care of recurring events
            if (!array_key_exists($uid, $this->objects)) {
                $this->objects[$uid] = VObject\Component::create('VCALENDAR');
            }

            $this->objects[$uid]->add($component);
        }

    }

    /**
     * Returns an ICalendar object or false when eof is hit
     *
     * @return mixed
     */
    public function getNext() {

        if($object=current($this->objects)) {
            next($this->objects);
            return $object;
        } else {
            return false;
        }

   }

}
