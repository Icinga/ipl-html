<?php

namespace ipl\Html\FormDecorator;

use ipl\Html\Attributes;
use ipl\Html\FormElement\BaseFormElement;
use ipl\Html\FormElement\FieldsetElement;
use ipl\Html\HtmlElement;

/**
 * Decorate the form element with HTML tags
 *
 * @phpstan-type PLACEMENT 'append'|'prepend'|null
 */
class HtmlTagDecorator extends BaseDecorator
{
    /** @var PLACEMENT Describes where the HTML tag should be placed
     *
     * If unknown or null, wrap is used as fallback
     */
    protected ?string $placement = null;

    public function decorate(DecorationResults $results, BaseFormElement $formElement): void
    {

        if ($formElement instanceof FieldsetElement) {
            return;
        }

        $html = new HtmlElement('div', $this->getAttributes());
        switch ($this->getPlacement()) {
            case 'append':
                $results->append($html);
                break;
            case 'prepend':
                $results->prepend($html);
                break;
            default:
                $results->wrap($html);
        }
    }

    /**
     * Set the placement of the HTML tag
     *
     * @param PLACEMENT $placement
     *
     * @return $this
     */
    public function setPlacement(?string $placement): self
    {
        $this->placement = $placement;

        return $this;
    }

    /**
     * Get the placement of the HTML tag
     *
     * @return PLACEMENT
     */
    public function getPlacement(): ?string
    {
        return $this->placement;
    }

    protected function registerAttributeCallbacks(Attributes $attributes): void
    {
        $attributes
            ->registerAttributeCallback('placement', null, [$this, 'setPlacement']);
    }
}
