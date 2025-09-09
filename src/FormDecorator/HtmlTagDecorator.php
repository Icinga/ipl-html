<?php

namespace ipl\Html\FormDecorator;

use InvalidArgumentException;
use ipl\Html\Attributes;
use ipl\Html\Contract\Decorator;
use ipl\Html\Contract\DecoratorOptions;
use ipl\Html\Contract\DecoratorOptionsInterface;
use ipl\Html\Contract\FormElement;
use ipl\Html\Contract\HtmlElementInterface;
use ipl\Html\HtmlElement;

use function ipl\Stdlib\get_php_type;

/**
 * Decorates the form element with an HTML tag
 */
class HtmlTagDecorator implements Decorator, DecoratorOptionsInterface
{
    use DecoratorOptions;

    /** @var Transformation Describes how the HTML tag should transform the content. Default: Wrap */
    protected Transformation $transformation = Transformation::Wrap;

    /** @var string HTML tag to use for the decoration. */
    protected string $tag;

    /** @var ?callable(FormElement & HtmlElementInterface): bool Callable to decide whether to decorate the element */
    protected $condition;

    /** @var ?(string|string[]) CSS classes to apply */
    protected null|string|array $class = null;

    /**
     * Get the HTML tag to use for the decoration
     *
     * @return string
     *
     * @throws InvalidArgumentException if the tag is not set
     */
    public function getTag(): string
    {
        if (empty($this->tag)) {
            throw new InvalidArgumentException('Option "tag" must be set');
        }

        return $this->tag;
    }

    /**
     * Set the HTML tag to use for the decoration
     *
     * @param string $tag
     *
     * @return $this
     */
    public function setTag(string $tag): static
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Set the transformation type of the HTML tag
     *
     * @param Transformation $transformation
     *
     * @return $this
     */
    public function setTransformation(Transformation $transformation): static
    {
        $this->transformation = $transformation;

        return $this;
    }

    /**
     * Get the transformation type of the HTML tag
     *
     * @return Transformation
     */
    public function getTransformation(): Transformation
    {
        return $this->transformation;
    }

    /**
     * Get the condition callable to decide whether to decorate the element
     *
     * @return ?callable(FormElement & HtmlElementInterface): bool
     */
    public function getCondition(): ?callable
    {
        return $this->condition;
    }

    /**
     * Set the condition callable to decide whether to decorate the element
     *
     * @param callable(FormElement & HtmlElementInterface): bool $condition
     *
     * @return $this
     */
    public function setCondition(callable $condition): static
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * Get the css class(es)
     *
     * @return ?(string|string[])
     */
    public function getClass(): string|array|null
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
        return 'HtmlTag';
    }

    public function decorate(DecorationResults $results, FormElement $formElement): void
    {
        $condition = $this->getCondition();
        if ($condition !== null) {
            $shouldDecorate = $condition($formElement);

            if (! is_bool($shouldDecorate)) {
                throw new InvalidArgumentException(sprintf(
                    'Condition callback must return a boolean, got %s',
                    get_php_type($shouldDecorate)
                ));
            }

            if (! $shouldDecorate) {
                return;
            }
        }

        $class = $this->getClass();
        $results->transform(
            $this->getTransformation(),
            new HtmlElement($this->getTag(), $class === null ? null : new Attributes(['class' => $class]))
        );
    }

    protected function registerAttributeCallbacks(Attributes $attributes): void
    {
        $attributes
            ->registerAttributeCallback('tag', null, $this->setTag(...))
            ->registerAttributeCallback('transformation', null, $this->setTransformation(...))
            ->registerAttributeCallback('condition', null, $this->setCondition(...))
            ->registerAttributeCallback('class', null, $this->setClass(...));
    }
}
