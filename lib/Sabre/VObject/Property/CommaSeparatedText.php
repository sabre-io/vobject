<?php

namespace Sabre\VObject\Property;

use
    Sabre\VObject\Property,
    Sabre\VObject\Parser\MimeDir;

/**
 * CommaSeparatedText property
 *
 * By default text is split up using semi-colons, in case of multiple values.
 * There's a few properties that use a comma instead.
 *
 * @copyright Copyright (C) 2007-2013 fruux GmbH. All rights reserved.
 * @author Evert Pot (http://evertpot.com/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class CommaSeparatedText extends Text {

    protected $delimiter = ',';

}
