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

    function __construct($filename);

    function getNext();

}
