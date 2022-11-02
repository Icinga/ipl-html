<?php

namespace ipl\Html\FormElement;

use ipl\Html\BaseHtmlElement;

class SelectOption extends BaseHtmlElement
{
    protected $tag = 'option';

    /** @var mixed */
    protected $value;

    /** @var string Label of the option */
    protected $label;

    /**
     * SelectOption constructor.
     * @param string|null $value
     * @param string|null $label
     */
    public function __construct($value = null, $label = null)
    {
        $this->value = $value;
        $this->label = $label;

        $this->getAttributes()->registerAttributeCallback('value', [$this, 'getValue']);
    }

    /**
     * Set the label of the option
     *
     * @param string $label
     *
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get the label of the option
     *
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    protected function assemble()
    {
        $this->setContent($this->getLabel());
    }
}
