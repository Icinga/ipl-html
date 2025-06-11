<?php

namespace ipl\Html\FormDecorator;

use ipl\Html\Attributes;
use ipl\Html\Contract\FormSubmitElement;
use ipl\Html\FormElement\BaseFormElement;
use ipl\Html\FormElement\FieldsetElement;
use ipl\Html\HtmlElement;
use ipl\Html\HtmlString;
use ipl\Html\Text;

class LabelDecorator extends BaseDecorator
{
    protected const CSS_CLASS = 'form-element-label';

    public function decorate(DecorationResults $results, BaseFormElement $formElement): void
    {
        if ($formElement instanceof FormSubmitElement || $formElement instanceof FieldsetElement) {
            return;
        }

        $label = HtmlString::create($formElement->getLabel() ?: '&nbsp;');

        if ($formElement->isRequired()) {
            $formElement->setAttribute('aria-required', 'true');

            $label->setContent(
                $label->getContent()
                . ' ' .
                new HtmlElement('span', Attributes::create(['class' => 'required-cue']), Text::create('*'))
            );

            if ($results->getParent()) {
                $results->getParent()->addToFooterOnce(
                    new HtmlElement('div',null, new Text('* fields are required')),
                    'required-cue-hint',
                );
            }
        }

        $labelAttr = null;
        if ($formElement->getAttributes()->has('id')) {
            $labelAttr = Attributes::create(['for' => $formElement->getAttributes()->get('id')->getValue()]);
        }

        $results->prepend(
            new HtmlElement(
                'div',
                $this->getAttributes(),
                new HtmlElement('label', $labelAttr, $label)
            )
        );
    }
}
