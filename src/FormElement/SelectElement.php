<?php

namespace ipl\Html\FormElement;

use Closure;
use ipl\Html\Html;
use ipl\I18n\Translation;
use ipl\Validator\DeferredInArrayValidator;

class SelectElement extends BaseFormElement
{
    use Translation;

    protected $tag = 'select';

    /** @var SelectOption[] */
    protected $options = [];

    protected $optionContent = [];

    /** @var Closure */
    protected $getPossibleValues;

    /** @var array Disabled values */
    protected $disabledOptions = [];

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
        $this->valid = null;
        $this->disabledOptions[] = $value;

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

    public function addDefaultValidators()
    {
        $this->getPossibleValues = Closure::fromCallable(function (): array {
            return array_diff(array_keys($this->options), $this->disabledOptions);
        });

        $this->getValidators()->add(new DeferredInArrayValidator($this->getPossibleValues));
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
     * @param $optionValue
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

    protected function assemble()
    {
        $this->addHtml(...array_values($this->optionContent));
    }
}
