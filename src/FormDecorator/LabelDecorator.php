<?php

namespace ipl\Html\FormDecorator;

use ipl\Html\Attributes;
use ipl\Html\Contract\Decorator;
use ipl\Html\Contract\DecoratorOptions;
use ipl\Html\Contract\FormElement;
use ipl\Html\Contract\FormSubmitElement;
use ipl\Html\FormattedString;
use ipl\Html\FormElement\FieldsetElement;
use ipl\Html\HtmlElement;
use ipl\Html\HtmlString;
use ipl\Html\Text;

/**
 * Decorates the label of the form element
 */
class LabelDecorator implements Decorator
{
    use DecoratorOptions;

    /** @var string|string[] CSS classes to apply */
    protected string|array $class = 'form-element-label';

    public function decorate(DecorationResults $results, FormElement $formElement): void
    {
        if ($formElement instanceof FormSubmitElement || $formElement instanceof FieldsetElement) {
            return;
        }

        $label = $formElement->getLabel() ?? '';
        if ($formElement->isRequired()) {
            $formElement->setAttribute('aria-required', 'true');
            $label = FormattedString::create(
                '%s %s',
                $label,
                new HtmlElement('span', Attributes::create(['class' => 'required-cue']), Text::create('*'))
            );
        } else {
            $label = HtmlString::create($label);
        }

        $labelAttr = null;
        if ($formElement->getAttributes()->has('id')) {
            $labelAttr = Attributes::create(['for' => $formElement->getAttributes()->get('id')->getValue()]);
        }

        $results->prepend(
            new HtmlElement(
                'div',
                Attributes::create(['class' => $this->class]),
                new HtmlElement('label', $labelAttr, $label)
            )
        );
    }

    protected function registerAttributeCallbacks(Attributes $attributes): void
    {
        $attributes->registerAttributeCallback('class', null, fn(string|array $value) => $this->class = $value);
    }
}
