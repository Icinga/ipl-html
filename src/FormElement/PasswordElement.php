<?php

namespace ipl\Html\FormElement;

class PasswordElement extends InputElement
{
    protected const OBSCURE_PASSWORD = '_ipl_form_5847ed1b5b8ca';

    protected $password;

    protected $type = 'password';

    public function getValue()
    {
        return $this->password;
    }

    public function setValue($value)
    {
        parent::setValue($value);
        // Consider any changes to the password made by the parent setValue() call.
        $value = parent::getValue();

        if ($value !== static::OBSCURE_PASSWORD) {
            $this->password = $value;
        }

        return $this;
    }

    public function getValueAttribute()
    {
        return $this->hasValue() ? static::OBSCURE_PASSWORD : null;
    }
}
