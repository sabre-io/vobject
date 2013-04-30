<?php

namespace Sabre\VObject;

/**
 * VCALENDAR/VCARD reader
 *
 * This class reads the vobject file, and returns a full element tree.
 *
 * TODO: this class currently completely works 'statically'. This is pointless,
 * and defeats OOP principals. Needs refactoring in a future version.
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
     * @return Node
     */
    static function read($data, $options = 0) {

        // Normalizing newlines
        $data = str_replace(array("\r","\n\n"), array("\n","\n"), $data);

        $lines = explode("\n", $data);

        // Skipping empty lines
        foreach($lines as $i => $line) {
            if (!$line) {
                // only remove its index => keep line numbers intact otherwise
                unset($lines[$i]);
            }
        }

        reset($lines);

        return self::readComponent($lines, $options);

    }

    /**
     * Reads and parses a single line.
     *
     * This method receives the full array of lines. The array pointer is used
     * to traverse.
     *
     * This method returns null if an invalid line was encountered, and the
     * IGNORE_INVALID_LINES option was turned on.
     *
     * @param array $lines
     * @param int $options See the OPTIONS constants.
     * @return Node
     */
    static private function readComponent(&$lines, $options = 0) {
        $obj = self::readProperty($lines, $options);

        if ($obj instanceof Property && $obj->name === 'BEGIN') {
            $obj = Component::create($obj->value);

            do {
                $parsed = self::readComponent($lines, $options);

                if (is_null($parsed)) {
                    continue;
                }

                // Checking component name of the 'END:' line.
                if ($parsed instanceof Property) {
                    if ($parsed->name === 'END') {
                        if ($parsed->value !== $obj->name) {
                            throw new ParseException('Invalid VObject, expected: "END:' . $obj->name . '" got: "END:' . $parsed->value . '"');
                        }
                        break;
                    }/* else if($obj->name === 'BEGIN') {
                        throw new ParseException('Invalid VObject, expected: "END: ' . $obj->name .'" GOT "' . $parsed->serialize() . '"');
                    }*/
                }

                $obj->add($parsed);

                if (current($lines) === false)
                    throw new ParseException('Invalid VObject. Document ended prematurely. Expected: "END:' . $obj->name.'"');

            } while(true);
        }
        return $obj;
    }

    static private function readProperty(&$lines, $options) {

        $line = current($lines);
        $lineNr = key($lines);
        next($lines);

        // Properties
        //$result = preg_match('/(?P<name>[A-Z0-9-]+)(?:;(?P<parameters>^(?<!:):))(.*)$/',$line,$matches);

        if ($options & self::OPTION_FORGIVING) {
            $token = '[A-Z0-9-\._]+';
        } else {
            $token = '[A-Z0-9-\.]+';
        }
        $parameters = "(?:;(?P<parameters>([^:^\"]|\"([^\"]*)\")*))?";
        $regex = "/^(?P<name>$token)$parameters:(?P<value>.*)$/i";

        $result = preg_match($regex,$line,$matches);

        if (!$result) {
            if ($options & self::OPTION_IGNORE_INVALID_LINES) {
                return null;
            } else {
                throw new ParseException('Invalid VObject, line ' . ($lineNr+1) . ' did not follow the icalendar/vcard format');
            }
        }

        $propertyName = strtoupper($matches['name']);
        $propertyValue = preg_replace_callback('#(\\\\(\\\\|N|n))#',function($matches) {
            if ($matches[2]==='n' || $matches[2]==='N') {
                return "\n";
            } else {
                return $matches[2];
            }
        }, $matches['value']);

        $obj = Property::create($propertyName, $propertyValue);

        if ($matches['parameters']) {

            foreach(self::readParameters($matches['parameters']) as $param) {
                $obj->add($param);
            }

        }

        // return $obj;


        // peek at next lines if this is a quoted-printable encoding
        $param = $obj['encoding'];
        if ($param !== null) {
            $value = strtoupper((string)$param);
            if ($value === 'QUOTED-PRINTABLE') {
                while (substr($obj->value, -1) === '!=') {
                    $line = current($lines);
                    next($lines);

                    if (true) {
                        $line = ltrim($line);
                    }

                    // next line is unparsable => append to current line
                    $obj->value = substr($obj->value, 0, -1) . $line;
                }
            }
        }

        // peek at following lines to check for line-folding
        while (true) {
            $line = current($lines);

            if ($line[0]===" " || $line[0]==="\t") {
                $obj->value .= substr($line, 1);
                next($lines);
            } else {
                break;
            }
        }

        return $obj;


    }

    /**
     * Reads a parameter list from a property
     *
     * This method returns an array of Parameter
     *
     * @param string $parameters
     * @return array
     */
    static private function readParameters($parameters) {

        $token = '[A-Z0-9-]+';

        $paramValue = '(?P<paramValue>[^\"^;]*|"[^"]*")';

        $regex = "/(?<=^|;)(?P<paramName>$token)(=$paramValue(?=$|;))?/i";
        preg_match_all($regex, $parameters, $matches,  PREG_SET_ORDER);

        $params = array();
        foreach($matches as $match) {

            if (!isset($match['paramValue'])) {

                $value = null;

            } else {

                $value = $match['paramValue'];

                if (isset($value[0]) && $value[0]==='"') {
                    // Stripping quotes, if needed
                    $value = substr($value,1,strlen($value)-2);
                }

                $value = preg_replace_callback('#(\\\\(\\\\|N|n|;|,))#',function($matches) {
                    if ($matches[2]==='n' || $matches[2]==='N') {
                        return "\n";
                    } else {
                        return $matches[2];
                    }
                }, $value);

            }

            $params[] = new Parameter($match['paramName'], $value);

        }

        return $params;

    }


}
