<?php

namespace Sabre\VObject;

/**
 * Base class for all elements
 *
 * @copyright Copyright (C) 2007-2012 Rooftop Solutions. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
abstract class Element extends Node {

    public $parent = null;

}
