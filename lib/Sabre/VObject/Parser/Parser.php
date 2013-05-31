<?php

namespace Sabre\VObject\Parser;

/**
 * Abstract parser.
 *
 * This class serves as a base-class for the different parsers.
 *
 * @copyright Copyright (C) 2007-2013 fruux GmbH. All rights reserved.
 * @author Evert Pot (http://evertpot.com/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
abstract class Parser {

    /**
     * Turning on this option makes the parser more forgiving.
     *
     * All it changes at the moment, is that underscores are allowed in
     * property names.
     */
    const OPTION_FORGIVING = 1;

    /**
     * If this option is turned on, any lines we cannot parse will be ignored
     * by the reader.
     */
    const OPTION_IGNORE_INVALID_LINES = 2;

    /**
     * Bitmask of parser options
     *
     * @var int
     */
    protected $options;

    /**
     * Creates the parser.
     *
     * Optionally, it's possible to parse the input stream here.
     *
     * @param mixed $input
     * @param int $options Any parser options (OPTION constants).
     * @return void
     */
    public function __construct($input = null, $options = 0) {

        if (!is_null($input)) {
            $this->setInput($input);
        }
        $this->options = $options;
    }

    /**
     * This method starts the parsing process.
     *
     * If the input was not supplied during construction, it's possible to pass
     * it here instead.
     *
     * If either input or options are not supplied, the defaults will be used.
     *
     * @param mixed $input
     * @param int|null $options
     * @return array
     */
    abstract public function parse($input = null, $options = null);

    /**
     * Sets the input data
     *
     * @param mixed $input
     * @return void
     */
    abstract public function setInput($input);

}
