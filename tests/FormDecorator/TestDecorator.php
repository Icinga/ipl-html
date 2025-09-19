<?php

namespace ipl\Tests\Html\FormDecorator;

use ipl\Html\Attributes;
use ipl\Html\Contract\Decorator;
use ipl\Html\Contract\FormElement;
use ipl\Html\FormDecoration\DecorationResults;
use ipl\Html\HtmlElement;

/**
 * Wraps the $formElement with a div with class "test-decorator"
 */
class TestDecorator implements Decorator
{
    public function getName(): string
    {
        return 'Test';
    }

    public function decorate(DecorationResults $results, FormElement $formElement): void
    {
        $results->wrap(new HtmlElement('div', new Attributes(['class' => 'test-decorator'])));
    }
}
