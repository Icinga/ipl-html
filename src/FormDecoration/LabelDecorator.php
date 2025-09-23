<?php

namespace ipl\Html\FormDecoration;

use ipl\Html\Attributes;
use ipl\Html\Contract\FormElementDecoration;
use ipl\Html\Contract\DecoratorOptions;
use ipl\Html\Contract\DecoratorOptionsInterface;
use ipl\Html\Contract\FormElement;
use ipl\Html\Contract\FormSubmitElement;
use ipl\Html\Contract\HtmlElementInterface;
use ipl\Html\HtmlElement;
use ipl\Html\Text;

/**
 * Decorates the label of the form element
 */
class LabelDecorator implements FormElementDecoration, DecoratorOptionsInterface
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
        $isHtmlElement = $formElement instanceof HtmlElementInterface;

        if (
            $formElement instanceof FormSubmitElement
            || $formElement->getLabel() === null
            || $isHtmlElement && $formElement->getTag() === 'fieldset'
        ) {
            return;
        }

        $labelAttr = new Attributes(['class' => $this->getClass()]);
        if ($isHtmlElement && $formElement->getAttributes()->has('id')) {
            $labelAttr->add(['for' => $formElement->getAttributes()->get('id')->getValue()]);
        }

        $results->append(new HtmlElement('label', $labelAttr, new Text($formElement->getLabel())));
    }

    protected function registerAttributeCallbacks(Attributes $attributes): void
    {
        $attributes->registerAttributeCallback('class', null, $this->setClass(...));
    }
}
