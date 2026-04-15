<?php

namespace ipl\Html\FormElement;

use InvalidArgumentException;
use ipl\Html\Common\MultipleAttribute;
use ipl\Html\Contract\DefaultFormElementDecoration;
use ipl\Html\Contract\FormElement;
use ipl\Html\Contract\FormElementDecorator;
use ipl\Html\Contract\Wrappable;
use ipl\Html\Form;
use LogicException;

use function ipl\Stdlib\get_php_type;

class FieldsetElement extends BaseFormElement implements \ipl\Html\Contract\FormElements, DefaultFormElementDecoration
{
    use FormElements {
        FormElements::getValue as private getElementValue;
    }

    protected $tag = 'fieldset';

    protected ?Form $form = null;

    /**
     * Get whether any of this set's elements has a value
     *
     * @return bool
     */
    public function hasValue()
    {
        $this->ensureAssembled();

        foreach ($this->getElements() as $element) {
            if ($element->hasValue()) {
                return true;
            }
        }

        return false;
    }

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
        if (! is_iterable($value)) {
            throw new InvalidArgumentException(
                sprintf(
                    '%s expects parameter $value to be an array|iterable, got %s instead',
                    __METHOD__,
                    get_php_type($value)
                )
            );
        }

        // We expect an array/iterable here,
        // so call populate to loop through it and apply values to the child elements of the fieldset.
        $this->populate($value);

        return $this;
    }

    public function validate()
    {
        $this->ensureAssembled();

        $this->valid = true;
        foreach ($this->getElements() as $element) {
            $element->validate();
            if (! $element->isValid()) {
                $this->valid = false;
            }
        }

        if ($this->valid) {
            parent::validate();
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

    public function onRegistered(Form $form)
    {
        $this->form = $form;
        parent::onRegistered($form);

        // Any children that were added before we joined the form never got their own
        // onRegistered call — propagate now. Use $this->elements directly rather than
        // getElements(), because some subclasses (notably TermInput) override
        // getElements() to force ensureAssembled(). Calling that here would run
        // assemble() before the parent's decorate() has attached our decorator,
        // leaving all children undecorated (no label, bare input).
        foreach ($this->elements as $element) {
            $element->onRegistered($form);
        }

        // Top-level trigger: once the outer form has finished its assemble pass,
        // assemble ourselves. At that point we've been decorated and every sibling
        // and post-addElement setter has been applied.
        $form->on(Form::ON_ASSEMBLED, function () {
            $this->ensureAssembled();
        });

        // Cascade: once we're assembled, any FieldsetElement children we added
        // during assemble() need the same treatment. Their own Form::ON_ASSEMBLED
        // listener is registered too late (the event already fired), so we drive
        // their assembly directly here. Their own ON_ASSEMBLED listener will then
        // cascade further down.
        $this->on(static::ON_ASSEMBLED, function () {
            foreach ($this->getElements() as $element) {
                if ($element instanceof FieldsetElement) {
                    $element->ensureAssembled();
                }
            }
        });
    }

    public function isValidEvent($event)
    {
        return $event === static::ON_ASSEMBLED || parent::isValidEvent($event);
    }

    protected function onElementRegistered(FormElement $element)
    {
        // IMPORTANT: register the name-nesting callback BEFORE propagating onRegistered.
        // Elements that read getValueOfNameAttribute() inside their own onRegistered
        // (e.g. TermInput building suggestion ids) need the nested name to be available
        // on the first call — otherwise they see the un-nested name.
        $element->getAttributes()->registerAttributeCallback('name', function () use ($element) {
            $multiple = false;
            if (in_array(MultipleAttribute::class, class_uses($element), true)) {
                $multiple = $element->isMultiple();
            }

            // This check is required as the getEscapedName method is not implemented in
            // the FormElement interface
            if ($element instanceof BaseFormElement) {
                $name = $element->getEscapedName();
            } else {
                $name = $element->getName();
            }

            /**
             * We don't change the {@see BaseFormElement::$name} property of the element,
             * otherwise methods like {@see FormElements::populate() and {@see FormElements::getElement()} would fail,
             * but only change the name attribute to nest the names.
             */
            return sprintf(
                '%s[%s]%s',
                $this->getValueOfNameAttribute(),
                $name,
                $multiple ? '[]' : ''
            );
        });

        if ($this->form !== null) {
            $element->onRegistered($this->form);
        }
    }
}
