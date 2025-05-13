<?php

namespace ipl\Html\FormDecorator;

use ipl\Html\FormElement\BaseFormElement;
use ipl\Html\FormElement\FieldsetElement;
use ipl\Html\HtmlElement;
use ipl\Html\Text;

/**
 * Decorate the description of the form element
 */
class DescriptionDecorator extends BaseDecorator
{
    protected const CSS_CLASS = 'form-element-description';

    public function decorate(DecorationResults $results, BaseFormElement $formElement): void
    {
        $description = $formElement->getDescription();

        if ($description === null || $formElement instanceof FieldsetElement) {
            return;
        }

        $descriptionId = null;
        if ($formElement->getAttributes()->has('id')) {
            $descriptionId = 'desc_' . $formElement->getAttributes()->get('id')->getValue();
            $formElement->getAttributes()->set('aria-describedby', $descriptionId);
        }

        $results->append(
            new HtmlElement(
                'p',
                $this->getAttributes()->set('id', $descriptionId),
                new Text($description)
            )
        );
    }
}
