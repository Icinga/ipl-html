<?php

namespace ipl\Html\FormDecoration;

use ipl\Html\Attributes;
use ipl\Html\Contract\DecorationResult;
use ipl\Html\Contract\DecoratorOptions;
use ipl\Html\Contract\DecoratorOptionsInterface;
use ipl\Html\Contract\FormElement;
use ipl\Html\Contract\FormElementDecoration;
use ipl\Html\Contract\HtmlElementInterface;
use ipl\Html\HtmlElement;
use ipl\Html\Text;

/**
 * Decorates the description of the form element
 */
class DescriptionDecorator implements FormElementDecoration, DecoratorOptionsInterface
{
    use DecoratorOptions;

    /** @var string|string[] CSS classes to apply */
    protected string|array $class = 'form-element-description';

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
        $description = $formElement->getDescription();
        $isHtmlElement = $formElement instanceof HtmlElementInterface;

        if ($description === null || ($isHtmlElement && $formElement->getTag() === 'fieldset')) {
            return;
        }

        $descriptionId = null;
        if ($isHtmlElement) {
            if ($formElement->getAttributes()->has('id')) {
                $elementId = $formElement->getAttributes()->get('id')->getValue();
            } else {
                $elementId = uniqid('form-element-');
                $formElement->getAttributes()->set('id', $elementId);
            }

            $descriptionId = 'desc_' . $elementId;
            $formElement->getAttributes()->set('aria-describedby', $descriptionId);
        }

        $result->append(
            new HtmlElement(
                'p',
                new Attributes(['class' => $this->getClass(), 'id' => $descriptionId]),
                new Text($description)
            )
        );
    }

    protected function registerAttributeCallbacks(Attributes $attributes): void
    {
        $attributes->registerAttributeCallback('class', null, $this->setClass(...));
    }
}
