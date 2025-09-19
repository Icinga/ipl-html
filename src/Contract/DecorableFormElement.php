<?php

namespace ipl\Html\Contract;

use ipl\Html\FormDecoration\DecoratorChain;

/**
 * Representation of form elements that support decoration
 */
interface DecorableFormElement
{
    /**
     * Get all decorators of this element
     *
     * @return DecoratorChain
     */
    public function getDecorators(): DecoratorChain;

    /**
     * Get whether the element has any decorators
     *
     * @return bool
     */
    public function hasDecorators(): bool;
}
