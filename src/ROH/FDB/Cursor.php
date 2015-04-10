<?php

namespace ROH\FDB;

use Norm\Cursor as NormCursor;

class Cursor extends NormCursor
{
    protected $count = 0;

    protected $buffer = array();

    protected $next;

    protected $isQueried = false;

    public function count($foundOnly = false)
    {
        if ($foundOnly) {
            throw new \Exception('Unimplemented '.__METHOD__);
        } else {
            $this->rewind();
            return $this->count;
        }
    }

    public function translateCriteria(array $criteria = array())
    {
        return $criteria;
    }

    public function current()
    {
        $current = $this->next[1];
        return isset($current) ? $this->collection->attach($current) : null;
    }

    public function next()
    {
        // Try to get the next element in our data buffer.
        $this->next = each($this->buffer);

        // Past the end of the data buffer
        if (false === $this->next && !$this->isQueried) {
            $this->isQueried = true;

            $connection = $this->collection->getConnection();

            $this->buffer = $connection->fetch($this);

            $this->count = count($this->buffer);

            $this->next = each($this->buffer);
        }
    }

    public function key()
    {
        return $this->next[0];
    }

    public function valid()
    {
        if ($this->next) {
            return true;
        }
    }

    public function rewind()
    {
        reset($this->buffer);
        $this->next();
    }

    public function distinct()
    {
        throw new \Exception('Unimplemented yet!');
    }
}