<?php

namespace ipl\Html;

use Exception;

/**
 * Text node where content creation is deferred until rendering
 *
 * The content is created by a passed in callback which is only called when the node is going to be rendered and
 * automatically escaped to HTML.
 * If the created content is already escaped, see {@link setEscaped()} to indicate this.
 *
 * # Example Usage
 * ```
 * $benchmark = new Benchmark();
 *
 * $performance = new DeferredText(function () use ($benchmark) {
 *     return $benchmark->summary();
 * });
 *
 * execute_query();
 *
 * $benchmark->tick('Fetched results');
 *
 * generate_report();
 *
 * $benchmark->tick('Report generated');
 *
 * echo $performance;
 * ```
 */
class DeferredText implements ValidHtml
{
    /** @var callable will return the text that should be rendered */
    protected $callback;

    /** @var bool */
    protected $escaped = false;

    /**
     * Create a new text node where content creation is deferred until rendering
     *
     * @param callable $callback Must return the content that should be rendered
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Create a new text node where content creation is deferred until rendering
     *
     * @param callable $callback Must return the content that should be rendered
     *
     * @return static
     */
    public static function create(callable $callback)
    {
        return new static($callback);
    }

    public function render()
    {
        $callback = $this->callback;

        if ($this->escaped) {
            return $callback();
        } else {
            return Html::escape($callback());
        }
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
     * Calls the render function, but is failsafe. In case an Exception occurs,
     * an error is rendered instead of the expected HTML
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
}
