<?php

namespace ipl\Html\FormDecorator;

use ipl\Html\Attributes;
use ipl\Html\Contract\Decorator;
use ipl\Html\Contract\DecoratorOptions;
use ipl\Html\Contract\DecoratorOptionsInterface;
use ipl\Html\Contract\FormElement;
use ipl\Html\HtmlElement;
use ipl\Html\Text;

/**
 * Decorates the description of the form element
 */
class DescriptionDecorator implements Decorator, DecoratorOptionsInterface
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

    public function getName(): string
    {
        return 'Description';
    }

    public function decorate(DecorationResults $results, FormElement $formElement): void
    {
        $description = $formElement->getDescription();

        if ($description === null || $formElement->getTag() === 'fieldset') {
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
