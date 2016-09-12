<?php

namespace Verga\Meta;

class Error {

    /**
     * Error description.
     *
     * @var string
     */
    protected $error;

    /**
     * Make new error meta.
     *
     * @param  string $error
     * @return void
     */
    public function __construct($error)
    {
        $this->error = $error;
    }

    /**
     * Get error description.
     *
     * @return string
     */
    public function error()
    {
        return $this->error;
    }
}
