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
        $parser = new self($options);

        return $parser->parseComponent($data);
    }

    private $buffer;
    private $pos;

    private function __construct($options = 0)
    {
        $this->options = $options;
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
    public function parseComponent($buffer)
    {
        $this->buffer = $this->normalizeNewlines($buffer);
        $this->pos = 0;

        return $this->readComponent();
    }

    private function normalizeNewlines($data)
    {
        // TODO: skip empty lines?
        return str_replace(array("\r\n", "\r"),"\n", $data);
    }

    private function readComponent()
    {
        $obj = $this->readProperty();

        if ($obj instanceof Property && $obj->name === 'BEGIN') {
            $obj = Component::create($obj->value);

            do {
                $parsed = $this->readComponent();

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

//                 if (current($lines) === false)
//                     throw new ParseException('Invalid VObject. Document ended prematurely. Expected: "END:' . $obj->name.'"');

            } while(true);
        }
        return $obj;
    }

    private function readProperty()
    {
        // Properties
        //$result = preg_match('/(?P<name>[A-Z0-9-]+)(?:;(?P<parameters>^(?<!:):))(.*)$/',$line,$matches);

        if ($this->options & self::OPTION_FORGIVING) {
            $token = 'A-Z0-9\-\._';
        } else {
            $token = 'A-Z0-9\-\.';
        }

        if (!$this->tokens($token, $match)) {
            return $this->error('Expected property name');
        }
        $propertyName = strtoupper($match);

        $propertyParams = array();
        while ($this->literal(';')) {
            $parameter = $this->readParameter();
            $propertyParams []= $parameter;
        }

        if (!$this->literal(':')) {
            return $this->error('Missing colon after property value');
        }

        $propertyValue = $this->readLine();


        // peek at next lines if this is a quoted-printable encoding
        // $param = $obj['encoding']; // TODO: check if encoding is actually set
        $param = null;
        if ($param !== null) {
            $value = strtoupper((string)$param);
            if ($value === 'QUOTED-PRINTABLE') {
                while (substr($propertyValue, -1) === '=') {
                    $line = $this->readLine();

                    if (true) {
                        $line = ltrim($line);
                    }

                    // next line is unparsable => append to current line
                    $propertyValue = substr($propertyValue, 0, -1) . $line;
                }
            }
        }

        // peek at following lines to check for line-folding
        while (true) {
            $pos = $this->tell();
            try {
                $line = $this->readLine();
            }
            catch (\Exception $e) {
                break;
            }

            if ($line[0]===" " || $line[0]==="\t") {
                $propertyValue .= substr($line, 1);
            } else {
                // reset position
                $this->seek($pos);
                break;
            }
        }

        $propertyValue = preg_replace_callback('#(\\\\(\\\\|N|n))#',function($matches) {
            if ($matches[2]==='n' || $matches[2]==='N') {
                return "\n";
            } else {
                return $matches[2];
            }
        }, $propertyValue);

        $property = Property::create($propertyName, $propertyValue);
        foreach ($propertyParams as $param) {
            $property->add($param);
        }
        return $property;
    }

    /**
     * Reads a single property parameter from buffer (and advance buffer behind this parameter)
     *
     * @param Parameter $parameter
     * @return boolean
     */
    private function readParameter()
    {
        $token = 'A-Z0-9\-';

        if (!$this->tokens($token, $paramName)) {
            return $this->error('Invalid parameter name');
        }
        $paramValue = null;

        if ($this->literal('=')) {
            if ($this->literal('"')) {
                // TODO: escaped quotes?
                if (!$this->until('"', $paramValue)) {
                    return $this->error('Missing parameter quote end delimiter');
                }
            } else {
                $paramValue = '';
                $this->tokens('A-Z0-9\-\_', $paramValue);
            }

            $paramValue = preg_replace_callback('#(\\\\(\\\\|N|n|;|,))#',function($matches) {
                if ($matches[2]==='n' || $matches[2]==='N') {
                    return "\n";
                } else {
                    return $matches[2];
                }
            }, $paramValue);
        }
        return new Parameter($paramName, $paramValue);
    }

    private function tokens($token, &$out)
    {
        if ($this->match('/([' . $token . ']+)/i', $match)) {
            $out = $match[1];
            return true;
        }
        return false;
    }

    private function error($str)
    {
//         if ($this->options & self::OPTION_IGNORE_INVALID_LINES) {
//             return null;
//         } else {
            $lineNr = $this->getLineNr();

            try {
                $pos = $this->tell();
                $line = $this->readLine();
                $this->seek($pos);
            }
            catch (\Exception $e) {
                $line = '<end>';
            }
            throw new ParseException('Invalid VObject, line ' . $lineNr . ' did not follow the icalendar/vcard format: ' . $str . ': ' . var_export($line, true));
//         }
    }

    private function readLine()
    {
        if ($this->pos >= strlen($this->buffer)) {
            throw new \Exception('Buffer drained');
        }
        $pos = strpos($this->buffer, "\n", $this->pos);

        if ($pos === false) {
            $ret = substr($this->buffer, $this->pos);
            $this->pos = strlen($this->buffer);

            // throw new \Exception('No line ending found');
        } else {
            $ret = (string)substr($this->buffer, $this->pos, ($pos - $this->pos));

            $this->pos = $pos + 1;
        }

        return $ret;
    }

    /**
     * get current position in buffer
     *
     * @return int
     */
    private function tell()
    {
        return $this->pos;
    }

    /**
     * set buffer position
     *
     * @param int $pos
     * @throws \Exception
     */
    private function seek($pos)
    {
        if ($pos < 0 || $pos > strlen($this->buffer)) {
            throw new \Exception('Invalid offset given');
        }
        $this->pos = $pos;
    }

    /**
     * get line number for given buffer position
     *
     * @return int
     */
    private function getLineNr()
    {
        return substr_count($this->buffer, "\n", 0, $this->pos) + 1;
    }

    /**
     * try to match given regex on current buffer position (and advance behind match)
     *
     * @param string $regex
     * @param array  $ret
     * @return boolean
     * @uses preg_match()
     */
    private function match($regex, &$ret)
    {
        if (preg_match($regex, $this->buffer, $ret, null, $this->pos)) {
            $this->pos += strlen($ret[0]);
            return true;
        }
        return false;
    }

    /**
     * match given literal string in buffer (and advance behind literal)
     *
     * @param string $expect
     * @return boolean
     */
    private function literal($expect)
    {
        $l = strlen($expect);
        if (substr($this->buffer, $this->pos, $l) === (string)$expect) {
            $this->pos += $l;
            return true;
        }
        return false;
    }

    /**
     * read from buffer until $end is found ($end will not be returned and advance behind end)
     *
     * @param string $end
     * @param string $out
     * @return boolean
     */
    private function until($end, &$out)
    {
        $pos = strpos($this->buffer, $end, $this->pos);
        if ($pos === false) {
            return false;
        }

        $out = substr($this->buffer, $this->pos, ($pos - $this->pos));
        $this->pos = $pos + strlen($end);

        return true;
    }
}
