<?php

namespace ipl\Html\Contract;

use ipl\Html\FormDecorator\DecorationResults;

/**
 * Representation of form element decorator
 */
interface Decorator
{
    /**
     * Get the name of the decorator
     *
     * The name is used to identify the decorator. Decorators can skip upcoming decorator(s) by its name using
     * {@see DecorationResults::$skipDecorators()} method.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Decorate the given form element
     *
     * A decorator can create HTML elements and apply attributes to the given $formElement element.
     * Only the elements added to {@see DecorationResults} are rendered in the end.
     *
     * The element can be added to the {@see DecorationResults} using the following three methods:
     * - {@see DecorationResults::append()} will add the element to the end of the results.
     * - {@see DecorationResults::prepend()} will add the element to the beginning of the results.
     * - {@see DecorationResults::wrap()} will wrap the results with the given element.
     *
     * **Reference implementation:**
     *
     *```
     *
     * public function decorate(DecorationResults $results, FormElement $formElement): void
     * {
     *     $description = $formElement->getDescription();
     *
     *     if ($description === null) {
     *         return;
     *     }
     *
     *     $results->append(new HtmlElement('p', null, new Text($description)));
     * }
     * ```
     *
     * @param DecorationResults $results
     * @param FormElement $formElement
     *
     * @return void
     */
    public function decorate(DecorationResults $results, FormElement $formElement): void;
}
