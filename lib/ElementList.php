<?php

namespace Sabre\VObject;

/**
 * VObject ElementList
 *
 * This class represents a list of elements. Lists are the result of queries,
 * such as doing $vcalendar->vevent where there's multiple VEVENT objects.
 *
 * @copyright Copyright (C) 2007-2014 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class ElementList implements \Iterator, \Countable, \ArrayAccess {

    /**
     * Inner elements
     *
     * @var array
     */
    protected $elements = [];

    /**
     * Creates the element list.
     *
     * @param array $elements
     */
    function __construct(array $elements) {

        $this->elements = $elements;

    }

    /* {{{ Iterator interface */

    /**
     * Current position
     *
     * @var int
     */
    private $key = 0;

    /**
     * Returns current item in iteration
     *
     * @return Element
     */
    function current() {

        return $this->elements[$this->key];

    }

    /**
     * To the next item in the iterator
     *
     * @return void
     */
    function next() {

        $this->key++;

    }

    /**
     * Returns the current iterator key
     *
     * @return int
     */
    function key() {

        return $this->key;

    }

    /**
     * Returns true if the current position in the iterator is a valid one
     *
     * @return bool
     */
    function valid() {

        return isset($this->elements[$this->key]);

    }

    /**
     * Rewinds the iterator
     *
     * @return void
     */
    function rewind() {

        $this->key = 0;

    }

    /* }}} */

    /* {{{ Countable interface */

    /**
     * Returns the number of elements
     *
     * @return int
     */
    function count() {

        return count($this->elements);

    }

    /* }}} */

    /* {{{ ArrayAccess Interface */


    /**
     * Checks if an item exists through ArrayAccess.
     *
     * @param int $offset
     * @return bool
     */
    function offsetExists($offset) {

        return isset($this->elements[$offset]);

    }

    /**
     * Gets an item through ArrayAccess.
     *
     * @param int $offset
     * @return mixed
     */
    function offsetGet($offset) {

        return $this->elements[$offset];

    }

    /**
     * Sets an item through ArrayAccess.
     *
     * @param int $offset
     * @param mixed $value
     * @return void
     */
    function offsetSet($offset, $value) {

        throw new \LogicException('You can not add new objects to an ElementList');

    }

    /**
     * Sets an item through ArrayAccess.
     *
     * This method just forwards the request to the inner iterator
     *
     * @param int $offset
     * @return void
     */
    function offsetUnset($offset) {

        throw new \LogicException('You can not remove objects from an ElementList');

    }

    /* }}} */

}
