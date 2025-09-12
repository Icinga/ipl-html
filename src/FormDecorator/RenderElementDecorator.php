<?php

namespace ipl\Html\FormDecorator;

use ipl\Html\Contract\Decorator;
use ipl\Html\Contract\FormElement;
use ipl\Html\Contract\HtmlElementInterface;

/**
 * Render the form element itself
 */
class RenderElementDecorator implements Decorator
{
    public function getName(): string
    {
        return 'RenderElement';
    }

    public function decorate(DecorationResults $results, FormElement & HtmlElementInterface $formElement): void
    {
        $results->append($formElement);
    }
}
