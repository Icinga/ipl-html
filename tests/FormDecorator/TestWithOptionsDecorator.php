<?php

namespace ipl\Tests\Html\FormDecorator;

use ipl\Html\Attributes;
use ipl\Html\Contract\Decorator;
use ipl\Html\Contract\DecoratorOptions;
use ipl\Html\Contract\DecoratorOptionsInterface;
use ipl\Html\Contract\FormElement;
use ipl\Html\Contract\HtmlElementInterface;
use ipl\Html\FormDecorator\DecorationResults;
use ipl\Html\HtmlElement;

/**
 * Wraps the $formElement with a div with class "test-with-options-decorator"
 *
 * Apply options using setter
 */
class TestWithOptionsDecorator implements Decorator, DecoratorOptionsInterface
{
    use DecoratorOptions;

    protected array $options = [];

    public function getName(): string
    {
        return 'TestWithOptions';
    }

    public function decorate(DecorationResults $results, FormElement & HtmlElementInterface $formElement): void
    {
        $results->wrap(new HtmlElement('div', new Attributes(['class' => 'test-with-options-decorator'])));
    }

    /**
     * @return string[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param string[] $options
     *
     * @return $this
     */
    public function setOptions(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    protected function registerAttributeCallbacks(Attributes $attributes): void
    {
        $attributes
            ->registerAttributeCallback('options', null, $this->setOptions(...));
    }
}
