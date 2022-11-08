<?php

namespace ipl\Html\FormElement;

class MultiselectElement extends SelectElement
{
    /** @var array Selected values */
    protected $value = [];

    public function getValueAttribute()
    {
        // select elements don't have a value attribute
        return null;
    }

    public function getNameAttribute()
    {
        return $this->getName() . '[]';
    }

    public function setValue($value)
    {
        $this->value = empty($value) ? [] : (array) $value;
        $this->valid = null;

        return $this;
    }

    protected function isSelectedOption($optionValue)
    {
        return in_array($optionValue, $this->getValue(), ! is_int($optionValue));
    }

    protected function assemble()
    {
        $this->setAttribute('multiple', true);

        parent::assemble();
    }
}
