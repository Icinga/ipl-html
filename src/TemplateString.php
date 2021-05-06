<?php

namespace ipl\Html;

use Exception;

/**
 * Render {{#mustache}}Mustache{{/mustache}}-like string from {@link ValidHtml} element arguments
 *
 * # Example Usage
 * ```
 * $info = new TemplateString(
 *     'Follow the %s for more information on %s {{#mustache}}Mustache{{/mustache}}',
 *     [
 *         new Link('doc/html', 'HTML documentation'),
 *         Html::tag('strong', 'HTML elements'),
 *        [
 *        'mustache' => Html::tag('span')
 *        ]
 *     ]
 * );
 * ```
 */
class TemplateString extends FormattedString
{
    protected $pattern = '/\{\{(#[^\{]+)\}\}((?:[^{}]|(?R))*)\{\{\/(.*?)\}\}/';

    protected $numMustaches;

    protected $mustaches = [];

    protected $mkeys = [];

    public function __construct($format, $args = null)
    {
        $parentArgs = $this->getFormattedStringArgs($args);
        $parentArgs = array_values($parentArgs);

        $format = str_replace('%s', '{{#fstr}}str{{/fstr}}', $format);
        parent::__construct($format, $parentArgs);
        $format = $this->render();

        $tempArgs = array_filter($args, function ($arg) use ($parentArgs) {
            return !in_array($arg, $parentArgs, true);
        });

        $tempArgs = array_values($tempArgs);

        if (preg_match_all($this->pattern, $format, $matches, PREG_SET_ORDER)) {
            $this->numMustaches = count($matches);

            $this->mustaches = [];

            $this->mkeys = [];
            $j = 0;
            for ($i = 0; $i < $this->numMustaches; $i++) {
                $format = str_replace($matches[$i][0], '%s', $format);
                if ($matches[$i][3] == 'fstr') {
                    $this->mkeys[$i] = $j;
                    $j++;
                } else {
                    $this->mkeys[$i] = $matches[$i][3];
                    $this->mustaches[$matches[$i][3]] = $matches[$i][2];
                }
            }

            $this->format = Html::wantHtml($format);
            $this->templateString($tempArgs);
        }
    }

    /**
     *  render string with mustache template and formatted HTML string elements
     *
     * @param ValidHtml[]  $args
     *
     * @return string
     */
    protected function templateString($args = [])
    {
        if (empty($args)) {
            throw new Exception(sprintf(
                'Empty parameter passed to %s',
                __METHOD__
            ));
        }

        $this->args = [];
        foreach ($args as $key => $arg) {
            if (is_array($arg) && array_keys($arg) !== range(0, count($arg) - 1)) {
                $arg = $this->getMustaches($arg, $this->mustaches);
                $args[$key] = $arg;
            }

            if (!is_array($arg) || !$this->isAssoc($arg)) {
                $args[$key] = array($arg);
            }
        }

        $args = call_user_func_array('array_merge', $args);
        $args = $this->reorderArgs($args, $this->mkeys);

        foreach ($args as $key => $val) {
            $val = Html::wantHtml($val);
            $this->args[$key] = $val;
        }

        return $this;
    }

    /**
     * map mustache template elements to their corresponding HTML elements
     * @param $tags
     * @param $mustaches
     * @return mixed
     */
    protected function getMustaches($tags, $mustaches)
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

                    $subtags = $this->getMustaches($subtags, $nMustaches);
                    $content->setContent(Html::wantHtml($subtags));
                } else {
                    $content->setContent($mustaches[$tag]);
                }
            }
        }
        return $tags;
    }

    /**
     * Filter arguments which are not of type string or array
     * @param null $args
     * @return mixed|null
     */
    protected function getFormattedStringArgs($args = null)
    {
        foreach ($args as $key => $val) {
            if (! is_scalar($val) || is_string($val)) {
                unset($args[$key]);
            }
        }
        return $args;
    }

    /**
     * check if an array is associative
     * @param array $arr
     * @return bool
     */
    protected function isAssoc($arr = [])
    {
        if (array() === $arr) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * reorder arguments into correct order
     * @param array $args
     * @param array $arr
     * @return array|null
     */
    protected function reorderArgs($args = [], $arr = [])
    {
        $reorderedArgs = array_replace_recursive(array_flip($arr), $args);
        return $reorderedArgs;
    }
}
