<?php

namespace Verga\Importer;

use Verga\Column\ColumnParser;
use Verga\Column\ColumnParasableInterface;
use Verga\Meta\Combine;
use Verga\Result\ResultBuilder;
use Verga\Result\Line\LineBuilder;
use Verga\Exception\ColumnNotFoundException;
use Verga\Exception\ColumnValidationException;

class CsvImporter {

    /**
     * String used to split the csv columns.
     *
     * @var string
     */
    protected $delimiter;

    /**
     * Number of lines to skip.
     *
     * @var int
     */
    protected $linesToSkip;

    /**
     * Columns configuration.
     *
     * @var array
     */
    protected $columns;

    /**
     * Callable used to validate rows.
     *
     * @var callable
     */
    protected $validate;

    /**
     * Callable used to map rows.
     *
     * @var callable
     */
    protected $map;

    /**
     * Import callable.
     *
     * @var callable
     */
    protected $import;

    /**
     * Make new importer insance.
     *
     * @param  string   $delimiter
     * @param  int      $linesToSkip
     * @param  array    $columns
     * @param  callable $validate
     * @param  callable $map
     * @param  callable $import
     * @return void
     *
     */
    public function __construct($delimiter, $linesToSkip, array $columns, $validate, $map, $import)
    {
        $this->delimiter = $delimiter;
        $this->linesToSkip = $linesToSkip;
        $this->columns = $this->columnsFromConfig($columns);
        $this->validate = $validate;
        $this->map = $map;
        $this->import = $import;
    }

    /**
     * Import csv from file.
     *
     * @param  string $filepath
     * @param  bool   $shouldImportRunned
     * @return \Verga\Result\Result
     */
    public function importFromFile($filepath, $shouldImportRunned = true)
    {
        $lines = file($filepath);
        return $this->processLines($lines, $shouldImportRunned);
    }

    /**
     * Import csv from url.
     *
     * @param  string $url
     * @param  bool   $shouldImportRunned
     * @return \Verga\Result\Result
     */
    public function importFromUrl($url, $shouldImportRunned = true)
    {
        $lines = file($url);
        return $this->processLines($lines, $shouldImportRunned);
    }

    /**
     * Import csv from string.
     *
     * @param  string $str
     * @param  bool   $shouldImportRunned
     * @return \Verga\Result\Result
     */
    public function importFromString($str, $shouldImportRunned = true)
    {
        $lines = explode("\n", $str);
        return $this->processLines($lines, $shouldImportRunned);
    }

    /**
     * Process a list of csv line and give back a structured result.
     *
     * @param  array $lines
     * @param  bool  $shouldImportRunned
     * @return \Verga\Result\Result
     */
    protected function processLines(array $lines, $shouldImportRunned)
    {
        $resultBuilder = new ResultBuilder();

        foreach ($lines as $lineNumber => $line) {
            if ($this->shouldLineProcessed($lineNumber, $line)) {
                $resultBuilder->pushLine($this->processLine($lineNumber, $line));
            }
        }

        if ($shouldImportRunned) {
            $resultBuilder->runImport($this->import);
        }

        return $resultBuilder->build();
    }

    /**
     * Should line processed?
     * Currently look only to line number to check if must be
     * skipped or not.
     *
     * @param  int    $lineNumber
     * @param  string $line
     * @return boolean
     */
    protected function shouldLineProcessed($lineNumber, $line)
    {
        return $lineNumber >= $this->linesToSkip;
    }

    /**
     * Process line and give back a parsed line
     * with value parsed and related errors if any.
     *
     * @param  int    $lineNumber
     * @param  string $line
     * @return \Verga\Result\Line\Line
     */
    protected function processLine($lineNumber, $line)
    {
        $row = $this->parseLine($lineNumber, $line);
        return $this->parseRow($lineNumber, $line, $row);
    }

    /**
     * Parse csv line and return the row with values
     * splitted by delimiter.
     *
     * @param  int    $lineNumber
     * @param  string $line
     * @return array
     */
    protected function parseLine($lineNumber, $line)
    {
        return str_getcsv($line, $this->delimiter);
    }

