<?php

namespace Sabre\VObject\Parser\XML\Element;

use Sabre\XML as SabreXML;

/**
 * Our own sabre/xml key-value element.
 *
 * It just removes the clark notation.
 *
 * @copyright Copyright (C) 2007-2014 fruux GmbH. All rights reserved.
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class KeyValue extends SabreXML\Element\KeyValue {

    /**
     * Get element name.
     *
     * @param SabreXML\Reader $reader
     * @return string
     */
    static function getElementName(SabreXML\Reader $reader) {

        return $reader->localName;

    }

}
