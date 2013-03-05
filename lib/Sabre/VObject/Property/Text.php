<?php

namespace Sabre\VObject\Property;

use Sabre\VObject;

/**
 * Text property
 *
 * This property was added to allow correct escaping of non-compound text
 * properties. This should not be used for properties where multiple values
 * (with a delimiter) are allowed.
 *
 * Note that this is a bit of a hack. In the future I hope to do this a bit
 * more elegantly, but this will have to do for now.
 *
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
 * @copyright Copyright (C) 2007-2013 Rooftop Solutions. All rights reserved.
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class Text extends VObject\Property {

    /**
     * Turns the object back into a serialized blob.
     *
     * @return string
     */
    public function serialize() {

        $str = $this->name;
        if ($this->group) $str = $this->group . '.' . $this->name;

        foreach($this->parameters as $param) {

            $str.=';' . $param->serialize();

        }

        $src = array(
            '\\',
            "\n",
            ';',
            ',',
        );
        $out = array(
            '\\\\',
            '\n',
            '\;',
            '\,',
        );
        $str.=':' . str_replace($src, $out, $this->value);

        $out = '';
        while(strlen($str)>0) {
            if (strlen($str)>75) {
                $out.= mb_strcut($str,0,75,'utf-8') . "\r\n";
                $str = ' ' . mb_strcut($str,75,strlen($str),'utf-8');
            } else {
                $out.=$str . "\r\n";
                $str='';
                break;
            }
        }

        return $out;

    }

}
