<?php

namespace ipl\Html\FormElement;

use ipl\Html\Attributes;

class CheckboxElement extends InputElement
{
    /** @var bool Whether the checkbox is checked */
    protected $checked = false;

    /** @var string Value of the checkbox when it is checked */
    protected $checkedValue = 'y';

    protected $type = 'checkbox';

    /**
     * Get whether the checkbox is checked
     *
     * @return bool
     */
    public function isChecked()
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
    public function setChecked($checked)
    {
        $this->checked = $checked;

        return $this;
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

    public function setValue($value)
    {
        parent::setValue($value);

        $this->setChecked($value === $this->getCheckedValue());
    }

    public function getValueAttribute()
    {
        return $this->getCheckedValue();
    }

    protected function registerAttributeCallbacks(Attributes $attributes)
    {
        parent::registerAttributeCallbacks($attributes);

        $attributes->registerAttributeCallback(
            'checked',
            [$this, 'isChecked'],
            [$this, 'setChecked']
        );
    }
}
