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

    public function validate()
    {
        foreach ($this->getValue() as $value) {
            $option = $this->getOption($value);
            if (! $option || $option->getAttributes()->has('disabled')) {
                $this->valid = false;
                $this->addMessage("'$value' is not allowed here");

                return $this;
            }
        }

        BaseFormElement::validate();

        return $this;
    }

    protected function assemble()
    {
        $this->setAttribute('multiple', true);

        parent::assemble();
    }
}
