<?php

namespace ipl\Html\FormElement;

use ipl\Html\ValidHtml;

interface FormElementInterface extends ValidHtml
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @param $value
     * @return $this
     */
    public function setValue($value);

    /**
     * @return bool
     */
    public function hasValue();

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel($label);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * @return bool
     */
    public function isRequired();

    public function setRequired($required = true);

    /**
     * @return bool
     */
    public function isIgnored();

    public function setIgnored($ignored = true);

    /**
     * @return bool
     */
    public function isValid();

    /**
     * @return bool
     */
    public function hasBeenValidatedAndIsNotValid();

    /**
     * @return $this
     */
    public function validate();

    /**
     * Get whether there are any messages
     *
     * @return  bool
     */
    public function hasMessages();

    /**
     * Get all messages
     *
     * @return  array
     */
    public function getMessages();

    /**
     * Set the given messages overriding existing ones
     *
     * @param   string[]    $messages
     *
     * @return  $this
     */
    public function setMessages(array $messages);

    /**
     * Add a single message
     *
     * @param   string  $message
     * @param   mixed   ...$args    Other optional parameters for sprintf-style messages
     *
     * @return $this
     */
    public function addMessage($message);

    /**
     * Drop eventually existing messages
     *
     * @return  $this
     */
    public function clearMessages();
}
