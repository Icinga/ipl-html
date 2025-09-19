<?php

namespace ipl\Tests\Html\FormDecorator;

use ipl\Html\Contract\FormElementDecoration;
use ipl\Html\Contract\FormElement;
use ipl\Html\FormDecoration\DecorationResults;

/**
 * Render the $formElement and skip "TestRenderElement" decorator
 */
class TestSkipRenderElementDecorator implements FormElementDecoration
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
