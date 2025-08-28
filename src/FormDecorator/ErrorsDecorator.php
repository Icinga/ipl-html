<?php

namespace ipl\Html\FormDecorator;

use ipl\Html\Attributes;
use ipl\Html\Contract\Decorator;
use ipl\Html\Contract\DecoratorOptions;
use ipl\Html\Contract\FormElement;
use ipl\Html\HtmlElement;
use ipl\Html\Text;

/**
 * Decorates the errors messages of the form element
 */
class ErrorsDecorator implements Decorator
{
    use DecoratorOptions;

    /** @var string|string[] CSS classes to apply */
    protected string|array $class = 'form-element-errors';

    public function decorate(DecorationResults $results, FormElement $formElement): void
    {
        $errors = new HtmlElement('ul', new Attributes(['class' => $this->class]));
        foreach ($formElement->getMessages() as $message) {
            $errors->addHtml(new HtmlElement('li', null, Text::create($message)));
        }

        if (! $errors->isEmpty()) {
            $results->append($errors);
        }
    }

    protected function registerAttributeCallbacks(Attributes $attributes): void
    {
        $attributes->registerAttributeCallback('class', null, fn(string|array $value) => $this->class = $value);
    }
}