    /**
     * Parse row and give back the line parsed.
     *
     * @param  int    $lineNumber
     * @param  string $line
     * @param  array  $row
     * @return \Verga\Result\Line\Line
     */
    protected function parseRow($lineNumber, $line, array $row)
    {
        $lineBuilder = $this->parseRowColumns($lineNumber, $line, $row, $this->columns);

        // The line is invalid, valid the entire row or mapping them
        // don't make sense...
        // Simple transform the builder into a line and give back to imported
        if ($lineBuilder->isInvalid()) {
            return $lineBuilder->build();
        }

        // Validate row
        if (is_callable($this->validate)) {
            $lineBuilder->validate($this->validate);
        }

        // Map row
        if (is_callable($this->map) && $lineBuilder->isValid()) {
            $lineBuilder->map($this->map);
        }

        return $lineBuilder->build();
    }

    /**
     * Recursive parsing of row according to columns configuration.
     *
     * @param  int    $lineNumber
     * @param  string $line
     * @param  array  $row
     * @param  array  $allParsedData
     * @param  array  $deepKeys
     * @return \Verga\Result\Line\LineBuilder
     */
    protected function parseRowColumns($lineNumber, $line, $row, $columns, $allParsedData = null, $deepKeys = [])
    {
        $lineBuilder = new LineBuilder($lineNumber, $line, $row);

        foreach ($columns as $key => $column) {

            // Keep It Real
            if (is_null($allParsedData)) {
                $currentParsedData = $lineBuilder->getParsedData();
            } else {
                $currentParsedData = $allParsedData;
                $this->deepSet($currentParsedData, $deepKeys, $lineBuilder->getParsedData());
            }

            if (is_array($column)) {
                // Recursion...
                $columnLineBuilder = $this->parseRowColumns($lineNumber, $line, $row, $column, $currentParsedData, array_merge($deepKeys, [$key]));
                $lineBuilder->setParsedDataValue($key, $columnLineBuilder->getParsedData());
                if ($columnLineBuilder->hasColumnsErrors()) {
                    $lineBuilder->setColumnsError($key, $columnLineBuilder->getColumnsErrors());
                }
            } else {
                try {
                    $columnData = $this->parseRowColumn($column, $lineNumber, $line, $row, $currentParsedData);
                    $lineBuilder->setParsedDataValue($key, $columnData);
                } catch (ColumnNotFoundException $e) {
                    $lineBuilder->setColumnsError($key, $e->getMessage());
                } catch (ColumnValidationException $e) {
                    $lineBuilder->setColumnsError($key, $e->getMessage());
                    $lineBuilder->setParsedDataValue($key, $e->getValue());
                }
            }
        }

        return $lineBuilder;
    }

    /**
     * Parse row column using given column parser.
     *
     * @param  \Verga\Column\ColumnParasableInterface $columnParser
     * @param  int    $lineNumber
     * @param  string $line
     * @param  array  $row
     * @param  mixed  $parsedData
     * @return mixed
     * @throws \Verga\Exception\ColumnNotFoundException|\Verga\Exception\ColumnValidationException
     */
    protected function parseRowColumn(ColumnParasableInterface $columnParser, $lineNumber, $line, $row, $parsedData)
    {
        return $columnParser->parse($lineNumber, $line, $row, $parsedData);
    }

    /**
     * Utilty to transform the array based configuration
     * of columns into classes.
     *
     * @param  array $columns
     * @return array
     */
    protected function columnsFromConfig(array $columns)
    {
        $c = [];

        foreach ($columns as $key => $config) {

            if ($config instanceof Combine) {
                $c[$key] = $this->columnsFromConfig($config->columns());
            } else {
                $c[$key] = $this->makeColumnParser($config);
            }
        }

        return $c;
    }

    /**
     * Set the value of array using a deep key like: ["some", "deep", "key"].
     * Taken from laravel core:
     * https://github.com/laravel/framework/blob/5.3/src/Illuminate/Support/Arr.php#L434
     *
     * @param  array  $array
     * @param  array  $keys
     * @param  mixed  $value
     * @return array
     */
    public static function deepSet(&$array, $keys, $value)
    {
        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * Make column parser.
     * TODO: Make it configurable
     *
     * @return \Verga\Column\ColumnParser
     */
    protected function makeColumnParser($config)
    {
        // Shortcut for simple column mapping
        if (is_int($config)) {
            $config = ['col' => $config];
        }

        $c = array_merge([
            'value'    => null,
            'col'      => null,
            'required' => true,
            'default'  => null,
            'validate' => null,
            'map'      => null,
        ], $config);

        return new ColumnParser(
            $c['value'],
            (int)$c['col'],
            $c['required'],
            $c['default'],
            $c['validate'],
            $c['map']
        );
    }
}
