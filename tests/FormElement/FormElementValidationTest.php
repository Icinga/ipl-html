<?php

namespace ipl\Tests\Html\FormElement;

use ipl\Html\FormElement\FieldsetElement;
use ipl\Html\FormElement\TextElement;
use ipl\I18n\NoopTranslator;
use ipl\I18n\StaticTranslator;
use ipl\Tests\Html\TestCase;

class FormElementValidationTest extends TestCase
{
    protected function setUp(): void
    {
        StaticTranslator::$instance = new NoopTranslator();
    }

    public function testRequiredElementsAreInvalidIfValidatedIndividually()
    {
        $element = new TextElement('test_element', ['required' => true]);

        $this->assertFalse($element->isValid(), 'Required form elements do not validate themselves correctly');
    }

    public function testRequiredElementsInFieldsetsAreRequired()
    {
        $fieldset = new FieldsetElement('test');
        $fieldset->addElement('text', 'tset', [
            'required' => true
        ]);

        $this->assertFalse($fieldset->isValid(), 'Required fieldset fields are not required');
    }
}
