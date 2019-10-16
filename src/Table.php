<?php

namespace ipl\Html;

use RuntimeException;
use Traversable;
use stdClass;

class Table extends BaseHtmlElement
{
    protected $contentSeparator = "\n";

    /** @var string */
    protected $tag = 'table';

    /** @var HtmlElement */
    private $caption;

    /** @var HtmlElement */
    private $header;

    /** @var HtmlElement */
    private $body;

    /** @var HtmlElement */
    private $footer;

    /**
     * @param ValidHtml $html
     * @return $this
     */
    protected function addIndexedContent(ValidHtml $html)
    {
        if ($html instanceof BaseHtmlElement) {
            switch ($html->getTag()) {
                case 'tr':
                    $this->getBody()->addHtml($html);
                    break;

                case 'thead':
                    parent::addIndexedContent($html);
                    $this->header = $html;
                    break;

                case 'tbody':
                    parent::addIndexedContent($html);
                    $this->body = $html;
                    break;

                case 'tfoot':
                    parent::addIndexedContent($html);
                    $this->footer = $html;
                    break;

                case 'caption':
                    if ($this->caption === null) {
                        $this->prepend($html);
                        $this->caption = $html;
                    } else {
                        throw new RuntimeException(
                            'Tables allow only one <caption> tag'
                        );
                    }
                    break;

                default:
                    $this->getBody()->addHtml(static::row([$html]));
            }
        } else {
            $this->getBody()->addHtml(static::row([$html]));
        }

        return $this;
    }

    /**
     * @param mixed $content
     * @return $this
     */
    public function add($content)
    {
        if ($content instanceof stdClass) {
            $this->getBody()->addHtml(static::row((array) $content));
        } elseif (is_array($content) || $content instanceof Traversable) {
            $this->getBody()->addHtml(static::row($content));
        } elseif ($content instanceof ValidHtml) {
            $this->addHtml($content);
        } else {
            $this->getBody()->addHtml(static::row([$content]));
        }

        return $this;
    }

    /**
     * Set the table title
     *
     * Will be rendered as a "caption" HTML element
     *
     * @param $caption
     * @return $this
     */
    public function setCaption($caption)
    {
        if ($caption instanceof BaseHtmlElement && $caption->getTag() === 'caption') {
            $this->caption = $caption;
            $this->prepend($caption);
        } elseif ($this->caption === null) {
            $this->caption = new HtmlElement('caption', null, $caption);
            $this->prepend($this->caption);
        } else {
            $this->caption->setContent($caption);
        }

        return $this;
    }

    /**
     * Static helper creating a tr element
     *
     * @param Attributes|array $attributes
     * @param Html|array|string $content
     * @return HtmlElement
     */
    public static function tr($content = null, $attributes = null)
    {
        return Html::tag('tr', $attributes, $content);
    }

    /**
     * Static helper creating a th element
     *
     * @param Attributes|array $attributes
     * @param Html|array|string $content
     * @return HtmlElement
     */
    public static function th($content = null, $attributes = null)
    {
        return Html::tag('th', $attributes, $content);
    }

    /**
     * Static helper creating a td element
     *
     * @param Attributes|array $attributes
     * @param Html|array|string $content
     * @return HtmlElement
     */
    public static function td($content = null, $attributes = null)
    {
        return Html::tag('td', $attributes, $content);
    }

    /**
     * @param $row
     * @param null $attributes
     * @param string $tag
     * @return HtmlElement
     */
    public static function row($row, $attributes = null, $tag = 'td')
    {
        $tr = static::tr();
        foreach ((array) $row as $value) {
            $tr->add(Html::tag($tag, null, $value));
        }

        if ($attributes !== null) {
            $tr->setAttributes($attributes);
        }

        return $tr;
    }

    /**
     * @return HtmlElement
     */
    public function getBody()
    {
        if ($this->body === null) {
            $this->addHtml(Html::tag('tbody')->setSeparator("\n"));
        }

        return $this->body;
    }

    /**
     * @return HtmlElement
     */
    public function getHeader()
    {
        if ($this->header === null) {
            $this->addHtml(Html::tag('thead')->setSeparator("\n"));
        }

        return $this->header;
    }

    /**
     * @return HtmlElement
     */
    public function getFooter()
    {
        if ($this->footer === null) {
            $this->addHtml(Html::tag('tfoot')->setSeparator("\n"));
        }

        return $this->footer;
    }

    /**
     * @return HtmlElement
     */
    public function nextBody()
    {
        $this->body = null;

        return $this->getBody();
    }

    /**
     * @return HtmlElement
     */
    public function nextHeader()
    {
        $this->header = null;

        return $this->getHeader();
    }
}
