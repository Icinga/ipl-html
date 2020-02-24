<?php

namespace ipl\Html;

use Exception;

/**
 * A text node
 *
 * Primitive element that renders text to HTML while automatically escaping its content.
 * If the passed content is already escaped, see {@link setEscaped()} to indicate this.
 */
class Text implements ValidHtml
{
    /** @var string */
    protected $string;

    /** @var bool Whether the content is already escaped */
    protected $escaped = false;

    /**
     * Create a new text node
     *
     * @param string $string
     */
    public function __construct($string)
    {
        $this->string = (string) $string;
    }

    /**
     * Create a new text node
     *
     * @param string $text
     *
     * @return static
     */
    public static function create($text)
    {
        return new static($text);
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->string;
    }

    /**
     * Get whether the content promises to be already escaped
     *
     * @return bool
     */
    public function isEscaped()
    {
        return $this->escaped;
    }

    /**
     * Set whether the content is already escaped
     *
     * @param bool $escaped
     *
     * @return $this
     */
    public function setEscaped($escaped = true)
    {
        $this->escaped = $escaped;

        return $this;
    }

    /**
     * Render text to HTML when treated like a string
     *
     * Calls {@link render()} internally in order to render the text to HTML.
     * Exceptions will be automatically caught and returned as HTML string as well using {@link Error::render()}.
     *
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

    public function render()
    {
        if ($this->escaped) {
            return $this->string;
        } else {
            return Html::escape($this->string);
        }
    }
}
