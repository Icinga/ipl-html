<?php

namespace ipl\Html\FormElement;

use ipl\Html\Contract\FormElement;
use ipl\Html\Contract\FormElementDecorator;
use ipl\Html\Contract\Wrappable;
use LogicException;

class FieldsetElement extends BaseFormElement
{
    use FormElements {
        FormElements::getValue as private getElementValue;
    }

    protected $tag = 'fieldset';

    public function getValue($name = null, $default = null)
    {
        if ($name === null) {
            if ($default !== null) {
                throw new LogicException("Can't provide default without a name");
            }

            return $this->getValues();
        }

        return $this->getElementValue($name, $default);
    }

    public function setValue($value)
    {
        // We expect an array/iterable here,
        // so call populate to loop through it and apply values to the child elements of the fieldset.
        $this->populate($value);

        return $this;
    }

    public function validate()
    {
        parent::validate();

        foreach ($this->getElements() as $element) {
            $element->validate();
            if (! $element->isValid()) {
                $this->valid = false;
            }
        }

        return $this;
    }

    public function getValueAttribute()
    {
        // Fieldsets do not have the value attribute.
        return null;
    }

    public function setWrapper(Wrappable $wrapper)
    {
        // TODO(lippserd): Revise decorator implementation to properly implement decorator propagation
        if (
            ! $this->hasDefaultElementDecorator()
            && $wrapper instanceof FormElementDecorator
        ) {
            $this->setDefaultElementDecorator(clone $wrapper);
        }

        return parent::setWrapper($wrapper);
    }

    protected function onElementRegistered(FormElement $element)
    {
        $element->getAttributes()->registerAttributeCallback('name', function () use ($element) {
            /**
             * We don't change the {@see BaseFormElement::$name} property of the element,
             * otherwise methods like {@see FormElements::populate() and {@see FormElements::getElement()} would fail,
             * but only change the name attribute to nest the names.
             */
            return sprintf(
                '%s[%s]',
                $this->getValueOfNameAttribute(),
                $element->getName()
            );
        });
    }
}
