<?php

namespace ipl\Html\Contract;

use ipl\Html\Form;

/**
 * Representation of form elements
 */
interface FormElement extends Wrappable, HtmlElementInterface
{
    /**
     * Get the description for the element, if any
     *
     * @return ?string
     */
    public function getDescription(): ?string;

    /**
     * Get the label for the element, if any
     *
     * @return ?string
     */
    public function getLabel(): ?string;

    /**
     * Get the validation error messages
     *
     * @return string[]
     */
    public function getMessages();

    /**
     * Add a validation error message
     *
     * @param string $message
     *
     * @return $this
     */
    public function addMessage($message);

    /**
     * Get the name of the element
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get whether the element has a value
     *
     * @return bool False if the element's value is null, the empty string or the empty array; true otherwise
     */
    public function hasValue(): bool;

    /**
     * Get the value of the element
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Set the value of the element
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function setValue($value): static;

    /**
     * Get whether the element has been validated
     *
     * @return bool
     */
    public function hasBeenValidated(): bool;

    /**
     * Get whether the element is ignored
     *
     * @return bool
     */
    public function isIgnored(): bool;

    /**
     * Get whether the element is required
     *
     * @return bool
     */
    public function isRequired(): bool;

    /**
     * Get whether the element is valid
     *
     * @return bool
     */
    public function isValid(): bool;

    /**
     * Validate the element
     *
     * @return $this
     */
    public function validate(): static;

    /**
     * Handler which is called after this element has been registered
     *
     * @param Form $form
     *
     * @return void
     */
    public function onRegistered(Form $form): void;
}
