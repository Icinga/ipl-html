<?php

namespace ipl\Html;

use Traversable;

class Table extends BaseHtmlElement
{
    protected $contentSeparator = ' ';

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
     * @param array|ValidHtml|string $content
     * @return $this
     */
    public function add($content)
    {
        $this->ensureAssembled();

        if ($content instanceof HtmlElement) {
            switch ($content->getTag()) {
                case 'tr':
                    $this->getBody()->add($content);
                    break;

                case 'thead':
                    parent::add($content);
                    if ($this->header !== null) {
                        $this->header = $content;
                    }
                    break;

                case 'tbody':
                    parent::add($content);
                    if ($this->body !== null) {
                        // Hint: we might also want to fail here
                        $this->body = $content;
                    }
                    break;

                case 'tfoot':
                    $this->getBody()->add($content);
                    break;

                case 'caption':
                    if ($this->caption === null) {
                        $this->prepend($content);
                        $this->caption = $content;
                    } else {
                        // Hint: we might also want to fail here
                        $this->add($content);
                    }
                    break;

                default:
                    $this->getBody()->add(static::row($content));
            }
            $this->getBody()->add($content);
        } elseif (is_array($content) || $content instanceof Traversable) {
            $this->getBody()->add(static::row($content));
        } else {
            $this->getBody()->add([$content]);
        }

        return $this;
    }

    /**
     * Set the table title
     *
     * Will be rendered as a "caption" HTML element
     *
     * @param $content
     * @return $this
     */
    public function setCaption($content)
    {
        $this->caption = new HtmlElement('caption', null, $content);

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
    public function generateHeader()
    {
        return $this->nextHeader()->add(
            $this->addHeaderColumnsTo(static::tr())
        );
    }

    /**
     * @return HtmlElement
     */
    public function createFooter($columns = null)
    {
        return new HtmlElement(
            'tfoot',
            null,
            static::row($columns, 'th')
        );
    }

    /**
     * @param HtmlElement $parent
     * @return HtmlElement
     */
    protected function addHeaderColumnsTo(HtmlElement $parent)
    {
        foreach ($this->getHeaderColumns() as $column) {
            $parent->add(static::th($column));
        }

        return $parent;
    }

    /**
     * @return null|array|Traversable
     */
    public function getHeaderColumns()
    {
        return [];
    }

    /**
     * @return HtmlElement
     */
    public function getBody()
    {
        if ($this->body === null) {
            $this->body = Html::tag('tbody')->setSeparator("\n");
        }

        return $this->body;
    }

    /**
     * @return HtmlElement
     */
    public function getHeader()
    {
        if ($this->header === null) {
            $this->header = Html::tag('thead')->setSeparator("\n");
        }

        return $this->header;
    }

    /**
     * @return HtmlElement
     */
    public function getFooter()
    {
        if ($this->footer === null) {
            $this->footer = $this->createFooter();
        }

        return $this->footer;
    }

    /**
     * @return HtmlElement
     */
    public function nextBody()
    {
        if ($this->body !== null) {
            $this->add($this->body);
            $this->body = null;
        }

        return $this->getBody();
    }

    /**
     * @return HtmlElement
     */
    public function nextHeader()
    {
        if ($this->header !== null) {
            $this->add($this->header);
            $this->header = null;
        }

        return $this->getHeader();
    }

    /**
     * @return string
     */
    public function renderContent()
    {
        if (null !== $this->caption) {
            $this->add($this->caption);
        }

        if (null !== $this->header) {
            $this->add($this->header);
        }

        if (null !== $this->body) {
            $this->add($this->getBody());
        }

        if (null !== $this->footer) {
            $this->add($this->footer);
        }

        return parent::renderContent();
    }
}
