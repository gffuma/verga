<?php

namespace Verga\Result\Line;

class InvalidLine extends Line {

    /**
     * Columns errors.
     *
     * @var array
     */
    protected $columnsErrors;

    /**
     * Line error.
     *
     * @var string
     */
    protected $lineError;

    /**
     * Make a new invalid line instance.
     *
     * @param  int    $lineNumber
     * @param  string $line
     * @param  array  $row
     * @param  mixed  $parsedData
     * @param  array  $columnsErrors
     * @param  string $lineError
     * @return void
     */
    public function __construct($lineNumber, $line, array $row, $parsedData, array $columnsErrors, $lineError)
    {
        parent::__construct($lineNumber, $line, $row, $parsedData);
        $this->columnsErrors = $columnsErrors;
        $this->lineError = $lineError;
    }

    /**
     * Is the line valid?
     * Nope.
     *
     * @return bool
     */
    public function isValid()
    {
        return false;
    }

    /**
     * Is the line imported?
     * Nope.
     *
     * @return bool
     */
    public function isImported()
    {
        return false;
    }

    /**
     * Get the columns errors.
     *
     * @return array
     */
    public function getColumnsErrors()
    {
        return $this->columnsErrors;
    }

    /**
     * Has columns errors?
     *
     * @return bool
     */
    public function hasColumnsErrors()
    {
        return ! empty($this->columnsErrors);
    }

    /**
     * Get the columns errors.
     *
     * @return array
     */
    public function getLineError()
    {
        return $this->lineError;
    }

    /**
     * Has line error?
     *
     * @return bool
     */
    public function hasLineError()
    {
        return ! is_null($this->lineError);
    }
}
