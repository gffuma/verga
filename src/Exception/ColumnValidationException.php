<?php

namespace Verga\Exception;

use Exception;

class ColumnValidationException extends ColumnProcessException {

    /**
     * The value that fail the validation.
     *
     * @var mixed
     */
    protected $value;

    /**
     * Make new column validation exception instance.
     *
     * @param  mixed     $value
     * @param  string    $message
     * @param  int       $code
     * @param  Exception $previous
     * @return void
     */
    public function __construct($value, $message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->value = $value;
    }

    /**
     * Get the value that fail the validation.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
