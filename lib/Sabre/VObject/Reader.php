<?php

namespace Sabre\VObject;

use Sabre\VObject\Parser\String as StringParser;

/**
 * VCALENDAR/VCARD reader
 *
 * This class reads the vobject file, and returns a full element tree.
 *
 * TODO: this class is left here for reference and to not break BC and will
 * likely be removed in a future version.
 *
 * @copyright Copyright (C) 2007-2013 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class Reader {

    /**
     * If this option is passed to the reader, it will be less strict about the
     * validity of the lines.
     *
     * Currently using this option just means, that it will accept underscores
     * in property names.
     */
    const OPTION_FORGIVING = 1;

    /**
     * If this option is turned on, any lines we cannot parse will be ignored
     * by the reader.
     */
    const OPTION_IGNORE_INVALID_LINES = 2;

    /**
     * Parses the file and returns the top component
     *
     * The options argument is a bitfield. Pass any of the OPTIONS constant to
     * alter the parsers' behaviour.
     *
     * @param string $data
     * @param int $options
     * @return Component|Property
     */
    static function read($data, $options = 0) {

        $parser = new StringParser($data, $options);

        return $parser->readComponentOrProperty();
    }
}
