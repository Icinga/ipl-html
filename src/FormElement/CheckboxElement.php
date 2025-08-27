<?php

namespace ipl\Html\FormElement;

use ipl\Html\Attributes;

class CheckboxElement extends InputElement
{
    /** @var bool Whether the checkbox is checked */
    protected bool $checked = false;

    /** @var string Value of the checkbox when it is checked */
    protected string $checkedValue = 'y';

    /** @var string Value of the checkbox when it is not checked */
    protected string $uncheckedValue = 'n';

    protected ?string $type = 'checkbox';

    /**
     * Get whether the checkbox is checked
     *
     * @return bool
     */
    public function isChecked(): bool
    {
        return $this->checked;
    }

    /**
     * Set whether the checkbox is checked
     *
     * @param bool $checked
     *
     * @return $this
     */
    public function setChecked(bool $checked): static
    {
        $this->checked = $checked;

        return $this;
    }

    /**
     * Get the value of the checkbox when it is checked
     *
     * @return string
     */
    public function getCheckedValue(): string
    {
        return $this->checkedValue;
    }

    /**
     * Set the value of the checkbox when it is checked
     *
     * @param string $checkedValue
     *
     * @return $this
     */
    public function setCheckedValue(string $checkedValue): static
    {
        $this->checkedValue = $checkedValue;

        return $this;
    }

    /**
     * Get the value of the checkbox when it is not checked
     *
     * @return string
     */
    public function getUncheckedValue(): string
    {
        return $this->uncheckedValue;
    }

    /**
     * Set the value of the checkbox when it is not checked
     *
     * @param string $uncheckedValue
     *
     * @return $this
     */
    public function setUncheckedValue(string $uncheckedValue): static
    {
        $this->uncheckedValue = $uncheckedValue;

        return $this;
    }

    public function setValue($value): static
    {
        if (is_bool($value)) {
            $value = $value ? $this->getCheckedValue() : $this->getUncheckedValue();
        }

        $this->setChecked($value === $this->getCheckedValue());

        return parent::setValue($value);
    }

    public function getValueAttribute(): string
    {
        return $this->getCheckedValue();
    }

    protected function registerAttributeCallbacks(Attributes $attributes): void
    {
        parent::registerAttributeCallbacks($attributes);

        $attributes
            ->registerAttributeCallback('checked', [$this, 'isChecked'], [$this, 'setChecked'])
            ->registerAttributeCallback('checkedValue', null, [$this, 'setCheckedValue'])
            ->registerAttributeCallback('uncheckedValue', null, [$this, 'setUncheckedValue']);
    }

    public function renderUnwrapped(): string
    {
        $html = parent::renderUnwrapped();

        return (new HiddenElement($this->getValueOfNameAttribute(), ['value' => $this->getUncheckedValue()])) . $html;
    }

    /**
     * Determine if the checkbox is considered "checked".
     * Returns true if the current value matches the checked value, otherwise false.
     */
    public function hasValue(): bool
    {
        return $this->getValue() === $this->getCheckedValue();
    }
}
