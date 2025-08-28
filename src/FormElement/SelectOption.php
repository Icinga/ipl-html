<?php

namespace ipl\Html\FormElement;

use ipl\Html\BaseHtmlElement;

class SelectOption extends BaseHtmlElement
{
    protected $tag = 'option';

    /** @var string|int|null Value of the option */
    protected string|int|null $value;

    /** @var string Label of the option */
    protected string $label;

    /**
     * SelectOption constructor.
     *
     * @param int|string|null $value
     * @param string $label
     */
    public function __construct(int|string|null $value, string $label)
    {
        $this->value = $value === '' ? null : $value;
        $this->label = $label;

        $this->getAttributes()->registerAttributeCallback('value', [$this, 'getValueAttribute']);
    }

    /**
     * Set the label of the option
     *
     * @param string $label
     *
     * @return $this
     */
    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get the label of the option
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Get the value of the option
     *
     * @return string|int|null
     */
    public function getValue(): int|string|null
    {
        return $this->value;
    }

    /**
     * Callback for the value attribute
     *
     * @return string
     */
    public function getValueAttribute(): string
    {
        return (string) $this->getValue();
    }

    protected function assemble(): void
    {
        $this->setContent($this->getLabel());
    }
}
