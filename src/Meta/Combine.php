<?php

namespace Verga\Meta;

class Combine {

    /**
     * Columns configuration.
     *
     * @var array
     */
    protected $columns;

    /**
     * Make new combine meta.
     *
     * @param  array $columns
     * @return void
     */
    public function __construct(array $columns)
    {
        $this->columns = $columns;
    }

    /**
     * Get columns configuration.
     *
     * @return array
     */
    public function columns()
    {
        return $this->columns;
    }
}
