<?php

namespace Verga\Result\Line;

abstract class Line {

    /**
     * Csv line number.
     *
     * @var int
     */
    protected $lineNumber;

    /**
     * Original csv line.
     *
     * @var string
     */
    protected $line;

    /**
     * The row parsed array using delimiter.
     *
     * @var array
     */
    protected $row;

    /**
     * The data parsed from row.
     *
     * @var mixed
     */
    protected $parsedData;

    /**
     * Make a new line instance.
     *
     * @param  int    $lineNumber
     * @param  string $line
     * @param  array  $row
     * @param  mixed  $parsedData
     * @return void
     */
    public function __construct($lineNumber, $line, array $row, $parsedData)
    {
        $this->lineNumber = $lineNumber;
        $this->line = $line;
        $this->row = $row;
        $this->parsedData = $parsedData;
    }

    /**
     * Get the line number.
     *
     * @return int
     */
    public function getLineNumber()
    {
        return $this->lineNumber;
    }

    /**
     * Get the line.
     *
     * @return string
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * Get the row.
     *
     * @return array
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * Get parsed data.
     *
     * @return mixed
     */
    public function getParsedData()
    {
        return $this->parsedData;
    }

    /**
     * Is the line imported?
     *
     * @return bool
     */
    public abstract function isImported();

    /**
     * Is the line valid?
     *
     * @return bool
     */
    public abstract function isValid();

    /**
     * Simple the opposite of valid eheh.
     *
     * @return bool
     */
    public function isInvalid()
    {
        return ! $this->isValid();
    }
}
