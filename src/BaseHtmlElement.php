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
     * @inheritdoc
     *
     * @throws  \RuntimeException   If the element is void but has content
     */
    public function renderUnwrapped()
    {
        $this->ensureAssembled();

        $tag = $this->getTag();
        $attributes = $this->getAttributes()->render();
        $content = $this->renderContent();

        if (! $this->wantsClosingTag()) {
            if (strlen($content)) {
                // @TODO(el): Should we add a dedicated exception class?
                throw new \RuntimeException('Void elements must not have content');
            }

            return sprintf('<%s%s />', $tag, $attributes);
        }

        return sprintf(
            '<%s%s>%s</%s>',
            $tag,
            $attributes,
            $content,
            $tag
        );
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
}
