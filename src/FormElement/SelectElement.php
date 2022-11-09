<?php

namespace ipl\Html\FormElement;

use ipl\Html\Attributes;
use ipl\Html\Common\MultipleAttribute;
use ipl\Html\Html;
use ipl\Validator\DeferredInArrayValidator;
use ipl\Validator\ValidatorChain;
use UnexpectedValueException;

class SelectElement extends BaseFormElement
{
    use MultipleAttribute;

    protected $tag = 'select';

    /** @var SelectOption[] */
    protected $options = [];

    protected $optionContent = [];

    /** @var array|string */
    protected $value;

    public function getValueAttribute()
    {
        // select elements don't have a value attribute
        return null;
    }

    public function getNameAttribute()
    {
        $name = $this->getName();

        return $this->isMultiple() ? ($name . '[]') : $name;
    }

    public function getValue()
    {
        if ($this->isMultiple()) {
            return parent::getValue() ?? [];
        }

        return parent::getValue();
    }

    public function hasOption($value)
    {
        return isset($this->options[$value]);
    }

    public function deselect()
    {
        $this->setValue(null);

        return $this;
    }

    public function disableOption($value)
    {
        if ($option = $this->getOption($value)) {
            $option->getAttributes()->add('disabled', true);
        }

        return $this;
    }

    public function disableOptions($values)
    {
        foreach ($values as $value) {
            $this->disableOption($value);
        }

        return $this;
    }

    /**
     * @param $value
     * @return SelectOption|null
     */
    public function getOption($value)
    {
        if ($this->hasOption($value)) {
            return $this->options[$value];
        } else {
            return null;
        }
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = [];
        $this->optionContent = [];
        foreach ($options as $value => $label) {
            $this->optionContent[$value] = $this->makeOption($value, $label);
        }

        return $this;
    }

    protected function makeOption($value, $label)
    {
        if (is_array($label)) {
            $grp = Html::tag('optgroup', ['label' => $value]);
            foreach ($label as $option => $val) {
                $grp->addHtml($this->makeOption($option, $val));
            }

            return $grp;
        } else {
            $option = new SelectOption($value, $label);
            $option->getAttributes()->registerAttributeCallback('selected', function () use ($option) {
                return $this->isSelectedOption($option->getValue());
            });
            $this->options[$value] = $option;

            return $this->options[$value];
        }
    }

    /**
     * Whether the given option is a selected option
     *
     * @param int|string $optionValue
     *
     * @return bool
     */
    protected function isSelectedOption($optionValue)
    {
        $value = $this->getValue();

        if ($this->isMultiple()) {
            if (! is_array($value)) {
                throw new UnexpectedValueException(
                    'Value must be an array when the `multiple` attribute is set to `true`'
                );
            }

            return in_array($optionValue, $value, ! is_int($optionValue));
        }

        if (is_array($value)) {
            throw new UnexpectedValueException(
                'Value cannot be an array without setting the `multiple` attribute to `true`'
            );
        }

        return is_int($optionValue)
            // The loose comparison is required because PHP casts
            // numeric strings to integers if used as array keys
            ? $value == $optionValue
            : $value === $optionValue;
    }

    protected function addDefaultValidators(ValidatorChain $chain): void
    {
        $chain->add(
            new DeferredInArrayValidator(function (): array {
                $possibleValues = [];

                foreach ($this->options as $option) {
                    if ($option->getAttributes()->get('disabled')->getValue()) {
                        continue;
                    }

                    $possibleValues[] = $option->getValue();
                }

                return $possibleValues;
            })
        );
    }

    protected function assemble()
    {
        $this->addHtml(...array_values($this->optionContent));
    }

    protected function registerAttributeCallbacks(Attributes $attributes)
    {
        parent::registerAttributeCallbacks($attributes);

        $attributes->registerAttributeCallback(
            'options',
            null,
            [$this, 'setOptions']
        );

        // ZF1 compatibility:
        $this->getAttributes()->registerAttributeCallback(
            'multiOptions',
            null,
            [$this, 'setOptions']
        );

        $this->registerMultipleAttributeCallback($attributes);
    }
}
