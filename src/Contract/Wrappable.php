<?php

namespace ipl\Html\Contract;

use ipl\Html\ValidHtml;

/**
 * Representation of wrappable elements
 */
interface Wrappable extends ValidHtml
{
    /**
     * Get the wrapper, if any
     *
     * @return ?Wrappable
     */
    public function getWrapper(): ?Wrappable;

    /**
     * Set the wrapper
     *
     * @param Wrappable $wrapper
     *
     * @return $this
     */
    public function setWrapper(Wrappable $wrapper): static;

    /**
     * Add a wrapper
     *
     * @param Wrappable $wrapper
     *
     * @return $this
     */
    public function addWrapper(Wrappable $wrapper): static;

    /**
     * Prepend a wrapper
     *
     * @param Wrappable $wrapper
     *
     * @return $this
     */
    public function prependWrapper(Wrappable $wrapper): static;
}
