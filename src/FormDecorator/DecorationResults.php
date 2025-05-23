<?php

namespace ipl\Html\FormDecorator;

use ipl\Html\BaseHtmlElement;
use ipl\Html\HtmlDocument; //TODO: add a new interface, MutableHtml?, which restrict only to HtmlElement

/**
 * DecorationResults stores and render the results of decorators
 */
class DecorationResults
{
    protected array $content = [];

    public function append(HtmlDocument $item): self
    {
        $this->content[] = $item;

        return $this;
    }

    public function prepend(BaseHtmlElement $item): self
    {
        array_unshift($this->content, $item);

        return $this;
    }

    public function wrap(BaseHtmlElement $item): self
    {
        $this->content = [[$item, $this->content]];

        return $this;
    }

    protected function render(): string
    {
        if (empty($this->content)) {
            return '';
        }

        $content = new HtmlDocument();
        foreach ($this->content as $item) {
            if (is_array($item)) {
                $parent = $item[0];
                $parent->addHtml(...$item[1]);
            //TODO: two wrapper after each other does not work
                // link HtmlTag + HtmlTag again
                $item = $parent;
            }

            $content->addHtml((new HtmlDocument())->addHtml($item));
        }

        return $content->render();
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
