<?php

namespace ipl\Tests\Html\FormDecorator;

use ipl\Html\Attributes;
use ipl\Html\Contract\Decorator;
use ipl\Html\Contract\DecoratorOptions;
use ipl\Html\Contract\DecoratorOptionsInterface;
use ipl\Html\Contract\FormElement;
use ipl\Html\FormDecorator\DecorationResults;
use ipl\Html\HtmlElement;

/**
 * Wraps the $formElement with a div with class "test-with-options-decorator"
 */
class TestWithOptionsDecorator implements Decorator, DecoratorOptionsInterface
{
    use DecoratorOptions;

    public function decorate(DecorationResults $results, FormElement $formElement): void
    {
        $results->wrap(new HtmlElement('div', new Attributes(['class' => 'test-with-options-decorator'])));
    }

    protected function registerAttributeCallbacks(Attributes $attributes): void
    {
    }
}
