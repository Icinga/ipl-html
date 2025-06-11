<?php

namespace ipl\Html\FormDecorator;

use ipl\Html\HtmlDocument;
use ipl\Html\HtmlElement;
use ipl\Html\ValidHtml;

//TODO: add a new interface, MutableHtml?, which restrict only to HtmlElement

class FormDecorationResults
{
    protected array $header = [];

    protected array $footer = [];

    public function addToHeader(ValidHtml $item): self
    {
        $this->header[] = $item;

        return $this;
    }

    public function addToFooter(ValidHtml $item): self
    {
        $this->footer[] = $item;

        return $this;
    }

    public function addToHeaderOnce(ValidHtml $item, string $key, bool $merge = false): self
    {
        if (! isset($this->header[$key])) {
            $this->header[$key] = $item;
        } elseif ($merge) {
            $this->header[$key] = (new HtmlDocument())->addHtml($this->header[$key], $item);
        }

        return $this;
    }

    public function addToFooterOnce(ValidHtml $item, string $key, bool $merge = false): self
    {
        if (! isset($this->footer[$key])) {
            $this->footer[$key] = $item;
        } elseif ($merge) {
            $this->footer[$key] = (new HtmlDocument())->addHtml($this->footer[$key], $item);
        }

        return $this;
    }

    public function getHeader(): array
    {
        return $this->header;
    }

    public function getFooter(): array
    {
        return $this->footer;
    }

    public function getElementDecorationResults(): DecorationResults
    {
        return (new DecorationResults())->setParent($this);
    }
}
