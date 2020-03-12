<?php

namespace ipl\Html;

use Countable;
use Exception;
use InvalidArgumentException;
use ipl\Html\Contract\Wrappable;

/**
 * HTML document
 *
 * An HTML document is composed of a tree of HTML nodes, i.e. text nodes and HTML elements.
 */
class HtmlDocument implements Countable, Wrappable
{
    protected $contentSeparator = '';

    protected $hasBeenAssembled = false;

    /** @var BaseHtmlElement */
    protected $wrapper;

    /** @var ValidHtml[] */
    private $content = [];

    /** @var array */
    private $contentIndex = [];

    /**
     * return ValidHtml[]
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param HtmlDocument|array|string $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = [];
        $this->add($content);

        return $this;
    }

    /**
     * Get the content separator
     *
     * @return string
     */
    public function getSeparator()
    {
        return $this->contentSeparator;
    }

    /**
     * @param $separator
     * @return self
     */
    public function setSeparator($separator)
    {
        $this->contentSeparator = $separator;

        return $this;
    }

    /**
     * @param $tag
     * @return BaseHtmlElement
     * @throws InvalidArgumentException
     */
    public function getFirst($tag)
    {
        foreach ($this->content as $c) {
            if ($c instanceof BaseHtmlElement && $c->getTag() === $tag) {
                return $c;
            }
        }

        throw new InvalidArgumentException(sprintf(
            'Trying to get first %s, but there is no such',
            $tag
        ));
    }

    /**
     * @param ValidHtml|mixed $content
     * @return $this
     */
    public function add($content)
    {
        if (is_iterable($content) && ! $content instanceof ValidHtml) {
            foreach ($content as $c) {
                $this->add($c);
            }
        } elseif ($content !== null) {
            $this->addIndexedContent(Html::wantHtml($content));
        }

        return $this;
    }

    /**
     * @param HtmlDocument $from
     * @param callable     $callback
     *
     * @return $this
     */
    public function addFrom(HtmlDocument $from, $callback = null)
    {
        $from->ensureAssembled();

        $isCallable = is_callable($callback);
        foreach ($from->getContent() as $item) {
            $this->add($isCallable ? $callback($item) : $item);
        }

        return $this;
    }

    /**
     * @param $content
     * @return $this
     */
    public function prepend($content)
    {
        if (is_iterable($content) && ! $content instanceof ValidHtml) {
            foreach (array_reverse(is_array($content) ? $content : iterator_to_array($content)) as $c) {
                $this->prepend($c);
            }
        } elseif ($content !== null) {
            $pos = 0;
            $html = Html::wantHtml($content);
            array_unshift($this->content, $html);
            $this->incrementIndexKeys();
            $this->addObjectPosition($html, $pos);
        }

        return $this;
    }

    public function remove(ValidHtml $html)
    {
        $key = spl_object_hash($html);
        if (array_key_exists($key, $this->contentIndex)) {
            foreach ($this->contentIndex[$key] as $pos) {
                unset($this->content[$pos]);
            }
        }
        $this->content = array_values($this->content);

        $this->reIndexContent();
    }

    /**
     * @return $this
     */
    public function ensureAssembled()
    {
        if (! $this->hasBeenAssembled) {
            $this->hasBeenAssembled = true;
            $this->assemble();
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->content);
    }

    /**
     * @return string
     */
    public function renderUnwrapped()
    {
        $this->ensureAssembled();
        $html = [];

        foreach ($this->content as $element) {
            $html[] = $element->render();
        }

        return implode($this->contentSeparator, $html);
    }

    protected function assemble()
    {
    }

    /**
     * @param Wrappable $wrapper
     * @return $this
     */
    public function setWrapper(Wrappable $wrapper)
    {
        $this->wrapper = $wrapper;

        return $this;
    }

    /**
     * @param Wrappable $wrapper
     * @return $this
     */
    public function addWrapper(Wrappable $wrapper)
    {
        if ($this->wrapper === null) {
            $this->setWrapper($wrapper);
        } else {
            $this->wrapper->addWrapper($wrapper);
        }

        return $this;
    }

    /**
     * @param Wrappable $wrapper
     * @return $this
     */
    public function prependWrapper(Wrappable $wrapper)
    {
        if ($this->wrapper === null) {
            $this->setWrapper($wrapper);
        } else {
            $wrapper->addWrapper($this->wrapper);
            $this->setWrapper($wrapper);
        }

        return $this;
    }

    /**
     * @return Wrappable|null
     */
    public function getWrapper()
    {
        return $this->wrapper;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->content);
    }

    /**
     * @return string
     */
    public function render()
    {
        $this->ensureAssembled();
        if ($this->wrapper === null) {
            return $this->renderUnwrapped();
        } else {
            return $this->renderWrapped();
        }
    }

    /**
     * @return string
     */
    protected function renderWrapped()
    {
        // TODO: we don't like this, but have no better solution right now.
        //       However, it works as expected, tests are green
        $wrapper = $this->wrapper;
        $this->wrapper = null;
        $result = $wrapper->renderWrappedDocument($this);
        $this->wrapper = $wrapper;

        return $result;
    }

    /**
     * @param HtmlDocument $document
     * @return string
     */
    protected function renderWrappedDocument(HtmlDocument $document)
    {
        $wrapper = clone($this);

        $wrapper->ensureAssembled();

        $key = spl_object_hash($document);

        if (! array_key_exists($key, $wrapper->contentIndex)) {
            $wrapper->add($document);
        }

        return $wrapper->render();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->render();
        } catch (Exception $e) {
            return Error::render($e);
        }
    }

    private function reIndexContent()
    {
        $this->contentIndex = [];
        foreach ($this->content as $pos => $html) {
            $this->addObjectPosition($html, $pos);
        }
    }

    private function addObjectPosition(ValidHtml $html, $pos)
    {
        $key = spl_object_hash($html);
        if (array_key_exists($key, $this->contentIndex)) {
            $this->contentIndex[$key][] = $pos;
        } else {
            $this->contentIndex[$key] = [$pos];
        }
    }

    private function addIndexedContent(ValidHtml $html)
    {
        $pos = count($this->content);
        $this->content[$pos] = $html;
        $this->addObjectPosition($html, $pos);
    }

    private function incrementIndexKeys()
    {
        foreach ($this->contentIndex as & $index) {
            foreach ($index as & $pos) {
                $pos++;
            }
        }
    }

    public function __clone()
    {
        foreach ($this->content as $key => $element) {
            $this->content[$key] = clone($element);
        }

        $this->reIndexContent();
    }
}
