<?php

namespace ipl\Html\FormElement;

use ipl\Html\Attribute;
use ipl\Html\Contract\FormSubmitElement;

class SubmitElement extends InputElement implements FormSubmitElement
{
    protected ?string $type = 'submit';

    protected ?string $buttonLabel = null;

    public function setLabel(?string $label): static
    {
        $this->buttonLabel = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getButtonLabel(): string
    {
        if ($this->buttonLabel === null) {
            return $this->getName();
        } else {
            return $this->buttonLabel;
        }
    }

    /**
     * @return Attribute
     */
    public function getValueAttribute(): Attribute
    {
        return new Attribute('value', $this->getButtonLabel());
    }

    public function hasBeenPressed(): bool
    {
        return $this->getButtonLabel() === $this->getValue();
    }

    public function isIgnored(): bool
    {
        return true;
    }
}
