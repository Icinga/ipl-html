<?php

namespace ipl\Html;

use Exception;

/**
 * {@link sprintf()}-like formatted HTML string supporting lazy rendering of {@link ValidHtml} element arguments
 *
 * # Example Usage
 * ```
 * $info = new FormattedString(
 *     'Follow the %s for more information on %s',
 *     [
 *         new Link('doc/html', 'HTML documentation'),
 *         Html::tag('strong', 'HTML elements')
 *     ]
 * );
 * ```
 */
class FormattedString implements ValidHtml
{
    /** @var ValidHtml[] */
    protected $args = [];

    /** @var ValidHtml */
    protected $string;

    /**
     * FormattedString constructor.
     * @param $string
     * @param array $args
     */
    public function __construct($string, array $args = [])
    {
        $this->string = Html::wantHtml($string);

        foreach ($args as $key => $val) {
            $this->args[$key] = Html::wantHtml($val);
        }
    }

    /**
     * @param $string
     * @return static
     */
    public static function create($string)
    {
        $args = func_get_args();
        array_shift($args);

        return new static($string, $args);
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
        return vsprintf(
            $this->string->render(),
            $this->args
        );
    }
}
