<?php

namespace ipl\Tests\Html\TestDummy;

use ipl\Html\FormElement\PasswordElement;

class ObscurePassword extends PasswordElement
{
    public static function get(): string
    {
        return static::DUMMYPASSWORD;
    }
}
