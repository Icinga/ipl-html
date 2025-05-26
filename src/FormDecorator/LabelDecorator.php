<?php

namespace ipl\Html\FormDecorator;

use ipl\Html\Attributes;
use ipl\Html\Contract\FormSubmitElement;
use ipl\Html\FormElement\BaseFormElement;
use ipl\Html\FormElement\FieldsetElement;
use ipl\Html\HtmlElement;
use ipl\Html\HtmlString;
use ipl\Html\Text;

class LabelDecorator extends BaseDecorator
{
    protected const CSS_CLASS = 'form-element-label';

    /** The required cue, set to null to disable it */
    protected ?string $requiredCue = null;

    public function decorate(DecorationResults $results, BaseFormElement $formElement): void
    {
        if ($formElement instanceof FormSubmitElement || $formElement instanceof FieldsetElement) {
            return;
        }

        if ($formElement->isRequired()) {
            $formElement->setAttribute('aria-required', 'true');
        }

        $labelAttr = null;
        if ($formElement->getAttributes()->has('id')) {
            $labelAttr = Attributes::create(['for' => $formElement->getAttributes()->get('id')->getValue()]);
        }

        $label2 = HtmlString::create($formElement->getLabel() ?: '&nbsp;');

        $requiredCue = $this->getRequiredCue();
        if ($requiredCue) {
            $requiredCue = new HtmlElement('span', Attributes::create(['class' => 'required-cue']), Text::create($requiredCue));

            $label2->setContent($label2->getContent() . ' ' . $requiredCue);
        }


        $label = HtmlString::create(($formElement->getLabel() ?: '&nbsp;') . ($requiredCue ? ' '. $requiredCue : ''));

        $results->prepend(
            new HtmlElement(
                'div',
                $this->getAttributes(),
                new HtmlElement('label', $labelAttr, $label2)
            )
        );
    }

    /**
     * Get the required cue
     *
     * @return ?string
     */
    public function getRequiredCue(): ?string
    {
        return $this->requiredCue;
    }

    /**
     * Set the required cue
     *
     * @param mixed $requiredCue
     *
     * @return $this
     */
    public function setRequiredCue(string $requiredCue): self
    {
        $this->requiredCue = $requiredCue;

        return $this;
    }

    protected function registerAttributeCallbacks(Attributes $attributes): void
    {
        $attributes
            ->registerAttributeCallback('requiredCue', null, [$this, 'setRequiredCue']);
    }
}
