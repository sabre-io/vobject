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

    protected $categories;
    
    protected $fileHandle;

    public function __construct($filename) {
        $this->validFileType = '';

        $this->fileHandle = fopen($filename, 'r');
    }

    public function getNext() {
        $vcard = '';

        if(feof($this->fileHandle)) {
            return false;
        }

        while(!feof($this->fileHandle)) {
                $line = fgets($this->fileHandle);
                $vcard .= $line;

                if (stripos($line, "END:")===0) {

                    $object = VObject\Reader::read($vcard);

                    if($object->name !== 'VCARD') {
                        throw new VObject\ParseException("Thats no vCard!", 1);
                    }

                    // remember vcards with categories
                    if($object->categories) {
                        $categories = explode(",", $object->categories);

                        foreach ($categories as $category) {
                            $this->categories[(string)$category][] = (string)$object->uid;
                        }
                    }

                    return $object;

                }
        }
        if($this->categories) {
            reset($this->categories);
        }
    }

}
