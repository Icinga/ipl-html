<?php

namespace ipl\Html\FormElement;

use ipl\Html\Attributes;

class MultiselectElement extends SelectElement
{
    /** @var array Selected values */
    protected $value = [];

    protected $defaultAttributes = ['multiple' => true];

    protected function registerValueCallback(Attributes $attributes)
    {
        $attributes->registerAttributeCallback(
            'value',
            null,
            [$this, 'setValue']
        );
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
}
