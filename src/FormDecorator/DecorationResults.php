<?php

namespace ipl\Html\FormDecorator;

use ipl\Html\Contract\FormElement;
use ipl\Html\Contract\MutableHtml;
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
     * @param FormElement|MutableHtml $item The HTML content to be added
     *
     * @return $this
     */
    public function append(FormElement|MutableHtml $item): static
    {
        $this->content[] = $item;

        return $this;
    }

    /**
     * Add the given HTML element to the beginning of the results
     *
     * @param FormElement|MutableHtml $item The HTML element to be added
     *
     * @return $this
     */
    public function prepend(FormElement|MutableHtml $item): static
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
     * @param MutableHtml $parent The parent element
     * @param MutableHtml|array $item The content to be added
     *
     * @return MutableHtml The resolved parent element with content added
     */
    private function resolveWrapped(MutableHtml $parent, MutableHtml|array $item): MutableHtml
    {
        if ($item instanceof MutableHtml) {
            $parent->addHtml($item);
        } elseif (! empty($item)) {
            if (is_array($item[0])) {
                $parent->addHtml($this->resolveWrapped($item[0][0], $item[0][1]));
            } else {
                $parent->addHtml(...$item);
            }
        }

        return $parent;
    }
}
