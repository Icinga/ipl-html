<?php

namespace ipl\Html\FormDecorator;

use ipl\Html\Attributes;
use ipl\Html\Contract\Decorator;
use ipl\Html\Contract\DecoratorOptions;
use ipl\Html\Contract\FormElement;
use ipl\Html\FormElement\FieldsetElement;
use ipl\Html\HtmlElement;
use ipl\Html\Text;

/**
 * Decorates the description of the form element
 */
class DescriptionDecorator implements Decorator
{
    use DecoratorOptions;

    /** @var string|string[] CSS classes to apply */
    protected string|array $class = 'form-element-description';

    public function decorate(DecorationResults $results, FormElement $formElement): void
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
                new Attributes(['class' => $this->class, 'id' => $descriptionId]),
                new Text($description)
            )
        );
    }

    protected function registerAttributeCallbacks(Attributes $attributes): void
    {
        $attributes->registerAttributeCallback('class', null, fn(string|array $value) => $this->class = $value);
    }
}
