<?php

namespace ipl\Html\FormDecoration;

use ipl\Html\Attributes;
use ipl\Html\Contract\FormElementDecoration;
use ipl\Html\Contract\FormElement;
use ipl\Html\Contract\HtmlElementInterface;
use ipl\Html\Contract\MutableHtml;
use ipl\Html\HtmlElement;
use ipl\Html\Text;

/**
 * Decorates the fieldset of the form element
 */
class FieldsetDecorator implements FormElementDecoration
{
    public function getName(): string
    {
        return 'Fieldset';
    }

    public function decorate(DecorationResults $results, FormElement $formElement): void
    {
        $isHtmlElement = $formElement instanceof HtmlElementInterface;
        if (! $formElement instanceof MutableHtml || ! $isHtmlElement || $formElement->getTag() !== 'fieldset') {
            return;
        }

        $description = $formElement->getDescription();
        if ($description !== null) {
            $attributes = null;
            if ($formElement->getAttributes()->has('id')) {
                $descriptionId = 'desc_' . $formElement->getAttributes()->get('id')->getValue();
                $formElement->getAttributes()->set('aria-describedby', $descriptionId);
                $attributes = new Attributes(['id' => $descriptionId]);
            }

            $formElement->prependHtml(new HtmlElement('p', $attributes, new Text($description)));
        }

        $label = $formElement->getLabel();
        if ($label !== null) {
            $formElement->prependHtml(new HtmlElement('legend', null, Text::create($label)));
        }
    }
}
