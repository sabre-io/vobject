<?php

namespace Sabre\VObject\Property;

use Sabre\VObject;

/**
* Compound property.
*
* This class adds (de)serialization of compound properties to/from arrays.
*
* Currently the following properties from RFC 6350 are mapped to use this
* class:
*
*  N:          Section 6.2.2
*  ADR:        Section 6.3.1
*  ORG:        Section 6.6.4
*  CATEGORIES: Section 6.7.1
*
* In order to use this correctly, you must call setArray and getArray to
* retrieve and modify dates respectively.
*
* If you use the 'value' or properties directly, this object does not keep
* reference and results might appear incorrectly.
*
* The splitCompoundValues() and concatCompoundValues() methods are written
* by Lars Kneschke https://code.google.com/p/sabredav/issues/detail?id=145#c6
*
* @author Thomas Tanghus (http://tanghus.net/)
* @author Lars Kneschke
* @author Evert Pot (http://www.rooftopsolutions.nl/)
* @copyright Copyright (C) 2007-2012 Rooftop Solutions. All rights reserved.
*
* @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
*/

/**
* This class represents a compound property in a vCard.
*/
class Compound extends VObject\Property {

    /**
    * Property value as array
    *
    * @var array
    */
    public $arr;

    /**
    * If property names are added to this map, they will be (de)serialised as arrays
    * using the getArray() and setArray() methods.
    * The keys are the property names, values are delimiter chars.
    *
    * @var array
    */
    static public $delimiterMap = array(
        'N'				=>	';',
        'ADR'			=>	';',
        'ORG'			=>	';',
        'CATEGORIES'	=>	',',
    );

    /**
    * Get a compound value as an array.
    *
    * @param $name string
    * @return array
    */
    public function getArray() {
        if(!$this->arr) {
            $this->arr = $this->parseData($this->name, $this->value);
        }
        return $this->arr;
    }

    /**
    * Set a compound value as an array.
    *
    * @param $name string
    * @return array
    */
    public function setArray(array $values) {

        $this->arr = array_map('trim', $values);

        if(in_array($this->name, array_keys(self::$delimiterMap))) {
            $this->setValue($this->concatCompoundValues($values, self::$delimiterMap[$this->name]));
        } else {
            throw new Sabre_DAV_Exception_UnsupportedMediaType(
                    'This property cannot be saved as an array: '
                        . $this->name);
        }

    }

    /**
    * Parses the serialised data structure to create an array.
    *
    * @param string|null $propertyValue The string to parse.
    * @return Array
    */
    static public function parseData($propertyName, $propertyValue) {

        if (is_null($propertyValue)) {
            return  null;
        }

        $arr = array();

        if(in_array($propertyName, array_keys(self::$delimiterMap))) {
            $arr = self::splitCompoundValues($propertyValue, self::$delimiterMap[$propertyName]);
        } else {
            throw new Sabre_DAV_Exception_UnsupportedMediaType(
                    'This property cannot de-serialised as an array: '
                        . $this->name);
        }

        $arr = array_map('trim', $arr);
        return $arr;
    }

    /**
    * split compound value into single parts
    *
    * @param string $value
    * @param string $delimiter
    * @return array
    */
    public static function splitCompoundValues($value, $delimiter = ';') {

        // split by any $delimiter which is NOT prefixed by a slash
        $compoundValues = preg_split("/(?<!\\\)$delimiter/", $value);

        // remove slashes from any semicolon and comma left escaped in the single values
        foreach ($compoundValues as &$compoundValue) {
            $compoundValue = str_replace("\\;", ';', $compoundValue);
            $compoundValue = str_replace("\\,", ',', $compoundValue);
        }

        reset($compoundValues);

        return $compoundValues;
    }

    /**
    * concat single values to one compound value
    *
    * @param array $values
    * @param string $glue
    * @return string
    */
    public static function concatCompoundValues(array $values, $glue = ';') {

        // add slashes to all semicolons and commas in the single values
        foreach($values as &$value) {
            $value = str_replace( ';', "\\;", $value);
            $value = str_replace( ',', "\\,", $value);
        }

        return implode($glue, $values);
    }

    /**
    * Turns the object back into a serialized blob.
    *
    * This method is overridden to not serialize the value
    * as it is already serialized.
    *
    * @return string
    */
    public function serialize() {

        $str = $this->name;
        if ($this->group) $str = $this->group . '.' . $this->name;

        if (count($this->parameters)) {
            foreach($this->parameters as $param) {

                $str.=';' . $param->serialize();

            }
        }

        $str.=':' . $this->value;

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