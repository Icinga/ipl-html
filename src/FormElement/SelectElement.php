<?php

namespace ipl\Html\FormElement;

use ipl\Html\Html;
use ipl\Validator\DeferredInArrayValidator;
use ipl\Validator\ValidatorChain;

class SelectElement extends BaseFormElement
{
    protected $tag = 'select';

    /** @var SelectOption[] */
    protected $options = [];

    protected $optionContent = [];

    public function __construct($name, $attributes = null)
    {
        $this->getAttributes()->registerAttributeCallback(
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

        parent::__construct($name, $attributes);
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
        return is_int($optionValue)
            // The loose comparison is required because PHP casts
            // numeric strings to integers if used as array keys
            ? $this->getValue() == $optionValue
            : $this->getValue() === $optionValue;
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
}
