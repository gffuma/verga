<?php

namespace Verga\Result;

class Result {

    /**
     * List of \Verga\Line\Line.
     *
     * @var array
     */
    protected $lines;

    /**
     * Make a new result instance.
     *
     * @param  array $lines
     * @return void
     */
    public function __construct(array $lines)
    {
        $this->lines = $lines;
    }

    /**
     * Return all the lines.
     *
     * @return array
     */
    public function getLines()
    {
        return $this->lines;
    }

    /**
     * Return all the valid lines.
     *
     * @return array
     */
    public function getValidLines()
    {
        return array_filter($this->lines, function ($line) {
            return $line->isValid();
        });
    }

    /**
     * Return all the invalid lines.
     *
     * @return array
     */
    public function getInvalidLines()
    {
        return array_filter($this->lines, function ($line) {
            return $line->isInvalid();
        });
    }
}
