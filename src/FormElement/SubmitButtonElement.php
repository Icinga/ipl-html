<?php

namespace ipl\Html\FormElement;

use ipl\Html\Attributes;
use ipl\Html\Contract\FormSubmitElement;

class SubmitButtonElement extends ButtonElement implements FormSubmitElement
{
    protected $defaultAttributes = ['type' => 'submit'];

    /** @var string The value that's transmitted once the button is pressed */
    protected string $submitValue = 'y';

    /**
     * Get the value to transmit once the button is pressed
     *
     * @return string
     */
    public function getSubmitValue(): string
    {
        return $this->submitValue;
    }

    /**
     * Set the value to transmit once the button is pressed
     *
     * @param string $value
     *
     * @return $this
     */
    public function setSubmitValue(string $value): static
    {
        $this->submitValue = $value;

        return $this;
    }

    public function setLabel(string $label): static
    {
        return $this->setContent($label);
    }

    public function hasBeenPressed(): bool
    {
        return $this->getValue() === $this->getSubmitValue();
    }

    public function isIgnored(): bool
    {
        return true;
    }

    protected function registerAttributeCallbacks(Attributes $attributes): void
    {
        parent::registerAttributeCallbacks($attributes);

        $attributes->registerAttributeCallback('value', null, [$this, 'setSubmitValue']);
    }

    public function getValueAttribute(): string
    {
        return $this->submitValue;
    }
}
