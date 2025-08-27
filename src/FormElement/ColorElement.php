<?php

namespace ipl\Html\FormElement;

use ipl\Validator\HexColorValidator;
use ipl\Validator\ValidatorChain;

class ColorElement extends InputElement
{
    protected ?string $type = 'color';

    protected function addDefaultValidators(ValidatorChain $chain): void
    {
        $chain->add(new HexColorValidator());
    }
}
