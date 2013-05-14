<?php

namespace Sabre\VObject\Component;

use Sabre\VObject;

/**
 * The VCalendar component
 *
 * This component adds functionality to a component, specific for a VCALENDAR.
 *
 * @copyright Copyright (C) 2007-2013 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class VCalendar extends VObject\Document {

    /**
     * The default name for this component.
     *
     * This should be 'VCALENDAR' or 'VCARD'.
     *
     * @var string
     */
    static $defaultName = 'VCALENDAR';

    /**
     * This is a list of components, and which classes they should map to.
     *
     * @var array
     */
    public $componentMap = array(
        'VEVENT'    => 'VEvent',
        'VFREEBUSY' => 'VFreeBusy',
        'VJOURNAL'  => 'VJournal',
        'VTODO'     => 'VTodo',
        'VALARM'    => 'VAlarm',
    );

    /**
     * List of properties, and which classes they map to.
     *
     * @var array
     */
    public $propertyMap = array(
        // Calendar properties
        'CALSCALE'      => 'FlatText',
        'METHOD'        => 'FlatText',
        'PRODID'        => 'FlatText',
        'VERSION'       => 'FlatText',

        // Component properties
        'ATTACH'            => 'Binary',
        'CATEGORIES'        => 'CommaSeparatedText',
        'CLASS'             => 'FlatText',
        'COMMENT'           => 'FlatText',
        'DESCRIPTION'       => 'FlatText',
        'GEO'               => 'Text',
        'LOCATION'          => 'FlatText',
        'PERCENT-COMPLETE'  => 'Integer',
        'PRIORITY'          => 'Integer',
        'RESOURCES'         => 'CommaSeparatedText',
        'STATUS'            => 'FlatText',
        'SUMMARY'           => 'FlatText',

        // Date and Time Component Properties
        'COMPLETED'     => 'DateTime',
        'DTEND'         => 'DateTime',
        'DUE'           => 'DateTime',
        'DTSTART'       => 'DateTime',
        'DURATION'      => 'Duration',
        'FREEBUSY'      => 'Period',
        'TRANSP'        => 'FlatText',

        // Time Zone Component Properties
        'TZID'          => 'FlatText',
        'TZNAME'        => 'FlatText',
        'TZOFFSETFROM'  => 'FlatText',
        'TZOFFSETTO'    => 'FlatText',
        'TZURL'         => 'Url',

        // Relationship Component Properties
        'ATTENDEE'      => 'Url',
        'CONTACT'       => 'FlatText',
        'ORGANIZER'     => 'Url',
        'RECURRENCE-ID' => 'DateTime',
        'RELATED-TO'    => 'FlatText',
        'URL'           => 'Url',
        'UID'           => 'FlatText',

        // Recurrence Component Properties
        'EXDATE'        => 'DateTime',
        'RDATE'         => 'DateTime',
        'RRULE'         => 'Recur',
        'EXRULE'        => 'Recur', // Deprecated since rfc5545

        // Alarm Component Properties
        'ACTION'        => 'FlatText',
        'REPEAT'        => 'Integer',
        'TRIGGER'       => 'Duration',

        // Change Management Component Properties
        'CREATED'       => 'DateTime',
        'DTSTAMP'       => 'DateTime',
        'LAST-MODIFIED' => 'DateTime',
        'SEQUENCE'      => 'Integer',

        // Request Status
        'REQUEST-STATUS' => 'Text',




        'RDATE'         => 'DateTime',



    );

    /**
     * Returns the current document type.
     *
     * @return void
     */
    public function getDocumentType() {

        return self::ICALENDAR20;

    }

    /**
     * Returns a list of all 'base components'. For instance, if an Event has
     * a recurrence rule, and one instance is overridden, the overridden event
     * will have the same UID, but will be excluded from this list.
     *
     * VTIMEZONE components will always be excluded.
     *
     * @param string $componentName filter by component name
     * @return array
     */
    public function getBaseComponents($componentName = null) {

        $components = array();
        foreach($this->children as $component) {

            if (!$component instanceof VObject\Component)
                continue;

            if (isset($component->{'RECURRENCE-ID'}))
                continue;

            if ($componentName && $component->name !== strtoupper($componentName))
                continue;

            if ($component->name === 'VTIMEZONE')
                continue;

            $components[] = $component;

        }

        return $components;

    }

    /**
     * If this calendar object, has events with recurrence rules, this method
     * can be used to expand the event into multiple sub-events.
     *
     * Each event will be stripped from it's recurrence information, and only
     * the instances of the event in the specified timerange will be left
     * alone.
     *
     * In addition, this method will cause timezone information to be stripped,
     * and normalized to UTC.
     *
     * This method will alter the VCalendar. This cannot be reversed.
     *
     * This functionality is specifically used by the CalDAV standard. It is
     * possible for clients to request expand events, if they are rather simple
     * clients and do not have the possibility to calculate recurrences.
     *
     * @param DateTime $start
     * @param DateTime $end
     * @return void
     */
    public function expand(\DateTime $start, \DateTime $end) {

        $newEvents = array();

        foreach($this->select('VEVENT') as $key=>$vevent) {

            if (isset($vevent->{'RECURRENCE-ID'})) {
                unset($this->children[$key]);
                continue;
            }


            if (!$vevent->rrule) {
                unset($this->children[$key]);
                if ($vevent->isInTimeRange($start, $end)) {
                    $newEvents[] = $vevent;
                }
                continue;
            }

            $uid = (string)$vevent->uid;
            if (!$uid) {
                throw new \LogicException('Event did not have a UID!');
            }

            $it = new VObject\RecurrenceIterator($this, $vevent->uid);
            $it->fastForward($start);

            while($it->valid() && $it->getDTStart() < $end) {

                if ($it->getDTEnd() > $start) {

                    $newEvents[] = $it->getEventObject();

                }
                $it->next();

            }
            unset($this->children[$key]);

        }

        // Setting all properties to UTC time.
        foreach($newEvents as $newEvent) {

            foreach($newEvent->children as $child) {
                if ($child instanceof VObject\Property\DateTime && $child->hasTime()) {
                    $dt = $child->getDateTimes();
                    // We only need to update the first timezone, because
                    // setDateTimes will match all other timezones to the
                    // first.
                    $dt[0]->setTimeZone(new \DateTimeZone('UTC'));
                    $child->setDateTimes($dt);
                }

            }

            $this->add($newEvent);

        }

        // Removing all VTIMEZONE components
        unset($this->VTIMEZONE);

    }

    /**
     * Validates the node for correctness.
     * An array is returned with warnings.
     *
     * Every item in the array has the following properties:
     *    * level - (number between 1 and 3 with severity information)
     *    * message - (human readable message)
     *    * node - (reference to the offending node)
     *
     * @return array
     */
    /*
    public function validate() {

        $warnings = array();

        $version = $this->select('VERSION');
        if (count($version)!==1) {
            $warnings[] = array(
                'level' => 1,
                'message' => 'The VERSION property must appear in the VCALENDAR component exactly 1 time',
                'node' => $this,
            );
        } else {
            if ((string)$this->VERSION !== '2.0') {
                $warnings[] = array(
                    'level' => 1,
                    'message' => 'Only iCalendar version 2.0 as defined in rfc5545 is supported.',
                    'node' => $this,
                );
            }
        }
        $version = $this->select('PRODID');
        if (count($version)!==1) {
            $warnings[] = array(
                'level' => 2,
                'message' => 'The PRODID property must appear in the VCALENDAR component exactly 1 time',
                'node' => $this,
            );
        }
        if (count($this->CALSCALE) > 1) {
            $warnings[] = array(
                'level' => 2,
                'message' => 'The CALSCALE property must not be specified more than once.',
                'node' => $this,
            );
        }
        if (count($this->METHOD) > 1) {
            $warnings[] = array(
                'level' => 2,
                'message' => 'The METHOD property must not be specified more than once.',
                'node' => $this,
            );
        }

        $allowedComponents = array(
            'VEVENT',
            'VTODO',
            'VJOURNAL',
            'VFREEBUSY',
            'VTIMEZONE',
        );
        $allowedProperties = array(
            'PRODID',
            'VERSION',
            'CALSCALE',
            'METHOD',
        );
        $componentsFound = 0;
        foreach($this->children as $child) {
            if($child instanceof Component) {
                $componentsFound++;
                if (!in_array($child->name, $allowedComponents)) {
                    $warnings[] = array(
                        'level' => 1,
                        'message' => 'The ' . $child->name . " component is not allowed in the VCALENDAR component",
                        'node' => $this,
                    );
                }
            }
            if ($child instanceof Property) {
                if (!in_array($child->name, $allowedProperties)) {
                    $warnings[] = array(
                        'level' => 2,
                        'message' => 'The ' . $child->name . " property is not allowed in the VCALENDAR component",
                        'node' => $this,
                    );
                }
            }
        }

        if ($componentsFound===0) {
            $warnings[] = array(
                'level' => 1,
                'message' => 'An iCalendar object must have at least 1 component.',
                'node' => $this,
            );
        }

        return array_merge(
            $warnings,
            parent::validate()
        );

    }
     */

}

