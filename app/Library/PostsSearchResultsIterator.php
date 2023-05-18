<?php

namespace App\Library;

use \Iterator;

class PostsSearchResultsIterator implements Iterator
{
    private $results;
    private $position = 0;

    public function __construct($results)
    {
        $this->results = $results;
    }

    public function rewind():void
    {
        $this->position = 0;
    }

    public function current(): mixed
    {
        return $this->results[$this->position];
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function next():void
    {
        ++$this->position;
    }

    public function valid(): bool
    {
        return isset($this->results[$this->position]);
    }

    public function count(): int
    {
        return count($this->results);
    }
}

