<?php

namespace ipl\Html\FormElement;

use ipl\Html\Attributes;

class RadioOption
{
    /** @var string The default label class */
    public const LABEL_CLASS = 'radio-label';

    /** @var ?(int|string) Value of the option */
    protected int|string|null $value;

    /** @var string Label of the option */
    protected string $label;

    /** @var string|string[] Css class of the option's label */
    protected string|array $labelCssClass = self::LABEL_CLASS;

    /** @var bool Whether the radio option is disabled */
    protected bool $disabled = false;

    /** @var ?Attributes */
    protected ?Attributes $attributes;

    /**
     * RadioOption constructor.
     *
     * @param int|string|null $value
     * @param string $label
     */
    public function __construct(int|string|null $value, string $label)
    {
        $this->value = $value === '' ? null : $value;
        $this->label = $label;
    }

    /**
     * Set the label of the option
     *
     * @param string $label
     *
     * @return $this
     */
    public function setLabel(string $label): self
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
     * @return int|string|null
     */
    public function getValue(): int|string|null
    {
        return $this->value;
    }

    /**
     * Set css class to the option label
     *
     * @param string|string[] $labelCssClass
     *
     * @return $this
     */
    public function setLabelCssClass(string|array $labelCssClass): self
    {
        $this->labelCssClass = $labelCssClass;

        return $this;
    }

    /**
     * Get css class of the option label
     *
     * @return string|string[]
     */
    public function getLabelCssClass(): string|array
    {
        return $this->labelCssClass;
    }

    /**
     * Set whether to disable the option
     *
     * @param bool $disabled
     *
     * @return $this
     */
    public function setDisabled(bool $disabled = true): static
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * Get whether the option is disabled
     *
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    /**
     * Add the attributes
     *
     * @param Attributes $attributes
     *
     * @return $this
     */
    public function addAttributes(Attributes $attributes): static
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Get the attributes
     *
     * @return Attributes
     */
    public function getAttributes(): Attributes
    {
        if ($this->attributes === null) {
            $this->attributes = new Attributes();
        }

        return $this->attributes;
    }
}
