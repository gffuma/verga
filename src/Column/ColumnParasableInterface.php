<?php

namespace Verga\Column;

interface ColumnParasableInterface {

    /**
     * Parse column.
     *
     * @param  int   $lineNumber Current csv line number.
     * @param  int   $line       Current csv raw line.
     * @param  array $row        The row parsed by delimiter of current line.
     * @param  mixed $parsedData Alredy parsed data of others column of current csv row.
     * @return mixed
     */
    public function parse($lineNumber, $line, $row, $parsedData);
}
