<?php

namespace Sabre\VObject;

/**
 * Collection
 *
 * A collection represents a set of documents. See Sabre\VObject\Document. It's
 * not a bag, thus all items must have the same type.
 *
 * @copyright Copyright (C) 2011-2015 fruux GmbH (https://fruux.com/).
 * @author Ivan Enderlin
 * @license http://sabre.io/license/ Modified BSD License
 */
class Collection implements \ArrayAccess, \Countable, \IteratorAggregate {

    /**
     * Type of document.
     *
     * @var string
     */
    protected $type;

    /**
     * Collection.
     *
     * @var array
     */
    protected $collection = [];

    /**
     * Construct the collection by setting the type of items it contains, for
     * instance: 'Sabre\VObject\Vcard'.
     *
     * @param string $type    Type of items.
     * @return void
     */
    function __construct($type) {

        $this->type = $type;

    }

    /**
     * Check if a component exists.
     *
     * @param  mixed  $offset    Component offset.
     * @return bool
     */
    function offsetExists($offset) {

        return array_key_exists($offset, $this->collection);

    }

    /**
     * Get a component.
     *
     * @param  mixed  $offset    Component offset.
     * @return mixed
     */
    function offsetGet($offset) {

        if (!$this->offsetExists($offset)) {
            return null;
        }

        return $this->collection[$offset];

    }

    /**
     * Set a component.
     *
     * @param  mixed  $offset    Component offset.
     * @param  mixed  $value     Component.
     * @return Collection
     * @throw  \InvalidArgumentException
     */
    function offsetSet($offset, $value) {

        $type = $this->type;

        if (!(($value instanceof Document) && ($value instanceof $type))) {
            throw new \InvalidArgumentException('Items of this collection must be of type ' . __NAMESPACE__ . '\Document and ' . $type);
        }

        if (is_null($offset)) {
            $this->collection[] = $value;
        }
        else {
            $this->collection[$offset] = $value;
        }

        return $this;

    }

    /**
     * Unset a component.
     *
     * @param  mixed  $offset    Component offset.
     * @return void
     */
    function offsetUnset($offset) {

        unset($this->collection[$offset]);

    }

    /**
     * Count the number of items in the collection.
     *
     * @return int
     */
    function count() {

        return count($this->collection);

    }

    /**
     * Get an iterator on the collection.
     *
     * @return \ArrayIterator
     */
    function getIterator() {

        return new \ArrayIterator($this->collection);

    }
}
