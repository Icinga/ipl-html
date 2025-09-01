<?php

namespace ipl\Html\Contract;

use ipl\Html\Attributes;
use ipl\Html\FormDecorator\DecorationResults;

/**
 * Interface for decorators that provide options
 */
interface DecoratorOptionsInterface
{
    /**
     * Get the attributes (decorator options)
     *
     * @return Attributes
     */
    public function getAttributes(): Attributes;
}
