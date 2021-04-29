<?php

namespace ipl\Html;

use Exception;

/**
 * Render {{#mustache}}Mustache{{/mustache}}-like string from {@link ValidHtml} element arguments
 *
 * # Example Usage
 * ```
 * $info = (new MustacheTemplate())->getMustaches(
 *     'Example {{#mustache}}Mustache{{/mustache}}',
 *     [
 *        'mustache' => Html::tag('span')
 *     ]
 * );
 * ```
 */
class MustachedTemplate implements ValidHtml
{
    /** @var ValidHtml[] */
    protected $args = [];

    /** @var ValidHtml */
    protected $format;

    protected $pattern = '/\{\{(#[^\{]+)\}\}((?:[^{}]|(?R))*)\{\{\/(.*?)\}\}/';

    protected $numMustaches;
    /**
     * Replace mustache templates with corresponding
     *
     * @param string $format
     * @param ValidHtml[]  $args
     *
     * @return string
     */
    public function replaceMustaches($format, $args = [])
    {
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
            $ms = $this->getMustaches($args, $mustaches);

            foreach ($ms as $key => $val) {
                $val = Html::wantHtml($val);
                $this->args[$key] = $val;
            }
        }

        return $this;
    }

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

    public function render()
    {
        return vsprintf(
            $this->format->render(),
            $this->args
        );
    }
}
