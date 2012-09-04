<?php

namespace Sabre\VObject\Splitter;

use Sabre\VObject;

/**
 * Splitter
 *
 * This class is responsible for splitting up VCard objects.
 *
 * @copyright Copyright (C) 2007-2012 Rooftop Solutions. All rights reserved.
 * @author Dominik Tobschall
 * @author Armin Hackmann
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class VCard implements VObject\Splitter {

    /**
     * File handle
     *
     * @var resource
     */
    protected $fileHandle;

    /**
     * Creates a new VObject/Splitter/VCard object.
     *
     * @param string $filename
     */
    public function __construct($filename) {

        $this->validFileType = '';

        $this->fileHandle = fopen($filename, 'r');

    }

    /**
     * Returns a VCard object or false when eof is hit
     *
     * @return mixed
     */
    public function getNext() {
        
        $vcard = '';

        do {

            if (feof($this->fileHandle)) {
                return false;
            }

            $line = fgets($this->fileHandle);
            $vcard .= $line;

        } while(stripos($line, "END:") !== 0);

        $object = VObject\Reader::read($vcard);

        if($object->name !== 'VCARD') {
            throw new \InvalidArgumentException("Thats no vCard!", 1);
        }

        return $object;

    }

}
