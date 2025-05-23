<?php

namespace ipl\Html\FormDecorator;

use ipl\Html\Attributes;
use ipl\Html\Contract\Decorator;

/**
 * Base form element decorator
 *
 * Extend this class in order to provide concrete element decorator.
 * When extending this class you can provide default css class with {@link static::CSS_CLASS}.
 *
 * Override {@link static::registerAttributeCallbacks()} method in order to register attribute callbacks.
 */
abstract class BaseDecorator implements Decorator
{
    /** @var string CSS class for decorator element */
    protected const CSS_CLASS = 'form-element';

    /** Attributes of the element **/
    protected ?Attributes $attributes = null;

    public function __construct(array $options = [])
    {
        if (! isset($options['class']) && static::CSS_CLASS !== null) {
            $options['class'] = static::CSS_CLASS;
        }

        $this->getAttributes()->set($options);
    }

    /**
     * Get the attributes
     *
     * @return Attributes
     */
    public function getAttributes(): Attributes
    {
        if ($this->attributes === null) {
            $this->attributes = new Attributes();
            $this->registerAttributeCallbacks($this->attributes);
        }

        return $this->attributes;
    }

    /**
     * Set the attributes
     *
     * @param Attributes|array|null $attributes
     *
     * @return $this
     */
    public function setAttributes(Attributes|array|null $attributes): self
    {
        $this->attributes = Attributes::wantAttributes($attributes);
            //TODO: not working, requiredCue not set
        $this->registerAttributeCallbacks($this->attributes);

        return $this;
    }

    /**
     * Register attribute callbacks
     *
     * Override this method in order to register attribute callbacks in concrete classes.
     */
    protected function registerAttributeCallbacks(Attributes $attributes): void
    {
    }
}
