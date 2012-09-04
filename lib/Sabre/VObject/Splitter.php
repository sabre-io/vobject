<?php

namespace Sabre\VObject;

/**
 * Splitter
 *
 * This class is responsible for splitting up VCard/iCalendar objects.
 *
 * @copyright Copyright (C) 2007-2012 Rooftop Solutions. All rights reserved.
 * @author Dominik Tobschall
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
interface Splitter {

    /**
     * Creates a new VObject/Splitter object.
     *
     * @param string $filename
     */
    function __construct($filename);

    /**
     * Returns an object of a splitted object
     *
     * @return mixed
     */
    function getNext();

}
