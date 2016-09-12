<?php

namespace Verga\Result\Line;

use Verga\Meta\Error;

class LineBuilder {

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
     * Make a new line builder instance.
     *
     * @param  int    $lineNumber
     * @param  string $line
     * @param  array  $row
     * @param  mixed  $parsedData
     * @return void
     */
    public function __construct($lineNumber, $line, array $row)
    {
        $this->lineNumber = $lineNumber;
        $this->line = $line;
        $this->row = $row;
        $this->parsedData = [];
        $this->columnsErrors = [];
    }


    /**
     * Set parsed data value on column.
     *
     * @param string $columnName
     * @param string $error
     * @return \Verga\Result\Line\LineBuilder
     */
    public function setParsedDataValue($columnName, $value)
    {
        $this->parsedData[$columnName] = $value;
        return $this;
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
     * Set column error.
     *
     * @param string $columnName
     * @param string $error
     * @return \Verga\Result\Line\LineBuilder
     */
    public function setColumnsError($columnName, $error)
    {
        $this->columnsErrors[$columnName] = $error;
        return $this;
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
     * Get the line error.
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

    /**
     * Is the line valid?
     *
     * @return bool
     */
    public function isValid()
    {
        return !$this->hasColumnsErrors() && !$this->hasLineError();
    }

    /**
     * Simple the opposite of valid eheh.
     *
     * @return bool
     */
    public function isInvalid()
    {
        return ! $this->isValid();
    }

    /**
     * Validate the entire line using the given callback,
     * when failed save the line error.
     *
     * @param  callable $validate
     * @return \Verga\Result\Line\LineBuilder
     */
    public function validate($validate)
    {
        $validation = call_user_func_array($validate, [
            $this->parsedData,
            $this->row,
            $this->lineNumber,
            $this->line,
        ]);

        if ($validation instanceof Error) {
            $this->lineError = $validation->error();
        }

        return $this;
    }

    /**
     * Map line parsed data unsing given callback.
     *
     * @param  callable $map
     * @return \Verga\Result\Line\LineBuilder
     */
    public function map($map)
    {
        $this->parsedData = call_user_func_array($map, [
            $this->parsedData,
            $this->row,
            $this->lineNumber,
            $this->line,
        ]);

        return $this;
    }

    /**
     * Build line.
     *
     * @return \Verga\Result\Line\Line
     */
    public function build()
    {
        if ($this->hasColumnsErrors() || $this->hasLineError()) {
            return new InvalidLine(
                $this->lineNumber,
                $this->line,
                $this->row,
                $this->parsedData,
                $this->columnsErrors,
                $this->lineError
            );
        }

        return new ValidLine(
            $this->lineNumber,
            $this->line,
            $this->row,
            $this->parsedData
        );
    }
}
