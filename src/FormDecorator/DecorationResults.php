<?php

namespace ipl\Html\FormDecorator;

use ipl\Html\BaseHtmlElement;
use ipl\Html\HtmlDocument;
use ipl\Html\ValidHtml;

/**
 * DecorationResults stores and render the results of decorators
 */
class DecorationResults implements ValidHtml
{
    protected array $content = [];

    /**
     * Add the given HTML element to the results
     *
     * @param BaseHtmlElement $item The HTML content to be added
     *
     * @return $this
     */
    public function append(BaseHtmlElement $item): static
    {
        $this->content[] = $item;

        return $this;
    }

    /**
     * Add the given HTML element to the beginning of the results
     *
     * @param BaseHtmlElement $item The HTML element to be added
     *
     * @return $this
     */
    public function prepend(BaseHtmlElement $item): static
    {
        array_unshift($this->content, $item);

        return $this;
    }

    /**
     * Add the given HTML element as the wrapper of the results
     *
     * @param BaseHtmlElement $item The HTML element to wrap around the content
     *
     * @return $this
     */
    public function wrap(BaseHtmlElement $item): static
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
        foreach ($this->content as $item) {
            if (is_array($item)) {
                $item = $this->resolveWrapped($item[0], $item[1]);
            }

            $content->addHtml($item);
        }

        return $content->render();
    }

    /**
     * Resolve wrapped content
     *
     * @param BaseHtmlElement $parent The parent element
     * @param BaseHtmlElement|array $item The content to be added
     *
     * @return BaseHtmlElement The resolved parent element with content added
     */
    private function resolveWrapped(BaseHtmlElement $parent, BaseHtmlElement|array $item): BaseHtmlElement
    {
        if (is_array($item) && isset($item[0]) && is_array($item[0])) {
            $item = $this->resolveWrapped($item[0][0], $item[0][1]);
        }

        if (is_array($item)) {
            $parent->addHtml(...$item);
        } else {
            $parent->addHtml($item);
        }

        return $parent;
    }
}
