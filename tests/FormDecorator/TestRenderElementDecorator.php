<?php

namespace ipl\Tests\Html\FormDecorator;

use ipl\Html\Contract\FormElementDecoration;
use ipl\Html\Contract\FormElement;
use ipl\Html\FormDecoration\DecorationResults;

/**
 * Render the $formElement
 */
class TestRenderElementDecorator implements FormElementDecoration
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
