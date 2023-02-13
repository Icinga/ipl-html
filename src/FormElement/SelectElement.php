<?php

namespace ipl\Html\FormElement;

use ipl\Html\Attributes;
use ipl\Html\Common\MultipleAttribute;
use ipl\Html\Html;
use ipl\Html\HtmlElement;
use ipl\Validator\DeferredInArrayValidator;
use ipl\Validator\ValidatorChain;
use SplObjectStorage;
use UnexpectedValueException;

class SelectElement extends BaseFormElement
{
    use MultipleAttribute;

    protected $tag = 'select';

    /** @var SelectOption[] */
    protected $options = [];

    /** @var SelectOption[]|HtmlElement[] */
    protected $optionContent = [];

    /** @var array Disabled select options */
    protected $disabledOptions = [];

    /** @var array|string|null */
    protected $value;

    /**
     * Get the option with specified value
     *
     * @param string|int|null $value
     *
     * @return ?SelectOption
     */
    public function getOption($value): ?SelectOption
    {
        return $this->options[$value] ?? null;
    }

    /**
     * Set the options from specified values
     *
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options): self
    {
        $this->options = [];
        $this->optionContent = [];
        foreach ($options as $value => $label) {
            $this->optionContent[$value] = $this->makeOption($value, $label);
        }

        return $this;
    }

    /**
     * Set the specified options as disable
     *
     * @param array $disabledOptions
     *
     * @return $this
     */
    public function setDisabledOptions(array $disabledOptions): self
    {
        if (! empty($this->options)) {
            foreach ($this->options as $option) {
                $optionValue = $option->getValue();

                $option->setAttribute(
                    'disabled',
                    in_array($optionValue, $disabledOptions, ! is_int($optionValue))
                    || ($optionValue === null && in_array('', $disabledOptions, true))
                );
            }

            $this->disabledOptions = [];
        } else {
            $this->disabledOptions = $disabledOptions;
        }

        return $this;
    }

    /**
     * Get the value of the element
     *
     * Returns `array` when the attribute `multiple` is set to `true`, `string` or `null` otherwise
     *
     * @return array|string|null
     */
    public function getValue()
    {
        if ($this->isMultiple()) {
            return parent::getValue() ?? [];
        }

        return parent::getValue();
    }

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

    public function __clone()
    {
        if ($this->hasBeenAssembled) {
            $contentObjectIds = [];
            foreach ($this->optionContent as $value => $content) {
                $contentObjectIds[$value] = spl_object_id($content);
            }
            foreach (array_diff_key($this->options, $contentObjectIds) as $value => $option) {
                $contentObjectIds[$value] = spl_object_id($option);
            }
            $this->copy()->then(function (SplObjectStorage $copies) use ($contentObjectIds) {
                $lookup = array_flip($contentObjectIds);
                foreach ($copies as $option) {
                    $objectId = spl_object_id($option);
                    if (! isset($lookup[$objectId])) {
                        continue;
                    }
                    $value = $lookup[$objectId];
                    $copy = $copies->getInfo();
                    if (isset($this->optionContent[$value])) {
                        $this->optionContent[$value] = $copy;
                    }
                    if (isset($this->options[$value])) {
                        $this->options[$value] = $copy;
                    }
                }
            });

            parent::__clone();

            return;
        }

        foreach ($this->optionContent as $value => &$content) {
            if (! $content instanceof SelectOption) {
                $content->copy()->then(function (SplObjectStorage $copies) use ($value) {
                    foreach ($copies as $option) {
                        if (! $option instanceof SelectOption) {
                            continue;
                        }
                        /** @var SelectOption $copy */
                        $copy = $copies->getInfo();
                        $copy->getAttributes()->rebindAttributeCallbacks($this->objectId(), $this);
                        $this->options[$value] = $copy;
                    }
                });

                $content = clone $content;
            } else {
                $content = clone $content;
                $content->getAttributes()->rebindAttributeCallbacks($this->objectId(), $this);
                $this->options[$value] = $content;
            }
        }

        parent::__clone();
    }

    /**
     * Make the selectOption for the specified value and the label
     *
     * @param string|int|null $value Value of the option
     * @param string|array $label Label of the option
     *
     * @return SelectOption|HtmlElement
     */
    protected function makeOption($value, $label)
    {
        if (is_array($label)) {
            $grp = Html::tag('optgroup', ['label' => $value]);
            foreach ($label as $option => $val) {
                $grp->addHtml($this->makeOption($option, $val));
            }

            return $grp;
        }

        $option = (new SelectOption($value, $label))
            ->setAttribute('disabled', in_array($value, $this->disabledOptions, ! is_int($value)));

        // The value of select options is immutable,
        // so we use that for the callback instead of the option itself to
        // make it possible to rebind callbacks after cloning the element.
        $option->getAttributes()->registerAttributeCallback('selected', function () use ($value) {
            return $this->isSelectedOption($value);
        });

        $this->options[$value] = $option;

        return $this->options[$value];
    }

    /**
     * Get whether the given option is selected
     *
     * @param int|string|null $optionValue
     *
     * @return bool
     */
    protected function isSelectedOption($optionValue): bool
    {
        $value = $this->getValue();

        if ($optionValue === '') {
            $optionValue = null;
        }

        if ($this->isMultiple()) {
            if (! is_array($value)) {
                throw new UnexpectedValueException(
                    'Value must be an array when the `multiple` attribute is set to `true`'
                );
            }

            return in_array($optionValue, $this->getValue(), ! is_int($optionValue))
                || ($optionValue === null && in_array('', $this->getValue(), true));
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

        $attributes->registerAttributeCallback(
            'disabledOptions',
            null,
            [$this, 'setDisabledOptions']
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
