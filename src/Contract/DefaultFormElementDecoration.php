<?php

namespace ipl\Html\Contract;

use ipl\Html\FormDecorator\DecoratorChain;

/**
 * @phpstan-import-type decoratorFormat from DecoratorChain
 * @phpstan-import-type decoratorsFormat from DecoratorChain
 * @phpstan-import-type decoratorOptionsFormat from DecoratorChain
 *
 * @phpstan-type loaderPaths array<int, array{0: string, 1?: string}>
 */
interface DefaultFormElementDecoration
{
    /**
     * Get the default element decorators.
     *
     * @return decoratorsFormat
     */
    public function getDefaultElementDecorators(): array;

    /**
     * Set the default element decorators.
     *
     * The default decorators will be applied to all elements that do not have explicit decorators.
     * The order of the decorators is important, as it determines the rendering order.
     *
     * Please see {@see DecoratorChain::addDecorators()} for the supported array formats.
     *
     * @param decoratorsFormat $decorators
     *
     * @return $this
     */
    public function setDefaultElementDecorators(array $decorators): static;

    /**
     * Add custom element decorator loader paths for the elements
     *
     * Each entry must be an array with index 0: class namespace, index 1: class name suffix (optional).
     *
     * @param loaderPaths $loaderPaths
     *
     * @return $this
     */
    public function addElementDecoratorLoaderPaths(array $loaderPaths): static;
}
