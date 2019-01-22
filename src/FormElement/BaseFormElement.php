<?php

namespace ipl\Html\FormElement;

use ipl\Html\Attribute;
use ipl\Html\BaseHtmlElement;
use ipl\Stdlib\MessageContainer;
use ipl\Stdlib\Contracts\ValidatorInterface;
use InvalidArgumentException;

abstract class BaseFormElement extends BaseHtmlElement
{
    use MessageContainer;

    /** @var string */
    protected $name;

    /** @var mixed */
    protected $value;

    /** @var string */
    protected $description;

    /** @var string */
    protected $label;

    /** @var null|bool */
    protected $isValid;

    /** @var bool */
    protected $required = false;

    /** @var bool */
    protected $ignored = false;

    /** @var ValidatorInterface[] */
    protected $validators = [];

    // TODO: Validators, errors, errorMessages()

    /**
     * Link constructor.
     * @param $name
     * @param \ipl\Html\Attributes|array|null $attributes
     */
    public function __construct($name, $attributes = null)
    {
        $this->registerCallbacks();
        if ($attributes !== null) {
            $this->addAttributes($attributes);
        }
        $this->setName($name);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        $this->isValid = null;

        return $this;
    }

    public function isRequired()
    {
        return $this->required;
    }

    public function setRequired($required = true)
    {
        $this->required = (bool) $required;

        return $this;
    }

    public function isIgnored()
    {
        return $this->ignored;
    }

    public function setIgnored($ignored = true)
    {
        $this->required = (bool) $ignored;

        return $this;
    }

    /**
     * @return Attribute|string
     */
    public function getNameAttribute()
    {
        return $this->getName();
    }

    /**
     * @return mixed
     */
    public function getValueAttribute()
    {
        return $this->getValue();
    }

    /**
     * @return null
     */
    public function getNoAttribute()
    {
        return null;
    }

    /**
     * @return null|Attribute
     */
    public function getRequiredAttribute()
    {
        if ($this->isRequired()) {
            return new Attribute('required', true);
        }

        return null;
    }

    /**
     * @return ValidatorInterface[]
     */
    public function getValidators()
    {
        return $this->validators;
    }

    /**
     * @param array $validators
     */
    public function setValidators(array $validators)
    {
        $this->validators = [];
        foreach ($validators as $name => $validator) {
            if (! $validator instanceof ValidatorInterface) {
                if (is_int($name)) {
                    if (! is_array($validator)) {
                        throw new InvalidArgumentException(
                            'Invalid validator specification: Neither a validator instance nor an array provided'
                        );
                    }

                    if (! isset($validator['name'])) {
                        throw new InvalidArgumentException(
                            'Invalid validator array specification: Key "name" is missing'
                        );
                    }

                    $name = $validator['name'];

                    if (isset($validator['options'])) {
                        $validator = $validator['options'];
                    } else {
                        $validator = null;
                    }
                }

                $validator = $this->createValidator($name, $validator);
            }

            $this->validators[] = $validator;
        }
    }

    /**
     * @param $name
     * @param $options
     * @return ValidatorInterface
     */
    public function createValidator($name, $options)
    {
        $class = 'ipl\\Validator\\' . ucfirst($name) . 'Validator';
        if (class_exists($class)) {
            return new $class($options);
        } else {
            throw new InvalidArgumentException(
                "Can't create validator $name: Class $class not found"
            );
        }
    }

    public function hasValue()
    {
        $value = $this->getValue();

        return $value !== null && $value !== '' && $value !== [];
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        if ($this->isValid === null) {
            $this->validate();
        }

        return $this->isValid;
    }

    /**
     * @return bool
     */
    public function hasBeenValidatedAndIsNotValid()
    {
        return $this->isValid !== null && ! $this->isValid;
    }

    /**
     * @return $this
     */
    public function validate()
    {
        $isValid = true;

        foreach ($this->getValidators() as $validator) {
            if (! $validator->isValid($this->getValue())) {
                $isValid = false;
                foreach ($validator->getMessages() as $message) {
                    $this->addMessage($message);
                }
            }
        }

        $this->isValid = $isValid;

        return $this;
    }

    protected function registerCallbacks()
    {
        $this->registerValueCallback();
        $this->getAttributes()
            ->registerAttributeCallback('label', [$this, 'getNoAttribute'], [$this, 'setLabel'])
            ->registerAttributeCallback('name', [$this, 'getNameAttribute'], [$this, 'setName'])
            ->registerAttributeCallback('description', [$this, 'getNoAttribute'], [$this, 'setDescription'])
            ->registerAttributeCallback('validators', null, [$this, 'setValidators'])
            ->registerAttributeCallback('required', [$this, 'getRequiredAttribute'], [$this, 'setRequired']);
    }

    protected function registerValueCallback()
    {
        $this->getAttributes()->registerAttributeCallback(
            'value',
            [$this, 'getValueAttribute'],
            [$this, 'setValue']
        );
    }
}
