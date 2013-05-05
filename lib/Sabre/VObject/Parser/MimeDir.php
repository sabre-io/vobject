<?php

namespace Sabre\VObject\Parser;

use Sabre\VObject\ParseException;

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

        switch($property['name']) {

            case 'begin' :
                // It's actually the start of a new component!
                $component = array(
                    strtolower($property['value']),
                    array(),
                    array(),
                );
                if ($this->currentComponent) {
                    $this->currentComponent[2][] =& $component;
                } else {
                    $this->rootComponent =& $component;
                }
                $this->componentStack[] =& $component;
                $this->currentComponent =& $component;
                break;

            case 'end' :
                $name = strtolower($property['value']);
                if ($name!==$this->currentComponent[0]) {
                    throw new ParseException('Invalid MimeDir file. expected: "END:' . strtoupper($this->currentComponent[0]) . '" got: "END:' . strtoupper($name) . '"');
                }
                // Unrolling the stack
                array_pop($this->componentStack);

                if (count($this->componentStack)===0) {
                    // End of document reached
                    return false;
                }

                end($this->componentStack);
                $this->currentComponent =& $this->componentStack[ key($this->componentStack) ];

                break;

            default :
                $this->currentComponent[1][] = array(
                    $property['name'],
                    $property['parameters'],
                    null, // This is the type identifier in jCal/jCard, but we're skipping it here.
                    $property['value']
                );
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

        $name = null;
        $parameters = array();
        $value = null;

        if ($this->options & self::OPTION_FORGIVING) {
            $token = 'A-Z0-9\-\._';
        } else {
            $token = 'A-Z0-9\-\.';
        }

        // Matches the property name, group, and wether it ends with a ; or a :
        if (!preg_match('/^(?P<name>[' . $token . ']+)(?P<endtoken>:|;)/', $line, $matches)) {
            throw new ParseException('Invalid MimeDir file, line ' . ($this->startLine) . ' did not follow iCalendar/vCard specifications');
        }

        $name = strtolower($matches['name']);

        $lineOffset = strlen($matches[0]);

        if ($matches['endtoken']===';') {
            $parameters = $this->readParameters($line, $lineOffset);
        }

        $value = substr($line, $lineOffset);

        if (
            isset($parameters['encoding']) &&
            strtoupper($parameters['encoding'])==='QUOTED-PRINTABLE'
        ) {

            if ($this->options & self::OPTION_FORGIVING) {
                // MS Office may generate badly formatted vcards. When the encoding
                // is QUOTED-PRINTABLE and the value is spread over multiple lines.

                // quoted-printable soft line break at line end => try to read next
                // lines
                echo "\n", $value, "\n";
                while (substr($value, -1) === '=') {
                    echo "\n", $value, "\n";
                    $value.= "\n" . $this->readLine();
                }
            }

            $value = quoted_printable_decode($value);

        }

        return array(
            'name' => $name,
            'parameters' => $parameters,
            'value' => $value,
        );

    }

    /**
     * Reads the list of parameters for a property
     *
     * @param string $line
     * @param int $lineOffset How far we are into reading the line
     * @return void
     */
    protected function readParameters($line, &$lineOffset) {

        $nameToken = 'A-Z0-9\-';
        $safeChar = '^"^;^:^,';
        $qSafeChar = '^"';
        $paramValueToken = '((?P<value>[' . $safeChar . ']+)|"(?P<qvalue>[' . $qSafeChar . ']+)")';
        $endToken = '(?P<endtoken>:|;|,)';

        $parameters = array();

        do {

            if (!preg_match('/^(?P<name>[' . $nameToken . ']+)(?:='.$paramValueToken.')?'.$endToken.'/', substr($line, $lineOffset), $matches)) {
                throw new ParseException('Invalid Mimedir file. The parameter on line ' . $this->startLine . ', column ' . $lineOffset . ' did not follow iCalendar/vCard specifications');
            }

            $paramName = strtolower($matches['name']);

            while(true) {
                $lineOffset += strlen($matches[0]);
                $value = $matches['qvalue']?:$matches['value'];

                if (isset($parameters[$paramName])) {
                    if (is_array($parameters[$paramName])) {
                        $parameters[$paramName][] = $value;
                    } else {
                        $parameters[$paramName] = array(
                            $parameters[$paramName],
                            $value,
                        );
                    }
                } else {
                    $parameters[$paramName] = $value;
                }

                if ($matches['endtoken']!==',') {
                    break;
                } else {
                    if (!preg_match('/^'.$paramValueToken . $endToken .'/', substr($line, $lineOffset), $matches)) {
                        throw new ParseException('Invalid Mimedir file. The parameter on line ' . $this->startLine . ', column ' . $lineOffset . ' did not follow iCalendar/vCard specifications');
                    }
                }

            }


        } while ($matches['endtoken']===';');

        return $parameters;

    }

}
