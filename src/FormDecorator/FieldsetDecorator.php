<?php

namespace ipl\Html\FormDecorator;

use ipl\Html\FormElement\BaseFormElement;
use ipl\Html\FormElement\FieldsetElement;
use ipl\Html\HtmlElement;
use ipl\Html\Text;

/**
 * Decorate the fieldset of the form element
 */
class FieldsetDecorator extends BaseDecorator
{
    protected const CSS_CLASS = null;

    public function decorate(DecorationResults $results, BaseFormElement $formElement): void
    {
        if (! $formElement instanceof FieldsetElement) {
            return;
        }

        $attributes = $this->getAttributes();

        $label = $formElement->getLabel();
        if ($label !== null) {
            $formElement->prependHtml(new HtmlElement('legend', $attributes, Text::create($label)));
        }

        $description = $formElement->getDescription();
        if ($description === null) {
            return;
        }

        if ($formElement->getAttributes()->has('id')) {
            $descriptionId = 'desc_' . $formElement->getAttributes()->get('id')->getValue();
            $formElement->getAttributes()->set('aria-describedby', $descriptionId);
            $attributes->set('id', $descriptionId);
        }

        $formElement->prependHtml(new HtmlElement('p', $attributes, new Text($description)));
    }
}
