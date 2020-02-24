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
     * @return string
     */
    public function getText()
    {
        return $this->string;
    }

    /**
     * @param bool $escaped
     * @return $this
     */
    public function setEscaped($escaped = true)
    {
        $this->escaped = $escaped;
        return $this;
    }

    /**
     * @param $text
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
    public function render()
    {
        if ($this->escaped) {
            return $this->string;
        } else {
            return Html::escape($this->string);
        }
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
}
