<?php

namespace Verga\Result\Line;

class ImportedLine extends ValidLine {

    /**
     * The data imported.
     *
     * @var mixed
     */
    protected $importedData;

    /**
     * Make a new imported line instance.
     *
     * @param  int    $lineNumber
     * @param  string $line
     * @param  array  $row
     * @param  mixed  $parsedData
     * @param  mixed  $importedData
     * @return void
     */
    public function __construct($lineNumber, $line, array $row, $parsedData, $importedData)
    {
        parent::__construct($lineNumber, $line, $row, $parsedData);
        $this->importedData = $importedData;
    }

    /**
     * Is the line imported?
     * Yeah!
     *
     * @return bool
     */
    public function isImported()
    {
        return true;
    }

    /**
     * Get imported data.
     *
     * @return mixed
     */
    public function getImportedData()
    {
        return $this->importedData;
    }
}
