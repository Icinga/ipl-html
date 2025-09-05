<?php

namespace ipl\Html\FormDecorator;

use ipl\Html\Contract\MutableHtml;
use ipl\Html\HtmlDocument;
use ipl\Html\ValidHtml;

/**
 * DecorationResults stores and render the results of decorators
 *
 * @phpstan-type content array<int, ValidHtml|array<int, mixed>>
 */
class DecorationResults implements ValidHtml
{
    /** @var content The HTML content */
    protected array $content = [];

    /** @var string[] List of decorator names to skip */
    protected array $skipDecorators = [];

    /**
     * Add decorator names to be skipped
     *
     * @param string[] $decoratorNames List of decorator names to skip
     *
     * @return $this
     */
    public function skipDecorators(array $decoratorNames): static
    {
        $this->skipDecorators += $decoratorNames;

        return $this;
    }

    /**
     * Get the list of decorator names to skip
     *
     * @return string[]
     */
    public function getSkipDecorators(): array
    {
        return $this->skipDecorators;
    }

    /**
     * Add the given HTML element to the results
     *
     * @param ValidHtml $item The HTML content to be added
     *
     * @return $this
     */
    public function append(ValidHtml $item): static
    {
        $this->content[] = $item;

        return $this;
    }

    /**
     * Add the given HTML element to the beginning of the results
     *
     * @param ValidHtml $item The HTML element to be added
     *
     * @return $this
     */
    public function prepend(ValidHtml $item): static
    {
        array_unshift($this->content, $item);

        return $this;
    }

    /**
     * Add the given HTML element as the wrapper of the results
     *
     * @param MutableHtml $item The HTML element to wrap around the content
     *
     * @return $this
     */
    public function wrap(MutableHtml $item): static
    {
        $this->content = [[$item, $this->content]];

        return $this;
    }

    /**
     * Render the given content
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * Render the results
     *
     * @return string The rendered HTML content
     */
    public function render(): string
    {
        if (empty($this->content)) {
            return '';
        }

        $content = new HtmlDocument();
        $this->resolveContent($content, $this->content);

        return $content->render();
    }

    /**
     * Resolve content
     *
     * @param MutableHtml $parent The parent element
     * @param content $content The content to be added
     *
     * @return void
     */
    protected function resolveContent(MutableHtml $parent, array $content): void
    {
        foreach ($content as $item) {
            if (is_array($item)) {
                $item = $this->resolveWrappedContent($item[0], $item[1]);
            }

            $parent->addHtml($item);
        }
    }

    /**
     * Resolve wrapped content
     *
     * @param MutableHtml $parent The parent element
     * @param ValidHtml|content $item The content to be added
     *
     * @return ValidHtml The resolved parent element with content added
     */
    protected function resolveWrappedContent(MutableHtml $parent, ValidHtml|array $item): ValidHtml
    {
        if ($item instanceof ValidHtml) {
            $parent->addHtml($item);
        } else {
            $this->resolveContent($parent, $item);
        }

        return $parent;
    }
}
