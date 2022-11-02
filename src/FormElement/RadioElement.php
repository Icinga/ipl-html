<?php

namespace ipl\Html\FormElement;

use ipl\Html\Attributes;
use ipl\Html\HtmlElement;
use ipl\Html\HtmlString;
use ipl\I18n\Translation;
use ipl\Validator\InArrayValidator;

class RadioElement extends InputElement
{
    use Translation;

    protected $type = 'radio';

    /** @var InputElement[] Radio button elements */
    protected $options;

    /** @var array Values that are not disabled */
    protected $possibleValues = [];

    public function __construct($name, $attributes = null)
    {
        $this->getAttributes()->registerAttributeCallback(
            'options',
            null,
            [$this, 'setOptions']
        );

        $this->getAttributes()->registerAttributeCallback(
            'disabledOptions',
            null,
            [$this, 'disabledOptions']
        );

        parent::__construct($name, $attributes);
    }

    /**
     * Disable radio button with given value
     *
     * @param string $value
     *
     * @return $this
     */
    private function disabledOption(string $value): RadioElement
    {
        $this->valid = null;
        $this->validators = null; // required to add validator with new possibleValues

        $this->possibleValues = array_diff($this->possibleValues, (array) $value);

        if ($option = $this->getOption($value)) {
            $option->setAttribute('disabled', true);
        }

        return $this;
    }

    /**
     * Disable radio buttons with given values
     *
     * @param string[] $values
     *
     * @return $this
     */
    public function disabledOptions(array $values): RadioElement
    {
        foreach ($values as $value) {
            $this->disabledOption($value);
        }

        return $this;
    }

    /**
     * Get radio button with given value
     *
     * @param string $value
     *
     * @return InputElement|null
     */
    public function getOption(string $value): ?InputElement
    {
        return $this->options[$value] ?? null;
    }

    public function setValue($value)
    {
        parent::setValue($value);

        foreach ($this->options as $radio) {
            $radio->getAttributes()->remove('checked');
            if ($radio->getValueAttribute() === $value) {
                $radio->setAttribute('checked', true);
            }
        }

        return $this;
    }

    public function addDefaultValidators()
    {
        $this->getValidators()->add(new InArrayValidator(['haystack' => $this->possibleValues]));
    }

    /**
     * Prepare options
     *
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options): RadioElement
    {
        $this->possibleValues = array_keys($options);

        foreach ($options as $value => $label) {
            $input = (new InputElement($this->getName()))
                ->setType($this->type)
                ->setValue($value)
                ->setLabel($label)
                ->setRequired($this->isRequired())
                ->setAttributes(clone $this->getAttributes());

            $this->options[$value] = $input;
        }

        return $this;
    }

    public function renderUnwrapped()
    {
        // Parent::renderUnwrapped() requires $tag and the content should be empty. However, since we are wrapping
        // each button in a label, the call to parent cannot work here and must be overridden.
        return $this->renderContent();
    }

    protected function assemble()
    {
        foreach ($this->options as $radioElm) {
            $labelElm = new HtmlElement(
                'label',
                Attributes::create(['class' => 'radio-label']),
                $radioElm,
                HtmlString::create($radioElm->getLabel())
            );

            $this->addHtml($labelElm);
        }
    }
}
