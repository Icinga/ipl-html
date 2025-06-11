<?php

namespace ipl\Html\FormDecorator;

use ipl\Html\FormElement\BaseFormElement;
use ipl\Html\HtmlElement;
use ipl\Html\Text;

/**
 * Decorate the errors of the form element
 */
class ErrorsDecorator extends BaseDecorator
{
    protected const CSS_CLASS = 'errors';

    public function decorate(DecorationResults $results, BaseFormElement $formElement): void
    {
        $errors = new HtmlElement('ul', $this->getAttributes());
        foreach ($formElement->getMessages() as $message) {
            $errors->addHtml(new HtmlElement('li', null, Text::create($message)));
        }

        if (! $errors->isEmpty()) {
            $results->append($errors);
        }
    }
}
