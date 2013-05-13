<?php

namespace Sabre\VObject\Parser;

use
    Sabre\VObject\ParseException,
    Sabre\VObject\Component,
    Sabre\VObject\Property,
    Sabre\VObject\Component\VCalendar,
    Sabre\VObject\Component\VCard;

/**
 * MimeDir parser.
 *
 * This class parses iCalendar/vCard files and returns an array.
 *
 * The array is identical to the format jCard/jCal use.
 *
 * @copyright Copyright (C) 2007-2013 fruux GmbH. All rights reserved.
 * @author Evert Pot (http://evertpot.com/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class MimeDir {

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
     * The input stream.
     *
     * @var resource
     */
    protected $input;

    /**
     * Bitmask of parser options
     *
     * @var int
     */
    protected $options;

    /**
     * Root component
     *
     * @var Component
     */
    protected $root;

    /**
     * Parses an iCalendar or vCard file
     *
     * @param string|resource $input
     * @param int $options
     * @return array
     */
    public function parse($input, $options = 0) {

        // Resetting the parser
        $this->lineIndex = 0;
        $this->startLine = 0;
        $this->root = null;

        if (is_string($input)) {
            // Convering to a stream.
            $stream = fopen('php://temp', 'r+');
            fwrite($stream, $input);
            rewind($stream);
            $this->input = $stream;
        } else {
            $this->input = $input;
        }

        $this->options = $options;

        $this->parseDocument();

        return $this->root;

    }

    /**
     * Parses an entire document.
     *
     * @return void
     */
    protected function parseDocument() {

        $line = $this->readLine();
        switch(strtoupper($line)) {
            case 'BEGIN:VCALENDAR' :
                $this->root = new VCalendar();
                break;
            case 'BEGIN:VCARD' :
                $this->root = new VCard();
                break;
            default :
                throw new ParseException('This parser only support VCARD and VCALENDAR files');
        }

        while(true) {

            // Reading until we hit END:
            $line = $this->readLine();
            if (strtoupper(substr($line,0,4)) === 'END:') {
                break;
            }
            $result = $this->parseLine($line);
            if ($result) {
                $this->root->add($result);
            }

        }

        $name = strtoupper(substr($line, 4));
        if ($name!==$this->root->name) {
            throw new ParseException('Invalid MimeDir file. expected: "END:' . $this->root->name . '" got: "END:' . $name . '"');
        }

    }

    /**
     * Parses a line, and if it hits a component, it will also attempt to parse
     * the entire component
     *
     * @param string $line Unfolded line
     * @return Node
     */
    protected function parseLine($line) {

        // Start of a new component
        if (strtoupper(substr($line, 0, 6)) === 'BEGIN:') {

            $component = $this->root->createComponent(substr($line,6));

            while(true) {

                // Reading until we hit END:
                $line = $this->readLine();
                if (strtoupper(substr($line,0,4)) === 'END:') {
                    break;
                }
                $result = $this->parseLine($line);
                if ($result) {
                    $component->add($result);
                }

            }

            $name = strtoupper(substr($line, 4));
            if ($name!==$component->name) {
                throw new ParseException('Invalid MimeDir file. expected: "END:' . $component->name . '" got: "END:' . $name . '"');
            }

            return $component;

        } else {

            // Property reader
            $property = $this->readProperty($line);
            if (!$property) {
                // Ignored line
                return false;
            }
            return $property;

        }

    }

    /**
     * We need to look ahead 1 line every time to see if we need to 'unfold'
     * the next line.
     *
     * If that was not the case, we store it here.
     *
     * @var null|string
     */
    protected $lineBuffer;

    /**
     * The real current line number.
     */
    protected $lineIndex = 0;

    /**
     * In the case of unfolded lines, this property holds the line number for
     * the start of the line.
     *
     * @var int
     */
    protected $startLine = 0;

    /**
     * Contains a 'raw' representation of the current line.
     *
     * @var string
     */
    protected $rawLine;

    /**
     * Reads a single line from the buffer.
     *
     * This method strips any newlines and also takes care of unfolding.
     *
     * @return string
     */
    protected function readLine() {

        if (!is_null($this->lineBuffer)) {
            $rawLine = $line = $this->lineBuffer;
        } else {
            $rawLine = $line = rtrim(fgets($this->input), "\r\n");
            $this->lineIndex++;
        }

        $this->startLine = $this->lineIndex;

        // Looking ahead for folded lines.
        while(true) {

            $nextLine = rtrim(fgets($this->input), "\r\n");
            $this->lineIndex++;
            if (!$nextLine) {
                break;
            }
            if ($nextLine[0] === "\t" || $nextLine[0] === " ") {
                $line.=substr($nextLine,1);
                $rawLine.="\n " . substr($nextLine,1);
            } else {
                $this->lineBuffer = $nextLine;
                break;
            }

        }
        $this->rawLine = $rawLine;
        return $line;

    }

    /**
     * Reads a property or component from a line.
     *
     * @return void
     */
    protected function readProperty($line) {

        if ($this->options & self::OPTION_FORGIVING) {
            $propNameToken = 'A-Z0-9\-\._';
        } else {
            $propNameToken = 'A-Z0-9\-\.';
        }

        $paramNameToken = 'A-Z0-9\-';
        $safeChar = '^";:,';
        $qSafeChar = '^"';

        $regex = "/
            ^(?P<name> [$propNameToken]+ ) (?=[;:])        # property name
            |
            (?<=:)(?P<propValue> .*)$                      # property value
            |
            ;(?P<paramName> [$paramNameToken]+) (?=[=;:])  # parameter name
            |
            (=|,)(?P<paramValue>                           # parameter value
                (?: [$safeChar]+) |
                \"(?: [$qSafeChar]+)\"
            ) (?=[;:,])
            /xi";

        //echo $regex, "\n"; die();
        preg_match_all($regex, $line, $matches,  PREG_SET_ORDER );

        $property = array(
            'name' => null,
            'parameters' => array(),
            'value' => null
        );

        $lastParam = null;

        /**
         * Looping through all the tokens.
         *
         * Note that we are looping through them in reverse order, because if a
         * sub-pattern matched, the subsequent named patterns will not show up
         * in the result.
         */
        foreach($matches as $match) {

            if (isset($match['paramValue'])) {
                if ($match['paramValue'][0] === '"') {
                    $value = substr($match['paramValue'], 1, -1);
                } else {
                    $value = $match['paramValue'];
                }

                $value = $this->unescapeParam($value);

                if (is_null($property['parameters'][$lastParam])) {
                    $property['parameters'][$lastParam] = $value;
                } elseif (is_array($property['parameters'][$lastParam])) {
                    $property['parameters'][$lastParam][] = $value;
                } else {
                    $property['parameters'][$lastParam] = array(
                        $property['parameters'][$lastParam],
                        $value
                    );
                }
                continue;
            }
            if (isset($match['paramName'])) {
                $lastParam = strtoupper($match['paramName']);
                $property['parameters'][$lastParam] = null;
                continue;
            }
            if (isset($match['propValue'])) {
                $property['value'] = $match['propValue'];
                continue;
            }
            if (isset($match['name']) && $match['name']) {
                $property['name'] = strtoupper($match['name']);
                continue;
            }

            throw new \LogicException('This code should not be reachable');

        }

        if (is_null($property['value']) || !$property['name']) {
            if ($this->options & self::OPTION_IGNORE_INVALID_LINES) {
                return false;
            }
            throw new ParseException('Invalid Mimedir file. Line starting at ' . $this->startLine . ' did not follow iCalendar/vCard conventions');
        }

        $propObj = $this->root->createProperty($property['name'], null, $property['parameters']);

        if (isset($property['parameters']['ENCODING']) && strtoupper($property['parameters']['ENCODING']) === 'QUOTED-PRINTABLE') {
            $propObj->setValue($this->extractQuotedPrintableValue());
        } else {
            $propObj->setRawMimeDirValue($property['value']);
        }

        return $propObj;

    }

    /**
     * Unescapes a property value.
     *
     * vCard 2.1 says:
     *   * Semi-colons must be escaped in some property values, specifically
     *     ADR, ORG and N.
     *   * Semi-colons must be escaped in parameter values, because semi-colons
     *     are also use to separate values.
     *   * No mention of escaping backslashes with another backslash.
     *   * newlines are not escaped either, instead QUOTED-PRINTABLE is used to
     *     span values over more than 1 line.
     *
     * vCard 3.0 says:
     *   * (rfc2425) Backslashes, newlines (\n or \N) and comma's must be
     *     escaped, all time time.
     *   * Comma's are used for delimeters in multiple values
     *   * (rfc2426) Adds to to this that the semi-colon MUST also be escaped,
     *     as in some properties semi-colon is used for separators.
     *   * Properties using semi-colons: N, ADR, GEO, ORG
     *   * Both ADR and N's individual parts may be broken up further with a
     *     comma.
     *   * Properties using commas: NICKNAME, CATEGORIES
     *
     * vCard 4.0 (rfc6350) says:
     *   * Commas must be escaped.
     *   * Semi-colons may be escaped, an unescaped semi-colon _may_ be a
     *     delimiter, depending on the property.
     *   * Backslashes must be escaped
     *   * Newlines must be escaped as either \N or \n.
     *   * Some compound properties may contain multiple parts themselves, so a
     *     comma within a semi-colon delimited property may also be unescaped
     *     to denote multiple parts _within_ the compound property.
     *   * Text-properties using semi-colons: N, ADR, ORG, CLIENTPIDMAP.
     *   * Text-properties using commas: NICKNAME, RELATED, CATEGORIES, PID.
     *
     * Even though the spec says that commas must always be escaped, the
     * example for GEO in Section 6.5.2 seems to violate this.
     *
     * iCalendar 2.0 (rfc5545) says:
     *   * Commas or semi-colons may be used as delimiters, depending on the
     *     property.
     *   * Commas, semi-colons, backslashes, newline (\N or \n) are always
     *     escaped, unless they are delimiters.
     *   * Colons shall not be escaped.
     *   * Commas can be considered the 'default delimiter' and is described as
     *     the delimiter in cases where the order of the multiple values is
     *     insignificant.
     *   * Semi-colons are described as the delimiter for 'structured values'.
     *     They are specifically used in Semi-colons are used as a delimiter in
     *     REQUEST-STATUS, RRULE, GEO and EXRULE. EXRULE is deprecated however.
     *
     * Now for the parameters
     *
     * If delimiter is not set (null) this method will just return a string.
     * If it's a comma or a semi-colon the string will be split on those
     * characters, and always return an array.
     *
     * @param string $input
     * @param string $delimiter
     * @return string|array
     */
    static public function unescapeValue($input, $delimiter = ';') {

        $regex = '#  (?: (\\\\ (?: \\\\ | N | n | ; | , ) )';
        if ($delimiter) {
            $regex.= ' | (' . $delimiter . ')';
        }
        $regex .= ') #x';

        $matches = preg_split($regex, $input, -1, PREG_SPLIT_DELIM_CAPTURE  |  PREG_SPLIT_NO_EMPTY );

        $resultArray = array();
        $result = '';

        foreach($matches as $match) {

            switch ($match) {
                case '\\\\' :
                    $result.='\\';
                    break;
                case '\N' :
                case '\n' :
                    $result.="\n";
                    break;
                case '\;' :
                    $result.=';';
                    break;
                case '\,' :
                    $result.=',';
                    break;
                case $delimiter :
                    $resultArray[] = $result;
                    $result='';
                    break;
                default :
                    $result.=$match;
                    break;

            }

        }

        $resultArray[] = $result;
        return $delimiter ? $resultArray : $result;

    }

    /**
     * Unescapes a parameter value.
     *
     * vCard 2.1:
     *   * Does not mention a mechanism for this. In addition, double quotes
     *     are never used to wrap values.
     *   * This means that parameters can simply not contain colons or
     *     semi-colons.
     *
     * vCard 3.0 (rfc2425, rfc2426):
     *   * Parameters _may_ be surrounded by double quotes.
     *   * If this is not the case, semi-colon, colon and comma may simply not
     *     occur (the comma used for multiple parameter values though).
     *   * If it is surrounded by double-quotes, it may simply not contain
     *     double-quotes.
     *   * This means that a parameter can in no case encode double-quotes, or
     *     newlines.
     *
     * vCard 4.0 (rfc6350)
     *   * Behavior seems to be identical to vCard 3.0
     *
     * iCalendar 2.0 (rfc5545)
     *   * Behavior seems to be identical to vCard 3.0
     *
     * Parameter escaping mechanism (rfc6868) :
     *   * This rfc describes a new way to escape parameter values.
     *   * New-line is encoded as ^n
     *   * ^ is encoded as ^^.
     *   * " is encoded as ^'
     *
     * @param string $input
     * @return void
     */
    private function unescapeParam($input) {

        return
            preg_replace_callback('#(\^(\^|n|\'))#',function($matches) {
                switch($matches[2]) {
                    case 'n' :
                        return "\n";
                    case '^' :
                        return '^';
                    case '\'' :
                        return '"';
                }
            }, $input);

    }

    /**
     * Gets the quoted-printable value, and decodes it.
     *
     * @return void
     */
    private function extractQuotedPrintableValue() {

        // We need to parse the raw line again to get the start of the value.
        //
        // We are basically looking for the first colon (:), but we need to
        // skip over the parameters first, as they may contain one.
        $regex = '/^
            (?: [^:])+ # Anything but a colon
            (?: "[^"]")* # A parameter in double quotes
            : # start of the value we really care about
            (.*)$
        /xs';

        preg_match($regex, $this->rawLine, $matches);

        $value = $matches[1];
        // Removing the first whitespace character from every line. Kind of
        // like unfolding, but we keep the newline.
        $value = str_replace("\n ", "\n", $value);

        // Microsoft products don't always correctly fold lines, they may be
        // missing a whitespace. So if 'forgiving' is turned on, we will take
        // those as well.
        if ($this->options & self::OPTION_FORGIVING) {
            while(substr($value,-1) === '=') {
                // Reading the line
                $this->readLine();
                // Grabbing the raw form
                $value.="\n" . $this->rawLine;
            }
        }

        return quoted_printable_decode($value);

    }

}
