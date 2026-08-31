<?php

namespace ipl\Html\FormElement;

use ipl\Html\Attributes;
use ipl\Html\Form;

class PasswordElement extends InputElement
{
    /** @var string Dummy passwd of this element to be rendered */
    public const DUMMYPASSWORD = '_ipl_form_5847ed1b5b8ca';

    protected $type = 'password';

    /** @var mixed Stored value, preserved across DUMMYPASSWORD submissions */
    protected mixed $storedValue = null;

    /**
     * Value from the very first setValue() call, used as the baseline to detect a change
     *
     * `null` is a legitimate baseline (no pre-existing secret); {@see $seeded} distinguishes that
     * from "not captured yet".
     *
     * @var mixed
     */
    protected mixed $initialValue = null;

    /** @var bool Whether {@see $initialValue} has been captured */
    protected bool $seeded = false;

    /**
     * Number of setValue() calls received so far, dummy placeholder included
     *
     * A pre-existing value seeded before the current request's own value lands on top of it takes
     * at least two calls; an element that only ever got the request's own value never does, and
     * thus has nothing worth masking.
     *
     * @var int
     */
    protected int $assignments = 0;

    /** @var ?Form The form this element is registered with */
    protected ?Form $form = null;

    public function getValue()
    {
        if ($this->hasFailedValidation()) {
            return null;
        }

        return $this->storedValue;
    }

    /**
     * Get whether a changed value has failed to validate
     *
     * Peeks at an already known form validity only, never asks for it. Calling {@see isValid()}
     * would trigger validation, which may lead to an infinite loop.
     *
     * @return bool
     */
    protected function hasFailedValidation(): bool
    {
        $value = parent::getValue();

        return $value !== null
            && $value !== static::DUMMYPASSWORD
            && (
                $this->valid === false
                || ($this->form !== null && $this->form->hasBeenValidated() && ! $this->form->isValid())
            );
    }

    public function setValue($value)
    {
        $this->assignments++;
        parent::setValue($value);
        $value = parent::getValue();
        if ($value !== static::DUMMYPASSWORD) {
            $this->storedValue = $value;

            if (! $this->seeded) {
                $this->seeded = true;
                $this->initialValue = $value;
            }
        }

        return $this;
    }

    /**
     * Validate the element using all registered validators
     *
     * The dummy placeholder isn't a real password, just a marker for "unchanged", so it's exempt
     * from validators meant for freshly typed input.
     *
     * @return $this
     */
    public function validate(): static
    {
        $this->ensureAssembled();

        if (parent::getValue() === static::DUMMYPASSWORD) {
            $this->valid = true;
            $this->clearMessages();

            return $this;
        }

        return parent::validate();
    }

    /**
     * Get the value to render into the `value` attribute
     *
     * In order:
     * - Nothing stored, or a changed value that just failed validation: `null`.
     * - No request to compare against yet (e.g. an initial GET render): masked with
     *   {@see DUMMYPASSWORD}.
     * - Nothing was seeded before this request's own value, i.e. no secret to protect (see
     *   {@see $assignments}): shown while still editing, cleared once actually submitted.
     * - Otherwise: masked if unchanged or actually submitted, shown while still editing.
     *
     * @return ?string
     */
    public function getValueAttribute()
    {
        if ($this->storedValue === null) {
            return null;
        }

        $stillEditing = $this->form !== null && ! $this->form->hasBeenSubmitted();
        if (! $stillEditing && $this->hasFailedValidation()) {
            return null;
        }

        if ($this->form === null || ! $this->form->hasBeenSent()) {
            return static::DUMMYPASSWORD;
        }

        if ($this->assignments < 2) {
            return $stillEditing ? $this->storedValue : null;
        }

        $unchanged = $this->storedValue === $this->initialValue;

        return $unchanged || ! $stillEditing ? static::DUMMYPASSWORD : $this->storedValue;
    }

    public function onRegistered(Form $form)
    {
        parent::onRegistered($form);
        $this->form = $form;
    }

    protected function registerAttributeCallbacks(Attributes $attributes)
    {
        parent::registerAttributeCallbacks($attributes);
        $attributes->registerAttributeCallback('value', $this->getValueAttribute(...), $this->setValue(...));
    }
}
