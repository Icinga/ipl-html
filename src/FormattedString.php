<?php

namespace ipl\Html;

use Exception;
use InvalidArgumentException;

use function ipl\Stdlib\get_php_type;

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
    protected $format;

    protected $pattern = '/\{\{(#[^\{]+)\}\}((?:[^{}]|(?R))*)\{\{\/(.*?)\}\}/';

    protected $numMustaches;

    /**
     * Create a new {@link sprintf()}-like formatted HTML string
     *
     * @param string   $format
     * @param iterable $args
     *
     * @throws InvalidArgumentException If arguments given but not iterable
     */
    public function __construct($format, $args = null)
    {
        $this->format = Html::wantHtml($format);

        if ($args !== null) {
            if (! is_iterable($args)) {
                throw new InvalidArgumentException(sprintf(
                    '%s expects parameter two to be iterable, got %s instead',
                    __METHOD__,
                    get_php_type($args)
                ));
            }

            foreach ($args as $key => $val) {
                if (! is_scalar($val) || is_string($val)) {
                    $val = Html::wantHtml($val);
                }
                $this->args[$key] = $val;
            }
        }
    }


    /**
     * Create a new {@link sprintf()}-like formatted HTML string
     *
     * @param string $format
     * @param mixed  ...$args
     *
     * @return static
     */
    public static function create($format, ...$args)
    {
        return new static($format, $args);
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
            $this->format->render(),
            $this->args
        );
    }

    public function getMustaches($args = [])
    {
        $format = $this->render();
        if (preg_match_all($this->pattern, $format, $matches, PREG_SET_ORDER)) {
            $this->numMustaches = count($matches);

            $mustaches = [];

            for ($i = 0; $i < $this->numMustaches; $i++) {
                $format = str_replace($matches[$i][0], '%s', $format);
                $mustaches[$matches[$i][3]] = $matches[$i][2];
            }

            $this->format = Html::wantHtml($format);

            if (empty($args)) {
                throw new Exception(sprintf(
                    'Empty parameter passed to %s',
                    __METHOD__
                ));
            }

            $this->args = [];
            $ms = $this->mustacheReplace($args, $mustaches);

            foreach ($ms as $key => $val) {
                $val = Html::wantHtml($val);
                $this->args[$key] = $val;
            }
        }
    }

    public function mustacheReplace($tags, $mustaches)
    {
        foreach ($tags as $tag => $content) {
            if (array_key_exists($tag, $mustaches)) {
                if (preg_match_all($this->pattern, $mustaches[$tag], $nestedmatches, PREG_SET_ORDER)) {
                    $nMustaches = [];
                    $subtags = [];
                    for ($i = 0; $i < count($nestedmatches); $i++) {
                        $nMustaches[$nestedmatches[$i][3]] = $nestedmatches[$i][2];
                        $subtags[$nestedmatches[$i][3]] = $tags[$nestedmatches[$i][3]];
                        unset($tags[$nestedmatches[$i][3]]);
                    }
                    $subtags = $this->mustacheReplace($subtags, $nMustaches);
                    $content->setContent(Html::wantHtml($subtags));
                } else {
                    $content->setContent($mustaches[$tag]);
                }
            }
        }
        return $tags;
    }
}
