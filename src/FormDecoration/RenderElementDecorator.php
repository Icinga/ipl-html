<?php

namespace ipl\Html\FormDecoration;

use ipl\Html\Contract\FormElementDecoration;
use ipl\Html\Contract\FormElement;

/**
 * Render the form element itself
 */
class RenderElementDecorator implements FormElementDecoration
{
    public function decorate(DecorationResults $results, FormElement $formElement): void
    {
        $results->append($formElement);
    }
}
