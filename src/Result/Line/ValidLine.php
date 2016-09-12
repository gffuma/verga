<?php

namespace Verga\Result\Line;

class ValidLine extends Line {

    /**
     * Is the line valid?
     * Sure the class name is ValidLine eeheh.
     *
     * @return bool
     */
    public function isValid()
    {
        return true;
    }

    /**
     * Is the line imported?
     * Valid but not alredy imported.
     *
     * @return bool
     */
    public function isImported()
    {
        return false;
    }

    /**
     * Return a new instance of ImportedLine running the import callback.
     *
     * @param  callable $import
     * @return \Verga\Result\Line\ImportedLine
     */
    public function runImport($import)
    {
        return new ImportedLine(
            $this->lineNumber,
            $this->line,
            $this->row,
            $this->parsedData,
            call_user_func_array($import, [
                $this->parsedData,
                $this->row,
                $this->line,
                $this->lineNumber,
            ])
        );
    }
}
