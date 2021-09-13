<?php

namespace App\Traits;

trait AccessorsTrait
{
    /**
     * Accessor methods for private vars
     */
    public function results()
    {
        return $this->results;
    }

    public function error()
    {
        return $this->error;
    }

    public function count()
    {
        return $this->count;
    }
}
