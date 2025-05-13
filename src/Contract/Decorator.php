<?php

namespace ipl\Html\Contract;

use ipl\Html\FormDecorator\DecorationResults;
use ipl\Html\FormElement\BaseFormElement;

/**
 * Representation of form element decorators
 */
interface Decorator
{
    /**
     * Decorate the given form element
     *
     * A decorator can create some HTML elements, apply attributes to the given $formElement and created element.
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
     * public function decorate(DecorationResults $results, BaseFormElement $formElement): void
     * {
     *     $description = $formElement->getDescription();
     *
     *     if ($description === null || $formElement instanceof FieldsetElement) {
     *         return;
     *     }
     *
     *     $results->append(new HtmlElement('p', null, new Text($description)));
     * }
     * ```
     *
     * @param DecorationResults $results
     *
     * @param BaseFormElement $formElement
     */
    public function decorate(DecorationResults $results, BaseFormElement $formElement): void;
}
