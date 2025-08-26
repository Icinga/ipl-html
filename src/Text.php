<?php

namespace ipl\Html;

use Throwable;

/**
 * A text node
 *
 * Primitive element that renders text to HTML while automatically escaping its content.
 * If the passed content is already escaped, see {@link setEscaped()} to indicate this.
 */
class Text implements ValidHtml
{
    /** @var string */
    protected string $content;

    /** @var bool Whether the content is already escaped */
    protected bool $escaped = false;

    /**
     * Create a new text node
     *
     * @param string $content
     */
    public function __construct(string $content)
    {
        $this->setContent($content);
    }

    /**
     * Create a new text node
     *
     * @param string $content
     *
     * @return static
     */
    public static function create(string $content): static
    {
        return new static($content);
    }

    /**
     * Get the content
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Set the content
     *
     * @param string $content
     *
     * @return $this
     */
    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get whether the content promises to be already escaped
     *
     * @return bool
     */
    public function isEscaped(): bool
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
    public function setEscaped(bool $escaped = true): static
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
    public function __toString(): string
    {
        try {
            return $this->render();
        } catch (Throwable $e) {
            return Error::render($e);
        }
    }

    public function render(): string
    {
        if ($this->escaped) {
            return $this->content;
        } else {
            return Html::escape($this->content);
        }
    }
}
