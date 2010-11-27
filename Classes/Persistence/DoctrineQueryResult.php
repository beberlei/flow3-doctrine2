<?php

namespace F3\Doctrine\Persistence;

class DoctrineQueryResult implements \F3\FLOW3\Persistence\QueryResultInterface
{
    private $rows;

    private $query;

    public function __construct($rows, $query)
    {
        $this->rows = $rows;
        $this->query = $query;
    }

    public function count()
    {
        return count($this->rows);
    }

    public function current()
    {
        return current($this->rows);
    }

    public function getFirst()
    {
        return (isset($this->rows[0])) ? $this->rows[0] : false;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function key()
    {
        return key($this->rows);
    }

    public function next()
    {
        return next($this->rows);
    }

    public function offsetExists($offset)
    {
        return isset($this->rows[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->rows[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->rows[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->rows[$offset]);
    }

    public function rewind()
    {
        reset($this->rows);
    }

    public function toArray()
    {
        return $this->rows;
    }

    public function valid()
    {
        return current($this->rows) !== false;
    }

}