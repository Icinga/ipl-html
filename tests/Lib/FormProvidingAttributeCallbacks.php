<?php

namespace ipl\Tests\Html\Lib;

use ipl\Html\Form;
use ipl\Html\FormElement\FieldsetElement;

class FormProvidingAttributeCallbacks extends Form
{
    public function getFormaction(): ?string
    {
        return $this->getAction();
    }
    public function getFormmethod(): ?string
    {
        return $this->getMethod();
    }
    protected function assemble()
    {
        $submit = $this->createElement('submit', 'submit');
        $submit
            ->getAttributes()
            ->registerAttributeCallback('formaction', [$this, 'getFormaction'])
            ->registerAttributeCallback('formmethod', [$this, 'getFormmethod']);
        /** @var FieldsetElement $fieldset */
        $fieldset = $this->createElement('fieldset', 'fieldset');
        $fieldset->addElement($submit);
        $this->addElement($fieldset);
    }
}
