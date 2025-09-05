<?php

namespace ipl\Html\FormDecorator;

use InvalidArgumentException;
use ipl\Html\Attributes;
use ipl\Html\Contract\Decorator;
use ipl\Html\Contract\DecoratorOptions;
use ipl\Html\Contract\DecoratorOptionsInterface;
use ipl\Html\Contract\FormElement;
use ipl\Html\HtmlElement;

use function ipl\Stdlib\get_php_type;

/**
 * Decorates the form element with an HTML tag
 */
class HtmlTagDecorator implements Decorator, DecoratorOptionsInterface
{
    use DecoratorOptions;

    /** @var Placement Describes where the HTML tag should be placed. Default : Wrap */
    protected Placement $placement = Placement::Wrap;

    /** @var string HTML tag to use for the decoration. */
    protected string $tag;

    /** @var ?callable(FormElement): bool Callable to decide whether to decorate element */
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
     * Set the placement of the HTML tag
     *
     * @param Placement $placement
     *
     * @return $this
     */
    public function setPlacement(Placement $placement): static
    {
        $this->placement = $placement;

        return $this;
    }

    /**
     * Get the placement of the HTML tag
     *
     * @return Placement
     */
    public function getPlacement(): Placement
    {
        return $this->placement;
    }

    /**
     * Get the condition callable to decide whether to decorate the element
     *
     * @return ?callable(FormElement): bool
     */
    public function getCondition(): ?callable
    {
        return $this->condition;
    }

    /**
     * Set the condition callable to decide whether to decorate the element
     *
     * @param callable(FormElement): bool $condition
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
        $html = new HtmlElement($this->getTag(), $class === null ? null : new Attributes(['class' => $class]));

        match ($this->getPlacement()) {
            Placement::Append   => $results->append($html),
            Placement::Prepend  => $results->prepend($html),
            default             => $results->wrap($html)
        };
    }

    protected function registerAttributeCallbacks(Attributes $attributes): void
    {
        $attributes
            ->registerAttributeCallback('tag', null, $this->setTag(...))
            ->registerAttributeCallback('placement', null, $this->setPlacement(...))
            ->registerAttributeCallback('condition', null, $this->setCondition(...))
            ->registerAttributeCallback('class', null, $this->setClass(...));
    }
}
