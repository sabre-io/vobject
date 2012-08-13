<?php

namespace Sabre\VObject;

/**
 * Useful utilities for working with various strings.
 *
 * @copyright Copyright (C) 2007-2012 Rooftop Solutions. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class StringUtil {

    /**
     * Returns true or false depending on if a string is valid UTF-8
     *
     * @param string $str
     * @return bool
     */
    static function isUTF8($str) {

        // First check.. mb_check_encoding
        if (!mb_check_encoding($str, 'UTF-8')) {
            return false;
        }

        // Control characters
        if (preg_match('%(?:[\x00-\x08\x0B-\x0C\x0E\x0F])%', $str)) {
            return false;
        }

        return true;

    }

}

