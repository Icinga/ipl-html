<?php

namespace ipl\Html\FormElement;

use ipl\Validator\HexColorValidator;
use ipl\Validator\ValidatorChain;

/** @extends InputElement<string> */
class ColorElement extends InputElement
{
    protected $type = 'color';

    protected function addDefaultValidators(ValidatorChain $chain): void
    {
        $chain->add(new HexColorValidator());
    }
}
