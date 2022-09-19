<?php

namespace ipl\Html\FormElement;

use ipl\Validator\HexColorValidator;

class ColorElement extends InputElement
{
    protected $type = 'color';

    public function addDefaultValidators(): void
    {
        $this->getValidators()->add(new HexColorValidator());
    }
}
