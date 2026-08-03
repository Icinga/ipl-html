<?php

namespace ipl\Html\FormElement;

use ipl\Html\Attributes;

class CheckboxElement extends InputElement
{
    /** @var string Value of the checkbox when it is checked */
    protected $checkedValue = 'y';

    /** @var string Value of the checkbox when it is not checked */
    protected $uncheckedValue = 'n';

    protected $type = 'checkbox';

    /**
     * Get whether the checkbox is checked
     *
     * @return bool
     */
    public function isChecked()
    {
        return $this->getValue() === $this->getCheckedValue();
    }

    /**
     * Set whether the checkbox is checked
     *
     * This stores a boolean that {@see getValue()} resolves to the checked or
     * unchecked value on read.
     *
     * Because this sets the element's value, a required checkbox is considered
     * to have a value, and therefore passes validation, as soon as it is
     * checked, even before the form has been submitted.
     *
     * @param bool $checked
     *
     * @return $this
     */
    public function setChecked($checked)
    {
        return $this->setValue((bool) $checked);
    }

    /**
     * Get the value of the checkbox when it is checked
     *
     * @return string
     */
    public function getCheckedValue()
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
    public function setCheckedValue($checkedValue)
    {
        $this->checkedValue = $checkedValue;

        return $this;
    }

    /**
     * Get the value of the checkbox when it is not checked
     *
     * @return string
     */
    public function getUncheckedValue()
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
    public function setUncheckedValue($uncheckedValue)
    {
        $this->uncheckedValue = $uncheckedValue;

        return $this;
    }

    /**
     * Get the value of the element
     *
     * A boolean value is resolved to the checked or unchecked value here, not
     * when it is set. This way these values can still change afterward,
     * no matter in which order the element's attributes are set.
     *
     * @return mixed
     */
    public function getValue()
    {
        $value = parent::getValue();

        if (is_bool($value)) {
            return $value ? $this->getCheckedValue() : $this->getUncheckedValue();
        }

        return $value;
    }

    public function getValueAttribute()
    {
        return $this->getCheckedValue();
    }

    protected function registerAttributeCallbacks(Attributes $attributes)
    {
        parent::registerAttributeCallbacks($attributes);

        $attributes
            ->registerAttributeCallback('checked', [$this, 'isChecked'], [$this, 'setChecked'])
            ->registerAttributeCallback('checkedValue', null, [$this, 'setCheckedValue'])
            ->registerAttributeCallback('uncheckedValue', null, [$this, 'setUncheckedValue']);
    }

    public function renderUnwrapped()
    {
        $html = parent::renderUnwrapped();

        $value = $this->getAttribute('disabled')->getValue() && $this->isChecked()
            ? $this->getCheckedValue()
            : $this->getUncheckedValue();

        return (new HiddenElement($this->getValueOfNameAttribute(), ['value' => $value])) . $html;
    }

    /**
     * Determine if the checkbox is considered "checked"
     *
     * Returns true if the current value matches the checked value, otherwise false.
     *
     * @return bool
     */
    public function hasValue(): bool
    {
        return $this->isChecked();
    }
}
