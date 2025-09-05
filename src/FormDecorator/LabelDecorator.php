<?php

namespace ipl\Html\FormDecorator;

use ipl\Html\Attributes;
use ipl\Html\Contract\Decorator;
use ipl\Html\Contract\DecoratorOptions;
use ipl\Html\Contract\DecoratorOptionsInterface;
use ipl\Html\Contract\FormElement;
use ipl\Html\Contract\FormSubmitElement;
use ipl\Html\FormElement\FieldsetElement;
use ipl\Html\HtmlElement;
use ipl\Html\Text;

/**
 * Decorates the label of the form element
 */
class LabelDecorator implements Decorator, DecoratorOptionsInterface
{
    use DecoratorOptions;

    /** @var string|string[] CSS classes to apply */
    protected string|array $class = 'form-element-label';

    /**
     * Get the css class(es)
     *
     * @return string|string[]
     */
    public function getClass(): string|array
    {
        return $this->class;
    }

    /**
     * Set the css class(es)
     *
     * @param string|string[] $class
     *
     * @return $this
     */
    public function setClass(string|array $class): static
    {
        $this->class = $class;

        return $this;
    }

    public function decorate(DecorationResults $results, FormElement $formElement): void
    {
        if (
            $formElement instanceof FormSubmitElement
            || $formElement instanceof FieldsetElement
            || $formElement->getLabel() === null
        ) {
            return;
        }

        $labelAttr = null;
        if ($formElement->getAttributes()->has('id')) {
            $labelAttr = Attributes::create(['for' => $formElement->getAttributes()->get('id')->getValue()]);
        }

        $results->prepend(
            new HtmlElement(
                'div',
                Attributes::create(['class' => $this->getClass()]),
                new HtmlElement('label', $labelAttr, new Text($formElement->getLabel()))
            )
        );
    }

    protected function registerAttributeCallbacks(Attributes $attributes): void
    {
        $attributes->registerAttributeCallback('class', null, $this->setClass(...));
    }
}
