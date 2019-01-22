<?php

namespace ipl\Validator;

use ipl\Stdlib\Contracts\ValidatorInterface;

class TestValidator implements ValidatorInterface
{
    protected $options;

    public function __construct($options = null)
    {
        $this->options = $options;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function isValid($value)
    {
    }

    public function getMessages()
    {
    }
}
