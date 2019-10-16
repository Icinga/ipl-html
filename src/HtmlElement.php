<?php

namespace ipl\Html;

/**
 * The HtmlElement represents any HTML element
 *
 * A typical HTML element includes a tag, attributes and content.
 */
class HtmlElement extends BaseHtmlElement
{
    /**
     * Create a new HTML element from the given tag, attributes and content
     *
     * @param string     $tag        The tag for the element
     * @param Attributes $attributes The HTML attributes for the element
     * @param ValidHtml  ...$content The content of the element
     */
    public function __construct($tag, Attributes $attributes = null, ValidHtml ...$content)
    {
        $this->tag = $tag;

        if ($attributes !== null) {
            $this->getAttributes()->addAttributes($attributes);
        }

        foreach ($content as $element) {
            $this->addIndexedContent($element);
        }
    }

    /**
     * Container constructor.
     *
     * @param string $tag
     * @param mixed  $attributes
     * @param mixed  $content
     *
     * @return static
     */
    public static function create($tag, $attributes = null, $content = null)
    {
        return new static($tag, Attributes::wantAttributes($attributes), ...Html::wantHtmlList($content));
    }
}
