<?php

namespace ipl\Html;

use RuntimeException;

abstract class BaseHtmlElement extends HtmlDocument
{
    /** @var array You may want to set default attributes when extending this class */
    protected $defaultAttributes;

    /** @var Attributes */
    protected $attributes;

    /** @var string */
    protected $tag;

    /**
     * List of void elements which must not contain end tags or content
     *
     * If {@link $isVoid} is null, this property should be used to decide whether the content and end tag has to be
     * rendered.
     *
     * @var array
     *
     * @see https://www.w3.org/TR/html5/syntax.html#void-elements
     */
    protected static $voidElements = [
        'area'   => 1,
        'base'   => 1,
        'br'     => 1,
        'col'    => 1,
        'embed'  => 1,
        'hr'     => 1,
        'img'    => 1,
        'input'  => 1,
        'link'   => 1,
        'meta'   => 1,
        'param'  => 1,
        'source' => 1,
        'track'  => 1,
        'wbr'    => 1
    ];

    /**
     * List of elements that fall under 'phrasing content' and should not have a content separator.
     *
     * If {@link $contentSeparator} is null, this property should be used to decide whether there should be a newline or
     * no content separator rendered.
     *
     * @var array
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/Guide/HTML/Content_categories#Phrasing_content
     */
    protected static $phrasingContent = [
        'abbr'      => 1,
        'audio'     => 1,
        'b'         => 1,
        'bdo'       => 1,
        'br'        => 1,
        'button'    => 1,
        'canvas'    => 1,
        'cite'      => 1,
        'code'      => 1,
        'command'   => 1,
        'data'      => 1,
        'datalist'  => 1,
        'dfn'       => 1,
        'em'        => 1,
        'embed'     => 1,
        'i'         => 1,
        'iframe'    => 1,
        'img'       => 1,
        'input'     => 1,
        'kbd'       => 1,
        'keygen'    => 1,
        'label'     => 1,
        'mark'      => 1,
        'math'      => 1,
        'meter'     => 1,
        'noscript'  => 1,
        'object'    => 1,
        'output'    => 1,
        'progress'  => 1,
        'q'         => 1,
        'ruby'      => 1,
        'samp'      => 1,
        'script'    => 1,
        'select'    => 1,
        'small'     => 1,
        'span'      => 1,
        'strong'    => 1,
        'sub'       => 1,
        'sup'       => 1,
        'svg'       => 1,
        'textarea'  => 1,
        'time'      => 1,
        'var'       => 1,
        'video'     => 1,
        'wbr'       => 1
    ];

    /** @var bool|null Whether the element is void. If null, void check should use {@link $voidElements} */
    protected $isVoid;

    /**
     * Get the attributes of the element
     *
     * @return  Attributes
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
     * Set the attributes of the element
     *
     * @param   Attributes|array|null   $attributes
     *
     * @return  $this
     */
    public function setAttributes($attributes)
    {
        $this->attributes = Attributes::wantAttributes($attributes);

        return $this;
    }

    /**
     * Set the attribute with the given name and value
     *
     * If the attribute with the given name already exists, it gets overridden.
     *
     * @param   string              $name   The name of the attribute
     * @param   string|bool|array   $value  The value of the attribute
     *
     * @return  $this
     */
    public function setAttribute($name, $value)
    {
        $this->getAttributes()->set($name, $value);

        return $this;
    }

    /**
     * Add the given attributes
     *
     * @param   Attributes|array    $attributes
     *
     * @return  $this
     */
    public function addAttributes($attributes)
    {
        $this->getAttributes()->add($attributes);

        return $this;
    }

    /**
     * Get the default attributes of the element
     *
     * @return  array
     */
    public function getDefaultAttributes()
    {
        return $this->defaultAttributes;
    }

    /**
     * Get the tag of the element
     *
     * Since HTML Elements must have a tag, this method throws an exception if the element does not have a tag.
     *
     * @return  string
     *
     * @throws  RuntimeException   If the element does not have a tag
     */
    final public function getTag()
    {
        $tag = $this->tag();

        if (! strlen($tag)) {
            throw new RuntimeException('Element must have a tag');
        }

        return $tag;
    }

    /**
     * Internal method for accessing the tag
     *
     * You may override this method in order to provide the tag dynamically
     *
     * @return  string
     */
    protected function tag()
    {
        return $this->tag;
    }

    /**
     * Set the tag of the element
     *
     * @param   string  $tag
     *
     * @return  $this
     */
    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Render the content of the element to HTML
     *
     * @return  string
     */
    public function renderContent()
    {
        return parent::renderUnwrapped();
    }

    public function add($content)
    {
        $this->ensureAssembled();

        parent::add($content);

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @throws  RuntimeException   If the element does not have a tag or is void but has content
     */
    public function renderUnwrapped()
    {
        $this->ensureAssembled();

        $tag = $this->getTag();
        $attributes = $this->getAttributes()->render();
        $content = $this->renderContent();

        if ($this->contentSeparator == null) {
            if ($this->isPhrasingContent()) {
                $this->setSeparator('');
            } else {
                $this->setSeparator("\n");
            }
        }

        if (strlen($this->contentSeparator)) {
            if (strlen($content)) {
                $content = $this->contentSeparator . $content . $this->contentSeparator;
            }
        }

        if (! $this->wantsClosingTag()) {
            if (strlen($content)) {
                throw new RuntimeException('Void elements must not have content');
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
     * Determines whether this element falls under 'phrasing content'
     * and should therefore not be rendered with a content separator
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/Guide/HTML/Content_categories#Phrasing_content
     *
     * @return bool
     */
    public function isPhrasingContent()
    {
        return isset(self::$phrasingContent[$this->getTag()]);
    }

    /**
     * Use this element to wrap the given document
     *
     * @param   HtmlDocument    $document
     *
     * @return  $this
     */
    public function wrap(HtmlDocument $document)
    {
        $document->addWrapper($this);

        return $this;
    }

    /**
     * Get whether the closing tag should be rendered
     *
     * @return  bool    True for void elements, false otherwise
     */
    public function wantsClosingTag()
    {
        // TODO: There is more. SVG and MathML namespaces
        return ! $this->isVoid();
    }

    /**
     * Get whether the element is void
     *
     * The default void detection which checks whether the element's tag is in the list of void elements according to
     * https://www.w3.org/TR/html5/syntax.html#void-elements.
     *
     * If you want to override this behavior, use {@link setVoid()}.
     *
     * @return  bool
     */
    public function isVoid()
    {
        if ($this->isVoid !== null) {
            return $this->isVoid;
        }

        $tag = $this->getTag();

        return isset(self::$voidElements[$tag]);
    }

    /**
     * Set whether the element is void
     *
     * You may use this method to override the default void detection which checks whether the element's tag is in the
     * list of void elements according to https://www.w3.org/TR/html5/syntax.html#void-elements.
     *
     * If you specify null, void detection is reset to its default behavior.
     *
     * @param   bool|null    $void
     *
     * @return  $this
     */
    public function setVoid($void = true)
    {
        $this->isVoid = $void === null ?: (bool) $void;

        return $this;
    }
}
