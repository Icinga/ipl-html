<?php

namespace ipl\Html\FormDecorator;

use ipl\Html\Attributes;
use ipl\Html\BaseHtmlElement;
use ipl\Html\FormElement\BaseFormElement;
use ipl\Html\FormElement\SubmitElement;
use ipl\Html\Html;
use ipl\Html\ValidHtml;

/**
 * Form element decorator based on div elements
 */
class DivDecorator extends BaseHtmlElement implements DecoratorInterface
{
    /** @var BaseFormElement The decorated form element */
    protected $formElement;

    /** @var bool Whether the form element has been added already */
    protected $formElementAdded = false;

    protected $tag = 'div';

    public function decorate(BaseFormElement $formElement)
    {
        $decorator = clone $this;

        $decorator->formElement = $formElement;

        // TODO(el): Replace with SubmitElementInterface once introduced
        if ($formElement instanceof SubmitElement) {
            $class = 'form-control';
        } else {
            $class = 'form-element';
        }

        $decorator->getAttributes()->add('class', $class);

        $formElement->prependWrapper($decorator);

        return $decorator;
    }

    protected function assembleDescription()
    {
        $description = $this->formElement->getDescription();

        if ($description !== null) {
            return Html::tag('p', ['class' => 'form-element-description'], $description);
        }

        return null;
    }

    protected function assembleErrors()
    {
        $errors = [];

        foreach ($this->formElement->getMessages() as $message) {
            $errors[] = Html::tag('p', ['class' => 'form-element-error'], $message);
        }

        if (! empty($errors)) {
            return $errors;
        }

        return null;
    }

    protected function assembleLabel()
    {
        $label = $this->formElement->getLabel();

        if ($label !== null) {
            $attributes = null;

            if ($this->formElement->getAttributes()->has('id')) {
                $attributes = new Attributes(['for' => $this->formElement->getAttributes()->get('id')]);
            }

            return Html::tag('label', $attributes, $label);
        }

        return null;
    }

    protected function addIndexedContent(ValidHtml $html)
    {
        if ($html === $this->formElement) {
            // Our wrapper implementation automatically adds the wrapped element but we already did this in assemble
            if ($this->formElementAdded) {
                return $this;
            }

            $this->formElementAdded = true;
        }

        parent::addIndexedContent($html);

        return $this;
    }

    protected function assemble()
    {
        if ($this->formElement->hasBeenValidatedAndIsNotValid()) {
            $this->getAttributes()->add('class', 'has-error');
        }

        $this->formElement->getAttributes()->add('class');

        $this->addHtml(...array_filter(array_merge([
            $this->assembleLabel(),
            $this->formElement,
            $this->assembleDescription()
        ], $this->assembleErrors() ?: [])));
    }
}
