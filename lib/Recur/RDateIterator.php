<?php

namespace Sabre\VObject\Recur;

use Sabre\VObject\DateTimeParser;
use Sabre\VObject\InvalidDataException;

/**
 * RRuleParser.
 *
 * This class receives an RRULE string, and allows you to iterate to get a list
 * of dates in that recurrence.
 *
 * For instance, passing: FREQ=DAILY;LIMIT=5 will cause the iterator to contain
 * 5 items, one for each day.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class RDateIterator implements \Iterator
{
    /**
     * Creates the Iterator.
     *
     * @param string|array $rrule
     */
    public function __construct($rrule, \DateTimeInterface $start)
    {
        $this->startDate = $start;
        $this->parseRDate($rrule);
        array_unshift($this->dates, \DateTimeImmutable::createFromInterface($this->startDate));
        sort($this->dates);
        $this->dates = array_values($this->dates);
        $this->rewind();
    }

    /* Implementation of the Iterator interface {{{ */

    #[\ReturnTypeWillChange]
    public function current(): ?\DateTimeInterface
    {
        if (!$this->valid()) {
            return null;
        }
        if (is_string($this->dates[$this->counter])) {
            $this->dates[$this->counter] =
                DateTimeParser::parse(
                    $this->dates[$this->counter],
                    $this->startDate->getTimezone()
                );
        }
        return $this->dates[$this->counter];
    }

    /**
     * Returns the current item number.
     */
    #[\ReturnTypeWillChange]
    public function key(): int
    {
        return $this->counter;
    }

    /**
     * Returns whether the current item is a valid item for the recurrence
     * iterator.
     */
    #[\ReturnTypeWillChange]
    public function valid(): bool
    {
        return $this->counter < count($this->dates);
    }

    /**
     * Resets the iterator.
     */
    #[\ReturnTypeWillChange]
    public function rewind(): void
    {
        $this->counter = 0;
    }

    /**
     * Goes on to the next iteration.
     *
     * @throws InvalidDataException
     */
    #[\ReturnTypeWillChange]
    public function next(): void
    {
        ++$this->counter;
    }

    /* End of Iterator implementation }}} */

    /**
     * Returns true if this recurring event never ends.
     */
    public function isInfinite(): bool
    {
        return false;
    }

    /**
     * This method allows you to quickly go to the next occurrence after the
     * specified date.
     *
     * @throws InvalidDataException
     */
    public function fastForward(\DateTimeInterface $dt): void
    {
        while ($this->valid() && $this->current() < $dt) {
            $this->next();
        }
    }

    /**
     * The reference start date/time for the rrule.
     *
     * All calculations are based on this initial date.
     */
    protected \DateTimeInterface $startDate;

    /**
     * The current item in the list.
     *
     * You can get this number with the key() method.
     */
    protected int $counter = 0;

    /* }}} */

    /**
     * This method receives a string from an RRULE property, and populates this
     * class with all the values.
     *
     * @param string|array $rrule
     */
    protected function parseRDate($rdate)
    {
        if (is_string($rdate)) {
            $rdate = explode(',', $rdate);
        }
        $this->dates = array_map(
            fn(string $dateString) => DateTimeParser::parse(
                $dateString,
                $this->startDate->getTimezone()
            ),
            $rdate
        );
    }

    /**
     * Array with the RRULE dates.
     */
    protected array $dates = [];
}
