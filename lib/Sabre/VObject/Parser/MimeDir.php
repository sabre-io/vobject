<?php

namespace Sabre\VObject\Parser;

use
    Sabre\VObject\ParseException,
    Sabre\VObject\Component,
    Sabre\VObject\Property;

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
        $this->componentStack = array();
        $this->currentComponent = null;
        $this->rootComponent = null;

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

        while($this->parseLine()) { }

        return $this->rootComponent;

    }



    /**
     * The current component in the stack that we're parsing.
     * This is a fixed-length array with 3 elements:
     *
     * 0. name
     * 1. array of properties
     * 2. array of sub-components
     *
     * @var mixed
     */
    protected $currentComponent;

    /**
     * The component stack. When we start parsing a new component and item will
     * be pushed, when we are finished parsing a component, we pop it again.
     *
     * @var array
     */
    protected $componentStack = array();

    /**
     * The top-level component
     *
     * @var array
     */
    protected $rootComponent = null;

    /**
     * Responsible for parsing a single line.
     *
     * @return string
     */
    protected function parseLine() {

        $line = $this->readLine();
        $property = $this->readProperty($line);
        if (!$property) {
            // Ignored line
            return true;
        }

        switch($property['name']) {

            case 'BEGIN' :
                // It's actually the start of a new component!
                $component = Component::create($property['value']);

                if ($this->currentComponent) {
                    $this->currentComponent->add($component);
                } else {
                    $this->rootComponent = $component;
                }
                $this->componentStack[] = $component;
                $this->currentComponent = $component;
                break;

            case 'END' :
                $name = strtoupper($property['value']);
                if ($name!==$this->currentComponent->name) {
                    throw new ParseException('Invalid MimeDir file. expected: "END:' . $this->currentComponent->name . '" got: "END:' . $name . '"');
                }
                // Unrolling the stack
                array_pop($this->componentStack);

                if (count($this->componentStack)===0) {
                    // End of document reached
                    return false;
                }

                end($this->componentStack);
                $this->currentComponent = $this->componentStack[ key($this->componentStack) ];
                break;

            default :
                $property = Property::create($property['name'], $property['value'], $property['parameters']);
                $this->currentComponent->add($property);
                break;

        }
        return true;

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
     * Reads a single line from the buffer.
     *
     * This method strips any newlines and also takes care of unfolding.
     *
     * @return string
     */
    protected function readLine() {

        if (!is_null($this->lineBuffer)) {
            $line = $this->lineBuffer;
        } else {
            $line = rtrim(fgets($this->input), "\r\n");
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
            } else {
                $this->lineBuffer = $nextLine;
                break;
            }

        }
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
        $safeChar = '^"^;^:^,';
        $qSafeChar = '^"';

        $regex = "/
            ^(?P<name> [$propNameToken]+ ) (?=[;:])        # property name
            |
            (?<=:)(?P<propValue> .*)$                      # property value
            |
            ;(?P<paramName> [$paramNameToken]+) (?=[=;:])  # parameter name
            |
            =(?P<paramValue>                               # parameter value
                (?: [$safeChar]+) |
                \"(?: [$qSafeChar]+)\"
            ) (?=[;:,])
            |
            ,(?P<paramValue2>                              # secondary parameter value
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

            if (isset($match['paramValue2'])) {
                if ($match['paramValue2'][0] === '"') {
                    $value = substr($match['paramValue2'], 1, -1);
                } else {
                    $value = $match['paramValue2'];
                }

                $value = $this->unescapeValue($value);

                if (is_array($property['parameters'][$lastParam])) {
                    $property['parameters'][$lastParam][] = $value;
                } else {
                    $property['parameters'][$lastParam] = array(
                        $property['parameters'][$lastParam],
                        $value
                    );
                }
                continue;
            }
            if (isset($match['paramValue'])) {
                if ($match['paramValue'][0] === '"') {
                    $value = substr($match['paramValue'],1, -1);
                } else {
                    $value = $this->unescapeValue($match['paramValue']);
                }
                $property['parameters'][$lastParam] = $value;
                continue;
            }
            if (isset($match['paramName'])) {
                $lastParam = strtoupper($match['paramName']);
                $property['parameters'][$lastParam] = null;
                continue;
            }
            if (isset($match['propValue'])) {
                $property['value'] = $this->unescapeValue($match['propValue']);
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

        return $property;

    }

    /**
     * This is what needs to be fixed for vobject 3.0
     *
     * @param string $input
     * @return void
     */
    private function unescapeValue($input) {

        return preg_replace_callback('#(\\\\(\\\\|N|n))#',function($matches) {
            if ($matches[2]==='n' || $matches[2]==='N') {
                return "\n";
            } else {
                return $matches[2];
            }
        }, $input);

    }

}
