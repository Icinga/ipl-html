<?php

namespace ipl\Tests\Html\FormDecorator;

use ipl\Html\Contract\Decorator;
use ipl\Html\Contract\FormElement;
use ipl\Html\FormDecorator\DecorationResults;

/**
 * Render the $formElement and skip "TestRenderElement" decorator
 */
class TestSkipRenderElementDecorator implements Decorator
{
    public function getName(): string
    {
        return 'TestSkipRenderElement';
    }

    public function decorate(DecorationResults $results, FormElement $formElement): void
    {
        $results->skipDecorators('TestRenderElement');
        $results->append($formElement);
    }
}
