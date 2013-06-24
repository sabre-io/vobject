<?php

namespace Sabre\VObject;

/**
 * This is the CLI interface for sabre-vobject.
 *
 * @copyright Copyright (C) 2007-2013 fruux GmbH. All rights reserved.
 * @author Evert Pot (http://evertpot.com/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class Cli {

    /**
     * No output
     *
     * @var bool
     */
    protected $quiet = false;

    /**
     * Help display
     *
     * @var bool
     */
    protected $showHelp = false;

    /**
     * Wether to spit out 'mimedir' or 'json' format.
     *
     * @var string
     */
    protected $format;

    /**
     * JSON pretty print
     *
     * @var bool
     */
    protected $pretty;

    /**
     * Source file
     *
     * @var string
     */
    protected $inputPath;

    /**
     * Destination file
     *
     * @var string
     */
    protected $outputPath;

    /**
     * output stream
     *
     * @var resource
     */
    protected $output;

    /**
     * Main function
     *
     * @return int
     */
    public function main(array $argv) {

        list($options, $positional) = $this->parseArguments($argv);

        if (isset($options['q'])) {
            $this->quiet = true;
        }
        $this->log($this->colorize('green', "sabre-vobject ") . $this->colorize('yellow', Version::VERSION));

        foreach($options as $name=>$value) {

            switch($name) {

                case 'q' :
                    // Already handled earlier.
                    break;
                case 'h' :
                case 'help' :
                    $this->showHelp();
                    return 0;
                    break;
                case 'format' :
                    switch($value) {

                        // jcard/jcal documents
                        case 'jcard' :
                        case 'jcal' :

                        // specific document versions
                        case 'vcard21' :
                        case 'vcard30' :
                        case 'vcard40' :
                        case 'icalendar20' :

                        // specific formats
                        case 'json' :
                        case 'mimedir' :

                        // icalendar/vcad
                        case 'icalendar' :
                        case 'vcard' :
                            $this->format = $value;
                            break;

                        default :
                            $this->log('Error: unknown format: ' . $value, 'red');
                            return 1;
                            break;

                    }
                    break;
                case 'pretty' :
                    $this->pretty = true;
                    break;
                default :
                    $this->log('Error: unknown option: ' . $name, 'red');
                    return 1;
                    break;

            }

        }

        if (count($positional) === 0) {
            $this->showHelp();
            return 1;
        }

        if (count($positional) === 1) {
            $this->log('Error: inputfile is a required argument', 'red');
            $this->showHelp();
            return 1;
        }

        if (count($positional) > 3) {
            $this->log('Error: too many arguments', 'red');
            $this->showHelp();
            return 1;
        }

        if (!in_array($positional[0], array('validate','repair','convert','color'))) {
            $this->log('Error: unknown command: ' . $positional[0], 'red');
            $this->showHelp();
            return 1;
        }
        $command = $positional[0];

        $this->inputPath = $positional[1];
        $this->outputPath = isset($positional[2])?$positional[2]:'-';

        if ($this->outputPath === '-') {
            $this->output = STDOUT;
        } else {
            $this->output = fopen($this->outputPath,'w');
        }

        $realCode = 0;

        try {

            while($input = $this->readInput()) {

                $returnCode = $this->$command($input);
                if ($returnCode!==0) $realCode = $returnCode;

            }

        } catch (EofException $e) {
            // end of file
        }

        return $returnCode;

    }

    /**
     * Shows the help message.
     *
     * @return void
     */
    protected function showHelp() {

        $this->log('Usage:', 'yellow');
        $this->log("  vobject [options] command [arguments]");
        $this->log('');
        $this->log('Options:', 'yellow');
        $this->log($this->colorize('green', '  -q       ') . "Don't output anything.");
        $this->log($this->colorize('green', '  -help -h ') . "Display this help message.");
        $this->log($this->colorize('green', '  --format ') . "Convert to a specific format. Must be one of: vcard, vcard21,");
        $this->log("           vcard30, vcard40, icalendar20, jcal, jcard, json, mimedir.");
        $this->log($this->colorize('green', '  --pretty ') . "json pretty-print.");
        $this->log('');
        $this->log('Commands:', 'yellow');
        $this->log($this->colorize('green', '  validate') . ' source_file              Validates a file for correctness.');
        $this->log($this->colorize('green', '  repair') . ' source_file [output_file]  Repairs a file.');
        $this->log($this->colorize('green', '  convert') . ' source_file [output_file] Converts a file.');
        $this->log($this->colorize('green', '  color') . ' source_file                 Colorize a file, useful for debbugging.');
        $this->log(<<<HELP

If source_file is set as '-', STDIN will be used.
If output_file is omitted, STDOUT will be used.
All other output is sent to STDERR.

HELP
    );

        $this->log('Examples:', 'yellow');
        $this->log('   vobject convert --pretty contact.vcf contact.json');
        $this->log('   vobject convert --format=vcard40 old.vcf new.vcf');
        $this->log('   vobject color calendar.ics');
        $this->log('');
        $this->log('https://github.com/fruux/sabre-vobject','purple');

    }

    /**
     * Validates a VObject file
     *
     * @param Component $vObj
     * @return int
     */
    protected function validate($vObj) {

        $returnCode = 0;

        switch($vObj->name) {
            case 'VCALENDAR' :
                $this->log("iCalendar: " . (string)$vObj->VERSION);
                break;
            case 'VCARD' :
                $this->log("vCard: " . (string)$vObj->VERSION);
                break;
        }

        $warnings = $vObj->validate();
        if (!count($warnings)) {
            $this->log("  No warnings!");
        } else {
            foreach($warnings as $warn) {

                $returnCode = 2;
                $this->log("  " . $warn['message']);

            }

        }

        return $returnCode;

    }

    /**
     * Repairs a VObject file
     *
     * @param Component $vObj
     * @return int
     */
    protected function repair($vObj) {

        $returnCode = 0;

        switch($vObj->name) {
            case 'VCALENDAR' :
                $this->log("iCalendar: " . (string)$vObj->VERSION);
                break;
            case 'VCARD' :
                $this->log("vCard: " . (string)$vObj->VERSION);
                break;
        }

        $warnings = $vObj->validate(Node::REPAIR);
        if (!count($warnings)) {
            $this->log("  No warnings!");
        } else {
            foreach($warnings as $warn) {

                $returnCode = 2;
                $this->log("  " . $warn['message']);

            }

        }
        fwrite($this->output, $vObj->serialize());

        return $returnCode;

    }

    /**
     * Converts a vObject file to a new format.
     *
     * @param Component $vObj
     * @return int
     */
    protected function convert($vObj) {

        if (!$this->format) {
            if (substr($this->outputPath, strrpos($this->outputPath,'.')+1) === 'json') {
                $this->format = 'json';
            } else {
                $this->format = 'mimedir';
            }
        }

        $json = false;
        $convertVersion = null;
        $forceInput = null;

        switch($this->format) {
            case 'json' :
                $json = true;
                if ($vObj->name === 'VCARD') {
                    $convertVersion = Document::VCARD40;
                }
                break;
            case 'jcard' :
                $json = true;
                $forceInput = 'VCARD';
                $convertVersion = Document::VCARD40;
                break;
            case 'jcal' :
                $json = true;
                $forceInput = 'VCALENDAR';
                break;
            case 'mimedir' :
            case 'icalendar' :
            case 'icalendar20' :
            case 'vcard' :
                break;
            case 'vcard21' :
                $convertVersion = Document::VCARD21;
                break;
            case 'vcard30' :
                $convertVersion = Document::VCARD30;
                break;
            case 'vcard40' :
                $convertVersion = Document::VCARD40;
                break;

        }

        if ($forceInput && $vObj->name !== $forceInput) {
            $this->log('Error: you cannot convert a ' . strtolower($vObj->name) . ' to ' . $this->format);
        }
        if ($convertVersion) {
            $vObj = $vObj->convert($convertVersion);
        }
        if ($json) {
            $jsonOptions = 0;
            if ($this->pretty) {
                $jsonOptions = JSON_PRETTY_PRINT;
            }
            fwrite($this->output, json_encode($vObj->jsonSerialize(), $jsonOptions));
        } else {
            fwrite($this->output, $vObj->serialize());
        }

        return 0;

    }

    /**
     * Colorizes a file
     *
     * @param Component $vObj
     * @return int
     */
    protected function color($vObj) {

        fwrite($this->output, $this->serializeComponent($vObj));

    }

    /**
     * Returns an ansi color string for a color name.
     *
     * @param string $color
     * @return string
     */
    protected function colorize($color, $str, $resetTo = 'default') {

        $colors = array(
            'cyan'    => '1;36',
            'red'     => '1;31',
            'yellow'  => '1;33',
            'blue'    => '0;34',
            'green'   => '0;32',
            'default' => '0',
            'purple'  => '0;35',
        );
        return "\033[" . $colors[$color] . 'm' . $str . "\033[".$colors[$resetTo]."m";

    }

    /**
     * Writes out a string in specific color.
     *
     * @param string $color
     * @param string $str
     * @return void
     */
    protected function cWrite($color, $str) {

        fwrite($this->output, $this->colorize($color, $str));

    }

    protected function serializeComponent(Component $vObj) {

        $this->cWrite('cyan', 'BEGIN');
        $this->cWrite('red', ':');
        $this->cWrite('yellow', $vObj->name . "\n");

        /**
         * Gives a component a 'score' for sorting purposes.
         *
         * This is solely used by the childrenSort method.
         *
         * A higher score means the item will be lower in the list.
         * To avoid score collisions, each "score category" has a reasonable
         * space to accomodate elements. The $key is added to the $score to
         * preserve the original relative order of elements.
         *
         * @param int $key
         * @param array $array
         * @return int
         */
        $sortScore = function($key, $array) {

            if ($array[$key] instanceof Component) {

                // We want to encode VTIMEZONE first, this is a personal
                // preference.
                if ($array[$key]->name === 'VTIMEZONE') {
                    $score=300000000;
                    return $score+$key;
                } else {
                    $score=400000000;
                    return $score+$key;
                }
            } else {
                // Properties get encoded first
                // VCARD version 4.0 wants the VERSION property to appear first
                if ($array[$key] instanceof Property) {
                    if ($array[$key]->name === 'VERSION') {
                        $score=100000000;
                        return $score+$key;
                    } else {
                        // All other properties
                        $score=200000000;
                        return $score+$key;
                    }
                }
            }

        };

        $tmp = $vObj->children;
        uksort($vObj->children, function($a, $b) use ($sortScore, $tmp) {

            $sA = $sortScore($a, $tmp);
            $sB = $sortScore($b, $tmp);

            if ($sA === $sB) return 0;

            return ($sA < $sB) ? -1 : 1;

        });

        foreach($vObj->children as $child) {
            if ($child instanceof Component) {
                $this->serializeComponent($child);
            } else {
                $this->serializeProperty($child);
            }
        }

        $this->cWrite('cyan', 'END');
        $this->cWrite('red', ':');
        $this->cWrite('yellow', $vObj->name . "\n");

    }

    /**
     * Colorizes a property.
     *
     * @param Property $property
     * @return void
     */
    protected function serializeProperty(Property $property) {

        if ($property->group) {
            $this->cWrite('default', $property->group);
            $this->cWrite('red', '.');
        }

        $str = '';
        $this->cWrite('yellow', $property->name);

        foreach($property->parameters as $param) {

            $this->cWrite('red',';');
            $this->cWrite('blue', $param->serialize());

        }
        $this->cWrite('red',':');

        if ($property instanceof Property\Binary) {

            $this->cWrite('default', 'embedded binary stripped. (' . strlen($property->getValue()) . ' bytes)');

        } else {

            $parts = $property->getParts();
            $first1 = true;
            // Looping through property values
            foreach($parts as $part) {
                if ($first1) {
                    $first1 = false;
                } else {
                    $this->cWrite('red', $property->delimiter);
                }
                $first2 = true;
                // Looping through property sub-values
                foreach((array)$part as $subPart) {
                    if ($first2) {
                        $first2 = false;
                    } else {
                        // The sub-value delimiter is always comma
                        $this->cWrite('red', ',');
                    }

                    $subPart = strtr($subPart, array(
                        '\\' => $this->colorize('purple', '\\\\', 'green'),
                        ';'  => $this->colorize('purple', '\;', 'green'),
                        ','  => $this->colorize('purple', '\,', 'green'),
                        "\n" => $this->colorize('purple', "\\n\n\t", 'green'),
                        "\r" => "",
                    ));

                    $this->cWrite('green', $subPart);
                }
            }

        }
        $this->cWrite("default", "\n");

    }

    /**
     * Parses the list of arguments.
     *
     * @param array $argv
     * @return void
     */
    protected function parseArguments(array $argv) {

        $positional = array();
        $options = array();

        for($ii=0; $ii < count($argv); $ii++) {

            // Skipping the first argument.
            if ($ii===0) continue;

            $v = $argv[$ii];

            if (substr($v,0,2)==='--') {
                // This is a long-form option.
                $optionName = substr($v,2);
                $optionValue = true;
                if (strpos($optionName,'=')) {
                    list($optionName, $optionValue) = explode('=', $optionName);
                }
                $options[$optionName] = $optionValue;
            } elseif (substr($v,0,1) === '-') {
                // This is a short-form option.
                foreach(str_split(substr($v,1)) as $option) {
                    $options[$option] = true;
                }

            } else {

                $positional[] = $v;

            }

        }

        return array($options, $positional);

    }

    protected $parser;

    /**
     * Reads the input file
     *
     * @return Component
     */
    protected function readInput() {

        if (!$this->parser) {
            if ($this->inputPath==='-') {
                $input = STDIN;
            } else {
                $input = fopen($this->inputPath,'r');
            }

            $this->parser = new Parser\MimeDir($input);
        }

        return $this->parser->parse();

    }

    /**
     * Sends a message to STDERR.
     *
     * @param string $msg
     * @return void
     */
    protected function log($msg, $color = 'default') {

        if (!$this->quiet) {
            if ($color!=='default') {
                $msg = $this->colorize($color, $msg);
            }
            fwrite(STDERR, $msg . "\n");
        }

    }

}
