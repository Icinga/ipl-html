<?php

namespace ipl\Html\Contract;

use ipl\Html\Attributes;

/**
 * Options for decorators
 *
 * This trait manages the decorator options.
 */
trait DecoratorOptions
{
    /** @var ?Attributes Attributes of the decorator */
    protected ?Attributes $attributes = null;

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
     * @param ?(Attributes|array) $attributes
     *
     * @return $this
     */
    public function setAttributes(Attributes|array|null $attributes): static
    {
        $this->attributes = Attributes::wantAttributes($attributes);

        return $this;
    }

    /**
     * Register attribute callbacks
     *
     * Override this method in order to register attribute callbacks in concrete classes.
     *
     * @param Attributes $attributes
     */
    protected function registerAttributeCallbacks(Attributes $attributes): void
    {
    }
}
