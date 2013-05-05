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

        $parser = new Parser\MimeDir();
        $result = $parser->parse($data, $options);

        return self::mapComponent($result);
       
    }

    static private function mapComponent($componentInfo) {

        $obj = Component::create(
            strtoupper($componentInfo[0])
        );

        foreach($componentInfo[1] as $propInfo) {

            $obj->add(
                self::mapProperty($propInfo)
            );

        }

        foreach($componentInfo[2] as $compInfo) {

            $obj->add(
                self::mapComponent($compInfo)
            );

        }

        return $obj;

    }

    static private function mapProperty($propInfo) {

        $obj = Property::create(
            strtoupper($propInfo[0]),
            $propInfo[3]
        );

        foreach($propInfo[1] as $paramName=>$paramValue) {
            if (is_array($paramValue)) {
                $paramValue = implode(',', $paramValue);
            };
            $obj->add(
                new Parameter( $paramName, $paramValue )
            );
        }

        return $obj;

    }

}
