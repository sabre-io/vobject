<?php

namespace Sabre\VObject\Property;

/**
 * URI property
 *
 * This object encodes URI values. vCard 2.1 calls these URL.
 *
 * @copyright Copyright (C) 2007-2013 fruux GmbH. All rights reserved.
 * @author Evert Pot (http://evertpot.com/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class Uri extends Text {

    protected $delimiter = null;

}
