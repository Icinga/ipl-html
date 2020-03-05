<?php

namespace ipl\Html\FormElement;

use ipl\Html\Attributes;

class TextareaElement extends BaseFormElement
{
    protected $tag = 'textarea';

    public function setValue($value)
    {
        parent::setValue($value);
        $this->setContent($value);

        return $this;
    }

    protected function registerValueCallback(Attributes $attributes)
    {
        $attributes->registerAttributeCallback(
            'value',
            null,
            [$this, 'setValue']
        );
    }
}
