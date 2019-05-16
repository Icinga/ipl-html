<?php

namespace ipl\Html\FormDecorator;

use ipl\Html\BaseHtmlElement;

// TODO: FormElementDecoratorInterface?
interface DecoratorInterface
{
    /**
     * @param BaseHtmlElement $element
     * @return static
     */
    public function decorate(BaseHtmlElement $element);
}
