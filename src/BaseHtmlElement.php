<?php

namespace ipl\Html;

abstract class BaseHtmlElement extends HtmlDocument
{
    /** @var array You may want to set default attributes when extending this class */
    protected $defaultAttributes;

    /** @var Attributes */
    protected $attributes;

    /** @var string */
    protected $tag;

    protected static $voidElements = [
        'area',
        'base',
        'br',
        'col',
        'embed',
        'hr',
        'img',
        'input',
        'link',
        'meta',
        'param',
        'source',
        'track',
        'wbr'
    ];

    protected $isVoid;

    /**
     * @return Attributes
     */
    public function getAttributes()
    {
        if ($this->attributes === null) {
            $default = $this->getDefaultAttributes();
            if (empty($default)) {
                $this->attributes = new Attributes();
            } else {
                $this->attributes = Attributes::wantAttributes($default);
            }
        }

        return $this->attributes;
    }

    /**
     * @param Attributes|array|null $attributes
     * @return $this
     */
    public function setAttributes($attributes)
    {
        $this->attributes = Attributes::wantAttributes($attributes);

        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        $this->getAttributes()->set($key, $value);

        return $this;
    }

    /**
     * @param Attributes|array|null $attributes
     * @return $this
     */
    public function addAttributes($attributes)
    {
        $this->getAttributes()->add($attributes);

        return $this;
    }

    /**
     * @return array
     */
    public function getDefaultAttributes()
    {
        return $this->defaultAttributes;
    }

    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    public function getTag()
    {
        return $this->tag;
    }

    public function renderContent()
    {
        return parent::renderUnwrapped();
    }

    /**
     * @param array|ValidHtml|string $content
     * @return $this
     */
    public function add($content)
    {
        $this->ensureAssembled();

        parent::add($content);

        return $this;
    }

    /**
     * @return string
     */
    public function renderUnwrapped()
    {
        $this->ensureAssembled();
        $tag = $this->getTag();

        $content = $this->renderContent();
        if (strlen($content) || $this->wantsClosingTag()) {
            return sprintf(
                '<%s%s>%s</%s>',
                $tag,
                $this->renderAttributes(),
                $content,
                $tag
            );
        } else {
            return sprintf(
                '<%s%s />',
                $tag,
                $this->renderAttributes()
            );
        }
    }

    /**
     * @return string
     */
    public function renderAttributes()
    {
        if ($this->attributes === null && empty($this->defaultAttributes)) {
            return '';
        } else {
            return $this->getAttributes()->render();
        }
    }

    /**
     * @param HtmlDocument $document
     * @return $this
     */
    public function wrap(HtmlDocument $document)
    {
        $document->addWrapper($this);

        return $this;
    }

    public function wantsClosingTag()
    {
        // TODO: There is more. SVG and MathML namespaces
        return ! $this->isVoidElement();
    }

    public function isVoidElement()
    {
        if ($this->isVoid === null) {
            $this->isVoid = in_array($this->tag, self::$voidElements);
        }

        return $this->isVoid;
    }

    public function setVoid($void = true)
    {
        $this->isVoid = $void;

        return $this;
    }

    /**
     * Whether the given something can be rendered
     *
     * @param mixed $any
     * @return bool
     */
    protected function canBeRendered($any)
    {
        return is_string($any) || is_int($any) || is_null($any);
    }
}
