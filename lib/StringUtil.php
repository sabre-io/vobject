<?php

namespace Sabre\VObject;

/**
 * Useful utilities for working with various strings.
 *
 * @copyright Copyright (C) 2007-2014 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class StringUtil {

    /**
     * Returns true or false depending on if a string is valid UTF-8
     *
     * @param string $str
     * @return bool
     */
    static public function isUTF8($str) {

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

    /**
     * This method tries its best to convert the input string to UTF-8.
     *
     * Currently only ISO-5991-1 input and UTF-8 input is supported, but this
     * may be expanded upon if we receive other examples.
     *
     * @param string $str
     * @return string
     */
    static public function convertToUTF8($str) {
        /*
         * Unfortunately, mb_check_encoding is not reliable.
         * But mb_convert_encoding can be used to convert
         * from (presumed) UTF-8 to UTF-16 then back to UTF-8,
         * and will replace invalid input with question marks.
         * That means if previous and result are not equal,
         * the input was not UTF-8, in which case we convert
         * it as if it were ISO-8859-1; all 256 bytes are
         * (after conversion) valid per RFC3629 §4. We need,
         * for this to work, to temporarily change the internal
         * encoding used by the mb_* functions, though.
         */
        $mb_encoding = mb_internal_encoding();
        mb_internal_encoding("UTF-8");

        /* coerce to string first */
        $str = ''.$str;
        /* check for UTF-8 encoding as detailed above */
        $ws = mb_convert_encoding($str, "UTF-16LE", "UTF-8");
        $mbs = mb_convert_encoding($ws, "UTF-8", "UTF-16LE");
        /* match? */
        if ($mbs !== $str) {
            /* convert from ISO-8859-1 to UTF-16LE */
            $ws = implode("\0", str_split($str)) . "\0";
            /* convert from UTF-16LE to UTF-8 */
            $mbs = mb_convert_encoding($ws, "UTF-8", "UTF-16LE");
        }

        /* restore internal encoding used by the mb_* functions */
        mb_internal_encoding($mb_encoding);

        /* remove any C0 control characters (C1 are fine) */
        return (preg_replace('%(?:[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F])%', '', $mbs));

    }

}

