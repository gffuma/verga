<?php

namespace Verga\Column;

use Verga\Exception\ColumnNotFoundException;
use Verga\Exception\ColumnValidationException;
use Verga\Meta\Error;

class ColumnParser implements ColumnParasableInterface {

    /**
     * When given is used as the fixed value for the column.
     *
     * @var mixed
     */
    protected $fixedValue;

    /**
     * Column index of csv.
     *
     * @var int
     */
    protected $columnIndex;

    /**
     * Is the column required?
     *
     * @var boolean
     */
    protected $required;

    /**
     * The default value when column index does't exist for the current row.
     *
     * @var mixed
     */
    protected $defaultValue;

    /**
     * An optional callable for column validation.
     *
     * @var callable
     */
    protected $validate;

    /**
     * An optional callable for column mapping.
     *
     * @var callable
     */
    protected $map;

    /**
     * Make a new column parser instance.
     *
     * @param  mixed    $fixedValue
     * @param  int      $columnIndex
     * @param  boolean  $required
     * @param  mixed    $defaultValue
     * @param  callable $validate
     * @param  callable $map
     * @return void
     */
    public function __construct($fixedValue, $columnIndex, $required, $defaultValue, $validate, $map)
    {
        $this->fixedValue = $fixedValue;
        $this->columnIndex = $columnIndex;
        $this->required = $required;
        $this->defaultValue = $defaultValue;
        $this->validate = $validate;
        $this->map = $map;
    }

    /**
     * Parse column.
     *
     * @param  int   $lineNumber Current csv line number.
     * @param  int   $line       Current csv raw line.
     * @param  array $row        The row parsed by delimiter of current line.
     * @param  mixed $parsedData Alredy parsed data of others column of current csv row.
     * @return mixed
     * @throws \Verga\Exception\ColumnNotFoundException|\Verga\Exception\ColumnValidationException
     */
    public function parse($lineNumber, $line, $row, $parsedData)
    {
        // Use the fixed value as parsed value
        if (! is_null($this->fixedValue)) {
            return $this->fixedValue;
        }

        // Check if column exist
        if (! $this->hasColumn($lineNumber, $row, $parsedData)) {
            // Throw exception when column is required
            if ($this->required) {
                throw new ColumnNotFoundException(
                    $this->getNotFoundedColumnMessage($lineNumber, $row, $parsedData)
                );
            }
            // Otherwise give back the default value
            return $this->defaultValue;
        }

        // Get the value
        $value = $this->getColumnValue($lineNumber, $row, $parsedData);

        // Validate them
        if (is_callable($this->validate)) {
            $validation = call_user_func_array($this->validate, [$value, $parsedData, $row, $lineNumber, $line]);
            if ($validation instanceof Error) {
                throw new ColumnValidationException($value, $validation->error());
            }
        }

        // Map them
        if (is_callable($this->map)) {
            $value = call_user_func_array($this->map, [$value, $parsedData, $row, $lineNumber, $line]);
        }

        return $value;
    }

    /**
     * Check if row has column.
     *
     * @param  int   $lineNumber
     * @param  array $row
     * @param  mixed $parsedData
     * @return boolean
     */
    protected function hasColumn($lineNumber, $row, $parsedData)
    {
        return isset($row[$this->columnIndex]);
    }

    /**
     * Get the raw column value of given row.
     *
     * @param  int   $lineNumber
     * @param  array $row
     * @param  mixed $parsedData
     * @return string
     */
    protected function getColumnValue($lineNumber, $row, $parsedData)
    {
        return $row[$this->columnIndex];
    }

    /**
     * Get the message when the column doesn't exist and is required.
     * Specific method for customomize the error message.
     *
     * @param  int   $lineNumber
     * @param  array $row
     * @param  mixed $parsedData
     * @return string
     */
    protected function getNotFoundedColumnMessage($lineNumber, $row, $parsedData)
    {
        return "The column at position {$this->columnIndex} doesn't exist.";
    }
}
