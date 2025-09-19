<?php

namespace ipl\Tests\Html\FormDecorator;

use ipl\Html\Contract\Decorator;
use ipl\Html\Contract\FormElement;
use ipl\Html\FormDecoration\DecorationResults;

/**
 * Render the $formElement
 */
class TestRenderElementDecorator implements Decorator
{
    public function getName(): string
    {
        return 'TestRenderElement';
    }

    public function decorate(DecorationResults $results, FormElement $formElement): void
    {
        $results->append($formElement);
    }
}
