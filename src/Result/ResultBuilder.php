<?php

namespace Verga\Result;

class ResultBuilder {

    /**
     * List of \Verga\Line\Line.
     *
     * @var array
     */
    protected $lines;

    /**
     * Make a new result builder instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->lines = [];
    }

    /**
     * Push a new line.
     *
     * @param  \Verga\Result\Line\Line $line
     * @return \Verga\Result\ResultBuilder
     */
    public function pushLine(Line\Line $line)
    {
        $this->lines[] = $line;
        return $this;
    }

    /**
     * Run import callback on valid lines.
     *
     * @param  callable $import
     * @return \Verga\Result\ResultBuilder
     */
    public function runImport($import)
    {
        for ($i = 0; $i < count($this->lines); $i++) {
            if ($this->lines[$i]->isValid()) {
                $this->lines[$i] = $this->lines[$i]->runImport($import);
            }
        }

        return $this;
    }

    /**
     * Build the result.
     *
     * @return \Verga\Result\Result
     */
    public function build()
    {
        return new Result($this->lines);
    }
}
