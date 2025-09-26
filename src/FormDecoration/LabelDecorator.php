<?php

namespace ipl\Html\FormDecoration;

use ipl\Html\Attributes;
use ipl\Html\Contract\DecorationResult;
use ipl\Html\Contract\DecoratorOptions;
use ipl\Html\Contract\DecoratorOptionsInterface;
use ipl\Html\Contract\FormElement;
use ipl\Html\Contract\FormElementDecoration;
use ipl\Html\Contract\FormSubmitElement;
use ipl\Html\Contract\HtmlElementInterface;
use ipl\Html\HtmlElement;
use ipl\Html\Text;
use ipl\Html\ValidHtml;

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

    public function decorateFormElement(DecorationResult $result, FormElement $formElement): void
    {
        $isHtmlElement = $formElement instanceof HtmlElementInterface;

        $elementLabel = $this->getElementLabel($formElement);
        if (
            $formElement instanceof FormSubmitElement
            || $elementLabel === null
            || ($isHtmlElement && $formElement->getTag() === 'fieldset')
        ) {
            return;
        }

        if ($elementLabel instanceof HtmlElementInterface) {
            $attributes['class'] = $this->getClass();
            if ($isHtmlElement) {
                $elementAttributes = $formElement->getAttributes();
                if (! $elementAttributes->has('id')) {
                    $elementAttributes->set('id', uniqid('form-element-'));
                }

                $attributes['for'] = $elementAttributes->get('id')->getValue();
            }

            $elementLabel->addAttributes($attributes);
        }

        $result->append($elementLabel);
    }

    /**
     * Get the label element for the given form element
     *
     * @param FormElement $formElement
     *
     * @return ?ValidHtml The label element or null if no label is set
     */
    protected function getElementLabel(FormElement $formElement): ?ValidHtml
    {
        $label = $formElement->getLabel();
        if ($label === null) {
            return null;
        }

        return new HtmlElement('label', content: new Text($label));
    }

    protected function registerAttributeCallbacks(Attributes $attributes): void
    {
        $attributes->registerAttributeCallback('class', null, $this->setClass(...));
    }
}
